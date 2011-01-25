<!-- BEGIN header -->
<p style="text-align: center; color: {th_err};">{error}</p>
<form name=frm method="POST" action="{action_url}">
{hidden_vars}
<table border="0" align="center">
   <tr class="th">
    <td colspan="2">&nbsp;<b>{title}</b></td>
   </tr>
<!-- END header -->

<!-- BEGIN body -->
  <tr class="{row_on}">
  <td>{lang_Enable_Registration?}</td>
  <td>
   <select name="newsettings[enable_registration]">
	<option value="False"{selected_enable_registration_False}>{lang_No}</option>
	<option value="True"{selected_enable_registration_True}>{lang_Yes}</option>
   </select>
  </td>
</tr>
   <tr class="th">
    <td colspan="2"><b>{lang_Login_screen}</b></td>
  </tr>
<tr class="row_off">
  <td>{lang_Register_link_at_login_screen?}</td>
  <td>
   <select name="newsettings[register_link]">
	<option value="False"{selected_register_link_False}>{lang_No}</option>
	<option value="True"{selected_register_link_True}>{lang_Yes}</option>
   </select>
  </td>
</tr>
<tr class="row_on">
	<td>{lang_Addressbook_the_contacts_should_be_saved_to_before_they_are_confirmed.}</td>
	<td>{hook_addressbook_list}</td>
</tr>
<tr class="{row_on}">
  <td>{lang_Lost_password_link_at_login_screen?}</td>
  <td>
   <select name="newsettings[lostpassword_link]">
	<option value="False"{selected_lostpassword_link_False}>{lang_No}</option>
	<option value="True"{selected_lostpassword_link_True}>{lang_Yes}</option>
   </select>
  </td>
</tr>
<tr class="{row_off}">
  <td>{lang_Lost_user_id_link_at_login_screen?}</td>
   <td>
   <select name="newsettings[lostid_link]">
	<option value="False"{selected_lostid_link_False}>{lang_No}</option>
	<option value="True"{selected_lostid_link_True}>{lang_Yes}</option>
   </select>
  </td>
</tr>
<tr class="{row_on}">
    <td>{lang_Use_trial_accounts?}</td>
    <td>
     <select name="newsettings[trial_accounts]">
      <option value="False"{selected_trial_accounts_False}>{lang_No}</option>
      <option value="True"{selected_trial_accounts_True}>{lang_Yes}</option>
     </select>
    </td>
  </tr>
  <tr class="{row_off}">
    <td>{lang_Days_until_trial_accounts_expire}:</td>
   <td><input name="newsettings[days_until_trial_account_expires]" value="{value_days_until_trial_account_expires}"></td>
  </tr>
   <tr class="th">
    <td colspan="2"><b>{lang_Global_options}</b></td>
  </tr>

  <tr class="{row_on}">
   <td>{lang_Anonymous_user}:</td>
   <td><input name="newsettings[anonymous_user]" value="{value_anonymous_user}"></td>
  </tr>
  <tr class="{row_off}">
   <td>{lang_Anonymous_password}:</td>
   <td><input type="password" name="newsettings[anonymous_pass]" value="{value_anonymous_pass}"></td>
  </tr>
  <tr class="{row_on}">
  <td>{lang_Name_Sender_to_send_notices_from}:</td>
  <td><input name="newsettings[name_nobody]" value="{value_name_nobody}"></td>
  </tr>
  <tr class="{row_off}">
    <td>{lang_Email_address_to_send_notices_from}:</td>
    <td><input name="newsettings[mail_nobody]" value="{value_mail_nobody}"></td>
  </tr>
  <tr class="{row_on}">
    <td>{lang_Email_address_to_display_for_support}:</td>
    <td><input name="newsettings[support_email]" value="{value_support_email}"></td>
  </tr>
 <tr class="{row_on}">
 <td>{lang_Terms_of_Service_text} ({lang_use HTML})</td>
 <td>
	<textarea name="newsettings[tos_text]" cols="40" rows="20">{value_tos_text}</textarea>
</td>
<!--<tr class="{row_off}">-->

<!-- END body -->

<!-- BEGIN footer -->
  <tr class="{th_bg}">
    <td colspan="2">
&nbsp;
    </td>
  </tr>
  <tr>
    <td colspan="2" align="center">
      <input type="submit" name="submit" value="{lang_submit}">
      <input type="submit" name="cancel" value="{lang_cancel}">
    </td>
  </tr>
</table>
</form>
<!-- END footer -->
