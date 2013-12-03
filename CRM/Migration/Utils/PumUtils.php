<?php
/*
+--------------------------------------------------------------------+
| Project       :   CiviCRM PUM Implementation                       |
| Part of       :   CiviCRM extension nl.pum.generic                 |
| Description   :   Class with PUM helper functions                  |
| License       :   Academic Free License V3.0                       |
+--------------------------------------------------------------------+
*/

/**
*
* @package CRM
* @copyright CiviCRM LLC (c) 2004-2013
* $Id$
*
*/
class CRM_Utils_PumUtils {
    /**
     * function to retrieve the ID of a Custom Field, based on label, name or
     * column_name
     * 
     * @author Erik Hommel (erik.hommel@civicoop.org)
     * @date 24 Nov 2013
     * @param $params containing either label, name or column_name
     * @return $result array with id, is_error and possibly error_message
     */
    static function retrieveCustomFieldId($params) {
        /*
         * requires label, name or column_name
         */
        $customFieldId = 0;
        $result = array();
        if (empty($params)) {
            $result['is_error'] = 1;
            $result['error_message'] = "Empty parameters passed. Label, name or column_name is required.";
            return $result;
        }
        if (!is_array($params)) {
            return $customFieldId;
        }
        if (!isset($params['label']) && !isset($params['name']) && !isset($params['column_name'])) {
            return $customFieldId;
        }
        if (isset($params['label'])) {
            $customFieldParams['label'] = (string) $params['label'];
        }
        if (isset($params['name'])) {
            $customFieldParams['name'] = (string) $params['name'];
        }
        try {
            $apiResult = civicrm_api3('CustomField', 'Getsingle', $params);
        }
        catch (CiviCRM_API3_Exception $e) {
            $result['is_error'] = 1;
            $result['error_message'] = "Error with OptionGroup Getsingle API : ".$e->getMessage();
            return $result;
        }
        if (isset($apiResult['id'])) {
            $result['is_error'] = 0;
            $result['id'] = $apiResult['id'];
        } else {
            $result['is_error'] = 1;
            $result['error_message'] = "No id found in result of OptionGroup Getsingle API";
        }
        return $result;
    }
}
