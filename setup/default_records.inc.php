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

use EGroupware\Api;

// Install expiry timer
// password of "anonymous" is always a random one (setup::add_account()) and is NOT stored: the registration
// creates its anonymous session without authentication, but only for an account with the 'anonymous' ACL
$anonymous = $GLOBALS['egw_setup']->add_account($anonuser='anonymous','SiteMgr','User','anonymous','NoGroup');
$GLOBALS['egw_setup']->add_acl('phpgwapi', 'anonymous', $anonymous);
$GLOBALS['egw_setup']->add_acl('registration', 'run', $anonymous);
$async = new Api\Asyncservice();
$async->set_timer(array('hour' => '*'),'registration-purge','registration.registration_bo.purge_expired',null, $anonymous);

// Default configuration
$config = array(
	'anonymous_user'	=> $anonuser,
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
