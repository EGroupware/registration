<?php
/**
 * Registration - Sitemgr form
 *
 * @link http://www.egroupware.org
 * @author Nathan Gray
 * @package registration
 * @copyright (c) 2010 by Nathan Gray
 * @license http://opensource.org/licenses/gpl-license.php GPL - GNU General Public License
 * @version $Id$
 */

/**
 * SiteMgr contact form for registration
 *
 */
class registration_sitemgr extends addressbook_contactform
{
	public $public_functions = array(
		'confirm' => true
	);
	/**
	 * Callback as variable for easier extending
	 *
	 * @var string
	 */
	var $callback = 'registration.registration_sitemgr.display';

	/**
	 * Shows the contactform and stores the submitted data
	 *
	 * @param array $content=null submitted eTemplate content
	 * @param int $addressbook=null int owner-id of addressbook to save contacts to
	 * @param array $fields=null field-names to show
	 * @param string $msg=null message to show after submitting the form
	 * @param string $email=null comma-separated email addresses
	 * @param string $tpl_name=null custom etemplate to use
	 * @param string $subject=null subject for email
	 * @param string $copytoreceiver=false send a copy of notification to receiver
	 * @return string html content
	 */
	function display(array $content=null,$block=array(),$properties=array())
	{
		if($_GET['confirm']) {
			return $this->confirm($_content, $block, $properties);
		}
		$arguments = $block->arguments;
		if(!is_array($arguments) || count($arguments) == 0) {
			$arguments = $content['arguments'] ? $content['arguments'] : array();
		}
		if($block && $block->version) {
			$arguments['sitemgr_version'] = $block->version;
			$arguments['title'] = $block->title;
		}
		$addressbook = $arguments['pending_addressbook'];

		// Required fields
		if($arguments['register_for'] == 'account')
		{
			$arguments['fields'][] = 'account_lid';
			$arguments['fields'][] = 'password';
		}
		$arguments['fields'][] = 'email';
		$fields = $arguments['fields'];
		$msg = '';
		$tpl_name = $arguments['etemplate'];

		if (empty($tpl_name) && !empty($content['tpl_form_name'])) $tpl_name =$content['tpl_form_name'];
		$tpl = new etemplate($tpl_name ? $tpl_name : 'registration.registration_form');
		// initializing some fields
		if (!$fields) $fields = array('org_name','n_fn','email','tel_work','url','note','captcha');
		$submitted = false;
		// check if submitted
		if (is_array($content))
		{
			if ((isset($content['captcha_result']) && $content['captcha'] != $content['captcha_result']) ||	// no correct captcha OR
				(time() - $content['start_time'] < 10 &&				// bot indicator (less then 10 sec to fill out the form and
				!$GLOBALS['egw_info']['etemplate']['java_script']))	// javascript disabled)
			{
				$submitted = "truebutfalse";
				$tpl->set_validation_error('captcha',lang('Wrong - try again ...'));
			}
			elseif ($content['submitit'])
			{
				$submitted = true;

				if($arguments['register_for'] == 'account')
				{
					// Check for account info
					$account_ok = true;
					try {
						registration_bo::check_account($content);
					} catch (egw_exception $e) {
						$msg = $e->getMessage();
						$account_ok = false;
					}
				}
				else
				{
					$account_ok = true;
				}

				$contact = new addressbook_bo();
				if ($account_ok && $content['owner'])	// save the contact in the addressbook
				{
					$content['private'] = 0;	// in case default_private is set
					
					// Set timestap to expiry, so we don't have to save both
					$content['timestamp'] = time() + ($arguments['expiry'] * 3600);
					$content['sitemgr_version'] = $arguments['sitemgr_version'];
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
							// Send out confirmation link
							$msg = registration_bo::send_confirmation($arguments, $registration);

							// check for fileuploads and attach the found files
							foreach($content as $name => $value)
							{
								if (is_array($value) && isset($value['tmp_name']) && is_readable($value['tmp_name']))
								{
									// do no further permission check, as this would require_once
									// the anonymous user to have run rights for addressbook AND
									// edit rights for the addressbook used to store the new entry,
									// which is clearly not wanted securitywise
									egw_vfs::$is_root = true;
									egw_link::link('addressbook',$registration['contact_id'],egw_link::VFS_APPNAME,$value,$name);
									egw_vfs::$is_root = false;
								}
							}

							return '<p align="center">'.($msg ? $msg : $content['msg']).'</p>';
						}
					}
					elseif ($msg == '')
					{
						return '<p align="center">'.lang('There was an error saving your data :-(').'<br />'.
							lang('The anonymous user has probably no add rights for this addressbook.').'</p>';
					}
				}
			}
		}
		$preserv['arguments'] = $arguments;
		if (!is_array($content))
		{
			$preserv['tpl_form_name'] = $tpl_name;
			$preserv['is_contactform'] = true;
			$preserv['email_contactform'] = $email;
			$preserv['subject_contactform'] = $subject;
			$preserv['email_copytoreceiver'] = $copytoreceiver;
			#if (!$fields) $fields = array('org_name','n_fn','email','tel_work','url','note','captcha');
			$custom = 1;
		}
		elseif ($submitted == 'truebutfalse')
		{
			$preserv['tpl_form_name'] = $tpl_name;
			unset($content['submitit']);
			$custom = 1;
		}
		foreach($fields as $name)
		{
			if ($name[0] == '#')	// custom field
			{
				static $contact;
				if (is_null($contact))
				{
					$contact = new addressbook_bo();
				}
				$content['show']['custom'.$custom] = true;
				$content['customfield'][$custom] = $name;
				$content['customlabel'][$custom] = $contact->customfields[substr($name,1)]['label'];
				++$custom;
			}
			elseif($name == 'adr_one_locality')
			{
				if (!($content['show'][$name] = $GLOBALS['egw_info']['user']['preferences']['addressbook']['addr_format']))
				{
					$content['show'][$name] = 'postcode_city';
				}
			}
			else
			{
				$content['show'][$name] = true;
			}
		}
		// reset the timestamp
		$preserv['start_time'] = time();
		$content['lang'] = $GLOBALS['egw_info']['user']['preferences']['common']['lang'];
		$content['addr_format'] = $GLOBALS['egw_info']['user']['preferences']['addressbook']['addr_format'];

		if ($addressbook) $preserv['owner'] = $addressbook;
		if ($msg) $preserv['msg'] = $msg;
		$content['message'] = $msg;

		// a simple calculation captcha
		$num1 = rand(1,99);
		$num2 = rand(1,99);
		if ($num2 > $num1)	// keep the result positive
		{
			$n = $num1; $num1 = $num2; $num2 = $n;
		}
		if (in_array('captcha',$fields))
		{
			$content['captcha_task'] = sprintf('%d - %d =',$num1,$num2);
			$preserv['captcha_result'] = $num1-$num2;
		}
		return $tpl->exec($this->callback,$content,$sel_options,$readonlys,$preserv);
	}

	/**
	 * Confirm link
	 */
	public function confirm() {
		$register_code = ($_GET['confirm'] && preg_match('/^[0-9a-f]{32}$/',$_GET['confirm'])) ? $_GET['confirm'] : false;
		if($register_code && registration_bo::confirm($register_code)) {
			return lang('Registration complete');
		} else {
			return lang('Unable to process confirmation.');
		}
	}
}
