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
abstract class Path2ban {

  /**
   * This function compare real path and restricted, and takes action if necessary.
   *
   * @return bool whether path2ban action was taken.
   */
  public static function destinationCheck() {
    // Convert the Backdrop path to lowercase.
    $destination = '';
    if (array_key_exists('destination', $_GET)) {
      $destination = backdrop_strtolower($_GET['destination']);
    }

    // Don't accidentally error because of an empty string.
    if (empty($destination)) {
      return FALSE;
    }

    // Compare the lowercase paths.
    $pages = backdrop_strtolower(config_get('path2ban.settings', 'list'));
    $page_match = backdrop_match_path($destination, $pages);

    if (!$page_match) {
      return FALSE;
    }

    $should_block_user = self::shouldBlockUser();

    if (!$should_block_user) {
      return FALSE;
    }

    self::blockUser($destination);
    return TRUE;
  }

  /**
   * Registers the infraction and considers blocking the user.
   *
   * @return bool true if should block the user.
   */
  private static function shouldBlockUser() {
    global $user;
    if ($user->uid == 1) {
      backdrop_set_message(t('Hi User One! Use another account and another IP for testing path2ban module. Your IP not banned.'));
      return FALSE;
    }

    $bypass = (user_access('bypass path2ban'));
    $window = intval(config_get('path2ban.settings', 'threshold_window'));
    $limit = intval(config_get('path2ban.settings', 'threshold_limit'));
    $limit = ($limit < 1) ? 1 : $limit;

    if ($bypass) {
      watchdog('path2ban', 'Permitting IP address %ip as they have the \'bypass path2ban\' role.', array('%ip' => ip_address()));
      return FALSE;
    }

    flood_register_event('path2ban', $window);

    // When flood_is_allowed returns false, the user has run out of chances.
    if (flood_is_allowed('path2ban', $limit, $window)) {
      if (config_get('path2ban.settings', 'warn_user')) {
        backdrop_set_message(config_get('path2ban.settings', 'warn_user_message'), 'warning');
      }
      return FALSE;
    }

    // We should block the user.
    return TRUE;
  }

  /**
   * This function bans IP addresses of web scanners and sends a notification
   * email to User One.
   */
  private static function blockUser($destination = '') {
    $config = config('path2ban.settings');
    // Actually ban.
    $ip = ip_address();
    $reason = t('Path2Ban: Most recent attempt was %destination', array(
      '%destination' => $destination,
    ));
    ip_blocking_block_ip($ip, $reason, 'path2ban');

    state_set('path2ban_banned_count', state_get('path2ban_banned_count') + 1);

    backdrop_set_message(t('Sorry, your IP has been banned.'), 'error');

    // Notify user one.
    if ($config->get('notify')) {
      $user1 = user_load(1);
      $url = url('admin/config/people/ip-blocking', array('absolute' => TRUE));
      $params['subject'] = config_get('system.core', 'site_name') . t(': Blocked IP due to web-scanner attack');
      $params['body'][] = t("Hi User One,
        There were suspected web-scanner activities.
        Associated IP (@ip) has been blocked.
        The most recent attempt was @destination.
        You can review the list of blocked IPs at @url
        Thank you.
        Sent by path2ban module.
      ", array('@ip' => $ip, '@destination' => $destination, '@url' => $url));
      backdrop_mail('path2ban', 'blocked-ip', $user1->mail, user_preferred_language($user1), $params);
    }
  }

}
