<!-- BEGIN form -->
<center>{errors}</center>
<form action="{form_action}" method="POST">
 <table border="0" width="75%" align="center">
  <tr>
   <td width="1%">{missing_passwd}</td>
   <td><b>{lang_password}</b></td>
   <td><input name="r_reg[passwd]" value="{value_passwd}"></td>
  </tr>

  <tr>
   <td width="1%">{missing_passwd_confirm}</td>
   <td><b>{lang_reenter_password}</b></td>
   <td><input name="r_reg[passwd_confirm]" value="{value_passwd_confirm}"></td>
  </tr>

  <tr>
   <td width="1%">{missing_birthday}</td>
   <td><b>{lang_birthday}</b></td>
   <td>{input_bday_month} / {input_bday_day} / {input_bday_year}</td>
  </tr>

  <tr>
   <td width="1%">{missing_email}</td>
   <td><b>{lang_email}</b></td>
   <td><input name="r_reg[email]" value="{value_email}"></td>
  </tr>

  <tr>
   <td width="1%">{missing_firstname}</td>
   <td><b>{lang_firstname}</b></td>
   <td><input name="r_reg[firstname]" value="{value_firstname}"></td>
  </tr>

  <tr>
   <td width="1%">{missing_lastname}</td>
   <td><b>{lang_lastname}</b></td>
   <td><input name="r_reg[lastname]" value="{value_lastname}"></td>
  </tr>

  <tr>
   <td width="1%">{missing_address}</td>
   <td><b>{lang_address}</b></td>
   <td><input name="r_reg[address]" value="{value_address}"></td>
  </tr>

  <tr>
   <td width="1%">{missing_city}</td>
   <td><b>{lang_city}</b></td>
   <td><input name="r_reg[city]" value="{value_city}"></td>
  </tr>

  <tr>
   <td width="1%">{missing_state}</td>
   <td><b>{lang_state}</b></td>
   <td>{input_state}</td>
  </tr>

  <tr>
   <td width="1%">{missing_zip}</td>
   <td><b>{lang_zip}</b></td>
   <td><input name="r_reg[zip]" value="{value_zip}"></td>
  </tr>

  <tr>
   <td width="1%">{missing_country}</td>
   <td><b>{lang_country}</b></td>
   <td>{input_country}</td>
  </tr>

  <tr>
   <td width="1%">{missing_phone}</td>
   <td>{lang_phone}</td>
   <td><input name="o_reg[phone]" value="{value_phone}"></td>
  </tr>

  <tr>
   <td width="1%"></td>
   <td>{lang_gender}</td>
   <td><select name="o_reg[gender]"><option value="">[ {lang_select_gender} ]</option><option value="Male">{lang_male}</option><option value="Female">{lang_female}</option></select></td>
  </tr>

  <tr>
   <td width="1%">{missing_tos_agree}</td>
   <td colspan="2"><b><font size="2">{lang_tos_agree}</font></b><input type="checkbox" name="r_reg[tos_agree]" value="True"></td>
  </tr>

  <tr>
   <td colspan="3"><input type="submit" name="submit" value="{lang_submit}"></td>
  </tr>
 </table>
</form>
<!-- END form -->

