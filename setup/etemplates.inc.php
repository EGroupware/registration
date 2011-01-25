<?php
/**
	* eGroupWare - eTemplates for Application registration
	* http://www.egroupware.org
	* generated by soetemplate::dump4setup() 2011-01-25 10:22
	*
	* @license http://opensource.org/licenses/gpl-license.php GPL - GNU General Public License
	* @package registration
	* @subpackage setup
	* @version $Id$
	*/

$templ_version=1;

$templ_data[] = array('name' => 'registration.change_password','template' => '','lang' => '','group' => '0','version' => '1.9.001','data' => 'a:1:{i:0;a:6:{s:4:"type";s:4:"grid";s:4:"data";a:6:{i:0;a:5:{s:2:"h1";s:10:",!@message";s:2:"h3";s:6:",@done";s:2:"h2";s:6:",@done";s:2:"h4";s:6:",@done";s:2:"h5";s:7:",!@done";}i:1;a:1:{s:1:"A";a:3:{s:4:"type";s:5:"label";s:4:"span";s:11:"all,message";s:4:"name";s:7:"message";}}i:2;a:1:{s:1:"A";a:5:{s:4:"type";s:6:"passwd";s:5:"label";s:14:"Enter password";s:4:"name";s:8:"password";s:6:"needed";s:1:"1";s:5:"align";s:5:"right";}}i:3;a:1:{s:1:"A";a:5:{s:4:"type";s:6:"passwd";s:5:"label";s:17:"re-enter password";s:4:"name";s:9:"password2";s:6:"needed";s:1:"1";s:5:"align";s:5:"right";}}i:4;a:1:{s:1:"A";a:5:{s:4:"type";s:6:"button";s:4:"span";s:3:"all";s:5:"label";s:6:"Change";s:5:"align";s:6:"center";s:4:"name";s:6:"submit";}}i:5;a:1:{s:1:"A";a:4:{s:4:"type";s:5:"label";s:4:"size";s:11:",/login.php";s:5:"label";s:33:"You can go back to the login page";s:5:"align";s:6:"center";}}}s:4:"rows";i:5;s:4:"cols";i:1;s:5:"align";s:6:"center";s:7:"options";a:0:{}}}','size' => '','style' => '','modified' => '1295484757',);

$templ_data[] = array('name' => 'registration.config','template' => '','lang' => '','group' => '0','version' => '1.9.001','data' => 'a:1:{i:0;a:4:{s:4:"type";s:4:"grid";s:4:"data";a:21:{i:0;a:3:{s:2:"h1";s:10:",!@message";s:2:"c2";s:2:"th";s:3:"c11";s:2:"th";}i:1;a:2:{s:1:"A";a:3:{s:4:"type";s:5:"label";s:4:"span";s:11:"all,message";s:4:"name";s:7:"message";}s:1:"B";a:1:{s:4:"type";s:5:"label";}}i:2;a:2:{s:1:"A";a:3:{s:4:"type";s:5:"label";s:4:"span";s:3:"all";s:5:"label";s:14:"Global options";}s:1:"B";a:1:{s:4:"type";s:5:"label";}}i:3;a:2:{s:1:"A";a:2:{s:4:"type";s:5:"label";s:5:"label";s:14:"Anonymous user";}s:1:"B";a:3:{s:4:"type";s:14:"select-account";s:4:"name";s:14:"anonymous_user";s:6:"needed";s:1:"1";}}i:4;a:2:{s:1:"A";a:2:{s:4:"type";s:5:"label";s:5:"label";s:18:"Anonymous password";}s:1:"B";a:3:{s:4:"type";s:6:"passwd";s:4:"name";s:14:"anonymous_pass";s:6:"needed";s:1:"1";}}i:5;a:2:{s:1:"A";a:2:{s:4:"type";s:5:"label";s:5:"label";s:26:"Registered accounts expire";}s:1:"B";a:4:{s:4:"type";s:13:"select-number";s:4:"name";s:15:"accounts_expire";s:4:"size";s:12:"Never,1,30,,";s:5:"label";s:7:"%s days";}}i:6;a:2:{s:1:"A";a:2:{s:4:"type";s:5:"label";s:5:"label";s:32:"Name Sender to send notices from";}s:1:"B";a:2:{s:4:"type";s:4:"text";s:4:"name";s:11:"name_nobody";}}i:7;a:2:{s:1:"A";a:2:{s:4:"type";s:5:"label";s:5:"label";s:34:"Email address to send notices from";}s:1:"B";a:2:{s:4:"type";s:9:"url-email";s:4:"name";s:11:"mail_nobody";}}i:8;a:2:{s:1:"A";a:2:{s:4:"type";s:5:"label";s:5:"label";s:36:"Email address to display for support";}s:1:"B";a:2:{s:4:"type";s:9:"url-email";s:4:"name";s:13:"support_email";}}i:9;a:2:{s:1:"A";a:2:{s:4:"type";s:5:"label";s:5:"label";s:21:"Terms of Service text";}s:1:"B";a:1:{s:4:"type";s:5:"label";}}i:10;a:2:{s:1:"A";a:4:{s:4:"type";s:8:"textarea";s:4:"size";s:4:"5,80";s:4:"span";s:3:"all";s:4:"name";s:8:"tos_text";}s:1:"B";a:1:{s:4:"type";s:5:"label";}}i:11;a:2:{s:1:"A";a:3:{s:4:"type";s:5:"label";s:4:"span";s:3:"all";s:5:"label";s:10:"Login page";}s:1:"B";a:1:{s:4:"type";s:5:"label";}}i:12;a:2:{s:1:"A";a:2:{s:4:"type";s:5:"label";s:5:"label";s:19:"Enable registration";}s:1:"B";a:2:{s:4:"type";s:11:"select-bool";s:4:"name";s:19:"enable_registration";}}i:13;a:2:{s:1:"A";a:2:{s:4:"type";s:5:"label";s:5:"label";s:29:"Register link at login screen";}s:1:"B";a:2:{s:4:"type";s:11:"select-bool";s:4:"name";s:13:"register_link";}}i:14;a:2:{s:1:"A";a:2:{s:4:"type";s:5:"label";s:5:"label";s:19:"Registration fields";}s:1:"B";a:3:{s:4:"type";s:6:"select";s:4:"size";s:1:"5";s:4:"name";s:4:"show";}}i:15;a:2:{s:1:"A";a:2:{s:4:"type";s:5:"label";s:5:"label";s:69:"Addressbook the contacts should be saved to before they are confirmed";}s:1:"B";a:2:{s:4:"type";s:6:"select";s:4:"name";s:19:"pending_addressbook";}}i:16;a:2:{s:1:"A";a:2:{s:4:"type";s:5:"label";s:5:"label";s:26:"Registration expires after";}s:1:"B";a:5:{s:4:"type";s:3:"int";s:4:"size";s:4:"1,48";s:5:"label";s:8:"%s hours";s:4:"name";s:6:"expiry";s:6:"needed";s:1:"1";}}i:17;a:2:{s:1:"A";a:2:{s:4:"type";s:5:"label";s:5:"label";s:18:"Lost password link";}s:1:"B";a:2:{s:4:"type";s:11:"select-bool";s:4:"name";s:17:"lostpassword_link";}}i:18;a:2:{s:1:"A";a:2:{s:4:"type";s:5:"label";s:5:"label";s:12:"Lost user ID";}s:1:"B";a:2:{s:4:"type";s:11:"select-bool";s:4:"name";s:11:"lostid_link";}}i:19;a:2:{s:1:"A";a:1:{s:4:"type";s:5:"label";}s:1:"B";a:1:{s:4:"type";s:5:"label";}}i:20;a:2:{s:1:"A";a:5:{s:4:"type";s:4:"hbox";s:4:"size";s:1:"2";s:4:"span";s:3:"all";i:1;a:3:{s:4:"type";s:6:"button";s:5:"label";s:4:"Save";s:4:"name";s:4:"save";}i:2;a:3:{s:4:"type";s:6:"button";s:4:"name";s:6:"cancel";s:5:"label";s:6:"Cancel";}}s:1:"B";a:1:{s:4:"type";s:5:"label";}}}s:4:"rows";i:20;s:4:"cols";i:2;}}','size' => '','style' => '','modified' => '1295559457',);

$templ_data[] = array('name' => 'registration.lost_password','template' => '','lang' => '','group' => '0','version' => '1.9.001','data' => 'a:1:{i:0;a:7:{s:4:"type";s:4:"grid";s:4:"data";a:5:{i:0;a:1:{s:2:"h1";s:10:",!@message";}i:1;a:1:{s:1:"A";a:3:{s:4:"type";s:5:"label";s:4:"span";s:11:"all,message";s:4:"name";s:7:"message";}}i:2;a:1:{s:1:"A";a:2:{s:4:"type";s:5:"label";s:5:"label";s:142:"After you enter your username, instructions to change your password will be sent to you by e-mail to the address you gave when you registered.";}}i:3;a:1:{s:1:"A";a:4:{s:4:"type";s:4:"text";s:5:"label";s:8:"Username";s:4:"name";s:8:"username";s:5:"align";s:6:"center";}}i:4;a:1:{s:1:"A";a:4:{s:4:"type";s:6:"button";s:5:"label";s:6:"Submit";s:5:"align";s:6:"center";s:4:"name";s:6:"submit";}}}s:4:"rows";i:4;s:4:"cols";i:1;s:4:"size";s:3:"50%";s:5:"align";s:6:"center";s:7:"options";a:1:{i:0;s:3:"50%";}}}','size' => '50%','style' => '','modified' => '1295476642',);

$templ_data[] = array('name' => 'registration.lost_username','template' => '','lang' => '','group' => '0','version' => '1.9.001','data' => 'a:1:{i:0;a:7:{s:4:"type";s:4:"grid";s:4:"data";a:5:{i:0;a:1:{s:2:"h1";s:10:",!@message";}i:1;a:1:{s:1:"A";a:3:{s:4:"type";s:5:"label";s:4:"span";s:11:"all,message";s:4:"name";s:7:"message";}}i:2;a:1:{s:1:"A";a:2:{s:4:"type";s:5:"label";s:5:"label";s:120:"After you enter your email address, the user accounts associated with this email address will be mailed to that address.";}}i:3;a:1:{s:1:"A";a:5:{s:4:"type";s:9:"url-email";s:5:"label";s:5:"Email";s:4:"name";s:5:"email";s:4:"size";s:2:"35";s:5:"align";s:6:"center";}}i:4;a:1:{s:1:"A";a:4:{s:4:"type";s:6:"button";s:5:"label";s:6:"Submit";s:5:"align";s:6:"center";s:4:"name";s:6:"submit";}}}s:4:"rows";i:4;s:4:"cols";i:1;s:4:"size";s:3:"50%";s:5:"align";s:6:"center";s:7:"options";a:1:{i:0;s:3:"50%";}}}','size' => '50%','style' => '','modified' => '1295540857',);

$templ_data[] = array('name' => 'registration.registration_form','template' => '','lang' => '','group' => '0','version' => '1.9.001','data' => 'a:1:{i:0;a:4:{s:4:"type";s:4:"grid";s:4:"data";a:6:{i:0;a:4:{s:2:"h3";s:17:",!@show[password]";s:2:"h2";s:20:",!@show[account_lid]";s:2:"h4";s:20:",!@show[account_lid]";s:2:"h1";s:10:",!@message";}i:1;a:6:{s:1:"A";a:3:{s:4:"type";s:5:"label";s:4:"span";s:11:"all,message";s:4:"name";s:7:"message";}s:1:"B";a:1:{s:4:"type";s:5:"label";}s:1:"C";a:1:{s:4:"type";s:5:"label";}s:1:"D";a:1:{s:4:"type";s:5:"label";}s:1:"E";a:1:{s:4:"type";s:5:"label";}s:1:"F";a:1:{s:4:"type";s:5:"label";}}i:2;a:6:{s:1:"A";a:2:{s:4:"type";s:5:"image";s:4:"span";s:1:",";}s:1:"B";a:2:{s:4:"type";s:5:"label";s:5:"label";s:8:"Login ID";}s:1:"C";a:6:{s:4:"type";s:4:"hbox";s:4:"name";s:11:"account_lid";s:6:"needed";s:1:"1";s:4:"size";s:1:"2";i:1;a:3:{s:4:"type";s:4:"text";s:4:"name";s:11:"account_lid";s:6:"needed";s:1:"1";}i:2;a:3:{s:4:"type";s:5:"label";s:4:"span";s:10:",redItalic";s:5:"label";s:1:"*";}}s:1:"D";a:1:{s:4:"type";s:5:"label";}s:1:"E";a:1:{s:4:"type";s:5:"label";}s:1:"F";a:1:{s:4:"type";s:5:"label";}}i:3;a:6:{s:1:"A";a:1:{s:4:"type";s:5:"label";}s:1:"B";a:2:{s:4:"type";s:5:"label";s:5:"label";s:8:"Password";}s:1:"C";a:4:{s:4:"type";s:4:"hbox";s:4:"size";s:1:"2";i:1;a:3:{s:4:"type";s:4:"text";s:4:"name";s:8:"password";s:6:"needed";s:1:"1";}i:2;a:3:{s:4:"type";s:5:"label";s:4:"span";s:10:",redItalic";s:5:"label";s:1:"*";}}s:1:"D";a:1:{s:4:"type";s:5:"label";}s:1:"E";a:1:{s:4:"type";s:5:"label";}s:1:"F";a:1:{s:4:"type";s:5:"label";}}i:4;a:6:{s:1:"A";a:2:{s:4:"type";s:5:"hrule";s:4:"span";s:3:"all";}s:1:"B";a:1:{s:4:"type";s:5:"label";}s:1:"C";a:1:{s:4:"type";s:5:"label";}s:1:"D";a:1:{s:4:"type";s:5:"label";}s:1:"E";a:1:{s:4:"type";s:5:"label";}s:1:"F";a:1:{s:4:"type";s:5:"label";}}i:5;a:6:{s:1:"A";a:3:{s:4:"type";s:8:"template";s:4:"span";s:3:"all";s:4:"name";s:23:"addressbook.contactform";}s:1:"B";a:1:{s:4:"type";s:5:"label";}s:1:"C";a:1:{s:4:"type";s:5:"label";}s:1:"D";a:1:{s:4:"type";s:5:"label";}s:1:"E";a:1:{s:4:"type";s:5:"label";}s:1:"F";a:1:{s:4:"type";s:5:"label";}}}s:4:"rows";i:5;s:4:"cols";i:6;}}','size' => '','style' => '','modified' => '1294443986',);

$templ_data[] = array('name' => 'registration.view','template' => '','lang' => '','group' => '0','version' => '1.9.001','data' => 'a:1:{i:0;a:6:{s:4:"type";s:4:"grid";s:4:"data";a:5:{i:0;a:1:{s:2:"h2";s:5:",!@ip";}i:1;a:1:{s:1:"A";a:3:{s:4:"type";s:6:"select";s:4:"name";s:6:"status";s:8:"readonly";s:1:"1";}}i:2;a:1:{s:1:"A";a:4:{s:4:"type";s:5:"label";s:5:"label";s:2:"IP";s:7:"no_lang";s:1:"1";s:4:"name";s:2:"ip";}}i:3;a:1:{s:1:"A";a:4:{s:4:"type";s:9:"date-time";s:5:"label";s:16:"@timestamp_label";s:4:"name";s:9:"timestamp";s:8:"readonly";s:1:"1";}}i:4;a:1:{s:1:"A";a:3:{s:4:"type";s:9:"link-list";s:4:"name";s:5:"links";s:8:"readonly";s:1:"1";}}}s:4:"rows";i:4;s:4:"cols";i:1;s:5:"align";s:6:"center";s:7:"options";a:0:{}}}','size' => '','style' => '','modified' => '1294071270',);

