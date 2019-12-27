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