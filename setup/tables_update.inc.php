<?php
/**
 * EGroupware Registration
 *
 * @license http://opensource.org/licenses/gpl-license.php GPL - GNU General Public License
 * @package registration
 * @link http://www.egroupware.org
 */

function registration_upgrade0_8_1()
{
	global $setup_info, $phpgw_setup;

	$phpgw_setup->oProc->CreateTable('phpgw_reg_fields', array(
		'fd' => array(
			'field_name' => array('type' => 'varchar', 'precision' => 255,'nullable' => False),
			'field_text' => array('type' => 'text','nullable' => False),
			'field_type' => array('type' => 'varchar', 'precision' => 255,'nullable' => True),
			'field_values' => array('type' => 'text','nullable' => True),
			'field_required' => array('type' => 'char', 'precision' => 1,'nullable' => True),
			'field_order' => array('type' => 'int', 'precision' => 4,'nullable' => True)
		),
		'pk' => array(),
		'ix' => array(),
		'fk' => array(),
		'uc' => array()
	));

	return $setup_info['registration']['currentver'] = '0.8.2';
}


function registration_upgrade0_8_2()
{
	return $setup_info['registration']['currentver'] = '1.0.1';
}


function registration_upgrade1_0_0()
{
	return $setup_info['registration']['currentver'] = '1.0.1';
}


function registration_upgrade1_0_1()
{
	$GLOBALS['egw_setup']->oProc->RenameTable('phpgw_reg_fields','egw_reg_fields');
	$GLOBALS['egw_setup']->oProc->RenameTable('phpgw_reg_accounts','egw_reg_accounts');

	return $GLOBALS['setup_info']['registration']['currentver'] = '1.2';
}


function registration_upgrade1_2()
{
	$GLOBALS['egw_setup']->oProc->AddColumn('egw_reg_accounts','reg_status',array(
		'type' => 'varchar',
		'precision' => '1',
		'nullable' => False,
		'default' => 'x'
	));

	return $GLOBALS['setup_info']['registration']['currentver'] = '1.3.001';
}


function registration_upgrade1_3_001()
{
	return $GLOBALS['setup_info']['registration']['currentver'] = '1.4';
}


function registration_upgrade1_4()
{
	return $GLOBALS['setup_info']['registration']['currentver'] = '1.8';
}


function registration_upgrade1_8()
{
	$GLOBALS['egw_setup']->oProc->CreateTable('egw_registration',array(
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
		'fk' => array('contact_id' => 'egw_addressbook.contact_id'),
		'ix' => array(),
		'uc' => array()
	));

	$GLOBALS['egw_setup']->oProc->DropTable('egw_reg_accounts');
	$GLOBALS['egw_setup']->oProc->DropTable('egw_reg_fields');
	return $GLOBALS['setup_info']['registration']['currentver'] = '1.9.001';
}


function registration_upgrade1_9_001()
{
	return $GLOBALS['setup_info']['registration']['currentver'] = '14.1';
}


function registration_upgrade14_1()
{
	return $GLOBALS['setup_info']['registration']['currentver'] = '16.1';
}


function registration_upgrade16_1()
{
	return $GLOBALS['setup_info']['registration']['currentver'] = '17.1';
}

/**
 * Bump version to 19.1
 *
 * @return string
 */
function registration_upgrade17_1()
{
	return $GLOBALS['setup_info']['registration']['currentver'] = '19.1';
}

function registration_upgrade19_1()
{
	$GLOBALS['egw_setup']->oProc->AddColumn('egw_registration','primary_group',array(
		'type' => 'int',
		'precision' => '4'
	));

	return $GLOBALS['setup_info']['registration']['currentver'] = '19.1.001';
}

/**
 * Bump version to 20.1
 *
 * @return string
 */
function registration_upgrade19_1_001()
{
	return $GLOBALS['setup_info']['registration']['currentver'] = '20.1';
}

/**
 * Bump version to 21.1
 *
 * @return string
 */
function registration_upgrade20_1()
{
	return $GLOBALS['setup_info']['registration']['currentver'] = '21.1';
}

/**
 * Bump version to 23.1
 *
 * @return string
 */
function registration_upgrade21_1()
{
	return $GLOBALS['setup_info']['registration']['currentver'] = '23.1';
}