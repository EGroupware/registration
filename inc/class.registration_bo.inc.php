<?php
/**
 * Registration - General business object
 *
 * @link http://www.egroupware.org
 * @author Nathan Gray
 * @package registration
 * @copyright (c) 2011 by Nathan Gray
 * @license http://opensource.org/licenses/gpl-license.php GPL - GNU General Public License
 * @version $Id$
 */

/**
 * General business object for Registration
 * Both the sitemgr module and the login page UI pull from here.
 */

require_once(EGW_INCLUDE_ROOT . '/sitemgr/inc/class.Content_BO.inc.php');

class registration_bo extends bo_tracking {

	protected static $so;

	const PENDING = 1;
	const CONFIRMED = 2;

	public static $status_list = array(
		self::PENDING	=> 'Pending',
		self::CONFIRMED	=> 'Registered'
	);

	public function __construct() {
		self::static_init();
	}

	public static function static_init() {
		if(!is_object(self::$so)) {
			self::$so = new registration_so();
		}
	}

	/**
	 * Read registration information, given the ID
	 */
	public static function read($reg_id) {
		$reg_info = self::$so->read((int)$reg_id);
		if($reg_info && $reg_info['contact_id']) {
			$addressbook = new addressbook_bo();
			$reg_info += $addressbook->read($reg_info['contact_id']);
		}
		return $reg_info;
	}

	/**
	 * Store registration information
	 */
	public static function save($values, $link = true) {
		if(!$values['status']) $values['status'] = self::PENDING;
		if(!$values['register_code']) $values['register_code'] = md5(time());

		// Check account & password
		if($values['account_lid']) {
			if(!$values['password']) {
				throw new egw_exception_wrong_userinput(lang('You must enter a password'));
			}
			if(self::$so->read(array('account_lid' => $values['account_lid'])) ||
				$GLOBALS['egw']->accounts->exists($values['account_lid']) !== 0) {
				throw new egw_exception(lang('Sorry, that username is already taken.'));
			}
		}

		// If there's contact info, store it in addressbook
		$addressbook = new addressbook_bo();
		$contact_fields = $addressbook->contact_fields;
		unset($contact_fields['email']); // Always present
		unset($contact_fields['id']); // Address already there
		if(array_intersect_key($contact_fields,$values)) {
			$result = $addressbook->save($values);
			if(!$result) {
				throw new egw_exception_no_permission($addressbook->error);
				return False;
			}


			$values['contact_id'] = $result;
		}

		// Check pre-confirm, add in post-confirm hook, if registering for a hooked app
		if(strpos($values['register_for'], ':') !== False) {
			list($app, $name) = explode(':', $values['register_for']);
			$hook = $GLOBALS['egw']->hooks->single('registration', $app);
			if($hook['post_confirm_hook']) $values['post_confirm_hook'] = $hook['post_confirm_hook'];
			if($hook['pre_check']) {
				$result = ExecMethod($hook['pre_check'], $values);
				if($result !== true) throw new egw_exception($result);
			}
		}

		$result = self::$so->save($values);
		if(!$result && $link) {
			// Link
			egw_link::link('registration', self::$so->data['reg_id'], 'addressbook', $values['contact_id']);
		}
		if(!$result) {
			return self::$so->data['reg_id'];
		}
		return false;
	}

	/**
	 * Removes a registration record.
	 * Does not remove associated contact (it may be used)
	 */
	public static function delete($reg_id) {
		self::$so->delete($reg_id);
	}

	/**
	 * Send an email with a confirmation link
	 */
	public function send_confirmation($arguments, $reg_info) {
		$config = config::read('registration');
		$time = egw_time::to($reg_info['timestamp']) . ' (' . $arguments['expiry'] . ' ' . lang('hours') . ')';
		$link = html::link($arguments['link'],  array('confirm' => $reg_info['register_code']));
		$subject = $arguments['subject'] ? $arguments['subject'] : lang('subject for confirmation email title: %1', $arguments['title']);
		$message = $arguments['message'] ? $arguments['message'] : lang('confirmation email for %1 expires %2 link: %3', $arguments['title'], $time, $link);

		if($config['tos_text']) $message .= "\n" . lang('terms of service') . "\n" . $config['tos_text'];
		if($config['support_email']) $message .= "\n" . $config['support_email'];

		$mail = new send();
		$mail->From = $config['mail_nobody'] ? $config['mail_nobody'] : 'noreply@'.$GLOBALS['egw_info']['server']['mail_suffix'];
		$mail->FromName = $config['name_nobody'] ? $config['name_nobody'] : 'eGroupWare '.lang('registration');
		$mail->AddAddress($reg_info['email'], $reg_info['n_fileas']);
		if($config['support_email']) $mail->AddReplyTo($config['support_email']);
		$mail->Subject = $subject;
		$mail->Body = $message;

		if(!$mail->Send()) {
			return "Error: " . $mail->ErrorInfo;
		} else {
			return "Confirmation message sent";
		}
	}

	/**
	 * Process a pending registration (or password change)
	 *
	 * Everything has been set up already, now finish it off.
	 */
	public static function confirm($registration_code) {
		$registration = self::$so->read(array('register_code' => $registration_code, 'status' => self::PENDING));

		if(!$registration) return false;

		// Load address
		$addressbook = new addressbook_bo();
		$address = $addressbook->read($registration['contact_id']);

		// Load settings
		if($registration['sitemgr_version']) {
			// All the settings (more than from login page) are set in the block
			$content = new Content_BO();
			$config = $content->getversion($registration['sitemgr_version']);
		} else {
			// Login page - use global config
			$config = config::read('registration');
			if($registration['account_lid']) $config['register_for'] = 'account';
		}

		if($config['register_for'] == 'account' && self::check_account(array_merge($registration + $address), $account)) {
			// Add a new account
			$command = new admin_cmd_edit_user(false, $account, $account['password']);
			$command->run();
			// Read it back
			$account_id = $GLOBALS['egw']->accounts->name2id($account['account_lid']);
			$account = $addressbook->read("account:$account_id");

			// Anon user has no rights to edit accounts - you can't do this
			//$addressbook->merge(array($registration['contact_id'], $account_id));

			// Registering an account creates a new addressbook entry.  Delete the old one.
			if(!$addressbook->delete($registration['contact_id'])) echo 'Could not delete old address';
			$registration['contact_id'] = $account['id'];

			// Link to the new contact
			egw_link::link('registration', $registration['reg_id'], 'addressbook', $account['id']);

		} elseif ($config['confirmed_addressbook']) {
			// Move address
			$address['owner'] = $config['confirmed_addressbook'];
			$result = $addressbook->save($address, true);
			if(!$result) echo $addressbook->error;
		}

		// Finish registration
		$registration['status'] = self::CONFIRMED;
		$registration['ip'] = egw_session::getuser_ip();
		$registration['timestamp'] = time();
		$registration['account_lid'] = null;
		$registration['password'] = null;
		$result = self::$so->save($registration);

		// Run post confirmation code
		if($registration['post_confirm_hook']) {
			ExecMethod($registration['post_confirm_hook'], $registration);
		}

		// Update link
		egw_link::notify_update('registration', $registration_code);

		return $registration;
	}

	/**
	 * Check to see if registration info satisfies account requirements
	 *
	 * @param registration array
	 * @param account optional array of info populated from $registration to be passed to user command
	 */
	public static function check_account($registration, &$account = array()) {
		$account = array(
			'account_lid'		=> $registration['account_lid'],
			'account_firstname'	=> $registration['n_given'],
			'account_lastname'	=> $registration['n_family'],
			'account_email'		=> $registration['email'],
			'account_passwd'	=> $registration['password'],
			'account_active'	=> true,
			'account_primary_group'	=> $version['primary_group'],
			'account_groups'	=> $version['groups'],
			'account_expires'	=> null,
			'changepassword'        => true,
			'mustchangepassword'    => true,
		);
		// Just check for validity, don't actually run
		// Schedule in the future to get the checks, then delete it.
		$command = new admin_cmd_edit_user(false, $account, $registration['password']);
		$command->run(time() + 10000, true, false);
		$command->delete();
		return true;
	}

	/**
	 * Get a list of things you can register for
	 *
	 * Registration supports a 'registration' hook, so you can register for other
	 * things / apps using the same process.  
	 * The hook should return an array with the keys 'name', 'pre_check' and 'post_confirm_hook'.
	 * It is also acceptable to return an array with several name/pre_check/post_confirm_hook 
	 * sub-arrays for multiple options per application.
	 *
	 * Name should be an un-translated [human] reference.  If you want multiple options, name
	 * must be unique.
	 * pre_check and post_confirm_hook should be methods in the standard ExecMethod style.
	 * pre_check is optional, and will be called with the data about to be saved.  It should return
	 * true or an error message.
	 * post_confirm_hook does not need to return anything, and is called after the confirmation is 
	 * completed.
	 *
	 * @return array
	 */
	public static function register_apps_list() {
		$list = array(
			'account'	=> lang('Account'),
			'other'		=> lang('Other')
		);

		// Poll registration hook
		$hooks = $GLOBALS['egw']->hooks->process('registration');
		foreach($hooks as $appname => $app) {
			if(!is_array($app[0])) $app = Array($app);
			foreach($app as $result) {
				$list[$appname . ':' . $result['name']] = lang($result['name']);
			}
		}
		return $list;
	}

	
	/**
	 * Get a list of addressbooks that can be used by registration
	 *
	 * This actually checks the permissions the anonymous user has on each
	 * addressbook, and only returns the addressbooks that will actually work.
	 *
	 * @param use either PENDING or CONFIRMED
	 *
	 * @return array of addressbook_id => name
	 */
	public static function get_allowed_addressbooks($use = self::PENDING) {
		if($use == self::CONFIRMED) {
			$perms = EGW_ACL_ADD;
		} elseif ($use == self::PENDING) {
			$perms = EGW_ACL_READ|EGW_ACL_ADD|EGW_ACL_DELETE;
		} else {
			return array();
		}

		$config = config::read('registration');
                $anonymous_user = $GLOBALS['egw']->accounts->name2id($config['anonymous_user']);

                // Shuffle stuff to get the anon user's addressbook grants
                $user = $GLOBALS['egw_info']['user']['account_id'];
                $GLOBALS['egw_info']['user']['account_id'] = $anonymous_user;
                $acl = new acl($anonymous_user);
                $addressbook = new addressbook_bo();
                $addressbook->grants = $acl->get_grants('addressbook',false);
                $addressbooks = $addressbook->get_addressbooks($perms);
                $GLOBALS['egw_info']['user']['account_id'] = $user;
		
		return $addressbooks;
	}

	/**
	 * Get a list of pages with registration blocks 
	 * 
	 * The list is used for login module "Register" link
	 */
	public static function get_blocks() {
		$modules = new Modules_BO();
		$module_id = $modules->getmoduleid('registration_form');
		$blocks = $GLOBALS['Common_BO']->content->so->getallblocks(
			$GLOBALS['Common_BO']->cats->getpermittedcatsWrite(), 
			$GLOBALS['Common_BO']->getstates('Edit')
		);
		$reg_blocks = array();
		foreach($blocks as &$block) {
			// getallblocks() doesn't fully populate the block object
			$block = $GLOBALS['Common_BO']->content->getblock($block->id, false);
			if($block->module_id == $module_id) {
				$page = $GLOBALS['Common_BO']->pages->getPage($block->page_id);
				$title = $GLOBALS['Common_BO']->content->getlangblocktitle($block->id, $GLOBALS['egw_info']['user']['preferences']['common']['lang']);
				$reg_blocks[$page->name] = $title;
			}
		}

		return $reg_blocks;
	}

	/**
	 * Purge any unconfirmed registrations that have expired
	 */
	public static function purge_expired() {
		$expire = $GLOBALS['egw']->db->quote($GLOBALS['egw']->db->to_timestamp(time()));
		$query = array('status' => self::PENDING, 'timestamp <= ' . $expire);

		// Clear contact information
		$expired = self::$so->search($query, array('reg_id','contact_id'));
		if(is_array($expired)) {
			$addressbook = new addressbook_bo();
			foreach($expired as $record) {
				// Clear registration
				if(!$addressbook->delete($record['contact_id'])) {
					echo $addressbook->user . ' needs permission to delete';
				}
				self::$so->delete($record['reg_id']);
			}
		}

	}

	/**
	 * To be able to supply link titles
	 */
	public static function link_title($entry) {
		if(!is_array($entry)) {
			$entry = self::read($entry);
		}
		if(!$entry) return 'Error';

		$title = '';

		// Load settings
		if($entry['sitemgr_version']) {
			$content = new Content_BO();
			$block_id = $content->so->getblockidforversion($entry['sitemgr_version']);
			$title = $content->getlangblocktitle($block_id, $GLOBALS['egw_info']['user']['preferences']['common']['lang']);
			$title .= ' ';
		}
		
		switch($entry['status']) {
			case self::PENDING:
				$title .= lang(self::$status_list[$entry['status']]) . ': ' . lang('Expires') . ' ' . egw_time::to($entry['timestamp']);
				break;
			case self::CONFIRMED:
				$title .= lang(self::$status_list[$entry['status']]) . ': ' . egw_time::to($entry['timestamp']) . ' [' . $entry['ip'] . ']';
				break;
			default:
				$title .= lang(self::$status_list[$entry['status']]) . ': ' . egw_time::to($entry['timestamp']) . ' [' . $entry['ip'] . ']';
				break;
		}

		return $title;
	}
}
registration_bo::static_init();
?>
