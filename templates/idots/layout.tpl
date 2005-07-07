<!-- BEGIN header -->
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xml:lang="{lang}" xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<!--
		HTML Coding Standards:
		1. use lowercase is possible, because of xhtml validation
		2. make your template validate either html 4.01 or xhtml 1
		3. make your application validat both if possible
		4. always use quotionmarks when possible e.g. <img src="/path/to/image" class="class" alt="this is an image :)" />
		5. use png-graphics if possible, but keep in ming IE has a transparency bug when it renders png's
		-->

		<!-- LAY-OUT BUGS 
		1. in IE no link cursor is displayd when for png's that link
		2. tabs are ugly in preferences
		3. spacers inside sidebox
		-->
		<title>{website_title}</title>
		<meta http-equiv="content-type" content="text/html; charset={charset}" />
		<meta name="keywords" content="eGroupWare" />
		<meta name="description" content="eGroupware" />
		<meta name="keywords" content="eGroupWare" />
		<meta name="copyright" content="eGroupWare http://www.egroupware.org (c) 2003" />
		<meta name="language" content="en" />
		<meta name="author" content="eGroupWare http://www.egroupware.org" />
		<meta name="robots" content="none" />
		<link rel="icon" href="{img_icon}" type="image/x-ico" />
		<link rel="shortcut icon" href="{img_shortcut}" />
		<link href="../phpgwapi/templates/idots/css/idots.css" type="text/css" rel="StyleSheet" />
		{css}
		{java_script}

		<!-- This solves the Internet Explorer PNG-transparency bug, but only for IE 5.5 and higher --> 
		<!--[if gte IE 5.5000]>
		<script src="../phpgwapi/templates/idots/js/pngfix.js" type=text/javascript>
		</script>
		<![endif]-->
	</head>
<body {body_tags}>

<div id="divLogo"><a href="http://{logo_url}" target="_blank"><img src="{img_icon}" border="0" alt="{logo_title}" title="{logo_title}"></a></div>

<div id="divMain">
	<div id="divAppIconBar">
		<table width="100%" border="0" cellspacing="0" cellpadding="0">
			<tr>
				<td width="180" valign="top" align="left"><img src="../phpgwapi/templates/idots/images/grey-pixel.png" width="1" height="68" alt="spacer" /></td>
				<td>
					<table width="100%" border="0" cellspacing="0" cellpadding="0">
						<tr>
							<td width="100%"><img src="../phpgwapi/templates/idots/images/spacer.gif" width="1" height="68" alt="spacer" /></td>
						</tr>
						<tr>
							<td width="100%">&nbsp;</td>
						</tr>
					</table>

				</td>
				<td width="1" valign="top" align="right"><img src="../phpgwapi/templates/idots/images/grey-pixel.png" width="1" height="68" alt="spacer" /></td>
			</tr>
		</table>
	</div>
<br/>
<td id="tdAppbox" valign="top">
<div id="divAppboxHeader">{lang_header}</div>
<div id="divAppbox">
<table width="98%" cellpadding"0" cellspacing="0">
<tr><td>
<!-- END header -->

<!-- BEGIN footer -->
		 							</td></tr></table>
</div>
</td>
</tr>
</table>
</div>
</div>
<div id="divPoweredBy"><br/><span>{powered_by}</span></div>	
</body>
</html>
<!-- END footer -->

