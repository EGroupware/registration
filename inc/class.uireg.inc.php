<?php
	/**************************************************************************\
	* phpGroupWare - Registration                                              *
	* http://www.phpgroupware.org                                              *
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

	class uireg
	{
		var $template;
		var $bomanagefields;
		var $fields;
		var $bo;
		var $lang_code;
		var $public_functions = array(
			'welcome_screen' => True,
			'step1'   => True,
			'step2'   => True,
			'lostpw1' => True,
			'lostpw3' => True,
			'lostpw4' => True,
			'ready_to_activate' => True,
			'email_sent_lostpw' => True,
			'tos'     => True
		);

		function uireg()
		{
			$this->template = $GLOBALS['phpgw']->template;
			$this->bo = createobject ('registration.boreg');
			$this->bomanagefields = createobject ('registration.bomanagefields');
			$this->fields = $this->bomanagefields->get_field_list ();

			$this->set_lang_code();
		}

		function set_lang_code($code='')
		{
			if($code)
			{
				$this->lang_code=$code;
			}
			elseif(strlen($GLOBALS[lang_code])==2)
			{
				$this->lang_code=$GLOBALS[lang_code];
			}
			else
			{
				$this->lang=$GLOBALS[HTTP_POST_VARS][lang_code];
			}

			if ($this->lang_code)
			{

				$GLOBALS['phpgw_info']['user']['preferences']['common']['lang'] = $this->lang_code;
				$GLOBALS['phpgw']->translation->init();	
			}

		}

		function set_header_footer_blocks()
		{
			$this->template->set_file(array(
				'_layout' => 'layout.tpl'
			));
			$this->template->set_block('_layout','header');
			$this->template->set_block('_layout','footer');
		}

		function header($head_subj='')
		{
			$this->set_header_footer_blocks();

			if($head_subj)
			{
				$this->template->set_var('lang_header',$head_subj);
			}
			else
			{
				$this->template->set_var('lang_header',lang('eGroupWare - Account registration'));
			}

			$this->template->pfp('out','header');
		}

		function footer()
		{
			$this->template->pfp('out','footer');
		}

		function create_option_string($selected,$values)
		{
			while (is_array($values) && list($var,$value) = each($values))
			{
				$s .= '<option value="' . $var . '"';
				if ("$var" == "$selected")	// the "'s are necessary to force a string-compare
				{
					$s .= ' selected';
				}
				$s .= '>' . $value . '</option>';
			}
			return $s;
		}
		function step1($errors = '',$r_reg = '',$o_reg = '')
		{
			global $config;

			if($config['enable_registration']!="True")
			{
				$this->header();
				echo '<br/><div align="center">';	
				echo lang('On-line registration is not activated. Please contact the site administrator for more information about registration.');
				echo '</div><br/>';
				$this->footer();
				exit;
			}


			if ($errors && $config['username_is'] == 'http')
			{
				$vars[message]=	lang('An error occured. Please contact our technical support and let them know.');
				$this->simple_screen ('error_general.tpl', $GLOBALS['phpgw']->common->error_list ($errors),$vars);
			}

			/* Note that check_select_username () may not return */
			$select_username = $this->bo->check_select_username ();
			if (!$select_username || is_string ($select_username))
			{
				$vars[message]=	lang('An error occured. Please contact our technical support and let them know.');
				$this->simple_screen ('error_general.tpl', $GLOBALS['phpgw']->common->error_list (array ($select_username)),$vars);
			}

			$this->header();
			$this->template->set_file(array(
				'_loginid_select' => 'loginid_select.tpl'
			));
			$this->template->set_block('_loginid_select','form');

			if ($errors)
			{
				$this->template->set_var('errors',$GLOBALS['phpgw']->common->error_list($errors));
			}

			// temporary set all available langcodes
			$langs = $GLOBALS['phpgw']->translation->get_installed_langs();
			$comeback_code=$this->lang_code;
			foreach ($langs as $key => $name)	// if we have a translation use it
			{
				unset($choosetrans);
				$this->set_lang_code($key);
				
		//		$lang_choose_string.=$key.lang('Choose your language');
				$choosetrans=lang('Choose your language');
			
				if($choosetrans!='Choose your language*' && $choosetrans!=$prevstring)
				{
					if($lang_choose_string) $lang_choose_string .='<br/> ';
					$lang_choose_string .=$choosetrans;
					$prevstring=$choosetrans;
				}
					
				$trans = lang($name);
				if ($trans != $name . '*')
				{
					$langs[$key] = $trans;
				}
			} 
			$this->set_lang_code($comeback_code);
			
			$this->template->set_var('lang_choose_language',$lang_choose_string);


			$s .= $this->create_option_string($this->lang_code,$langs);
			$this->template->set_var('selectbox_languages','<select name="lang_code" onChange="this.form.langchanged.value=\'true\';this.form.submit()">'.$s.'</select>');





			$this->template->set_var('form_action',$GLOBALS['phpgw']->link('/registration/main.php','menuaction=registration.boreg.step1'));
			$this->template->set_var('lang_username',lang('Username'));
			$this->template->set_var('lang_submit',lang('Submit'));

			$this->template->pfp('out','form');

			$this->footer();
		}

		function step2($errors = '',$r_reg = '',$o_reg = '',$missing_fields='')
		{
			global $config;

			$show_password_prompt = True;
			$select_password = $this->bo->check_select_password ();
			if (is_string ($select_password))
			{
				$vars[message]=	lang('An error occured. Please contact our technical support and let them know.');
				$this->simple_screen ('error_general.tpl', $select_password,$vars);
			}
			elseif (!$select_password)
			{
				$show_password_prompt = False;
			}

			$this->header();
			$this->template->set_file(array(
				'_personal_info' => 'personal_info.tpl'
			));
			$this->template->set_block('_personal_info','form');
			$this->template->set_var('lang_code',$this->lang_code);

			if ($errors)
			{
				$this->template->set_var('errors',$GLOBALS['phpgw']->common->error_list($errors));
			}

			if ($missing_fields)
			{
				while (list(,$field) = each($missing_fields))
				{
					$missing[$field] = True;
					$this->template->set_var('missing_' . $field,'<font color="#CC0000">*</font>');
				}
			}

			if (is_array($r_reg))
			{
				while (list($name,$value) = each($r_reg))
				{
					$post_values[$name] = $value;
					$this->template->set_var('value_' . $name,$value);
				}
			}

			if (is_array($o_reg))
			{
				while (list($name,$value) = each($o_reg))
				{
					$post_values[$name] = $value;
					$this->template->set_var('value_' . $name,$value);
				}
			}

			$this->template->set_var('form_action',$GLOBALS['phpgw']->link('/registration/main.php','menuaction=registration.boreg.step2'));
			$this->template->set_var('lang_password',lang('Password'));
			$this->template->set_var('lang_reenter_password',lang('Re-enter password'));
			$this->template->set_var('lang_submit',lang('Submit'));

			if (!$show_password_prompt)
			{
				$this->template->set_block ('form', 'password', 'empty');
			}

			$this->template->set_block ('form', 'other_fields_proto', 'other_fields_list');

			reset ($this->fields);
			while (list ($num, $field_info) = each ($this->fields))
			{
				$input_field = $this->get_input_field ($field_info, $post_values);
				$var = array (
					'missing_indicator' => $missing[$field_info['field_name']] ? '<font color="#CC0000">*</font>' : '',
					'bold_start'  => $field_info['field_required'] == 'Y' ? '<b>' : '',
					'bold_end'    => $field_info['field_required'] == 'Y' ? '</b>' : '',
					'lang_displayed_text' => lang ($field_info['field_text']),
					'input_field' => $input_field
				);

				$this->template->set_var ($var);

				$this->template->parse ('other_fields_list', 'other_fields_proto', True);
			}

			if ($config['display_tos'])
			{
				$this->template->set_var('tos_link',$GLOBALS['phpgw']->link('/registration/main.php','menuaction=registration.uireg.tos'));
				$this->template->set_var('lang_tos_agree',lang('I have read the terms and conditions and agree by them.'));
				if ($r_reg['tos_agree'])
				{
					$this->template->set_var('value_tos_agree', 'checked');
				}
			}
			else
			{
				$this->template->set_block ('form', 'tos', 'blank');
			}

			$this->template->pfp('out','form');
			$this->footer();
		}

		//
		// username
		//
		function lostpw1($errors = '',$r_reg = '')
		{
			$this->header();
			$this->template->set_file(array(
				'_lostpw_select' => 'lostpw_select.tpl'
			));
			$this->template->set_block('_lostpw_select','form');

			if ($errors)
			{
				$this->template->set_var('errors',$GLOBALS['phpgw']->common->error_list($errors));
			}

			$this->template->set_var('form_action',$GLOBALS['phpgw']->link('/registration/main.php','menuaction=registration.boreg.lostpw1'));
			$this->template->set_var('lang_explain',lang('After you enter your username, instructions to change your password will be sent to you by e-mail to the address you gave when you registered.'));
			$this->template->set_var('lang_username',lang('Username'));
			$this->template->set_var('lang_submit',lang('Submit'));

			$this->template->pfp('out','form');
			$this->footer();
		}

		//
		// change password
		//
		function lostpw3($errors = '',$r_reg = '',$lid = '')
		{
			$this->header();
			$this->template->set_file(array(
				'_lostpw_change' => 'lostpw_change.tpl'
			));
			$this->template->set_block('_lostpw_change','form');

			if ($errors)
			{
				$this->template->set_var('errors',$GLOBALS['phpgw']->common->error_list($errors));
			}

			$this->template->set_var('form_action',$GLOBALS['phpgw']->link('/registration/main.php','menuaction=registration.boreg.lostpw3'));
			$this->template->set_var('value_username', $lid);
			$this->template->set_var('lang_changepassword',lang("Change password for user"));
			$this->template->set_var('lang_enter_password',lang('Enter your new password'));
			$this->template->set_var('lang_reenter_password',lang('Re-enter your password'));
			$this->template->set_var('lang_change',lang('Change'));

			$this->template->pfp('out','form');
			$this->footer();
		}

		//
		// password was changed
		//
		function lostpw4()
		{
			$this->header();
			$this->template->set_file(array(
				'screen' => 'lostpw_changed.tpl'
			));

			$message=lang('Your password was changed. You can go back to the <a href="%1">login</a> page.',$GLOBALS['phpgw_info']['server']['webserver_url']);

			$this->template->set_var('message',$message);

			$this->template->pfp('out','screen');
			$this->footer();
		}

		function get_input_field ($field_info, $post_values)
		{
			global $r_regs, $o_regs;

			$post_value = $post_values[$field_info['field_name']];

			$name = $field_info['field_name'];
			$values = explode (",", $field_info['field_values']);
			$required = $field_info['field_required'];
			$type = $field_info['field_type'];

			if (!$type)
			{
				$type = 'text';
			}

			if ($type == 'gender')
			{
				$values = array (
					lang('Male'),
					lang('Female')
				);

				$type = 'dropdown';
			}

			if ($required == 'Y')
			{
				$a = 'r_reg';
			}
			else
			{
				$a = 'o_reg';
			}

			if ($type == 'text' || $type == 'email' || $type == 'first_name' ||
			$type == 'last_name' || $type == 'address' || $type == 'city' ||
			$type == 'zip' || $type == 'phone')
			{
				$rstring = '<input type=text name="' . $a . '[' . $name . ']" value="' . $post_value . '">';
			}

			if ($type == 'textarea')
			{
				$rstring = '<textarea name="' . $a . '[' . $name . ']" value="' . $post_value . '" cols="40" rows="5">' . $post_value . '</textarea>';
			}

			if ($type == 'dropdown')
			{
				if (!is_array ($values))
				{
					$rstring = "Error: Dropdown list '$name' has no values";
				}
				else
				{
					$rstring = '<select name="' . $a . '[' . $name . ']"><option value=""> </option>';
					while (list (,$value) = each ($values))
					{
						$value = trim ($value);

						unset ($selected);
						if ($value == $post_value)
						{
							$selected = "selected";
						}

						$rstring .= '<option value="' . $value . '" ' . $selected . '>' . $value . '</option>';
					}

					$rstring .= "</select>";
				}
			}

			if ($type == 'checkbox')
			{
				unset ($checked);
				if ($post_value)
				$checked = "checked";

				$rstring = '<input type=checkbox name="' . $a . '[' . $name . ']" ' . $checked . '>';
			}

			if ($type == 'birthday' || $type == 'state' || $type == 'country')
			{
				$sbox = createobject ('phpgwapi.sbox');
			}

			if ($type == 'state')
			{
				$rstring = $sbox->list_states ($a . '[' . $name . ']', $post_value);
			}

			if ($type == 'country')
			{
				$vselected=$post_value;
				$aname=$a . '[' . $name . ']';
				
				$str = '<select name="'.$aname.'">'."\n"
				. ' <option value="  "'.($vselected == '  '?' selected':'').'>'.lang('Select One').'</option>'."\n";
				reset($sbox->country_array);
				while(list($vkey,$vvalue) = each($sbox->country_array))
				{
					$str .= ' <option value="'.$vkey.'"'.($vselected == $vkey?' selected':'') . '>'.$vvalue.'</option>'."\n";
				}
				$str .= '</select>'."\n";

				$rstring = $str;
			}

			if ($type == 'birthday')
			{
				$rstring = $sbox->getmonthtext ($a . '[' . $name . '_month]', $post_values[$name . '_month']);
				$rstring .= $sbox->getdays ($a . '[' . $name . '_day]', $post_values[$name . '_day']);
				$rstring .= $sbox->getyears ($a . '[' . $name . '_year]', $post_values[$name . '_year'], 1900, date ('Y') + 1);
			}

			return $rstring;
		}
		

		function simple_screen($template_file, $text = '',$vars=false,$head_subj='')
		{
			//$this->setLang();
			$this->header($head_subj);

			$this->template->set_file(array(
				'screen' => $template_file
			));

			if ($text)
			{
				$this->template->set_var ('extra_text', $text);
			}

			if(is_array($vars))
			{
				$this->template->set_var ($vars);
			}

			$this->template->pfp('out','screen');
			$this->footer();
			exit;
		}

		function ready_to_activate()
		{
			$this->set_lang_code();

			global $config, $reg_id;

			if ($config['activate_account'] == 'email')
			{
				$var[lang_email_confirm]=lang('We have sent a confirmation email to your email address. You must click on the link within 2 hours. If you do not, it may take a few days until your loginid will become available again.');

				$this->simple_screen('confirm_email_sent.tpl','',$var);
			}
			else
			{

				/* ($config['activate_account'] == 'immediately') */
				$GLOBALS['phpgw']->redirect($GLOBALS['phpgw']->link('/registration/main.php','menuaction=registration.boreg.step4&lang_code='.$this->lang_code.'&reg_id=' . $reg_id));
			}
		}

		function email_sent_lostpw()
		{
			$vars[message]=lang('We have sent a mail with instructions to change your password. You should follow the included link within two hours. If you do not, you will have to go to the lost password screen again.');
			$this->simple_screen('confirm_email_sent_lostpw.tpl','',$vars);
		}

		function welcome_screen()
		{
			$this->set_lang_code();
			$this->header();

			$login_url=$GLOBALS['phpgw_info']['server']['webserver_url'];

			$message=lang('Your account is now active!  Click <a href="%1">here</a> to log into your account.',$login_url);

			$this->template->set_file(array(
				'screen' => 'welcome_message.tpl'
			));

			$this->template->set_var('lang_your_account_is_active',$message);

			$this->template->pfp('out','screen');
			$this->footer();
		}

		function tos()
		{
			global $config;
			$var[tos_text]= $config['tos_text'];
			$var[lang_close_window]= '<a href="javascript:self.close()">'.lang('Close Window').'</a>';
			
			$this->simple_screen('tos.tpl','',$var,lang('Terms of Service'));
		}
	}
