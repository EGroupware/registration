<!-- BEGIN form -->
<center>{errors}</center>
<!-- BEGIN input -->
<form action="{form_action}" method="POST">
 <table border="0" width="70%" align="center">
  <tr>
   <td valign="top" align="right">{lang_choose_language}</td>
   <td valign="top"><input type="hidden" name="langchanged" value="false" >{selectbox_languages}</td>
  </tr>

<tr>
 <td colspan="2" align="center">&nbsp;</td>
</tr>

  <tr>
  <td align="right">{lang_username}</td>
  <td><input name="r_reg[loginid]" value="{value_username}"></td>
 </tr>

 <tr>
   <td colspan="2" align="center"><input type="submit" name="xsubmit" value="{lang_submit}"></td>
  </tr>
 </table>
</form>
<!-- END input -->
<!-- END form -->
