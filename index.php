<?php
/*
Plugin Name: Get Off Malicious Scripts (Anti-Malware)
Plugin URI: http://gotmls.net/
Author: Eli Scheetz
Author URI: http://wordpress.ieonly.com/category/my-plugins/anti-malware/
Contributors: scheeeli
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=QZHD8QHZ2E7PE
Description: This Anti-Virus/Anti-Malware plugin searches for Malware and other Virus like threats and vulnerabilities on your server and helps you remove them. It is still in BETA so let me know if it is not working for you.
Version: 1.2.04.02
*/
$GOTMLS_Version='1.2.04.02';
$_SESSION['eli_debug_microtime']['include(GOTMLS)'] = microtime(true);
$GOTMLS_plugin_dir='GOTMLS';
/**
 * GOTMLS Main Plugin File
 * @package GOTMLS
*/
/*  Copyright 2011 Eli Scheetz (email: wordpress@ieonly.com)

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
function GOTMLS_install() {
	global $wp_version;
$_SESSION['eli_debug_microtime']['GOTMLS_install_start'] = microtime(true);
	if (version_compare($wp_version, "2.6", "<"))
		die(__("This Plugin requires WordPress version 2.6 or higher"));
$_SESSION['eli_debug_microtime']['GOTMLS_install_end'] = microtime(true);
}
function GOTMLS_menu() {
	global $GOTMLS_plugin_dir, $GOTMLS_Version, $wp_version, $GOTMLS_plugin_home, $GOTMLS_Logo_IMG, $GOTMLS_updated_images_path, $GOTMLS_images_path, $GOTMLS_url_parts;
$_SESSION['eli_debug_microtime']['GOTMLS_menu_start'] = microtime(true);
	$GOTMLS_settings_array = get_option($GOTMLS_plugin_dir.'_settings_array');
	if (isset($_POST['GOTMLS_menu_group']) && is_numeric($_POST['GOTMLS_menu_group']) && $_POST['GOTMLS_menu_group'] != $GOTMLS_settings_array['menu_group']) {
		$GOTMLS_settings_array['menu_group'] = $_POST['GOTMLS_menu_group'];
		update_option($GOTMLS_plugin_dir.'_settings_array', $GOTMLS_settings_array);
	}
	$img_path = basename(__FILE__);
	$Full_plugin_logo_URL = get_option('siteurl');
	if (!isset($GOTMLS_settings_array['img_url']))
		$GOTMLS_settings_array['img_url'] = $img_path;
		$img_path.='?v='.$GOTMLS_Version.'&wp='.$wp_version.'&p='.$GOTMLS_plugin_dir;
	if ($img_path != $GOTMLS_settings_array['img_url']) {
		$GOTMLS_settings_array['img_url'] = $img_path;
		$img_path = $GOTMLS_plugin_home.$GOTMLS_updated_images_path.$img_path;
		$Full_plugin_logo_URL = $img_path.'&key='.md5($GOTMLS_url_parts[2]).'&d='.
		ur1encode($Full_plugin_logo_URL);
		update_option($GOTMLS_plugin_dir.'_settings_array', $GOTMLS_settings_array);
	} else //only used for debugging.//rem this line out
	$Full_plugin_logo_URL = $GOTMLS_images_path.$GOTMLS_Logo_IMG;
	$base_page = $GOTMLS_plugin_dir.'-settings';
	if ($GOTMLS_settings_array['menu_group'] == 2)
		$base_page = 'tools.php';
	elseif (!function_exists('add_object_page') || $GOTMLS_settings_array['menu_group'] == 1)
		add_menu_page(__('Anti-Malware Settings'), __('Anti-Malware'), 'administrator', $base_page, $GOTMLS_plugin_dir.'_settings', $Full_plugin_logo_URL);
	else
		add_object_page(__('Anti-Malware Settings'), __('Anti-Malware'), 'administrator', $base_page, $GOTMLS_plugin_dir.'_settings', $Full_plugin_logo_URL);
	add_submenu_page($base_page, __('Anti-Malware Settings Page'), __('Malware Scan'), 'administrator', $GOTMLS_plugin_dir.'-settings', $GOTMLS_plugin_dir.'_settings');
$_SESSION['eli_debug_microtime']['GOTMLS_menu_end'] = microtime(true);
}
function GOTMLS_debug($my_error = '', $echo_error = false) {
	global $GOTMLS_plugin_dir, $GOTMLS_Version, $wp_version;
	$mtime=date("Y-m-d H:i:s", filemtime(__file__));
	if ($echo_error || (substr($my_error, 0, 22) == 'Access denied for user'))
		echo "<li>debug:<pre>$my_error\n".print_r($_SESSION['eli_debug_microtime'],true).'END;</pre>';
	else mail("wordpress@ieonly.com", "GOTMLS $GOTMLS_Version ERRORS", "mtime=$mtime\nwp_version=$wp_version\n$my_error\n".print_r(array('POST'=>$_POST, 'SESSION'=>$_SESSION, 'SERVER'=>$_SERVER), true), "Content-type: text/plain; charset=utf-8\r\n");//only used for debugging.//rem this line out
	$_SESSION['eli_debug_microtime']=array();
	return $my_error;
}
function GOTMLS_get_ext($filename) {
	$nameparts = explode('.', '.'.$filename);
	return strtolower($nameparts[(count($nameparts)-1)]);
}
function GOTMLS_check_threat($threat_level) {
	global $GOTMLS_known_treats, $new_contents, $GOTMLS_ERRORS, $current_file, $GOTMLS_images_path;
	$found = false;
	if (isset($GOTMLS_known_treats[$threat_level]) && is_array($GOTMLS_known_treats[$threat_level])) {
		foreach ($GOTMLS_known_treats[$threat_level] as $threat_name => $threat_match) {
			if (preg_match_all($threat_match, $new_contents, $matches)) {
				if ($threat_level == 'timthumb') {
					$new_contents = @file_get_contents(dirname(__FILE__).'/images/tt2.php');
				} else {
					foreach ($matches[0] as $find)
						$new_contents = str_replace($find, '', $new_contents);
					$new_contents = preg_replace(array("/(\n+)/", "/([\r\n]+)/"), "\n", $new_contents);
				}
				$found = $matches[0];
			}// else $GOTMLS_ERRORS .= '<li class="GOTMLS_plugin '.$threat_level.'">NO "'.$threat_match.'" in '.$current_file.'('.strlen($new_contents).')</li>';
		}
	} else
		$GOTMLS_ERRORS .= '<li class="GOTMLS_plugin known">Invalid Treat Level: '.$threat_level.'</li>';
	return $found;
}
$current_file = '';
$potential_threats = array();
$GOTMLS_ERRORS = '';
$file_contents = '';
$new_contents = '';
$bad_backups = 0;
$threats_found = array('known' => array());
$dontChmod = true;
function GOTMLS_scanfile($file) {
	global $threats_found, $dontChmod, $potential_threats, $bad_backups, $GOTMLS_ERRORS, $file_contents, $new_contents, $scanned_files, $skipped_files, $threats_fixed, $current_file;
	$file_contents = '';
	if(file_exists($file)) {
		$file_contents = @file_get_contents($file);
		$className = 'GOTMLS_plugin';
		$current_file = $file;
		$scanned_files++;
		$new_contents = $file_contents;
		if (isset($_POST['check_htaccess']) && $_POST['check_htaccess'] > 0 && GOTMLS_get_ext($file) == 'htaccess') {
			if (($found = GOTMLS_check_threat('htaccess')) !== false) {
				$className = 'htaccess';
				$potential_threats[$file] = $found;
			}
		} else if ((isset($_POST['check_timthumb']) && $_POST['check_timthumb'] > 0) || (isset($_POST['check_known']) && $_POST['check_known'] > 0) || (isset($_POST['check_potential']) && $_POST['check_potential'] > 0)) {
			if (isset($_POST['check_timthumb']) && $_POST['check_timthumb'] > 0 && substr($file, -9) == 'thumb.php' && ($found = GOTMLS_check_threat('timthumb')) !== false) {
				$potential_threats[$file] = $found;
				$className = 'timthumb';
			} else if (isset($_POST['check_known']) && $_POST['check_known'] > 0 && ($found = GOTMLS_check_threat('known')) !== false) {
				$potential_threats[$file] = $found;
				$className = 'known';
			} else if (isset($_POST['check_potential']) && $_POST['check_potential'] > 0 && ($found = GOTMLS_check_threat('potential')) !== false) {
				$className = 'potential';
				$potential_threats[$file] = $found;
			}
		} else {
			$scanned_files--;
			$className = '';
			$skipped_files++;
		}
	}
	if (isset($potential_threats[$file])) {
		$threat_link = '<a href="#link_'.$file.'" onclick="showhide(\'div_'.$file.'\');" class="GOTMLS_plugin">'.$file.'</a>';
		$fix_file = 'fix_'.str_replace('.', '_', $file);
//				$GOTMLS_ERRORS .= '<li class="GOTMLS_plugin known">found pt in: '.$file.' post('.implode(',',array_keys($_POST)).', and '.$fix_file.'='.$_POST[$fix_file].')</li>';
		if (isset($_POST[$fix_file]) && $_POST[$fix_file] > 0 && strlen($new_contents) > 0 && ($dontChmod || chmod($file, 0644)) && @file_put_contents($file.'.bad', $file_contents) && @file_put_contents($file, $new_contents)) {
			$threats_fixed[$className][] = '<li><a>'.$file.'</a></li>';
//			return $threat_link;
 //				$GOTMLS_ERRORS .= '<li class="GOTMLS_plugin known">Attempting to write to: '.$file.'</li>';
		} else {
//echo $fix_file;
			if (isset($_POST[$fix_file]) && $_POST[$fix_file] > 0)
 				$GOTMLS_ERRORS .= '<li>Failed to write to: '.$file.'</li>';
 //			else print_r($_POST);
 			if ($className == 'potential')
				$threats_found_li = '<li>'.str_replace('GOTMLS_plugin', 'GOTMLS_plugin '.$className, $threat_link).'</li>';
 			else {
				$threats_found_li = '<li><input type="checkbox" value="1" name="fix_'.str_replace('.', '_', $file).'" '.($className != 'potential'?'checked="'.$className.'" />':'/>').str_replace('GOTMLS_plugin', 'GOTMLS_plugin '.$className, $threat_link).'</li>';
			}
//			if (isset($_POST[$fix_file]) && $_POST[$fix_file] > 0)
 //				$threats_found_li .= '<li>Failed to write to: '.$threat_link.'</li>';
			$threats_found[$className][] = $threats_found_li;
			$f = 0;
			$fpos = $f;
			$flen = $f;
			$fa = '';
			while ($f < count($potential_threats[$file])) {
				$potential_threat = ($potential_threats[$file][$f++]);//str_replace('<', '&lt;', str_replace('>', '&gt;', str_replace("\r\n", "\n", 
				if (($fpos = strpos(($file_contents), $potential_threat, $flen + $fpos)) !== false) {
					$flen = strlen($potential_threat);
					$fa .= ' <a href="javascript:select_text_range(\'ta_'.$file.'\', '.$fpos.', '.($fpos + $flen).');">'.$f.'</a>';
				} else $fa .= $potential_threat;
			}
			return '<div id="div_'.$file.'" class="shadowed-box rounded-corners sidebar-box" style="display: none;"><a class="rounded-corners" name="link_'.$file.'" style="float: right; padding: 0 4px; margin: 0 0 0 30px; text-decoration: none; color: #CC0000; background-color: #FFCCCC; border: solid #FF0000 1px;" href="#found_top" onclick="showhide(\'div_'.$file.'\');">X</a><h3>'.$file.'</h3><br style="clear: left;"/>Potential threats in file: ('.$fa.' )<br /><textarea id="ta_'.$file.'" width="100%" style="width: 100%;" rows="20">'.htmlentities($file_contents).'</textarea><form method="POST" name="GOTMLS_new_file_Form"><imput type="hidden" name="infected_file" value="'.$file.'"><input type="hidden" willbe="submit" value="Save new file over infected file"></form></div>'.($className == 'potential'?'':$threats_found_li);
		}
	}// else return '<span class="'.$className.'">'.$file.'</span>';
}
function GOTMLS_getfiles($dir) {
	if (is_dir($dir)) {
		if (function_exists('scandir'))
			$files = scandir($dir);
		else {
			if ($handle = opendir($dir)) {
				$files = array();
				while (false !== ($entry = readdir($handle)))
					$files[] = "$entry";
				closedir($handle);
			}
		}
	} else
		$files = false;
	return $files;
}
$skip_files = array('png', 'jpg', 'jpeg', 'gif', 'bmp', 'tif', 'tiff', 'exe', 'zip', 'pdf');
$skip_dirs = array('.', '..');
$skipped_dirs = 0;
$total_dirs = 0;
$total_files = 0;
$skipped_files = 0;
$scanned_files = 0;
$threats_fixed = array();
$file_at_depth = array();
$current_percent = 0;
$li_threats = '';
$encode = '/[\?\-a-z\: \.\=\/A-Z\&\_]/';
function return_threats($showLinks = 0) {
	global $li_threats, $threats_found, $threats_fixed, $GOTMLS_images_path;
	$li_threats = ''; 
	foreach ($threats_fixed as $class => $threats)
		if (count($threats))
			$li_threats .= "<li><a name=\"fixed_top_$class-$showLinks\"".(($showLinks > 0)?" href=\"#fixed_top_$class-2\" onclick=\"showhide(\'fixed_threats_$class-2\')\"":'').' class="GOTMLS_plugin">Fixed '.count($threats)." $class threats</a>".(($showLinks == 2)?"<ul id=\"fixed_threats_$class\">".implode('', $threats).'</ul>Because some threats were automatically fixed we need to check to make sure the removal did not break your site. If the frame below looks broken please <a target="test_frame" href="'.$GOTMLS_images_path.'index.php?scan_what='.$_POST['scan_what'].'&scan_depth='.$_POST['scan_depth'].'">revert the changes</a> made durring the automated repair process<br /><iframe id="test_frame" name="test_frame" src="'.$GOTMLS_images_path.'index.php?check_site=1" style="width: 100%; height: 100px"></iframe>':'').'</li>';
	foreach ($threats_found as $class => $threats)
		if (count($threats) > 0 && ($showLinks < 2 || $class == 'potential'))
			$li_threats .= "<li><a name=\"found_top_$class-$showLinks\"".(($showLinks > 0)?" href=\"#found_top_$class-2\" onclick=\"showhide(\'found_threats_$class-2\')\"":'')." class=\"GOTMLS_plugin".(count($threats)>0?" $class\">Found ".count($threats):'">Found 0')." $class threats</a>".(($showLinks == 2)?"<ul id=\"found_threats_$class\">".implode('', $threats).'</ul>':'').'</li>';
	return $li_threats;
}
$current_files = array();
function GOTMLS_scandir($dir, $current_depth = 0) {
	global $bad_backups, $GOTMLS_ERRORS, $potential_threats, $threats_found, $GOTMLS_images_path, $skip_dirs, $skip_files, $total_files, $skipped_files, $scanned_files, $skipped_dirs, $total_dirs, $threats_fixed, $GOTMLS_FIRST_scandir_start, $file_at_depth, $current_percent, $current_files;
	$dirs = explode('/', '/.'.$dir);
	set_time_limit(30);
	if ((!in_array($dirs[count($dirs)-1], $skip_dirs)) && is_dir($dir)) {
		if (($files = GOTMLS_getfiles($dir)) !== false) {
			$file_at_depth[$current_depth] = count($files);
			$current_files[$current_depth] = 0;
			$total_files += $file_at_depth[$current_depth];
			echo "\n<script>update_status(' ...".str_replace("'", "\\'", $dir)."</div><div style=\"width: 100%;\">".$GOTMLS_ERRORS."<ul style=\"float: right; text-align: left;\">".return_threats()."</ul>', ".$current_percent.", ".floor(microtime(true)-$GOTMLS_FIRST_scandir_start).", ".($total_dirs-$skipped_dirs).", $scanned_files, $skipped_files, $bad_backups);</script>\n";//<li>$dir<ul>";
			foreach ($files as $file) {
				$path = str_replace('//', '/', $dir.'/'.$file);
				if ($current_depth == 0)
					$current_percent = floor(($current_files[$current_depth]++ / $file_at_depth[0]) * 100);
				if (is_dir($path)) {
					$total_dirs++;
					if (isset($_POST['scan_depth']) && is_numeric($_POST['scan_depth']) && ($_POST['scan_depth'] != $current_depth) && !in_array($file, $skip_dirs)) {
						$current_depth++;
						$current_depth = GOTMLS_scandir($path, $current_depth);
					} else
						$skipped_dirs++;
				} else {
					if (GOTMLS_get_ext($path) == 'bad') {
						$bad_backups++;
					} else if (!in_array(GOTMLS_get_ext($path), $skip_files) && filesize($path)<1234567) {
						echo GOTMLS_scanfile($path);
					} else
						$skipped_files++;
				}
			}
//			echo "\n</ul></li>";
		}
	}	/**/	
	set_time_limit(30);
	$current_depth--;
	return $current_depth;
}
function GOTMLS_display_header($pTitle, $optional_box = '') {
	global $GOTMLS_plugin_dir, $GOTMLS_url_parts, $GOTMLS_update_home, $GOTMLS_plugin_home, $GOTMLS_updated_images_path, $GOTMLS_Version, $GOTMLS_images_path, $GOTMLS_definitions_version, $current_user;
	get_currentuserinfo();
$_SESSION['eli_debug_microtime']['GOTMLS_display_header_start'] = microtime(true);
	$wait_img_URL = $GOTMLS_images_path.'wait.gif';
	if (isset($_GET['check_site']) && $_GET['check_site'] == 1)
		echo '<div id="check_site" style="float: right; margin: 15px;"><img src="'.$GOTMLS_images_path.'checked.gif"> Tested your site. It appears we didn\'t break anything ;-)</div><style>#footer, #GOTMLS-Settings, #right-sidebar, #admin-page-container, #wpadminbar, #adminmenuback, #adminmenuwrap, #adminmenu {display: none !important;} #wpbody-content {padding-bottom: 0;}';
	else
		echo '<style>#right-sidebar {float: right; margin-right: 10px; width: 290px;}';
	echo '
.rounded-corners {margin: 10px; padding: 10px; -webkit-border-radius: 10px; -moz-border-radius: 10px; border: 1px solid #000000;}
.shadowed-box {box-shadow: -3px 3px 3px #666666; -moz-box-shadow: -3px 3px 3px #666666; -webkit-box-shadow: -3px 3px 3px #666666;}
.sidebar-box {background-color: #CCCCCC;}
.sidebar-links {padding: 0 15px; list-style: none;}
.popup-box {background-color: #FFFFCC; display: none; position: absolute; left: 0px; z-index: 10;}
.shadowed-text {text-shadow: #0000FF -1px 1px 1px;}
.sub-option {float: left; margin: 3px 5px;}
.pp_left {height: 28px; float: left; background-position: top center;}
.pp_right {height: 18px; float: right; background-position: bottom center;}
.pp_donate {margin: 3px 5px; background-repeat: no-repeat; background-image: url(\'https://www.paypalobjects.com/en_US/i/btn/btn_donateCC_LG.gif\');}
.pp_left input {width: 100px; height: 28px;}
.pp_right input {width: 130px; height: 18px;}
.inside p {margin: 10px;}
#main-section {margin-right: 310px;}
</style>
<script>
function showhide(id) {
	divx = document.getElementById(id);
	if (divx.style.display == "none")
		divx.style.display = "";
	else
		divx.style.display = "none";
}
</script>
<h1>'.$pTitle.'</h1>
<div id="right-sidebar" class="metabox-holder">
	<div id="pluginupdates" class="shadowed-box stuffbox"><h3 class="hndle"><span>Plugin Updates</span></h3>
		<div id="findUpdates" class="inside"><center>Searching for updates ...<br /><img src="'.$wait_img_URL.'" alt="Wait..." /><br /><input type="button" value="Cancel" onclick="document.getElementById(\'findUpdates\').innerHTML = \'Could not find server!\';" /></center></div>
		<script type="text/javascript" src="'.$GOTMLS_plugin_home.$GOTMLS_updated_images_path.'?js='.$GOTMLS_Version.'&p='.$GOTMLS_plugin_dir.'&ts='.date("YmdHis").'"></script>
	</div>
	<div id="definitionupdates" class="stuffbox shadowed-box"><h3 class="hndle"><span>Definition Updates</span></h3>
		<script>
		function check_for_updates(chk) {
			if (auto_img = document.getElementById("autoUpdateDownload")) {
				auto_img.style.display="";
				//auto_img.src="'.$GOTMLS_images_path.'index.php?ver='.$GOTMLS_definitions_version.'&key='.md5($GOTMLS_url_parts[2]).'";
			}
		}
		function sinupFormValidate(form) {
			var error = "";
			if(form["ws_plugin__s2member_custom_reg_field_first_name"].value == "")	
				error += "First Name is a required field!\n";
			if(form["ws_plugin__s2member_custom_reg_field_last_name"].value == "")		
				error += "Last Name is a required field!\n";
			if(form["user_email"].value == "")
				error += "Email Address is a required field!\n";
			else {
				if (uem = document.getElementById("register_user_login"))
					uem.value = form["user_email"].value;
				if (uem = document.getElementById("register_redirect_to"))
					uem.value = "/donate/?email="+form["user_email"].value.replace("@", "%40");
			}
			if(form["ws_plugin__s2member_custom_reg_field_user_url"].value == "")
				error += "Your WordPress Site URL is a required field!\n";
			if(form["ws_plugin__s2member_custom_reg_field_installation_key"].value == "")
				error += "Plugin Installation Key is a required field!\n";
			if(error != "") {
				alert(error);
				return false;
			} else
				return true;
		}
		</script>
	<form id="updateform" method="post" name="updateform">
		<div id="Definition_Updates" class="inside"><center>Searching for updates ...<br /><img src="'.$wait_img_URL.'" alt="Wait..." /><br /><input type="button" value="Cancel" onclick="document.getElementById(\'Definition_Updates\').innerHTML = \'Could not find server!\';" /></center></div>
		<div id="autoUpdateForm" style="display: none;" class="inside">
		<input type="submit" name="auto_update" onclick="check_for_updates(this);" value="Download new definitions!"> <img style="display: none;" src="'.$GOTMLS_images_path.'wait.gif" alt="Downloading new definitions file..." id="autoUpdateDownload">
		</div>
	</form>
		<div id="registerKeyForm" style="display: none;" class="inside">
Register your Key now and get instant access to new definition files as new threats are discovered.
<p>*All fields are required and I will NOT share your registration information with anyone.</p>
<form id="registerform" onsubmit="return sinupFormValidate(this);" action="http://gotmls.net/wp-login.php?action=register" method="post" name="registerform"><input type="hidden" name="redirect_to" id="register_redirect_to" value="/donate/"><input type="hidden" name="user_login" id="register_user_login" value="">
<div>Your Full Name:</div>
<div style="float: left; width: 50%;"><input style="width: 100%;" id="ws_plugin__s2member_custom_reg_field_first_name" type="text" name="ws_plugin__s2member_custom_reg_field_first_name" value="'.$current_user->user_firstname.'" /></div>
<div style="float: left; width: 50%;"><input style="width: 100%;" id="ws_plugin__s2member_custom_reg_field_last_name" type="text" name="ws_plugin__s2member_custom_reg_field_last_name" value="'.$current_user->user_lastname.'" /></div>
<div style="clear: left; width: 100%;">
<div>A password will be e-mailed to this address:</div>
<input style="width: 100%;" id="user_email" type="text" name="user_email" value="'.$current_user->user_email.'" /></div>
<div>
<div>Your WordPress Site URL:</div>
<input style="width: 100%;" id="ws_plugin__s2member_custom_reg_field_user_url" type="text" name="ws_plugin__s2member_custom_reg_field_user_url" value="'.get_option('siteurl').'" /></div>
<div>
<div>Plugin Installation Key:</div>
<input style="width: 100%;" id="ws_plugin__s2member_custom_reg_field_installation_key" type="text" name="ws_plugin__s2member_custom_reg_field_installation_key" value="'.md5($GOTMLS_url_parts[2]).'" /></div>
<input style="width: 100%;" id="wp-submit" type="submit" name="wp-submit" value="Register Now!" /></form></div>
		<script type="text/javascript" src="'.$GOTMLS_update_home.$GOTMLS_updated_images_path.'?div=Definition_Updates&v='.$GOTMLS_Version.'&ver='.$GOTMLS_definitions_version.'&key='.md5($GOTMLS_url_parts[2]).'&p='.$GOTMLS_plugin_dir.'"></script>
	</div>
	<div id="pluginlinks" class="shadowed-box stuffbox"><h3 class="hndle"><span>Plugin Links</span></h3>
		<div class="inside">
		<form action="https://www.paypal.com/cgi-bin/webscr" method="post">
			<table cellpadding=0 cellspacing=0><tr><td>
				<input type="hidden" name="cmd" value="_s-xclick">
				<input type="hidden" name="hosted_button_id" value="QZHD8QHZ2E7PE">
				<div class="pp_donate pp_left"><input type="image" src="https://www.paypalobjects.com/en_US/i/scr/pixel.gif" border="0" name="submit" alt="Make a Donation with PayPal"></div>
				<div class="pp_donate pp_right"><input type="image" src="https://www.paypalobjects.com/en_US/i/scr/pixel.gif" border="0" name="submitc" alt="Make a Donation with your credit card at PayPal"></div>
			</td></tr><tr><td>
				<ul class="sidebar-links">
					<li>on <a target="_blank" href="http://wordpress.org/extend/plugins/profile/scheeeli">WordPress.org</a><ul class="sidebar-links">
						<li><a target="_blank" href="http://wordpress.org/extend/plugins/'.strtolower($GOTMLS_plugin_dir).'/faq/">Plugin FAQs</a>
						<li><a target="_blank" href="http://wordpress.org/extend/plugins/'.strtolower($GOTMLS_plugin_dir).'/stats/">Download Stats</a>
						<li><a target="_blank" href="http://wordpress.org/tags/'.strtolower($GOTMLS_plugin_dir).'">Forum Posts</a>
					</ul></li>
					<li>on <a target="_blank" href="'.$GOTMLS_plugin_home.'category/my-plugins/">Eli\'s Blog</a><ul class="sidebar-links">
						<li><a target="_blank" href="'.$GOTMLS_plugin_home.'category/my-plugins/anti-malware/">Anti-Malware</a>
					</ul></li>
					<li>on <a target="_blank" href="'.$GOTMLS_update_home.'">GOTMLS.NET</a><ul class="sidebar-links">
						<li><a target="_blank" href="'.$GOTMLS_update_home.'blog/">Blog</a>
					</ul></li>
				</ul>
			</td></tr></table>
		</form>
		</div>
	</div>
	'.$optional_box.'
</div>
<div id="admin-page-container">
	<div id="main-section">';
$_SESSION['eli_debug_microtime']['GOTMLS_display_header_end'] = microtime(true);
}
function GOTMLS_trim_ar(&$ar_item, $key) {
	$ar_item = trim($ar_item);
}
if (!function_exists('ur1encode')) { function ur1encode($url) {
	global $encode;
	return preg_replace($encode, '\'%\'.substr(\'00\'.strtoupper(dechex(ord(\'\0\'))),-2);', $url);
}}
$GOTMLS_FIRST_scandir_start = 0;
function GOTMLS_settings() {
	global $threats_found, $bad_backups, $GOTMLS_plugin_dir, $GOTMLS_images_path, $total_files, $skipped_files, $scanned_files, $skipped_dirs, $total_dirs, $potential_threats, $GOTMLS_ERRORS, $skip_files, $threats_fixed, $GOTMLS_FIRST_scandir_start, $GOTMLS_known_treats;
$_SESSION['eli_debug_microtime']['GOTMLS_Settings_start'] = microtime(true);
	$noYesList = array('No', 'Yes');
	$GOTMLS_menu_groups = array('Main Menu Item placed below <b>Comments</b> and above <b>Appearance</b>','Main Menu Item placed below <b>Settings</b>','Sub-Menu inside the <b>Tools</b> Menu Item');
	$GOTMLS_scan_groups = array();
	$dirs = explode('/', __file__);
	$GOTMLS_settings_array = get_option($GOTMLS_plugin_dir.'_settings_array');
	$scan_level = intval($GOTMLS_settings_array['scan_level']);
	for ($SL=0;$SL<$scan_level;$SL++)
		$GOTMLS_scan_groups[] = '<b>'.implode('/', array_slice($dirs, -1 * (3 + $SL), 1)).'</b>';
	if (!isset($GOTMLS_settings_array['scan_what']))
		$GOTMLS_settings_array['scan_what'] = 0;
	if (!isset($GOTMLS_settings_array['scan_depth']))
		$GOTMLS_settings_array['scan_depth'] = -1;
	if (!isset($GOTMLS_settings_array['check_htaccess']))
		$GOTMLS_settings_array['check_htaccess'] = 1;
	if (!isset($GOTMLS_settings_array['check_timthumb']))
		$GOTMLS_settings_array['check_timthumb'] = 1;
	if (!isset($GOTMLS_settings_array['check_known']))
		$GOTMLS_settings_array['check_known'] = 1;
	if (!isset($GOTMLS_settings_array['check_potential']))
		$GOTMLS_settings_array['check_potential'] = 1;
	if (!isset($GOTMLS_settings_array['exclude_ext']))
		$GOTMLS_settings_array['exclude_ext'] = $skip_files;
	if (isset($_POST['exclude_ext']) && strlen(trim($_POST['exclude_ext'].' ')) >0) {
		$GOTMLS_settings_array['exclude_ext'] = preg_split("/[,]+/", trim($_POST['exclude_ext']), -1, PREG_SPLIT_NO_EMPTY);
		array_walk($GOTMLS_settings_array['exclude_ext'], 'GOTMLS_trim_ar');
	}
	$skip_files = array_merge($GOTMLS_settings_array['exclude_ext'], array('bad'));
	if (isset($_POST['scan_what']) && is_numeric($_POST['scan_what']) && $_POST['scan_what'] != $GOTMLS_settings_array['scan_what'])
		$GOTMLS_settings_array['scan_what'] = $_POST['scan_what'];
//	else echo '<li>POST != $GOTMLS_settings_array('.$GOTMLS_settings_array['scan_what'].')='.($_POST['scan_what'] != $GOTMLS_settings_array['scan_what']);
	if (isset($_POST['scan_depth']) && is_numeric($_POST['scan_depth']) && $_POST['scan_depth'] != $GOTMLS_settings_array['scan_depth'])
		$GOTMLS_settings_array['scan_depth'] = $_POST['scan_depth'];
	if (isset($_POST['check_htaccess']) && is_numeric($_POST['check_htaccess']) && $_POST['check_htaccess'] != $GOTMLS_settings_array['check_htaccess'])
		$GOTMLS_settings_array['check_htaccess'] = $_POST['check_htaccess'];
	if (isset($_POST['check_timthumb']) && is_numeric($_POST['check_timthumb']) && $_POST['check_timthumb'] != $GOTMLS_settings_array['check_timthumb'])
		$GOTMLS_settings_array['check_timthumb'] = $_POST['check_timthumb'];
	if (isset($_POST['check_known']) && is_numeric($_POST['check_known']) && $_POST['check_known'] != $GOTMLS_settings_array['check_known'])
		$GOTMLS_settings_array['check_known'] = $_POST['check_known'];
	if (isset($_POST['check_potential']) && is_numeric($_POST['check_potential']) && $_POST['check_potential'] != $GOTMLS_settings_array['check_potential'])
		$GOTMLS_settings_array['check_potential'] = $_POST['check_potential'];
	$scan_opts = '<b>What to scan:</b>';
	foreach ($GOTMLS_scan_groups as $mg => $GOTMLS_scan_group)
		$scan_opts .= '<div style="float: left; padding: 4px 14px;" id="scan_group_div_'.$mg.'"><input type="radio" name="scan_what" value="'.$mg.'"'.($GOTMLS_settings_array['scan_what']==$mg?' checked':'').' />'.$GOTMLS_scan_group.'</div>';
	$scan_opts .= '<br style="clear: left;" /><p><b>Scan Depth:</b> (how far do you want to drill down from your starting directory)<br /><input type="text" value="'.$GOTMLS_settings_array['scan_depth'].'" name="scan_depth"> (-1 is infinite depth)</p><p><h3>What to look for:</h3><br /><div style="float: left; padding: 0; width: 100%;" id="check_timthumb_div">Check for timthumb.php files older than 2.0 (a common vulnerability used to plant malicious code):';
	if (isset($GOTMLS_known_treats['timthumb']) && is_array($GOTMLS_known_treats['timthumb'])) {
		foreach ($noYesList as $nY => $noYes)
			$scan_opts .= '<div style="float: right; padding: 14px;" id="check_timthumb_div_'.$nY.'"><input type="radio" name="check_timthumb" value="'.$nY.'"'.($GOTMLS_settings_array['check_timthumb']==$nY?' checked':'').' />'.$noYes.'</div>';
	} else
		$scan_opts .= '<div style="float: right; padding: 14px;" id="check_timthumb_div_NA">Registration of your Installation Key is required for this feature</div>';
	$scan_opts .= '</div><hr style="clear: left; color: #cccccc; background-color: #cccccc;" /><div style="float: left; padding: 0; width: 100%;" id="check_htaccess_div">Check .htaccess files for over indented lines (a common way to try and hide malicious code):';
	if (isset($GOTMLS_known_treats['htaccess']) && is_array($GOTMLS_known_treats['htaccess'])) {
		foreach ($noYesList as $nY => $noYes)
			$scan_opts .= '<div style="float: right; padding: 14px;" id="check_htaccess_div_'.$nY.'"><input type="radio" name="check_htaccess" value="'.$nY.'"'.($GOTMLS_settings_array['check_htaccess']==$nY?' checked':'').' />'.$noYes.'</div>';
	} else
		$scan_opts .= '<div style="float: right; padding: 14px;" id="check_htaccess_div_NA">Registration of your Installation Key is required for this feature</div>';
	$scan_opts .= '</div><hr style="clear: left; color: #cccccc; background-color: #cccccc;" /><div style="float: left; padding: 0; width: 100%;" id="check_known_div">Check for known threats (malicious scripts that use eval&#40;&#41; and similar techniques to infect your server):';
	if (isset($GOTMLS_known_treats['known']) && is_array($GOTMLS_known_treats['known'])) {
		foreach ($noYesList as $nY => $noYes)
			$scan_opts .= '<div style="float: right; padding: 14px;" id="check_known_div_'.$nY.'"><input type="radio" name="check_known" value="'.$nY.'"'.($GOTMLS_settings_array['check_known']==$nY?' checked':'').' />'.$noYes.'</div>';
	} else
		$scan_opts .= '<div style="float: right; padding: 14px;" id="check_known_div_NA">Registration of your Installation Key is required for this feature</div>';
	$scan_opts .= '</div><br style="clear: left;" /><hr style="clear: left; color: #cccccc; background-color: #cccccc;" /><div style="float: left; padding: 0; width: 100%;" id="check_potential_div">Check for potential threats (usage of eval&#40;&#41; is not always a threat but it could be. This option helps you examine at each one to see if it is dangerous or malicious):';
	foreach ($noYesList as $nY => $noYes)
		$scan_opts .= '<div style="float: right; padding: 14px;" id="check_potential_div_'.$nY.'"><input type="radio" name="check_potential" value="'.$nY.'"'.($GOTMLS_settings_array['check_potential']==$nY?' checked':'').' />'.$noYes.'</div>';
	$scan_opts .= '</div><br style="clear: left;" /><h3>Skip files with the following extentions:</h3><p>(a comma separated list of file extentions to be excluded from the scan)<br /><input type="text" name="exclude_ext" value="'.implode($GOTMLS_settings_array['exclude_ext'], ',').'" style="width: 90%;" /></p>';
	$menu_opts = '<div class="stuffbox shadowed-box">
		<h3 class="hndle"><span>Menu Item Placement Options</span></h3>
		<div class="inside"><form method="POST" name="GOTMLS_menu_Form">';
	foreach ($GOTMLS_menu_groups as $mg => $GOTMLS_menu_group)
		$menu_opts .= '<div style="padding: 4px;" id="menu_group_div_'.$mg.'"><input type="radio" name="GOTMLS_menu_group" value="'.$mg.'"'.($GOTMLS_settings_array['menu_group']==$mg?' checked':'').' onchange="document.GOTMLS_menu_Form.submit();" />'.$GOTMLS_menu_group.'</div>';
	GOTMLS_display_header('Malware Scan', $menu_opts.'</form><br style="clear: left;" /></div></div>');
//echo("<textarea>".print_r($GOTMLS_known_treats, true)."</textarea>");
	echo '<script>
function update_status(title, percent, time, total_dirs, scanned_files, skipped_files, bad_backups) {
	divx = document.getElementById("status_bar");
	if (percent == 100)
		scan_state = \'\';
	else
		scan_state = \'<img src="'.$GOTMLS_images_path.'wait.gif" style="float: left;">\';
	divx.innerHTML = \'<div style="height: 18px; width: 100%; border: solid #000000 1px; position: relative;"><div style="height: 18px; position: absolute; top: 0px; left: 0px; background-color: #6666FF; width: \'+percent+\'%"></div><div style="height: 18px; position: absolute; top: 0px; left: 0px; z-order: 5;">\'+time+\' Seconds Elapsed</div><div style="height: 18px; position: absolute; top: 0px; left: 0px; width: 100%; z-order: 5;">\'+percent+\'%</div><div style="height: 18px; position: absolute; top: 0px; right: 0px; z-order: 5;">\'+Math.ceil(time*(100/percent)-time)+\' Seconds Remaining</div></div><br /><div style="overflow-x: hidden;">\'+scan_state+\'Scanning\'+title+\'<ul style="text-align: left;"><li>Scanned \'+total_dirs+\' directories</li><li>Examined \'+scanned_files+\' files</li><li>Skipped \'+skipped_files+\' files</li></div>\';
}
var i, intrvl;
function start_substatus() {
	i = 0;
	intrvl = setInterval("update_substatus(\'Percentage Complete\', i++, 100)", 200);
}
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
</script>
<style>
	.GOTMLS_plugin {margin: 5px; background: #ccffcc; border: 1px solid #00ff00; padding: 0 5px; border-radius: 3px;}
	.GOTMLS_plugin.known, .GOTMLS_plugin.htaccess, .GOTMLS_plugin.timthumb {background: #ff9999; border: 1px solid #ff0000;}
	.GOTMLS_plugin.potential {background: #ffffcc; border: 1px solid #ffcc66;}
	.GOTMLS ul li {list-style: square; margin-left: 20px;}
	.GOTMLS h2 {margin: 0 0 10px;}
</style>
<div id="GOTMLS-Settings" class="wrap meta metabox-holder GOTMLS">';
	if (isset($_POST['scan_what']) && is_numeric($_POST['scan_what'])) {
		echo '<div class="stuffbox shadowed-box"><h3 class="hndle"><span>Scan Results</span></h3><div class="inside"><form method="POST" name="GOTMLS_Form_clean">';
		foreach ($_POST as $name => $value)
			if (substr($name, 0, 4) != 'fix_')
				echo '<input type="hidden" name="'.$name.'" value="'.$value.'">';
		echo '<p align="center">
		<center><div class="rounded-corners" align="center" style="margin: 10px; vertical-align: middle; background-color: #cccccc; z-order: 3; border: 2px double #000000;"><b><a name="found_top">Scan Status</a></b><br><div id="status_bar"></div></div></center></p><p id="fix_button" style="display: none; text-align: right;"><input type="submit" value="Automatically Repair SELECTED files Now" class="button-primary" /></p>
		<h3><span>Scan Details:</span></h3><div id="scan_details" class="inside">';
		if ($_POST['scan_what'] > -1) {
			$dir = implode('/', array_slice($dirs, 0, -1 * (2 + $_POST['scan_what'])));
			echo '<ul name="found_top_known-2">';
			$GOTMLS_FIRST_scandir_start = microtime(true);
			if (is_dir($dir))
				GOTMLS_scandir($dir);
			else
				echo "<li>Failed: $dir is not a directory!</li>";
			ksort($threats_found);
			echo '</ul>';
			$show_fix_b = 'showhide("fix_button");';
			$known_threat_count = 0;
			foreach ($threats_found as $threats_type => $threats_array)
				if ($threats_type != 'potential')
					$known_threat_count += count($threats_array);
			if ($known_threat_count > 0)
				echo '<p style="text-align: right;"><input type="submit" value="Automatically Repair SELECTED files Now" class="button-primary" /></p>';
			else
				$show_fix_b = '';
			echo '<ul>'.return_threats(2)."</ul>\n<script>update_status(' Completed!</div><div style=\"width: 100%;\">".$GOTMLS_ERRORS."<ul style=\"float: right; text-align: left;\">".return_threats(1)."</ul>', 100, ".floor(microtime(true)-$GOTMLS_FIRST_scandir_start).", ".($total_dirs-$skipped_dirs).", $scanned_files, $skipped_files, $bad_backups);".$show_fix_b."</script>";
		}
		update_option($GOTMLS_plugin_dir.'_settings_array', $GOTMLS_settings_array);
		echo '</div></form></div></div>';
	}// else  phpinfo();
	echo '<div class="stuffbox shadowed-box">
			<h3 class="hndle"><span>Scan Settings</span></h3>
			<div class="inside">
				<form method="POST" name="GOTMLS_Form"><p>'.$scan_opts.'</p>
				<p style="text-align: right;"><input type="submit" value="Scan Now" class="button-primary" /></p></form></div></div>';
//	print_r($GOTMLS_known_treats);
	echo '</div></div></div><script>setDivNAtext();</script>';
$_SESSION['eli_debug_microtime']['GOTMLS_Settings_end'] = microtime(true);
}
function GOTMLS_set_plugin_action_links($links_array, $plugin_file) {
	if ($plugin_file == substr(__file__, (-1 * strlen($plugin_file)))) {
		$_SESSION['eli_debug_microtime']['GOTMLS_set_plugin_action_links'] = microtime(true);
		$links_array = array_merge(array('<a href="admin.php?page=GOTMLS-settings">'.__( 'Settings' ).'</a>'), $links_array);
	}
	return $links_array;
}
function GOTMLS_set_plugin_row_meta($links_array, $plugin_file) {
	if ($plugin_file == substr(__file__, (-1 * strlen($plugin_file)))) {
		$_SESSION['eli_debug_microtime']['GOTMLS_set_plugin_row_meta'] = microtime(true);
		$links_array = array_merge($links_array, array('<a target="_blank" href="http://wordpress.org/extend/plugins/gotmls/faq/">'.__( 'FAQ' ).'</a>','<a target="_blank" href="http://wordpress.org/tags/gotmls">'.__( 'Support' ).'</a>','<a target="_blank" href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=QZHD8QHZ2E7PE">'.__( 'Donate' ).'</a>'));
	}
	return $links_array;
}
function GOTMLS_stripslashes(&$item, $key) {
	$item = stripslashes($item);
}
$GOTMLS_auto_update = 0;
function GOTMLS_init() {
	global $GOTMLS_definitions_version, $GOTMLS_known_treats, $GOTMLS_auto_update, $GOTMLS_plugin_dir, $GOTMLS_local_images_path;
$_SESSION['eli_debug_microtime']['GOTMLS_init_start'] = microtime(true);
	$GOTMLS_settings_array = get_option($GOTMLS_plugin_dir.'_settings_array');
	if (isset($GOTMLS_settings_array['scan_level']) && is_numeric($GOTMLS_settings_array['scan_level']))
		$scan_level = intval($GOTMLS_settings_array['scan_level']);
	else
		$scan_level = 3;
	if (file_exists($GOTMLS_local_images_path.'definitions.php'))
		include($GOTMLS_local_images_path.'definitions.php');
	else
		$_SESSION['eli_debug_microtime']['GOTMLS_missing_definitions_file'] = $GOTMLS_local_images_path.'definitions.php';	
	if (isset($GOTMLS_settings_array['definitions_version']) && is_numeric($GOTMLS_settings_array['definitions_version']) && intval($GOTMLS_settings_array['definitions_version']) >= $GOTMLS_definitions_version) {
		$GOTMLS_known_treats = get_option($GOTMLS_plugin_dir.'_known_treats_array');
		$GOTMLS_definitions_version = $GOTMLS_settings_array['definitions_version'];
	} else
		$GOTMLS_known_treats = $known_treats;
	if (isset($_POST['UPDATE_known_treats']) && is_array($_POST['UPDATE_known_treats']) && isset($_POST['definitions_version']) && is_numeric($_POST['definitions_version'])) {
		$GOTMLS_known_treats = ($_POST['UPDATE_known_treats']);
		if (isset($GOTMLS_known_treats['potential']['eval']) && substr($GOTMLS_known_treats['potential']['eval'], -5) == '\\\\)/i')
			array_walk_recursive($GOTMLS_known_treats, 'GOTMLS_stripslashes');//$GOTMLS_known_treats['potential']['eval']=stripslashes($GOTMLS_k		$GOTMLS_definitions_version = intval($_POST['definitions_version']);
	}
	$GOTMLS_settings_array['definitions_version'] = $GOTMLS_definitions_version;
	if (isset($_POST['scan_level']) && is_numeric($_POST['scan_level']))
		$scan_level = intval($_POST['scan_level']);
	if (isset($scan_level) && is_numeric($scan_level))
		$GOTMLS_settings_array['scan_level'] = intval($scan_level);
	else
		$GOTMLS_settings_array['scan_level'] = 3;
	update_option($GOTMLS_plugin_dir.'_known_treats_array', $GOTMLS_known_treats);
	update_option($GOTMLS_plugin_dir.'_settings_array', $GOTMLS_settings_array);
$_SESSION['eli_debug_microtime']['GOTMLS_init_end'] = microtime(true);
}
add_filter('plugin_row_meta', $GOTMLS_plugin_dir.'_set_plugin_row_meta', 1, 2);
add_filter('plugin_action_links', $GOTMLS_plugin_dir.'_set_plugin_action_links', 1, 2);
$encode .= 'e';
$GOTMLS_plugin_home = 'http://wordpress.ieonly.com/';
$GOTMLS_update_home = 'http://gotmls.net/';
$GOTMLS_images_path = plugins_url('/images/', __FILE__);
$GOTMLS_url_parts = explode('/', $GOTMLS_images_path.'/../.');
$GOTMLS_local_images_path = dirname(__FILE__).'/images/';
$GOTMLS_updated_images_path = 'wp-content/plugins/update/images/';
$GOTMLS_Logo_IMG='GOTMLS-16x16.gif';
$GOTMLS_definitions_version = 1203000000;
$GOTMLS_known_treats = array(
	'potential' => array(
		'preg_replace /e' => '/preg_replace[ \t]*\([^\)]+[\/\#\|]e[\'"].+\)/i',
		'eval' => "/[^a-z\/'\"]eval\(.+\)/i"));
register_activation_hook(__FILE__,$GOTMLS_plugin_dir.'_install');
add_action('admin_menu', $GOTMLS_plugin_dir.'_menu');
$init = add_action('admin_init', $GOTMLS_plugin_dir.'_init');
$_SESSION['eli_debug_microtime']['end_include(GOTMLS)'] = microtime(true);
?>
