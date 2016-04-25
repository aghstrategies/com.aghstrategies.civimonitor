<?php

/**
 * Copyright 2014-2015 AGH Strategies, LLC
 * Released under the Affero GNU Public License version 3
 * but with NO WARRANTY: neither the implied warranty of merchantability
 * nor fitness for a particular purpose
 */

require_once 'civimonitor.civix.php';

/**
 * Implements hook_civicrm_cron().
 */
function civimonitor_civicrm_cron($jobManager) {
  $params = array(
    'version' => 3,
    'lastCron' => gmdate('U'),
  );
  $result = civicrm_api('Setting', 'create', $params);
}

/**
 * Implements hook_civicrm_config().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_config
 */
function civimonitor_civicrm_config(&$config) {
  _civimonitor_civix_civicrm_config($config);
}

/**
 * Implements hook_civicrm_xmlMenu().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_xmlMenu
 */
function civimonitor_civicrm_xmlMenu(&$files) {
  _civimonitor_civix_civicrm_xmlMenu($files);
}

/**
 * Implements hook_civicrm_install().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_install
 */
function civimonitor_civicrm_install() {
  return _civimonitor_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_uninstall().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_uninstall
 */
function civimonitor_civicrm_uninstall() {
  return _civimonitor_civix_civicrm_uninstall();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_enable
 */
function civimonitor_civicrm_enable() {
  return _civimonitor_civix_civicrm_enable();
}


/**
 * Implements hook_civicrm_enable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_disable
 */
function civimonitor_civicrm_disable() {
  return _civimonitor_civix_civicrm_disable();
}

/**
 * Implements hook_civicrm_upgrade().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_upgrade
 */
function civimonitor_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _civimonitor_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implements hook_civicrm_managed().
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_managed
 */
function civimonitor_civicrm_managed(&$entities) {
  return _civimonitor_civix_civicrm_managed($entities);
}

/**
 * Implements hook_civicrm_caseTypes().
 *
 * Generate a list of case-types
 *
 * Note: This hook only runs in CiviCRM 4.4+.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_caseTypes
 */
function civimonitor_civicrm_caseTypes(&$caseTypes) {
  _civimonitor_civix_civicrm_caseTypes($caseTypes);
}

/**
 * Implements hook_civicrm_alterSettingsFolders().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_alterSettingsFolders
 */
function civimonitor_civicrm_alterSettingsFolders(&$metaDataFolders = NULL) {
  _civimonitor_civix_civicrm_alterSettingsFolders($metaDataFolders);
}

/**
 * Implements hook_civicrm_alterAPIPermissions().
 */
function civimonitor_civicrm_permission(&$permissions) {
  $version = CRM_Utils_System::version();
  if (version_compare($version, '4.6.1') >= 0) {
    $permissions += array(
      'access CiviMonitor' => array(
        ts('Access CiviMonitor', array('domain' => 'com.aghstrategies.civimonitor')),
        ts('Grants the necessary API permissions for a monitoring user without Administer CiviCRM', array('domain' => 'com.aghstrategies.civimonitor')),
      ),
    );
  }
  else {
    $permissions += array(
      'access CiviMonitor' => ts('Access CiviMonitor', array('domain' => 'com.aghstrategies.civimonitor')),
    );
  }
}

/**
 * Implements hook_civicrm_alterAPIPermissions().
 */
function civimonitor_civicrm_alterAPIPermissions($entity, $action, &$params, &$permissions) {
  if (CRM_Core_Permission::check('administer CiviCRM')) {
    return;
  }

  switch ($entity) {
    case 'setting':
      if ($action == 'get' && $params['return'] == 'lastCron') {
        $permissions['setting'] = array(
          'get' => array(
            'access CiviMonitor',
          ),
        );
      }
      break;

    case 'domain':
      if ($action == 'get' && $params['return'] == 'version') {
        $permissions['domain'] = array(
          'get' => array(
            'access CiviMonitor',
          ),
        );
      }
      break;

    case 'monitor':
      $permissions['monitor'] = array(
        'getextensions' => array(
          'access CiviMonitor',
        ),
        'getpaymentprocessors' => array(
          'access CiviMonitor',
        ),
        'getmailingbackend' => array(
          'access CiviMonitor',
        ),
      );
      break;

    case 'system':
      $permissions['system'] = array(
        'check' => array(
          'access CiviMonitor',
        ),
      );
      break;

    default:
  }
}
