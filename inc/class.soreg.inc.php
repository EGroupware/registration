<?php
	/**************************************************************************\
	* phpGroupWare - Registration                                              *
	* http://www.phpgroupware.org                                              *
	* This application written by Joseph Engo <jengo@phpgroupware.org>         *
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

			if ($phpgw->accounts->exists($account_lid) || $this->db->f(0))
			{
				$this->db->unlock();
				return True;
			}
			else
			{
				// To prevent race conditions, reserve the account_lid
				$this->db->query("insert into phpgw_reg_accounts values ('','$account_lid','','" . time() . "')",__LINE__,__FILE__);
				$this->db->unlock();
				$phpgw->session->appsession('loginid','registration',$account_lid);
				return False;
			}
		}

		function step2($fields)
		{
			global $phpgw_info, $phpgw, $SERVER_NAME, $PHP_SELF;

			$smtp = createobject('phpgwapi.send');

			// We are not going to use link(), becuase we may not have the same sessionid by that time
			// If we do, it will not affect it
			if ($HTTP_SERVER_VARS['HTTPS'])
			{
				$url = 'https://';
			}
			else
			{
				$url = 'http://';
			}
			$url .= $SERVER_NAME . $PHP_SELF;

			$this->reg_id = md5(time() . $account_lid . $phpgw->common->randomstring(32));
			$account_lid  = $phpgw->session->appsession('loginid','registration');

			$phpgw->db->query("update phpgw_reg_accounts set reg_id='" . $this->reg_id . "', reg_dla='"
					. time() . "', reg_info='" . base64_encode(serialize($fields))
					. "' where reg_lid='$account_lid'",__LINE__,__FILE__);

			$phpgw->template->set_file(array(
				'message' => 'confirm_email.tpl'
			));
			$phpgw->template->set_var('firstname',$fields['n_given']);
			$phpgw->template->set_var('lastname',$fields['n_family']);
			$phpgw->template->set_var('activate_url',$url . '?menuaction=registration.boreg.step4&reg_id=' . $this->reg_id);

			$smtp->msg('email',$fields['email'],lang('Account registration'),$phpgw->template->fp('out','message'),'','','','No reply <noreply@' . $SERVER_NAME . '>');

			return $this->reg_id;
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
			global $config, $phpgw, $reg_info;

			$fields             = unserialize(base64_decode($_reg_info));
			$reg_info['lid']    = $account_lid;
			$reg_info['fields'] = $fields;

			$account_id = $phpgw->accounts->auto_add($account_lid,$fields['passwd'],False,False,0,'A');
			$accounts   = createobject('phpgwapi.accounts',$account_id);
			$contacts   = createobject('phpgwapi.contacts');

			$phpgw->db->transaction_begin();
			$accounts    = createobject('phpgwapi.accounts',$account_id);
			$accounts->read_repository();
			$accounts->data['firstname'] = $fields['n_given'];
			$accounts->data['lastname']  = $fields['n_family'];
			$accounts->save_repository();

			$contacts->add($account_id,$fields,0,'P');

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