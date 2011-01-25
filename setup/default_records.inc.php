<?php
/**
 * Registration - Default records for a new installation
 *
 * @package registration
 * @subpackage setup
 *
 * @author Nathan Gray
 * @version $Id$
 */

// Install expiry timer
$anonymous = $GLOBALS['egw_setup']->add_account($anonuser='anonymous','SiteMgr','User',$anonpasswd='anonymous','NoGroup');
$async = new asyncservice();
$async->set_timer(array('hour' => '*'),'registration-purge','registration.registration_bo.purge_expired',null, $anonymous);

// Default configuration
$config = array(
	'anonymous_user'	=> $anonuser,
	'anonymous_pass'	=> $anonpasswd,
	'accounts_expire'	=> -1,	// Never
	'enable_registration'	=> false,
	'register_link'		=> false,
	'expiry'		=> 2,
);
foreach($config as $name => $value) {
	$GLOBALS['egw_setup']->db->insert($GLOBALS['egw_setup']->config_table,array(
		'config_value' => $value,
		'config_app'   => 'registration',
	),array(
		'config_name'  => $name,
	),__LINE__,__FILE__);
}
