<?php

require_once 'CRM/Core/Page.php';

class CRM_Migration_Page_Processpersons extends CRM_Core_Page {
    function run() {
        /*
         * check and create specific group for Migration
         */
        $groupTitle = "Migration PUM ".date("Ymd");
        try {
            $migrateGroup = civicrm_api3('Group', 'Getsingle', array('title' => $groupTitle));
            if (isset($migrateGroup['id'])) {
                try {
                    civicrm_api('Group', 'Delete', array('id' => $migrateGroup['id']));
                } catch (CiviCRM_API3_Exception $e) {
                }
            }
        } catch (CiviCRM_API3_Exception $e) {
        }
        $newGroupParams = array(
            'title'         =>  $groupTitle,
            'name'          =>  $groupTitle,
            'description'   =>  "Migration group for PUM",
            'is_reserved'   =>  1
        );
        try {
            $migrateGroup = civicrm_api3('Group', 'Create', $newGroupParams);
            if (isset($migrateGroup['id'])) {
                $migrateGroupId = $migrateGroup['id'];
            }
        } catch (CiviCRM_API3_Exception $e) {
            CRM_Core_Session::setStatus('Could not create group '.$groupTitle, 'alert');
        }
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
                $newContact = civicrm_api3('Contact', 'Create', $contactParams);
                if (isset($newContact['id'])) {
                    $newContactId = $newContact['id'];
                    try {
                        $groupContactParams = array(
                            'group_id'      =>  $migrateGroupId,
                            'contact_id'    =>  $newContactId
                        );
                        civicrm_api3('GroupContact', 'Create', $groupContactParams);
                    } catch (CiviCRM_API3_Exception $e) {
                        
                    }
                }
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
