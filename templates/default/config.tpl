<!-- BEGIN header -->
<form method="POST" action="{action_url}">
 <table border="0" align="center" width="50%">
<!-- END header -->

<!-- BEGIN body -->
  <tr bgcolor="{th_bg}">
   <td colspan="2"><font color="{th_text}">&nbsp;<b>{title} - {lang_registration_settings}</b></font></td>
  </tr>
  <tr bgcolor="{row_on}">
   <td>{lang_use_trial_accounts}:</td>
   <td><select name="newsettings[trial_accounts]"><option value="">{lang_no}</option><option value="True"{selected_trial_accounts_True}>{lang_yes}</option></select></td>
  </tr>
  <tr bgcolor="{row_off}">
   <td>{lang_days_until_trial_accounts_expire}:</td>
   <td><input name="newsettings[days_until_trial_account_expires]" value="{value_days_until_trial_account_expires}"></td>
  </tr>
<!-- END body -->

<!-- BEGIN footer -->
  <tr>
   <td align="left"><input type="submit" name="cancel" value="Cancel"></td>
   <td align="right"><input type="submit" name="submit" value="Submit"></td>
  </tr>
 </table>
</form>
<!-- END footer -->
