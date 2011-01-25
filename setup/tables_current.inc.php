<?php
  /**************************************************************************\
  * eGroupWare - Setup                                                       *
  * http://www.egroupware.org                                                *
  * --------------------------------------------                             *
  *  This program is free software; you can redistribute it and/or modify it *
  *  under the terms of the GNU General Public License as published by the   *
  *  Free Software Foundation; either version 2 of the License, or (at your  *
  *  option) any later version.                                              *
  \**************************************************************************/

  /**************************************************************************\
  * This file should be generated for you. It should never be edited by hand *
  \**************************************************************************/

  /* $Id$ */

  // table array for registration
$phpgw_baseline = array(
	'egw_registration' => array(
		'fd' => array(
			'reg_id' => array('type' => 'auto','nullable' => False),
			'contact_id' => array('type' => 'int','precision' => '4','nullable' => False),
			'status' => array('type' => 'varchar','precision' => '1','nullable' => False,'default' => '0'),
			'ip' => array('type' => 'varchar','precision' => '20'),
			'timestamp' => array('type' => 'timestamp'),
			'register_code' => array('type' => 'varchar','precision' => '40'),
			'post_confirm_hook' => array('type' => 'varchar','precision' => '255'),
			'sitemgr_version' => array('type' => 'int','precision' => '4','comment' => 'Key for the sitemgr block info'),
			'account_lid' => array('type' => 'varchar','precision' => '64'),
			'password' => array('type' => 'varchar','precision' => '100')
		),
		'pk' => array('reg_id'),
		'fk' => array('contact_id' => 'egw_addressbook.contact_id','sitemgr_version' => 'egw_sitemgr_content.version_id'),
		'ix' => array(),
		'uc' => array()
	)
);
?>
