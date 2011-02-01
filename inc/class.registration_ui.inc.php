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

/**
 * User interface for Registration
 *
 * The user interface for the sitemgr module is in registration_sitemgr.
 * This file is for the normal eGW bits.
 */
class registration_ui {

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

	public function __construct() {
		$config = config::read('registration');
		$this->expiry = $config['expiry'];
	}

	public function index() {
	}

	/**
	 * View registration details (when link from contact is clicked)
	 *
	 * @param content
	 */
	public function view($content = array()) {
		$reg_id = ($_GET['reg_id'] && preg_match('/^[0-9]+$/',$_GET['reg_id'])) ? $_GET['reg_id'] : $content['reg_id'];

		if(!$reg_id) return lang('Missing registration');
		
		$registration = registration_bo::read($reg_id);

		switch ($registration['status']) {
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

		$GLOBALS['egw_info']['flags']['app_header'] = lang('registration');
		$template = new etemplate('registration.view');
		$template->exec('registration.registration_ui.view', $registration,$sel_options,$readonlys,$preserv);
	}

	/**
	 * Register for a user account
	 *
	 * This is a mirror of the sitemgr module registering an account, available outside of sitemgr.
	 */
	public static function register($content = array()) {
		// Fields to show
		$data['show'] = array(
			'account_lid'	=> true,
			'n_fn'		=> true,
			'password'	=> true,
			'email'		=> true
		);
		$config = config::read('registration');
		$template = new etemplate('registration.registration_form');

		if($config['show']) {
			$fields = explode(',',$config['show']);
			$data['show'] += array_combine($fields, array_fill(0,count($fields),true));
		}
		$data += $content;
		if($content['submitit']) {
                        if (isset($data['show']['captcha']) && 
				((isset($content['captcha_result']) && $content['captcha'] != $content['captcha_result']) || // no correct captcha OR
                                (time() - $content['start_time'] < 10 &&                                // bot indicator (less then 10 sec to fill out the form and
                                !$GLOBALS['egw_info']['etemplate']['java_script'])))     // javascript disabled)
                        {
                                $captcha_fail = true;
                                $template->set_validation_error('captcha',lang('Wrong - try again ...'));
				unset($content['captcha']);
                        }

			if(!$captcha_fail) {
				$config['register_for'] = 'account';
				// Check for account info
				try {
					registration_bo::check_account($content);
					$contact = new addressbook_bo();
					if ($config['pending_addressbook'])   // save the contact in the addressbook
					{
						$content['owner'] = $config['pending_addressbook'];
						$content['private'] = 0;        // in case default_private is set

						// Set timestap to expiry, so we don't have to save both
						$content['timestamp'] = time() + ($config['expiry'] * 3600);
						try {
							$reg_id = registration_bo::save($content);
						} catch (Exception $e) {
							$msg = $e->getMessage();
							$reg_id = false;
						}

						if($reg_id) {
							$registration = registration_bo::read($reg_id);
							if ($registration['contact_id'])
							{
								$config['title'] = lang('account registration');
								// Send out confirmation link
								$msg = registration_bo::send_confirmation($config, $registration);
							}
							$account_ok = true;
						}
					}
				} catch (egw_exception $e) {
					$msg = $e->getMessage();
					$account_ok = false;
				}
			}
		}
		if($msg) $data['message'] = $msg;

		// a simple calculation captcha
                if (in_array('captcha',$data['show'])) {
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

		if($account_ok) {
			$readonlys['__ALL__'] = true;
		}

		// Display form
		$GLOBALS['egw_info']['flags'] = array(
			'noheader'  => True,
			'nonavbar' => True,
			'app_header' => lang('account registration')
		);
		$template->exec('registration.registration_ui.register', $data,$sel_options,$readonlys,$preserv);
	}

	/**
	 * Send lost user ID(s)
	 */
	public function lost_username($content = array()) {

		// Deal with incoming
		if($content && $content['email']) {
			// Find usernames
			$query = array(
				'type'		=> 'accounts',
				'query_type'	=> 'email',
				'query'		=> $content['email']
			);
			$accounts = $GLOBALS['egw']->accounts->search($query);
			if($accounts) {
				// Build list
				$account_list = array();
				foreach($accounts as $id => $account) {
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
			} else {
				$data['message'] = lang('Sorry, no account exists for %1', $content['email']);
			}
		}

		// Display form
		$GLOBALS['egw_info']['flags'] = array(
			'noheader'  => True,
			'nonavbar' => True,
			'app_header' => lang('Lost user ID')
		);
		$template = new etemplate('registration.lost_username');
		$template->exec('registration.registration_ui.lost_username', $data,$sel_options,$readonlys,$preserv);
	}

	/**
	 * Reset lost password
	 */
	public function lost_password($content = array()) {

		$data = $content;
		// Deal with incoming
		if($content['wait'] && $content['wait'] > time()) {
			$data['message'] = lang('Abuse reduction - please wait %1 seconds and try again.', $content['wait'] - time());
			$data['username'] = $content['username'];
			unset($content['username']);
		}
		if($content && $content['username']) {
			// Find username
			$account_id = $GLOBALS['egw']->accounts->name2id($content['username']);
			if($account_id) {
				$account = $GLOBALS['egw']->accounts->read($account_id);
				$info = array(
					'contact_id'	=> $account['person_id'],
					'email'		=> $account['account_email'],
					'timestamp'	=> time() + $this->expiry * 3600,
					'post_confirm_hook' => 'registration.registration_ui.change_password'
				);
				$reg_id = registration_bo::save($info, false);
				$info = registration_bo::read($reg_id);
				$arguments = array(
					'link'	=> '/registration/',
					'title'	=> lang('Lost Password'),
					'expiry'=> $this->expiry
				);
				$data['message'] = registration_bo::send_confirmation($arguments, $info);
				$readonlys['submit'] = true;
				$readonlys['username'] = true;
			} else {
				$data['message'] = lang('Sorry, that username does not exist.');
				// Start a timer to prevent excessive use
				$preserv['wait'] = time() + 10; // wait 10s
			}
		}

		// Display form
		$GLOBALS['egw_info']['flags'] = array(
			'noheader'  => True,
			'nonavbar' => True,
			'app_header' => lang('Lost password')
		);

		$template = new etemplate('registration.lost_password');
		$template->exec('registration.registration_ui.lost_password', $data,$sel_options,$readonlys,$preserv);
	}

	// Callback after clicking lost password confirm link
	public function change_password($content) {
		$preserv = $content;

		if($content['password'] && $content['password'] != $content['password2']) {
			unset($content['submit']);
			$data['message'] = lang('The two passwords are not the same');
		}
		if($content['submit']) {
			// Get account ID
			$addressbook = new addressbook_bo();
			$contact = $addressbook->read($content['contact_id']);
			$account_id = $contact['account_id'];

			// Change password
			$auth =& CreateObject('phpgwapi.auth');
			if($auth->change_password(false, $content['password'], $account_id)) {
				// No need to keep this record
				registration_bo::delete($content['reg_id']);
				$data['message'] = lang('Your password was changed.');
				$data['done'] = true;
			}
		}

		// Display form
		$GLOBALS['egw_info']['flags'] = array(
			'noheader'  => True,
			'nonavbar' => True,
			'app_header' => lang('Enter your new password')
		);
		$template = new etemplate('registration.change_password');
		$template->exec('registration.registration_ui.change_password', $data,$sel_options,$readonlys,$preserv);
		common::egw_exit();
	}

        /**
         * Confirm link - used for password change and account registration from the login page.
         */
        public function confirm() {
		$GLOBALS['egw_info']['flags'] = array(
			'app_header' => lang('Confirm')
		);
		common::egw_header();
                $register_code = ($_GET['confirm'] && preg_match('/^[0-9a-f]{32}$/',$_GET['confirm'])) ? $_GET['confirm'] : false;
                if($register_code && registration_bo::confirm($register_code)) {
                        echo lang('Registration complete');
                } else {
                        echo lang('Unable to process confirmation.');
                }
		common::parse_navbar();
		common::egw_footer();
        }

	/**
	 * eTemplate based config
	 */
	function config($content = array()) {
		if(!$GLOBALS['egw_info']['user']['apps']['admin']) {
			egw::redirect_link('/index.php');
		}
		if($content['cancel']) {
			egw::redirect_link('/admin/index.php');
		}
			
		if($content['save']) {
			unset($content['save']);

			// Widget gives ID, code wants username
			$content['anonymous_user'] = $GLOBALS['egw']->accounts->id2name($content['anonymous_user']);

			// If not expiring, use -1
			if(!$content['accounts_expire']) $content['accounts_expire'] = -1;

			// Save
			foreach($content as $key => $value) {
				config::save_value($key, $value, 'registration');
			}
		}
		
		$data = config::read('registration');


		// Code uses username, widget wants ID
		$data['anonymous_user'] = $GLOBALS['egw']->accounts->name2id($data['anonymous_user']);

		$anon_apps = $GLOBALS['egw']->acl->get_user_applications($data['anonymous_user']);
		if($anon_apps['registration'] != EGW_ACL_READ) {
			$data['message'] = lang('Anonymous user needs access to registration application');
		}

		if(!$data['name_nobody']) $data['name_nobody'] = 'eGroupWare '.lang('registration');
		if(!$data['mail_nobody']) $data['mail_nobody'] = 'noreply@'.$GLOBALS['egw_info']['server']['mail_suffix'];

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
		foreach($uicontacts->customfields as $name => $data)
		{
			$sel_options['show']['#'.$name] = $data['label'];
		}
		$sel_options['show'] += array(
			'sep4'                 => '----------------------------',
			'note'                 => lang('message'),
			'sep5'                 => '----------------------------',
			'captcha'              => lang('Verification'),
		);

		$GLOBALS['egw_info']['flags']['app_header'] = lang('Site Configuration');
		$template = new etemplate('registration.config');
		$template->exec('registration.registration_ui.config', $data,$sel_options,$readonlys,$preserv);
	}
}
