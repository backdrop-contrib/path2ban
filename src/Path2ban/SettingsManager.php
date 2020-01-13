<?php

/**
 * @file
 * To contain the path2ban settings manager class
 */

/**
 * @class
 * Path2ban_SettingsManager is a container class to store the functionality that
 * manages Path2ban's settings.
 */
abstract class Path2ban_SettingsManager {

  const MODE_USE_MENU_CALLBACK = 1;
  const MODE_USE_HOOK = 2;

  /**
   * A utility function to add new entries to the restricted paths list.
   *
   * @param array $new_entries
   */
  public static function addNewEntries($new_entries) {
    $list = variable_get('path2ban_list', path2ban_get_default_paths_to_ban());
    $list = $list . "\n";

    // Check that the user hasn't already added them before adding.
    foreach ($new_entries as $each_new_entry) {
      if (FALSE === strpos($list, $each_new_entry)) {
        $list .= $each_new_entry . "\n";
      }
    }

    variable_set('path2ban_list', $list);
  }

  /**
   * Switch to hook mode, clearing the site_403 and site_404 variables as we go.
   */
  public static function switchToHookMode() {
    variable_set('path2ban_mode', self::MODE_USE_HOOK);

    // Wipe old path variables.
    if ('path2ban/403' == variable_get('site_403')) {
      variable_set('site_403', '');
    }
    if ('path2ban/404' == variable_get('site_404')) {
      variable_set('site_404', '');
    }
  }

  /**
   * Switch to the old mode, which needs the site_403 and site_404 variables to
   * be set.
   */
  public static function switchToMenuCallbackMode() {
    variable_set('path2ban_mode', self::MODE_USE_MENU_CALLBACK);

    // Log what the old settings were.
    $old_site_403 = variable_get('site_403');
    $old_site_404 = variable_get('site_404');

    // Update the settings to the new values.
    variable_set('site_403', 'path2ban/403');
    variable_set('site_404', 'path2ban/404');

    // Show the user messages and log the old entries if needed.
    $variables_changed = FALSE;

    if (!in_array($old_site_403, array('', 'path2ban/403'))) {
      $variables_changed = TRUE;
    }
    if (!in_array($old_site_404, array('', 'path2ban/404'))) {
      $variables_changed = TRUE;
    }

    if (TRUE == $variables_changed) {
      if (module_exists('dblog')) {
        watchdog(
          'path2ban',
          'Path2ban updated your site_403 and site_404 settings. site_403 was
            \'%old_site_403\' and site_404 was \'%old_site_404\'.',
          array(
            '%old_site_403' => $old_site_403,
            '%old_site_404' => $old_site_404
          ),
          WATCHDOG_WARNING
        );
        drupal_set_message("Your site 403 and 404 paths were overridden.\n The
        old entries can be found in your watchdog log.");
      }
      else {
        drupal_set_message('Your site 403 and 404 paths were overridden.');
      }
    }

  }

}
