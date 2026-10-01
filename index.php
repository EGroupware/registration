<?php
/**\
	* eGroupWare - Registration                                                       *
	* http://www.egroupware.org                                                       *
	*                                                                                 *
	* This application originally written by Joseph Engo <jengo@phpgroupware.org>     *
	* Funding for this program originally provided by http://www.checkwithmom.com     *
	***********************************************************************************
	* @link http://www.egroupware.org
	* @author Nathan Gray
	* @package registration
	* @copyright (c) 2011 by Nathan Gray
	* @license http://opensource.org/licenses/gpl-license.php GPL - GNU General Public License
	\*

	/* $Id$ */

use EGroupware\Api;

	/**
	 * Check if we allow anon access and create the anonymous session
	 *
	 * The session is created without authentication (no password is stored or needed), but only for an account
	 * carrying the 'anonymous' ACL, see registration_bo::anonymous_account().
	 *
	 * @param array &$anon_account NOT used
	 * @return string|boolean session-id if a session was created, false otherwise
	 */
	function registration_check_anon_access(&$anon_account)
	{
		unset($anon_account);
		if (!($account_id = registration_bo::anonymous_account()))
		{
			return false;
		}
		$session = $GLOBALS['egw']->session;
		if (!($sessionid = $session->create(
			$GLOBALS['egw']->accounts->id2name($account_id).'@'.$GLOBALS['egw_info']['user']['domain'],
			'', 'text', false, false)))
		{
			error_log(__FUNCTION__."() could not create anonymous session for account #$account_id: $session->reason");
			return false;
		}
		return $sessionid;
	}

	// if confirmation id is given, redirect to confirm
	if(!empty($_GET['confirm']))
	{
	   $_GET['menuaction'] = 'registration.registration_ui.confirm';
	}

	$GLOBALS['egw_info']['flags'] = array(
		'noheader'  => True,
		'nonavbar' => True,
		'currentapp' => 'registration',
		'autocreate_session_callback' => 'registration_check_anon_access',
	);
	include('../header.inc.php');

	$app = 'registration';
	if ($_GET['menuaction'])
	{
		list($a,$class,$method) = explode('.',$_GET['menuaction']);
		if ($a && $class && $method)
		{
			$obj = CreateObject($app. '.'. $class);
			if (is_array($obj->public_functions) && $obj->public_functions[$method])
			{
				echo $obj->$method();
				exit();
			}
		}
	}
	ExecMethod('registration.registration_ui.register');
	exit();
