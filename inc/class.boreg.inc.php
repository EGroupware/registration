<?php
	/**************************************************************************\
	* eGroupWare - Registration                                                *
	* http://www.eGroupWare.org                                                *
	* This application written by Joseph Engo <jengo@phprgoupware.org>         *
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

	class boreg
	{
		var $template;
		var $bomanagefields;
		var $fields;
		var $so;
		var $lang_code;
		var $reg_id;
		var $public_functions = array(
			'lostpw1' => True,
			'lostpw2' => True,
			'lostpw3' => True,
			'lostid1' => True,
		);

		function boreg()
		{
			$this->so =& CreateObject ('registration.soreg');
			$this->bomanagefields =& CreateObject ('registration.bomanagefields');
			$this->fields = $this->bomanagefields->get_field_list ();

			$_reg_id=$_GET['reg_id']?$_GET['reg_id']:$_POST['reg_id'];
			$this->reg_id=$_reg_id?$_reg_id:'';
		}

		function step2_validate_fields()
		{
			global $config, $r_reg, $o_reg;

			$r_reg=$_POST['r_reg'];
			$o_reg=$_POST['o_reg'];

			if ($config['password_is'] == 'http')
			{
				$r_reg['passwd'] = $r_reg['passwd_confirm'] = $_SERVER['PHP_AUTH_PW'];
			}

			if (($config['display_tos']) && ! $r_reg['tos_agree'])
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

			reset ($this->fields);
			while (list (,$field_info) = each ($this->fields))
			{
				$name = $field_info['field_name'];
				$text = $field_info['field_text'];
				$values = explode (',', $field_info['field_values']);
				$required = $field_info['field_required'];
				$type = $field_info['field_type'];

				if ($required == 'Y')
				{
					$a = $r_reg;
				}
				else
				{
					$a = $o_reg;
				}

				$post_value = $a[$name];

				if ($type == 'email')
				{
					if ($post_value && (!ereg ('@', $post_value) || ! ereg ("\.", $post_value)))
					{
						if ($required == 'Y')
						{
							$errors[] = lang('You have entered an invalid email address');
							$missing_fields[] = $name;
						}
					}
				}

				if ($type == 'birthday')
				{
					if (!checkdate ((int) $a[$name . '_month'], (int) $a[$name . '_day'], (int) $a[$name . '_year']))
					{
						if ($required == 'Y')
						{
							$errors[] = lang ('You have entered an invalid birthday');
							$missing_fields[] = $name;
						}
					}
					else
					{
							$a[$name] = sprintf ('%s/%s/%s', $a[$name . '_month'], $a[$name . '_day'], $a[$name . '_year']);
					}
				}

				if ($type == 'dropdown')
				{
					if ($post_value)
					{
						while (list (,$value) = each ($values))
						{
							if ($value == $post_value)
							{
								$ok = 1;
							}
						}

						if (!$ok)
						{
							$errors[] = lang ('You specified a value for %1 that is not a choice',$text);

							$missing_fields[] = $name;
						}
					}
				}
			}

			while (is_array($o_reg) && list($name,$value) = each($o_reg))
			{
				$fields[$name] = $value;
			}

			if (is_array ($o_reg))
			{
				reset($o_reg);
			}

			if (is_array($missing_fields))
			{
				$errors[] = lang('You must fill in all of the required fields');
			}

/*			if (! is_array($errors))
			{
				//$so     =& CreateObject('registration.soreg');
				// only send mail if activation requires it
			 }
*/
			if (is_array($errors))
			{
			   $ret_arr['errors']=$errors;
			   $ret_arr['r_reg']=$r_reg;
			   $ret_arr['o_reg']=$o_reg;
			   $ret_arr['missing_fields']=$missing_fields;
			}
			else
			{
			   $ret_arr['reg_id'] = $this->so->sendActivationLink($fields,$config['activate_account'] == 'email',$lang_to_pass);
			}

			return $ret_arr;
		}

		//
		// username
		//
		function lostpw1()
		{
			//var $r_reg;

			$r_reg = $_REQUEST['r_reg'] ;
			$so =& CreateObject('registration.soreg');
			
			if (! $r_reg['loginid'])
			{
				$errors[] = lang('You must enter a username');
			}
			if (! is_array($errors) && !$GLOBALS['egw']->accounts->exists($r_reg['loginid']))
			{
				$errors[] = lang('Sorry, that username does not exist.');
			}

			if(! is_array($errors))
			{
							$error = $so->lostpw1($r_reg['loginid']);
				if($error)
				{
					$errors[] = $error;
				}
			}
			
			$ui =& CreateObject('registration.uireg');
			if (is_array($errors))
			{
				$ui->lostpw1($errors,$r_reg);
			}
			else
			{
				// Redirect them so they don't hit refresh and make a mess
				$GLOBALS['egw']->redirect($GLOBALS['egw']->link('/registration/index.php','menuaction=registration.uireg.email_sent_lostpw'));
			}
		}

		//
		// link sent by mail
		//
		function lostpw2()
		{

			$so =& CreateObject('registration.soreg');
			$ui =& CreateObject('registration.uireg');
			$reg_info = $so->valid_reg($this->reg_id);

			if (! is_array($reg_info))
			{
				$ui->simple_screen('error_confirm.tpl');
				return False;
			}

			$so->lostpw2($reg_info['reg_lid']);

			$ui->lostpw3('', '', $reg_info['reg_lid']);
			return True;
		}

		//
		// new password
		//
		function lostpw3()
		{
			//var $r_reg;
			$r_reg = $_REQUEST['r_reg'] ;

			$lid = $GLOBALS['egw']->session->appsession('loginid','registration');
			if(!$lid) {
				$error[] = lang('Wrong session');
			}

			if ($r_reg['passwd'] != $r_reg['passwd_2'])
			{
					$errors[] = lang('The two passwords are not the same');
			}

			if (! $r_reg['passwd'])
			{
					$errors[] = lang('You must enter a password');
			}

			if(! is_array($errors))
			{
				$so =& CreateObject('registration.soreg');
				$so->lostpw3($lid, $r_reg['passwd']);
			}

			$ui =& CreateObject('registration.uireg');

			if (is_array($errors))
			{
				$ui->lostpw3($errors, $r_reg, $lid);
			} 
			else
			{
				$ui->lostpw4();
			}

			return True;
		}

		function check_select_username ()
		{
			global $config;

			if ($config['username_is'] == 'choice')
			{
				return True;
			}
			elseif ($config['username_is'] == 'http')
			{
				if (!$_SERVER['PHP_AUTH_USER'])
				{
					return "HTTP username is not set";
				}
				else
				{
					$GLOBALS['egw']->redirect($GLOBALS['egw']->link ('/registration/index.php', 'menuaction=registration.uireg.step1_validate&r_reg[loginid]=' . $_SERVER['PHP_AUTH_USER']));
				}
			}

			return True;
		}
		
		function lostid1()
		{
			//var $config;
			$r_reg = $_REQUEST['r_reg'] ;
			$so =& CreateObject('registration.soreg');
			
			if (! $r_reg['email'])
			{
				$errors[] = lang('You must enter an email account');
			}
			if (! is_array($errors))
			{
				$userids=$GLOBALS['egw']->accounts->name2id($email,'account_email');
				if (count($userids) == 0)
				{
					$errors[] = lang('Sorry, no account exists for '.$r_reg['email']);
				}
			}

			if(! is_array($errors))
			{
							$error = $so->lostid1($r_reg['email']);
				if($error)
				{
					$errors[] = $error;
				}
			}
			
			$ui =& CreateObject('registration.uireg');
			if (is_array($errors))
			{
				$ui->lostid1($errors,$r_reg);
			}
			else
			{
				$ui->email_sent_lostid($r_reg['email']);
			}
		}
		
		function check_select_password ()
		{
			global $config;

			if ($config['password_is'] == 'choice')
			{
				return True;
			}
			elseif ($config['password_is'] == 'http')
			{
				if (!$_SERVER['PHP_AUTH_PW'])
				{
					return "HTTP password is not set";
				}
				else
				{
					return False;
				}
			}

			return True;
		}
		
		function check_challenge()
		{
			return True;
		}
	}
