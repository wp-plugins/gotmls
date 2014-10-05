<?php
/*
Plugin Name: Anti-Malware and Brute-Force Security by ELI
Plugin URI: http://gotmls.net/
Author: Eli Scheetz
Text Domain: gotmls
Author URI: http://wordpress.ieonly.com/category/my-plugins/anti-malware/
Contributors: scheeeli, gotmls
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=QZHD8QHZ2E7PE
Description: This Anti-Virus/Anti-Malware plugin searches for Malware and other Virus like threats and vulnerabilities on your server and helps you remove them. It's always growing and changing to adapt to new threats so let me know if it's not working for you.
Version: 3.15.16
*/
/*            ___
 *           /  /\     GOTMLS Main Plugin File
 *          /  /:/     @package GOTMLS
 *         /__/::\
 Copyright \__\/\:\__  © 2012-2014 Eli Scheetz (email: eli@gotmls.net)
 *            \  \:\/\
 *             \__\::/ This program is free software; you can redistribute it
 *     ___     /__/:/ and/or modify it under the terms of the GNU General Public
 *    /__/\   _\__\/ License as published by the Free Software Foundation;
 *    \  \:\ /  /\  either version 2 of the License, or (at your option) any
 *  ___\  \:\  /:/ later version.
 * /  /\\  \:\/:/
  /  /:/ \  \::/ This program is distributed in the hope that it will be useful,
 /  /:/_  \__\/ but WITHOUT ANY WARRANTY; without even the implied warranty
/__/:/ /\__    of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
\  \:\/:/ /\  See the GNU General Public License for more details.
 \  \::/ /:/
  \  \:\/:/ You should have received a copy of the GNU General Public License
 * \  \::/ with this program; if not, write to the Free Software Foundation,    
 *  \__\/ Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA        */

if (isset($_SERVER["SCRIPT_FILENAME"]) && __FILE__ == $_SERVER["SCRIPT_FILENAME"]) die('You are not allowed to call this page directly.<p>You could try starting <a href="http://'.$_SERVER["SERVER_NAME"].'">here</a>.');

define("GOTMLS_plugin_path", dirname(__FILE__).'/');
load_plugin_textdomain('gotmls', false, basename(GOTMLS_plugin_path).'/languages');
require_once(GOTMLS_plugin_path.'images/index.php');

function GOTMLS_install() {
	global $wp_version;
	if (version_compare($wp_version, GOTMLS_require_version, "<"))
		die(GOTMLS_require_version_LANGUAGE);
}
register_activation_hook(__FILE__, "GOTMLS_install");

function GOTMLS_menu() {
	global $GOTMLS_settings_array;
	if (isset($_POST["GOTMLS_menu_group"]) && is_numeric($_POST["GOTMLS_menu_group"]) && $_POST["GOTMLS_menu_group"] != $GOTMLS_settings_array["menu_group"]) {
		$GOTMLS_settings_array["menu_group"] = $_POST["GOTMLS_menu_group"];
		update_option('GOTMLS_settings_array', $GOTMLS_settings_array);
	}
	$GOTMLS_Full_plugin_logo_URL = GOTMLS_images_path.'GOTMLS-16x16.gif';
	$base_page = "GOTMLS-settings";
	$base_function = "GOTMLS_settings";
	$user_can = "activate_plugins";
	$pluginTitle = "Anti-Malware";
	$pageTitle = "$pluginTitle ".GOTMLS_Scan_Settings_LANGUAGE;
	if ($GOTMLS_settings_array["menu_group"] == 2)
		add_submenu_page("tools.php", $pageTitle, "<span style=\"background: url('$GOTMLS_Full_plugin_logo_URL') no-repeat; vertical-align: middle; border: 0 none; display: inline-block; height: 16px; width: 16px;\"></span> $pluginTitle", $user_can, $base_page, str_replace("-", "_", $base_page));
	else {
		if (is_multisite() && $GOTMLS_settings_array["menu_group"] > 2)
			$user_can = "manage_network";
		if (!function_exists("add_object_page") || $GOTMLS_settings_array["menu_group"])
			add_menu_page($pageTitle, $pluginTitle, $user_can, $base_page, $base_function, $GOTMLS_Full_plugin_logo_URL);
		else
			add_object_page($pageTitle, $pluginTitle, $user_can, $base_page, $base_function, $GOTMLS_Full_plugin_logo_URL);
		add_submenu_page($base_page, "$pluginTitle ".GOTMLS_Scan_Settings_LANGUAGE, GOTMLS_Scan_Settings_LANGUAGE, $user_can, $base_page, $base_function);
		add_submenu_page($base_page, "$pluginTitle ".GOTMLS_Run_Quick_Scan_LANGUAGE, GOTMLS_Run_Quick_Scan_LANGUAGE, $user_can, "$base_page&scan_type=Quick+Scan", $base_function);
		add_submenu_page($base_page, "$pluginTitle ".GOTMLS_View_Quarantine_LANGUAGE, GOTMLS_View_Quarantine_LANGUAGE, $user_can, "$base_page&scan_type=Quarantine", $base_function);
	}
}

function GOTMLS_display_header($pTitle, $optional_box = "") {
	global $GOTMLS_onLoad, $GOTMLS_loop_execution_time, $GOTMLS_update_home, $GOTMLS_plugin_home, $GOTMLS_definitions_versions, $wp_version, $current_user, $GOTMLS_SessionError, $GOTMLS_protocol, $GOTMLS_settings_array;
	get_currentuserinfo();
	$GOTMLS_url_parts = explode('/', GOTMLS_siteurl);
	if (isset($_GET["check_site"]) && $_GET["check_site"] == 1)
		echo '<br /><br /><div class="updated" id="check_site" style="z-index: 1234567; position: absolute; top: 1px; left: 1px; margin: 15px;"><img src="'.GOTMLS_images_path.'checked.gif" height=16 width=16 alt="&#x2714;"> '.GOTMLS_Tested_your_site_LANGUAGE.' ;-)</div><script type="text/javascript">window.parent.document.getElementById("check_site_warning").style.backgroundColor=\'#0C0\';</script><iframe style="width: 230px; height: 110px; position: absolute; right: 4px; bottom: 4px; border: none;" scrolling="no" src="http://wordpress.org/extend/plugins/GOTMLS/stats/?compatibility[version]='.$wp_version.'&compatibility[topic_version]='.GOTMLS_Version.'&compatibility[compatible]=1#compatibility-works"></iframe><a target="_blank" href="http://wordpress.org/extend/plugins/gotmls/faq/?compatibility[version]='.$wp_version.'&compatibility[topic_version]='.GOTMLS_Version.'&compatibility[compatible]=1#compatibility-works"><span style="width: 234px; height: 82px; position: absolute; right: 4px; bottom: 36px;"></span><span style="width: 345px; height: 32px; position: absolute; right: 84px; bottom: 4px;">Vote "Works" on WordPress.org -&gt;</span></a><style>#footer, #GOTMLS-Settings, #right-sidebar, #admin-page-container, #wpadminbar, #adminmenuback, #adminmenuwrap, #adminmenu {display: none !important;} #wpbody-content {padding-bottom: 0;} #wpcontent, #footer {margin-left: 5px !important;}';
	else
		echo '<style>#right-sidebar {float: right; margin-right: 10px; width: 290px;}';
	$ver_info = GOTMLS_Version.'&p='.strtoupper(GOTMLS_plugin_dir).'&wp='.$wp_version.'&ts='.date("YmdHis").'&key='.GOTMLS_installation_key.'&d='.ur1encode(GOTMLS_siteurl);
	$Update_Link = '<div style="text-align: center;"><a href="';
	$new_version = "";
	$file = basename(GOTMLS_plugin_path).'/index.php';
	$current = get_site_transient("update_plugins");
	if (isset($current->response[$file]->new_version)) {
		$new_version = sprintf(__("Upgrade to %s now!",'gotmls'), $current->response[$file]->new_version).'<br /><br />';
		$Update_Link .= wp_nonce_url(self_admin_url('update.php?action=upgrade-plugin&plugin=').$file, 'upgrade-plugin_'.$file);
	}
	$Update_Link .= "\">$new_version</a></div>";
	$Definition_Updates = '?div=Definition_Updates';
	foreach ($GOTMLS_definitions_versions as $definition_name=>$definition_version)
		$Definition_Updates .= "&ver[$definition_name]=$definition_version";
	echo '
.rounded-corners {margin: 10px; border-radius: 10px; -moz-border-radius: 10px; -webkit-border-radius: 10px; border: 1px solid #000;}
.shadowed-box {box-shadow: -3px 3px 3px #666; -moz-box-shadow: -3px 3px 3px #666; -webkit-box-shadow: -3px 3px 3px #666;}
.sidebar-box {background-color: #CCC;}
.sidebar-links {padding: 2px 5px; list-style: none;}
.sidebar-links li img {margin: 3px; height: 16px; vertical-align: middle;}
.sidebar-links li {margin-bottom: 0 !important}
.popup-box {background-color: #FFC; display: none; position: absolute; left: 0px; z-index: 10;}
.shadowed-text {text-shadow: #00F -1px 1px 1px;}
.sub-option {float: left; margin: 3px 5px;}
.inside p {margin: 10px;}
.GOTMLS_li, .GOTMLS_plugin li {list-style: none;}
.GOTMLS_plugin {margin: 5px; background: #cfc; border: 1px solid #0f0; padding: 0 5px; border-radius: 3px;}
.GOTMLS_plugin.known, .GOTMLS_plugin.backdoor, .GOTMLS_plugin.htaccess, .GOTMLS_plugin.timthumb, .GOTMLS_plugin.errors {background: #f99; border: 1px solid #f00;}
.GOTMLS_plugin.potential, .GOTMLS_plugin.wp_login, .GOTMLS_plugin.skipdirs, .GOTMLS_plugin.skipped {background: #ffc; border: 1px solid #fc6;}
.GOTMLS ul li {margin-left: 20px;}
.GOTMLS h2 {margin: 0 0 10px;}
.postbox {margin-right: 10px;}
#pastDonations li {list-style: none;}
#main-section {margin-right: 310px;}
#main-page-title {
	background: url("'.$GOTMLS_protocol.'://1.gravatar.com/avatar/5feb789dd3a292d563fea3b885f786d6?s=64&r=G") no-repeat scroll 0 0 transparent;
	line-height: 22px;
    margin: 10px 0 0;
    padding: 0 0 0 84px;}
</style>
<div id="div_file" class="shadowed-box rounded-corners sidebar-box" style="padding: 0; display: none; position: fixed; top: '.$GOTMLS_settings_array["msg_position"][1].'; left: '.$GOTMLS_settings_array["msg_position"][0].'; width: '.$GOTMLS_settings_array["msg_position"][3].'; height: '.$GOTMLS_settings_array["msg_position"][2].'; border: solid #c00; z-index: 112358;"><table style="width: 100%; height: 100%;" cellspacing="0" cellpadding="0"><tr><td style="border-bottom: 1px solid #EEEEEE;" colspan="2"><a class="rounded-corners" name="link_file" style="float: right; padding: 0 4px; margin: 6px; text-decoration: none; color: #C00; background-color: #FCC; border: solid #F00 1px;" href="#found_top" onclick="showhide(\'div_file\');">X</a><h3 onmousedown="grabDiv();" onmouseup="releaseDiv();" id="windowTitle" style="cursor: move; border-bottom: 0px none; z-index: 2345677; position: absolute; left: 0px; top: 0px; margin: 0px; padding: 6px; width: 90%; height: 20px;">'.GOTMLS_Loading_LANGUAGE.'</h3></td></tr><tr><td colspan="2" style="height: 100%"><div style="width: 100%; height: 100%; position: relative; padding: 0; margin: 0;" class="inside"><br /><br /><center><img src="'.GOTMLS_images_path.'wait.gif" height=16 width=16 alt="..."> '.GOTMLS_Loading_LANGUAGE.'<br /><br /><input type="button" onclick="showhide(\'GOTMLS_iFrame\', true);" value="'.GOTMLS_too_long_LANGUAGE.'" class="button-primary" /></center><iframe id="GOTMLS_iFrame" name="GOTMLS_iFrame" style="top: 0px; left: 0px; position: absolute; width: 100%; height: 100%; background-color: #CCC;"></iframe></td></tr><tr><td style="height: 20px;"><iframe id="GOTMLS_statusFrame" name="GOTMLS_statusFrame" style="width: 100%; height: 20px; background-color: #CCC;"></iframe></div></td><td style="height: 20px; width: 20px;"><h3 id="cornerGrab" onmousedown="grabCorner();" onmouseup="releaseCorner();" style="cursor: move; height: 24px; width: 24px; margin: 0; padding: 0; z-index: 2345678; position: absolute; right: 0px; bottom: 0px;">&#8690;</h3></td></tr></table></div>
<script type="text/javascript">
function showhide(id) {
	divx = document.getElementById(id);
	if (divx) {
		if (divx.style.display == "none" || arguments[1]) {
			divx.style.display = "block";
			divx.parentNode.className = (divx.parentNode.className+"close").replace(/close/gi,"");
			return true;
		} else {
			divx.style.display = "none";
			return false;
		}
	}
}
function loadIframe(title) {
	showhide("GOTMLS_iFrame", true);
	showhide("GOTMLS_iFrame");
	document.getElementById("windowTitle").innerHTML = title;
	showhide("div_file", true);
}
function cancelserver(divid) {
	document.getElementById(divid).innerHTML = "<div class=\'updated\'>'.GOTMLS_Could_not_find_server_LANGUAGE.'</div>";
}
function checkupdateserver(server, divid) {
	var updatescript = document.createElement("script");
	updatescript.setAttribute("src", server);
	divx = document.getElementById(divid);
	if (divx) {
		divx.appendChild(updatescript);
		if (arguments[2])
			return setTimeout("stopCheckingDefinitions = checkupdateserver(\'"+arguments[2]+"\',\'"+divid+"\')",15000);
		else
			return setTimeout("cancelserver(\'"+divid+"\')",'.($GOTMLS_loop_execution_time+1).'000+3000);
	}
}
var IE = document.all?true:false;
if (!IE) document.captureEvents(Event.MOUSEMOVE)
document.onmousemove = getMouseXY;
var offsetX = 0;
var offsetY = 0;
var offsetW = 0;
var offsetH = 0;
var curX = 0;
var curY = 0;
var curDiv;
function getMouseXY(e) {
	if (IE) { // grab the mouse pos if browser is IE
		curX = event.clientX + document.body.scrollLeft;
		curY = event.clientY + document.body.scrollTop;
	} else {  // grab the mouse pos if browser is Not IE
		curX = e.pageX - document.body.scrollLeft;
		curY = e.pageY - document.body.scrollTop;
	}
	if (curX < 0) {curX = 0;}
	if (curY < 0) {curY = 0;}
	if (offsetX && curX > 10) {curDiv.style.left = (curX - offsetX)+"px";}
	if (offsetY && (curY - offsetY) > 0) {curDiv.style.top = (curY - offsetY)+"px";}
	if (offsetW && (curX - offsetW) > 360) {curDiv.style.width = (curX - offsetW)+"px";}
	if (offsetH && (curY - offsetH) > 200) {curDiv.style.height = (curY - offsetH)+"px";}
	return true;
}
function px2num(px) {
	return px.substring(0, px.length - 2);
}
function setDiv(DivID) {
	curDiv=document.getElementById(DivID);
	if (IE && curDiv)
		DivID.style.position = "absolute";
}
function grabDiv() {
	corner = document.getElementById("windowTitle");
	if (corner) {
		corner.style.width="100%";
		corner.style.height="100%";
	}
	offsetX=curX-px2num(curDiv.style.left); 
	offsetY=curY-px2num(curDiv.style.top);
}
function releaseDiv() {
	corner = document.getElementById("windowTitle");
	if (corner) {
		corner.style.width="90%";
		corner.style.height="20px";
	}
	document.getElementById("GOTMLS_statusFrame").src = "'.GOTMLS_script_URI.'&GOTMLS_x="+curDiv.style.left+"&GOTMLS_y="+curDiv.style.top;
	offsetX=0; 
	offsetY=0;
}
function grabCorner() {
	corner = document.getElementById("cornerGrab");
	if (corner) {
		corner.style.width="100%";
		corner.style.height="100%";
	}
	offsetW=curX-px2num(curDiv.style.width); 
	offsetH=curY-px2num(curDiv.style.height);
}
function releaseCorner() {
	corner = document.getElementById("cornerGrab");
	if (corner) {
		corner.style.width="20px";
		corner.style.height="20px";
	}
	document.getElementById("GOTMLS_statusFrame").src = "'.GOTMLS_script_URI.'&GOTMLS_w="+curDiv.style.width+"&GOTMLS_h="+curDiv.style.height;
	offsetW=0; 
	offsetH=0;
}
setDiv("div_file");
</script>
'.$GOTMLS_SessionError.'
<h1 id="main-page-title">'.$pTitle.'</h1>
<div id="right-sidebar" class="metabox-holder">
	<div id="pluginupdates" class="shadowed-box stuffbox"><h3 class="hndle"><span>'.GOTMLS_Plugin_Updates_LANGUAGE.' '.$wp_version.'</span></h3>
		<div id="findUpdates" class="inside"><center>'.GOTMLS_Searching_updates_LANGUAGE.'<br /><img src="'.GOTMLS_images_path.'wait.gif" height=16 width=16 alt="Wait..." /><br /><input type="button" value="Cancel" onclick="cancelserver(\'findUpdates\');" /></center></div>
		'.$Update_Link.'
	</div>
	<script type="text/javascript">
		stopCheckingUpdates = checkupdateserver("'.$GOTMLS_plugin_home.GOTMLS_update_images_path.'?js='.$ver_info.'", "findUpdates", "'.str_replace("://", "://www.", $GOTMLS_plugin_home).GOTMLS_update_images_path.'?js='.$ver_info.'");
	</script>
	<div id="definitionupdates" class="stuffbox shadowed-box"><h3 class="hndle"><span>'.GOTMLS_Definition_Updates_LANGUAGE.' ('.$definition_version.')</span></h3>
		<script type="text/javascript">
		function check_for_updates(chk) {
			if (auto_img = document.getElementById("autoUpdateDownload")) {
				auto_img.style.display="";
				check_for_donation(chk);
			}
		}
		function check_for_donation(chk) {
			if (document.getElementById("autoUpdateDownload").src.replace(/^.+\?/,"")=="0") {
				alert(chk+"\\n\\n'.GOTMLS_Please_donate_LANGUAGE.'");
				if ('.str_replace("-", "", GOTMLS_sexagesimal($definition_version)).'0 > 10000000001 && chk.substr(0, 8) == "Changed " && chk.substr(8, 1) != "0")
					window.open("'.$GOTMLS_update_home.GOTMLS_installation_key.'/donate/?donation-source="+chk, "_blank");
			} else
				alert(chk);
		}
		function sinupFormValidate(form) {
			var error = "";
			if(form["first_name"].value == "")	
				error += "'.__("First Name is a required field!",'gotmls').'\n";
			if(form["last_name"].value == "")		
				error += "'.__("Last Name is a required field!",'gotmls').'\n";
			if(form["user_email"].value == "")
				error += "'.__("Email Address is a required field!",'gotmls').'\n";
			else {
				if (uem = document.getElementById("register_user_login"))
					uem.value = form["user_email"].value;
				if (uem = document.getElementById("register_redirect_to"))
					uem.value = "/donate/?email="+form["user_email"].value.replace("@", "%40");
			}
			if(form["user_url"].value == "")
				error += "'.__("Your WordPress Site URL is a required field!",'gotmls').'\n";
			if(form["installation_key"].value == "")
				error += "'.__("Plugin Installation Key is a required field!",'gotmls').'\n";
			if(error != "") {
				alert(error);
				return false;
			} else {
				document.getElementById("Definition_Updates").innerHTML = \'<img src="'.GOTMLS_images_path.'wait.gif">'.__("Submitting Registration ...",'gotmls').'\';
				setTimeout(\'stopCheckingDefinitions = checkupdateserver("'.$GOTMLS_update_home.$Definition_Updates.'&js='.$ver_info.'", "Definition_Updates");\', 6000);
				showhide("registerKeyForm");
				return true;
			}
		}
		function downloadUpdates(dUpdates) {
			foundUpdates = document.getElementById("autoUpdateForm");
			if (foundUpdates)
				foundUpdates.style.display = "";
		}
		</script>
	<form id="updateform" method="post" name="updateform" action="'.GOTMLS_script_URI.'">
		<img style="display: none; float: right; margin-right: 14px;" src="'.GOTMLS_images_path.'checked.gif" height=16 width=16 alt="definitions file updated" id="autoUpdateDownload" onclick="downloadUpdates(\'UpdateDownload\');">
		<div id="Definition_Updates" class="inside"><center>'.__("Searching for updates ...",'gotmls').'<br /><img src="'.GOTMLS_images_path.'wait.gif" height=16 width=16 alt="Wait..." /><br /><input type="button" value="Cancel" onclick="cancelserver(\'Definition_Updates\');" /></center></div>
		<div id="autoUpdateForm" style="display: none;" class="inside">
		<input type="submit" name="auto_update" value="'.__("Download new definitions!",'gotmls').'"> 
		</div>
	</form>
		<div id="registerKeyForm" style="display: none;" class="inside">
'.__("If you have not already registered your Key then register now and get instant access to definition updates.<p>*All fields are required and I will NOT share your registration information with anyone.</p>",'gotmls').'
<form id="registerform" onsubmit="return sinupFormValidate(this);" action="'.$GOTMLS_update_home.'wp-login.php?action=register" method="post" name="registerform" target="GOTMLS_iFrame"><input type="hidden" name="redirect_to" id="register_redirect_to" value="/donate/"><input type="hidden" name="user_login" id="register_user_login" value="">
<div>'.__("Your Full Name:",'gotmls').'</div>
<div style="float: left; width: 50%;"><input style="width: 100%;" id="first_name" type="text" name="first_name" value="'.$current_user->user_firstname.'" /></div>
<div style="float: left; width: 50%;"><input style="width: 100%;" id="last_name" type="text" name="last_name" value="'.$current_user->user_lastname.'" /></div>
<div style="clear: left; width: 100%;">
<div>'.__("A password will be e-mailed to this address:",'gotmls').'</div>
<input style="width: 100%;" id="user_email" type="text" name="user_email" value="'.$current_user->user_email.'" /></div>
<div>
<div>'.__("Your WordPress Site URL:",'gotmls').'</div>
<input style="width: 100%;" id="user_url" type="text" name="user_url" value="'.GOTMLS_siteurl.'" readonly /></div>
<div>
<div>'.__("Plugin Installation Key:",'gotmls').'</div>
<input style="width: 100%;" id="installation_key" type="text" name="installation_key" value="'.GOTMLS_installation_key.'" readonly /><input id="old_key" type="hidden" name="old_key" value="'.md5($GOTMLS_url_parts[2]).'" /></div>
<input style="width: 100%;" id="wp-submit" type="submit" name="wp-submit" value="Register Now!" /></form></div>
	</div>
	<script type="text/javascript">
		var divNAtext = false;
		function loadGOTMLS() {
			clearTimeout(divNAtext);
			setDivNAtext();
			'.$GOTMLS_onLoad.'
		}
		function showRegForm() {
			foundUpdates = document.getElementById("registerKeyForm");
			if (foundUpdates)
				foundUpdates.style.display = "block";				
			showRegFormTO = setTimeout("showRegForm()", 9000);
		}
		showRegFormTO = setTimeout("showRegForm()", 19000);
		stopCheckingDefinitions = checkupdateserver("'.$GOTMLS_update_home.$Definition_Updates.'&js='.$ver_info.'", "Definition_Updates", "'.str_replace("://", "://www.", $GOTMLS_update_home).$Definition_Updates.'&js='.$ver_info.'");
		if (divNAtext)
			loadGOTMLS();
		else
			divNAtext=true;
	</script>
	<div id="pluginlinks" class="shadowed-box stuffbox"><h3 class="hndle"><span>'.__("Plugin Links",'gotmls').'</span></h3>
		<div class="inside">
			<div id="pastDonations"></div>
			<form name="ppdform" id="ppdform" action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_blank">
			<input type="hidden" name="cmd" value="_donations">
			<input type="hidden" name="business" value="eli@gotmls.net">
			<input type="hidden" name="no_shipping" value="1">
			<input type="hidden" name="no_note" value="1">
			<input type="hidden" name="currency_code" value="USD">
			<input type="hidden" name="tax" value="0">
			<input type="hidden" name="lc" value="US">
			<input type="hidden" name="bn" value="PP-DonationsBF">
			<input type="radio" name="amount" value="14.89">$14+
			<input type="radio" name="amount" value="29.14" checked>$29+
			<input type="radio" name="amount" value="49.75">$49+
			<input type="radio" name="amount" value="76.00">$76
			<input type="radio" name="amount" value="152.00">$152
			<input type="hidden" name="item_name" value="Donation to Eli\'s Anti-Malware Plugin">
			<input type="hidden" name="item_number" value="GOTMLS-key-'.GOTMLS_installation_key.'">
			<input type="hidden" name="custom" value="key-'.GOTMLS_installation_key.'">
			<input type="hidden" name="notify_url" value="'.$GOTMLS_update_home.GOTMLS_installation_key.'/ipn">
			<input type="hidden" name="page_style" value="GOTMLS">
			<input type="hidden" name="return" value="'.$GOTMLS_update_home.GOTMLS_installation_key.'/donate/?donation-source=paid">
			<input type="hidden" name="cancel_return" value="'.$GOTMLS_update_home.GOTMLS_installation_key.'/donate/?donation-source=cancel">
			<input type="image" id="pp_button" src="'.GOTMLS_images_path.'btn_donateCC_WIDE.gif" border="0" name="submitc" alt="'.__("Make a Donation with PayPal",'gotmls').'">
			<div>
				<ul class="sidebar-links">
					<li style="float: right;"><b>on <a target="_blank" href="http://profiles.wordpress.org/scheeeli">WordPress.org</a></b><ul class="sidebar-links">
						<li><a target="_blank" href="http://wordpress.org/extend/plugins/'.GOTMLS_plugin_dir.'/faq/">Plugin FAQs</a></li>
						<li><a target="_blank" href="http://wordpress.org/support/plugin/'.GOTMLS_plugin_dir.'">Forum Posts</a></li>
						<li><a target="_blank" href="http://wordpress.org/support/view/plugin-reviews/gotmls'.GOTMLS_plugin_dir.'">Plugin Reviews</a></li>
					</ul></li>
					<li><img src="'.$GOTMLS_update_home.'/favicon.ico" border="0" alt="Plugin site:"><b><a target="_blank" href="'.$GOTMLS_update_home.'">GOTMLS.NET</a></b></li>
					<li><img src="'.$GOTMLS_plugin_home.'/favicon.ico" border="0" alt="Developer site:"><b><a target="_blank" href="'.$GOTMLS_plugin_home.'/category/my-plugins/anti-malware/">Eli\'s Blog</a></b></li>
					<li><img src="//ssl.gstatic.com/ui/v1/icons/mail/favicon.ico" border="0" alt="mail:"><b><a target="_blank" href="mailto:eli@gotmls.net">Email Eli</a></b></li>
					<li><iframe allowtransparency="true" frameborder="0" scrolling="no" src="//platform.twitter.com/widgets/follow_button.html?screen_name=GOTMLS&amp;show_count=false" style="width:125px; height:20px;"></iframe></li>
				</ul>
			</div>
			</form>
			<a target="_blank" href="http://safebrowsing.clients.google.com/safebrowsing/diagnostic?site='.urlencode(GOTMLS_siteurl).'">Google Safe Browsing Diagnostic</a>
		</div>
	</div>
	'.$optional_box.'
</div>
<div id="admin-page-container">
	<div id="main-section">';
}

function GOTMLS_settings() {
	global $GOTMLS_quarantine_dir, $GOTMLS_definitions_array, $GOTMLS_threat_levels, $GOTMLS_scanfiles, $GOTMLS_loop_execution_time, $GOTMLS_skip_ext, $GOTMLS_skip_dirs, $GOTMLS_settings_array, $GOTMLS_dirs_at_depth, $GOTMLS_dir_at_depth, $GOTMLS_protocol;
	$GOTMLS_menu_groups = array(__("Main Menu Item placed below <b>Comments</b> and above <b>Appearance</b>",'gotmls'),__("Main Menu Item placed below <b>Settings</b>",'gotmls'),__("Sub-Menu inside the <b>Tools</b> Menu Item",'gotmls'));
	if (is_multisite() && current_user_can("manage_network"))
		$GOTMLS_menu_groups[] = __("ONLY SHOW for <b>Network Admins</b>",'gotmls');
	$GOTMLS_scan_groups = array();
	$dirs = GOTMLS_explode_dir(__file__);
	$scan_level = intval($GOTMLS_settings_array["scan_level"]);
	$root_path = implode(GOTMLS_slash(), array_slice(GOTMLS_explode_dir(__file__), 0, (2 + $scan_level) * -1));
	for ($SL=0;$SL<$scan_level;$SL++)
		$GOTMLS_scan_groups[] = '<b>'.implode(GOTMLS_slash(), array_slice($dirs, -1 * (3 + $SL), 1)).'</b>';
	if (isset($_POST["check"]))
		$GOTMLS_settings_array["check"] = $_POST["check"];
	if (isset($_POST["exclude_ext"])) {	
		if (strlen(trim(str_replace(",","",$_POST["exclude_ext"]).' ')) > 0)
			$GOTMLS_settings_array["exclude_ext"] = preg_split('/[\s]*([,]+[\s]*)+/', trim(str_replace('.', ',', $_POST["exclude_ext"])), -1, PREG_SPLIT_NO_EMPTY);
		else
			$GOTMLS_settings_array["exclude_ext"] = array();
	}
	if (isset($_GET['eli']) && $_GET['eli']=='quarantine')
		$GOTMLS_skip_ext = $GOTMLS_settings_array["exclude_ext"];
	else
		$GOTMLS_skip_ext = array_merge($GOTMLS_settings_array["exclude_ext"], array("gotmls"));
	if (isset($_POST["exclude_dir"])) {
		if (strlen(trim(str_replace(",","",$_POST["exclude_dir"]).' ')) > 0)
			$GOTMLS_settings_array["exclude_dir"] = preg_split('/[\s]*([,]+[\s]*)+/', trim($_POST["exclude_dir"]), -1, PREG_SPLIT_NO_EMPTY);
		else
			$GOTMLS_settings_array["exclude_dir"] = array();
		for ($d=0; $d<count($GOTMLS_settings_array["exclude_dir"]); $d++)
			if (dirname($GOTMLS_settings_array["exclude_dir"][$d]) != ".")
				$GOTMLS_settings_array["exclude_dir"][$d] = str_replace("\\", "", str_replace("/", "", str_replace(dirname($GOTMLS_settings_array["exclude_dir"][$d]), "", $GOTMLS_settings_array["exclude_dir"][$d])));
	}
	$GOTMLS_skip_dirs = array_merge($GOTMLS_settings_array["exclude_dir"], $GOTMLS_skip_dirs);
	if (isset($_POST["scan_what"]) && is_numeric($_POST["scan_what"]) && $_POST["scan_what"] != $GOTMLS_settings_array["scan_what"])
		$GOTMLS_settings_array["scan_what"] = $_POST["scan_what"];
	if (isset($_POST["check_custom"]) && $_POST["check_custom"] != $GOTMLS_settings_array["check_custom"])
		$GOTMLS_settings_array["check_custom"] = stripslashes($_POST["check_custom"]);
	if (isset($_POST["scan_depth"]) && is_numeric($_POST["scan_depth"]) && $_POST["scan_depth"] != $GOTMLS_settings_array["scan_depth"])
		$GOTMLS_settings_array["scan_depth"] = $_POST["scan_depth"];
	if (isset($_POST['check_htaccess']) && is_numeric($_POST['check_htaccess']) && $_POST['check_htaccess'] != $GOTMLS_settings_array['check_htaccess'])
		$GOTMLS_settings_array['check_htaccess'] = $_POST['check_htaccess'];
	if (isset($_POST['check_timthumb']) && is_numeric($_POST['check_timthumb']) && $_POST['check_timthumb'] != $GOTMLS_settings_array['check_timthumb'])
		$GOTMLS_settings_array['check_timthumb'] = $_POST['check_timthumb'];
	if (isset($_POST['check_wp_login']) && is_numeric($_POST['check_wp_login']) && $_POST['check_wp_login'] != $GOTMLS_settings_array['check_wp_login'])
		$GOTMLS_settings_array['check_wp_login'] = $_POST['check_wp_login'];
	if (isset($_POST['check_known']) && is_numeric($_POST['check_known']) && $_POST['check_known'] != $GOTMLS_settings_array['check_known'])
		$GOTMLS_settings_array['check_known'] = $_POST['check_known'];
	if (isset($_POST['check_potential']) && is_numeric($_POST['check_potential']) && $_POST['check_potential'] != $GOTMLS_settings_array['check_potential'])
		$GOTMLS_settings_array['check_potential'] = $_POST['check_potential'];
	GOTMLS_update_scan_log(array("settings" => $GOTMLS_settings_array));
	$scan_opts = '';
	$scan_optjs = "<script type=\"text/javascript\">\nfunction showOnly(what) {\n";
	foreach ($GOTMLS_scan_groups as $mg => $GOTMLS_scan_group) {
		$scan_optjs .= "document.getElementById('only$mg').style.display = 'none';\n";
		$scan_opts .= '<div style="position: relative; float: right; padding: 2px 0px 4px 30px;" id="scan_group_div_'.$mg.'"><input type="radio" name="scan_what" id="not-only'.$mg.'" value="'.$mg.'"'.($GOTMLS_settings_array["scan_what"]==$mg?' checked':'').' /><a style="text-decoration: none;" href="#scan_what" onclick="showOnly(\''.$mg.'\');document.getElementById(\'not-only'.$mg.'\').checked=true;">'.$GOTMLS_scan_group.'</a><br /><div class="rounded-corners" style="position: absolute; display: none; background-color: #CCF; padding: 10px; z-index: 10;" id="only'.$mg.'"><div style="position: relative; padding: 0 40px 0 0;"><a class="rounded-corners" style="position: absolute; right: 0; margin: 0; padding: 0 4px; text-decoration: none; color: #C00; background-color: #FCC; border: solid #F00 1px;" href="#scan_what" onclick="showhide(\'only'.$mg.'\');">X</a><b>'.str_replace(" ", "&nbsp;", __("Only Scan These Folders:",'gotmls')).'</b></div>';
		$dir = implode(GOTMLS_slash(), array_slice($dirs, 0, -1 * (2 + $mg)));
		$files = GOTMLS_getfiles($dir);
		if (is_array($files))
			foreach ($files as $file)
				if (is_dir(GOTMLS_trailingslashit($dir).$file))
					$scan_opts .= '<br /><input type="checkbox" name="scan_only[]" value="'.$file.'" />'.$file;
		$scan_opts .= '</div></div>';
	}
	$scan_optjs .= "document.getElementById('only'+what).style.display = 'block';\n}\n</script>";
	$scan_opts = '><form method="POST" name="GOTMLS_Form" action="'.str_replace('&mt=', '&last_mt=', str_replace('&scan_type=', '&last_type=', GOTMLS_script_URI)).'"><input type="hidden" name="scan_type" id="scan_type" value="Complete Scan" /><div style="float: left;"><b>'.__("What to scan:",'gotmls').'</b></div><div style="float: left;">'.$scan_opts.$scan_optjs.'</div><div style="float: left;" id="scanwhatfolder"></div><br style="clear: left;" /><p><b>'.__("Scan Depth:",'gotmls').'</b> ('.__("how far do you want to drill down from your starting directory?",'gotmls').')</p><div style="padding: 0 30px;"><input type="text" value="'.$GOTMLS_settings_array["scan_depth"].'" name="scan_depth"> ('.__("-1 is infinite depth",'gotmls').')</div><p><b>'.__("What to look for:",'gotmls').'</b></p><div style="padding: 0 30px;">';//.print_r(array('<pre>',$GOTMLS_settings_array,'</pre>'),1);
	foreach ($GOTMLS_threat_levels as $threat_name=>$threat_level) {
		$scan_opts .= '<div style="padding: 0;" id="check_'.$threat_level.'_div">';
		if (isset($GOTMLS_definitions_array[$threat_level]))
			$scan_opts .= '<input type="checkbox" name="check[]" id="check_'.$threat_level.'_Yes" value="'.$threat_level.'"'.(in_array($threat_level,$GLOBALS["GOTMLS"]["settings"]["check"])?' checked':'').' /> <a style="text-decoration: none;" href="#check_'.$threat_level.'_div_0" onclick="document.getElementById(\'check_'.$threat_level.'_Yes\').checked=true;//showhide(\'dont_check_'.$threat_level.'\');">';
		else
			$scan_opts .= '<a title="'.__("Download Definition Updates to Use this feature",'gotmls').'"><img src="'.GOTMLS_images_path.'blocked.gif" height=16 width=16 alt="X">';
		$scan_opts .= "<b>$threat_name</b></a>";
		if (!isset($GOTMLS_definitions_array[$threat_level]))
			$scan_opts .= '<br /><div style="padding: 14px;" id="check_'.$threat_level.'_div_NA">'.__("Registration of your Installation Key is required for this feature",'gotmls').'</div>';//'<br /><input type="checkbox" name="dont_check[]" value="'.htmlspecialchars($threat_name).'"'.(in_array($threat_name, $GOTMLS_settings_array["dont_check"])?' checked /><script>showhide("dont_check_'.(count($potential_threat)?'known':'potential').'", true);</script>':' />').$threat_name;
		$scan_opts .= '</div>';
	}
	if (isset($_GET['eli'])) $scan_opts .= '<div style="padding: 10px;"><b>'.__("Custom RegExp:",'gotmls').'</b> ('.__("For very advanced users only. Do not use this without talking to Eli first. If used incorrectly you could easily break your site.",'gotmls').')<br /><input type="text" name="check_custom" style="width: 100%;" value="'.htmlspecialchars($GOTMLS_settings_array["check_custom"]).'" /></div>';//still testing this option
	$scan_opts .= '</div><p>'.__("<b>Skip files with the following extentions:</b> (a comma separated list of file extentions to be excluded from the scan)",'gotmls').'</p><div style="padding: 0 30px;"><input type="text" name="exclude_ext" value="'.implode(",", $GOTMLS_settings_array["exclude_ext"]).'" style="width: 100%;" /></div><p>'.__("<b>Skip directories with the following names:</b> (a comma separated list of folders to be excluded from the scan)",'gotmls').'</p><div style="padding: 0 30px;"><input type="text" name="exclude_dir" value="'.implode(",", $GOTMLS_settings_array["exclude_dir"]).'" style="width: 100%;" /></div><p style="text-align: right;"><input type="submit" id="complete_scan" value="'.GOTMLS_Run_Complete_Scan_LANGUAGE.'" class="button-primary" /></p></form></div></div>';
	$menu_opts = '<div class="stuffbox shadowed-box">
	<h3 class="hndle"><span>'.__("Menu Item Placement Options",'gotmls').'</span></h3>
	<div class="inside"><form method="POST" name="GOTMLS_menu_Form">';
	foreach ($GOTMLS_menu_groups as $mg => $GOTMLS_menu_group)
		$menu_opts .= '<div style="padding: 4px;" id="menu_group_div_'.$mg.'"><input type="radio" name="GOTMLS_menu_group" value="'.$mg.'"'.($GOTMLS_settings_array["menu_group"]==$mg?' checked':'').' onchange="document.GOTMLS_menu_Form.submit();" />'.$GOTMLS_menu_group.'</div>';
	@ob_start();
	$OB_default_handlers = array("default output handler", "zlib output compression");
	foreach (ob_list_handlers() as $OB_last_handler)
		if (!in_array($OB_last_handler, $OB_default_handlers))
			echo '<div class="error">'.sprintf(__("Another Plugin or Theme is using '%s' to handle output buffers. <br />This prevents actively outputing the buffer on-the-fly and will severely degrade the performance of this (and many other) Plugins. <br />Consider disabling caching and compression plugins (at least during the scanning process).",'gotmls'), $OB_last_handler).'</div>';
	GOTMLS_display_header('Anti-Malware by <img style="vertical-align: middle;" alt="ELI" src="'.$GOTMLS_protocol.'://0.gravatar.com/avatar/69ad8428e97469d0dcd64f1f60c07bd8?s=64" /> at GOTMLS.NET', $menu_opts.'</form><br style="clear: left;" /></div></div>');
	$scan_groups = array_merge(array(__("Scanned Files",'gotmls')=>"scanned",__("Selected Folders",'gotmls')=>"dirs",__("Scanned Folders",'gotmls')=>"dir",__("Skipped Folders",'gotmls')=>"skipdirs",__("Skipped Files",'gotmls')=>"skipped",__("Read/Write Errors",'gotmls')=>"errors",__("Quarantined Files",'gotmls')=>"bad"), $GOTMLS_threat_levels);
	echo '<script type="text/javascript">
var percent = 0;
function changeFavicon(percent) {
	var oldLink = document.getElementById("wait_gif");
	if (oldLink) {
		if (percent >= 100) {
			document.getElementsByTagName("head")[0].removeChild(oldLink);
			var link = document.createElement("link");
			link.id = "wait_gif";
			link.type = "image/gif";
			link.rel = "shortcut icon";
			if ('.implode(" + ", array_merge($GOTMLS_threat_levels, array(__("Potential Threats",'gotmls')=>"errors",__("WP-Login Vulnerability ",'gotmls')=>"errors"))).')
				link.href = "'.GOTMLS_images_path.'threat.gif";
			else
				link.href = "'.GOTMLS_images_path.'checked.gif";
			document.getElementsByTagName("head")[0].appendChild(link);
		}
	} else {
		var icons = document.getElementsByTagName("link");
		var link = document.createElement("link");
		link.id = "wait_gif";
		link.type = "image/gif";
		link.rel = "shortcut icon";
		link.href = "'.GOTMLS_images_path.'wait.gif";
	//	document.head.appendChild(link);
		document.getElementsByTagName("head")[0].appendChild(link);
	}
}
function update_status(title, time) {
	sdir = (dir+direrrors);
	if (arguments[2] >= 0 && arguments[2] <= 100)
		percent = arguments[2];
	else
		percent = Math.floor((sdir*100)/dirs);
	scan_state = "6F6";
	if (percent == 100) {
		showhide("pause_button", true);
		showhide("pause_button");
		title = "<b>'.__("Scan Complete!",'gotmls').'</b>";
	} else
		scan_state = "99F";
	changeFavicon(percent);
	if (sdir) {
		if (arguments[2] >= 0 && arguments[2] <= 100)
			timeRemaining = Math.ceil(((time-startTime)*(100/percent))-(time-startTime));
		else
			timeRemaining = Math.ceil(((time-startTime)*(dirs/sdir))-(time-startTime));
		if (timeRemaining > 59)
			timeRemaining = Math.ceil(timeRemaining/60)+" Minute";
		else
			timeRemaining += " Second";
		if (timeRemaining.substr(0, 2) != "1 ")
			timeRemaining += "s";
	} else
		timeRemaining = "Calculating Time";
	timeElapsed = Math.ceil(time);
	if (timeElapsed > 59)
		timeElapsed = Math.floor(timeElapsed/60)+" Minute";
	else
		timeElapsed += " Second";
	if (timeElapsed.substr(0, 2) != "1 ")
		timeElapsed += "s";
	divHTML = \'<div align="center" style="vertical-align: middle; background-color: #ccc; z-index: 3; height: 18px; width: 100%; border: solid #000 1px; position: relative; padding: 10px 0;"><div style="height: 18px; padding: 10px 0; position: absolute; top: 0px; left: 0px; background-color: #\'+scan_state+\'; width: \'+percent+\'%"></div><div style="height: 32px; position: absolute; top: 3px; left: 10px; z-index: 5; line-height: 16px;" align="left">\'+sdir+" Folder"+(sdir==1?"":"s")+" Checked<br />"+timeElapsed+\' Elapsed</div><div style="height: 38px; position: absolute; top: 0px; left: 0px; width: 100%; z-index: 5; line-height: 38px; font-size: 30px; text-align: center;">\'+percent+\'%</div><div style="height: 32px; position: absolute; top: 3px; right: 10px; z-index: 5; line-height: 16px;" align="right">\'+(dirs-sdir)+" Folder"+((dirs-sdir)==1?"":"s")+" Remaining<br />"+timeRemaining+" Remaining</div></div>";
	document.getElementById("status_bar").innerHTML = divHTML;
	document.getElementById("status_text").innerHTML = title;
	dis="none";
	divHTML = \'<ul style="float: right; margin: 0 20px; text-align: right;">\';
/*<!--*/';
	$MAX = 0;
	$vars = "var i, intrvl, direrrors=0";
	$fix_button_js = "";
	$found = "";
	$li_js = "return false;";
	foreach ($scan_groups as $scan_name => $scan_group) {
		$vars .= ", $scan_group=0";
		if ($MAX++ == 6) {
			echo "/*-->*/\n\tif ($scan_group > 0)\n\t\tscan_state = ' potential'; \n\telse\n\t\tscan_state = '';\n\tdivHTML += '</ul><ul style=\"text-align: left;\"><li class=\"GOTMLS_li\"><a href=\"admin.php?page=GOTMLS-settings&scan_type=Quarantine\" title=\"View Quarantine\" class=\"GOTMLS_plugin'+scan_state+'\">'+$scan_group+' '+($scan_group==1?('$scan_name').slice(0,-1):'$scan_name')+'</a></li>';\n/*<!--*/";
			$found = "Found ";
			$fix_button_js = "\n\t\tdis='block';";
		} else {
			if ($found && !in_array($scan_group, $GLOBALS["GOTMLS"]["settings"]["check"]))
				$potential_threat = ' potential" title="'.__("You are not currently scanning for this type of threat!",'gotmls');
			else
				$potential_threat = "";
			echo "/*-->*/\n\tif ($scan_group > 0) {\n\t\tscan_state = ' href=\"#found_$scan_group\" onclick=\"$li_js showhide(\\'found_$scan_group\\', true);\" class=\"GOTMLS_plugin $scan_group\"';$fix_button_js".($MAX>6?"\n\tshowhide('found_$scan_group', true);":"")."\n\t} else\n\t\tscan_state = ' class=\"GOTMLS_plugin$potential_threat\"';\n\tdivHTML += '<li class=\"GOTMLS_li\"><a'+scan_state+'>$found'+$scan_group+' '+($scan_group==1?('$scan_name').slice(0,-1):'$scan_name')+'</a></li>';\n/*<!--*/";
		}
		$li_js = "";
		if ($MAX > 11)
			$fix_button_js = "";
	}
	echo '/*-->*/
	document.getElementById("status_counts").innerHTML = divHTML+"</ul>";
	document.getElementById("fix_button").style.display = dis;
}
'.$vars.';
function showOnly(what) {
	document.getElementById("only_what").innerHTML = document.getElementById("only"+what).innerHTML;
}
var startTime = 0;
</script>
<div class="metabox-holder GOTMLS" style="width: 100%;" id="GOTMLS-Settings"><div class="postbox shadowed-box">
	<div title="Click to toggle" onclick="showhide(\'GOTMLS-Settings-Form\');" class="handlediv"><br></div>
	<h3 title="Click to toggle" onclick="showhide(\'GOTMLS-Settings-Form\');" style="cursor: pointer;" class="hndle"><span>'.GOTMLS_Scan_Settings_LANGUAGE.'</span></h3>
	<div id="GOTMLS-Settings-Form" class="inside"';
	if ((isset($_REQUEST["scan_type"]) && ($_REQUEST["scan_type"] == "Quarantine")) || (isset($_REQUEST["scan_what"]) && is_numeric($_REQUEST["scan_what"]))) {
		if (!isset($_REQUEST["scan_type"]))
			$_REQUEST["scan_type"] = "Complete Scan";
		update_option('GOTMLS_settings_array', $GOTMLS_settings_array);
		echo ' style="display: none;"'.$scan_opts.'<form method="POST" target="GOTMLS_iFrame" name="GOTMLS_Form_clean"><input type="hidden" id="GOTMLS_fixing" name="GOTMLS_fixing" value="1"><div class="postbox shadowed-box"><div title="Click to toggle" onclick="showhide(\'GOTMLS-Scan-Progress\');" class="handlediv"><br></div><h3 title="Click to toggle" onclick="showhide(\'GOTMLS-Scan-Progress\');" style="cursor: pointer;" class="hndle"><span>'.$_REQUEST["scan_type"].' Status</span></h3>';
		if ($_REQUEST["scan_type"] != "Quarantine") {
			if ($_REQUEST["scan_what"] > -1)
				GOTMLS_update_scan_log(array("scan" => array("dir" => implode(GOTMLS_slash(), array_slice($dirs, 0, -1 * (2 + $_REQUEST["scan_what"]))))));
			echo '<div id="GOTMLS-Scan-Progress" class="inside">';
			foreach ($_POST as $name => $value) {
				if (substr($name, 0, 10) != 'GOTMLS_fix') {
					if (is_array($value)) {
						foreach ($value as $val)
							echo '<input type="hidden" name="'.$name.'[]" value="'.htmlspecialchars($val).'">';
					} else
						echo '<input type="hidden" name="'.$name.'" value="'.htmlspecialchars($value).'">';
				}
			}
			echo '<div id="status_text"><img src="'.GOTMLS_images_path.'wait.gif" height=16 width=16 alt="..."> '.GOTMLS_Loading_LANGUAGE.'</div><div id="status_bar"></div><p id="pause_button" style="display: none; position: absolute; left: 0; text-align: center; margin-left: -30px; padding-left: 50%;"><input type="button" value="Pause" class="button-primary" onclick="pauseresume(this);" id="resume_button" /></p><div id="status_counts"></div><p id="fix_button" style="display: none; text-align: center;"><input id="repair_button" type="submit" value="'.__("Automatically Fix SELECTED Files Now",'gotmls').'" class="button-primary" onclick="loadIframe(\'Examine Results\');" /></p></div></div>
			<div class="postbox shadowed-box"><div title="Click to toggle" onclick="showhide(\'GOTMLS-Scan-Details\');" class="handlediv"><br></div><h3 title="Click to toggle" onclick="showhide(\'GOTMLS-Scan-Details\');" style="cursor: pointer;" class="hndle"><div style="float: right;">&nbsp;('.$GLOBALS["GOTMLS"]["scan"]["dir"].')&nbsp;</div><span>'.__("Scan Details:",'gotmls').'</span></h3>';
		}
		echo '<div id="GOTMLS-Scan-Details" class="inside">
		<script type="text/javascript">
		function checkAllFiles(check) {
			var checkboxes = new Array(); 
			checkboxes = document["GOTMLS_Form_clean"].getElementsByTagName("input");
			for (var i=0; i<checkboxes.length; i++)
				if (checkboxes[i].type == "checkbox")
					checkboxes[i].checked = check;
		}
		function setvalAllFiles(val) {
			var checkboxes = document.getElementById("GOTMLS_fixing");
			if (checkboxes)
				checkboxes.value = val;
		}
		</script>';
		if ($_REQUEST["scan_type"] == "Quarantine") {
			$entries = GOTMLS_getfiles($GOTMLS_quarantine_dir);
			echo GOTMLS_scan_log()."\n<ul name=\"found_Quarantine\" id=\"found_Quarantine\" class=\"GOTMLS_plugin known\" style=\"background-color: #ccc; padding: 0;\"><h3>";
			if (is_array($entries) && ($key = array_search(".htaccess", $entries)))
				unset($entries[$key]);
			if (is_array($entries) && ($key = array_search("index.php", $entries)))
				unset($entries[$key]);
			if (is_array($entries) && count($entries)) {
				echo (count($entries)?'<input type="checkbox" onchange="checkAllFiles(this.checked); document.getElementById(\'fix_button\').style.display = \'block\';"> Check all ':'').count($entries).' Item'.(count($entries)==1?'':'s').' in Quarantine<span style="float: right;">Date Quarantined</span></h3><p id="fix_button" style="display: none; float: right;"><input id="repair_button" type="submit" value="'.__("Restore SELECTED files from Quarantine",'gotmls').'" class="button-primary" onclick="if (confirm(\''.__("Are you sure you want to overwrite the previously cleaned files with the selected files in the Quarantine?",'gotmls').'\')) { setvalAllFiles(1); loadIframe(\'File Restoration Results\'); } else return false;" /><br /><input id="delete_button" type="submit" class="button-primary" value="'.__("Delete SELECTED files from Quarantine",'gotmls').'" style="background-color: #C33; color: #FFF; background-image: linear-gradient(to bottom, #C22, #933); border-color: #933 #933 #900; box-shadow: 0 1px 0 rgba(230, 120, 120, 0.5) inset; text-decoration: none; text-shadow: 0 1px 0 rgba(0, 0, 0, 0.1); margin-top: 10px;" onclick="if (confirm(\''.__("Are you sure you want to permanently delete the selected files in the Quarantine?",'gotmls').'\')) { setvalAllFiles(2); loadIframe(\'File Deletion Results\'); } else return false;" /></p>'.__("<p><b>The following items have been found to contain malicious code, they have been cleaned, and the original infected file contents have been saved here in the Quarantine. The code is safe here and you do not need to do anything further with these files.</b></p> FYI - these files are found in:",'gotmls').' '.$GOTMLS_quarantine_dir;
				sort($entries);
				foreach ($entries as $entry) {
					$file = GOTMLS_trailingslashit($GOTMLS_quarantine_dir).$entry;
					$date = date("y-m-d-H-i",filemtime($file));
					echo '<li><img src="'.GOTMLS_images_path.'/blocked.gif" height=16 width=16 alt="Q" style="float: left;">';
					if (is_file($file) && GOTMLS_get_ext($entry) == "gotmls") {
						$file_date = explode(".", $entry);
						if (count($file_date) > 2 && strlen($file_date[0]) == 5)
							$date = GOTMLS_sexagesimal($file_date[0]);
						elseif (@rename($file, GOTMLS_trailingslashit($GOTMLS_quarantine_dir).GOTMLS_sexagesimal($date).".$entry"))
							$file = GOTMLS_trailingslashit($GOTMLS_quarantine_dir).GOTMLS_sexagesimal($date).".$entry";
						echo '<input type="checkbox" name="GOTMLS_fix[]" value="'.GOTMLS_encode($file).'" id="check_'.GOTMLS_encode($file).'" onchange="document.getElementById(\'fix_button\').style.display = \'block\';" />'.GOTMLS_error_link("View Quarantined File", $file).str_replace($root_path, "", GOTMLS_decode($file_date[count($file_date)-2]));
					} else
						echo '<li><img src="'.GOTMLS_images_path.'/blocked.gif" height=16 width=16 alt="?" style="float: left;">'.GOTMLS_error_link("Foreign File in Quarantine", $file).$file;
					$date = explode("-", $date);
					echo "</a> <span style='float: right; margin-right: 8px;'>(20$date[0]-$date[1]-$date[2] at $date[3]:$date[4])</span></li>";
				}
			} else
				echo __("No Items in Quarantine",'gotmls').'</h3>';
			echo "</ul>";//</form>";
		} elseif ($_REQUEST["scan_what"] > -1) {
			if (!($dir = implode(GOTMLS_slash(), array_slice($dirs, 0, -1 * (2 + $_REQUEST["scan_what"]))))) $dir = "/";
			foreach ($scan_groups as $scan_name => $scan_group)
				echo "\n<ul name=\"found_$scan_group\" id=\"found_$scan_group\" class=\"GOTMLS_plugin $scan_group\" style=\"background-color: #ccc; display: none; padding: 0;\"><a class=\"rounded-corners\" name=\"link_$scan_group\" style=\"float: right; padding: 0 4px; margin: 5px 5px 0 30px; line-height: 16px; text-decoration: none; color: #C00; background-color: #FCC; border: solid #F00 1px;\" href=\"#found_top\" onclick=\"showhide('found_$scan_group');\">X</a><h3>$scan_name</h3>\n".($scan_group=='potential'?'<p> &nbsp; * '.__("NOTE: These are probably not malicious scripts (but it's a good place to start looking <u>IF</u> your site is infected and no Known Threats were found).",'gotmls').'</p>':($scan_group=='wp_login'?'<p> &nbsp; * '.__("NOTE: Your WordPress Login page is susceptible to a brute-force attack (just like any other login page). These types of attacks are becoming more prevalent these days and can sometimes cause your server to become slow or unresponsive, even if the attacks do not succeed in gaining access to your site. Applying this patch will block access to the WordPress Login page whenever this type of attack is detected. For more information on this subject",'gotmls').' <a target="_blank" href="http://gotmls.net/tag/wp-login-php/">'.__("read my blog",'gotmls').'</a>.</p>':'<br />')).'</ul>';
			GOTMLS_update_scan_log(array("scan" => array("start" => time(), "type" => $_REQUEST["scan_type"])));
			while (in_array($OB_last_handler, $OB_default_handlers) && @ob_end_flush())
				foreach (ob_list_handlers() as $OB_handler)
					$OB_last_handler = $OB_handler;
			@ob_start();
			if ($_REQUEST["scan_type"] == "Quick Scan")
				$li_js = "\nfunction testComplete() {\n\tif (percent != 100)\n\t\talert('".__("The Quick Scan was unable to finish because of a shortage of memory or a problem accessing a file. Please try using the Complete Scan, it is slower but it will handle these errors better and continue scanning the rest of the files.",'gotmls')."');\n}\nwindow.onload=testComplete;\n</script>\n<script type=\"text/javascript\">";
			echo "\n<script type=\"text/javascript\">$li_js\n/*<!--*/";
			if (is_dir($dir)) {
				$GOTMLS_dirs_at_depth[0] = 1;
				$GOTMLS_dir_at_depth[0] = 0;
				if (!(isset($_GET["eli"]) && $_GET["eli"] == "NOQ")) {
					$GOTMLS_dirs_at_depth[0]++;
					GOTMLS_readdir($GOTMLS_quarantine_dir);
				}
				if (isset($_POST['scan_only']) && is_array($_POST['scan_only'])) {
					$GOTMLS_dirs_at_depth[0] += (count($_POST['scan_only']) - 1);
					foreach ($_POST['scan_only'] as $only_dir)
						if (is_dir(GOTMLS_trailingslashit($dir).$only_dir))
							GOTMLS_readdir(GOTMLS_trailingslashit($dir).$only_dir);
				} else
					GOTMLS_readdir($dir);
			} else
				echo GOTMLS_return_threat("errors", "blocked", $dir, GOTMLS_error_link("Not a valid directory!"));
			if ($_REQUEST["scan_type"] == "Quick Scan")
				echo GOTMLS_update_status(__("Completed!",'gotmls'), 100);
			else {
				echo GOTMLS_update_status(__("Starting Scan ...",'gotmls')).'/*-->*/';
				echo "\nvar scriptSRC = '".GOTMLS_script_URI."&no_error_reporting&GOTMLS_scan=';\nvar scanfilesArKeys = new Array('".implode("','", array_keys($GOTMLS_scanfiles))."');\nvar scanfilesArNames = new Array('Scanning ".implode("','Scanning ", $GOTMLS_scanfiles)."');".'
var scanfilesI = 0;
var stopScanning;
var gotStuckOn = "";
function scanNextDir(gotStuck) {
	clearTimeout(stopScanning);
	if (gotStuck > -1) {
		if (scanfilesArNames[gotStuck].substr(0, 3) != "Re-") {
			if (scanfilesArNames[gotStuck].substr(0, 9) == "Checking ") {
				scanfilesArNames.push(scanfilesArNames[gotStuck]);
				scanfilesArKeys.push(scanfilesArKeys[gotStuck]+"&GOTMLS_skip_file[]="+encodeURIComponent(scanfilesArNames[gotStuck].substr(9)));
			} else {
				scanfilesArNames.push("Re-"+scanfilesArNames[gotStuck]);
				scanfilesArKeys.push(scanfilesArKeys[gotStuck]+"&GOTMLS_only_file=");
			}
		} else {
			scanfilesArNames.push("Got Stuck "+scanfilesArNames[gotStuck]);
			scanfilesArKeys.push(scanfilesArKeys[gotStuck]+"&GOTMLS_skip_dir="+scanfilesArKeys[gotStuck]);
		}
	}
	if (document.getElementById("resume_button").value != "Pause") {
		stopScanning=setTimeout("scanNextDir(-1)", 1000);
		startTime++;
	}
	else if (scanfilesI < scanfilesArKeys.length) {
		document.getElementById("status_text").innerHTML = scanfilesArNames[scanfilesI];
		var newscript = document.createElement("script");
		newscript.setAttribute("src", scriptSRC+scanfilesArKeys[scanfilesI]);
		divx = document.getElementById("found_scanned");
		if (divx)
			divx.appendChild(newscript);
		stopScanning=setTimeout("scanNextDir("+(scanfilesI++)+")",'.$GOTMLS_loop_execution_time.'000);
	}
}
startTime = ('.ceil(time()-$GLOBALS["GOTMLS"]["scan"]["start"]).'+3);
stopScanning=setTimeout("scanNextDir(-1)",3000);
function pauseresume(butt) {
	if (butt.value == "Resume")
		butt.value = "Pause";
	else
		butt.value = "Resume";
}
showhide("pause_button", true);
/*<!--*/';
			}
			if (@ob_get_level()) {
				GOTMLS_flush('script');
				@ob_end_flush();
			}
			echo "/*-->*/\n</script>";
		}
		echo "\n</div></div></form>";
	} else {
		echo $scan_opts.'<div class="postbox shadowed-box"><div title="Click to toggle" onclick="showhide(\'GOTMLS-Scan-Progress\');" class="handlediv"><br></div><h3 title="Click to toggle" onclick="showhide(\'GOTMLS-Scan-Progress\');" style="cursor: pointer;" class="hndle"><span>'.__("Last Scan Status",'gotmls').'</span></h3><div id="GOTMLS-Scan-Progress" class="inside">'.GOTMLS_scan_log()."\n</div></div>";
	}
	echo "\n</div></div></div>";
}

function GOTMLS_set_plugin_action_links($links_array, $plugin_file) {
	if ($plugin_file == substr(__file__, (-1 * strlen($plugin_file))) && strlen($plugin_file) > 10)
		$links_array = array_merge(array('<a href="admin.php?page=GOTMLS-settings&scan_type=Quick+Scan">'.GOTMLS_Run_Quick_Scan_LANGUAGE.'</a>', '<a href="admin.php?page=GOTMLS-settings">'.GOTMLS_Scan_Settings_LANGUAGE.'</a>'), $links_array);
	return $links_array;
}

function GOTMLS_set_plugin_row_meta($links_array, $plugin_file) {
	if ($plugin_file == substr(__file__, (-1 * strlen($plugin_file))) && strlen($plugin_file) > 10)
		$links_array = array_merge($links_array, array('<a target="_blank" href="http://gotmls.net/faqs/">FAQ</a>','<a target="_blank" href="http://gotmls.net/support/">Support</a>','<a target="_blank" href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=QZHD8QHZ2E7PE">Donate</a>'));
	return $links_array;
}

function GOTMLS_init() {
	global $GOTMLS_update_home, $GOTMLS_settings_array, $GOTMLS_onLoad, $GOTMLS_threat_levels, $wpdb, $GOTMLS_threats_found, $GOTMLS_settings_array, $GOTMLS_definitions_versions, $GOTMLS_definitions_array, $GOTMLS_file_contents, $GOTMLS_skip_ext;
	if (!isset($GOTMLS_settings_array["scan_what"]))
		$GOTMLS_settings_array["scan_what"] = 2;
	if (!isset($GOTMLS_settings_array["scan_depth"]))
		$GOTMLS_settings_array["scan_depth"] = -1;
	if (isset($_REQUEST["scan_type"]) && $_REQUEST["scan_type"] == "Quick Scan") {
		if (!isset($_REQUEST["scan_what"]))	$_REQUEST["scan_what"] = 2;
		if (!isset($_REQUEST["scan_depth"]))
			$_REQUEST["scan_depth"] = 2;
		if (!(isset($_POST["scan_only"]) && is_array($_POST["scan_only"])))
			$_POST["scan_only"] = array("","wp-content/plugins","wp-content/themes");
	}//$GOTMLS_settings_array["check_custom"] = stripslashes($_POST["check_custom"]);
	if (!isset($GOTMLS_settings_array["check_custom"]))
		$GOTMLS_settings_array["check_custom"] = "";
	if (isset($GOTMLS_settings_array["scan_level"]) && is_numeric($GOTMLS_settings_array["scan_level"]))
		$scan_level = intval($GOTMLS_settings_array["scan_level"]);
	else
		$scan_level = count(explode('/', trailingslashit(get_option("siteurl")))) - 1;
	if (!(isset($GOTMLS_settings_array["dont_check"]) && is_array($GOTMLS_settings_array["dont_check"])))
		$GOTMLS_settings_array["dont_check"] = array();
	if (isset($_REQUEST["dont_check"]) && is_array($_REQUEST["dont_check"]) && count($_REQUEST["dont_check"]))
		$GOTMLS_settings_array["dont_check"] = $_REQUEST["dont_check"];
	if ($array = get_option('GOTMLS_definitions_array')) {
		if (is_array($array))
			$GOTMLS_definitions_array = $array;
	} else {
		$wpdb->query("DELETE FROM $wpdb->options WHERE `option_name` LIKE 'GOTMLS_known_%' OR `option_name` LIKE 'GOTMLS_definitions_array_%'");
		array_walk($GOTMLS_settings_array, "GOTMLS_reset_settings");
	}
	foreach ($GOTMLS_definitions_array as $threat_level=>$definition_names)
		foreach ($definition_names as $definition_name=>$definition_version)
			if (is_array($definition_version))
				if (!isset($GOTMLS_definitions_versions[$threat_level]) || $definition_version[0] > $GOTMLS_definitions_versions[$threat_level])
					$GOTMLS_definitions_versions[$threat_level] = $definition_version[0];
	if (isset($_POST["UPDATE_definitions_array"])) {
		$GOTnew_definitions = maybe_unserialize(GOTMLS_decode($_POST["UPDATE_definitions_array"]));
		$GOTMLS_onLoad .= "check_for_updates('Downloaded Definitions');";
	} //elseif (file_exists(GOTMLS_plugin_path.'definitions_update.txt'))	$GOTnew_definitions = maybe_unserialize(GOTMLS_decode(file_get_contents(GOTMLS_plugin_path.'definitions_update.txt')));
	if (isset($GOTnew_definitions) && is_array($GOTnew_definitions)) {
		$GOTMLS_definitions_array = GOTMLS_array_replace_recursive($GOTMLS_definitions_array, $GOTnew_definitions);	
		if (file_exists(GOTMLS_plugin_path.'definitions_update.txt'))
			@unlink(GOTMLS_plugin_path.'definitions_update.txt');
		if (isset($GOTMLS_settings_array["check"]))
			unset($GOTMLS_settings_array["check"]);
		update_option('GOTMLS_definitions_array', $GOTMLS_definitions_array);
		foreach ($GOTMLS_definitions_array as $threat_level=>$definition_names)
			foreach ($definition_names as $definition_name=>$definition_version)
				if (is_array($definition_version))
					if (!isset($GOTMLS_definitions_versions[$threat_level]) || $definition_version[0] > $GOTMLS_definitions_versions[$threat_level])
						$GOTMLS_definitions_versions[$threat_level] = $definition_version[0];
	}
	asort($GOTMLS_definitions_versions);
	if (isset($_REQUEST["check"]) && is_array($_REQUEST["check"]))
		$GOTMLS_settings_array["check"] = $_REQUEST["check"];
/*	$threat_names = array_keys($GOTMLS_definitions_array["known"]);
	foreach ($threat_names as $threat_name) {
		if (isset($GOTMLS_definitions_array["known"][$threat_name]) && is_array($GOTMLS_definitions_array["known"][$threat_name]) && count($GOTMLS_definitions_array["known"][$threat_name]) > 1) {
			if ($GOTMLS_definitions_array["known"][$threat_name][0] > $GOTMLS_definitions_version)
				$GOTMLS_definitions_version = $GOTMLS_definitions_array["known"][$threat_name][0];
			if (!(count($GOTMLS_settings_array["dont_check"]) && in_array($threat_name, $GOTMLS_settings_array["dont_check"]))) {
				$GOTMLS_threat_levels[$threat_name] = count($GOTMLS_definitions_array["known"][$threat_name]);
				if (!isset($GOTMLS_settings_array["check"]) && $GOTMLS_threat_levels[$threat_name] > 2)
					$GOTMLS_settings_array["check"] = "known";
			}
		}
	}*/
	if (!isset($GOTMLS_settings_array["check"]))
		$GOTMLS_settings_array["check"] = $GOTMLS_threat_levels;
	if (isset($_POST["GOTMLS_fix"]) && !is_array($_POST["GOTMLS_fix"]))
		$_POST["GOTMLS_fix"] = array($_POST["GOTMLS_fix"]);
	GOTMLS_update_scan_log(array("settings" => $GOTMLS_settings_array));
	if (isset($_POST['GOTMLS_whitelist']) && isset($_POST['GOTMLS_chksum'])) {
		$file = GOTMLS_decode($_POST['GOTMLS_whitelist']);
		$chksum = explode("O", $_POST['GOTMLS_chksum']."O");
		if (strlen($chksum[0]) == 32 && strlen($chksum[1]) == 32 && is_file($file) && md5(@file_get_contents($file)) == $chksum[0]) {
			$filesize = @filesize($file);
			if (true) {
				if (!isset($GOTMLS_definitions_array["whitelist"][$file][0]))
					$GOTMLS_definitions_array["whitelist"][$file][0] = "A0002";
				$GOTMLS_definitions_array["whitelist"][$file][$chksum[0].'O'.$filesize] = "A0002";
			} else
				unset($GOTMLS_definitions_array["whitelist"][$file]);
			update_option("GOTMLS_definitions_array", $GOTMLS_definitions_array);
			die("<html><body>Added $file to Whitelist!<br /><iframe style='width: 90%; height: 350px;' src='$GOTMLS_update_home?whitelist=".$_POST['GOTMLS_whitelist']."&hash=$chksum[0]&size=$filesize&key=$chksum[1]'></iframe></body></html>");
		} else echo "<li>Invalid Data!</li>";
	} elseif (isset($_GET["GOTMLS_scan"])) {
		$file = GOTMLS_decode($_GET["GOTMLS_scan"]);
		if (is_dir($file)) {
			@error_reporting(0);
			@header("Content-type: text/javascript");
			if (isset($GOTMLS_settings_array["exclude_ext"]) && is_array($GOTMLS_settings_array["exclude_ext"]))
				$GOTMLS_skip_ext = $GOTMLS_settings_array["exclude_ext"];
			@ob_start();
			echo GOTMLS_scandir($file);
			if (@ob_get_level()) {
				GOTMLS_flush();
				@ob_end_flush();
			}
			die('//END OF JavaScript');
		} else {
			if (!file_exists($file))
				die(sprintf(__("The file %s does not exist.",'gotmls'), $file)."<br />\n".(file_exists(GOTMLS_quarantine($file))?sprintf(__("You could <a %s>try viewing the quarantined backup file</a>.",'gotmls'), 'target="GOTMLS_iFrame" href="'.GOTMLS_script_URI.'&GOTMLS_scan='.GOTMLS_encode(GOTMLS_quarantine($file)).'"'):__("The file must have already been delete.",'gotmls'))."<script type=\"text/javascript\">\nwindow.parent.showhide('GOTMLS_iFrame', true);\n</script>");
			else {
				$clean_file = $file;
				if (GOTMLS_get_ext($file) == 'gotmls' && dirname($file) == dirname(GOTMLS_quarantine($file))) {
					$clean_file = 'Quarantined: '.GOTMLS_decode(array_pop(explode(".", '.'.substr($file, strlen(dirname($file))+1, -7))));
					$_GET["eli"] = "quarantine";
				}
				GOTMLS_scanfile($file);
				if (isset($GOTMLS_threats_found) && is_array($GOTMLS_threats_found) && count($GOTMLS_threats_found)) {
					$fa = '';
					$f = 1;
					foreach ($GOTMLS_threats_found as $threats_found=>$threats_name) {
						$fpos = 0;
						$flen = 0;
						$potential_threat = str_replace("\r", "", $threats_found);
						while (($fpos = strpos(str_replace("\r", "", $GOTMLS_file_contents), ($potential_threat), $flen + $fpos)) !== false) {
							$flen = strlen($potential_threat);
							$fa .= ' <a title="'.htmlspecialchars($threats_name).'" href="javascript:select_text_range(\'ta_file\', '.($fpos).', '.($fpos + $flen).');">['.$f++.']</a>';
						}
						if (0 == $flen)
							$fa = 'ERROR['.($f++).']: Threat_size{'.strlen($potential_threat).'} } Content_size{'.strlen(str_replace("\r", "", $GOTMLS_file_contents)).'}';
					}
				} else
					$fa = " No Threats Found";
				die("\n".'<script type="text/javascript">
function select_text_range(ta_id, start, end) {
	ta_element = document.getElementById(ta_id);
	ta_element.focus();
	if(ta_element.setSelectionRange)
	   ta_element.setSelectionRange(start, end);
	else {
	   var r = ta_element.createTextRange();
	   r.collapse(true);
	   r.moveEnd(\'character\', end);
	   r.moveStart(\'character\', start);
	   r.select();   
	}
}
window.parent.showhide("GOTMLS_iFrame", true);
</script><table style="top: 0px; left: 0px; width: 100%; height: 100%; position: absolute;"><tr><td style="width: 100%"><form style="margin: 0;" method="post"'.(is_file($clean_file)?' onsubmit="return confirm(\''.__("Are you sure this file is not infected and you want to ignore it in future scans?",'gotmls').'\');"><input type="hidden" name="GOTMLS_whitelist" value="'.GOTMLS_encode($clean_file).'"><input type="hidden" name="GOTMLS_chksum" value="'.md5($GOTMLS_file_contents).'O'.GOTMLS_installation_key.'"><input type="submit" value="Whitelist this file" style="float: right;">':(is_file(GOTMLS_quarantine($clean_file))?' >':'>')).'</form><div id="fileperms" class="shadowed-box rounded-corners" style="display: none; position: absolute; left: 8px; top: 29px; background-color: #ccc; border: medium solid #C00; box-shadow: -3px 3px 3px #666; border-radius: 10px; padding: 10px;"><b>File Details</b><br />permissions:'.GOTMLS_fileperms($file).'<br />modified:'.date(" Y-m-d H:i:s ", filemtime($file)).'<br />changed:'.date(" Y-m-d H:i:s ", filectime($file)).'</div><div style="overflow: auto;"><span onmouseover="document.getElementById(\'fileperms\').style.display=\'block\';" onmouseout="document.getElementById(\'fileperms\').style.display=\'none\';">'.__("Potential threats in file:",'gotmls').'</span> ('.$fa.' )</div></td></tr><tr><td style="height: 100%"><textarea id="ta_file" style="width: 100%; height: 100%">'.htmlentities(str_replace("\r", "", $GOTMLS_file_contents)).'</textarea></td></tr></table>');
			}
		}
	} elseif (isset($_POST['GOTMLS_fix']) && is_array($_POST['GOTMLS_fix'])) {
		$callAlert = "clearTimeout(callAlert);\ncallAlert=setTimeout('alert_repaired(1)', 30000);";
		$li_js = "\n<script type=\"text/javascript\">\nvar callAlert;\nfunction alert_repaired(failed) {\nclearTimeout(callAlert);\nif (failed)\nfilesFailed='the rest, try again to change more.';\nwindow.parent.check_for_donation('Changed '+filesFixed+' files, failed to change '+filesFailed);\n}\n$callAlert\nwindow.parent.showhide('GOTMLS_iFrame', true);\nfilesFixed=0;\nfilesFailed=0;\nfunction fixedFile(file) {\n filesFixed++;\nwindow.parent.document.getElementById('list_'+file).className='GOTMLS_plugin';\nwindow.parent.document.getElementById('check_'+file).checked=false;\n }\n function failedFile(file) {\n filesFailed++;\nwindow.parent.document.getElementById('check_'+file).checked=false; \n}\n</script>\n<script type=\"text/javascript\">\n/*<!--*/";
		foreach ($_POST["GOTMLS_fix"] as $path) {
			if (file_exists(GOTMLS_decode($path))) {
				echo '<li>fixing '.GOTMLS_decode($path).' ...';
				$li_js .= GOTMLS_scanfile(GOTMLS_decode($path));
				echo "</li>\n$li_js/*-->*/\n$callAlert\n</script>\n";
				$li_js = "<script type=\"text/javascript\">\n/*<!--*/";
			}
		}
		die('<div id="check_site_warning" style="background-color: #F00;">'.sprintf(__("Because some threats were automatically fixed we need to check to make sure the removal did not break your site. If this stays Red and the frame below does not load please <a %s>revert the changes</a> made during the automated fix process.",'gotmls'), 'target="test_frame" href="admin.php?page=GOTMLS-settings&scan_type=Quarantine"').' <span style="color: #F00;">'.__("Never mind, it worked!",'gotmls').'</span></div><br /><iframe id="test_frame" name="test_frame" src="'.GOTMLS_script_URI.'&check_site=1" style="width: 100%; height: 200px"></iframe>'.$li_js."/*-->*/\nalert_repaired(0);\n</script>\n");
	} elseif (isset($_POST["GOTMLS_fixing"]))
		die("<script type=\"text/javascript\">\nwindow.parent.showhide('GOTMLS_iFrame', true);\nalert('".__("Nothing Selected to be Changed!",'gotmls')."');\n</script>".__("Done!",'gotmls'));
	if (isset($_POST["scan_level"]) && is_numeric($_POST["scan_level"]))
		$scan_level = intval($_POST["scan_level"]);
	if (isset($scan_level) && is_numeric($scan_level))
		$GOTMLS_settings_array["scan_level"] = intval($scan_level);
	else
		$GOTMLS_settings_array["scan_level"] = count(explode('/', trailingslashit(get_option("siteurl")))) - 1;
	if (isset($_GET["GOTMLS_x"]) || isset($_GET["GOTMLS_y"]) || isset($_GET["GOTMLS_h"]) || isset($_GET["GOTMLS_w"])) {
		if (isset($_GET["GOTMLS_x"]))
			$GOTMLS_settings_array["msg_position"][0] = $_GET["GOTMLS_x"];
		if (isset($_GET["GOTMLS_y"]))
			$GOTMLS_settings_array["msg_position"][1] = $_GET["GOTMLS_y"];
		if (isset($_GET["GOTMLS_h"]))
			$GOTMLS_settings_array["msg_position"][2] = $_GET["GOTMLS_h"];
		if (isset($_GET["GOTMLS_w"]))
			$GOTMLS_settings_array["msg_position"][3] = $_GET["GOTMLS_w"];
		$_GET["GOTMLS_msg"] = "New window position saved. ";//.print_r($GOTMLS_settings_array["msg_position"], true);
	}
	update_option('GOTMLS_settings_array', $GOTMLS_settings_array);
	if (isset($_GET["GOTMLS_msg"]))
		die('<body style="margin: 0; padding: 0;">'.$_GET["GOTMLS_msg"].'</body>');
}
if (function_exists("is_admin") && is_admin() && ((isset($_POST['GOTMLS_whitelist']) && isset($_POST['GOTMLS_chksum'])) || (isset($_GET["GOTMLS_scan"]) && is_dir(GOTMLS_decode($_GET["GOTMLS_scan"]))))) {
	@set_time_limit($GOTMLS_loop_execution_time-5);
	GOTMLS_loaded();
	GOTMLS_init();
	die("\n//PHP to Javascript Error!\n");
} else {
	add_filter("plugin_row_meta", "GOTMLS_set_plugin_row_meta", 1, 2);
	add_filter("plugin_action_links", "GOTMLS_set_plugin_action_links", 1, 2);
	add_action("plugins_loaded", "GOTMLS_loaded");
	add_action("admin_notices", "GOTMLS_admin_notices");
	add_action("admin_menu", "GOTMLS_menu");
	add_action("network_admin_menu", "GOTMLS_menu");
	$init = add_action("admin_init", "GOTMLS_init");
}
?>