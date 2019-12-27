<?php

/**
 * @file
 * path2ban core functionality file.
 */

/**
 * @class Path2ban
 * The Path2ban class contains the core functionality to assess and block
 * visitors who violate rules.
 */
class Path2ban {

  /**
   * This function compare real path and restricted, and takes action if necessary.
   * @return bool whether path2ban action was taken.
   */
  public static function destination_check() {
    // Convert the Drupal path to lowercase.
    $destination = '';
    if (array_key_exists('destination', $_GET)) {
      $destination = drupal_strtolower($_GET['destination']);
    }

    // Don't accidentally error because of an empty string.
    if (empty($destination)) {
      return FALSE;
    }

    // Compare the lowercase paths.
    $pages = drupal_strtolower(variable_get('path2ban_list', ''));
    $page_match = drupal_match_path($destination, $pages);

    if (!$page_match) {
      return FALSE;
    }

    $should_block_user = self::should_block_user();

    if (!$should_block_user) {
      return FALSE;
    }

    self::block_user();
    return TRUE;
  }

  /**
   * Registers the infraction and considers blocking the user.
   * @return bool true if should block the user.
   */
  private static function should_block_user() {
    global $user;
    if ($user->uid == 1) {
      drupal_set_message(t('Hi User One! Use another account and another IP for testing path2ban module. Your IP not banned.'));
      return FALSE;
    }

    $bypass = (user_access('bypass path2ban')) ;
    $window = intval(variable_get('path2ban_threshold_window', 3600));
    $limit = intval(variable_get('path2ban_threshold_limit', 5));
    $limit = ($limit < 1) ? 1 : $limit;
    //$testmode = variable_get('path2ban_test_mode', 0);

    flood_register_event('path2ban', $window); // by default: $window=3600, $identifier=ip
    if ($bypass) {
      drupal_set_message(t('Your IP address has been logged.'), 'warning');
    }

    if (flood_is_allowed('path2ban', $limit, $window)) { // by default: $window=3600
      return FALSE;
    }

    // When flood_is_allowed returns false, the user has run out of chances.
    if ($bypass) {
    watchdog('path2ban', 'Would have banned IP address %ip but they have the \'bypass path2ban\' role.', array('%ip' => ip_address()));
    return FALSE;
    }

    return TRUE; // We should block the user.
  }

  /**
   * This function bans IP addresses of web scanners and sends a notification 
   * email to User One.
   */
  private static function block_user() {
    // Actually ban.
    $ip = ip_address();
    db_insert('blocked_ips')
      ->fields(array('ip' => $ip))
      ->execute();
    watchdog('path2ban', 'Banned IP address %ip', array('%ip' => $ip));
    drupal_set_message(t('Sorry, your IP has been banned.'), 'error');

    // Notify user one.
    if (variable_get('path2ban_notify', 0)) {
      $user1 = user_load(1);
      $url = url('admin/config/people/ip-blocking', array('absolute' => TRUE));
      $params['subject'] = variable_get('site_name') . t(': Blocked IP due to web-scanner attack');
      $params['body'][] = t("Hi User One,
        There were suspected web-scanner activities.
        Associated IP (@ip) has been blocked.
        You can review the list of blocked IPs at @url
        Thank you.
        Sent by path2ban module.
      ", array('@ip' => $ip, '@url' => $url));
      //drupal_mail('path2ban', 'blocked-ip', $user1->mail, language_default(), $params);
      drupal_mail('path2ban', 'blocked-ip', $user1->mail, user_preferred_language($user1), $params);
    }
  }
}