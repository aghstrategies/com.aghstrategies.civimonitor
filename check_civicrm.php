<?php

/**
 * Copyright 2014 AGH Strategies, LLC
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

//server providing version status
$vstatus_server = '';

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

    $a = json_decode($result, true);
    if ($a["is_error"] != 1 && is_array($a['values'])) {
      foreach ($a["values"] as $id => $attrib) {
        if (isset($attrib['version'])) {
          $status = file_get_contents("$vstatus_server/?version={$attrib['version']}");
          $status = json_decode($status);
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
