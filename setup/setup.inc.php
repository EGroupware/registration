<?php
/**
 * EGroupware Registration
 *
 * @license http://opensource.org/licenses/gpl-license.php GPL - GNU General Public License
 * @package registration
 * @link http://www.egroupware.org
 * @author Nathan Gray
 * @version $Id$
 */

/* Basic information about this app */
$setup_info['registration']['name']      = 'registration';
$setup_info['registration']['title']     = 'Registration';
$setup_info['registration']['version']   = '1.9.001';
$setup_info['registration']['app_order'] = '40';
$setup_info['registration']['enable']    = 2;
$setup_info['registration']['license']   = 'GPL';

/* The tables this app creates */
$setup_info['registration']['tables']    = array('egw_registration');

/* The hooks this app includes, needed for hooks registration */
$setup_info['registration']['hooks']['admin'] = 'registration_hooks::all_hooks';
$setup_info['registration']['hooks']['search_link'] = 'registration_hooks::search_link';

/* Dependencies for this app to work */
$setup_info['registration']['depends'][] = array(
	'appname'  => 'phpgwapi',
	'versions' => Array('1.9')
);

