<?php
	/**************************************************************************\
	* phpGroupWare - Registration                                              *
	* http://www.phpgroupware.org                                              *
	* This application written by Joseph Engo <jengo@phpgroupware.org>         *
	* --------------------------------------------                             *
	* Funding for this program was provided by http://www.checkwithmom.com     *
	* --------------------------------------------                             *
	*  This program is free software; you can redistribute it and/or modify it *
	*  under the terms of the GNU General Public License as published by the   *
	*  Free Software Foundation; either version 2 of the License, or (at your  *
	*  option) any later version.                                              *
	\**************************************************************************/

	/* $Id$ */

	$title = $appname;
	$file = Array(
		'Site Configuration'	=> $phpgw->link('/admin/config.php','appname=registration&set_configclass_appname=True'),
		'Manage Fields'	=> $phpgw->link ('/index.php', 'menuaction=registration.uimanagefields.admin')
	);

	display_section($appname,$title,$file);
?>