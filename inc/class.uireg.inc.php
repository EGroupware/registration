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

	class uireg
	{
		var $template;
		var $public_functions = array(
			'step1' => True,
			'step2' => True,
			'step3' => True
		);

		function uireg()
		{
			global $phpgw;

			$this->template = $phpgw->template;
		}

		function set_header_footer_blocks()
		{
			$this->template->set_file(array(
				'_layout' => 'layout.tpl'
			));		
			$this->template->set_block('_layout','header');
			$this->template->set_block('_layout','footer');
		}

		function header()
		{
			$this->set_header_footer_blocks();
			$this->template->set_var('lang_header',lang('phpGroupWare - Account registration'));
			$this->template->pfp('out','header');
		}

		function footer()
		{
			$this->template->pfp('out','footer');		
		}

		function step1($errors = '',$r_reg = '',$o_reg = '')
		{
			global $phpgw;

			$this->header();
			$this->template->set_file(array(
				'_loginid_select' => 'loginid_select.tpl'
			));
			$this->template->set_block('_loginid_select','form');

			if ($errors)
			{
				$this->template->set_var('errors',$phpgw->common->error_list($errors));
			}

			$this->template->set_var('form_action',$phpgw->link('/registration/main.php','menuaction=registration.boreg.step1'));
			$this->template->set_var('lang_username',lang('Username'));
			$this->template->set_var('lang_submit',lang('Submit'));

			$this->template->pfp('out','form');
			$this->footer();
		}

		function step2($errors = '',$r_reg = '',$o_reg = '',$missing_fields='')
		{
			global $phpgw;

			$this->header();
			$this->template->set_file(array(
				'_personal_info' => 'personal_info.tpl'
			));
			$this->template->set_block('_personal_info','form');

			if ($errors)
			{
				$this->template->set_var('errors',$phpgw->common->error_list($errors));
			}

			if ($missing_fields)
			{
				while (list(,$field) = each($missing_fields))
				{
					$this->template->set_var('missing_' . $field,'<font color="CC0000">*</font>');
				}
			}

			if (is_array($r_reg))
			{
				while (list($name,$value) = each($r_reg))
				{
					$this->template->set_var('value_' . $name,$value);
				}
			}

			if (is_array($o_reg))
			{
				while (list($name,$value) = each($o_reg))
				{
					$this->template->set_var('value_' . $name,$value);
				}
			}

			$this->template->set_var('form_action',$phpgw->link('/registration/main.php','menuaction=registration.boreg.step2'));
			$this->template->set_var('lang_password',lang('Password'));
			$this->template->set_var('lang_reenter_password',lang('Re-enter password'));
			$this->template->set_var('lang_email',lang('E-mail Address'));
			$this->template->set_var('lang_birthday',lang('Birthday'));
			$this->template->set_var('lang_firstname',lang('First name'));
			$this->template->set_var('lang_lastname',lang('Last name'));
			$this->template->set_var('lang_address',lang('Address'));
			$this->template->set_var('lang_city',lang('City'));
			$this->template->set_var('lang_state',lang('State'));
			$this->template->set_var('lang_zip',lang('ZIP/Postal'));
			$this->template->set_var('lang_country',lang('Country'));
			$this->template->set_var('lang_phone',lang('Phone'));
			$this->template->set_var('lang_gender',lang('Gender'));
			$this->template->set_var('lang_select_gender',lang('Select Gender'));
			$this->template->set_var('lang_male',lang('Male'));
			$this->template->set_var('lang_female',lang('Female'));
			$this->template->set_var('lang_submit',lang('Submit'));

			$sbox    = createobject('phpgwapi.sbox');
			$this->template->set_var('input_country',$sbox->form_select($r_reg['country'],'r_reg[country]'));
			$this->template->set_var('input_state',$sbox->list_states('r_reg[state]',$r_reg['state']));

			$this->template->set_var('lang_tos_agree',lang('I have read the terms and conditions and agree by them.'));

			$this->template->pfp('out','form');
			$this->footer();
		}

		function simple_screen($template_file)
		{
			$this->header();
			$this->template->set_file(array(
				'screen' => $template_file
			));

			$this->template->pfp('out','screen');
			$this->footer();
		}



	}
