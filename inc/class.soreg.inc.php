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

		function soreg()
		{
			$this->db = $GLOBALS['phpgw']->db;
		}

		function account_exists($account_lid)
		{
			$this->db->lock('phpgw_reg_accounts');
			$this->db->query("select `reg_dla` from phpgw_reg_accounts where reg_lid='$account_lid'",__LINE__,__FILE__);
			$this->db->next_record();

			$GLOBALS['phpgw']->db->lock('phpgw_accounts');
			
//			echo (time()-$this->db->f(0));
//			echo "<br>";
//			echo time();
			
			if (  $GLOBALS['phpgw']->accounts->exists($account_lid) || ( $this->db->f(0) && (time()-$this->db->f(0))<1800  ))
			{
				$GLOBALS['phpgw']->db->unlock();
				$this->db->unlock();
				return True;
			}
			else
			{
				$GLOBALS['phpgw']->db->unlock();
				// To prevent race conditions, reserve the account_lid
				$this->db->query("insert into phpgw_reg_accounts values ('','$account_lid','','" . time() . "')",__LINE__,__FILE__);
				$this->db->unlock();
				$GLOBALS['phpgw']->session->appsession('loginid','registration',$account_lid);
				return False;
			}
		}

		function step2($fields,$send_mail=True)
		{
			global $config;

			$smtp = createobject('phpgwapi.send');

			// We are not going to use link(), because we may not have the same sessionid by that time
			// If we do, it will not affect it
			$url = $GLOBALS['phpgw_info']['server']['webserver_url'] . "/registration/main.php";
			if (substr($url,0,4) != 'http')
			{
				$url = ($_SERVER['HTTPS'] ? 'https://' : 'http://') . $url;
			}
			$this->reg_id = md5(time() . $account_lid . $GLOBALS['phpgw']->common->randomstring(32));
			$account_lid  = $GLOBALS['phpgw']->session->appsession('loginid','registration');

			$GLOBALS['phpgw']->db->query("update phpgw_reg_accounts set reg_id='" . $this->reg_id . "', reg_dla='"
				. time() . "', reg_info='" . base64_encode(serialize($fields))
				. "' where reg_lid='$account_lid'",__LINE__,__FILE__);
	
			$GLOBALS['phpgw']->template->set_file(array(
				'message' => 'confirm_email.tpl'
			));
			
			$GLOBALS['phpgw']->template->set_var('Hi',lang('Hi'));
			$GLOBALS['phpgw']->template->set_var('message1',lang('This is a confirmation email for your new account.  Click on the following link to finish activating your account. This link will expire in 2 hours.'));

			$GLOBALS['phpgw']->template->set_var('message2',lang('If you did not request this account, simply ignore this message.'));

			if ($fields['n_given'])
			{
				$GLOBALS['phpgw']->template->set_var ('firstname', $fields['n_given'] . ' ');
			}

			if ($fields['n_family'])
			{
				$GLOBALS['phpgw']->template->set_var ('lastname', $fields['n_family']);
			}

			$GLOBALS['phpgw']->template->set_var ('activate_url',$url . '?menuaction=registration.boreg.step4&reg_id='. $this->reg_id);

			if ($config['support_email'])
			{
				$GLOBALS['phpgw']->template->set_var ('support_email_text', lang ('Report all problems and abuse to'));
				$GLOBALS['phpgw']->template->set_var ('support_email', $config['support_email']);
			}

			$subject = $config['subject_confirm'] ? lang($config['subject_confirm']) : lang('Account registration');
			$noreply = $config['mail_nobody'] ? ('No reply <' . $config['mail_nobody'] . '>') : ('No reply <noreply@' . $_SERVER['SERVER_NAME'] . '>');

			if ($send_mail)
			{
				$smtp->msg('email',$fields['email'],$subject,$GLOBALS['phpgw']->template->fp('out','message'),'','','',$noreply);
			}
			return $this->reg_id;
		}

		//
		// username
		//
		function lostpw1($account_lid)
		{
			global $config;

//			$url = $GLOBALS['phpgw_info']['server']['webserver_url'] . "/registration/main.php";
 			$url = ($_SERVER['HTTPS'] ? 'https://' : 'http://').$GLOBALS['phpgw_info']['server']['hostname'] . "/registration/main.php";

			$error = '';

			//
			// Remember md5 string sent by mail
			//
			$reg_id = md5(time() . $account_lid . $GLOBALS['phpgw']->common->randomstring(32));
			$this->db->query("insert into phpgw_reg_accounts values ('$reg_id','$account_lid','','" . time() . "')",__LINE__,__FILE__);

			//
			// Send the mail that will allow to change the password
			//
			$GLOBALS['phpgw']->db->query("select * from phpgw_accounts, phpgw_addressbook where account_lid='$account_lid' and phpgw_addressbook.lid='*$account_lid*'",__LINE__,__FILE__);
			$GLOBALS['phpgw']->db->next_record();

			$info = array(
				'firstname' => $GLOBALS['phpgw']->db->f('account_firstname'),
				'lastname' => $GLOBALS['phpgw']->db->f('account_lastname'),
				'email' => $GLOBALS['phpgw']->db->f('email')
			);

			if ($GLOBALS['phpgw']->db->f('account_lid'))
			{
				$smtp = createobject('phpgwapi.send');

				$GLOBALS['phpgw']->template->set_file(array(
					'message' => 'lostpw_email.tpl'
				));

				$GLOBALS['phpgw']->template->set_var('hi',lang('Hi'));
				$GLOBALS['phpgw']->template->set_var('message1',lang('You requested to change your password. Please follow the URL below to do so. This URL will expire in two hours. After this delay you should go thru the lost password procedure again.'));
				
				$GLOBALS['phpgw']->template->set_var('message2',lang('If you did not request this change, simply ignore this message.'));

				$GLOBALS['phpgw']->template->set_var('firstname',$info['firstname']);
				$GLOBALS['phpgw']->template->set_var('lastname',$info['lastname']);
				$GLOBALS['phpgw']->template->set_var('activate_url',$url . '?menuaction=registration.boreg.lostpw2&reg_id=' . $reg_id);

				$subject = $config['subject_lostpw'] ? lang($config['subject_lostpw']) : lang('Account password retrieval');
				$noreply = $config['mail_nobody'] ? ('No reply <' . $config['mail_nobody'] . '>') : ('No reply <noreply@' . $_SERVER['SERVER_NAME'] . '>');

				$smtp->msg('email',$info['email'],$subject,$GLOBALS['phpgw']->template->fp('out','message'),'','','',$noreply);
			}
			else
			{
				$error = "Account $account_lid record could not be found, report to site administrator";
			}

			return $error;
		}

		//
		// link sent by mail
		//
		function lostpw2($account_lid)
		{
			$GLOBALS['phpgw']->db->query("select account_id from phpgw_accounts where account_lid='$account_lid'",__LINE__,__FILE__);
			$GLOBALS['phpgw']->db->next_record();
			$account_id = $GLOBALS['phpgw']->db->f('account_id');

			$GLOBALS['phpgw']->session->appsession('loginid','registration',$account_lid);
			$GLOBALS['phpgw']->session->appsession('id','registration',$account_id);
		}

		//
		// new password
		//
		function lostpw3($account_lid, $passwd)
		{
			$auth = createobject('phpgwapi.auth');
			$auth->change_password('supposed to be old password', $passwd, $GLOBALS['phpgw']->session->appsession('id','registration'));

			$GLOBALS['phpgw']->db->query("delete from phpgw_reg_accounts where reg_lid='$account_lid'",__LINE__,__FILE__);
		}

		function valid_reg($reg_id)
		{
			$GLOBALS['phpgw']->db->query("select * from phpgw_reg_accounts where reg_id='$reg_id'",__LINE__,__FILE__);
			$GLOBALS['phpgw']->db->next_record();

			if ($GLOBALS['phpgw']->db->f('reg_id'))
			{
				return array(
					'reg_id'   => $GLOBALS['phpgw']->db->f('reg_id'),
					'reg_lid'  => $GLOBALS['phpgw']->db->f('reg_lid'),
					'reg_info' => $GLOBALS['phpgw']->db->f('reg_info'),
					'reg_dla'  => $GLOBALS['phpgw']->db->f('reg_dla')
				);
			}
			else
			{
				echo False;
			}
		}

		function delete_reg_info($reg_id)
		{
			$this->db->query("delete from phpgw_reg_accounts where reg_id='$reg_id'",__LINE__,__FILE__);
		}

		function create_account($account_lid,$_reg_info)
		{
			global $config, $reg_info;

			$fields = unserialize(base64_decode($_reg_info));
			$fields['lid'] = "*$account_lid*";
			//$fields['lid'] = $account_lid;


			$reg_info['lid']    = $account_lid;
			$reg_info['fields'] = $fields;

			$account_id = $GLOBALS['phpgw_info']['user']['account_id'] = $GLOBALS['phpgw']->accounts->auto_add($account_lid,$fields['passwd'],True,False,0,'A');

			if (!$account_id)
			{
				return False;
			}

			//var_dump($account_id);
			$accounts   = createobject('phpgwapi.accounts',$account_id);
			$contacts   = createobject('phpgwapi.contacts');

			$GLOBALS['phpgw']->db->transaction_begin();
			$accounts->read_repository();
			$accounts->data['firstname'] = $fields['n_given'];
			$accounts->data['lastname']  = $fields['n_family'];
			$accounts->save_repository();

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
			reset ($contact_fields);
			while (list ($num, $field) = each ($contact_fields))
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

			$GLOBALS['phpgw']->db->transaction_commit();

			$accounts->read_repository();
			if ($config['trial_accounts'])
			{
				$accounts->data['expires'] = time() + ((60 * 60) * ($config['days_until_trial_account_expires'] * 24));
			}
			else
			{
				$accounts->data['expires'] = -1;
			}
			$accounts->data['status'] = 'A';
			$accounts->data['email'] = $fields['email'];
			$accounts->save_repository();

			#if(@stat(PHPGW_SERVER_ROOT . '/messenger/inc/hook_registration.inc.php'))
			#{
			#	include(PHPGW_SERVER_ROOT . '/messenger/inc/hook_registration.inc.php');
			#}
		}
		function getuserids($email)
		{
			$account_lid = null ;
			$GLOBALS['phpgw']->db->query("select account_lid from phpgw_accounts where account_email='$email'") ;
			while($GLOBALS['phpgw']->db->next_record())
			{
			
			$account_lid[] = $GLOBALS['phpgw']->db->f('account_lid') ;
			}
			return $account_lid ;
		}
	
		function lostid1($email)
		{
			global $config;
			
			$userids = $this->getuserids($email) ;
			
			$url = ($_SERVER['HTTPS'] ? 'https://' : 'http://').$GLOBALS['phpgw_info']['server']['hostname'] . "/registration/main.php";

			$error = '';

			$smtp = createobject('phpgwapi.send');

			$GLOBALS['phpgw']->template->set_file(array('message' => 'lostid_email.tpl'));

			// First find the first user in the address book with that account
			$qrystmt = "SELECT * FROM phpgw_accounts,phpgw_addressbook WHERE phpgw_addressbook.owner=phpgw_accounts.account_id AND phpgw_accounts.account_email='$email' AND phpgw_addressbook.lid='*". $userids[1]."*'" ;

			$GLOBALS['phpgw']->db->query($qrystmt,__LINE__,__FILE__);
			$GLOBALS['phpgw']->db->next_record() ;
			$info = array(
				'firstname' => $GLOBALS['phpgw']->db->f('account_firstname'),
				'lastname' => $GLOBALS['phpgw']->db->f('account_lastname'),
				'email' => $email,
			) ;
			
			// Go thru the usereds and find the names, 
			for ($icount = 1 ; $icount < count($userids) ; $icount++)
			{
				$qrystmt ="SELECT * FROM phpgw_addressbook WHERE lid='*$userids[$icount]*'" ;
				$GLOBALS['phpgw']->db->query($qrystmt,__LINE__,__FILE__);
				$GLOBALS['phpgw']->db->next_record() ;
				
				// Hack to find a name
				if ((is_null($info['firstname'])) || (is_null($info['lastname'])))
				{
					$info['firstname'] = $GLOBALS['phpgw']->db->f('account_firstname') ;
					$info['lastname'] = $GLOBALS['phpgw']->db->f('account_lastname') ;
				}
				$useridlist = $useridlist . $GLOBALS['phpgw']->db->f('n_given') . ' ' . $GLOBALS['phpgw']->db->f('n_family') . ' = ' . $userids[$icount] . "\n" ;
			}
			if (is_null($info['firstname']))
				$info['firstname'] = lang('[Unknown first name]') ;
				
			if (is_null($info['lastname']))
				$info['lastname'] = lang('[Unknown last name]') ;
			
			$GLOBALS['phpgw']->template->set_var('hi',lang('Hi'));
			$GLOBALS['phpgw']->template->set_var('firstname',$info['firstname']);
			$GLOBALS['phpgw']->template->set_var('lastname',$info['lastname']);
			$GLOBALS['phpgw']->template->set_var('message1', lang('lost_user_id_message'));
					
			// Send the mail that tell the user id
			$GLOBALS['phpgw']->template->set_var('lostids',$useridlist) ;
			
			$subject = $config['subject_lostid'] ? lang($config['subject_lostpid']) : lang('Lost user account retrieval');
			$noreply = $config['mail_nobody'] ? ('No reply <' . $config['mail_nobody'] . '>') : ('No reply <noreply@' . $_SERVER['SERVER_NAME'] . '>');
			
			// Debugging
			//print('<PRE>') ;
			//print_r($info) ;
			//print_r($subject) ;
			//print_r($noreply) ;
			//print('</PRE>') ;
			
			$ret = $smtp->msg('email',$info['email'],$subject,$GLOBALS['phpgw']->template->fp('out','message'),'','','',$noreply);
			if ($ret != true)
				$error = lang('unable to send email, contact your administrator.') ;
			return $error;
		}
	}
