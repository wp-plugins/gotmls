<?php
/*
Plugin Name: Anti-Malware by ELI (Get Off Malicious Scripts)
Plugin URI: http://gotmls.net/
Author: Eli Scheetz
Author URI: http://wordpress.ieonly.com/category/my-plugins/anti-malware/
Contributors: scheeeli
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=QZHD8QHZ2E7PE
Description: This Anti-Virus/Anti-Malware plugin searches for Malware and other Virus like threats and vulnerabilities on your server and helps you remove them. It's always growing and changing to adapt to new threats so let me know if it's not working for you.
Version: 1.3.05.14
*/

/**
 * GOTMLS Main Plugin File
 * @package GOTMLS
*/

/*  Copyright 2012-2013 Eli Scheetz (email: eli@gotmls.net)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

if (__FILE__ == $_SERVER["SCRIPT_FILENAME"]) die('You are not allowed to call this page directly.<p>You could try starting <a href="http://'.$_SERVER["SERVER_NAME"].'">here</a>.');

include_once(trailingslashit(dirname(__FILE__))."images/index.php");
$GOTMLS_images_path = plugins_url("/images/", __FILE__);

function GOTMLS_install() {
	global $wp_version;
	if (version_compare($wp_version, "2.6", "<"))
		die(__("This Plugin requires WordPress version 2.6 or higher"));
}
register_activation_hook(__FILE__, "GOTMLS_install");

function GOTMLS_menu() {
	global $GOTMLS_plugin_dir, $GOTMLS_images_path, $GOTMLS_settings_array;
$_SESSION["GOTMLS_debug"][(microtime(true)-$_SESSION["GOTMLS_debug"]["START_microtime"]).' GOTMLS_menu_start'] = GOTMLS_memory_usage(true);
	if (isset($_POST["GOTMLS_menu_group"]) && is_numeric($_POST["GOTMLS_menu_group"]) && $_POST["GOTMLS_menu_group"] != $GOTMLS_settings_array["menu_group"]) {
		$GOTMLS_settings_array["menu_group"] = $_POST["GOTMLS_menu_group"];
		update_option($GOTMLS_plugin_dir.'_settings_array', $GOTMLS_settings_array);
	}
	$GOTMLS_Full_plugin_logo_URL = GOTMLS_trailingslashit($GOTMLS_images_path).'GOTMLS-16x16.gif';
	$base_page = "$GOTMLS_plugin_dir-settings";
	if ($GOTMLS_settings_array["menu_group"] == 2)
		add_submenu_page("tools.php", __("Anti-Malware Settings/Scan Page"), __("<span style=\"background: url('$GOTMLS_Full_plugin_logo_URL') no-repeat; vertical-align: middle; border: 0 none; display: inline-block; height: 16px; width: 16px;\"></span> Anti-Malware"), "administrator", $base_page, str_replace("-", "_", $base_page));
	else {
		if (!function_exists("add_object_page") || $GOTMLS_settings_array["menu_group"] == 1)
			add_menu_page(__("Anti-Malware Settings/Scan"), __("Anti-Malware"), "administrator", $base_page, $GOTMLS_plugin_dir.'_settings', $GOTMLS_Full_plugin_logo_URL);
		else
			add_object_page(__("Anti-Malware Settings/Scan"), __("Anti-Malware"), "administrator", $base_page, $GOTMLS_plugin_dir.'_settings', $GOTMLS_Full_plugin_logo_URL);
		add_submenu_page($base_page, __("Anti-Malware Settings"), __("Scan Settings"), "administrator", $base_page, $GOTMLS_plugin_dir.'_settings');
		add_submenu_page($base_page, __("Anti-Malware Quick Scan"), __("Run Quick Scan"), "administrator", "$base_page&scan_type=Quick+Scan", $GOTMLS_plugin_dir.'_settings');
		add_submenu_page($base_page, __("Anti-Malware Quarantine"), __("View Quarantine"), "administrator", "$base_page&scan_type=Quarantine", $GOTMLS_plugin_dir.'_settings');
	}
$_SESSION["GOTMLS_debug"][(microtime(true)-$_SESSION["GOTMLS_debug"]["START_microtime"]).' GOTMLS_menu_end'] = GOTMLS_memory_usage(true);
}

function GOTMLS_display_header($pTitle, $optional_box = "") {
	global $GOTMLS_url, $GOTMLS_onLoad, $GOTMLS_loop_execution_time, $_SERVER_REQUEST_URI, $GOTMLS_plugin_dir, $GOTMLS_update_home, $GOTMLS_plugin_home, $GOTMLS_updated_images_path, $GOTMLS_images_path, $GOTMLS_definitions_versions, $GOTMLS_Version, $wp_version, $current_user, $GOTMLS_updated_definition_path, $GOTMLS_SessionError;
	get_currentuserinfo();
$_SESSION["GOTMLS_debug"][(microtime(true)-$_SESSION["GOTMLS_debug"]["START_microtime"]).' GOTMLS_display_header_start'] = GOTMLS_memory_usage(true);
	$GOTMLS_url_parts = explode('/', $GOTMLS_url);
	$wait_img_URL = $GOTMLS_images_path.'wait.gif';
	$definition_version = array_pop(array_slice($GOTMLS_definitions_versions, count($GOTMLS_definitions_versions)-1));
	if (isset($_GET["check_site"]) && $_GET["check_site"] == 1)
		echo '<br /><br /><div class="updated" id="check_site" style="z-index: 1234567; position: absolute; top: 1px; left: 1px; margin: 15px;"><img src="'.$GOTMLS_images_path.'checked.gif" height=16 width=16 alt="&#x2714;"> Tested your site. It appears we didn\'t break anything ;-)</div><script type="text/javascript">window.parent.document.getElementById("check_site_warning").style.backgroundColor=\'#0C0\';</script><iframe style="width: 230px; height: 110px; position: absolute; right: 4px; bottom: 4px; border: none;" scrolling="no" src="http://wordpress.org/extend/plugins/GOTMLS/stats/?compatibility[version]='.$wp_version.'&compatibility[topic_version]='.$GOTMLS_Version.'&compatibility[compatible]=1#compatibility-works"></iframe><a target="_blank" href="http://wordpress.org/extend/plugins/gotmls/faq/?compatibility[version]='.$wp_version.'&compatibility[topic_version]='.$GOTMLS_Version.'&compatibility[compatible]=1#compatibility-works"><span style="width: 234px; height: 82px; position: absolute; right: 4px; bottom: 36px;"></span><span style="width: 345px; height: 32px; position: absolute; right: 84px; bottom: 4px;">Vote "Works" on WordPress.org -&gt;</span></a><style>#footer, #GOTMLS-Settings, #right-sidebar, #admin-page-container, #wpadminbar, #adminmenuback, #adminmenuwrap, #adminmenu {display: none !important;} #wpbody-content {padding-bottom: 0;} #wpcontent, #footer {margin-left: 5px !important;}';
	else
		echo '<style>#right-sidebar {float: right; margin-right: 10px; width: 290px;}';
	$ver_info = $GOTMLS_Version.'&p='.$GOTMLS_plugin_dir.'&wp='.$wp_version.'&ts='.date("YmdHis").'&key='.md5($GOTMLS_url).'&d='.ur1encode($GOTMLS_url);
	$Update_Link = '<div style="text-align: center;"><a href="';
	$new_version = "";
	$file = "gotmls/index.php";
	$current = get_site_transient("update_plugins");
	if (isset($current->response[$file]->new_version)) {
		$new_version = 'Upgrade to '.$current->response[$file]->new_version.' now!<br /><br />';
		$Update_Link .= wp_nonce_url(self_admin_url('update.php?action=upgrade-plugin&plugin=').$file, 'upgrade-plugin_'.$file);
	}
	$Update_Link .= "\">$new_version</a></div>";
	echo '
.rounded-corners {margin: 10px; border-radius: 10px; -moz-border-radius: 10px; -webkit-border-radius: 10px; border: 1px solid #000;}
.shadowed-box {box-shadow: -3px 3px 3px #666; -moz-box-shadow: -3px 3px 3px #666; -webkit-box-shadow: -3px 3px 3px #666;}
.sidebar-box {background-color: #CCC;}
.sidebar-links {padding: 2px 15px; list-style: none;}
.sidebar-links li {margin-bottom: 0 !important}
.popup-box {background-color: #FFC; display: none; position: absolute; left: 0px; z-index: 10;}
.shadowed-text {text-shadow: #00F -1px 1px 1px;}
.sub-option {float: left; margin: 3px 5px;}
.inside p {margin: 10px;}
.GOTMLS_li, .GOTMLS_plugin li {list-style: none;}
.GOTMLS_plugin {margin: 5px; background: #cfc; border: 1px solid #0f0; padding: 0 5px; border-radius: 3px;}
.GOTMLS_plugin.known, .GOTMLS_plugin.backdoor, .GOTMLS_plugin.htaccess, .GOTMLS_plugin.wp_login, .GOTMLS_plugin.timthumb, .GOTMLS_plugin.errors {background: #f99; border: 1px solid #f00;}
.GOTMLS_plugin.potential, .GOTMLS_plugin.skipdirs, .GOTMLS_plugin.skipped {background: #ffc; border: 1px solid #fc6;}
.GOTMLS ul li {margin-left: 20px;}
.GOTMLS h2 {margin: 0 0 10px;}
.postbox {margin-right: 10px;}
#pastDonations li {list-style: none;}
#main-section {margin-right: 310px;}
#main-page-title {
	background: url("http://1.gravatar.com/avatar/5feb789dd3a292d563fea3b885f786d6?s=64&r=G") no-repeat scroll 0 0 transparent;
	line-height: 22px;
    margin: 10px 0 0;
    padding: 0 0 0 84px;}
</style>
<script type="text/javascript">
function showhide(id) {
	divx = document.getElementById(id);
	if (divx.style.display == "none" || arguments[1]) {
		divx.style.display = "block";
		divx.parentNode.className = (divx.parentNode.className+"close").replace(/close/gi,"");
		return true;
	} else {
		divx.style.display = "none";
		return false;
	}
}
function cancelserver(divid) {
	document.getElementById(divid).innerHTML = "<div class=\'updated\'>Could not find server!</div>";
}
function checkupdateserver(server, divid) {
	var updatescript = document.createElement("script");
	updatescript.setAttribute("src", server);
	divx = document.getElementById(divid);
	if (divx)
		divx.appendChild(updatescript);
	if (arguments[2])
		return setTimeout("checkupdateserver(\'"+arguments[2]+"\',\'"+divid+"\')",15000);
	else
		return setTimeout("cancelserver(\'"+divid+"\')",'.($GOTMLS_loop_execution_time+1).'000+3000);
}
</script>
'.$GOTMLS_SessionError.'
<h1 id="main-page-title">'.$pTitle.'</h1>
<div id="right-sidebar" class="metabox-holder">
	<div id="pluginupdates" class="shadowed-box stuffbox"><h3 class="hndle"><span>Plugin Updates for WP '.$wp_version.'</span></h3>
		<div id="findUpdates" class="inside"><center>Searching for updates ...<br /><img src="'.$wait_img_URL.'" height=16 width=16 alt="Wait..." /><br /><input type="button" value="Cancel" onclick="cancelserver(\'findUpdates\');" /></center></div>
		'.$Update_Link.'
	</div>
	<script type="text/javascript">
		stopCheckingUpdates = checkupdateserver("'.$GOTMLS_plugin_home.$GOTMLS_updated_images_path.'?js='.$ver_info.'", "findUpdates", "'.str_replace("wordpress", "wp", $GOTMLS_plugin_home).$GOTMLS_updated_images_path.'?js='.$ver_info.'");
	</script>
	<div id="definitionupdates" class="stuffbox shadowed-box"><h3 class="hndle"><span>Definition Updates ('.$definition_version.')</span></h3>
		<script type="text/javascript">
		function check_for_updates(chk) {
			if (auto_img = document.getElementById("autoUpdateDownload")) {
				auto_img.style.display="";
				check_for_donation(chk);
			}
		}
		function check_for_donation(chk) {
			if (document.getElementById("autoUpdateDownload").src.replace(/^.+\?/,"")=="0") {
				alert(chk+"\\n\\nPlease make a donation for the use of this wonderful feature!");
				if ('.str_replace("-", "", GOTMLS_sexagesimal($definition_version)).'0 > 10000000001 && chk.substr(0, 6) == "Repair")
					window.open("'.$GOTMLS_update_home.'donate/?donation-key='.md5($GOTMLS_url).'&donation-source="+chk, "_blank");
			} else
				alert(chk);
		}
		function sinupFormValidate(form) {
			var error = "";
			if(form["first_name"].value == "")	
				error += "First Name is a required field!\n";
			if(form["last_name"].value == "")		
				error += "Last Name is a required field!\n";
			if(form["user_email"].value == "")
				error += "Email Address is a required field!\n";
			else {
				if (uem = document.getElementById("register_user_login"))
					uem.value = form["user_email"].value;
				if (uem = document.getElementById("register_redirect_to"))
					uem.value = "/donate/?email="+form["user_email"].value.replace("@", "%40");
			}
			if(form["user_url"].value == "")
				error += "Your WordPress Site URL is a required field!\n";
			if(form["installation_key"].value == "")
				error += "Plugin Installation Key is a required field!\n";
			if(error != "") {
				alert(error);
				return false;
			} else
				return true;
		}
		function downloadUpdates(dUpdates) {
			foundUpdates = document.getElementById("autoUpdateForm");
			if (foundUpdates)
				foundUpdates.style.display = "";
		}
		</script>
	<form id="updateform" method="post" name="updateform" action="'.$_SERVER_REQUEST_URI.'">
		<img style="display: none; float: right; margin-right: 14px;" src="'.$GOTMLS_images_path.'checked.gif" height=16 width=16 alt="definitions file updated" id="autoUpdateDownload" onclick="downloadUpdates(\'UpdateDownload\');">
		<div id="Definition_Updates" class="inside"><center>Searching for updates ...<br /><img src="'.$wait_img_URL.'" height=16 width=16 alt="Wait..." /><br /><input type="button" value="Cancel" onclick="cancelserver(\'Definition_Updates\');" /></center></div>
		<div id="autoUpdateForm" style="display: none;" class="inside">
		<input type="submit" name="auto_update" value="Download new definitions!"> 
		</div>
	</form>
		<div id="registerKeyForm" style="display: none;" class="inside">
Register your Key now and get instant access to new definition files as new threats are discovered.
<p>*All fields are required and I will NOT share your registration information with anyone.</p>
<form id="registerform" onsubmit="return sinupFormValidate(this);" action="'.$GOTMLS_update_home.'wp-login.php?action=register" method="post" name="registerform"><input type="hidden" name="redirect_to" id="register_redirect_to" value="/donate/"><input type="hidden" name="user_login" id="register_user_login" value="">
<div>Your Full Name:</div>
<div style="float: left; width: 50%;"><input style="width: 100%;" id="first_name" type="text" name="first_name" value="'.$current_user->user_firstname.'" /></div>
<div style="float: left; width: 50%;"><input style="width: 100%;" id="last_name" type="text" name="last_name" value="'.$current_user->user_lastname.'" /></div>
<div style="clear: left; width: 100%;">
<div>A password will be e-mailed to this address:</div>
<input style="width: 100%;" id="user_email" type="text" name="user_email" value="'.$current_user->user_email.'" /></div>
<div>
<div>Your WordPress Site URL:</div>
<input style="width: 100%;" id="user_url" type="text" name="user_url" value="'.$GOTMLS_url.'" readonly /></div>
<div>
<div>Plugin Installation Key:</div>
<input style="width: 100%;" id="installation_key" type="text" name="installation_key" value="'.md5($GOTMLS_url).'" readonly /><input id="old_key" type="hidden" name="old_key" value="'.md5($GOTMLS_url_parts[2]).'" /></div>
<input style="width: 100%;" id="wp-submit" type="submit" name="wp-submit" value="Register Now!" /></form></div>
	</div>
	<script type="text/javascript">
		var divNAtext = false;
		function loadGOTMLS() {
			clearTimeout(divNAtext);
			setDivNAtext();
			'.$GOTMLS_onLoad.'
		}
		stopCheckingDefinitions = checkupdateserver("'.$GOTMLS_update_home.$GOTMLS_updated_definition_path.'?div=Definition_Updates';
	foreach ($GOTMLS_definitions_versions as $definition_name=>$definition_version)
		echo "&ver[$definition_name]=$definition_version";
	echo '&js='.$ver_info.'", "Definition_Updates");
		if (divNAtext)
			loadGOTMLS();
		else
			divNAtext=true;
	</script>
	<div id="pluginlinks" class="shadowed-box stuffbox"><h3 class="hndle"><span>Plugin Links</span></h3>
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
			<input type="hidden" name="item_number" value="GOTMLS-key-'.md5($GOTMLS_url).'">
			<input type="hidden" name="custom" value="key-'.md5($GOTMLS_url).'">
			<input type="hidden" name="notify_url" value="'.$GOTMLS_update_home.'?ipn">
			<input type="hidden" name="page_style" value="GOTMLS">
			<input type="hidden" name="return" value="'.$GOTMLS_update_home.'donate/?paid='.md5($GOTMLS_url).'">
			<input type="hidden" name="cancel_return" value="'.$GOTMLS_update_home.'donate/?cancel='.md5($GOTMLS_url).'">
			<input type="image" id="pp_button" src="'.$GOTMLS_images_path.'btn_donateCC_WIDE.gif" border="0" name="submitc" alt="Make a Donation with PayPal">
			<div>
				<ul class="sidebar-links">
					<li style="float: right;"><b>on <a target="_blank" href="http://profiles.wordpress.org/scheeeli">WordPress.org</a></b><ul class="sidebar-links">
						<li><a target="_blank" href="http://wordpress.org/extend/plugins/'.strtolower($GOTMLS_plugin_dir).'/faq/">Plugin FAQs</a></li>
						<li><a target="_blank" href="http://wordpress.org/support/plugin/'.strtolower($GOTMLS_plugin_dir).'">Forum Posts</a></li>
						<li><a target="_blank" href="http://wordpress.org/support/view/plugin-reviews/gotmls'.strtolower($GOTMLS_plugin_dir).'">Plugin Reviews</a></li>
					</ul></li>
					<li><b>on <a target="_blank" href="'.$GOTMLS_plugin_home.'category/my-plugins/">Eli\'s Blog</a></b><ul class="sidebar-links">
						<li><a target="_blank" href="'.$GOTMLS_plugin_home.'category/my-plugins/anti-malware/">Anti-Malware</a></li>
					</ul></li>
					<li><b>on <a target="_blank" href="'.$GOTMLS_update_home.'">GOTMLS.NET</a></b><ul class="sidebar-links">
						<li><a target="_blank" href="'.$GOTMLS_update_home.'blog/">Blog</a></li>
					</ul></li>
				</ul>
			</div>
			</form>
			<a target="_blank" href="http://safebrowsing.clients.google.com/safebrowsing/diagnostic?site='.urlencode($GOTMLS_url).'">Google Safe Browsing Diagnostic</a>
		</div>
	</div>
	'.$optional_box.'
</div>
<div id="admin-page-container">
	<div id="main-section">';
$_SESSION["GOTMLS_debug"][(microtime(true)-$_SESSION["GOTMLS_debug"]["START_microtime"]).' GOTMLS_display_header_end'] = GOTMLS_memory_usage(true);
}

function GOTMLS_settings() {
	global $GOTMLS_scan_logs_array, $GOTMLS_quarantine_dir, $GOTMLS_definitions_array, $GOTMLS_threat_levels, $GOTMLS_script_URI, $GOTMLS_scanfiles, $GOTMLS_plugin_dir, $GOTMLS_images_path, $GOTMLS_loop_execution_time, $GOTMLS_skip_ext, $GOTMLS_skip_dirs, $GOTMLS_settings_array, $GOTMLS_dirs_at_depth, $GOTMLS_dir_at_depth;
$_SESSION["GOTMLS_debug"][(microtime(true)-$_SESSION["GOTMLS_debug"]["START_microtime"]).' GOTMLS_Settings_start'] = GOTMLS_memory_usage(true);
	$GOTMLS_menu_groups = array("Main Menu Item placed below <b>Comments</b> and above <b>Appearance</b>","Main Menu Item placed below <b>Settings</b>","Sub-Menu inside the <b>Tools</b> Menu Item");
	$GOTMLS_scan_groups = array();
	$dirs = GOTMLS_explode_dir(__file__);
	$scan_level = intval($GOTMLS_settings_array["scan_level"]);
	for ($SL=0;$SL<$scan_level;$SL++)
		$GOTMLS_scan_groups[] = '<b>'.implode(GOTMLS_slash(), array_slice($dirs, -1 * (3 + $SL), 1)).'</b>';
	if (!isset($GOTMLS_settings_array["scan_what"]))
		$GOTMLS_settings_array["scan_what"] = 2;
	if (!isset($GOTMLS_settings_array["scan_depth"]))
		$GOTMLS_settings_array["scan_depth"] = -1;
	if (isset($_POST["check"]))
		$_SESSION["check"] = $_POST["check"];
	if (!(isset($GOTMLS_settings_array["exclude_ext"]) && is_array($GOTMLS_settings_array["exclude_ext"])))
		$GOTMLS_settings_array["exclude_ext"] = $GOTMLS_skip_ext;
	if (!isset($GOTMLS_settings_array["check_custom"]))
		$GOTMLS_settings_array["check_custom"] = "";
	if (isset($_POST["exclude_ext"])) {
		if (strlen(trim(str_replace(",","",$_POST["exclude_ext"]).' ')) > 0)
			$GOTMLS_settings_array["exclude_ext"] = preg_split('/[\s]*([,]+[\s]*)+/', trim($_POST["exclude_ext"]), -1, PREG_SPLIT_NO_EMPTY);
		else
			$GOTMLS_settings_array["exclude_ext"] = array();
	}
	if (isset($_GET['eli']) && $_GET['eli']=='quarantine')
		$GOTMLS_skip_ext = $GOTMLS_settings_array['exclude_ext'];
	else
		$GOTMLS_skip_ext = array_merge($GOTMLS_settings_array['exclude_ext'], array('gotmls'));
	if (!(isset($GOTMLS_settings_array['exclude_dir']) && is_array($GOTMLS_settings_array['exclude_dir'])))
		$GOTMLS_settings_array["exclude_dir"] = array();
	if (isset($_POST["exclude_dir"])) {
		if (strlen(trim(str_replace(",","",$_POST["exclude_dir"]).' ')) > 0)
			$GOTMLS_settings_array["exclude_dir"] = preg_split('/[\s]*([,]+[\s]*)+/', trim($_POST["exclude_dir"]), -1, PREG_SPLIT_NO_EMPTY);
		else
			$GOTMLS_settings_array["exclude_dir"] = array();
	}
	$GOTMLS_skip_dirs = array_merge($GOTMLS_settings_array["exclude_dir"], $GOTMLS_skip_dirs);
	if (isset($_POST["scan_what"]) && is_numeric($_POST["scan_what"]) && $_POST["scan_what"] != $GOTMLS_settings_array["scan_what"])
		$GOTMLS_settings_array["scan_what"] = $_POST["scan_what"];
	if (isset($_POST["check_custom"]) && $_POST["check_custom"] != $GOTMLS_settings_array["check_custom"]) {
		$GOTMLS_settings_array["check_custom"] = stripslashes($_POST["check_custom"]);
		$_SESSION["check_custom"] = $GOTMLS_settings_array["check_custom"];
	}
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
	$scan_opts = '><form method="POST" name="GOTMLS_Form" action="'.str_replace('&scan_type=', '&last_type=', $GOTMLS_script_URI).'"><input type="hidden" name="scan_type" id="scan_type" value="Quick Scan" /><div style="float: left;"><b>What to scan:</b></div><div style="float: left;">';
	$scan_optjs = "<script type=\"text/javascript\">\nfunction showOnly(what) {\n";
	foreach ($GOTMLS_scan_groups as $mg => $GOTMLS_scan_group) {
		$scan_optjs .= "document.getElementById('only$mg').style.display = 'none';\n";
		$scan_opts .= '<div style="position: relative; float: right; padding: 2px 0px 4px 30px;" id="scan_group_div_'.$mg.'"><input type="radio" name="scan_what" id="not-only'.$mg.'" value="'.$mg.'"'.($GOTMLS_settings_array['scan_what']==$mg?' checked':'').' /><a style="text-decoration: none;" href="#scan_what" onclick="showOnly(\''.$mg.'\');document.getElementById(\'not-only'.$mg.'\').checked=true;">'.$GOTMLS_scan_group.'</a><br /><div class="rounded-corners" style="position: absolute; display: none; background-color: #CCF; padding: 10px; z-index: 10;" id="only'.$mg.'"><div style="position: relative; padding: 0 40px 0 0;"><a class="rounded-corners" style="position: absolute; right: 0; margin: 0; padding: 0 4px; text-decoration: none; color: #C00; background-color: #FCC; border: solid #F00 1px;" href="#scan_what" onclick="showhide(\'only'.$mg.'\');">X</a><b>Only&nbsp;Scan&nbsp;These&nbsp;Folders:</b></div>';
		$dir = implode(GOTMLS_slash(), array_slice($dirs, 0, -1 * (2 + $mg)));
		$files = GOTMLS_getfiles($dir);
		if (is_array($files))
			foreach ($files as $file)
				if (is_dir(GOTMLS_trailingslashit($dir).$file))
					$scan_opts .= '<br /><input type="checkbox" name="scan_only[]" value="'.$file.'" />'.$file;
		$scan_opts .= '</div></div>';
	}
	$scan_optjs .= "document.getElementById('only'+what).style.display = 'block';\n}\n</script>";
	$scan_opts .= $scan_optjs.'</div><div style="float: left;" id="scanwhatfolder"></div><br style="clear: left;" /><p><b>Scan Depth:</b> (how far do you want to drill down from your starting directory)</p><div style="padding: 0 30px;"><input type="text" value="'.$GOTMLS_settings_array["scan_depth"].'" name="scan_depth"> (-1 is infinite depth)</div><p><b>What to look for:</b></p><div style="padding: 0 30px;">';//.print_r($GOTMLS_definitions_array,1).'</pre>';
//	$potential_threat = array(, '(Find and Automatially Repair files infected with malicious code)</a><div class="rounded-corners" style="display: none; background-color: #CCF; padding: 10px; z-index: 10;" id="dont_check_known"><a class="rounded-corners" style="float: right; padding: 0 4px; margin: 0 0 0 30px; text-decoration: none; color: #C00; background-color: #FCC; border: solid #F00 1px;" href="#check_known_div_0" onclick="showhide(\'dont_check_known\');">X</a><b>Don\'t&nbsp;Scan&nbsp;for&nbsp;These&nbsp;Patterns:</b>');
	foreach ($GOTMLS_threat_levels as $threat_name=>$threat_level) {
		$scan_opts .= '<div style="padding: 0;" id="check_'.$threat_level.'_div">';
		if (isset($GOTMLS_definitions_array[$threat_level]))
			$scan_opts .= '<input type="checkbox" name="check[]" id="check_'.$threat_level.'_Yes" value="'.$threat_level.'"'.(in_array($threat_level,$_SESSION["check"])?' checked':'').' /> <a style="text-decoration: none;" href="#check_'.$threat_level.'_div_0" onclick="document.getElementById(\'check_'.$threat_level.'_Yes\').checked=true;//showhide(\'dont_check_'.$threat_level.'\');">';
		else
			$scan_opts .= '<a title="Download Definition Updates to Use this feature"><img src="'.$GOTMLS_images_path.'blocked.gif" height=16 width=16 alt="X">';
//					if ($_SESSION["check"] != "potential")
		$scan_opts .= "<b>$threat_name</b></a>";
		if (!isset($GOTMLS_definitions_array[$threat_level]))
			$scan_opts .= '<br /><div style="padding: 14px;" id="check_'.$threat_level.'_div_NA">Registration of your Installation Key is required for this feature</div>';//'<br /><input type="checkbox" name="dont_check[]" value="'.htmlspecialchars($threat_name).'"'.(in_array($threat_name, $GOTMLS_settings_array["dont_check"])?' checked /><script>showhide("dont_check_'.(count($potential_threat)?'known':'potential').'", true);</script>':' />').$threat_name;
		$scan_opts .= '</div>';
	}
	if (isset($_GET['eli'])) $scan_opts .= '<div style="padding: 10px;"><b>Custom RegExp:</b> (For very advanced users only. Do not use this without talking to Eli first. If used incorrectly you could easily break your site.)<br /><input type="text" name="check_custom" style="width: 100%;" value="'.htmlspecialchars($GOTMLS_settings_array["check_custom"]).'" /></div>';//still testing this option
	$scan_opts .= '</div><p><b>Skip files with the following extentions:</b>(a comma separated list of file extentions to be excluded from the scan)</p><div style="padding: 0 30px;"><input type="text" name="exclude_ext" value="'.implode(",", $GOTMLS_settings_array["exclude_ext"]).'" style="width: 100%;" /></div><p><b>Skip directories with the following names:</b>(a comma separated list of folders to be excluded from the scan)</p><div style="padding: 0 30px;"><input type="text" name="exclude_dir" value="'.implode(",", $GOTMLS_settings_array["exclude_dir"]).'" style="width: 100%;" /></div><p style="text-align: right;"><input type="submit" onclick="document.getElementById(\'scan_type\').value=this.value;" id="complete_scan" value="Complete Scan" class="button-primary" />&nbsp;<input type="submit" onclick="document.getElementById(\'scan_type\').value=this.value;" id="quick_scan" value="Quick Scan" class="button-primary" /></p></form></div></div>';
	$menu_opts = '<div class="stuffbox shadowed-box">
	<h3 class="hndle"><span>Menu Item Placement Options</span></h3>
	<div class="inside"><form method="POST" name="GOTMLS_menu_Form">';
	foreach ($GOTMLS_menu_groups as $mg => $GOTMLS_menu_group)
		$menu_opts .= '<div style="padding: 4px;" id="menu_group_div_'.$mg.'"><input type="radio" name="GOTMLS_menu_group" value="'.$mg.'"'.($GOTMLS_settings_array["menu_group"]==$mg?' checked':'').' onchange="document.GOTMLS_menu_Form.submit();" />'.$GOTMLS_menu_group.'</div>';
	@ob_start();
	$OB_default_handler = "default output handler";
	foreach (ob_list_handlers() as $OB_last_handler)
		if ($OB_last_handler != $OB_default_handler)
			echo "<div class=\"error\">Another Plugin or Theme is using '$OB_last_handler' to hadle output buffers. <br />This prevents actively outputing the buffer on-the-fly and will severely degrade the performance of this (and many other) Plugins. <br />Consider disabling caching and compression plugins (at least during the scanning process).</div>";
	GOTMLS_display_header('Anti-Malware by <img style="vertical-align: middle;" alt="ELI" src="http://0.gravatar.com/avatar/69ad8428e97469d0dcd64f1f60c07bd8?s=64" /> at GOTMLS.NET', $menu_opts.'</form><br style="clear: left;" /></div></div>');
	$scan_groups = array_merge(array("Scanned Files"=>"scanned","Selected Folders"=>"dirs","Scanned Folders"=>"dir","Skipped Folders"=>"skipdirs","Skipped Files"=>"skipped","Read/Write Errors"=>"errors","Quarantined Files"=>"bad"), $GOTMLS_threat_levels);
	echo '<script type="text/javascript">
var percent = 0;
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
		title = "<b>Scan Complete!</b>";
	} else
		scan_state = "99F";
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
	divHTML = \'<div align="center" style="vertical-align: middle; background-color: #ccc; z-index: 3; height: 18px; width: 100%; border: solid #000 1px; position: relative; padding: 10px 0;"><div style="height: 18px; padding: 10px 0; position: absolute; top: 0px; left: 0px; background-color: #\'+scan_state+\'; width: \'+percent+\'%"></div><div style="height: 32px; position: absolute; top: 3px; left: 10px; z-index: 5;" align="left">\'+sdir+" Folder"+(sdir==1?"":"s")+" Checked<br />"+timeElapsed+\' Elapsed</div><div style="height: 38px; position: absolute; top: 0px; left: 0px; width: 100%; z-index: 5; line-height: 38px; font-size: 30px; text-align: center;">\'+percent+\'%</div><div style="height: 32px; position: absolute; top: 3px; right: 10px; z-index: 5;" align="right">\'+(dirs-sdir)+" Folder"+((dirs-sdir)==1?"":"s")+" Remaining<br />"+timeRemaining+" Remaining</div></div>";
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
			if ($found && !in_array($scan_group, $_SESSION["check"]))
				$potential_threat = ' potential" title="You are not currently scanning for this type of threat!';
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
var IE = document.all?true:false;
if (!IE) document.captureEvents(Event.MOUSEMOVE)
document.onmousemove = getMouseXY;
var offsetX = 0;
var offsetY = 0;
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
	if (offsetX > 0) {curDiv.style.left = (curX - offsetX)+"px";}
	if (offsetY > 0) {curDiv.style.top = (curY - offsetY)+"px";}
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
	document.getElementById("GOTMLS_iFrame").style.display="none";
	offsetX=curX-px2num(curDiv.style.left); 
	offsetY=curY-px2num(curDiv.style.top);
}
function releaseDiv() {
	document.getElementById("GOTMLS_iFrame").style.display="block";
	offsetX=0; 
	offsetY=0;
}
var startTime = 0;
</script>
<div class="metabox-holder GOTMLS" style="width: 100%;" id="GOTMLS-Settings"><div class="postbox shadowed-box">
	<div title="Click to toggle" onclick="showhide(\'GOTMLS-Settings-Form\');" class="handlediv"><br></div>
	<h3 title="Click to toggle" onclick="showhide(\'GOTMLS-Settings-Form\');" style="cursor: pointer;" class="hndle"><span>Scan Settings</span></h3>
	<div id="GOTMLS-Settings-Form" class="inside"';
	if ((isset($_REQUEST['scan_type']) && ($_REQUEST['scan_type'] == 'Quarantine')) || (isset($_REQUEST['scan_what']) && is_numeric($_REQUEST['scan_what']))) {
		if (!isset($_REQUEST['scan_type']))
			$_REQUEST['scan_type'] = 'Quick Scan';
		echo ' style="display: none;"'.$scan_opts;
		update_option($GOTMLS_plugin_dir.'_settings_array', $GOTMLS_settings_array);
		echo '<form method="POST" target="GOTMLS_iFrame" name="GOTMLS_Form_clean"><input type="hidden" name="GOTMLS_fixing" value="1"><div class="postbox shadowed-box"><div title="Click to toggle" onclick="showhide(\'GOTMLS-Scan-Progress\');" class="handlediv"><br></div><h3 title="Click to toggle" onclick="showhide(\'GOTMLS-Scan-Progress\');" style="cursor: pointer;" class="hndle"><span>'.$_REQUEST['scan_type'].' Status</span></h3>';
		if ($_REQUEST['scan_type'] != 'Quarantine') {
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
			echo '<div id="status_text"><img src="'.$GOTMLS_images_path.'wait.gif" height=16 width=16 alt="..."> Loading Scan, Please Wait ...</div><div id="status_bar"></div><p id="pause_button" style="display: none; position: absolute; text-align: center; margin-left: -30px; padding-left: 50%;"><input type="button" value="Pause" class="button-primary" onclick="pauseresume(this);" id="resume_button" /></p><div id="status_counts"></div><p id="fix_button" style="display: none; text-align: center;"><input id="repair_button" type="submit" value="Automatically Repair SELECTED files Now" class="button-primary" onclick="showhide(\'GOTMLS_iFrame\', true);showhide(\'GOTMLS_iFrame\');showhide(\'div_file\', true);" /></p></div></div>
			<div class="postbox shadowed-box"><div title="Click to toggle" onclick="showhide(\'GOTMLS-Scan-Details\');" class="handlediv"><br></div><h3 title="Click to toggle" onclick="showhide(\'GOTMLS-Scan-Details\');" style="cursor: pointer;" class="hndle"><span>Scan Details:</span></h3>';
		}
		echo '<div id="GOTMLS-Scan-Details" class="inside"><div onmousedown="grabDiv();" onmouseup="releaseDiv();" id="div_file" class="shadowed-box rounded-corners sidebar-box" style="display: none; position: fixed; top: 100px; left: 100px; width: 80%; border: solid #c00; z-index: 112358;"><a class="rounded-corners" name="link_file" style="float: right; padding: 0 4px; margin: 6px; text-decoration: none; color: #C00; background-color: #FCC; border: solid #F00 1px;" href="#found_top" onclick="showhide(\'div_file\');">X</a><h3 style="border-radius: 10px 10px 0 0; -moz-border-radius: 10px 10px 0 0; -webkit-border-radius: 10px 10px 0 0;">Examine Results</h3><div style="width: 100%; height: 400px; position: relative; padding: 0; margin: 0;" class="inside"><br /><br /><center><img src="'.$GOTMLS_images_path.'wait.gif" height=16 width=16 alt="..."> Loading Results, Please Wait ...<br /><br /><input type="button" onclick="showhide(\'GOTMLS_iFrame\', true);" value="It\'s taking too long ... I can\'t wait ... show me the results!" class="button-primary" /></center><iframe id="GOTMLS_iFrame" name="GOTMLS_iFrame" style="top: 0px; left: 0px; width: 100%; height: 400px; background-color: #CCC; position: absolute;"></iframe></div></div><script type="text/javascript">
		setDiv("div_file");
		</script>';
		if ($_REQUEST["scan_type"] == "Quarantine") {
			$entries = GOTMLS_getfiles($GOTMLS_quarantine_dir);
			echo GOTMLS_scan_log()."\n<ul name=\"found_Quarantine\" id=\"found_Quarantine\" class=\"GOTMLS_plugin known\" style=\"background-color: #ccc; padding: 0;\"><h3>";
			if (is_array($entries) && ($key = array_search(".htaccess", $entries)))
				unset($entries[$key]);
			if (is_array($entries) && ($key = array_search("index.php", $entries)))
				unset($entries[$key]);
			if (is_array($entries) && count($entries)) {
				echo count($entries).' Item'.(count($entries)==1?'':'s').' in Quarantine<span style="float: right;">Date Quarantined</span></h3><p id="fix_button" style="display: none; float: right;"><input id="repair_button" type="submit" value="Restore SELECTED files from Quarantine" class="button-primary" onclick="if (confirm(\'Are you sure you want to overwrite the previously cleaned files with the selected files in the Quarantine?\')) { showhide(\'GOTMLS_iFrame\', true); showhide(\'GOTMLS_iFrame\'); showhide(\'div_file\', true); } else return false;" /></p><p><b>The following items have been found to contain malicious code, they have been cleaned, and the original infected file contents have been saved here in the Quarantine. The code is safe here and you do not need to do anything further with these files.</b></p> FYI - these files are found in '.$GOTMLS_quarantine_dir;
				sort($entries);
				foreach ($entries as $entry) {
					$file = GOTMLS_trailingslashit($GOTMLS_quarantine_dir).$entry;
					$date = date("y-m-d-H-i",filemtime($file));
					echo "<li><img src='$GOTMLS_images_path/blocked.gif' height=16 width=16 alt='Q' style='float: left;'>";
					if (is_file($file) && GOTMLS_get_ext($entry) == "gotmls") {
						$file_date = explode(".", $entry);
						if (count($file_date) > 2 && strlen($file_date[0]) == 5)
							$date = GOTMLS_sexagesimal($file_date[0]);
						elseif (@rename($file, GOTMLS_trailingslashit($GOTMLS_quarantine_dir).GOTMLS_sexagesimal($date).".$entry"))
							$file = GOTMLS_trailingslashit($GOTMLS_quarantine_dir).GOTMLS_sexagesimal($date).".$entry";
						echo '<input type="checkbox" value="1" name="GOTMLS_fix['.GOTMLS_encode($file).']" id="check_'.GOTMLS_encode($file).'" onchange="document.getElementById(\'fix_button\').style.display = \'block\';" />'.GOTMLS_error_link("View Quarantined File", $file).GOTMLS_decode($file_date[count($file_date)-2]);
					} else
						echo '<li><img src="'.$GOTMLS_images_path.'/blocked.gif" height=16 width=16 alt="?" style="float: left;">'.GOTMLS_error_link("Foreign File in Quarantine", $file).$file;
					$date = explode("-", $date);
					echo "</a> <span style='float: right; margin-right: 8px;'>(20$date[0]-$date[1]-$date[2] at $date[3]:$date[4])</span></li>";
				}
			} else
				echo "No Items in Quarantine</h3>";
			echo "</ul>";//</form>";
		} elseif ($_REQUEST['scan_what'] > -1) {
			$dir = implode(GOTMLS_slash(), array_slice($dirs, 0, -1 * (2 + $_REQUEST['scan_what'])));
			foreach ($scan_groups as $scan_name => $scan_group)
				echo "\n<ul name=\"found_$scan_group\" id=\"found_$scan_group\" class=\"GOTMLS_plugin $scan_group\" style=\"background-color: #ccc; display: none; padding: 0;\"><a class=\"rounded-corners\" name=\"link_$scan_group\" style=\"float: right; padding: 0 4px; margin: 5px 5px 0 30px; text-decoration: none; color: #C00; background-color: #FCC; border: solid #F00 1px;\" href=\"#found_top\" onclick=\"showhide('found_$scan_group');\">X</a><h3>$scan_name</h3>\n".($scan_group=='potential'?'<br /> * NOTE: These are probably not malicious scripts (but it\'s a good place to start looking <u>IF</u> your site is infected and no Known Threats were found).<br /><br />':'<br />').'</ul>';// (<a href=\"javascript:void(0)\" onclick=\"selectWholeGroup('found_$scan_group');\">Select All</a>) (<a href=\"javascript:void(0)\" onclick=\"selectNoneGroup('found_$scan_group');\">Select None</a>)
			$_SESSION["GOTMLS_LAST_scan_start"] = time();
			$GOTMLS_scan_logs_array["LAST_SCAN_start"] = $_SESSION["GOTMLS_LAST_scan_start"];
			$GOTMLS_scan_logs_array["LAST_SCAN_type"] = $_REQUEST["scan_type"];
			update_option("GOTMLS_scan_logs_array", $GOTMLS_scan_logs_array);
			while ($OB_last_handler == $OB_default_handler && @ob_end_flush())
				foreach (ob_list_handlers() as $OB_handler)
					$OB_last_handler = $OB_handler;
			@ob_start();
			if ($_REQUEST["scan_type"] == "Quick Scan")
				$li_js = "\nfunction testComplete() {\n\tif (percent != 100)\n\t\talert('The Quick Scan was unable to finish because of a shortage of memory or a problem accessing a file. Please try using the Complete Scan, it is slower but it will handle these errors better and continue scanning the rest of the files.');\n}\nwindow.onload=testComplete;\n</script>\n<script type=\"text/javascript\">";
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
			if ($_REQUEST["scan_type"] == "Quick Scan") {
				echo GOTMLS_update_status("Completed!", 100);
				$GOTMLS_scan_logs_array["LAST_SCAN_finish"] = time();
				update_option("GOTMLS_scan_logs_array", $GOTMLS_scan_logs_array);
			} else {
				echo GOTMLS_update_status("Starting Scan ...").'/*-->*/';
				echo "\nvar scriptSRC = '$GOTMLS_script_URI&GOTMLS_scan=';\nvar scanfilesArKeys = new Array('".implode("','", array_keys($GOTMLS_scanfiles))."');\nvar scanfilesArNames = new Array('Scanning ".implode("','Scanning ", $GOTMLS_scanfiles)."');".'
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
startTime = ('.ceil(time()-$_SESSION['GOTMLS_LAST_scan_start']).'+3);
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
		echo $scan_opts.'<div class="postbox shadowed-box"><div title="Click to toggle" onclick="showhide(\'GOTMLS-Scan-Progress\');" class="handlediv"><br></div><h3 title="Click to toggle" onclick="showhide(\'GOTMLS-Scan-Progress\');" style="cursor: pointer;" class="hndle"><span>Last Scan Status</span></h3><div id="GOTMLS-Scan-Progress" class="inside">'.GOTMLS_scan_log()."\n</div></div>";
	}
	echo "\n</div></div></div>";
$_SESSION["GOTMLS_debug"][(microtime(true)-$_SESSION["GOTMLS_debug"]["START_microtime"]).' GOTMLS_Settings_end'] = GOTMLS_memory_usage(true);
}

function GOTMLS_set_plugin_action_links($links_array, $plugin_file) {
	if ($plugin_file == substr(__file__, (-1 * strlen($plugin_file))) && strlen($plugin_file) > 10) {
		$_SESSION["GOTMLS_debug"][(microtime(true)-$_SESSION["GOTMLS_debug"]["START_microtime"]).' GOTMLS_set_plugin_action_links'] = GOTMLS_memory_usage(true);
		$base_page = "admin.php";
		$links_array = array_merge(array('<a href="'.$base_page.'?page=GOTMLS-settings&scan_type=Quick+Scan">Quick Scan</a>', '<a href="'.$base_page.'?page=GOTMLS-settings">Settings</a>'), $links_array);
	}
	return $links_array;
}

function GOTMLS_set_plugin_row_meta($links_array, $plugin_file) {
	if ($plugin_file == substr(__file__, (-1 * strlen($plugin_file))) && strlen($plugin_file) > 10)
		$links_array = array_merge($links_array, array('<a target="_blank" href="http://gotmls.net/faqs/">FAQ</a>','<a target="_blank" href="http://gotmls.net/support/">Support</a>','<a target="_blank" href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=QZHD8QHZ2E7PE">Donate</a>'));
	return $links_array;
}

function GOTMLS_init() {
	global $GOTMLS_update_home, $GOTMLS_updated_definition_path, $GOTMLS_settings_array, $GOTMLS_url, $GOTMLS_onLoad, $GOTMLS_threat_levels, $wpdb, $GOTMLS_threats_found, $GOTMLS_settings_array, $GOTMLS_definitions_versions, $GOTMLS_definitions_array, $GOTMLS_plugin_dir, $GOTMLS_local_images_path, $GOTMLS_images_path, $GOTMLS_file_contents, $GOTMLS_script_URI, $GOTMLS_skip_ext;
$_SESSION["GOTMLS_debug"][(microtime(true)-$_SESSION["GOTMLS_debug"]["START_microtime"]).' GOTMLS_init_start'] = GOTMLS_memory_usage(true);
	if (!isset($GOTMLS_settings_array["scan_what"]))
		$GOTMLS_settings_array["scan_what"] = 2;
	if (!isset($GOTMLS_settings_array["scan_depth"]))
		$GOTMLS_settings_array["scan_depth"] = -1;
	if (isset($_REQUEST["scan_type"]) && $_REQUEST["scan_type"] == "Quick Scan") {
		if (!isset($_REQUEST["scan_what"]))
			$_REQUEST["scan_what"] = $GOTMLS_settings_array["scan_what"];
		if (!isset($_REQUEST["scan_depth"]))
			$_REQUEST["scan_depth"] = $GOTMLS_settings_array["scan_depth"];
	}//$GOTMLS_settings_array["check_custom"] = stripslashes($_POST["check_custom"]);
	if (isset($GOTMLS_settings_array["check_custom"]) && strlen($GOTMLS_settings_array["check_custom"]))
		$_SESSION["check_custom"] = $GOTMLS_settings_array["check_custom"];
	else
		$_SESSION["check_custom"] = "";
	if (isset($GOTMLS_settings_array["scan_level"]) && is_numeric($GOTMLS_settings_array["scan_level"]))
		$scan_level = intval($GOTMLS_settings_array["scan_level"]);
	else
		$scan_level = 3;
	if (!(isset($GOTMLS_settings_array["dont_check"]) && is_array($GOTMLS_settings_array["dont_check"])))
		$GOTMLS_settings_array["dont_check"] = array();
	if (isset($_REQUEST["dont_check"]) && is_array($_REQUEST["dont_check"]) && count($_REQUEST["dont_check"]))
		$GOTMLS_settings_array["dont_check"] = $_REQUEST["dont_check"];
	if ($array = get_option($GOTMLS_plugin_dir.'_definitions_array')) {
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
		$GOTnew_definitions = explode("-+====+-", '-+====+-'.GOTMLS_decode($_POST["UPDATE_definitions_array"]));
		GOTMLS_set_global($GOTMLS_onLoad, "check_for_updates('Downloaded Definitions');");
	} elseif (file_exists(GOTMLS_trailingslashit(dirname(__FILE__)).'definitions_update.txt'))
		$GOTnew_definitions = explode("-+====+-", '-+====+-'.GOTMLS_decode(file_get_contents(GOTMLS_trailingslashit(dirname(__FILE__)).'definitions_update.txt')));
	if (isset($GOTnew_definitions) && is_array($GOTnew_definitions)) {
		for ($type = 1; $type < count($GOTnew_definitions); $type++) {
			$GOTnew_type = explode("-+===+-", '-+===+-'.$GOTnew_definitions[$type]);
			for ($definition = 2; $definition < count($GOTnew_type); $definition++) {
				$GOTnew_definition = explode("-+==+-", '-+==+-'.$GOTnew_type[$definition]);
				if (count($GOTnew_definition) == 3 && ($array = explode("-+=+-", $GOTnew_definition[2]))) {
					if (is_array($array) && count($array) > 1 && (!(isset($GOTMLS_definitions_array[$GOTnew_type[1]][$GOTnew_definition[1]]) && is_array($GOTMLS_definitions_array[$GOTnew_type[1]][$GOTnew_definition[1]])) || $array[0] > $GOTMLS_definitions_array[$GOTnew_type[1]][$GOTnew_definition[1]][0])) {
						if (strlen($array[1]) > 2)
							$GOTMLS_definitions_array[$GOTnew_type[1]][$GOTnew_definition[1]] = $array;
						else
							unset($GOTMLS_definitions_array[$GOTnew_type[1]][$GOTnew_definition[1]]);
						if ($array[0] > $GOTMLS_definitions_versions[$GOTnew_type[1]])
							$GOTMLS_definitions_versions[$GOTnew_type[1]] = $array[0];
					}
				}
			}
		}
		if (file_exists(GOTMLS_trailingslashit(dirname(__FILE__)).'definitions_update.txt'))
			@unlink(GOTMLS_trailingslashit(dirname(__FILE__)).'definitions_update.txt');
		update_option($GOTMLS_plugin_dir.'_definitions_array', $GOTMLS_definitions_array);
		if (isset($_SESSION["check"]))
			unset($_SESSION["check"]);
	}
	asort($GOTMLS_definitions_versions);
	if (isset($_REQUEST["check"]) && is_array($_REQUEST["check"]))
		$_SESSION["check"] = $_REQUEST["check"];
/*	$threat_names = array_keys($GOTMLS_definitions_array["known"]);
	foreach ($threat_names as $threat_name) {
		if (isset($GOTMLS_definitions_array["known"][$threat_name]) && is_array($GOTMLS_definitions_array["known"][$threat_name]) && count($GOTMLS_definitions_array["known"][$threat_name]) > 1) {
			if ($GOTMLS_definitions_array["known"][$threat_name][0] > $GOTMLS_definitions_version)
				$GOTMLS_definitions_version = $GOTMLS_definitions_array["known"][$threat_name][0];
			if (!(count($GOTMLS_settings_array["dont_check"]) && in_array($threat_name, $GOTMLS_settings_array["dont_check"]))) {
				$GOTMLS_threat_levels[$threat_name] = count($GOTMLS_definitions_array["known"][$threat_name]);
				if (!isset($_SESSION["check"]) && $GOTMLS_threat_levels[$threat_name] > 2)
					$_SESSION["check"] = "known";
			}
		}
	}*/
	if (!isset($_SESSION["check"]))//	foreach ($GOTMLS_threat_levels as $threat_level)
		$_SESSION["check"] = $GOTMLS_threat_levels;
	if (isset($_POST["GOTMLS_fix"]) && !is_array($_POST["GOTMLS_fix"]))
		$_POST["GOTMLS_fix"] = array($_POST["GOTMLS_fix"]=>1);
	if (isset($_POST['GOTMLS_whitelist']) && isset($_POST['GOTMLS_chksum'])) {
		$file = GOTMLS_decode($_POST['GOTMLS_whitelist']);
		$chksum = explode("O", $_POST['GOTMLS_chksum']."O");
		if (strlen($chksum[0]) == 32 && strlen($chksum[1]) == 32 && is_file($file) && md5(@file_get_contents($file)) == $chksum[0]) {
			if (true)
				$GOTMLS_definitions_array["whitelist"][$file] = array("A0002", $chksum[0]);
			else
				unset($GOTMLS_definitions_array["whitelist"][$file]);
			update_option("GOTMLS_definitions_array", $GOTMLS_definitions_array);
			die("<html><body>Added $file to Whitelist!<br /><iframe style='width: 90%; height: 350px;' src='$GOTMLS_update_home$GOTMLS_updated_definition_path?whitelist=".$_POST['GOTMLS_whitelist']."&hash=$chksum[0]&key=$chksum[1]'></iframe></body></html>");
		} else echo "<li>Invalid Data!</li>";
	} elseif (isset($_GET["GOTMLS_scan"])) {
		$file = GOTMLS_decode($_GET["GOTMLS_scan"]);
		if (is_dir($file)) {
			@header("Content-type: text/javascript");
			if (isset($GOTMLS_settings_array["exclude_ext"]) && is_array($GOTMLS_settings_array["exclude_ext"]))
				$GOTMLS_skip_ext = $GOTMLS_settings_array["exclude_ext"];
			die(GOTMLS_scandir($file));
		} else {
			if (!file_exists($file))
				die("\nThe file $file does not exist.<br />\n".(file_exists(GOTMLS_quarantine($file))?'You could <a target="GOTMLS_iFrame" href="'.$GOTMLS_script_URI.'&GOTMLS_scan='.GOTMLS_encode(GOTMLS_quarantine($file)).'">try viewing the quarantined backup file</a>.':'The file must have already been delete.'));
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
							$fa .= ' <a title="'.htmlspecialchars($threats_name).'" href="javascript:select_text_range(\'ta_file\', '.($fpos).', '.($fpos + $flen).');">Found['.$f++.']</a>';
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
</script><form method="post"'.(is_file($clean_file)?' onsubmit="return confirm(\'Are you sure this file is not infected and you want to ignore it in future scans?\');"><input type="submit" value="Whitelist this file" style="float: right;"><input type="hidden" name="GOTMLS_whitelist" value="'.GOTMLS_encode($clean_file).'"><input type="hidden" name="GOTMLS_chksum" value="'.md5($GOTMLS_file_contents).'O'.md5($GOTMLS_url).'">':'>').$clean_file.'<br />Potential threats in file: ('.$fa.' ) <br /><textarea id="ta_file" width="100%" style="width: 100%;" rows="20">'.htmlentities(str_replace("\r", "", $GOTMLS_file_contents)).'</textarea></form>');
			}
		}
	} elseif (isset($_POST['GOTMLS_fix']) && is_array($_POST['GOTMLS_fix'])) {
		$callAlert = "clearTimeout(callAlert);\ncallAlert=setTimeout('alert_repaired(1)', 30000);";
		$li_js = "\n<script type=\"text/javascript\">\nvar callAlert;\nfunction alert_repaired(failed) {\nclearTimeout(callAlert);\nif (failed)\nfilesFailed='the rest, try the Auto-Repair again to fix more.';\nwindow.parent.check_for_donation('Repaired '+filesFixed+' files, failed to repair '+filesFailed);\n}\n$callAlert\nwindow.parent.showhide('GOTMLS_iFrame', true);\nfilesFixed=0;\nfilesFailed=0;\nfunction fixedFile(file) {\n filesFixed++;\nwindow.parent.document.getElementById('list_'+file).className='GOTMLS_plugin';\nwindow.parent.document.getElementById('check_'+file).checked=false;\n }\n function failedFile(file) {\n filesFailed++;\nwindow.parent.document.getElementById('check_'+file).checked=false; \n}\n</script>\n<script type=\"text/javascript\">\n/*<!--*/";
		foreach ($_POST["GOTMLS_fix"] as $path => $val) {
			if (file_exists(GOTMLS_decode($path)) && $val) {
				echo '<li>fixing '.GOTMLS_decode($path).' ...';
				$li_js .= GOTMLS_scanfile(GOTMLS_decode($path));
				echo "</li>\n$li_js/*-->*/\n$callAlert\n</script>\n";
				$li_js = "<script type=\"text/javascript\">\n/*<!--*/";
			}
		}
		die('<div id="check_site_warning" style="background-color: #F00;">Because some threats were automatically fixed we need to check to make sure the removal did not break your site. If this stays Red and the frame below does not load please <a target="test_frame" href="admin.php?page=GOTMLS-settings&scan_type=Quarantine">revert the changes</a> made during the automated repair process. <span style="color: #F00;">Never mind, it worked!</span></div><br /><iframe id="test_frame" name="test_frame" src="'.$GOTMLS_script_URI.'&check_site=1" style="width: 100%; height: 200px"></iframe>'.$li_js."/*-->*/\nalert_repaired(0);\n</script>\n");
	} elseif (isset($_POST["GOTMLS_fixing"]))
		die("<script type=\"text/javascript\">\nwindow.parent.showhide('GOTMLS_iFrame', true);\nalert('Nothing Selected to be Fixed!');\n</script>Done!");
	if (isset($_POST["scan_level"]) && is_numeric($_POST["scan_level"]))
		$scan_level = intval($_POST["scan_level"]);
	if (isset($scan_level) && is_numeric($scan_level))
		$GOTMLS_settings_array["scan_level"] = intval($scan_level);
	else
		$GOTMLS_settings_array["scan_level"] = 3;
//	$GOTMLS_settings_array["updated"] = date("y-m-d-H-m-s");
	update_option($GOTMLS_plugin_dir.'_settings_array', $GOTMLS_settings_array);
$_SESSION["GOTMLS_debug"][(microtime(true)-$_SESSION["GOTMLS_debug"]["START_microtime"]).' GOTMLS_init_end'] = GOTMLS_memory_usage(true);
}

if (function_exists('is_admin') && is_admin() && ((isset($_POST['GOTMLS_whitelist']) && isset($_POST['GOTMLS_chksum'])) || (isset($_GET["GOTMLS_scan"]) && is_dir(GOTMLS_decode($_GET["GOTMLS_scan"]))))) {
	@set_time_limit($GOTMLS_loop_execution_time-5);
	GOTMLS_init();
	die("\n//PHP to Javascript Error!\n");
} else {
	add_filter("plugin_row_meta", $GOTMLS_plugin_dir.'_set_plugin_row_meta', 1, 2);
	add_filter("plugin_action_links", $GOTMLS_plugin_dir.'_set_plugin_action_links', 1, 2);
	add_action("admin_menu", "GOTMLS_menu");
	$init = add_action("admin_init", $GOTMLS_plugin_dir.'_init');
}
$_SESSION["GOTMLS_debug"]["START_memory_usage"] = GOTMLS_memory_usage(true);
?>