<?php
	/**************************************************************************\
	* phpGroupWare - Registration                                              *
	* http://www.phpgroupware.org                                              *
	* This application written by Joseph Engo <jengo@phpgroupware.org>         *
	* Modified by Jason Wies (Zone) <zone@users.sourceforge.net>		   *
	* Modified by Loic Dachary <loic@gnu.org>				   *
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
			global $phpgw;

			$this->db = $phpgw->db;		
		}

		function account_exists($account_lid)
		{
			global $phpgw;

			$this->db->lock('phpgw_reg_accounts');
			$this->db->query("select count(*) from phpgw_reg_accounts where reg_lid='$account_lid'",__LINE__,__FILE__);
			$this->db->next_record();

			$phpgw->db->lock('phpgw_accounts');
			if ($phpgw->accounts->exists($account_lid) || $this->db->f(0))
			{
				$phpgw->db->unlock();
				$this->db->unlock();
				return True;
			}
			else
			{
				$phpgw->db->unlock();
				// To prevent race conditions, reserve the account_lid
				$this->db->query("insert into phpgw_reg_accounts values ('','$account_lid','','" . time() . "')",__LINE__,__FILE__);
				$this->db->unlock();
				$phpgw->session->appsession('loginid','registration',$account_lid);
				return False;
			}
		}

		function step2($fields)
		{
			global $phpgw_info, $phpgw, $config, $SERVER_NAME;

			$smtp = createobject('phpgwapi.send');

 
			// We are not going to use link(), because we may not have the same sessionid by that time
			// If we do, it will not affect it
			$url = $GLOBALS['phpgw_info']['server']['webserver_url'] . "/registration/main.php";

			$this->reg_id = md5(time() . $account_lid . $phpgw->common->randomstring(32));
			$account_lid  = $phpgw->session->appsession('loginid','registration');

			$phpgw->db->query("update phpgw_reg_accounts set reg_id='" . $this->reg_id . "', reg_dla='"
					. time() . "', reg_info='" . base64_encode(serialize($fields))
					. "' where reg_lid='$account_lid'",__LINE__,__FILE__);

			$phpgw->template->set_file(array(
				'message' => 'confirm_email.tpl'
			));

			if ($fields['n_given'])
			{
				$phpgw->template->set_var ('firstname', $fields['n_given'] . ' ');
			}

			if ($fields['n_family'])
			{
				$phpgw->template->set_var ('lastname', $fields['n_family'] . ' ');
			}

			$phpgw->template->set_var ('activate_url',$url . '?menuaction=registration.boreg.step4&reg_id='. $this->reg_id);

			if ($config['support_email'])
			{
				$phpgw->template->set_var ('support_email_text', lang ('Report all
roblems and abuse to'));
				$phpgw->template->set_var ('support_email', $config['support_email']);
			}

			$subject = $config['subject_confirm'] ? lang($config['subject_confirm']) : lang('Account registration');
		        $noreply = $config['mail_nobody'] ? ('No reply <' . $config['mail_nobody'] . '>') : ('No reply <noreply@' . $SERVER_NAME . '>');

			$smtp->msg('email',$fields['email'],$subject,$phpgw->template->fp('out','message'),'','','',$noreply);

			return $this->reg_id;
		}

		//
		// username
		//
		function lostpw1($account_lid)
		{
		  global $phpgw_info, $phpgw, $SERVER_NAME, $config;
		  
		  $url = $GLOBALS['phpgw_info']['server']['webserver_url'] . "/registration/main.php";

		  $error = '';

		  //
		  // Remember md5 string sent by mail
		  //
		  $reg_id = md5(time() . $account_lid . $phpgw->common->randomstring(32));
		  $this->db->query("insert into phpgw_reg_accounts values ('$reg_id','$account_lid','','" . time() . "')",__LINE__,__FILE__);

		  //
		  // Send the mail that will allow to change the password
		  //
		  $phpgw->db->query("select * from phpgw_accounts, phpgw_addressbook where account_lid='$account_lid' and phpgw_addressbook.lid='*$account_lid*'",__LINE__,__FILE__);
		  $phpgw->db->next_record();

		  $info = array(
				'firstname' => $phpgw->db->f('account_firstname'),
				'lastname' => $phpgw->db->f('account_lastname'),
				'email' => $phpgw->db->f('email')
				);
				
		  if ($phpgw->db->f('account_lid')) {

		    $smtp = createobject('phpgwapi.send');

		    $phpgw->template->set_file(array(
						     'message' => 'lostpw_email.tpl'
						     ));
		    $phpgw->template->set_var('firstname',$info['firstname']);
		    $phpgw->template->set_var('lastname',$info['lastname']);
		    $phpgw->template->set_var('activate_url',$url . '?menuaction=registration.boreg.lostpw2&reg_id=' . $reg_id);

		    $subject = $config['subject_lostpw'] ? lang($config['subject_lostpw']) : lang('Account password retrieval');
		    $noreply = $config['mail_nobody'] ? ('No reply <' . $config['mail_nobody'] . '>') : ('No reply <noreply@' . $SERVER_NAME . '>');

		    $smtp->msg('email',$info['email'],$subject,$phpgw->template->fp('out','message'),'','','',$noreply);
		  } else {
		    $error = "Account $account_lid record could not be found, report to site administrator";
		  }

		  return $error;
		}

		//
		// link sent by mail
		//
		function lostpw2($account_lid)
		{
		  global $phpgw;

		  $phpgw->db->query("select account_id from phpgw_accounts where account_lid='$account_lid'",__LINE__,__FILE__);
		  $phpgw->db->next_record();
		  $account_id = $phpgw->db->f('account_id');
		  
		  $phpgw->session->appsession('loginid','registration',$account_lid);
		  $phpgw->session->appsession('id','registration',$account_id);
		}

		//
		// new password
		//
		function lostpw3($account_lid, $passwd)
		{
		  global $phpgw;

		  $auth = createobject('phpgwapi.auth');
		  $auth->change_password('supposed to be old password', $passwd, $phpgw->session->appsession('id','registration'));
		  
		  $phpgw->db->query("delete from phpgw_reg_accounts where reg_lid='$account_lid'",__LINE__,__FILE__);
		}

		function valid_reg($reg_id)
		{
			global $phpgw;

			$phpgw->db->query("select * from phpgw_reg_accounts where reg_id='$reg_id'",__LINE__,__FILE__);
			$phpgw->db->next_record();

			if ($phpgw->db->f('reg_id'))
			{
				return array(
					'reg_id'   => $phpgw->db->f('reg_id'),
					'reg_lid'  => $phpgw->db->f('reg_lid'),
					'reg_info' => $phpgw->db->f('reg_info'),
					'reg_dla'  => $phpgw->db->f('reg_dla')
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
			global $phpgw, $phpgw_info, $config, $reg_info;

			$fields             = unserialize(base64_decode($_reg_info));
			$fields['lid'] = "*$account_lid*";

			$reg_info['lid']    = $account_lid;
			$reg_info['fields'] = $fields;

			$account_id = $phpgw_info['user']['account_id'] = $phpgw->accounts->auto_add($account_lid,$fields['passwd'],False,False,0,'A');

			if (!$account_id)
			{
				return False;
			}

			$accounts   = createobject('phpgwapi.accounts',$account_id);
			$contacts   = createobject('phpgwapi.contacts');

			$phpgw->db->transaction_begin();
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

			$contacts->add($account_id,$contact_fields,0,'P');

			$phpgw->db->transaction_commit();

			$accounts->read_repository();
			if ($config['trial_accounts'])
			{
				$accounts->data['expires'] = time() + ((60 * 60) * ($config['days_until_trial_account_expires'] * 24));
			}
			else
			{
				$accounts->data['expires'] = -1;
			}
			$accounts->data['status']  = 'A';
			$accounts->save_repository();

			include(PHPGW_SERVER_ROOT . '/messenger/inc/hook_registration.inc.php');
		}

	}