<?php
/**
 * Registration - User interface object
 *
 * @link http://www.egroupware.org
 * @author Nathan Gray
 * @package registration
 * @copyright (c) 2011 by Nathan Gray
 * @license http://opensource.org/licenses/gpl-license.php GPL - GNU General Public License
 * @version $Id$
 */

use EGroupware\Api;
use EGroupware\Api\Egw;
use EGroupware\Api\Acl;
use EGroupware\Api\Etemplate;

/**
 * User interface for Registration
 *
 * The user interface for the sitemgr module is in registration_sitemgr.
 * This file is for the normal eGW bits.
 */
class registration_ui
{

	// Directly accessable functions (via URL)
	public $public_functions = array(
		'index'	=>	true,
		'view'	=>	true,
		'register'	=> true,
		'lost_password'	=> true,
		'lost_username'	=> true,
		'config'	=> true,
		'confirm'	=> true,
	);

	protected $expiry = 2; // hours

	public function __construct()
	{
		$config = Api\Config::read('registration');
		$this->expiry = $config['expiry'];
		if($_GET['lang_code'])
		{
			$GLOBALS['egw_info']['user']['preferences']['common']['lang'] = substr($_GET['lang_code'],0,2);
		}
		Api\Translation::init();
	}

	public function index()
	{
	}

	/**
	 * View registration details (when link from contact is clicked)
	 *
	 * @param content
	 */
	public function view($content = array())
	{
		$reg_id = ($_GET['reg_id'] && preg_match('/^[0-9]+$/',$_GET['reg_id'])) ? $_GET['reg_id'] : $content['reg_id'];

		if(!$reg_id) return lang('Missing registration');

		$registration = registration_bo::read($reg_id);

		if($content && $registration['status'] == registration_bo::PENDING && $GLOBALS['egw_info']['user']['apps']['admin'])
		{
			if($content['cancel'])
			{
				// Cancel pending registration
				$addressbook = new Api\Contacts();
				if(!$addressbook->delete($registration['contact_id'])) {
					$msg = lang('%1 needs permission to delete - address remains.', Api\Accounts::username($addressbook->user));
				}
				registration_bo::delete($registration['reg_id']);
				$registration = registration_bo::read($reg_id);
				$msg .= lang('Canceled');
			}
			if($content['register'])
			{
				// Push through pending registration
				$registration = registration_bo::confirm($registration['register_code']);
				$msg .= lang('Registered');
			}
		}

		switch ($registration['status'])
		{
			case registration_bo::PENDING:
				$registration['timestamp_label'] = lang('Expires');
				break;
			case registration_bo::CONFIRMED:
				$registration['timestamp_label'] = lang('Registered');
				break;
		}

		$registration['links'] = array(
			'to_app' => 'registration',
			'to_id' => $reg_id
		);

		$sel_options['status'] = registration_bo::$status_list;

		$registration['no_actions'] = !$GLOBALS['egw_info']['user']['apps']['admin'] || $registration['status'] != registration_bo::PENDING;
		if(!$registration['no_actions'])
		{
			// Check ACL on target addressbooks
			$addressbook = new Api\Contacts();
			if(!in_array($registration['owner'], array_keys($addressbook->get_addressbooks(Acl::DELETE))))
			{
				$msg .= lang('You don\'t have delete permission, address will be left if you manually register or cancel.');
			}
		}

		$registration['msg'] = $msg;
		$preserv['reg_id'] = $reg_id;

		$GLOBALS['egw_info']['flags']['app_header'] = lang('registration');
		$template = new Etemplate('registration.view');
		$template->exec('registration.registration_ui.view', $registration,$sel_options,[],$preserv);
	}

	/**
	 * Register for a user account
	 *
	 * This is a mirror of the sitemgr module registering an account, available outside of sitemgr.
	 */
	public static function register($content = array())
	{
		// Fields to show
		$data['show'] = array(
			'account_lid'	=> true,
			'n_fn'		=> true,
			'password'	=> true,
			'email'		=> true
		);
		$config = Api\Config::read('registration');
		$template = new Etemplate('registration.registration_form');

		if($config['show'])
		{
			if (!is_array($fields = $config['show'])) $fields = explode(',', $fields);
			$data['show'] += array_combine($fields, array_fill(0,count($fields),true));
			$cfs = [];
			foreach($data['show'] as $field => $show)
			{
				if($field[0] == '#')
				{
					$cfs[substr($field, 1)] = true;
				}
			}
			if(count($cfs))
			{
				$data['show']['custom'] = true;
				$data['show']['sep3'] = true;
				$data['show']['sep4'] = true;
				$template->setElementAttribute("custom", "fields", $cfs);
			}
		}

		// what to use as account_lid, default ask user
		switch ($config['username'])
		{
			case 'email':
				$data['show']['account_email'] = true;
				unset($data['show']['email'], $data['show']['account_lid']);
				break;
		}
		// did user need to choose a primary group
		$primary_groups = is_array($config['primary_group']) ? $config['primary_group'] : explode(',', $config['primary_group']);
		if ($primary_groups > 1)
		{
			$data['show']['primary_group'] = true;
			$sel_options['primary_group'] = [];
			foreach($primary_groups as $group)
			{
				$sel_options['primary_group'][$group] = Api\Accounts::id2name($group);	// id2name to not show Group prefix
			}
		}

		$data += $content;
		if($content['submitit'])
		{
			if (isset($data['show']['captcha']) &&
				((isset($content['captcha_result']) && $content['captcha'] != $content['captcha_result']) || // no correct captcha OR
					(time() - $content['start_time'] < 10 &&                                // bot indicator (less then 10 sec to fill out the form and
					!$GLOBALS['egw_info']['etemplate']['java_script'])))     // javascript disabled)
			{
				$captcha_fail = true;
				$template->set_validation_error('captcha',lang('Wrong - try again ...'));
				unset($content['captcha']);
			}

			if(!$captcha_fail)
			{
				// what to use as account_lid, default ask user
				switch($config['username'])
				{
					case 'email':
						$content['account_lid'] = $content['email'] = $content['account_email'];
						break;
				}
				// if user did NOT has to choose a primary group, use the once from config
				if ($primary_groups <= 1 || empty($content['primary_group']))
				{
					$content['primary_group'] = $primary_groups[0];
				}
				$config['register_for'] = 'account';
				// Check for account info
				try {
					registration_bo::check_account($content);
					$contact = new Api\Contacts();
					if ($config['pending_addressbook'])   // save the contact in the addressbook
					{
						$content['owner'] = $config['pending_addressbook'];
						$content['private'] = 0;        // in case default_private is set
						$content = array_merge($content, $content['custom'] ?? []);

						// Set timestap to expiry, so we don't have to save both
						$content['timestamp'] = time() + ($config['expiry'] * 3600);
						try
						{
							$reg_id = registration_bo::save($content);
						}
						catch (Exception $e)
						{
							$msg = $e->getMessage();
							$reg_id = false;
						}

						if($reg_id)
						{
							$registration = registration_bo::read($reg_id);
							if ($registration['contact_id'])
							{
								$config['title'] = lang('account registration');
								// If this is turned on, admin will manually process registrations
								if(!$config['no_email'])
								{
									// Send out confirmation link
									$msg = registration_bo::send_confirmation($config, $registration);
								}
							}
							$account_ok = true;
						}
					}
				}
				catch (Api\Exception $e)
				{
					$msg = $e->getMessage();
					$account_ok = false;
				}
			}
		}
		if($msg)
		{
			$data['message'] = $msg;
		}

		// a simple calculation captcha
		if (in_array('captcha',$data['show']))
		{
			$num1 = rand(1,99);
			$num2 = rand(1,99);
			if ($num2 > $num1)      // keep the result positive
			{
				$n = $num1; $num1 = $num2; $num2 = $n;
			}
			$data['captcha_task'] = sprintf('%d - %d =',$num1,$num2);
			$preserv['captcha_result'] = $num1-$num2;
		}
		$preserve['start_time'] = time();

		if($account_ok)
		{
			$readonlys['__ALL__'] = true;
			Api\Framework::message($msg ? $msg : 'Registration pending');
		}

		Api\Translation::add_app('addressbook');
		if($data['show']['adr_one_locality'])
		{
			$bo_addressbook = new Api\Contacts();
			$data['show']['adr_one_locality']  = $bo_addressbook->addr_format_by_country($data['adr_one_countryname']);
		}

		// Display form
		$GLOBALS['egw_info']['flags'] = array(
			'noheader'  => True,
			'nonavbar' => True,
			'app_header' => lang('account registration'),
			'currentapp' => 'registration'
		);
		$template->exec('registration.registration_ui.register', $data,$sel_options,$readonlys,$preserv);
		Api\Framework::includeCSS("/pixelegg/css/pixelegg.css");
	}

	/**
	 * Send lost user ID(s)
	 */
	public function lost_username($content = array())
	{

		$data = array();
		if ($content['gologin'])
		{
			return Egw::redirect_link('/login.php');
		}
		// Deal with incoming
		if($content && $content['email'])
		{
			// Find usernames
			$query = array(
				'type'		=> 'accounts',
				'query_type'	=> 'email',
				'query'		=> $content['email']
			);
			$accounts = $GLOBALS['egw']->accounts->search($query);
			if($accounts)
			{
				// Build list
				$account_list = array();
				foreach($accounts as $id => $account)
				{
					$account_list[] = $account['account_lid'];
				}
				// Don't need the confirmation, just send the email and discard
				$info = array(
					'contact_id'	=> $account['id'],
					'email'		=> $content['email'],
					'timestamp'	=> time(),
					'n_fileas'	=> $account['n_fileas']
				);
				$arguments = array(
					'subject'=> lang('Lost user ID'),
					'message'=> lang('lost_user_id_message username: %1', implode("\n", $account_list))
				);
				$data['message'] = registration_bo::send_confirmation($arguments, $info);
				$readonlys['submit'] = true;
				$readonlys['email'] = true;
			}
			else
			{
				// Unknown email, but don't tell them that
				$data['message'] = lang('we have sent a mail to your email account: %1 with your lost user ids.	', $content['email']);
			}
		}

		// Display form
		$GLOBALS['egw_info']['flags'] = array(
			'noheader'  => True,
			'nonavbar' => True,
			'app_header' => lang('Lost user ID'),
			'currentapp' => 'registration'
		);
		$template = new Etemplate('registration.lost_username');
		$template->exec('registration.registration_ui.lost_username', $data,$sel_options,$readonlys);
	}

	/**
	 * Reset lost password
	 */
	public function lost_password($content = array())
	{

		$data = $content;
		// Deal with incoming
		if($content['wait'] && $content['wait'] > time())
		{
			$data['message'] = lang('Abuse reduction - please wait %1 seconds and try again.', $content['wait'] - time());
			$data['username'] = $content['username'];
			unset($content['username']);
		}
		if ($content['gologin'])
		{
			return Egw::redirect_link('/login.php');
		}
		if($content && $content['username'])
		{
			// Find username
			$account_id = $GLOBALS['egw']->accounts->name2id($content['username']);
			if($account_id)
			{
				$account = $GLOBALS['egw']->accounts->read($account_id);
				$info = array(
					'contact_id'	=> $account['person_id'],
					'email'		=> $account['account_email'],
					'timestamp'	=> time() + $this->expiry * 3600,
					'post_confirm_hook' => 'registration.registration_ui.change_password'
				);
				$reg_id = registration_bo::save($info, false);
				$info += registration_bo::read($reg_id);
				$arguments = array(
					'link'	=> '/registration/',
					'title'	=> lang('Lost Password'),
					'expiry'=> $this->expiry
				);
				$data['message'] = registration_bo::send_confirmation($arguments, $info);
				$readonlys['submit'] = true;
				$readonlys['username'] = true;
			}
			else
			{
				// Invalid username, but give the same message to avoid telling
				// them that
				$data['message'] = lang('we have sent a mail with instructions to change your password. you should follow the included link within two hours. if you do not, you will have to go to the lost password screen again.');
				// Start a timer to prevent excessive use
				$preserv['wait'] = time() + 10; // wait 10s
			}
		}

		// Display form
		$GLOBALS['egw_info']['flags'] = array(
			'noheader'  => True,
			'nonavbar' => True,
			'app_header' => lang('Lost password'),
			'currentapp' => 'registration'
		);

		$template = new Etemplate('registration.lost_password');
		$template->exec('registration.registration_ui.lost_password', $data,$sel_options,$readonlys,$preserv);
	}

	// Callback after clicking lost password confirm link
	public function change_password($content)
	{
		$preserv = $content;
		$data = array();

		// Make sure registration is valid - skip when being called from purge
		if($content['reg_id'] && $content['status'] != registration_bo::CONFIRMED)
		{
			return;
		}
		if($content['password'] && $content['password'] != $content['password2'])
		{
			unset($content['submit']);
			$data['message'] = lang('The two passwords are not the same');
		}
		if($content['submit'])
		{
			// Get account ID
			$addressbook = new Api\Contacts();
			$contact = $addressbook->read($content['contact_id']);
			$account_id = $contact['account_id'];

			// Change password
			$auth = new Api\Auth();
			if($auth->change_password(false, $content['password'], $account_id))
			{
				// No need to keep this record
				registration_bo::delete($content['reg_id']);

				return Egw::redirect_link('/login.php',array( 'cd' => lang('Your password was changed.')));
			}
		}

		// Display form
		$GLOBALS['egw_info']['flags'] = array(
			'noheader'  => True,
			'nonavbar' => True,
			'app_header' => lang('Enter your new password'),
			'currentapp' => 'registration'
		);
		$template = new Etemplate('registration.change_password');
		$template->exec('registration.registration_ui.change_password', $data,$sel_options,[],$preserv);
		exit();
	}

	/**
	 * Confirm link - used for password change and account registration from the login page.
	 */
	public function confirm()
	{
		$GLOBALS['egw_info']['flags'] = array(
			'app_header' => lang('Confirm registration')
		);

        $register_code = ($_GET['confirm'] && preg_match('/^[0-9a-zA-Z]{40}$/', $_GET['confirm'])) ? $_GET['confirm'] : false;

		if($register_code && registration_bo::confirm($register_code))
		{
			$msg = lang('Registration complete');
		}
		else
		{
			$msg = lang('Unable to process confirmation.');
		}
		// delete cookies to NOT use link-registry of anonymous user stored in session
		Api\Session::egw_setcookie('sessionid');
		Api\Session::egw_setcookie('kp3');
		Api\Session::egw_setcookie('domain');

		Egw::redirect_link('/login.php',array( 'cd' => lang('Confirm registration') . ': ' . $msg));
	}

	/**
	 * eTemplate based Api\Config
	 */
	function config($content = array())
	{
		if(!$GLOBALS['egw_info']['user']['apps']['admin'])
		{
			Egw::redirect_link('/index.php');
		}
		if($content['cancel'])
		{
			Egw::redirect_link('/admin/index.php');
		}

		if($content['save'])
		{
			unset($content['save']);

			// Update async job to run as this user
			$async = new Api\Asyncservice();
			$job = $async->read('registration-purge');
			$job = $job['registration-purge'];
			$job['account_id'] = $content['anonymous_user'];
			if(!$async->write($job, true))
			{
				$async->set_timer(array('hour' => '*'),'registration-purge','registration.registration_bo.purge_expired',null, $content['anonymous_user']);
			}

			// Widget gives ID, code wants username
			$content['anonymous_user'] = $GLOBALS['egw']->accounts->id2name($content['anonymous_user']);

			// If not expiring, use -1
			if(!$content['accounts_expire']) $content['accounts_expire'] = -1;

			// Save
			foreach($content as $key => $value)
			{
				Api\Config::save_value($key, $value, 'registration');
			}
		}

		$data = Api\Config::read('registration');


		// Code uses username, widget wants ID
		$data['anonymous_user'] = $GLOBALS['egw']->accounts->name2id($data['anonymous_user']);

		$anon_apps = $GLOBALS['egw']->acl->get_user_applications($data['anonymous_user']);
		if($anon_apps['registration'] != Acl::READ)
		{
			$data['message'] = lang('Anonymous user needs access to registration application');
		}

		if(!$data['name_nobody']) $data['name_nobody'] = 'eGroupWare '.lang('registration');
		if(!$data['mail_nobody']) $data['mail_nobody'] = 'noreply@'.$GLOBALS['egw_info']['server']['mail_suffix'];

		// Check for a mail account
		if(!registration_bo::$mail_account)
		{
			Api\Framework::message('No mail account', 'error');
			error_log("BAD");
		}
		// Get the addressbooks with the right permissions
		$sel_options['pending_addressbook'] = registration_bo::get_allowed_addressbooks(registration_bo::PENDING);

		// Copied from sitemgr module
		$uicontacts = new addressbook_ui();
            $sel_options['show'] = array(
			'org_name'             => lang('Company'),
			'org_unit'             => lang('Department'),
			//	'n_fn'                 => lang('Prefix').', '.lang('Firstname').' + '.lang('Lastname'), //Required
			'sep1'                 => '----------------------------',
			//      'email'                => lang('email'), // Required, so don't even make it optional
			'tel_work'             => lang('work phone'),
			'tel_cell'             => lang('mobile phone'),
			'tel_fax'              => lang('fax'),
			'tel_home'             => lang('home phone'),
			'url'                  => lang('url'),
			'sep2'                 => '----------------------------',
			'adr_one_street'       => lang('street'),
			'adr_one_street2'      => lang('address line 2'),
			'adr_one_locality'     => lang('city').' + '.lang('zip code'),
			'sep3'                 => '----------------------------',
		);
		foreach($uicontacts->customfields as $name => $cf_data)
		{
			$sel_options['show']['#'.$name] = $cf_data['label'];
		}
		$sel_options['show'] += array(
			'sep4'                 => '----------------------------',
			'note'                 => lang('message'),
			'sep5'                 => '----------------------------',
			'captcha'              => lang('Verification'),
		);

		$GLOBALS['egw_info']['flags']['app_header'] = lang('Site Configuration');
		$template = new Etemplate('registration.config');
		$template->exec('registration.registration_ui.config', $data,$sel_options);
	}
}
