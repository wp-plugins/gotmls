<?php
/*
Plugin Name: Anti-Malware by ELI (Get Off Malicious Scripts)
Plugin URI: http://gotmls.net/
Author: Eli Scheetz
Author URI: http://wordpress.ieonly.com/category/my-plugins/anti-malware/
Contributors: scheeeli
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=QZHD8QHZ2E7PE
Description: This Anti-Virus/Anti-Malware plugin searches for Malware and other Virus like threats and vulnerabilities on your server and helps you remove them. It's always growing and changing to adapt to new threats so let me know if it's not working for you.
Version: 1.2.08.31
*/
$GOTMLS_Version='1.2.08.31';
$_SESSION['eli_debug_microtime']['GOTMLS'] = array();
$_SESSION['eli_debug_microtime']['GOTMLS']['START_microtime'] = microtime(true);
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
$_SESSION['eli_debug_microtime']['GOTMLS'][(microtime(true)-$_SESSION['eli_debug_microtime']['GOTMLS']['START_microtime']).' GOTMLS_install_start'] = GOTMLS_memory_usage(true);
	if (version_compare($wp_version, "2.6", "<"))
		die(__("This Plugin requires WordPress version 2.6 or higher"));
$_SESSION['eli_debug_microtime']['GOTMLS'][(microtime(true)-$_SESSION['eli_debug_microtime']['GOTMLS']['START_microtime']).' GOTMLS_install_end'] = GOTMLS_memory_usage(true);
}
function GOTMLS_menu() {
	global $GOTMLS_plugin_dir, $GOTMLS_Logo_IMG, $GOTMLS_images_path;
$_SESSION['eli_debug_microtime']['GOTMLS'][(microtime(true)-$_SESSION['eli_debug_microtime']['GOTMLS']['START_microtime']).' GOTMLS_menu_start'] = GOTMLS_memory_usage(true);
	$GOTMLS_settings_array = get_option($GOTMLS_plugin_dir.'_settings_array');
	if (isset($_POST['GOTMLS_menu_group']) && is_numeric($_POST['GOTMLS_menu_group']) && $_POST['GOTMLS_menu_group'] != $GOTMLS_settings_array['menu_group']) {
		$GOTMLS_settings_array['menu_group'] = $_POST['GOTMLS_menu_group'];
		update_option($GOTMLS_plugin_dir.'_settings_array', $GOTMLS_settings_array);
	}
	$Full_plugin_logo_URL = $GOTMLS_images_path.$GOTMLS_Logo_IMG;
	$base_page = $GOTMLS_plugin_dir.'-settings';
	if ($GOTMLS_settings_array['menu_group'] == 2)
		add_submenu_page('tools.php', __('Anti-Malware Settings/Scan Page'), __('<span style="background: url(\''.$Full_plugin_logo_URL.'\') no-repeat; vertical-align: middle; border: 0 none; display: inline-block; height: 16px; width: 16px;"></span> Anti-Malware'), 'administrator', $GOTMLS_plugin_dir.'-settings', $GOTMLS_plugin_dir.'_settings');
	elseif (!function_exists('add_object_page') || $GOTMLS_settings_array['menu_group'] == 1)
		add_menu_page(__('Anti-Malware Settings/Scan'), __('Anti-Malware'), 'administrator', $base_page, $GOTMLS_plugin_dir.'_settings', $Full_plugin_logo_URL);
	else
		add_object_page(__('Anti-Malware Settings/Scan'), __('Anti-Malware'), 'administrator', $base_page, $GOTMLS_plugin_dir.'_settings', $Full_plugin_logo_URL);
$_SESSION['eli_debug_microtime']['GOTMLS'][(microtime(true)-$_SESSION['eli_debug_microtime']['GOTMLS']['START_microtime']).' GOTMLS_menu_end'] = GOTMLS_memory_usage(true);
}
function GOTMLS_debug($my_error = '', $echo_error = false) {
	global $GOTMLS_plugin_dir, $GOTMLS_Version, $wp_version;
	$mtime=date("Y-m-d H:i:s", filemtime(__file__));
	if ($echo_error || (substr($my_error, 0, 22) == 'Access denied for user'))
		echo "<li>debug:<pre>$my_error\n".print_r($_SESSION['eli_debug_microtime']['GOTMLS'],true).'END;</pre>';
	$_SESSION['eli_debug_microtime']['GOTMLS']=array();
	return $my_error;
}
function GOTMLS_get_ext($filename) {
	$nameparts = explode('.', '.'.$filename);
	return strtolower($nameparts[(count($nameparts)-1)]);
}
function GOTMLS_check_threat($threat_level) {
	global $GOTMLS_known_threats, $new_contents, $GOTMLS_ERRORS, $current_file, $GOTMLS_images_path;
	$found = false;
	if (isset($GOTMLS_known_threats[$threat_level]) && is_array($GOTMLS_known_threats[$threat_level])) {
		foreach ($GOTMLS_known_threats[$threat_level] as $threat_name => $threat_match) {
			if (preg_match_all($threat_match, $new_contents, $matches)) {
				if ($threat_level == 'timthumb') {
					$new_contents = @file_get_contents(dirname(__FILE__).'/images/tt2.php');
				} else {
					foreach ($matches[0] as $find)
						$new_contents = str_replace($find, '', $new_contents);
					$new_contents = preg_replace('/[\r\n]+/', "\n", preg_replace('/\<\?php[ \t\n\r]*\?\>/i', '', $new_contents));
				}
				$found = $matches[0];
			}// else $GOTMLS_ERRORS .= '<li class="GOTMLS_plugin '.$threat_level.'">NO "'.$threat_match.'" in '.$current_file.'('.strlen($new_contents).')</li>';
		}
	} else {
		if (preg_match_all('/'.$threat_level.'/i', $new_contents, $matches)) {
			foreach ($matches[0] as $find)
				$new_contents = str_replace($find, '', $new_contents);
			$found = $matches[0];
		}// else $GOTMLS_ERRORS .= '<li class="GOTMLS_plugin '.$threat_level.'">NO "'.$threat_match.'" in '.$current_file.'('.strlen	}
	}
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
	if (file_exists($file)) {
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
			} else if (isset($_POST['custom_reg_exp']) && strlen(trim($_POST['custom_reg_exp'])) > 0 && ($found = GOTMLS_check_threat($_POST['custom_reg_exp'])) !== false) {
				$potential_threats[$file] = $found;
				$className = 'custom_reg_exp';
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
 			elseif ($className == 'custom_reg_exp')
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
			return '<div id="div_'.$file.'" class="shadowed-box rounded-corners sidebar-box" style="display: none;"><a class="rounded-corners" name="link_'.$file.'" style="float: right; padding: 0 4px; margin: 0 0 0 30px; text-decoration: none; color: #CC0000; background-color: #FFCCCC; border: solid #FF0000 1px;" href="#found_top" onclick="showhide(\'div_'.$file.'\');">X</a><h3>'.$file.'</h3><br style="clear: left;"/>Potential threats in file: ('.$fa.' )<br /><textarea id="ta_'.$file.'" width="100%" style="width: 100%;" rows="20">'.htmlentities($file_contents).'</textarea><form method="POST" name="GOTMLS_new_file_Form"><imput type="hidden" name="infected_file" value="'.$file.'"><input type="hidden" willbe="submit" value="Save new file over infected file"></form></div>'.($className == 'potential'?'':'<script>showhide("fix_button", true);</script>'.$threats_found_li);
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
function GOTMLS_explode_dir($dir, $pre = '') {
	$path = explode(':', $dir.':');
	if (count($path) == 2)
		return explode('/', (strlen($pre)?'/':'').$pre.$path[0]);
	else
		return explode('\\', (strlen($pre)?'\\':'').$pre.$path[1]);
}
function GOTMLS_memory_usage($t = true) {
	if (function_exists('memory_get_usage'))
		return round(memory_get_usage($t) / 1024 / 1024, 2);
	else
		return 'Unknown';
}
function GOTMLS_update_status($status) {
	$memory_usage = GOTMLS_memory_usage();
	$microtime = ceil(microtime(true)-get_option('GOTMLS_LAST_scan_start'));
	return "\nupdate_status('".microtime(true).'-'.get_option('GOTMLS_LAST_scan_start').str_replace("'", "\\'", $status)."', $microtime, '$memory_usage MB');";
}
$skipped_files = 0;
$scanned_files = 0;
$_SERVER_REQUEST_URI = str_replace('&amp;','&', htmlspecialchars( $_SERVER['REQUEST_URI'] , ENT_QUOTES ) );
$script_URI = $_SERVER_REQUEST_URI.(strpos($_SERVER_REQUEST_URI,'?')?'&':'?').'ts='.microtime(true);
function GOTMLS_scandir($dir, $current_depth = 0) {
	global $bad_backups, $GOTMLS_ERRORS, $potential_threats, $threats_found, $GOTMLS_images_path, $skip_dirs, $skip_files, $total_files, $skipped_files, $scanned_files, $skipped_dirs, $total_dirs, $threats_fixed, $GOTMLS_FIRST_scandir_start, $file_at_depth, $current_percent, $current_files;
	$dirs = GOTMLS_explode_dir($dir, '.');
	set_time_limit(30);
	if (($current_depth==0 || !in_array($dirs[count($dirs)-1], $skip_dirs)) && is_dir($dir)) {
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
	global $_SERVER_REQUEST_URI, $GOTMLS_plugin_dir, $GOTMLS_update_home, $GOTMLS_plugin_home, $GOTMLS_updated_images_path, $GOTMLS_Version, $GOTMLS_images_path, $GOTMLS_definitions_version, $GOTMLS_Version, $wp_version, $current_user,$GOTMLS_updated_definition_path;
	get_currentuserinfo();
$_SESSION['eli_debug_microtime']['GOTMLS'][(microtime(true)-$_SESSION['eli_debug_microtime']['GOTMLS']['START_microtime']).' GOTMLS_display_header_start'] = GOTMLS_memory_usage(true);
	$GOTMLS_url = get_option('siteurl');
	$GOTMLS_url_parts = explode('/', $GOTMLS_url);
	$wait_img_URL = $GOTMLS_images_path.'wait.gif';
	if (isset($_GET['check_site']) && $_GET['check_site'] == 1)
		echo '<div id="check_site" style="float: right; margin: 15px;"><img src="'.$GOTMLS_images_path.'checked.gif"> Tested your site. It appears we didn\'t break anything ;-)</div><style>#footer, #GOTMLS-Settings, #right-sidebar, #admin-page-container, #wpadminbar, #adminmenuback, #adminmenuwrap, #adminmenu {display: none !important;} #wpbody-content {padding-bottom: 0;}';
	else
		echo '<style>#right-sidebar {float: right; margin-right: 10px; width: 290px;}';
	$ver_info = $GOTMLS_Version.'&p='.$GOTMLS_plugin_dir.'&wp='.$wp_version.'&ts='.date("YmdHis").'&nkey='.md5($GOTMLS_url).'&okey='.md5($GOTMLS_url_parts[2]).'&d='.ur1encode($GOTMLS_url);
	$Update_Link = '<div style="text-align: center;"><a href="';
	$new_version = '';
	$file = 'gotmls/index.php';
	$current = get_site_transient('update_plugins');
	if (isset($current->response[$file]->new_version)) {
		$new_version = 'Upgrade to '.$current->response[$file]->new_version.' now!<br /><br />';
		$Update_Link .= wp_nonce_url(self_admin_url('update.php?action=upgrade-plugin&plugin=').$file, 'upgrade-plugin_'.$file);
	}
	$Update_Link .= "\">$new_version</a></div>";
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
#main-page-title {
	background: url("http://1.gravatar.com/avatar/5feb789dd3a292d563fea3b885f786d6?s=64&r=G") no-repeat scroll 0 0 transparent;
	line-height: 22px;
    margin: 10px 0 0;
    padding: 0 0 0 84px;}
</style>
<script>
function showhide(id) {
	divx = document.getElementById(id);
	if (divx.style.display == "none" || arguments[1]) {
		divx.style.display = "";
		return true;
	} else {
		divx.style.display = "none";
		return false;
	}
}
</script>
<h1 id="main-page-title">'.$pTitle.'</h1>
<div id="right-sidebar" class="metabox-holder">
	<div id="pluginupdates" class="shadowed-box stuffbox"><h3 class="hndle"><span>Plugin Updates</span></h3>
		<div id="findUpdates" class="inside"><center>Searching for updates ...<br /><img src="'.$wait_img_URL.'" alt="Wait..." /><br /><input type="button" value="Cancel" onclick="document.getElementById(\'findUpdates\').innerHTML = \'Could not find server!\';" /></center></div>
		<script type="text/javascript" src="'.$GOTMLS_plugin_home.$GOTMLS_updated_images_path.'?js='.$ver_info.'"></script>
		'.$Update_Link.'
	</div>
	<div id="definitionupdates" class="stuffbox shadowed-box"><h3 class="hndle"><span>Definition Updates</span></h3>
		<script>
		function check_for_updates(chk) {
			if (auto_img = document.getElementById("autoUpdateDownload")) {
				auto_img.style.display="";
				check_for_donation(chk);
			}
		}
		function check_for_donation(chk) {
			if (auto_img.src.replace(/^.+\?/,"")=="0") {
				alert("Please make a donation to use this feature!");
				if (chk == "AutomaticallyRepair" || '.$GOTMLS_definitions_version.'0 > 12040000000) {
					window.open("http://gotmls.net/donate/?donation-key='.md5($GOTMLS_url).'&donation-source="+chk, "_blank");
				}
			}
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
			check_for_updates(dUpdates);
			foundUpdates = document.getElementById("autoUpdateForm");
			if (foundUpdates)
				foundUpdates.style.display = "";
		}
		</script>
	<form id="updateform" method="post" name="updateform" action="'.$_SERVER_REQUEST_URI.'">
		<img style="display: none; float: right; margin-right: 14px;" src="'.$GOTMLS_images_path.'checked.gif" alt="definitions file updated" id="autoUpdateDownload" onclick="downloadUpdates(\'UpdateDownload\');">
		<div id="Definition_Updates" class="inside"><center>Searching for updates ...<br /><img src="'.$wait_img_URL.'" alt="Wait..." /><br /><input type="button" value="Cancel" onclick="document.getElementById(\'Definition_Updates\').innerHTML = \'Could not find server!\';" /></center></div>
		<div id="autoUpdateForm" style="display: none;" class="inside">
		<input type="submit" name="auto_update" onclick="check_for_updates(\'DownloadDefinitions\');" value="Download new definitions!"> 
		</div>
	</form>
		<div id="registerKeyForm" style="display: none;" class="inside">
Register your Key now and get instant access to new definition files as new threats are discovered.
<p>*All fields are required and I will NOT share your registration information with anyone.</p>
<form id="registerform" onsubmit="return sinupFormValidate(this);" action="http://gotmls.net/wp-login.php?action=register" method="post" name="registerform"><input type="hidden" name="redirect_to" id="register_redirect_to" value="/donate/"><input type="hidden" name="user_login" id="register_user_login" value="">
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
		<script type="text/javascript" src="'.$GOTMLS_update_home.$GOTMLS_updated_definition_path.'?div=Definition_Updates&ver='.$GOTMLS_definitions_version.'&v='.$ver_info.'"></script>
	</div>
	<div id="pluginlinks" class="shadowed-box stuffbox"><h3 class="hndle"><span>Plugin Links</span></h3>
		<div class="inside">
			<form name="ppdform" id="ppdform" action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_blank"> 
			<input type="hidden" name="cmd" value="_donations"> 
			<input type="hidden" name="business" value="donations@gotmls.net"> 
			<input type="hidden" name="no_shipping" value="1"> 
			<input type="hidden" name="no_note" value="1"> 
			<input type="hidden" name="currency_code" value="USD"> 
			<input type="hidden" name="tax" value="0"> 
			<input type="hidden" name="lc" value="US"> 
			<input type="hidden" name="bn" value="PP-DonationsBF"> 
			<input type="radio" name="amount" value="5.00">$5
			<input type="radio" name="amount" value="10.00">$10
			<input type="radio" name="amount" value="15.00" checked>$15
			<input type="radio" name="amount" value="20.00">$20
			<input type="radio" name="amount" value="25.00">$25
			<input type="radio" name="amount" value="30.00">$30
			<input type="hidden" name="item_name" value="Donation to Eli\'s Anti-Malware Plugin"> 
			<input type="hidden" name="item_number" value="GOTMLS-key-'.md5($GOTMLS_url).'"> 
			<input type="hidden" name="custom" value="key-'.md5($GOTMLS_url).'"> 
			<input type="hidden" name="notify_url" value="http://gotmls.net/?ipn">
			<input type="hidden" name="return" value="http://gotmls.net/donate/?paid='.md5($GOTMLS_url).'">
			<input type="hidden" name="cancel_return" value="http://gotmls.net/donate/?cancel='.md5($GOTMLS_url).'">
			<div style="height: 28px;">
				<div class="pp_donate pp_left"><input type="image" src="https://www.paypalobjects.com/en_US/i/scr/pixel.gif" border="0" name="submit" alt="Make a Donation with PayPal"></div>
				<div class="pp_donate pp_right"><input type="image" src="https://www.paypalobjects.com/en_US/i/scr/pixel.gif" border="0" name="submitc" alt="Make a Donation with your credit card at PayPal"></div>
			</div>
			<div>
				<ul class="sidebar-links">
					<li style="float: right;"><b>on <a target="_blank" href="http://wordpress.org/extend/plugins/profile/scheeeli">WordPress.org</a></b><ul class="sidebar-links">
						<li><a target="_blank" href="http://wordpress.org/extend/plugins/'.strtolower($GOTMLS_plugin_dir).'/faq/">Plugin FAQs</a>
						<li><a target="_blank" href="http://wordpress.org/extend/plugins/'.strtolower($GOTMLS_plugin_dir).'/stats/">Download Stats</a>
						<li><a target="_blank" href="http://wordpress.org/tags/'.strtolower($GOTMLS_plugin_dir).'">Forum Posts</a>
					</ul></li>
					<li><b>on <a target="_blank" href="'.$GOTMLS_plugin_home.'category/my-plugins/">Eli\'s Blog</a></b><ul class="sidebar-links">
						<li><a target="_blank" href="'.$GOTMLS_plugin_home.'category/my-plugins/anti-malware/">Anti-Malware</a>
					</ul></li>
					<li><b>on <a target="_blank" href="'.$GOTMLS_update_home.'">GOTMLS.NET</a></b><ul class="sidebar-links">
						<li><a target="_blank" href="'.$GOTMLS_update_home.'blog/">Blog</a>
					</ul></li>
				</ul>
			</div>
			</form>
		</div>
	</div>
	'.$optional_box.'
</div>
<div id="admin-page-container">
	<div id="main-section">';
$_SESSION['eli_debug_microtime']['GOTMLS'][(microtime(true)-$_SESSION['eli_debug_microtime']['GOTMLS']['START_microtime']).' GOTMLS_display_header_end'] = GOTMLS_memory_usage(true);
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
	global $threats_found, $bad_backups, $GOTMLS_plugin_dir, $GOTMLS_images_path, $total_files, $skipped_files, $scanned_files, $skipped_dirs, $total_dirs, $potential_threats, $GOTMLS_ERRORS, $skip_files, $skip_dirs, $threats_fixed, $GOTMLS_FIRST_scandir_start, $GOTMLS_known_threats;
$_SESSION['eli_debug_microtime']['GOTMLS'][(microtime(true)-$_SESSION['eli_debug_microtime']['GOTMLS']['START_microtime']).' GOTMLS_Settings_start'] = GOTMLS_memory_usage(true);
	$noYesList = array('No', 'Yes');
	$GOTMLS_menu_groups = array('Main Menu Item placed below <b>Comments</b> and above <b>Appearance</b>','Main Menu Item placed below <b>Settings</b>','Sub-Menu inside the <b>Tools</b> Menu Item');
	$GOTMLS_scan_groups = array();
	$dirs = GOTMLS_explode_dir(__file__);
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
	if (!(isset($GOTMLS_settings_array['exclude_ext']) && is_array($GOTMLS_settings_array['exclude_ext'])))
		$GOTMLS_settings_array['exclude_ext'] = $skip_files;
	if (!isset($GOTMLS_settings_array['custom_reg_exp']))
		$GOTMLS_settings_array['custom_reg_exp'] = '';
	if (isset($_POST['exclude_ext']) && strlen(trim($_POST['exclude_ext'].' ')) >0) {
		$GOTMLS_settings_array['exclude_ext'] = preg_split("/[,]+/", trim($_POST['exclude_ext']), -1, PREG_SPLIT_NO_EMPTY);
		array_walk($GOTMLS_settings_array['exclude_ext'], 'GOTMLS_trim_ar');
	}
	$skip_files = array_merge($GOTMLS_settings_array['exclude_ext'], array('bad'));
	if (!(isset($GOTMLS_settings_array['exclude_dir']) && is_array($GOTMLS_settings_array['exclude_dir'])))
		$GOTMLS_settings_array['exclude_dir'] = array();
	if (isset($_POST['exclude_dir'])) {
		if (strlen(trim(str_replace(',','',$_POST['exclude_dir']).' ')) > 0)
			$GOTMLS_settings_array['exclude_dir'] = preg_split("/[\s]*([,]+[\s]*)+/", trim($_POST['exclude_dir']), -1, PREG_SPLIT_NO_EMPTY);
		else
			$GOTMLS_settings_array['exclude_dir'] = array();
	}
	$skip_dirs = array_merge($GOTMLS_settings_array['exclude_dir'], $skip_dirs);
	if (isset($_POST['scan_what']) && is_numeric($_POST['scan_what']) && $_POST['scan_what'] != $GOTMLS_settings_array['scan_what'])
		$GOTMLS_settings_array['scan_what'] = $_POST['scan_what'];
//	else echo '<li>POST != $GOTMLS_settings_array('.$GOTMLS_settings_array['scan_what'].')='.($_POST['scan_what'] != $GOTMLS_settings_array['scan_what']);
	if (isset($_POST['custom_reg_exp']) && $_POST['custom_reg_exp'] != $GOTMLS_settings_array['custom_reg_exp']) {
		$_POST['custom_reg_exp'] = stripslashes($_POST['custom_reg_exp']);
		$GOTMLS_settings_array['custom_reg_exp'] = ($_POST['custom_reg_exp']);
	}
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
	$scan_optjs = "<script>\nfunction showOnly(what) {\n";
	foreach ($GOTMLS_scan_groups as $mg => $GOTMLS_scan_group) {
		$scan_optjs .= "document.getElementById('only$mg').style.display = 'none';\n";
		$scan_opts .= '<div style="position: relative; float: left; padding: 4px 14px;" id="scan_group_div_'.$mg.'"><input type="radio" name="scan_what" id="not-only'.$mg.'" value="'.$mg.'"'.($GOTMLS_settings_array['scan_what']==$mg?' checked':'').' /><a style="text-decoration: none;" href="#scan_what" onclick="showOnly(\''.$mg.'\');document.getElementById(\'not-only'.$mg.'\').checked=true;">'.$GOTMLS_scan_group.'</a><br /><div class="rounded-corners" style="position: absolute; display: none; background-color: #CCCCFF;" id="only'.$mg.'"><a class="rounded-corners" style="float: right; padding: 0 4px; margin: 0 0 0 30px; text-decoration: none; color: #CC0000; background-color: #FFCCCC; border: solid #FF0000 1px;" href="#scan_what" onclick="showhide(\'only'.$mg.'\');">X</a><b>Only&nbsp;Scan&nbsp;These&nbsp;Folders:</b>';
		$dir = implode('/', array_slice($dirs, 0, -1 * (2 + $mg)));
		if (($files = GOTMLS_getfiles($dir)) !== false)
			foreach ($files as $file)
				if (is_dir($dir.'/'.$file) && !($file=='.' || $file=='..'))
					$scan_opts .= '<br /><input type="checkbox" name="scan_only[]" value="'.$file.'" />'.$file;
		$scan_opts .= '</div></div>';
	}
	$scan_optjs .= "document.getElementById('only'+what).style.display = 'block';\n}\n</script>";
	$scan_opts .= $scan_optjs.'<br style="clear: left;" /><p><b>Scan Depth:</b> (how far do you want to drill down from your starting directory)<br /><input type="text" value="'.$GOTMLS_settings_array['scan_depth'].'" name="scan_depth"> (-1 is infinite depth)</p><p><h3>What to look for:</h3><br /><div style="float: left; padding: 0; width: 100%;" id="check_timthumb_div">';
	if (isset($GOTMLS_known_threats['timthumb']) && is_array($GOTMLS_known_threats['timthumb'])) {
		foreach ($noYesList as $nY => $noYes)
			$scan_opts .= '<div style="float: right; padding: 14px;" id="check_timthumb_div_'.$nY.'"><input type="radio" name="check_timthumb" value="'.$nY.'"'.($GOTMLS_settings_array['check_timthumb']==$nY?' checked':'').' />'.$noYes.'</div>';
	} else
		$scan_opts .= '<div style="float: right; padding: 14px;" id="check_timthumb_div_NA">Registration of your Installation Key is required for this feature</div>';
	$scan_opts .= 'Check for timthumb versions older than 2.0 (a common vulnerability used to plant malicious code) and upgrade them to version 2.8.10:</div><hr style="clear: left; color: #cccccc; background-color: #cccccc;" /><div style="float: left; padding: 0; width: 100%;" id="check_htaccess_div">';
	if (isset($GOTMLS_known_threats['htaccess']) && is_array($GOTMLS_known_threats['htaccess'])) {
		foreach ($noYesList as $nY => $noYes)
			$scan_opts .= '<div style="float: right; padding: 14px;" id="check_htaccess_div_'.$nY.'"><input type="radio" name="check_htaccess" value="'.$nY.'"'.($GOTMLS_settings_array['check_htaccess']==$nY?' checked':'').' />'.$noYes.'</div>';
	} else
		$scan_opts .= '<div style="float: right; padding: 14px;" id="check_htaccess_div_NA">Registration of your Installation Key is required for this feature</div>';
	$scan_opts .= 'Check .htaccess files for over indented lines (a common way to try and hide malicious code):</div><hr style="clear: left; color: #cccccc; background-color: #cccccc;" /><div style="float: left; padding: 0; width: 100%;" id="check_known_div">';
	if (isset($GOTMLS_known_threats['known']) && is_array($GOTMLS_known_threats['known'])) {
		foreach ($noYesList as $nY => $noYes)
			$scan_opts .= '<div style="float: right; padding: 14px;" id="check_known_div_'.$nY.'"><input type="radio" name="check_known" value="'.$nY.'"'.($GOTMLS_settings_array['check_known']==$nY?' checked':'').' />'.$noYes.'</div>';
	} else
		$scan_opts .= '<div style="float: right; padding: 14px;" id="check_known_div_NA">Registration of your Installation Key is required for this feature</div>';
	$scan_opts .= 'Check for known threats (malicious scripts that use eval&#40;&#41; and similar techniques to infect your server):</div><br style="clear: left;" /><hr style="clear: left; color: #cccccc; background-color: #cccccc;" /><div style="float: left; padding: 0; width: 100%;" id="check_potential_div">';
	foreach ($noYesList as $nY => $noYes)
		$scan_opts .= '<div style="float: right; padding: 14px;" id="check_potential_div_'.$nY.'"><input type="radio" name="check_potential" value="'.$nY.'"'.($GOTMLS_settings_array['check_potential']==$nY?' checked':'').' />'.$noYes.'</div>';
	$scan_opts .= 'Check for potential threats (This option just looks for the usage of eval&#40;&#41;. It is usually not a threat but it could be. This helps you examine each file to see for yourself if you think it is dangerous or malicious. If you have reason to believe there is a threat hear you should have it examined by an expert.):</div><br style="clear: left;" /><h3>What to skip:</h3><p><b>Skip files with the following extentions:</b>(a comma separated list of file extentions to be excluded from the scan)<br /><input type="text" name="exclude_ext" value="'.implode($GOTMLS_settings_array['exclude_ext'], ',').'" style="width: 90%;" /></p><p><b>Skip directories with the following names:</b>(a comma separated list of folders to be excluded from the scan)<br /><input type="text" name="exclude_dir" value="'.implode($GOTMLS_settings_array['exclude_dir'], ',').'" style="width: 90%;" /></p>';
	if (isset($_GET['eli'])) $scan_opts .= '<p><b>Custom code search:</b>(a reg_exp string to be searched for, this is for very advanced users. Please do not use this without talking to Eli first. If used incorrectly you could break your entire site.)<br /><input type="text" name="custom_reg_exp" value="'.$GOTMLS_settings_array['custom_reg_exp'].'" style="width: 90%;" /></p>';//still testing this option
	$menu_opts = '<div class="stuffbox shadowed-box">
		<h3 class="hndle"><span>Menu Item Placement Options</span></h3>
		<div class="inside"><form method="POST" name="GOTMLS_menu_Form">';
	foreach ($GOTMLS_menu_groups as $mg => $GOTMLS_menu_group)
		$menu_opts .= '<div style="padding: 4px;" id="menu_group_div_'.$mg.'"><input type="radio" name="GOTMLS_menu_group" value="'.$mg.'"'.($GOTMLS_settings_array['menu_group']==$mg?' checked':'').' onchange="document.GOTMLS_menu_Form.submit();" />'.$GOTMLS_menu_group.'</div>';
	GOTMLS_display_header('Anti-Malware by <img style="vertical-align: middle;" alt="ELI" src="http://0.gravatar.com/avatar/8151cac22b3fc543d099241fd573d176?s=64&r=G" /> at GOTMLS.NET', $menu_opts.'</form><br style="clear: left;" /></div></div>');
//echo("<textarea>".print_r($GOTMLS_known_threats, true)."</textarea>");
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
function showOnly(what) {
	document.getElementById("only_what").innerHTML = document.getElementById("only"+what).innerHTML;
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
		foreach ($_POST as $name => $value) {
			if (substr($name, 0, 4) != 'fix_') {
				if (is_array($value)) {
					foreach ($value as $val)
						echo '<input type="hidden" name="'.$name.'[]" value="'.$val.'">';
				} else
					echo '<input type="hidden" name="'.$name.'" value="'.$value.'">';
			}
		}
		echo '<p align="center">
		<center><div class="rounded-corners" align="center" style="margin: 10px; vertical-align: middle; background-color: #cccccc; z-order: 3; border: 2px double #000000;"><b><a name="found_top">Scan Status</a></b><br><div id="status_bar"></div></div></center></p><p id="fix_button" style="display: none; text-align: right;"><input type="submit" value="Abort Scan and Repair SELECTED files Now" class="button-primary" onclick="check_for_donations(\'AbortRepair\');" /></p>
		<h3><span>Scan Details:</span></h3><div id="scan_details" class="inside">';
		if ($_POST['scan_what'] > -1) {
			$dir = implode('/', array_slice($dirs, 0, -1 * (2 + $_POST['scan_what'])));
			echo '<ul name="found_top_known-2">';
			$GOTMLS_FIRST_scandir_start = microtime(true);
			if (is_dir($dir)) {
				if (isset($_POST['scan_only']) && is_array($_POST['scan_only'])) {
					foreach ($_POST['scan_only'] as $only_dir)
						if (is_dir($dir.'/'.$only_dir))
							GOTMLS_scandir($dir.'/'.$only_dir);
				} else
					GOTMLS_scandir($dir);
			} else
				echo "<li>Failed: $dir is not a directory!</li>";
			ksort($threats_found);
			echo '</ul>';
			$show_fix_b = 'showhide("fix_button");';
			$known_threat_count = 0;
			foreach ($threats_found as $threats_type => $threats_array)
				if ($threats_type != 'potential')
					$known_threat_count += count($threats_array);
			if ($known_threat_count > 0)
				echo '<p style="text-align: right;"><input type="submit" value="Automatically Repair SELECTED files Now" class="button-primary" onclick="check_for_donations(\'AutomaticallyRepair\');" /></p>';
			else
				$show_fix_b = '';
			echo '<ul>'.return_threats(2)."</ul>\n<script>update_status(' Completed!</div><div style=\"width: 100%;\">".$GOTMLS_ERRORS."<ul style=\"float: right; text-align: left;\">".return_threats(1)."</ul>', 100, ".floor(microtime(true)-$GOTMLS_FIRST_scandir_start).", ".($total_dirs-$skipped_dirs).", $scanned_files, $skipped_files, $bad_backups);".$show_fix_b."</script>";
		}
		update_option($GOTMLS_plugin_dir.'_settings_array', $GOTMLS_settings_array);
		echo '</div></form></div></div>';
	}
	echo '<div class="stuffbox shadowed-box">
			<h3 class="hndle"><span>Scan Settings</span></h3>
			<div class="inside">
				<form method="POST" name="GOTMLS_Form"><p>'.$scan_opts.'</p>
				<p style="text-align: right;"><input type="submit" value="Scan Now" class="button-primary" /></p></form></div></div>';
	echo '</div></div></div><script>setDivNAtext();</script>';
$_SESSION['eli_debug_microtime']['GOTMLS'][(microtime(true)-$_SESSION['eli_debug_microtime']['GOTMLS']['START_microtime']).' GOTMLS_Settings_end'] = GOTMLS_memory_usage(true);
}
function GOTMLS_set_plugin_action_links($links_array, $plugin_file) {
	if ($plugin_file == substr(__file__, (-1 * strlen($plugin_file)))) {
		$_SESSION['eli_debug_microtime']['GOTMLS'][(microtime(true)-$_SESSION['eli_debug_microtime']['GOTMLS']['START_microtime']).' GOTMLS_set_plugin_action_links'] = GOTMLS_memory_usage(true);
		$GOTMLS_settings_array = get_option('GOTMLS_settings_array');
		if ($GOTMLS_settings_array['menu_group'] == 2)
			$base_page = 'tools.php';
		else
			$base_page = 'admin.php';
		$links_array = array_merge(array('<a href="'.$base_page.'?page=GOTMLS-settings">'.__( 'Settings' ).'</a>'), $links_array);
	}
	return $links_array;
}
function GOTMLS_set_plugin_row_meta($links_array, $plugin_file) {
	if ($plugin_file == substr(__file__, (-1 * strlen($plugin_file)))) {
		$_SESSION['eli_debug_microtime']['GOTMLS'][(microtime(true)-$_SESSION['eli_debug_microtime']['GOTMLS']['START_microtime']).' GOTMLS_set_plugin_row_meta'] = GOTMLS_memory_usage(true);
		$links_array = array_merge($links_array, array('<a target="_blank" href="http://wordpress.org/extend/plugins/gotmls/faq/">'.__( 'FAQ' ).'</a>','<a target="_blank" href="http://gotmls.net/support/">'.__( 'Support' ).'</a>','<a target="_blank" href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=QZHD8QHZ2E7PE">'.__( 'Donate' ).'</a>'));
	}
	return $links_array;
}
function GOTMLS_stripslashes(&$item, $key) {
	$item = stripslashes($item);
}
$GOTMLS_auto_update = 0;
function GOTMLS_init() {
	global $GOTMLS_definitions_version, $GOTMLS_known_threats, $GOTMLS_auto_update, $GOTMLS_plugin_dir, $GOTMLS_local_images_path;
$_SESSION['eli_debug_microtime']['GOTMLS'][(microtime(true)-$_SESSION['eli_debug_microtime']['GOTMLS']['START_microtime']).' GOTMLS_init_start'] = GOTMLS_memory_usage(true);
	$GOTMLS_settings_array = get_option($GOTMLS_plugin_dir.'_settings_array');
	if (isset($GOTMLS_settings_array['scan_level']) && is_numeric($GOTMLS_settings_array['scan_level']))
		$scan_level = intval($GOTMLS_settings_array['scan_level']);
	else
		$scan_level = 3;
	if (isset($GOTMLS_settings_array['definitions_version']) && is_numeric($GOTMLS_settings_array['definitions_version']) && intval($GOTMLS_settings_array['definitions_version']) >= $GOTMLS_definitions_version) {
		$known_threats = get_option($GOTMLS_plugin_dir.'_known_threats_array');
		if (is_array($known_threats) && isset($known_threats['potential'])) {
			$GOTMLS_known_threats = $known_threats;
			$GOTMLS_definitions_version = $GOTMLS_settings_array['definitions_version'];
		}
	}
	if (isset($_POST['UPDATE_known_threats']) && isset($_POST['definitions_version']) && is_numeric($_POST['definitions_version'])) {
		if (is_array($_POST['UPDATE_known_threats'])) {
			$GOTMLS_known_threats = ($_POST['UPDATE_known_threats']);
			if (isset($GOTMLS_known_threats['potential']['eval']) && substr($GOTMLS_known_threats['potential']['eval'], -5) == '\\\\)/i')
				array_walk_recursive($GOTMLS_known_threats, 'GOTMLS_stripslashes');
			$GOTMLS_definitions_version = intval($_POST['definitions_version']);
		} else {
			$GOTnew_known_threats = explode('-+===+-', base64_decode($_POST['UPDATE_known_threats']));
			if (is_array($GOTnew_known_threats) && count($GOTnew_known_threats)>1 && strlen($GOTnew_known_threats[0])==0) {
				for ($GOTnew_threat_types = 1; $GOTnew_threat_types  < count($GOTnew_known_threats); $GOTnew_threat_types++) {
					$GOTnew_threats = explode('-+==+-', $GOTnew_known_threats[$GOTnew_threat_types]);
					if (is_array($GOTnew_threats) && count($GOTnew_threats)>1) {
						$GOTMLS_threat_definitions = array();
						for ($GOTnew_threat = 1; $GOTnew_threat  < count($GOTnew_threats); $GOTnew_threat++) {
							$GOTnew_threat_definitions = explode('-+=+-', $GOTnew_threats[$GOTnew_threat]);
							if (is_array($GOTnew_threat_definitions) && count($GOTnew_threat_definitions)==2)
								$GOTMLS_threat_definitions[$GOTnew_threat_definitions[0]] = $GOTnew_threat_definitions[1];
						}
						$GOTMLS_known_threats[$GOTnew_threats[0]] = $GOTMLS_threat_definitions;
						$GOTMLS_definitions_version = intval($_POST['definitions_version']);
					}
				}
			}
		}
	}
	$GOTMLS_settings_array['definitions_version'] = $GOTMLS_definitions_version;
	if (isset($_POST['scan_level']) && is_numeric($_POST['scan_level']))
		$scan_level = intval($_POST['scan_level']);
	if (isset($scan_level) && is_numeric($scan_level))
		$GOTMLS_settings_array['scan_level'] = intval($scan_level);
	else
		$GOTMLS_settings_array['scan_level'] = 3;
	update_option($GOTMLS_plugin_dir.'_known_threats_array', $GOTMLS_known_threats);
	update_option($GOTMLS_plugin_dir.'_settings_array', $GOTMLS_settings_array);
$_SESSION['eli_debug_microtime']['GOTMLS'][(microtime(true)-$_SESSION['eli_debug_microtime']['GOTMLS']['START_microtime']).' GOTMLS_init_end'] = GOTMLS_memory_usage(true);
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
$GOTMLS_updated_definition_path = 'wp-content/plugins/update/definitions/';
$GOTMLS_Logo_IMG = 'GOTMLS-16x16.gif';
$GOTMLS_definitions_version = 1204000000;
$GOTMLS_known_threats = array(
	'potential' => array(
		'preg_replace /e' => '/preg_replace[ \t]*\([^\)]+[\/\#\|]e[\'"].+\)/i',
		'eval' => "/[^a-z\/'\"]eval\(.+\)/i"));
register_activation_hook(__FILE__,$GOTMLS_plugin_dir.'_install');
add_action('admin_menu', $GOTMLS_plugin_dir.'_menu');
$init = add_action('admin_init', $GOTMLS_plugin_dir.'_init');
$_SESSION['eli_debug_microtime']['GOTMLS']['START_memory_usage'] = GOTMLS_memory_usage(true);
?>