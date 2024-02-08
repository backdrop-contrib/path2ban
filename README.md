# Path2Ban

> [!IMPORTANT]
> This module is not fully ported or tested. [AntiScan](https://backdropcms.org/project/antiscan)
> is an alternative implementation of this functionality and is actively
> maintained, so consider using that instead.

The Path2Ban module allows you to block web scanner attacks from individual IP
addresses. The module has an editable list of restricted paths. All attempts to
scan a restricted path will be logged:

    `admin.php`
    `admin/index.php`
    `bitrix/admin/index.php`
    `administrator/index.php`
    `wp-login.php`
    and so on.

The module can send notification emails to the site administrator.

This module will start working with the pre-set defaults after you enable it.

You can configure the module at **admin/config/people/path2ban**.

## Default values:

 - `path2ban_threshold_limit = 5`
 - `path2ban_threshold_window = 3600`

This means that an IP will be banned after 5 visits to restricted paths within
one hour (3600 seconds).

## Installation and Usage

- Install this module using the [official Backdrop CMS instructions](https://backdropcms.org/guide/modules)
- Usage instructions can be [viewed and edited in the Wiki](https://github.com/backdrop-contrib/path2ban/wiki).

## Issues

 - Bugs and Feature requests should be reported in the [Issue Queue](https://github.com/backdrop-contrib/path2ban/issues).

## Current Maintainers

 - Seeking maintainers

## Credits

 - Ported to Backdrop CMS by [Laryn Kragt Bakker](https://github.com/laryn).
 - Maintainers on drupal.org include [ivan-simonov](https://www.drupal.org/u/ivan-simonov)
   and [civifirst-john](https://www.drupal.org/u/civifirst-john).

## License

This project is GPL v2 software. See the LICENSE.txt file in this directory for
complete text.
