<?php
	/**************************************************************************\
	* eGroupWare - Registration                                                *
	* http://www.eGroupWare.org                                                *
	* This application written by Joseph Engo <jengo@phpgroupware.org>         *
	* Modified by Jason Wies (Zone) <zone@users.sourceforge.net>               *
	* Modified by Loic Dachary <loic@gnu.org>                                  *
	* Modified by Pim Snel <pim@egroupware.org>                                *
	* --------------------------------------------                             *
	* Funding for this program was provided by http://www.checkwithmom.com     *
	* --------------------------------------------                             *
	*  This program is free software; you can redistribute it and/or modify it *
	*  under the terms of the GNU General Public License as published by the   *
	*  Free Software Foundation; either version 2 of the License, or (at your  *
	*  option) any later version.                                              *
	\**************************************************************************/

	/* $Id$ */

	class soreg
	{
		var $reg_id;
		var $db;
		var $reg_table = 'phpgw_reg_accounts';

		function soreg()
		{
			$this->db = clone($this->db);
		}

		function account_exists($account_lid)
		{
			$this->db->select($this->reg_table,'reg_dla',array(
				'reg_lid' => $account_lid,
			),__LINE__,__FILE__);
			$this->db->next_record();

//			echo (time()-$this->db->f(0));
//			echo "<br>";
//			echo time();
			
			if ( $GLOBALS['egw']->accounts->exists($account_lid) || ( $this->db->f(0) && (time()-$this->db->f(0))<1800  ))
			{
				return True;
			}
			else
			{
				// To prevent race conditions, reserve the account_lid
				$this->db->insert($this->reg_table,array(
					'reg_id'   => '',
					'reg_lid'  => $account_lid,
					'reg_info' => '',
					'reg_dla'  => time(),
				),__LINE__,__FILE__);
				$GLOBALS['egw']->session->appsession('loginid','registration',$account_lid);
				return False;
			}
		}

		function step2($fields,$send_mail=True)
		{
			global $config;
			$smtp =& CreateObject('phpgwapi.send');

			// We are not going to use link(), because we may not have the same sessionid by that time
			// If we do, it will not affect it
			$url = $GLOBALS['egw_info']['server']['hostname'] . "/registration/main.php";
			if (substr($url,0,4) != 'http')
			{
				$url = ($_SERVER['HTTPS'] ? 'https://' : 'http://') . $url;
			}
			$account_lid  = $GLOBALS['egw']->session->appsession('loginid','registration');
			$this->reg_id = md5(time() . $account_lid . $GLOBALS['egw']->common->randomstring(32));

			$this->db->update($this->reg_table,array(
				'reg_id' => $this->reg_id,
				'reg_dla' => time(),
				'reg_info' => base64_encode(serialize($fields))
			),array(
				'reg_lid' => $account_lid,
			),__LINE__,__FILE__);
	
			$GLOBALS['egw']->template->set_file(array(
				'message' => 'confirm_email.tpl'
			));
			
			$GLOBALS['egw']->template->set_var('Hi',lang('Hi'));
			$GLOBALS['egw']->template->set_var('message1',lang('This is a confirmation email for your new account.  Click on the following link to finish activating your account. This link will expire in 2 hours.'));

			$GLOBALS['egw']->template->set_var('message2',lang('If you did not request this account, simply ignore this message.'));

			if ($fields['n_given'])
			{
				$GLOBALS['egw']->template->set_var ('firstname', $fields['n_given'] . ' ');
			}

			if ($fields['n_family'])
			{
				$GLOBALS['egw']->template->set_var ('lastname', $fields['n_family']);
			}

			$GLOBALS['egw']->template->set_var ('activate_url',$url . '?menuaction=registration.boreg.step4&reg_id='. $this->reg_id);

			if ($config['support_email'])
			{
				$GLOBALS['egw']->template->set_var ('support_email_text', lang ('Report all problems and abuse to'));
				$GLOBALS['egw']->template->set_var ('support_email', $config['support_email']);
			}

			$subject = $config['subject_confirm'] ? lang($config['subject_confirm']) : lang('Account registration');
			$noreply = $config['mail_nobody'] ? ('No reply <' . $config['mail_nobody'] . '>') : ('No reply <noreply@' . $_SERVER['SERVER_NAME'] . '>');

			if ($send_mail)
			{
				$ret = $smtp->msg('email',$fields['email'],$subject,$GLOBALS['egw']->template->fp('out','message'),'','','',$noreply);
				if ($ret != True)
				{
					print(lang("Problem Sending Email:").$smtp->desc) ;
					print(lang("<br>Please Contact the site administrator.")) ;
					exit() ;
				}
			}
			return $this->reg_id;
		}

		//
		// username
		//
		function lostpw1($account_lid)
		{
			global $config;
			
			$url = ($_SERVER['HTTPS'] ? 'https://' : 'http://').$GLOBALS['egw_info']['server']['hostname'] . "/registration/main.php";

			$error = '';

			//
			// Remember md5 string sent by mail
			//
			$reg_id = md5(time() . $account_lid . $GLOBALS['egw']->common->randomstring(32));
			$this->db->insert($this->reg_table,array(
				'reg_id'   => $reg_id,
				'reg_lid'  => $account_lid,
				'reg_info' => '',
				'reg_dla'  => time(),
			),__LINE__,__FILE__);

			//
			// Send the mail that will allow to change the password
			//
			$account_id = $GLOBALS['egw']->accounts->name2id($account_lid);

			if ($account_id)
			{
				$info = array(
					'firstname' => $GLOBALS['egw']->accounts->id2name($account_id,'account_firstname'),
					'lastname'  => $GLOBALS['egw']->accounts->id2name($account_id,'account_lastname'),
					'email'     => $GLOBALS['egw']->accounts->id2name($account_id,'account_email'),
				);
				$smtp =& CreateObject('phpgwapi.send');

				$GLOBALS['egw']->template->set_file(array(
					'message' => 'lostpw_email.tpl'
				));

				$GLOBALS['egw']->template->set_var('hi',lang('Hi'));
				$GLOBALS['egw']->template->set_var('message1',lang('You requested to change your password. Please follow the URL below to do so. This URL will expire in two hours. After this delay you should go thru the lost password procedure again.'));
				
				$GLOBALS['egw']->template->set_var('message2',lang('If you did not request this change, simply ignore this message.'));

				$GLOBALS['egw']->template->set_var('firstname',$info['firstname']);
				$GLOBALS['egw']->template->set_var('lastname',$info['lastname']);
				$GLOBALS['egw']->template->set_var('activate_url',$url . '?menuaction=registration.boreg.lostpw2&reg_id=' . $reg_id);

				$subject = $config['subject_lostpw'] ? lang($config['subject_lostpw']) : lang('Account password retrieval');
				$noreply = $config['mail_nobody'] ? ('No reply <' . $config['mail_nobody'] . '>') : ('No reply <noreply@' . $_SERVER['SERVER_NAME'] . '>');

				$ret = $smtp->msg('email',$info['email'],$subject,$GLOBALS['egw']->template->fp('out','message'),'','','',$noreply);
				if ($ret != True)
				{
					print(lang("Problem Sending Email:").$smtp->desc) ;
					print(lang("<br>Please Contact the site administrator.")) ;
					exit() ;
				}
			}
			else
			{
				$error = lang("Account $account_lid record could not be found, report to site administrator");
			}

			return $error;
		}

		//
		// link sent by mail
		//
		function lostpw2($account_lid)
		{
			$account_id = $GLOBALS['egw']->accounts->name2id($account_lid);

			$GLOBALS['egw']->session->appsession('loginid','registration',$account_lid);
			$GLOBALS['egw']->session->appsession('id','registration',$account_id);
		}

		//
		// new password
		//
		function lostpw3($account_lid, $passwd)
		{
			$auth =& CreateObject('phpgwapi.auth');
			$auth->change_password(false, $passwd, $GLOBALS['egw']->session->appsession('id','registration'));

			$this->db->delete($this->reg_table,array('reg_lid' => $account_lid),__LINE__,__FILE__);
		}

		function valid_reg($reg_id)
		{
			$this->db->select($this->reg_table,'*',array('reg_id' => $reg_id),__LINE__,__FILE__);
			
			if (!$this->db->next_record()) return false;

			return array(
				'reg_id'   => $this->db->f('reg_id'),
				'reg_lid'  => $this->db->f('reg_lid'),
				'reg_info' => $this->db->f('reg_info'),
				'reg_dla'  => $this->db->f('reg_dla')
			);
		}

		function delete_reg_info($reg_id)
		{
			$this->db->delete($this->reg_table,array('reg_id' => $reg_id),__LINE__,__FILE__);
		}

		function create_account($account_lid,$_reg_info)
		{
			global $config, $reg_info;

			$fields = unserialize(base64_decode($_reg_info));
			$fields['lid'] = "*$account_lid*";
			//$fields['lid'] = $account_lid;


			$reg_info['lid']    = $account_lid;
			$reg_info['fields'] = $fields;

			$GLOBALS['auto_create_acct']['email'] = array(
				'firstname' => $fields['n_given'],
				'lastname'  => $fields['n_family'],
				'email'     => $fields['email'],
			);		
			$account_id = $GLOBALS['egw_info']['user']['account_id'] = $GLOBALS['egw']->accounts->auto_add($account_lid,$fields['passwd'],True,False,0,'A');

			if (!$account_id)
			{
				return False;
			}

			//var_dump($account_id);
			$accounts   =& CreateObject('phpgwapi.accounts',$account_id);
			$contacts   =& CreateObject('phpgwapi.contacts');

			$this->db->transaction_begin();

			$contact_fields = $fields;

			if ($contact_fields['bday_day'])
			{
				$contact_fields['bday'] = $contact_fields['bday_month'] . '/' . $contact_fields['bday_day'] . '/' . $contact_fields['bday_year'];
			}

			/* There are certain things we don't want stored in contacts */
			unset ($contact_fields['passwd']);
			unset ($contact_fields['passwd_confirm']);
			unset ($contact_fields['bday_day']);
			unset ($contact_fields['bday_month']);
			unset ($contact_fields['bday_year']);

			/* Don't store blank values either */
			foreach ($contact_fields as $num => $field)
			{
				if (!$contact_fields[$num])
				{
					unset ($contact_fields[$num]);
				}
			}

			//var_dump($contact_fields);
			//echo "ac<P>";
			//die($account_id);	
			$contacts->add($account_id,$contact_fields,0,'P');

			$this->db->transaction_commit();

			$accounts->read_repository();
			if ($config['trial_accounts'])
			{
				$accounts->data['expires'] = time() + ((60 * 60) * ($config['days_until_trial_account_expires'] * 24));
			}
			else
			{
				$accounts->data['expires'] = -1;
			}
			$accounts->save_repository();

			#if(@stat(EGW_SERVER_ROOT . '/messenger/inc/hook_registration.inc.php'))
			#{
			#	include(EGW_SERVER_ROOT . '/messenger/inc/hook_registration.inc.php');
			#}
		}
	
		function lostid1($email)
		{
			global $config;
			
			$url = ($_SERVER['HTTPS'] ? 'https://' : 'http://').$GLOBALS['egw_info']['server']['hostname'] . "/registration/main.php";

			$error = '';

			$smtp =& CreateObject('phpgwapi.send');

			$GLOBALS['egw']->template->set_file(array('message' => 'lostid_email.tpl'));

			$account_id = $GLOBALS['egw']->accounts->name2id($email,'account_email');
			$info = array(
				'firstname' => $GLOBALS['egw']->accounts->id2name($account_id,'account_firstname'),
				'lastname' => $GLOBALS['egw']->accounts->id2name($account_id,'account_lastname'),
				'email' => $GLOBALS['egw']->accounts->id2name($account_id,'account_email'),
			);
			if (is_null($info['firstname']))
				$info['firstname'] = lang('[Unknown first name]') ;
				
			if (is_null($info['lastname']))
				$info['lastname'] = lang('[Unknown last name]') ;
			
			$GLOBALS['egw']->template->set_var('hi',lang('Hi'));
			$GLOBALS['egw']->template->set_var('firstname',$info['firstname']);
			$GLOBALS['egw']->template->set_var('lastname',$info['lastname']);
			$GLOBALS['egw']->template->set_var('message1', lang('lost_user_id_message'));
					
			// Send the mail that tell the user id
			$GLOBALS['egw']->template->set_var('lostids',$GLOBALS['egw']->accounts->id2name($account_id));
			
			$subject = $config['subject_lostid'] ? lang($config['subject_lostpid']) : lang('Lost user account retrieval');
			$noreply = $config['mail_nobody'] ? ('No reply <' . $config['mail_nobody'] . '>') : ('No reply <noreply@' . $_SERVER['SERVER_NAME'] . '>');
			
			// Debugging
			//print('<PRE>') ;
			//print_r($info) ;
			//print_r($subject) ;
			//print_r($noreply) ;
			//print('</PRE>') ;
			
			$ret = $smtp->msg('email',$info['email'],$subject,$GLOBALS['egw']->template->fp('out','message'),'','','',$noreply);
			if ($ret != true)
				{
					print(lang("Problem Sending Email:").$smtp->desc) ;
					print(lang("<br>Please Contact the site administrator.")) ;
					exit() ;
				}
			return $error;
		}
	}
