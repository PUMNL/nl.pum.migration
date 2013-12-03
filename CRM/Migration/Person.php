<?php
/**
 * Person to be migrated to CiviCRM Individual
 *
 * @author Erik Hommel (erik.hommel@civicoop.org)
 */
class CRM_Migration_Person {
    private $sourceTable = NULL;
    private $maritalStatusOptionGroup = NULL;
    private $nationalityOptionGroup = NULL;
    /*
     * function __construct
     */
    function __construct() {
        $this->sourceTable = "pum_conversie_person";
        /*
         * retrieve OptionGroup ID's
         */
        try {
            $optionGroup = civicrm_api3('OptionGroup', 'Getsingle', array('name' => "Marital Status"));
            if (isset($optionGroup['id'])) {
                $this->maritalStatusOptionGroup = $optionGroup['id'];
            } else {
                $this->maritalStatusOptionGroup = 0;
            }
        } catch (CiviCRM_API3_Exception $e) {
            $this->maritalStatusOptionGroup = 0;
        }
        
        try {
            $optionGroup = civicrm_api3('OptionGroup', 'Getsingle', array('name' => "Nationality"));
            if (isset($optionGroup['id'])) {
                $this->nationalityOptionGroup = $optionGroup['id'];
            } else {
                $this->nationalityOptionGroup = 0;
            }
        } catch (CiviCRM_API3_Exception $e) {
            $this->nationalityOptionGroup = 0;
        }
        /*
         * add option values required for marital status and nationality
         */
        $this->generateMaritalStatusOptions();
        $this->generateNationalityOptions();
    }
    /**
     * function to read all source records into DAO
     * 
     * @return $daoSource, object
     */
    function loadSourceDAO() {
        $selectQuery = "SELECT * FROM ".$this->sourceTable;
        $daoSource = CRM_Core_DAO::executeQuery($selectQuery);
        return $daoSource;
    }
    /**
     * function to set the required parameters for the CiviCRM API
     * so it can create an individual
     * 
     * @param $daoSource, dao for source record
     * @return $apiParams, array with params required by CiviCRM Contact Api
     */
    function setApiParams($daoSource) {
        require_once 'CRM/Migration/Utils/PumUtils.php';
        $apiParams = array();
        if (empty($daoSource)) {
            return $apiParams;
        }

        $apiParams['contact_type'] = "Individual";

        if (isset($daoSource->firstname)) {
            $apiParams['first_name'] = (string) $daoSource->firstname;
        }

        if (isset($daoSource->infix)) {
            if (!empty($daoSource->infix)) {
                $apiParams['middle_name'] = (string) $daoSource->infix;
            }
        }

        if (isset($daoSource->surname)) {
            $apiParams['last_name'] = (string) $daoSource->surname;
        }

        if (isset($daoSource->gender)) {
            switch($daoSource->gender) {
                case "F":
                    $apiParams['gender_id'] = 1;
                    break;
                case "M":
                    $apiParams['gender_id'] = 2;
                    break;
                default:
                    $apiParams['gender_id'] = 3;
                    break;
            }
        } else {
            $apiParams['gender_id'] = 3;
        }

        if (isset($daoSource->datebirth)) {
            if (!empty($daoSource->datebirth)) {
                $apiParams['birth_date'] = date("Y-m-d", strtotime($daoSource->datebirth));
            }
        }

        if (isset($daoSource->unid)) {
            $customFieldArray = CRM_Utils_PumUtils::retrieveCustomFieldId(array('label' => "Prins Unique ID"));
            if (!civicrm_error($customFieldArray)) {
                if (isset($customFieldArray['id'])) {
                    $customFieldId = $customFieldArray['id'];
                }
            }
            if ($customFieldId != 0) {
                $apiParams['custom_'.$customFieldId] = (string) $daoSource->unid;
            }
        }

        if (isset($daoSource->passportname)) {
            if (!empty($daoSource->passportname)) {
                $customFieldArray = CRM_Utils_PumUtils::retrieveCustomFieldId(array('label' => "Passport Name"));
                if (!civicrm_error($customFieldArray)) {
                    if (isset($customFieldArray['id'])) {
                        $customFieldId = $customFieldArray['id'];
                    }
                }
                if ($customFieldId != 0) {
                    $apiParams['custom_'.$customFieldId] = (string) $daoSource->passportname;
                }
            }
        }

        if (isset($daoSource->initials)) {
            if (!empty($daoSource->initials)) {
                $customFieldArray = CRM_Utils_PumUtils::retrieveCustomFieldId(array('label' => "Initials"));
                if (!civicrm_error($customFieldArray)) {
                    if (isset($customFieldArray['id'])) {
                        $customFieldId = $customFieldArray['id'];
                    }
                }
                if ($customFieldId != 0) {
                    $apiParams['custom_'.$customFieldId] = (string) $daoSource->initials;
                }
            }
        }

        if (isset($daoSource->citybirth)) {
            if (!empty($daoSource->citybirth)) {
                $customFieldArray = CRM_Utils_PumUtils::retrieveCustomFieldId(array('label' => "City of Birth"));
                if (!civicrm_error($customFieldArray)) {
                    if (isset($customFieldArray['id'])) {
                        $customFieldId = $customFieldArray['id'];
                    }
                }
                if ($customFieldId != 0) {
                    $apiParams['custom_'.$customFieldId] = (string) $daoSource->citybirth;
                }
            }
        }

        if (isset($daoSource->dateregistration)) {
            if (!empty($daoSource->dateregistration)) {
                $customFieldArray = CRM_Utils_PumUtils::retrieveCustomFieldId(array('label' => "Registration Date"));
                if (!civicrm_error($customFieldArray)) {
                    if (isset($customFieldArray['id'])) {
                        $customFieldId = $customFieldArray['id'];
                    }
                }
                if ($customFieldId != 0) {
                    $apiParams['custom_'.$customFieldId] = date("Y-m-d", strtotime($daoSource->dateregistration));
                }
            }
        }

        if (isset($daoSource->nationality)) {
            if (!empty($daoSource->nationality)) {
                $nationalityValue = $this->getOptionValue("Nationality", $daoSource->nationality);
                if (!empty($nationalityValue)) {
                    $customFieldArray = CRM_Utils_PumUtils::retrieveCustomFieldId(array('label' => "Nationality"));
                    if (!civicrm_error($customFieldArray)) {
                        if (isset($customFieldArray['id'])) {
                            $customFieldId = $customFieldArray['id'];
                        }
                    }
                    if ($customFieldId != 0) {
                        $apiParams['custom_'.$customFieldId] = (int) $nationalityValue;
                    }
                }
            }
        }

        if (isset($daoSource->countrybirth)) {
            if (!empty($daoSource->countrybirth)) {
                $countryBirthValue = $this->getCountry($daoSource->countrybirth);
                if (!empty($countryBirthValue)) {
                    $customFieldArray = CRM_Utils_PumUtils::retrieveCustomFieldId(array('label' => "Country of Birth"));
                    if (!civicrm_error($customFieldArray)) {
                        if (isset($customFieldArray['id'])) {
                            $customFieldId = $customFieldArray['id'];
                        }
                    }
                    if ($customFieldId != 0) {
                        $apiParams['custom_'.$customFieldId] = (int) $countryBirthValue;
                    }
                }
            }
        }

        if (isset($daoSource->maritalstatus)) {
            if (!empty($daoSource->maritalstatus)) {
                $maritalStatusValue = $this->getOptionValue("Marital Status", $daoSource->maritalstatus);
                if (!empty($maritalStatusValue)) {
                    $customFieldArray = CRM_Utils_PumUtils::retrieveCustomFieldId(array('label' => "Marital Status"));
                    if (!civicrm_error($customFieldArray)) {
                        if (isset($customFieldArray['id'])) {
                            $customFieldId = $customFieldArray['id'];
                        }
                    }
                    if ($customFieldId != 0) {
                        $apiParams['custom_'.$customFieldId] = (int) $maritalStatusValue;
                    }
                }
            }
        }
        $apiParams['source'] = "Migration ".date("Y-m-d");
        
    return $apiParams;
    }
    /**
     * private function to set the option values for marital status
     * 
     * @author Erik Hommel (erik.hommel@civicoop.org)
     * @date 24 Nov 2013
     */
    private function generateMaritalStatusOptions() {
        /*
         * retrieve latest added value for option group with default 0
         */
        $latestValue = 0;
        $maxValue = "SELECT MAX(value) AS max FROM civicrm_option_value WHERE option_group_id = ".$this->maritalStatusOptionGroup;
        $daoMax = CRM_Core_DAO::executeQuery($maxValue);
        if ($daoMax->fetch()) {
            $latestValue = $daoMax->max + 1;
        }
        /*
         * add values
         */
        if ($this->maritalStatusOptionGroup != 0) {
            $selectDistinct = "SELECT DISTINCT(maritalstatus) FROM ".$this->sourceTable;
            $daoDistinct = CRM_Core_DAO::executeQuery($selectDistinct);
            while ($daoDistinct->fetch()) {
                if (!empty($daoDistinct->maritalstatus)) {
                    /*
                     * check if option value does not exist already
                     */
                    $optionValueExists = FALSE;
                    try {
                        $countOptionValues = civicrm_api3('OptionValue', 'Getcount', array('label' => $daoDistinct->maritalstatus));
                        if ($countOptionValues > 0) {
                            $optionValueExists = TRUE;
                        }
                    } catch (CiviCRM_API3_Exception $e) {
                    }
                    if ($optionValueExists == FALSE) {
                        $optionParams = array(
                            'option_group_id'   =>  $this->maritalStatusOptionGroup,
                            'name'              =>  $daoDistinct->maritalstatus,
                            'label'             =>  $daoDistinct->maritalstatus,
                            'value'             =>  $latestValue,
                            'is_active'         =>  1
                        );
                        try {
                            civicrm_api3('OptionValue', 'Create', $optionParams);
                            $latestValue++;
                        } catch (CiviCRM_API3_Exception $e) {
                        }
                    }
                }
            }
        }       
    }
    /**
     * private function to set the option values for nationality
     * 
     * @author Erik Hommel (erik.hommel@civicoop.org)
     * @date 24 Nov 2013
     */
    private function generateNationalityOptions() {
        /*
         * retrieve latest added value for option group with default 0
         */
        $latestValue = 0;
        $maxValue = "SELECT MAX(value) AS max FROM civicrm_option_value WHERE option_group_id = ".$this->nationalityOptionGroup;
        $daoMax = CRM_Core_DAO::executeQuery($maxValue);
        if ($daoMax->fetch()) {
            $latestValue = $daoMax->max + 1;
        }
        /*
         * add values
         */
        if ($this->nationalityOptionGroup != 0) {
            $selectDistinct = "SELECT DISTINCT(nationality) FROM ".$this->sourceTable;
            $daoDistinct = CRM_Core_DAO::executeQuery($selectDistinct);
            while ($daoDistinct->fetch()) {
                if (!empty($daoDistinct->nationality)) {
                    /*
                     * check if option value does not exist already
                     */
                    $optionValueExists = FALSE;
                    try {
                        $countOptionValues = civicrm_api3('OptionValue', 'Getcount', array('label' => $daoDistinct->nationality));
                        if ($countOptionValues > 0) {
                            $optionValueExists = TRUE;
                        }
                    } catch (CiviCRM_API3_Exception $e) {
                    }
                    if ($optionValueExists == FALSE) {
                        $optionParams = array(
                            'option_group_id'   =>  $this->nationalityOptionGroup,
                            'name'              =>  $daoDistinct->nationality,
                            'label'             =>  $daoDistinct->nationality,
                            'value'             =>  $latestValue,
                            'is_active'         =>  1
                        );
                        try {
                            civicrm_api3('OptionValue', 'Create', $optionParams);
                            $latestValue++;
                        } catch (CiviCRM_API3_Exception $e) {
                        }
                    }
                }
            }
        }       
    }
    /**
     * private function to retrieve the option value from a source value
     *      
     * @author Erik Hommel (erik.hommel@civicoop.org)
     * @date 24 Nov 2013
     * @param $optionType (type of option), $sourceValue (the value from the data source)
     * @return $returnValue, the value of the OptionValue retrieved
     */
    private function getOptionValue($optionType, $sourceValue) {
        if (empty($optionType) || empty($sourceValue)) {
            $returnValue = 0;
            return $returnValue;
        }
        /*
         * retrieve option group id
         */
        switch($optionType) {
            case "Marital Status":
                $optionGroupTitle = "Marital Status";
                break;
            case "Nationality":
                $optionGroupTitle = "Nationality";
                break;
        }
        try {
            $optionGroup = civicrm_api3('OptionGroup', 'Getsingle', array('title'=> $optionGroupTitle));
            if (isset($optionGroup['id'])) {
                /*
                 * retrieve option value using the source value
                 */
                try {
                    $apiOptionValueParams = array(
                        'option_group_id'   =>  $optionGroup['id'],
                        'label'             =>  $sourceValue
                    );
                    $apiOptionValue = civicrm_api3('OptionValue', 'Getsingle', $apiOptionValueParams );
                    if (isset($apiOptionValue['value'])) {
                        $returnValue = $apiOptionValue['value'];
                    }
                } catch (CiviCRM_API3_Exception $e) {
                    $returnValue = 0;
                }
            }   
        } catch (CiviCRM_API3_Exception $e) {
            $returnValue = 0;
        }
        return $returnValue;
    }    
    /**
     * private function to retrieve the CiviCRM country for a source value
     *      
     * @author Erik Hommel (erik.hommel@civicoop.org)
     * @date 24 Nov 2013
     * @param $sourceCountry, name of the country from PUM source system
     * @return $countryId, id of the country in CiviCRM
     */
    private function getCountry($sourceCountry) {
        require_once 'CRM/Utils/Array.php';
        /*
         * exceptions where country name from source can not be used in array
         */
        $countryExceptions["Bosnia and Herzegowina"] = "Bosnia and Herzegovina";
        $countryExceptions["Vietnam"] = "Vietnam";
        $countryExceptions["Palestine"] = "Palestinian Territory, Occupied";
        $countryExceptions["Democratic Republic of Congo"] = "Congo, The Democratic Republic of the";
        $countryExceptions["Tanzania"] = "Tanzania, United Republic of";
        /*
         * check if sourceCountry is in exceptions and if so, use
         * civi value
         */
        if (array_key_exists($sourceCountry, $countryExceptions)) {
            $sourceCountry = CRM_Utils_Array::value($sourceCountry, $countryExceptions);
        }
        /*
         * retrieve id from api
         */
        try {
            $apiCountry = civicrm_api3('Country', 'Getsingle', array('name' => $sourceCountry));
            if (isset($apiCountry['id'])) {
                $countryId = $apiCountry['id'];
            } else {
                $countryId = 0;
            }
        } catch (CiviCRM_API3_Exception $e) {
            $countryId = 0;
        }
        return $countryId;
    }    
}
