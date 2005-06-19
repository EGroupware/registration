<?php
	/**************************************************************************\
	* eGroupWare - Registration                                                *
	* http://www.egroupware.org                                                *
	* This application written by Joseph Engo <jengo@phpgroupware.org>         *
	* This application is havily modified by Pim Snel <pim@egroupware.org>     *
	* --------------------------------------------                             *
	* Funding for this program was provided by http://www.checkwithmom.com     *
	* Funding for this program was provided by http://www.lingewoud.nl         *
	* --------------------------------------------                             *
	*  This program is free software; you can redistribute it and/or modify it *
	*  under the terms of the GNU General Public License as published by the   *
	*  Free Software Foundation; version 2 of the License                      *
	\**************************************************************************/

	/* $Id$ */

	/*
	** This program is non-standard, we will create and manage our sessions manually.
	** We don't want users to be kicked out half way through, and we really don't need a true
	** session for it.
	*/


	// use other lang
	/*!
	@function lang
	@abstract function to deal with multilanguage support
	*/
	function lang($key, $m1='', $m2='', $m3='', $m4='', $m5='', $m6='', $m7='', $m8='', $m9='', $m10='') 
	{
		$vars  = array($m1,$m2,$m3,$m4,$m5,$m6,$m7,$m8,$m9,$m10);
		$value = $GLOBALS['egw']->translation->translate($key,$vars);
		return $value;
	}

	error_reporting(E_ALL & ~E_NOTICE);

	$GLOBALS['sessionid'] = @$_GET['sessionid'] ? $_GET['sessionid'] : @$_COOKIE['sessionid'];

	// Note: This is current not a drop in install, it requires some manual installation
	//       Take a look at the README file
	$domain       = 'default'; // move to ??  but how ???
	$template_set = 'idots'; // move to config but how ???
	$default_lang = 'en'; // move to config    but how, we can't get langs in the default config!!!


	if (@$_GET['menuaction'])
	{
		list($app,$class,$method) = explode('.',$_GET['menuaction']);
		if (! $app || ! $class || ! $method)
		{
			$invaild_data = True;
		}
	}
	else
	{
		$app = 'registration';
		$invaild_data = True;
	}

	$GLOBALS['egw_info']['flags'] = array(
		'noheader'   => True,
		'nonavbar'   => True,
		'noapi'      => True,
		'currentapp' => $app
	);
	include('../header.inc.php');
	include(EGW_INCLUDE_ROOT.'/phpgwapi/inc/common_functions.inc.php');


	
	$GLOBALS['egw_info']['server'] = $GLOBALS['egw_domain'][$domain];
	$GLOBALS['egw']                =& CreateObject('phpgwapi.egw');
	$GLOBALS['egw']->db            =& CreateObject('phpgwapi.egw_db');
	$GLOBALS['egw']->db->Host      = $GLOBALS['egw_info']['server']['db_host'];
	$GLOBALS['egw']->db->Type      = $GLOBALS['egw_info']['server']['db_type'];
	$GLOBALS['egw']->db->Database  = $GLOBALS['egw_info']['server']['db_name'];
	$GLOBALS['egw']->db->User      = $GLOBALS['egw_info']['server']['db_user'];
	$GLOBALS['egw']->db->Password  = $GLOBALS['egw_info']['server']['db_pass'];

	// Fill phpgw_info["server"] array 
	$GLOBALS['egw']->db->query("select * from phpgw_config WHERE config_app='phpgwapi'",__LINE__,__FILE__);
	while ($GLOBALS['egw']->db->next_record())
	{
		$GLOBALS['egw_info']['server'][$GLOBALS['egw']->db->f('config_name')] = stripslashes($GLOBALS['egw']->db->f('config_value'));
	}
	$GLOBALS['egw_info']['server']['template_set'] = $template_set;

	$GLOBALS['egw']->common        =& CreateObject('phpgwapi.common');
	$GLOBALS['egw']->auth          =& CreateObject('phpgwapi.auth');
	$GLOBALS['egw']->accounts      =& CreateObject('phpgwapi.accounts');
	$GLOBALS['egw']->acl           =& CreateObject('phpgwapi.acl');
	$GLOBALS['egw']->preferences   =& CreateObject('phpgwapi.preferences');
	$GLOBALS['egw']->applications  =& CreateObject('phpgwapi.applications');
	$GLOBALS['egw']->hooks         =& CreateObject('phpgwapi.hooks');
	$GLOBALS['egw']->session       =& CreateObject('phpgwapi.sessions');

	$GLOBALS['egw']->common->key  = md5($GLOBALS['kp3'] . $GLOBALS['sessionid'] . $GLOBALS['egw_info']['server']['encryptkey']);
	$GLOBALS['egw']->common->iv   = $GLOBALS['egw_info']['server']['mcrypt_iv'];

	$cryptovars[0] = $GLOBALS['egw']->common->key;
	$cryptovars[1] = $GLOBALS['egw']->common->iv;
	$GLOBALS['egw']->crypto =& CreateObject('phpgwapi.crypto', $cryptovars);

	define('EGW_APP_ROOT', $GLOBALS['egw']->common->get_app_dir());
	define('EGW_APP_INC', $GLOBALS['egw']->common->get_inc_dir());
	define('EGW_APP_TPL', $GLOBALS['egw']->common->get_tpl_dir());
	define('EGW_IMAGES', $GLOBALS['egw']->common->get_image_path());
	define('EGW_IMAGES_DIR', $GLOBALS['egw']->common->get_image_dir());
	define('PHPGW_APP_ROOT', $GLOBALS['egw']->common->get_app_dir());
	define('PHPGW_APP_INC', $GLOBALS['egw']->common->get_inc_dir());
	define('PHPGW_APP_TPL', $GLOBALS['egw']->common->get_tpl_dir());
	define('PHPGW_IMAGES', $GLOBALS['egw']->common->get_image_path());
	define('PHPGW_IMAGES_DIR', $GLOBALS['egw']->common->get_image_dir());

	$GLOBALS['egw']->template      =& CreateObject('phpgwapi.Template',PHPGW_APP_TPL);
	$GLOBALS['egw']->translation   =& CreateObject('phpgwapi.translation');

	//$GLOBALS['egw']->translation->userlang=$default_lang;

	$c =& CreateObject('phpgwapi.config','registration');
	$c->read_repository();
	
	$config = $c->config_data;


	if (! $sessionid)
	{
		$sessionid = $GLOBALS['egw']->session->create($config['anonymous_user'] . '@' . $domain,$config['anonymous_pass'],'text');
	}
	else
	{
		if (! $GLOBALS['egw']->session->verify())
		{
			// Lets hope this works
			$sessionid = $GLOBALS['egw']->session->create($config['anonymous_user'] . '@' . $domain,$config['anonymous_pass'],'text');
		}
	}

	if ($app && $class)
	{
		$obj =& CreateObject(sprintf('%s.%s',$app,$class));
		if ((is_array($obj->public_functions) && $obj->public_functions[$method]) && ! $invalid_data)
		{
			$obj->$method();
		}
	}
	else
	{
		$_obj =& CreateObject('registration.uireg');
		$_obj->step1();
	}
	

