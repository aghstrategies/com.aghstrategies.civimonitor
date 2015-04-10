<?php

/**
 * Monitor.Getextensions API
 *
 * @param array $params
 * @return array API result descriptor
 * @see civicrm_api3_create_success
 * @see civicrm_api3_create_error
 * @throws API_Exception
 */
function civicrm_api3_monitor_Getextensions($params) {
  $mapper = CRM_Extension_System::singleton()->getMapper();
  $manager = CRM_Extension_System::singleton()->getManager();
  $remotes = CRM_Extension_System::singleton()->getBrowser()->getExtensions();

  $keys = array_keys($manager->getStatuses());
  sort($keys);

  $return = 0;
  $msgArray = $okextensions = array();

  foreach ($keys as $key) {
    try {
      $obj = $mapper->keyToInfo($key);
    }
    catch (CRM_Extension_Exception $ex) {
      $return = ($return < 2) ? 3 : $return;
      $msgArray[] = ts('Failed to read extension (%1). Please refresh the extension list.', array(1 => $key));
      continue;
    }
    $row = CRM_Admin_Page_Extensions::createExtendedInfo($obj);
    switch ($row['status']) {
      case CRM_Extension_Manager::STATUS_UNINSTALLED:
      case CRM_Extension_Manager::STATUS_DISABLED:
      case CRM_Extension_Manager::STATUS_DISABLED_MISSING:
      continue 2;

      case CRM_Extension_Manager::STATUS_INSTALLED_MISSING:
      $return = 2;
      $msgArray[] = ts('%1 extension (%2) is installed but missing files.', array(1 => CRM_Utils_Array::value('label', $row), 2 => $key));
      continue;

      case CRM_Extension_Manager::STATUS_INSTALLED:
      if (CRM_Utils_Array::value($key, $remotes)) {
        if (version_compare($row['version'], $remotes[$key]->version, '<')) {
          $return = ($return < 1) ? 1 : $return;
          $msgArray[] = ts('%1 extension (%2) is upgradeable to version %3.', array(1 => CRM_Utils_Array::value('label', $row), 2 => $key, 3 => $remotes[$key]->version));
        }
        else {
          $okextensions[] = CRM_Utils_Array::value('label', $row) ? "{$row['label']} ($key)" : $key;
        }
      }
      else {
        $okextensions[] = CRM_Utils_Array::value('label', $row) ? "{$row['label']} ($key)" : $key;
      }
      break;
      default:
    }
  }

  $msg = implode('  ', $msgArray);
  if (empty($msgArray)) {
    $msg = (empty($okextensions)) ? 'No extensions installed.' : 'Extensions up-to-date: ' . implode(', ', $okextensions);
  }
  elseif (!empty($okextensions)) {
    $msg .= '  Other extensions up-to-date: ' . implode(', ', $okextensions);
  }

  $returnValues = array( // OK, return several data rows
    array('status' => $return, 'message' => $msg),
  );
  return civicrm_api3_create_success($returnValues, $params, 'monitor', 'Getextensions');
}
