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

	class boreg
	{
		var $template;
		var $public_functions = array(
			'step1' => True,
			'step2' => True,
			'step4' => True
		);

		function boreg()
		{
		
		}

		function step1()
		{
			global $phpgw, $r_reg;

			$so = createobject('registration.soreg');

			if (! $r_reg['loginid'])
			{
				$errors[] = lang('You must enter a username');
			}

			if (! is_array($errors) && $so->account_exists($r_reg['loginid']))
			{
				$errors[] = lang('Sorry, that username is already used.');
			}

			$ui = createobject('registration.uireg');
			if (is_array($errors))
			{
				$ui->step1($errors,$r_reg,$o_reg);
			}
			else
			{
				$phpgw->session->appsession('loginid','registration',$r_reg['loginid']);
				$ui->step2();
			}
		}

		function step2()
		{
			global $phpgw, $r_reg, $o_reg;
			//echo '<pre>'; print_r($r_reg); echo '</pre>';

			if (! $r_reg['tos_agree'])
			{
				$missing_fields[] = 'tos_agree';
			}

			while (list($name,$value) = each($r_reg))
			{
				if (! $value)
				{
					$missing_fields[] = $name;
				}
				$fields[$name] = $value;
			}
			reset($r_reg);

			if ($r_reg['adr_one_countryname'] == '  ')
			{
				$missing_fields[] = 'adr_one_countryname';
			}

			if ($r_reg['passwd'] != $r_reg['passwd_confirm'])
			{
				$errors[] = lang("The passwords you entered don't match");
				$missing_fields[] = 'passwd';
				$missing_fields[] = 'passwd_confirm';
			}

			if ($r_reg['email'] && (! ereg('@',$r_reg['email']) || ! ereg('\.',$r_reg['email'])))
			{
				$errors[] = lang('You have entered an invaild email address');
			}

			if ($r_reg['bday_month'] || $r_reg['bday_day'] || $r_reg['bday_year'])
			{
				if (! checkdate($r_reg['bday_month'],$r_reg['bday_day'],$r_reg['bday_year']))
				{
					$errors[]          = lang('You have entered an invalid birthday');
					$missing_fields[] = 'bday';
				}
				else
				{
					$r_reg['bday'] = sprintf('%s/%s/%s',$r_reg['bday_month'],$r_reg['bday_day'],$r_reg['bday_year']);
				}
			}
			else
			{
				$missing_fields[] = 'bday';
			}

			while (is_array($o_reg) && list($name,$value) = each($o_reg))
			{
				$fields[$name] = $value;
			}
			reset($o_reg);

			if (is_array($missing_fields))
			{
				$errors[] = lang('You must fill in all of the required fields');
			}

			if (! is_array($errors))
			{
				$so     = createobject('registration.soreg');
				$reg_id = $so->step2($fields);
			}

			$ui = createobject('registration.uireg');
			if (is_array($errors))
			{
				$ui->step2($errors,$r_reg,$o_reg,$missing_fields);
			}
			else
			{
				// Redirect them so they don't hit refresh and make a mess
				$phpgw->redirect($phpgw->link('/registration/main.php','menuaction=registration.uireg.email_sent'));
			}
		}

		function step4()
		{
			global $reg_id;

			$so = createobject('registration.soreg');
			$ui = createobject('registration.uireg');
			$reg_info = $so->valid_reg($reg_id);

			if (! is_array($reg_info))
			{
				$ui->simple_screen('error_confirm.tpl');
				return False;
			}

			$so->create_account($reg_info['reg_lid'],$reg_info['reg_info']);
			$so->delete_reg_info($reg_id);
			$ui->welcome_screen();
		}

	}