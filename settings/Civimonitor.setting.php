<?php
/**
 * @file
 * Settings metadata for com.aghstrategies.civimonitor.
 */

return array(
  'lastCron' => array(
    'group_name' => 'CiviMonitor',
    'group' => 'Civimonitor',
    'name' => 'lastCron',
    'type' => 'Integer',
    'default' => NULL,
    'add' => '4.6',
    'is_domain' => 1,
    'is_contact' => 0,
    'description' => 'Timestamp of last cron run',
    'help_text' => 'Set when cron is run',
  ),
);
