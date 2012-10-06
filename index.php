<?php
/*
Plugin Name: Anti-Malware by ELI (Get Off Malicious Scripts)
Plugin URI: http://gotmls.net/
Author: Eli Scheetz
Author URI: http://wordpress.ieonly.com/category/my-plugins/anti-malware/
Contributors: scheeeli
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=QZHD8QHZ2E7PE
Description: This Anti-Virus/Anti-Malware plugin searches for Malware and other Virus like threats and vulnerabilities on your server and helps you remove them. It's always growing and changing to adapt to new threats so let me know if it's not working for you.
Version: 1.2.10.05
*/
$GOTMLS_Version='1.2.10.05';
$_SESSION['GOTMLS_debug'] = array();
$_SESSION['GOTMLS_debug']['START_microtime'] = microtime(true);
$GOTMLS_plugin_dir='GOTMLS';
$GOTMLS_loop_execution_time = 60;
$GOTMLS_chmod = 0644;
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
$_SESSION['GOTMLS_debug'][(microtime(true)-$_SESSION['GOTMLS_debug']['START_microtime']).' GOTMLS_install_start'] = GOTMLS_memory_usage(true);
	if (version_compare($wp_version, "2.6", "<"))
		die(__("This Plugin requires WordPress version 2.6 or higher"));
$_SESSION['GOTMLS_debug'][(microtime(true)-$_SESSION['GOTMLS_debug']['START_microtime']).' GOTMLS_install_end'] = GOTMLS_memory_usage(true);
}
function GOTMLS_menu() {
	global $GOTMLS_plugin_dir, $GOTMLS_images_path;
$_SESSION['GOTMLS_debug'][(microtime(true)-$_SESSION['GOTMLS_debug']['START_microtime']).' GOTMLS_menu_start'] = GOTMLS_memory_usage(true);
	$GOTMLS_settings_array = get_option($GOTMLS_plugin_dir.'_settings_array');
	if (isset($_POST['GOTMLS_menu_group']) && is_numeric($_POST['GOTMLS_menu_group']) && $_POST['GOTMLS_menu_group'] != $GOTMLS_settings_array['menu_group']) {
		$GOTMLS_settings_array['menu_group'] = $_POST['GOTMLS_menu_group'];
		update_option($GOTMLS_plugin_dir.'_settings_array', $GOTMLS_settings_array);
	}
	$GOTMLS_Full_plugin_logo_URL = $GOTMLS_images_path.'GOTMLS-16x16.gif';
	$base_page = $GOTMLS_plugin_dir.'-settings';
	if ($GOTMLS_settings_array['menu_group'] == 2)
		add_submenu_page('tools.php', __('Anti-Malware Settings/Scan Page'), __('<span style="background: url(\''.$GOTMLS_Full_plugin_logo_URL.'\') no-repeat; vertical-align: middle; border: 0 none; display: inline-block; height: 16px; width: 16px;"></span> Anti-Malware'), 'administrator', $base_page, str_replace('-', '_', $base_page));
	else {
		if (!function_exists('add_object_page') || $GOTMLS_settings_array['menu_group'] == 1)
			add_menu_page(__('Anti-Malware Settings/Scan'), __('Anti-Malware'), 'administrator', $base_page, $GOTMLS_plugin_dir.'_settings', $GOTMLS_Full_plugin_logo_URL);
		else
			add_object_page(__('Anti-Malware Settings/Scan'), __('Anti-Malware'), 'administrator', $base_page, $GOTMLS_plugin_dir.'_settings', $GOTMLS_Full_plugin_logo_URL);
		add_submenu_page($base_page, __('Anti-Malware Settings'), __('Update Settings'), 'administrator', $base_page, $GOTMLS_plugin_dir.'_settings');
		add_submenu_page($base_page, __('Anti-Malware Quick Scan'), __('Run Quick Scan'), 'administrator', $base_page.'&scan_type=Quick+Scan', $GOTMLS_plugin_dir.'_settings');
	}
$_SESSION['GOTMLS_debug'][(microtime(true)-$_SESSION['GOTMLS_debug']['START_microtime']).' GOTMLS_menu_end'] = GOTMLS_memory_usage(true);
}
function GOTMLS_debug($my_error = '', $echo_error = false) {
	global $GOTMLS_plugin_dir, $wp_version;
	$mtime=date("Y-m-d H:i:s", filemtime(__file__));
	if ($echo_error || (substr($my_error, 0, 22) == 'Access denied for user'))
		echo "<li>debug:<pre>$my_error\n".print_r($_SESSION['GOTMLS_debug'],true).'END;</pre>';
	$_SESSION['GOTMLS_debug']=array();
	return $my_error;
}
function GOTMLS_get_ext($filename) {
	$nameparts = explode('.', '.'.$filename);
	return strtolower($nameparts[(count($nameparts)-1)]);
}
function GOTMLS_check_threat($threat_level, $file='') {
	global $GOTMLS_threats_found, $GOTMLS_known_threats, $GOTMLS_new_contents, $GOTMLS_file_contents;
	$GOTMLS_threats_found = array();
	$found = 0;
	if (isset($GOTMLS_known_threats[$threat_level]) && is_array($GOTMLS_known_threats[$threat_level])) {
		if (isset($_GET['eli']) && isset($_REQUEST['check_only'][$threat_level]) && is_array($_REQUEST['check_only'][$threat_level]))
			$threat_names = $_REQUEST['check_only'][$threat_level];
		else
			$threat_names = array_reverse(array_keys($GOTMLS_known_threats[$threat_level]));
		foreach ($threat_names as $threat_name) {
			if ($found = @preg_match_all($GOTMLS_known_threats[$threat_level][$threat_name], $GOTMLS_new_contents, $GOTMLS_threats_found)) {
				if ($threat_level == 'timthumb') {
					if (!($GOTMLS_new_contents = @file_get_contents(dirname(__FILE__).'/images/tt2.php')))
						$GOTMLS_new_contents = $GOTMLS_file_contents;
				} else {
					foreach ($GOTMLS_threats_found[0] as $find)
						$GOTMLS_new_contents = str_replace($find, '', $GOTMLS_new_contents);
					$GOTMLS_new_contents = trim(preg_replace('/[\r\n]+/', "\n", preg_replace('/\<\?php[ \t\n\r]*\?\>/i', '', $GOTMLS_new_contents)));
				}
				return $found;
			}
		}
	} elseif (isset($_GET['eli'])) {//don't use this without registration
		$found = preg_match_all('/'.$threat_level.'/i', $GOTMLS_new_contents, $GOTMLS_threats_found);
	}
	return $found;
}
$GOTMLS_file_contents = '';
$GOTMLS_new_contents = '';
$bad_backups = 0;
function GOTMLS_recoverfile($file) {
	global $recovered_files;
	$GOTMLS_file_contents = '';
	if(file_exists($file)) {
		$GOTMLS_file_contents = @file_get_contents($file);
		if (strlen($GOTMLS_file_contents) > 0 && @file_put_contents(substr($file, 0, -4), $GOTMLS_file_contents)) {
			$recovered_files++;
			return '<li>RECOVERED: '.substr($file, 0, -4).'</li>';
		} else
			return '<li>Failed to write to: '.substr($file, 0, -4).'</li>';
	}
}
$GOTMLS_default_ext = '';
$GOTMLS_encode = '';
$GOTMLS_threats_found = array();
function GOTMLS_scanfile($file) {
	global $GOTMLS_chmod, $GOTMLS_settings_array, $bad_backups, $GOTMLS_file_contents, $GOTMLS_new_contents, $GOTMLS_script_URI;
	$found = false;
	if (file_exists($file) && ($GOTMLS_file_contents = @file_get_contents($file))) {
		$className = "scanned";
		$GOTMLS_new_contents = $GOTMLS_file_contents;
		if (isset($GOTMLS_settings_array['check_htaccess']) && $GOTMLS_settings_array['check_htaccess'] > 0 && GOTMLS_get_ext($file) == 'htaccess') {
			if ($found = GOTMLS_check_threat('htaccess'))
				$className = "htaccess";
		} elseif ((isset($GOTMLS_settings_array['check_timthumb']) && $GOTMLS_settings_array['check_timthumb'] > 0) || (isset($GOTMLS_settings_array['check_known']) && $GOTMLS_settings_array['check_known'] > 0) || (isset($GOTMLS_settings_array['check_potential']) && $GOTMLS_settings_array['check_potential'] > 0)) {
			if (isset($GOTMLS_settings_array['check_timthumb']) && $GOTMLS_settings_array['check_timthumb'] > 0 && strtolower(substr($file, -9)) == 'thumb.php' && ($found = GOTMLS_check_threat('timthumb')))
				$className = "timthumb";
			elseif (isset($GOTMLS_settings_array['check_known']) && $GOTMLS_settings_array['check_known'] > 0 && ($found = GOTMLS_check_threat('known', $file)))
				$className = "known";
			elseif (isset($_GET['eli']) && isset($GOTMLS_settings_array['check_custom']) && strlen(trim($GOTMLS_settings_array['check_custom'])) > 0 && ($found = GOTMLS_check_threat($GOTMLS_settings_array['check_custom'])))
				$className = "potential";
			elseif (isset($GOTMLS_settings_array['check_potential']) && $GOTMLS_settings_array['check_potential'] > 0 && ($found = GOTMLS_check_threat('potential')))
				$className = "potential";
		} else
			$className = "skipped";
	} else {
		$GOTMLS_file_contents = 'Failed to read file contents!';
		$className = "errors";
	}
	$clean_file = GOTMLS_encode($file);
	if ($found) {
		$threat_link = "<a target=\"GOTMLS_iFrame\" href=\"$GOTMLS_script_URI&GOTMLS_scan=$clean_file\" id=\"list_$clean_file\" onclick=\"showhide('GOTMLS_iFrame', true);showhide('GOTMLS_iFrame');showhide('div_file', true);\" class=\"GOTMLS_plugin\">";
		if (isset($_POST['GOTMLS_fix'][$clean_file]) && $_POST['GOTMLS_fix'][$clean_file] > 0 && strlen($GOTMLS_file_contents) > 0 && @file_put_contents($file.'.bad', $GOTMLS_file_contents) && ((strlen($GOTMLS_new_contents)==0 && @unlink($file)) || (@file_put_contents($file, $GOTMLS_new_contents) || ((is_writable($file) || ($chmoded = @chmod($file, 0777))) && @file_put_contents($file, $GOTMLS_new_contents) && !($chmoded && !@chmod($file, $GOTMLS_chmod)))))) {
			echo ' Successs!';
			return "\nfixedFile('$clean_file');";
		} else {
			if (isset($_POST['GOTMLS_fix'][$clean_file]) && $_POST['GOTMLS_fix'][$clean_file] > 0) {
				echo ' Failed!';
				if (isset($_GET['eli']))
					print_r(array('debug_start'=>'<pre>','strlen'=>strlen($GOTMLS_file_contents),'is_writable'=>is_writable($file),'chmod'=>@chmod($file, $GOTMLS_chmod), 'file_put_contents(bad)'=>@file_put_contents($file.'.bad', $GOTMLS_file_contents),'file_put_contents(new)'=>@file_put_contents($file, $GOTMLS_new_contents), 'unlink'=>(strlen($GOTMLS_new_contents)==0?@unlink($file):'strlen:'.strlen($GOTMLS_new_contents)).'</pre>'));
				return;
			}
 			if ($className == "errors") {
				$threat_link = GOTMLS_error_link($GOTMLS_file_contents);
				$imageFile = 'blocked';
			} elseif ($className != "potential") {
				$threat_link = '<input type="checkbox" value="1" name="GOTMLS_fix['.$clean_file.']" id="check_'.$clean_file.'" checked="'.$className.'" />'.$threat_link;
				$imageFile = 'threat';
			} else
				$imageFile = 'question';
			return GOTMLS_return_threat($className, $imageFile, $file, str_replace('GOTMLS_plugin', 'GOTMLS_plugin '.$className, $threat_link));
		}
	} elseif (isset($_POST['GOTMLS_fix'][$clean_file]) && $_POST['GOTMLS_fix'][$clean_file] > 0) {
		echo ' Already Fixed!';
		return "\nfixedFile('$clean_file');";
	} else
		return GOTMLS_return_threat($className, ($className=="scanned"?'checked':'blocked'), $file);
}
function GOTMLS_remove_dots($dir) {
	if ($dir != '.' && $dir != '..')
		return $dir;
}
function GOTMLS_getfiles($dir) {
	$files = false;
	if (is_dir($dir)) {
		if (function_exists('scandir')) {
			$files = @scandir($dir);
			if (is_array($files))
				$files = array_filter($files, 'GOTMLS_remove_dots');
		} else {
			if ($handle = opendir($dir)) {
				$files = array();
				while (false !== ($entry = readdir($handle)))
					if ($entry != '.' && $entry != '..')
						$files[] = "$entry";
				closedir($handle);
			}
		}
	}
	return $files;
}
function GOTMLS_set_global(&$global_var, $string_val) {
	$global_var .= $string_val;
}
function GOTMLS_encode($unencoded_string) {
	$encoded_array = explode('=', base64_encode($unencoded_string).'=');
	return $encoded_array[0].(count($encoded_array)-1);
}
function GOTMLS_decode($encoded_string) {
	return base64_decode(substr($encoded_string, 0, -1).str_repeat('=', intval(substr($encoded_string, -1))));
}
GOTMLS_set_global($GOTMLS_default_ext, 'ieonly.');
$GOTMLS_skip_files = array('png', 'jpg', 'jpeg', 'gif', 'bmp', 'tif', 'tiff', 'exe', 'zip', 'pdf');
$GOTMLS_skip_dirs = array('.', '..');
$total_dirs = 0;
$total_files = 0;
$file_at_depth = array();
GOTMLS_set_global($GOTMLS_encode, '/[\?\-a-z\: \.\=\/A-Z\&\_]/');
function GOTMLS_return_threat($className, $imageFile, $fileName, $link = '') {
	global $GOTMLS_images_path;
	$fileNameJS = str_replace("'", "\\'", $fileName);
	$fileName64 = GOTMLS_encode($fileName);
	$li_js = "\n$className++;";
	if ($className != "scanned")
		$li_js .= "\ndivx=document.getElementById('found_$className');\nif (divx) {\n\tvar newli = document.createElement('li');\n\tnewli.innerHTML='<img src=\"$GOTMLS_images_path"."$imageFile.gif\" style=\"float: left;\" alt=\"$fileNameJS\" id=\"$imageFile"."_$fileName64\">".str_replace("'", "\\'", $link).$fileNameJS.($link?"</a>';\n\tdivx.display='block';":"';")."\n\tdivx.appendChild(newli);\n}";
	if ($link && is_file($fileName) && $className = "scanned")
		$li_js .= "\n$className++;\n";
	else {
		if ($className == "errors")
			$li_js .= "\ndivx=document.getElementById('wait_$fileName64');\nif (divx) {\ndivx.src='$GOTMLS_images_path/blocked.gif';\ndirerrors++;\n}";
		if ($className == "dir")
			$li_js .= "\ndivx=document.getElementById('wait_$fileName64');\nif (divx)\ndivx.src='$GOTMLS_images_path/checked.gif';";
	}
	return $li_js;
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
	return "\nupdate_status('".str_replace("'", "\\'", $status)."', $microtime, '$memory_usage MB');";
}
$GOTMLS_scanfiles = array();
$_SERVER_REQUEST_URI = str_replace('&amp;','&', htmlspecialchars( $_SERVER['REQUEST_URI'] , ENT_QUOTES ) );
$GOTMLS_script_URI = $_SERVER_REQUEST_URI.(strpos($_SERVER_REQUEST_URI,'?')?'&':'?').'ts='.microtime(true);
function GOTMLS_readdir($dir, $current_depth = 0) {
	global $GOTMLS_loop_execution_time, $GOTMLS_scanfiles, $bad_backups, $GOTMLS_images_path, $GOTMLS_skip_dirs, $GOTMLS_skip_files, $total_files, $total_dirs, $file_at_depth, $current_files;
	$dirs = GOTMLS_explode_dir($dir, '.');
	set_time_limit($GOTMLS_loop_execution_time);
//	if (($current_depth==0 || !in_array($dirs[count($dirs)-1], $GOTMLS_skip_dirs)) && is_dir($dir)) {
		$li_js = '';
		$files = GOTMLS_getfiles($dir);
		if (is_array($files)) {
			$file_at_depth[$current_depth] = count($files);
			$current_files[$current_depth] = 0;
			$total_files += $file_at_depth[$current_depth];
			$GOTMLS_scanfiles[] = GOTMLS_encode($dir);
			echo "\n<script>".GOTMLS_return_threat('dirs', 'wait', $dir).GOTMLS_update_status("Preparing $dir")."\n</script>\n";
//			flush();
			foreach ($files as $file) {
				$path = str_replace('//', '/', $dir.'/'.$file);
				if ($current_depth == 0)
					$current_percent = floor(($current_files[$current_depth]++ / $file_at_depth[0]) * 100);
				if (is_dir($path)) {
					$total_dirs++;
					if (isset($_REQUEST['scan_depth']) && is_numeric($_REQUEST['scan_depth']) && ($_REQUEST['scan_depth'] != $current_depth) && !in_array($file, $GOTMLS_skip_dirs)) {
						$current_depth++;
						$current_depth = GOTMLS_readdir($path, $current_depth);
					} else
						$li_js .= GOTMLS_return_threat('skipdirs', 'blocked', $path);
				} elseif ($_REQUEST['scan_type'] == 'Quick Scan') {
					if (GOTMLS_get_ext($path) == 'bad' && !isset($_GET['eli'])) {
						$bad_backups++;
						if (isset($_GET['eli']) && $_GET['eli']=='recover') echo GOTMLS_recoverfile($path);
					} elseif (in_array(GOTMLS_get_ext($path), $GOTMLS_skip_files) || @filesize($path)>1234567 || @filesize($path)==0)
						$li_js .= GOTMLS_return_threat('skipped', 'blocked', $path);
					else
						$li_js .= GOTMLS_scanfile($path);
				}
			}
		} else
			$li_js = GOTMLS_return_threat('errors', 'blocked', $dir, GOTMLS_error_link('Failed to list files in directory!'));
		echo "\n<script>$li_js";
		if ($_REQUEST['scan_type'] == 'Quick Scan')
			echo GOTMLS_return_threat('dir', 'checked', $dir).GOTMLS_update_status("Scanned $dir");
		echo "\n</script>\n";
//		flush();
//	}
	set_time_limit($GOTMLS_loop_execution_time);
	$current_depth--;
	return $current_depth;
}
function GOTMLS_display_header($pTitle, $optional_box = '') {
	global $_SERVER_REQUEST_URI, $GOTMLS_plugin_dir, $GOTMLS_update_home, $GOTMLS_plugin_home, $GOTMLS_updated_images_path, $GOTMLS_images_path, $GOTMLS_definitions_version, $GOTMLS_Version, $wp_version, $current_user,$GOTMLS_updated_definition_path;
	get_currentuserinfo();
$_SESSION['GOTMLS_debug'][(microtime(true)-$_SESSION['GOTMLS_debug']['START_microtime']).' GOTMLS_display_header_start'] = GOTMLS_memory_usage(true);
	$GOTMLS_url = get_option('siteurl');
	$GOTMLS_url_parts = explode('/', $GOTMLS_url);
	$wait_img_URL = $GOTMLS_images_path.'wait.gif';
	if (isset($_GET['check_site']) && $_GET['check_site'] == 1)
		echo '<div id="check_site" style="float: right; margin: 15px;"><img src="'.$GOTMLS_images_path.'checked.gif"> Tested your site. It appears we didn\'t break anything ;-)</div><script>window.parent.document.getElementById("check_site_warning").style.backgroundColor=\'#0C0\';</script><style>#footer, #GOTMLS-Settings, #right-sidebar, #admin-page-container, #wpadminbar, #adminmenuback, #adminmenuwrap, #adminmenu {display: none !important;} #wpbody-content {padding-bottom: 0;}';
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
.rounded-corners {margin: 10px; border-radius: 10px; -moz-border-radius: 10px; -webkit-border-radius: 10px; border: 1px solid #000;}
.shadowed-box {box-shadow: -3px 3px 3px #666; -moz-box-shadow: -3px 3px 3px #666; -webkit-box-shadow: -3px 3px 3px #666;}
.sidebar-box {background-color: #CCC;}
.sidebar-links {padding: 0 15px; list-style: none;}
.popup-box {background-color: #FFC; display: none; position: absolute; left: 0px; z-index: 10;}
.shadowed-text {text-shadow: #00F -1px 1px 1px;}
.sub-option {float: left; margin: 3px 5px;}
.inside p {margin: 10px;}
.GOTMLS_li, .GOTMLS_plugin li {list-style: none;}
.GOTMLS_plugin {margin: 5px; background: #cfc; border: 1px solid #0f0; padding: 0 5px; border-radius: 3px;}
.GOTMLS_plugin.known, .GOTMLS_plugin.htaccess, .GOTMLS_plugin.timthumb, .GOTMLS_plugin.errors {background: #f99; border: 1px solid #f00;}
.GOTMLS_plugin.potential, .GOTMLS_plugin.bad, .GOTMLS_plugin.skipdirs, .GOTMLS_plugin.skipped {background: #ffc; border: 1px solid #fc6;}
.GOTMLS ul li {margin-left: 20px;}
.GOTMLS h2 {margin: 0 0 10px;}
.postbox {margin-right: 10px;}
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
			if (document.getElementById("autoUpdateDownload").src.replace(/^.+\?/,"")=="0") {
				alert(chk+"\\n\\nPlease make a donation for the use of this wonderful feature!");
				if ('.$GOTMLS_definitions_version.'0 > 12040000000) {
					window.open("http://gotmls.net/donate/?donation-key='.md5($GOTMLS_url).'&donation-source="+chk, "_blank");
				}
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
		<img style="display: none; float: right; margin-right: 14px;" src="'.$GOTMLS_images_path.'checked.gif" alt="definitions file updated" id="autoUpdateDownload" onclick="downloadUpdates(\'UpdateDownload\');">
		<div id="Definition_Updates" class="inside"><center>Searching for updates ...<br /><img src="'.$wait_img_URL.'" alt="Wait..." /><br /><input type="button" value="Cancel" onclick="document.getElementById(\'Definition_Updates\').innerHTML = \'Could not find server!\';" /></center></div>
		<div id="autoUpdateForm" style="display: none;" class="inside">
		<input type="submit" name="auto_update" onclick="check_for_updates(\'Downloaded Definitions\');" value="Download new definitions!"> 
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
			<div id="pastDonations"></div>
			<form name="ppdform" id="ppdform" action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_blank">
			<input type="hidden" name="cmd" value="_donations">
			<input type="hidden" name="business" value="donations@gotmls.net">
			<input type="hidden" name="no_shipping" value="1">
			<input type="hidden" name="no_note" value="1">
			<input type="hidden" name="currency_code" value="USD">
			<input type="hidden" name="tax" value="0">
			<input type="hidden" name="lc" value="US">
			<input type="hidden" name="bn" value="PP-DonationsBF">
			<input type="radio" name="amount" value="10.00">$10
			<input type="radio" name="amount" value="20.00">$20
			<input type="radio" name="amount" value="30.00" checked>$30
			<input type="radio" name="amount" value="40.00">$40
			<input type="radio" name="amount" value="50.00">$50
			<input type="hidden" name="item_name" value="Donation to Eli\'s Anti-Malware Plugin">
			<input type="hidden" name="item_number" value="GOTMLS-key-'.md5($GOTMLS_url).'">
			<input type="hidden" name="custom" value="key-'.md5($GOTMLS_url).'">
			<input type="hidden" name="notify_url" value="http://gotmls.net/?ipn">
			<input type="hidden" name="return" value="http://gotmls.net/donate/?paid='.md5($GOTMLS_url).'">
			<input type="hidden" name="cancel_return" value="http://gotmls.net/donate/?cancel='.md5($GOTMLS_url).'">
			<input type="image" id="pp_button" src="http://wordpress.ieonly.com/wp-content/uploads/btn_donateCC_WIDE.gif" border="0" name="submitc" alt="Make a Donation with PayPal">
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
$_SESSION['GOTMLS_debug'][(microtime(true)-$_SESSION['GOTMLS_debug']['START_microtime']).' GOTMLS_display_header_end'] = GOTMLS_memory_usage(true);
}
function GOTMLS_trim_ar(&$ar_item, $key) {
	$ar_item = trim($ar_item);
}
if (!function_exists('ur1encode')) { function ur1encode($url) {
	global $GOTMLS_encode;
	return preg_replace($GOTMLS_encode, '\'%\'.substr(\'00\'.strtoupper(dechex(ord(\'\0\'))),-2);', $url);
}}
function GOTMLS_settings() {
	global $GOTMLS_script_URI, $_SERVER_REQUEST_URI, $GOTMLS_scanfiles, $bad_backups, $GOTMLS_plugin_dir, $GOTMLS_images_path, $total_files, $total_dirs, $GOTMLS_skip_files, $GOTMLS_skip_dirs, $GOTMLS_known_threats, $GOTMLS_settings_array;
$_SESSION['GOTMLS_debug'][(microtime(true)-$_SESSION['GOTMLS_debug']['START_microtime']).' GOTMLS_Settings_start'] = GOTMLS_memory_usage(true);
	$noYesList = array('No', 'Yes');
	$GOTMLS_menu_groups = array('Main Menu Item placed below <b>Comments</b> and above <b>Appearance</b>','Main Menu Item placed below <b>Settings</b>','Sub-Menu inside the <b>Tools</b> Menu Item');
	$GOTMLS_scan_groups = array();
	$dirs = GOTMLS_explode_dir(__file__);
	$scan_level = intval($GOTMLS_settings_array['scan_level']);
	for ($SL=0;$SL<$scan_level;$SL++)
		$GOTMLS_scan_groups[] = '<b>'.implode('/', array_slice($dirs, -1 * (3 + $SL), 1)).'</b>';
	if (!isset($GOTMLS_settings_array['scan_what']))
		$GOTMLS_settings_array['scan_what'] = 2;
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
		$GOTMLS_settings_array['exclude_ext'] = $GOTMLS_skip_files;
	if (!isset($GOTMLS_settings_array['check_custom']))
		$GOTMLS_settings_array['check_custom'] = '';
	if (isset($_POST['exclude_ext']) && strlen(trim($_POST['exclude_ext'].' ')) >0) {
		$GOTMLS_settings_array['exclude_ext'] = preg_split("/[,]+/", trim($_POST['exclude_ext']), -1, PREG_SPLIT_NO_EMPTY);
		array_walk($GOTMLS_settings_array['exclude_ext'], 'GOTMLS_trim_ar');
	}
	if (isset($_GET['eli']) && $_GET['eli']=='bad')
		$GOTMLS_skip_files = $GOTMLS_settings_array['exclude_ext'];
	else
		$GOTMLS_skip_files = array_merge($GOTMLS_settings_array['exclude_ext'], array('bad'));
	if (!(isset($GOTMLS_settings_array['exclude_dir']) && is_array($GOTMLS_settings_array['exclude_dir'])))
		$GOTMLS_settings_array['exclude_dir'] = array();
	if (isset($_POST['exclude_dir'])) {
		if (strlen(trim(str_replace(',','',$_POST['exclude_dir']).' ')) > 0)
			$GOTMLS_settings_array['exclude_dir'] = preg_split("/[\s]*([,]+[\s]*)+/", trim($_POST['exclude_dir']), -1, PREG_SPLIT_NO_EMPTY);
		else
			$GOTMLS_settings_array['exclude_dir'] = array();
	}
	$GOTMLS_skip_dirs = array_merge($GOTMLS_settings_array['exclude_dir'], $GOTMLS_skip_dirs);
	if (isset($_POST['scan_what']) && is_numeric($_POST['scan_what']) && $_POST['scan_what'] != $GOTMLS_settings_array['scan_what'])
		$GOTMLS_settings_array['scan_what'] = $_POST['scan_what'];
//	else echo '<li>POST != $GOTMLS_settings_array('.$GOTMLS_settings_array['scan_what'].')='.($_POST['scan_what'] != $GOTMLS_settings_array['scan_what']);
	if (isset($_POST['check_custom']) && $_POST['check_custom'] != $GOTMLS_settings_array['check_custom']) {
		$_POST['check_custom'] = stripslashes($_POST['check_custom']);
		$GOTMLS_settings_array['check_custom'] = ($_POST['check_custom']);
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
	$scan_opts = '><form method="POST" name="GOTMLS_Form"><p><input type="hidden" name="scan_type" id="scan_type" value="Quick Scan" /><b>What to scan:</b>';
	$scan_optjs = "<script>\nfunction showOnly(what) {\n";
	foreach ($GOTMLS_scan_groups as $mg => $GOTMLS_scan_group) {
		$scan_optjs .= "document.getElementById('only$mg').style.display = 'none';\n";
		$scan_opts .= '<div style="position: relative; float: left; padding: 4px 14px;" id="scan_group_div_'.$mg.'"><input type="radio" name="scan_what" id="not-only'.$mg.'" value="'.$mg.'"'.($GOTMLS_settings_array['scan_what']==$mg?' checked':'').' /><a style="text-decoration: none;" href="#scan_what" onclick="showOnly(\''.$mg.'\');document.getElementById(\'not-only'.$mg.'\').checked=true;">'.$GOTMLS_scan_group.'</a><br /><div class="rounded-corners" style="position: absolute; display: none; background-color: #CCCCFF; padding: 10px; z-index: 10;" id="only'.$mg.'"><a class="rounded-corners" style="float: right; padding: 0 4px; margin: 0 0 0 30px; text-decoration: none; color: #CC0000; background-color: #FFCCCC; border: solid #FF0000 1px;" href="#scan_what" onclick="showhide(\'only'.$mg.'\');">X</a><b>Only&nbsp;Scan&nbsp;These&nbsp;Folders:</b>';
		$dir = implode('/', array_slice($dirs, 0, -1 * (2 + $mg)));
		$files = GOTMLS_getfiles($dir);
		if (is_array($files))
			foreach ($files as $file)
				if (is_dir($dir.'/'.$file))
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
			$scan_opts .= '<div style="float: right; padding: 14px;" id="check_known_div_'.$nY.'"><input type="radio" name="check_known" id="check_known_'.$noYes.'" value="'.$nY.'"'.($GOTMLS_settings_array['check_known']==$nY?' checked':'').' />'.$noYes.'</div>';
	} else
		$scan_opts .= '<div style="float: right; padding: 14px;" id="check_known_div_NA">Registration of your Installation Key is required for this feature</div>';
	if (isset($_GET['eli'])) {
		$scan_opts .= 'Check for <a style="text-decoration: none;" href="#check_known_div_0" onclick="showhide(\'check_only_known\', true);document.getElementById(\'check_known_Yes\').checked=true;">known threats</a> (malicious scripts that use eval&#40;&#41; and similar techniques to infect your server):<br /><div class="rounded-corners" style="position: absolute; display: none; background-color: #CCCCFF; padding: 10px; z-index: 10;" id="check_only_known"><a class="rounded-corners" style="float: right; padding: 0 4px; margin: 0 0 0 30px; text-decoration: none; color: #CC0000; background-color: #FFCCCC; border: solid #FF0000 1px;" href="#check_known_div_0" onclick="showhide(\'check_only_known\');">X</a><b>Only&nbsp;Scan&nbsp;for&nbsp;These&nbsp;Threats:</b>';
		foreach ($GOTMLS_known_threats['known'] as $threat => $pattern)
			$scan_opts .= '<br /><input type="checkbox" name="check_only[known][]" value="'.$threat.'" checked />'.$threat;
		$scan_opts .= '</div>';
	} else
		$scan_opts .= 'Check for known threats (malicious scripts that use eval&#40;&#41; and similar techniques to infect your server):';
	$scan_opts .= '</div><br style="clear: left;" /><hr style="clear: left; color: #cccccc; background-color: #cccccc;" /><div style="float: left; padding: 0; width: 100%;" id="check_potential_div">';
	foreach ($noYesList as $nY => $noYes)
		$scan_opts .= '<div style="float: right; padding: 14px;" id="check_potential_div_'.$nY.'"><input type="radio" name="check_potential" value="'.$nY.'"'.($GOTMLS_settings_array['check_potential']==$nY?' checked':'').' />'.$noYes.'</div>';
	$scan_opts .= 'Check for potential threats (This option just looks for the usage of eval&#40;&#41;. It is usually not a threat but it could be. This helps you examine each file to see for yourself if you think it is dangerous or malicious. If you have reason to believe there is a threat hear you should have it examined by an expert.):</div><br style="clear: left;" /><h3>What to skip:</h3><p><b>Skip files with the following extentions:</b>(a comma separated list of file extentions to be excluded from the scan)<br /><input type="text" name="exclude_ext" value="'.implode($GOTMLS_settings_array['exclude_ext'], ',').'" style="width: 90%;" /></p><p><b>Skip directories with the following names:</b>(a comma separated list of folders to be excluded from the scan)<br /><input type="text" name="exclude_dir" value="'.implode($GOTMLS_settings_array['exclude_dir'], ',').'" style="width: 90%;" /></p>';
	if (isset($_GET['eli'])) $scan_opts .= '<p><b>Custom code search:</b>(a reg_exp string to be searched for, this is for very advanced users. Please do not use this without talking to Eli first. If used incorrectly you could break your entire site.)<br /><input type="text" name="check_custom" style="width: 90%;" value="'.str_replace('"','&quot;',$GOTMLS_settings_array['check_custom']).'" /></p>';//still testing this option
	$scan_opts .= '</p><p style="text-align: right;"><input type="submit" onclick="document.getElementById(\'scan_type\').value=this.value;" id="complete_scan" value="Complete Scan" class="button-primary" />&nbsp;<input type="submit" onclick="document.getElementById(\'scan_type\').value=this.value;" id="quick_scan" value="Quick Scan" class="button-primary" /></p></form></div></div>';
	$menu_opts = '<div class="stuffbox shadowed-box">
		<h3 class="hndle"><span>Menu Item Placement Options</span></h3>
		<div class="inside"><form method="POST" name="GOTMLS_menu_Form">';
	foreach ($GOTMLS_menu_groups as $mg => $GOTMLS_menu_group)
		$menu_opts .= '<div style="padding: 4px;" id="menu_group_div_'.$mg.'"><input type="radio" name="GOTMLS_menu_group" value="'.$mg.'"'.($GOTMLS_settings_array['menu_group']==$mg?' checked':'').' onchange="document.GOTMLS_menu_Form.submit();" />'.$GOTMLS_menu_group.'</div>';
	GOTMLS_display_header('Anti-Malware by <img style="vertical-align: middle;" alt="ELI" src="http://0.gravatar.com/avatar/8151cac22b3fc543d099241fd573d176?s=64&r=G" /> at GOTMLS.NET', $menu_opts.'</form><br style="clear: left;" /></div></div>');
	$scan_groups = array('Scanned Files'=>'scanned','Skipped Files'=>'skipped','Skipped Folders'=>'skipdirs','Scanned Folders'=>'dir','Read Folders'=>'dirs','BAD Backup Files'=>'bad','TimThumb Exploits'=>'timthumb','.htaccess Threats'=>'htaccess','Known Threats'=>'known','Potential Threats'=>'potential','Read/Write Errors'=>'errors');
	echo '<script>
function update_status(title, time) {
	sdir = (dir+direrrors);
	percent = Math.floor((sdir*100)/dirs);
	scan_state = "6F6";
	if (percent == 100)
		title = "<b>Scan Complete!</b>";
	else
		scan_state = "99F";
	timeRemaining = Math.ceil(time*(dirs/sdir)-time);
	if (timeRemaining > 59)
		timeRemaining = Math.ceil(timeRemaining/60)+" Minute";
	else
		timeRemaining += " Second";
	if (timeRemaining.substr(0, 2) != "1 ")
		timeRemaining += "s";
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
	divHTML = \'<ul style="float: right; margin: 0 20px; text-align: right;">\';';
	$MAX = 0;
	$vars = "var i, intrvl, direrrors=0";
	$fix_button_js = "";
	$found = "";
	$li_js = "return false;";
	foreach ($scan_groups as $scan_name => $scan_group) {
		$vars .= ", $scan_group=0";
		if ($MAX++ == 5) {
			$found = "Found ";
			$fix_button_js = "\n\t\tdis='block';";
			echo "\n\tdivHTML += '</ul><ul style=\"text-align: left;\">'";
		} else
			echo "\n\tif ($scan_group > 0) {\n\t\tscan_state = ' href=\"#found_$scan_group\" onclick=\"$li_js showhide(\\'found_$scan_group\\', true);\" class=\"GOTMLS_plugin $scan_group\"';$fix_button_js\n\tif ('$found'.length > 0)\n\t\tshowhide('found_$scan_group', true);\n\t} else\n\t\tscan_state = ' class=\"GOTMLS_plugin\"';\n\tdivHTML += '<li class=\"GOTMLS_li\"><a'+scan_state+'>$found'+$scan_group+' '+($scan_group==1?('$scan_name').slice(0,-1):'$scan_name')+'</a></li>';";
		$li_js = "";
		if ($MAX > 8)
			$fix_button_js = "";
		if ($MAX > 9)
			$found = "";
	}
	echo '
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
</script>
<div class="metabox-holder GOTMLS" style="width: 100%;" id="GOTMLS-Settings"><div class="postbox shadowed-box">
	<div title="Click to toggle" onclick="showhide(\'GOTMLS-Settings-Form\');" class="handlediv"><br></div>
	<h3 title="Click to toggle" onclick="showhide(\'GOTMLS-Settings-Form\');" style="cursor: pointer;" class="hndle"><span>Scan Settings</span></h3>
	<div id="GOTMLS-Settings-Form" class="inside"';
	if (isset($_REQUEST['scan_what']) && is_numeric($_REQUEST['scan_what'])) {
		if (!isset($_REQUEST['scan_type']))
			$_REQUEST['scan_type'] = 'Quick Scan';
		echo ' style="display: none;"'.$scan_opts;
		update_option($GOTMLS_plugin_dir.'_settings_array', $GOTMLS_settings_array);
		echo '<form method="POST" target="GOTMLS_iFrame" name="GOTMLS_Form_clean"><input type="hidden" name="GOTMLS_fixing" value="1"><div class="postbox shadowed-box"><div title="Click to toggle" onclick="showhide(\'GOTMLS-Scan-Progress\');" class="handlediv"><br></div><h3 title="Click to toggle" onclick="showhide(\'GOTMLS-Scan-Progress\');" style="cursor: pointer;" class="hndle"><span>Scan Progress</span></h3><div id="GOTMLS-Scan-Progress" class="inside">';
		foreach ($_POST as $name => $value) {
			if (substr($name, 0, 10) != 'GOTMLS_fix') {
				if (is_array($value)) {
					foreach ($value as $val)
						echo '<input type="hidden" name="'.$name.'[]" value="'.$val.'">';
				} else
					echo '<input type="hidden" name="'.$name.'" value="'.$value.'">';
			}
		}
		echo '<div id="status_text"></div><div id="status_bar"></div><div id="status_counts"></div><p id="fix_button" style="display: none; text-align: center;"><input id="repair_button" type="submit" value="Automatically Repair SELECTED files Now" class="button-primary" onclick="showhide(\'GOTMLS_iFrame\', true);showhide(\'GOTMLS_iFrame\');showhide(\'div_file\', true);" /></p></div></div>
		<div class="postbox shadowed-box"><div title="Click to toggle" onclick="showhide(\'GOTMLS-Scan-Details\');" class="handlediv"><br></div><h3 title="Click to toggle" onclick="showhide(\'GOTMLS-Scan-Details\');" style="cursor: pointer;" class="hndle"><span>Scan Details:</span></h3><div id="GOTMLS-Scan-Details" class="inside"><div onmousedown="grabDiv();" onmouseup="releaseDiv();" id="div_file" class="shadowed-box rounded-corners sidebar-box" style="display: none; position: fixed; top: 100px; left: 100px; width: 80%; border: solid #c00; z-index: 112358;"><a class="rounded-corners" name="link_file" style="float: right; padding: 0 4px; margin: 6px; text-decoration: none; color: #C00; background-color: #FCC; border: solid #F00 1px;" href="#found_top" onclick="showhide(\'div_file\');">X</a><h3 style="border-radius: 10px 10px 0 0; -moz-border-radius: 10px 10px 0 0; -webkit-border-radius: 10px 10px 0 0;">Examine Results</h3><div style="width: 100%; height: 400px; position: relative; padding: 0; margin: 0;" class="inside"><br /><br /><center><img src="'.$GOTMLS_images_path.'wait.gif"> Loading Results, Please Wait ...<br /><br /><input type="button" onclick="showhide(\'GOTMLS_iFrame\', true);" value="It\'s taking too long ... I can\'t wait ... show me the results!" class="button-primary" /></center><iframe id="GOTMLS_iFrame" name="GOTMLS_iFrame" style="top: 0px; left: 0px; width: 100%; height: 400px; background-color: #CCC; position: absolute;"></iframe></div></div><script>setDiv("div_file");</script>';
		if ($_REQUEST['scan_what'] > -1) {
			$dir = implode('/', array_slice($dirs, 0, -1 * (2 + $_REQUEST['scan_what'])));
			foreach ($scan_groups as $scan_name => $scan_group)
				echo "\n<ul name=\"found_$scan_group\" id=\"found_$scan_group\" class=\"GOTMLS_plugin $scan_group\" style=\"background-color: #ccc; display: none; padding: 0;\"><a class=\"rounded-corners\" name=\"link_$scan_group\" style=\"float: right; padding: 0 4px; margin: 5px 5px 0 30px; text-decoration: none; color: #C00; background-color: #FCC; border: solid #F00 1px;\" href=\"#found_top\" onclick=\"showhide('found_$scan_group');\">X</a><h3>$scan_name</h3><br /></ul>";
			update_option('GOTMLS_LAST_scan_start', microtime(true));
			if (is_dir($dir)) {
				if (isset($_POST['scan_only']) && is_array($_POST['scan_only'])) {
					foreach ($_POST['scan_only'] as $only_dir)
						if (is_dir($dir.'/'.$only_dir))
							GOTMLS_readdir($dir.'/'.$only_dir);
				} else
					GOTMLS_readdir($dir);
			} else
				echo "<li>Failed: $dir is not a directory!</li>";
			if ($_REQUEST['scan_type'] == 'Quick Scan')
				echo "\n<script>".GOTMLS_update_status('Completed!')."\n</script>";
			else {
				echo '
<script>'.GOTMLS_update_status('Starting Scan ...').'
var scriptSRC = "'.$GOTMLS_script_URI.'&GOTMLS_scan=";
var scanfilesAr = new Array("'.implode('","', $GOTMLS_scanfiles).'");
var scanfilesI = 0;
var stopScanning;
var gotStuckOn = "";
function scanNextDir(gotStuck) {
	if (gotStuck)
		gotStuckOn = document.getElementById("status_text").innerHTML+"&split="+gotStuck+"&GOTMLS_skip_file[]=";
	clearTimeout(stopScanning);
	if (scanfilesI < scanfilesAr.length) {
		var newscript = document.createElement("script");
		newscript.setAttribute("src", scriptSRC+scanfilesAr[scanfilesI++]);
		divx = document.getElementById("found_scanned");
		if (divx)
			divx.appendChild(newscript);
		if (scanfilesI < scanfilesAr.length)
			stopScanning=setTimeout("scanNextDir(\'"+scriptSRC+scanfilesAr[scanfilesI-1]+"\')",60000);
		else if (gotStuckOn)
			stopScanning=setTimeout("scanNextDir(false)",1000);
	} else if (gotStuckOn.substr(0, 9) == "Checking ") {
		gotStuckSplit = (gotStuckOn+"&split=").split("&split=");
		document.getElementById("status_text").innerHTML = "Re-"+gotStuckSplit[0];
		var newscript = document.createElement("script");
		newscript.setAttribute("src", gotStuckSplit[1]+encodeURIComponent(gotStuckSplit[0].substr(9)));
		divx = document.getElementById("found_scanned");
		if (divx)
			divx.appendChild(newscript);
	}
}
stopScanning=setTimeout("scanNextDir(false)",3000);
</script>';
			}
		}
		echo '</div></div></form>';
	} else {
		echo $scan_opts;
	}
	echo '</div></div></div><script>setDivNAtext();</script>';
$_SESSION['GOTMLS_debug'][(microtime(true)-$_SESSION['GOTMLS_debug']['START_microtime']).' GOTMLS_Settings_end'] = GOTMLS_memory_usage(true);
}
function GOTMLS_set_plugin_action_links($links_array, $plugin_file) {
	if ($plugin_file == substr(__file__, (-1 * strlen($plugin_file)))) {
		$_SESSION['GOTMLS_debug'][(microtime(true)-$_SESSION['GOTMLS_debug']['START_microtime']).' GOTMLS_set_plugin_action_links'] = GOTMLS_memory_usage(true);
		$GOTMLS_settings_array = get_option('GOTMLS_settings_array');
		if ($GOTMLS_settings_array['menu_group'] == 2)
			$base_page = 'tools.php';
		else
			$base_page = 'admin.php';
		$links_array = array_merge(array('<a href="'.$base_page.'?page=GOTMLS-settings&scan_type=Quick+Scan">'.__( 'Quick Scan' ).'</a>', '<a href="'.$base_page.'?page=GOTMLS-settings">'.__( 'Settings' ).'</a>'), $links_array);
	}
	return $links_array;
}
function GOTMLS_set_plugin_row_meta($links_array, $plugin_file) {
	if ($plugin_file == substr(__file__, (-1 * strlen($plugin_file)))) {
		$_SESSION['GOTMLS_debug'][(microtime(true)-$_SESSION['GOTMLS_debug']['START_microtime']).' GOTMLS_set_plugin_row_meta'] = GOTMLS_memory_usage(true);
		$links_array = array_merge($links_array, array('<a target="_blank" href="http://gotmls.net/faqs/">'.__( 'FAQ' ).'</a>','<a target="_blank" href="http://gotmls.net/support/">'.__( 'Support' ).'</a>','<a target="_blank" href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=QZHD8QHZ2E7PE">'.__( 'Donate' ).'</a>'));
	}
	return $links_array;
}
function GOTMLS_stripslashes(&$item, $key) {
	$item = stripslashes($item);
}
function GOTMLS_error_link($error) {
	return "<a title=\"$error\" onclick=\"return false;\" class=\"GOTMLS_plugin errors\">";
}
function GOTMLS_scandir($dir) {
	global $GOTMLS_skip_files;
	$li_js = "\nscanNextDir();";
	echo GOTMLS_update_status("Scanning $dir");
	$files = GOTMLS_getfiles($dir);
	if (is_array($files)) {
		foreach ($files as $file) {
			$path = str_replace('//', '/', $dir.'/'.$file);
			if (is_file($path)) {
				if (isset($_GET['GOTMLS_skip_file']) && is_array($_GET['GOTMLS_skip_file']) && in_array($path, $_GET['GOTMLS_skip_file'])) {
					$li_js = "\n//$path;\n";
					if ($path == $_GET['GOTMLS_skip_file'][count($_GET['GOTMLS_skip_file'])-1])
						echo GOTMLS_return_threat('errors', 'blocked', $path, GOTMLS_error_link('Failed to read file!'));
				} else {
					echo "\ndocument.getElementById('status_text').innerHTML='Checking ".str_replace("'", "'+\"'\"+'", $path)."';";
					if (GOTMLS_get_ext($path) == 'bad' && !isset($_GET['eli']))
						echo GOTMLS_return_threat('bad', 'blocked', $path);
					elseif (in_array(GOTMLS_get_ext($path), $GOTMLS_skip_files) || (@filesize($path)==0) || (@filesize($path)>1234567))
						echo GOTMLS_return_threat('skipped', 'blocked', $path);
					elseif (@filesize($path)===false)
						echo GOTMLS_return_threat('errors', 'blocked', $path, GOTMLS_error_link('Failed to deturmine file size!'));
					else
						echo GOTMLS_scanfile($path);
				}
			}
		}
		echo GOTMLS_return_threat('dir', 'checked', $dir);
	} else
		echo GOTMLS_return_threat('errors', 'blocked', $dir, GOTMLS_error_link('Failed to list files in directory!'));
	echo GOTMLS_update_status("Scanned $dir")."\n";
	return $li_js;
}
if (is_admin() && isset($_GET['GOTMLS_scan']) && file_exists(GOTMLS_decode($_GET['GOTMLS_scan'])) && is_dir(GOTMLS_decode($_GET['GOTMLS_scan'])))
	header('Content-type: text/javascript');
$GOTMLS_settings_array = array();
function GOTMLS_init() {
	global $GOTMLS_threats_found, $GOTMLS_settings_array, $GOTMLS_definitions_version, $GOTMLS_known_threats, $GOTMLS_plugin_dir, $GOTMLS_local_images_path, $GOTMLS_images_path, $GOTMLS_file_contents, $GOTMLS_script_URI, $GOTMLS_skip_files, $GOTMLS_Version, $wp_version;
$_SESSION['GOTMLS_debug'][(microtime(true)-$_SESSION['GOTMLS_debug']['START_microtime']).' GOTMLS_init_start'] = GOTMLS_memory_usage(true);
	$GOTMLS_settings_array = get_option($GOTMLS_plugin_dir.'_settings_array');
	if (!isset($GOTMLS_settings_array['scan_what']))
		$GOTMLS_settings_array['scan_what'] = 2;
	if (!isset($GOTMLS_settings_array['scan_depth']))
		$GOTMLS_settings_array['scan_depth'] = -1;
	if (isset($_REQUEST['scan_type']) && $_REQUEST['scan_type'] == 'Quick Scan') {
		if (!isset($_REQUEST['scan_what']))
			$_REQUEST['scan_what'] = $GOTMLS_settings_array['scan_what'];
		if (!isset($_REQUEST['scan_depth']))
			$_REQUEST['scan_depth'] = $GOTMLS_settings_array['scan_depth'];
	}
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
	if (isset($_POST['GOTMLS_fix']) && !is_array($_POST['GOTMLS_fix']))
		$_POST['GOTMLS_fix'] = array($_POST['GOTMLS_fix']=>1);
	if (isset($_POST['GOTMLS_fix']) && is_array($_POST['GOTMLS_fix'])) {
		$li_js = "<script>\nfilesFixed=0;\nfunction fixedFile(file) {\nfilesFixed++;\nwindow.parent.document.getElementById('list_'+file).className='GOTMLS_plugin';\nwindow.parent.document.getElementById('check_'+file).checked=false;\n}";
		foreach ($_POST['GOTMLS_fix'] as $path => $val) {
			if (file_exists(GOTMLS_decode($path)) && $val) {
				echo '<li>fixing '.GOTMLS_decode($path).' ...';
				$li_js .= GOTMLS_scanfile(GOTMLS_decode($path));
				echo '</li>';
			}
		}
		die("$li_js\nwindow.parent.showhide('GOTMLS_iFrame', true);\nwindow.parent.check_for_donation('Repaired '+filesFixed+' files');\n</script>\n".'<div id="check_site_warning" style="background-color: #F00;">Because some threats were automatically fixed we need to check to make sure the removal did not break your site. If this stays Red and the frame below does not load please <a target="test_frame" href="'.$GOTMLS_images_path.'index.php?scan_what='.$_REQUEST['scan_what'].'&scan_depth='.$_REQUEST['scan_depth'].'">revert the changes</a> made during the automated repair process. <span style="color: #F00;">Never mind, it worked!</span></div><br /><iframe id="test_frame" name="test_frame" src="'.$GOTMLS_images_path.'index.php?check_site=1&ts='.date("YmdHis").'" style="width: 100%; height: 200px"></iframe>');
	} elseif (isset($_GET['GOTMLS_scan'])) {
		$file = GOTMLS_decode($_GET['GOTMLS_scan']);
		if (file_exists($file) && is_dir($file)) {
//			foreach ($_GET as $name => $value) $_POST[$name] = $value;
			if (isset($GOTMLS_settings_array['exclude_ext']) && is_array($GOTMLS_settings_array['exclude_ext']))
				$GOTMLS_skip_files = $GOTMLS_settings_array['exclude_ext'];
			die(GOTMLS_scandir($file));
		} else {
			if (!file_exists($file))
				die("\nThe file $file does not exist.<br />\n".(GOTMLS_get_ext($file)!='bad'?'You could <a target="GOTMLS_iFrame" href="'.$GOTMLS_script_URI.'&GOTMLS_scan='.GOTMLS_encode($file.'.bad').'">try viewing the backup file</a>.':'The file must have already been delete.'));
			else {
				GOTMLS_scanfile($file);
				$f = 0;
				$fpos = $f;
				$flen = $f;
				$fa = '';
				while (isset($GOTMLS_threats_found[0]) && is_array($GOTMLS_threats_found[0]) && $f < count($GOTMLS_threats_found[0])) {
					$potential_threat = str_replace("\r", "", $GOTMLS_threats_found[0][$f++]);
					if (($fpos = strpos(str_replace("\r", "", $GOTMLS_file_contents), ($potential_threat), $flen + $fpos)) !== false) {
						$flen = strlen($potential_threat);
						$fa .= ' <a href="javascript:select_text_range(\'ta_file\', '.($fpos).', '.($fpos + $flen).');">'.$f.'</a>';
					} else $fa .= ' '.$f.'{'.($fpos).', '.($fpos + $flen).'} ['.strlen($potential_threat).', '.strlen(str_replace("\r", "", $GOTMLS_file_contents)).']';
				}
				die("\n".'<script>
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
</script>'.$file.'<br style="clear: left;"/>Potential threats in file: ('.$fa.' )<br /><textarea id="ta_file" width="100%" style="width: 100%;" rows="20">'.htmlentities(str_replace("\r", "", $GOTMLS_file_contents)).'</textarea><form method="POST" name="GOTMLS_new_file_Form"><imput type="hidden" name="infected_file" value="'.$file.'"><input type="hidden" willbe="submit" value="Save new file over infected file"></form>');
			}
		}
	} elseif (isset($_POST['GOTMLS_fixing']))
		die("<script>\nwindow.parent.showhide('GOTMLS_iFrame', true);\nalert('Nothing Selected to be Fixed!');\n</script>Done!");
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
$_SESSION['GOTMLS_debug'][(microtime(true)-$_SESSION['GOTMLS_debug']['START_microtime']).' GOTMLS_init_end'] = GOTMLS_memory_usage(true);
}
add_filter('plugin_row_meta', $GOTMLS_plugin_dir.'_set_plugin_row_meta', 1, 2);
add_filter('plugin_action_links', $GOTMLS_plugin_dir.'_set_plugin_action_links', 1, 2);
GOTMLS_set_global($GOTMLS_default_ext, 'com');
GOTMLS_set_global($GOTMLS_encode, substr($GOTMLS_default_ext, 0, 2));
$GOTMLS_plugin_home = "http://wordpress.$GOTMLS_default_ext/";
$GOTMLS_update_home = 'http://gotmls.net/';
$GOTMLS_images_path = plugins_url('/images/', __FILE__);
$GOTMLS_url_parts = explode('/', $GOTMLS_images_path.'/../.');
$GOTMLS_local_images_path = dirname(__FILE__).'/images/';
$GOTMLS_updated_images_path = 'wp-content/plugins/update/images/';
$GOTMLS_updated_definition_path = 'wp-content/plugins/update/definitions/';
$GOTMLS_definitions_version = 1204000000;
$GOTMLS_known_threats = array(
	'potential' => array(
		'preg_replace /e' => '/preg_replace[ \t]*\([^\)]+[\/\#\|]e[\'"].+\)/i',
		'eval' => "/[^a-z\/'\"]eval\(.+\)/i"));
register_activation_hook(__FILE__,$GOTMLS_plugin_dir.'_install');
add_action('admin_menu', $GOTMLS_plugin_dir.'_menu');
$init = add_action('admin_init', $GOTMLS_plugin_dir.'_init');
$_SESSION['GOTMLS_debug']['START_memory_usage'] = GOTMLS_memory_usage(true);
?>