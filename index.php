<?php
	/*********************************************************************************\
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

	/**
	 * Check if we allow anon access and with which creditials
	 *
	 * @param array &$anon_account anon account_info with keys 'login', 'passwd' and optional 'passwd_type'
	 * @return boolean true if we allow anon access, false otherwise
	 */
	function registration_check_anon_access(&$anon_account)
	{
		$config = config::read('registration');
		if ($config['enable_registration'] && $config['anonymous_user'])
		{
			$anon_account = array(
				'login'  => $config['anonymous_user'],
				'passwd' => $config['anonymous_pass'],
				'passwd_type' => 'text',
			);
			return true;
		}
		return false;
	 }

	 // if confirmation id is given, redirect to confirm
	 if(isset($_GET['confirm']) && preg_match('/^[0-9a-f]{32}$/',$_GET['confirm']))
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

	// force idots template set, even if user has eg. jDots, which would redirect to create it's framework
	$GLOBALS['egw_info']['server']['template_set'] = 'idots';
	
	$app = 'registration';
	if ($_GET['menuaction'])
	{
		list($a,$class,$method) = explode('.',$_GET['menuaction']);
		if ($a && $class && $method)
		{
			$obj =& CreateObject($app. '.'. $class);
			if (is_array($obj->public_functions) && $obj->public_functions[$method])
			{
				echo $obj->$method();
				common::egw_exit();
			}
		}
	}
	ExecMethod('registration.registration_ui.register');
	common::egw_exit();
