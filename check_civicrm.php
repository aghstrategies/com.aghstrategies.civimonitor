<?php

/**
 * Copyright 2014-2015 AGH Strategies, LLC
 * Released under the Affero GNU Public License version 3
 * but with NO WARRANTY: neither the implied warranty of merchantability
 * nor fitness for a particular purpose
 *
 * Place in /usr/lib/nagios/plugins
 *
 * Call with the commands:
 * /usr/bin/php /usr/lib/nagios/plugins/check_civicrm.php $HOSTADDRESS$ $_HOSTHTTP$ $_HOSTCMS$ $_HOSTSITE_KEY$ $_HOSTAPI_KEY$ cron
 * and
 * /usr/bin/php /usr/lib/nagios/plugins/check_civicrm.php $HOSTADDRESS$ $_HOSTHTTP$ $_HOSTCMS$ $_HOSTSITE_KEY$ $_HOSTAPI_KEY$ version
 *
 * in the host definition, provide the following custom variables:
 * _http      [http|https]
 * _cms       [drupal|joomla|wordpress]
 * _site_key  {your site key from settings.php}
 * _api_key   {an api key set on the civicrm_contact row corresponding to an admin user}
 */

$prot = ($argv[2] == 'https') ? 'https' : 'http';

switch (strtolower($argv[3])) {
  case 'joomla':
    $path = 'administrator/components/com_civicrm/civicrm';
    break;

  case 'wordpress':
    $path ='wp-content/plugins/civicrm/civicrm';
    break;

  case 'drupal':
  default:
    $path = 'sites/all/modules/civicrm';
}

switch (strtolower($argv[6])) {
  case 'version':
    $result = file_get_contents("$prot://{$argv[1]}/$path/extern/rest.php?entity=domain&action=get&key={$argv[4]}&api_key={$argv[5]}&return=version&json=1");

    $latest = file_get_contents('http://latest.civicrm.org/stable.php?format=json');

    $a = json_decode($result, true);
    if ($a["is_error"] != 1 && is_array($a['values'])) {
      foreach ($a["values"] as $id => $attrib) {
        if (isset($attrib['version'])) {
          $status = array(3, 'Unknown version status');
          $latest = json_decode($latest, true);
          ksort($latest, SORT_NUMERIC);
          list($m, $mm) = explode('.', $attrib['version']);
          if (isset($latest["{$m}.{$mm}"])) {
            if (isset($latest["{$m}.{$mm}"]['status'])) {
              if (version_compare("{$m}.{$mm}", '4.4') < 0) {
                echo "Much newer version available (currently on {$attrib['version']})";
                exit(2);
              }
              else {
                if ($latest["{$m}.{$mm}"]['status'] == 'lts') {
                  $latest["{$m}.{$mm}"]['status'] = 'LTS';
                }
                $versionDisplay = $attrib['version'] . ' ' . $latest["{$m}.{$mm}"]['status'];
              }
            }
            else {
              $versionDisplay = "{$attrib['version']} (unknown major version status)";
            }
            foreach ($latest["{$m}.{$mm}"]['releases'] as $info) {
              if (version_compare($attrib['version'], $info['version']) < 0) {
                if (isset($info['security']) && $info['security']) {
                  $status = array(2, "Security upgrade needed (currently on $versionDisplay)");
                  break;
                }
                else {
                  $status = array(1, "Newer version available (currently on $versionDisplay)");
                }
              }
              elseif (version_compare($attrib['version'], $info['version']) == 0) {
                $status = array(0, "Version $versionDisplay up-to-date");
              }
            }
          }
          echo $status[1];
          exit($status[0]);
        }
      }
    }
    echo 'Unknown error';
    exit(3);
    break;

  case 'cron':
    $result = file_get_contents("$prot://{$argv[1]}/$path/extern/rest.php?entity=setting&action=get&key={$argv[4]}&api_key={$argv[5]}&return=lastCron&json=1");

    $a = json_decode($result, true);

    if ($a["is_error"] != 1 && is_array($a['values'])) {
      foreach ($a["values"] as $id => $attrib) {
        if ($attrib['lastCron'] > gmdate('U') - 3600) {
          echo 'Last cron at ' . date('r', $attrib['lastCron']);
          exit(0);
        }
        elseif ($attrib['lastCron'] > gmdate('U') - 86400) {
          echo 'Last cron at ' . date('r', $attrib['lastCron']);
          exit(1);
        }
        elseif ($attrib['lastCron'] <= gmdate('U') - 86400) {
          echo 'Last cron at ' . date('r', $attrib['lastCron']);
          exit(2);
        }
      }
    }
    echo 'Unknown error';
    exit(3);
    break;

  default:
    echo 'No command given';
    exit(3);
}
