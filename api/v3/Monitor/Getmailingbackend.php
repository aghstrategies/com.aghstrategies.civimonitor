<?php

/**
 * Monitor.Getmailingbackend API
 *
 * @param array $params
 * @return array API result descriptor
 * @see civicrm_api3_create_success
 * @see civicrm_api3_create_error
 * @throws API_Exception
 */
function civicrm_api3_monitor_Getmailingbackend($params) {
  // Get types
  try {
    $result = civicrm_api3('Setting', 'get', array(
      'sequential' => 1,
      'return' => "mailing_backend",
    ));
    if (is_array($result['values'])) {
      foreach ($result['values'] as $settings) {
        $attrib = $settings['mailing_backend'];
        switch ($attrib['outBound_option']) {
          case 0:  // SMTP
            if (!empty($attrib['smtpServer'])) {
              $return = array(
                'message' => "SMTP: {$attrib['smtpServer']}",
                'status' => 0,
              );
            }
            else {
              $return = array(
                'message' => "SMTP: no server set",
                'status' => 2,
              );
            }
            break 2;

          case 1:  // Sendmail
            if (!empty($attrib['sendmail_path'])) {
              $return = array(
                'message' => "Sendmail: {$attrib['sendmail_path']}",
                'status' => 0,
              );
            }
            else {
              $return = array(
                'message' => "Sendmail: no path set",
                'status' => 2,
              );
            }
            break 2;

          case 2:  // Disabled
          case 5:  // Redirect to database
            $return = array(
              'message' => 'Outbound mail disabled',
              'status' => 2,
            );
            break 2;

          case 3:  // mail()
            $return = array(
              'message' => 'PHP mail()',
              'status' => 0,
            );
            break 2;

          default:
            $return = array(
              'message' => 'Unknown mailer',
              'status' => 0,
            );
        }
      }
    }
  }
  catch (CiviCRM_API3_Exception $e) {
    $error = $e->getMessage();
    throw new API_Exception(/*errorMessage*/ ts('Cannot find the settings for mailing backend', array('domain' => 'com.aghstrategies.civimonitor')), /*errorCode*/ 10);
  }

  $returnValues = array($return);

  return civicrm_api3_create_success($returnValues, $params, 'monitor', 'Getmailingbackend');
}
