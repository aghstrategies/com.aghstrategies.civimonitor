<?php

/**
 * Monitor.Getpaymentprocessors API
 *
 * @param array $params
 * @return array API result descriptor
 * @see civicrm_api3_create_success
 * @see civicrm_api3_create_error
 * @throws API_Exception
 */
function civicrm_api3_monitor_Getpaymentprocessors($params) {
  $types = array();
  $returnValues = array();

  // Get types
  try {
    $result = civicrm_api3('PaymentProcessorType', 'get');
    if (is_array($result['values'])) {
      foreach ($result['values'] as $id => $type) {
        $types[$id] = CRM_Utils_Array::value('title', $type);
      }
    }
  }
  catch (CiviCRM_API3_Exception $e) {
    $error = $e->getMessage();
    throw new API_Exception(/*errorMessage*/ ts('Cannot find the available payment processor types', array('domain' => 'com.aghstrategies.civimonitor')), /*errorCode*/ 10);
  }

  // Get processors
  try {
    $result = civicrm_api3('PaymentProcessor', 'get', array(
      'sequential' => 1,
      'is_test' => 0,
      'is_active' => 1,
    ));

    if (is_array($result['values'])) {
      foreach ($result['values'] as $processor) {
        $returnValues[$processor['id']] = array(
          'id' => $processor['id'],
          'title' => $processor['name'],
          'type' => CRM_Utils_Array::value($processor['payment_processor_type_id'], $types, 'Unknown type'),
        );
      }
    }
  }
  catch (CiviCRM_API3_Exception $e) {
    $error = $e->getMessage();
    throw new API_Exception(/*errorMessage*/ ts('Cannot find the available payment processors', array('domain' => 'com.aghstrategies.civimonitor')), /*errorCode*/ 20);
  }

  return civicrm_api3_create_success($returnValues, $params, 'monitor', 'Getpaymentprocessors');

}
