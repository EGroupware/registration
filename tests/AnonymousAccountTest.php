<?php

/**
 * Tests for registration_bo::anonymous_account()
 *
 * The registration creates its anonymous session WITHOUT a password (the stored "anonymous_pass" is gone), so it
 * must only ever do so for an account carrying the 'anonymous' ACL, never for a real user like an admin.
 *
 * @link http://www.egroupware.org
 * @package registration
 * @license http://opensource.org/licenses/gpl-license.php GPL - GNU General Public License
 */

require_once __DIR__.'/../../api/tests/LoggedInTest.php';

use EGroupware\Api;

class AnonymousAccountTest extends Api\LoggedInTest
{
	/**
	 * @var int[] account_id of the anonymous test account and of a group
	 */
	protected $anon_id;

	protected function setUp() : void
	{
		parent::setUp();

		$this->anon_id = $GLOBALS['egw']->accounts->name2id('anonymous', 'account_lid', 'u');
		if (!$this->anon_id || !$GLOBALS['egw']->acl->get_specific_rights_for_account($this->anon_id, 'anonymous', 'phpgwapi'))
		{
			$this->markTestSkipped('Test install has no anonymous account with the anonymous ACL');
		}
	}

	/**
	 * Pass criteria: the flagged anonymous account is returned as int account_id
	 */
	public function testAnonymousAccountWithAnonymousAcl()
	{
		$this->assertSame((int)$this->anon_id, registration_bo::anonymous_account(
			['enable_registration' => '1', 'anonymous_user' => 'anonymous']));
	}

	/**
	 * Pass criteria: a regular user (here the one running the test) is refused, as it has no 'anonymous' ACL
	 */
	public function testRegularUserIsRefused()
	{
		$this->assertFalse(registration_bo::anonymous_account(
			['enable_registration' => '1', 'anonymous_user' => $GLOBALS['egw_info']['user']['account_lid']]));
	}

	/**
	 * Pass criteria: no session account while registration is disabled, nothing configured, unknown user or a group
	 */
	public function testDisabledOrUnusableConfigIsRefused()
	{
		$this->assertFalse(registration_bo::anonymous_account(['enable_registration' => '', 'anonymous_user' => 'anonymous']));
		$this->assertFalse(registration_bo::anonymous_account(['enable_registration' => '1', 'anonymous_user' => '']));
		$this->assertFalse(registration_bo::anonymous_account(['enable_registration' => '1']));
		$this->assertFalse(registration_bo::anonymous_account(['enable_registration' => '1', 'anonymous_user' => 'no-such-user-'.uniqid()]));
		$this->assertFalse(registration_bo::anonymous_account(['enable_registration' => '1', 'anonymous_user' => 'Default']));
	}
}
