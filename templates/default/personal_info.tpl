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
   <td width="1%">{missing_n_given}</td>
   <td><b>{lang_firstname}</b></td>
   <td><input name="r_reg[n_given]" value="{value_n_given}"></td>
  </tr>

  <tr>
   <td width="1%">{missing_n_family}</td>
   <td><b>{lang_lastname}</b></td>
   <td><input name="r_reg[n_family]" value="{value_n_family}"></td>
  </tr>

  <tr>
   <td width="1%">{missing_value_adr_one_street}</td>
   <td><b>{lang_address}</b></td>
   <td><input name="r_reg[adr_one_street]" value="{value_adr_one_street}"></td>
  </tr>

  <tr>
   <td width="1%">{missing_adr_one_locality}</td>
   <td><b>{lang_city}</b></td>
   <td><input name="r_reg[adr_one_locality]" value="{value_adr_one_locality}"></td>
  </tr>

  <tr>
   <td width="1%">{missing_state}</td>
   <td><b>{lang_state}</b></td>
   <td>{input_state}</td>
  </tr>

  <tr>
   <td width="1%">{missing_adr_one_postalcode}</td>
   <td><b>{lang_zip}</b></td>
   <td><input name="r_reg[adr_one_postalcode]" value="{value_adr_one_postalcode}"></td>
  </tr>

  <tr>
   <td width="1%">{missing_adr_one_countryname}</td>
   <td><b>{lang_country}</b></td>
   <td>{input_countryname}</td>
  </tr>

  <tr>
   <td width="1%">{missing_tel_home}</td>
   <td>{lang_phone}</td>
   <td><input name="o_reg[tel_home]" value="{value_tel_home}"></td>
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

