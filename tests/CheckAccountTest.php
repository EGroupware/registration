<?php

/**
 * Tests for registration_bo::check_account()'s primary_group handling
 *
 * @link http://www.egroupware.org
 * @package registration
 * @license http://opensource.org/licenses/gpl-license.php GPL - GNU General Public License
 */

require_once __DIR__.'/../../api/tests/LoggedInTest.php';

use EGroupware\Api;

/**
 * registration_bo::check_account() builds the account admin_cmd_create_user will (with
 * skipAdminCheck()) create for a self-registered user, running as a non-admin/anonymous
 * session. account_primary_group is only ever meant to be chosen by the registrant when the
 * admin configured more than one selectable group (registration_ui then shows a <select> of
 * exactly those options) - the server must not trust a submitted value outside that configured
 * set, or a registrant could pick an arbitrary group instead of one the admin actually offered.
 */
class CheckAccountTest extends Api\LoggedInTest
{
	protected $group_a;
	protected $group_b;
	protected $had_orig_primary_group;
	protected $orig_primary_group;

	protected function setUp() : void
	{
		parent::setUp();

		$config = Api\Config::read('registration');
		$this->had_orig_primary_group = array_key_exists('primary_group', $config);
		$this->orig_primary_group = $config['primary_group'] ?? null;

		$this->asAdmin(function()
		{
			$members = array($GLOBALS['egw_info']['user']['account_id']);

			$cmd = new admin_cmd_edit_group(false, array(
				'account_lid' => 'check_account_test_group_a',
				'account_members' => $members,
			));
			$cmd->run();
			$this->group_a = $cmd->account;

			$cmd = new admin_cmd_edit_group(false, array(
				'account_lid' => 'check_account_test_group_b',
				'account_members' => $members,
			));
			$cmd->run();
			$this->group_b = $cmd->account;
		});

		Api\Config::save_value('primary_group', array($this->group_a, $this->group_b), 'registration');
	}

	protected function tearDown() : void
	{
		if ($this->had_orig_primary_group)
		{
			Api\Config::save_value('primary_group', $this->orig_primary_group, 'registration');
		}
		else
		{
			$config = new Api\Config('registration');
			$config->delete_value('primary_group');
			$config->save_repository();
		}

		if ($this->group_a) $GLOBALS['egw']->accounts->delete($this->group_a);
		if ($this->group_b) $GLOBALS['egw']->accounts->delete($this->group_b);

		parent::tearDown();
	}

	/**
	 * A registrant picking one of the admin-configured groups must get exactly that group.
	 */
	public function testPrimaryGroupWithinConfiguredChoicesIsKept()
	{
		$account = array();
		registration_bo::check_account(array(
			'account_lid' => 'check_account_test',
			'n_given'     => 'Check',
			'n_family'    => 'Account',
			'email'       => 'check_account_test@example.org',
			'password'    => 'Some$trongTestPassw0rd!',
			'password2'   => 'Some$trongTestPassw0rd!',
			'primary_group' => $this->group_b,
		), $account);

		$this->assertEquals($this->group_b, $account['account_primary_group']);
	}

	/**
	 * A submitted primary_group that is NOT one of the admin-configured choices must be
	 * replaced with a configured one, never passed through as-is.
	 */
	public function testPrimaryGroupOutsideConfiguredChoicesIsClamped()
	{
		$account = array();
		registration_bo::check_account(array(
			'account_lid' => 'check_account_test',
			'n_given'     => 'Check',
			'n_family'    => 'Account',
			'email'       => 'check_account_test@example.org',
			'password'    => 'Some$trongTestPassw0rd!',
			'password2'   => 'Some$trongTestPassw0rd!',
			'primary_group' => '999999',	// not one of the configured choices
		), $account);

		$this->assertContains($account['account_primary_group'], array($this->group_a, $this->group_b));
	}
}
