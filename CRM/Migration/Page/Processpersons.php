<?php

require_once 'CRM/Core/Page.php';

class CRM_Migration_Page_Processpersons extends CRM_Core_Page {
    function run() {
        /*
         * initialize Persons class
         */
        require_once "CRM/Migration/Person.php";
        $person = new CRM_Migration_Person();
        /*
         * load source data into daoSource
         */
        $daoSource = $person->loadSourceDAO();
        while ($daoSource->fetch()) {
            $contactParams = $person->setApiParams($daoSource);
            try {
                civicrm_api3('Contact', 'Create', $contactParams);
            }
            catch (CiviCRM_API3_Exception $e) {
                $sourceName = $daoSource->firstname." ";
                if (!empty($daoSource->infix)) {
                    $sourceName .= $daoSource->infix." ";
                }
                $sourceName .= $daoSource->surname;
                $message = "Could not create CiviCRM contact for person ".$sourceName." with unique ID ".$daoSource->unid;
                CRM_Utils_System::setUFMessage($message);
            }
        }        
        $this->assign('homeCiviUrl', CRM_Utils_System::url('civicrm', null, true));  
        parent::run();
    }
}
