<?php

require_once 'CRM/Core/Page.php';

class CRM_Migration_Page_Loadpersons extends CRM_Core_Page {
  function run() {
    CRM_Utils_System::setTitle(ts('Load persons into CiviCRM'));

    $this->assign('startLoadUrl', CRM_Utils_System::url('civicrm/processpersons', null, true));

    parent::run();
  }
}
