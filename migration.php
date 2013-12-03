<?php

require_once 'migration.civix.php';

/**
 * Implementation of hook_civicrm_config
 */
function migration_civicrm_config(&$config) {
  _migration_civix_civicrm_config($config);
}

/**
 * Implementation of hook_civicrm_xmlMenu
 *
 * @param $files array(string)
 */
function migration_civicrm_xmlMenu(&$files) {
  _migration_civix_civicrm_xmlMenu($files);
}

/**
 * Implementation of hook_civicrm_install
 */
function migration_civicrm_install() {
  return _migration_civix_civicrm_install();
}

/**
 * Implementation of hook_civicrm_uninstall
 */
function migration_civicrm_uninstall() {
  return _migration_civix_civicrm_uninstall();
}

/**
 * Implementation of hook_civicrm_enable
 */
function migration_civicrm_enable() {
  return _migration_civix_civicrm_enable();
}

/**
 * Implementation of hook_civicrm_disable
 */
function migration_civicrm_disable() {
  return _migration_civix_civicrm_disable();
}

/**
 * Implementation of hook_civicrm_upgrade
 *
 * @param $op string, the type of operation being performed; 'check' or 'enqueue'
 * @param $queue CRM_Queue_Queue, (for 'enqueue') the modifiable list of pending up upgrade tasks
 *
 * @return mixed  based on op. for 'check', returns array(boolean) (TRUE if upgrades are pending)
 *                for 'enqueue', returns void
 */
function migration_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _migration_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implementation of hook_civicrm_managed
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 */
function migration_civicrm_managed(&$entities) {
  return _migration_civix_civicrm_managed($entities);
}
/**
 * Implementation of hook civicrm_navigationMenu
 * to create a migration menu and menu items
 * 
 * @author Erik Hommel (erik.hommel@civicoop.org http://www.civicoop.org)
 * @date 2 Dec2013
 * @param array $params
 */
function migration_civicrm_navigationMenu( &$params ) {
    $maxKey = ( max( array_keys($params) ) );
    $params[$maxKey+1] = array (
        'attributes' => array (
            'label'      => 'Migration Menu',
            'name'       => 'Migration Menu',
            'url'        => null,
            'permission' => null,
            'operator'   => null,
            'separator'  => null,
            'parentID'   => null,
            'navID'      => $maxKey+1,
            'active'     => 1
    ),
        'child' =>  array (
            '1' => array (
                'attributes' => array (
                    'label'      => 'Migrate Persons',
                    'name'       => 'Migrate Persons',
                    'url'        => 'civicrm/loadpersons',
                    'operator'   => null,
                    'separator'  => 1,
                    'parentID'   => $maxKey+1,
                    'navID'      => 1,
                    'active'     => 1
                ),
                'child' => null
            ) 
        ) 
    );
}
