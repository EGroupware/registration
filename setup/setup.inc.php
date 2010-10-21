<?php
/**
 * EGroupware Registration
 *
 * Funding for this program was provided by http://www.checkwithmom.com
 * 
 * @license http://opensource.org/licenses/gpl-license.php GPL - GNU General Public License
 * @package registration
 * @link http://www.egroupware.org
 * @author Joseph Engo
 * @version $Id$
 */

/* Basic information about this app */
$setup_info['registration']['name']      = 'registration';
$setup_info['registration']['title']     = 'Registration';
$setup_info['registration']['version']   = '1.8';
$setup_info['registration']['app_order'] = '40';
$setup_info['registration']['enable']    = 2;
$setup_info['registration']['license']   = 'GPL';

// RB: never install by default, only via setup
$setup_info['registration']['only_db'] = array('never');

/* The tables this app creates */
$setup_info['registration']['tables']    = array('egw_reg_accounts','egw_reg_fields');

/* The hooks this app includes, needed for hooks registration */
#$setup_info['registration']['hooks'][] = 'admin';
$setup_info['registration']['hooks']['admin'] = 'registration.uireg.all_hooks';
$setup_info['registration']['hooks'][] = 'logout';

/* Dependencies for this app to work */
$setup_info['registration']['depends'][] = array(
	'appname'  => 'phpgwapi',
	'versions' => Array('1.7','1.8','1.9')
);
