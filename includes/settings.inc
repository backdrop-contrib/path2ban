<?php

/**
 * @file
 * path2ban settings file.
 *
 */

/**
 * Display path2ban settings form.
 */
function path2ban_settings() {
  //TODO: hard and soft IP block
  $form = array();
  $form['path2ban_options'] = array(
    '#type' => 'fieldset',
    '#title' => t('path2ban options'),
  );
  $description = t("Specify pages by using their paths. Enter one path per line. The '*' character is a wildcard. Example paths are %blog for the blog page and %blog-wildcard for every personal blog. %front is the front page.", array('%blog' => 'blog', '%blog-wildcard' => 'blog/*', '%front' => '<front>'));
  $form['path2ban_options']['path2ban_list'] = array(
    '#type' => 'textarea',
    '#title' => t('List of restricted paths'),
    '#description' => $description,
    '#default_value' => variable_get('path2ban_list', ''),
    '#rows' => 25,
  );
  $form['path2ban_options']['path2ban_threshold_limit'] = array(
    '#type' => 'textfield',
    '#title' => t('Threshold limit'),
    '#description' => t('The number of times an IP address can access restricted URLs during the threshold window before they are banned.'),
    '#default_value' => variable_get('path2ban_threshold_limit', 5),
  );
  $form['path2ban_options']['path2ban_threshold_window'] = array(
    '#type' => 'textfield',
    '#title' => t('Threshold window'),
    '#description' => t('The time period in which to consider attacks. 3600 seconds is one hour.'),
    '#default_value' => variable_get('path2ban_threshold_window', 3600),
  );
  $form['path2ban_options']['path2ban_notify'] = array(
    '#type' => 'checkbox',
    '#title' => t('Notify User One?'),
    '#default_value' => variable_get('path2ban_notify', 0),
    '#description' => t('Notify User One by email about blocked IP addresses.'),
  );
/*  $form['path2ban_options']['path2ban_test_mode'] = array(
    '#type' => 'checkbox',
    '#title' => t('Test mode'),
    '#default_value' => variable_get('path2ban_test_mode', 0),
    '#description' => t('Do not block any IP addresses'),
  );*/
  $form['path2ban_options']['path2ban_use_hooks'] = array(
    '#type' => 'checkbox',
    '#title' => t('Use hooks'),
    '#default_value' => variable_get('path2ban_use_hooks', 0),
    '#description' => t('Selecting this opption means that path2ban still works
      even if other modules, or your own configuration, uses Drupal\'s 403 and
      404 routes. There may be a reduction in performance across your website.
      <br>path2ban\'s maintainers are actively seeking feedback on this change,
      and invite you to leave comments and feedback
      <a href=\'https://www.drupal.org/project/path2ban/issues/3103110\'>here</a>.'),
  );

  return system_settings_form($form);
}

/**
 * Implements hook_page_alter()
 *
 * If watchdog is available, includes the lists of recent 403 and 404 errors.
 */
function path2ban_page_alter(&$page) {
  if ('admin/config/people/path2ban' !== $_GET['q']) {
    return;
  }

  // Some sites disable watchdog.
  if (!module_exists('dblog')) {
    $page['content']['no_blog_warning'] = array(
      '#markup' => '<div>On sites with Drupal watchdog enabled, we include a list
        of recent 403 and 404 errors here. We recommend you review your operating
        system\'s logs to discover new paths to restrict.</div><br/>',
    );
    return;
  }

  // At this point we know that watchdog is enabled.
  module_load_include('inc', 'dblog', 'dblog.admin');

  $page['content']['path2ban_error_list'] = array(
    '#type' => 'fieldset',
    '#title' => t('Recent 404 errors'),
  );

  $page['content']['path2ban_error_list']['intro'] = array(
    '#markup' => '<div>This is a list of recent 404 errors from watchdog.
      You can use these to decide which paths to restrict using the field above.
      <br/><br/>
      Occasionally legitimate visitors may get links wrong, or may
      even attempt to find out what CMS you\'re using. Therefore it\'s
      usually best not to block every item that appears in this list.<br/><br/>
      In most cases it\'s probably best not to restrict visitors based on access
      denied errors, as those can occur when users are automatically logged out,
      but you can find a list of those
      <a href="/admin/reports/access-denied">here</a>.</div><br/>',
  );

  $page['content']['path2ban_error_list']['404_header'] = array(
    '#markup' => '<strong>404 errors</strong><br/>',
   );

  $page['content']['path2ban_error_list']['404_errors'] = dblog_top('page not found');
}