<?php
/**
 * GOTMLS Plugin Global Variables and Functions
 * @package GOTMLS
*/

define("GOTMLS_local_images_path", dirname(__FILE__)."/");

if ((isset($_SERVER["SCRIPT_FILENAME"]) && substr(__FILE__, -1 * strlen($_SERVER["SCRIPT_FILENAME"])) == substr($_SERVER["SCRIPT_FILENAME"], -1 * strlen(__FILE__))) || !defined("GOTMLS_plugin_path")) {
	header("Content-type: image/gif");
	$img_src = GOTMLS_local_images_path.'GOTMLS-16x16.gif';
	if (!(file_exists($img_src) && $img_bin = @file_get_contents($img_src)))
		$img_bin = base64_decode('R0lGODlhEAAQAIABAAAAAP///yH5BAEAAAEALAAAAAAQABAAAAIshB0Qm+eo2HuJNWdrjlFm3S2hKB7kViKaxZmr98YgSo/jzH6tiU0974MADwUAOw==');
	die($img_bin);
} elseif (isset($_GET["no_error_reporting"]))
	@error_reporting(0);

define("GOTMLS_plugin_dir", "gotmls");
define("GOTMLS_Version", "4.14.52");
define("GOTMLS_require_version", "3.0");
define("GOTMLS_Failed_to_list_LANGUAGE", __("Failed to list files in directory!",'gotmls'));
define("GOTMLS_Run_Complete_Scan_LANGUAGE", __("Run Complete Scan",'gotmls'));
define("GOTMLS_Run_Quick_Scan_LANGUAGE", __("Run Quick Scan",'gotmls'));
define("GOTMLS_View_Quarantine_LANGUAGE", __("View Quarantine",'gotmls'));
define("GOTMLS_Tested_your_site_LANGUAGE", __("Tested your site. It appears we didn't break anything",'gotmls'));
define("GOTMLS_require_version_LANGUAGE", sprintf(__("This Plugin requires WordPress version %s or higher",'gotmls'), GOTMLS_require_version));
define("GOTMLS_Scan_Settings_LANGUAGE", __("Scan Settings",'gotmls'));
define("GOTMLS_Loading_LANGUAGE", __("Loading, Please Wait ...",'gotmls'));
define("GOTMLS_too_long_LANGUAGE", __("If this is taking too long, click here.",'gotmls'));
define("GOTMLS_Could_not_find_server_LANGUAGE", __("Could not find server!",'gotmls'));
define("GOTMLS_Plugin_Updates_LANGUAGE", __("Plugin Updates for WP",'gotmls'));
define("GOTMLS_Searching_updates_LANGUAGE", __("Searching for updates ...",'gotmls'));
define("GOTMLS_Definition_Updates_LANGUAGE", __("Definition Updates",'gotmls'));
define("GOTMLS_Please_donate_LANGUAGE", __("Please make a donation for the use of this wonderful feature!",'gotmls'));
define("GOTMLS_Automatically_Fix_LANGUAGE", __("Automatically Fix SELECTED Files Now",'gotmls'));
define("GOTMLS_Scan_Details_LANGUAGE", __("Scan Details:",'gotmls'));
define("GOTMLS_Last_Scan_Status_LANGUAGE", __("Scan Status",'gotmls'));
define("GOTMLS_update_images_path", "/wp-content/plugins/update/images/");
define("GOTMLS_siteurl", get_option("siteurl"));
define("GOTMLS_images_path", plugins_url('/', __FILE__));
define("GOTMLS_installation_key", md5(GOTMLS_siteurl));

$GLOBALS["GOTMLS"] = array("tmp"=>array("mt"=>((isset($_GET["mt"])&&is_numeric($_GET["mt"]))?$_GET["mt"]:microtime(true)), "default_ext"=>"ieonly.", "skip_ext"=>array("png", "jpg", "jpeg", "gif", "bmp", "tif", "tiff", "psd", "fla", "flv", "mov", "mp3", "exe", "zip", "pdf", "css", "pot", "po", "mo", "so", "doc", "docx", "svg", "ttf")));
define("GOTMLS_script_URI", preg_replace('/\&(last_)?mt=[0-9\.]+/','', str_replace('&amp;', '&', htmlspecialchars($_SERVER["REQUEST_URI"], ENT_QUOTES))).'&mt='.$GLOBALS["GOTMLS"]["tmp"]["mt"]);
$GLOBALS["GOTMLS"]["log"] = get_option('GOTMLS_scan_log/'.(isset($_SERVER["REMOTE_ADDR"])?$_SERVER["REMOTE_ADDR"]:"0.0.0.0").'/'.$GLOBALS["GOTMLS"]["tmp"]["mt"], array());
$GOTMLS_loop_execution_time = 60;
$GOTMLS_chmod_file = (0644);
$GOTMLS_chmod_dir = (0755);
$GOTMLS_file_contents = "";
$GOTMLS_new_contents = "";
$GOTMLS_onLoad = "";
$GOTMLS_encode = '/[\?\-a-z\: \.\=\/A-Z\&\_]/';
$GOTMLS_threat_files = array("htaccess"=>".htaccess","timthumb"=>"thumb.php");
$GOTMLS_core_files = array("wp_login"=>"/wp-login.php");
$GOTMLS_threat_levels = array(__("htaccess Threats",'gotmls')=>"htaccess",__("TimThumb Exploits",'gotmls')=>"timthumb",__("Backdoor Scripts",'gotmls')=>"backdoor",__("Known Threats",'gotmls')=>"known",__("WP-Login Updates",'gotmls')=>"wp_login",__("Potential Threats",'gotmls')=>"potential");
$GOTMLS_image_alt = array("wait"=>"...", "checked"=>"&#x2714;", "blocked"=>"X", "question"=>"?", "threat"=>"!");
$GOTMLS_threats_found = array();
$GOTMLS_dir_at_depth = array();
$GOTMLS_dirs_at_depth = array();
$GOTMLS_scanfiles = array();
$GOTMLS_skip_dirs = array(".", "..");
$GOTMLS_settings_array = get_option('GOTMLS_settings_array', array());
if (isset($_GET['img']) && substr(strtolower($_SERVER["SCRIPT_FILENAME"]), -15) == "/admin-ajax.php" && !in_array(GOTMLS_get_ext($_GET['img']), $GLOBALS["GOTMLS"]["tmp"]["skip_ext"]))
	include(dirname(__FILE__)."/../safe-load/index.php");
if (!(isset($GOTMLS_settings_array["msg_position"]) && is_array($GOTMLS_settings_array["msg_position"]) && count($GOTMLS_settings_array["msg_position"]) == 4))
	$GOTMLS_settings_array["msg_position"] = array('80px', '40px', '400px', '600px');
if (!isset($GOTMLS_settings_array["menu_group"]))
	$GOTMLS_settings_array["menu_group"] = 0;
if (!isset($GOTMLS_settings_array["scan_what"]))
	$GOTMLS_settings_array["scan_what"] = 2;
if (!isset($GOTMLS_settings_array["scan_depth"]))
	$GOTMLS_settings_array["scan_depth"] = -1;
if (!(isset($GOTMLS_settings_array["exclude_ext"]) && is_array($GOTMLS_settings_array["exclude_ext"])))
	$GOTMLS_settings_array["exclude_ext"] = $GLOBALS["GOTMLS"]["tmp"]["skip_ext"];
if (!isset($GOTMLS_settings_array["check_custom"]))
	$GOTMLS_settings_array["check_custom"] = "";
if (!(isset($GOTMLS_settings_array['exclude_dir']) && is_array($GOTMLS_settings_array['exclude_dir'])))
	$GOTMLS_settings_array["exclude_dir"] = array();
$GOTMLS_total_percent = 0;
$GOTMLS_HeadersError = "";
function GOTMLS_admin_notices() {
	global $GOTMLS_HeadersError;
    if (!is_admin())
		return;
   	elseif ($GOTMLS_HeadersError)
		echo $GOTMLS_HeadersError;
}

function GOTMLS_array_recurse($array1, $array2) {
	foreach ($array2 as $key => $value) {
		if (!isset($array1[$key]) || (isset($array1[$key]) && !is_array($array1[$key])))
			$array1[$key] = array();
		if (is_array($value))
			$value = GOTMLS_array_recurse($array1[$key], $value);
		$array1[$key] = $value;
	}
	return $array1;
}

function GOTMLS_array_replace_recursive($array1 = array()) {
	$args = func_get_args();
	$array1 = $args[0];
	if (!is_array($array1))
		$array1 = array();
	for ($i = 1; $i < count($args); $i++)
		if (is_array($args[$i]))
			$array1 = GOTMLS_array_recurse($array1, $args[$i]);
	return $array1;
}

function GOTMLS_update_scan_log($scan_log) {
	if (is_array($scan_log)) {
		$GLOBALS["GOTMLS"]["log"] = GOTMLS_array_replace_recursive($GLOBALS["GOTMLS"]["log"], $scan_log);
		if (isset($GLOBALS["GOTMLS"]["log"]["scan"]["percent"]) && is_numeric($GLOBALS["GOTMLS"]["log"]["scan"]["percent"]) && ($GLOBALS["GOTMLS"]["log"]["scan"]["percent"] >= 100))
			$GLOBALS["GOTMLS"]["log"]["scan"]["finish"] = time();
		if (isset($GLOBALS["GOTMLS"]["log"]["scan"]))
			update_option('GOTMLS_scan_log/'.(isset($_SERVER["REMOTE_ADDR"])?$_SERVER["REMOTE_ADDR"]:"0.0.0.0").'/'.$GLOBALS["GOTMLS"]["tmp"]["mt"], $GLOBALS["GOTMLS"]["log"]);
	}
}

function GOTMLS_loaded() {
	global $GOTMLS_HeadersError;
	if (headers_sent($filename, $linenum)) {
		if (!$filename)
			$filename = __("an unknown file",'gotmls');
		if (!is_numeric($linenum))
			$linenum = __("unknown",'gotmls');
		$GOTMLS_HeadersError = '<div class="error">'.sprintf(__('<b>Headers already sent</b> in %1$s on line %2$s.<br />This is not a good sign, it may just be a poorly written plugin but Headers should not have been sent at this point.<br />Check the code in the above mentioned file to fix this problem.','gotmls'), $filename, $linenum).'</div>';
	} elseif (!session_id() && isset($_GET["eli"])) {	@session_start();	$_SESSION["GOTMLS_debug"]=array();}
}

if (!function_exists("add_action")) {
	GOTMLS_loaded();
	GOTMLS_admin_notices();
}

function GOTMLS_fileperms($file) {
	$perms = fileperms($file);
	if (($perms & 0xC000) == 0xC000) {
		$info = 's';    // Socket
	} elseif (($perms & 0xA000) == 0xA000) {
		$info = 'l';    // Symbolic Link
	} elseif (($perms & 0x8000) == 0x8000) {
		$info = '-';    // Regular
	} elseif (($perms & 0x6000) == 0x6000) {
		$info = 'b';    // Block special
	} elseif (($perms & 0x4000) == 0x4000) {
		$info = 'd';    // Directory
	} elseif (($perms & 0x2000) == 0x2000) {
		$info = 'c';    // Character special
	} elseif (($perms & 0x1000) == 0x1000) {
		$info = 'p';    // FIFO pipe
	} else
		$info = 'u';    // Unknown
	// Owner
	$info .= (($perms & 0x0100) ? 'r' : '-');
	$info .= (($perms & 0x0080) ? 'w' : '-');
	$info .= (($perms & 0x0040) ? (($perms & 0x0800) ? 's' : 'x' ) : (($perms & 0x0800) ? 'S' : '-'));
	// Group
	$info .= (($perms & 0x0020) ? 'r' : '-');
	$info .= (($perms & 0x0010) ? 'w' : '-');
	$info .= (($perms & 0x0008) ? (($perms & 0x0400) ? 's' : 'x' ) : (($perms & 0x0400) ? 'S' : '-'));
	// World
	$info .= (($perms & 0x0004) ? 'r' : '-');
	$info .= (($perms & 0x0002) ? 'w' : '-');
	$info .= (($perms & 0x0001) ? (($perms & 0x0200) ? 't' : 'x' ) : (($perms & 0x0200) ? 'T' : '-'));
	return $info;
}

function GOTMLS_get_ext($filename) {
	$nameparts = explode(".", ".$filename");
	return strtolower($nameparts[(count($nameparts)-1)]);
}

function GOTMLS_check_threat($check_threats, $file='UNKNOWN') {
	global $GOTMLS_threats_found, $GOTMLS_new_contents, $GOTMLS_file_contents;
	$GOTMLS_threats_found = array();
	if (is_array($check_threats)) {
		foreach ($check_threats as $threat_name=>$threat_definitions) {
if (isset($_SESSION["GOTMLS_debug"])){			$_SESSION["GOTMLS_debug"]["threat_name"] = $threat_name;
			$_SESSION["GOTMLS_debug"]["last"]["threat_name"] = microtime(true);}
			if (is_array($threat_definitions) && count($threat_definitions) > 1 && strlen(array_shift($threat_definitions)) == 5) {
				while ($threat_definition = array_shift($threat_definitions)) {
					if ($found = @preg_match_all($threat_definition, $GOTMLS_file_contents, $threats_found)) {
						foreach ($threats_found[0] as $find) {
							$GOTMLS_threats_found[$find] = $threat_name;
							$GOTMLS_new_contents = str_replace($find, "", $GOTMLS_new_contents);
						}
					}
				}
			}
if (isset($_SESSION["GOTMLS_debug"])){			$file_time = round(microtime(true) - $_SESSION["GOTMLS_debug"]["last"]["threat_name"], 6);
			if (isset($_SESSION["GOTMLS_debug"][$_SESSION["GOTMLS_debug"]["threat_name"]]["total"]))
				$_SESSION["GOTMLS_debug"][$_SESSION["GOTMLS_debug"]["threat_name"]]["total"] += $file_time;
			else
				$_SESSION["GOTMLS_debug"][$_SESSION["GOTMLS_debug"]["threat_name"]]["total"] = $file_time;
			if (isset($_SESSION["GOTMLS_debug"][$_SESSION["GOTMLS_debug"]["threat_name"]]["count"]))
				$_SESSION["GOTMLS_debug"][$_SESSION["GOTMLS_debug"]["threat_name"]]["count"] ++;
			else
				$_SESSION["GOTMLS_debug"][$_SESSION["GOTMLS_debug"]["threat_name"]]["count"] = 1;
			if (!isset($_SESSION["GOTMLS_debug"][$_SESSION["GOTMLS_debug"]["threat_name"]]["least"]) || $file_time < $_SESSION["GOTMLS_debug"][$_SESSION["GOTMLS_debug"]["threat_name"]]["least"])
				$_SESSION["GOTMLS_debug"][$_SESSION["GOTMLS_debug"]["threat_name"]]["least"] = $file_time;
			if (!isset($_SESSION["GOTMLS_debug"][$_SESSION["GOTMLS_debug"]["threat_name"]]["most"]) || $file_time > $_SESSION["GOTMLS_debug"][$_SESSION["GOTMLS_debug"]["threat_name"]]["most"])
				$_SESSION["GOTMLS_debug"][$_SESSION["GOTMLS_debug"]["threat_name"]]["most"] = $file_time;}
		}
	} elseif (strlen($check_threats) && isset($_GET['eli']) && substr($check_threats, 0, 1) == '/' && ($found = preg_match_all($check_threats, $GOTMLS_file_contents, $threats_found))) {
		foreach ($threats_found[0] as $find) {
			$GOTMLS_threats_found[$find] = "known";
			$GOTMLS_new_contents = str_replace($find, "", $GOTMLS_new_contents);
		}
	}
if (isset($_SESSION["GOTMLS_debug"])){	$file_time = round(microtime(true) - $_SESSION["GOTMLS_debug"]["last"]["threat_level"], 6);
	if (isset($_SESSION["GOTMLS_debug"][$_SESSION["GOTMLS_debug"]["threat_level"]]["total"]))
		$_SESSION["GOTMLS_debug"][$_SESSION["GOTMLS_debug"]["threat_level"]]["total"] += $file_time;
	else
		$_SESSION["GOTMLS_debug"][$_SESSION["GOTMLS_debug"]["threat_level"]]["total"] = $file_time;
	if (isset($_SESSION["GOTMLS_debug"][$_SESSION["GOTMLS_debug"]["threat_level"]]["count"]))
		$_SESSION["GOTMLS_debug"][$_SESSION["GOTMLS_debug"]["threat_level"]]["count"] ++;
	else
		$_SESSION["GOTMLS_debug"][$_SESSION["GOTMLS_debug"]["threat_level"]]["count"] = 1;
	if (!isset($_SESSION["GOTMLS_debug"][$_SESSION["GOTMLS_debug"]["threat_level"]]["least"]) || $file_time < $_SESSION["GOTMLS_debug"][$_SESSION["GOTMLS_debug"]["threat_level"]]["least"])
		$_SESSION["GOTMLS_debug"][$_SESSION["GOTMLS_debug"]["threat_level"]]["least"] = $file_time;
	if (!isset($_SESSION["GOTMLS_debug"][$_SESSION["GOTMLS_debug"]["threat_level"]]["most"]) || $file_time > $_SESSION["GOTMLS_debug"][$_SESSION["GOTMLS_debug"]["threat_level"]]["most"])
		$_SESSION["GOTMLS_debug"][$_SESSION["GOTMLS_debug"]["threat_level"]]["most"] = $file_time;}
	return count($GOTMLS_threats_found);
}

function GOTMLS_scanfile($file) {
	global $GOTMLS_core_files, $wp_version, $GOTMLS_threat_levels, $GOTMLS_threat_files, $GOTMLS_definitions_array, $GOTMLS_threats_found, $GOTMLS_chmod_file, $GOTMLS_chmod_dir, $GOTMLS_settings_array, $GOTMLS_file_contents, $GOTMLS_new_contents;
	$GOTMLS_threats_found = array();
	$found = false;
	$threat_link = "";
	$className = "scanned";
	$clean_file = GOTMLS_encode($file);
	$file_name = GOTMLS_explode_dir($file);
	$file_parts = explode(".", ".".array_pop($file_name));
	if (is_file($file) && ($filesize = filesize($file)) && ($GOTMLS_file_contents = @file_get_contents($file))) {
		foreach ($GOTMLS_definitions_array["whitelist"] as $whitelist_file=>$non_threats) {
			if (isset($non_threats[0])) {
				$updated = $non_threats[0];
				unset($non_threats[0]);
			} else
				$updated = "A0002";
			if (is_array($non_threats) && count($non_threats) && substr(str_replace("\\", "/", $file), (-1 * strlen($whitelist_file))) == str_replace("\\", "/", $whitelist_file)) {
				if (in_array(md5($GOTMLS_file_contents).'O'.$filesize, array_keys($non_threats), true))
					return GOTMLS_return_threat($className, "checked.gif?$className", $file, $threat_link);
				elseif (in_array(md5($GOTMLS_file_contents), $non_threats, true)) {
					if (!(isset($GOTMLS_definitions_array["whitelist"][''.GOTMLS_get_ext($file)][0]) && $GOTMLS_definitions_array["whitelist"][''.GOTMLS_get_ext($file)][0] >= $updated))
						$GOTMLS_definitions_array["whitelist"][''.GOTMLS_get_ext($file)][0] = $updated;
					$GOTMLS_definitions_array["whitelist"][''.GOTMLS_get_ext($file)][md5($GOTMLS_file_contents).'O'.$filesize] = $updated;
					unset($GOTMLS_definitions_array["whitelist"][$whitelist_file]);
					update_option("GOTMLS_definitions_array", $GOTMLS_definitions_array);
					return GOTMLS_return_threat($className, "checked.gif?$className", $file, $threat_link);
				}
			}
		}
		$GOTMLS_new_contents = $GOTMLS_file_contents;
		if (isset($GLOBALS["GOTMLS"]["log"]["settings"]["check_custom"]) && strlen($GLOBALS["GOTMLS"]["log"]["settings"]["check_custom"]) && isset($_GET['eli']) && substr($GLOBALS["GOTMLS"]["log"]["settings"]["check_custom"], 0, 1) == '/' && ($found = GOTMLS_check_threat($GLOBALS["GOTMLS"]["log"]["settings"]["check_custom"])))
			$className = "known";
		else {
if (isset($_SESSION["GOTMLS_debug"])){			$_SESSION["GOTMLS_debug"]["file"] = $file;
			$_SESSION["GOTMLS_debug"]["last"]["total"] = microtime(true);}
			foreach ($GOTMLS_threat_levels as $threat_level) {
if (isset($_SESSION["GOTMLS_debug"])){				$_SESSION["GOTMLS_debug"]["threat_level"] = $threat_level;
				$_SESSION["GOTMLS_debug"]["last"]["threat_level"] = microtime(true);}
				if (in_array($threat_level, $GLOBALS["GOTMLS"]["log"]["settings"]["check"]) && !$found && isset($GOTMLS_definitions_array[$threat_level]) && (!array_key_exists($threat_level,$GOTMLS_core_files) || (substr($file."e", (-1 * strlen($GOTMLS_core_files[$threat_level]."e"))) == $GOTMLS_core_files[$threat_level]."e")) && (!array_key_exists($threat_level,$GOTMLS_threat_files) || ((GOTMLS_get_ext($file) == "gotmls" && isset($_GET["eli"]) && $_GET["eli"] == "quarantine")?(substr(GOTMLS_decode(array_pop(explode(".", '.'.substr($file, strlen(dirname($file))+1, -7))))."e", (-1 * strlen($GOTMLS_threat_files[$threat_level]."e"))) == $GOTMLS_threat_files[$threat_level]."e"):(substr($file."e", (-1 * strlen($GOTMLS_threat_files[$threat_level]."e"))) == $GOTMLS_threat_files[$threat_level]."e"))) && ($found = GOTMLS_check_threat($GOTMLS_definitions_array[$threat_level],$file)))
					$className = $threat_level;
			}
if (isset($_SESSION["GOTMLS_debug"])){			$file_time = round(microtime(true) - $_SESSION["GOTMLS_debug"]["last"]["total"], 5);
			if (isset($_SESSION["GOTMLS_debug"]["total"]["total"]))
				$_SESSION["GOTMLS_debug"]["total"]["total"] += $file_time;
			else
				$_SESSION["GOTMLS_debug"]["total"]["total"] = $file_time;
			if (isset($_SESSION["GOTMLS_debug"]["total"]["count"]))
				$_SESSION["GOTMLS_debug"]["total"]["count"] ++;
			else
				$_SESSION["GOTMLS_debug"]["total"]["count"] = 1;
			if (!isset($_SESSION["GOTMLS_debug"]["total"]["least"]) || $file_time < $_SESSION["GOTMLS_debug"]["total"]["least"])
				$_SESSION["GOTMLS_debug"]["total"]["least"] = $file_time;
			if (!isset($_SESSION["GOTMLS_debug"]["total"]["most"]) || $file_time > $_SESSION["GOTMLS_debug"]["total"]["most"])
				$_SESSION["GOTMLS_debug"]["total"]["most"] = $file_time;}
		}
	} else {
		$GOTMLS_file_contents = (filesize($file)?__("Failed to read file contents!",'gotmls').' '.(is_readable($file)?'(file_is_readable)':(file_exists($file)?(isset($_GET["eli"])?(@chmod($file, $GOTMLS_chmod_file)?'chmod':'read-only'):'(file_not_readable)'):'(does_not_exist)')):__("Empty file!",'gotmls'));
//		$threat_link = GOTMLS_error_link($GOTMLS_file_contents, $file);
		$className = "errors";
	}
	if (count($GOTMLS_threats_found)) {
		$threat_link = '<a target="GOTMLS_iFrame" href="'.GOTMLS_script_URI.'&GOTMLS_scan='.$clean_file.'" id="list_'.$clean_file.'" onclick="loadIframe(\''.str_replace("\"", "&quot;", '<div style="float: left;">Examine&nbsp;File&nbsp;...&nbsp;</div><div style="overflow: hidden; position: relative; height: 20px;"><div style="position: absolute; right: 0px; text-align: right; width: 9000px;">'.GOTMLS_strip4java($file)).'</div></div>\');" class="GOTMLS_plugin">';
		if (isset($_POST["GOTMLS_fix"]) && is_array($_POST["GOTMLS_fix"]) && in_array($clean_file, $_POST["GOTMLS_fix"])) {
			if (GOTMLS_trailingslashit($GLOBALS["GOTMLS"]["tmp"]["quarantine_dir"]) == substr($file, 0, strlen(GOTMLS_trailingslashit($GLOBALS["GOTMLS"]["tmp"]["quarantine_dir"])))) {
				if ($_POST["GOTMLS_fixing"] > 1 && @unlink($file))
					$GOTMLS_file_contents = "";
				elseif (count($file_parts) > 1 && strtolower($file_parts[count($file_parts)-1]) == "gotmls" && $GOTMLS_new_contents = @file_get_contents($file))
					$file = GOTMLS_decode($file_parts[count($file_parts)-2]);
				else
					$GOTMLS_new_contents = trim(preg_replace('/<\?(php)?\s*(\?>|$)/i', "", $GOTMLS_new_contents));
			} elseif (isset($GOTMLS_threat_files[$className]) && GOTMLS_get_ext($GOTMLS_threat_files[$className]) == "php") {
				$project = str_replace("_", "-", $className);
				$source = wp_remote_get("http://$project.googlecode.com/svn/trunk/$project.php");
				if (is_array($source) && isset($source["body"]) && strlen($source["body"]) > 500)
					$GOTMLS_new_contents = $source["body"].$GOTMLS_new_contents;
				else
					$GOTMLS_file_contents = "";
			} elseif (isset($GOTMLS_core_files[$className])) {
				$source = wp_remote_get("http://core.svn.wordpress.org/tags/".$wp_version.$GOTMLS_core_files[$className]);
				if (is_array($source) && isset($source["body"]) && strlen($source["body"]) > 500)
					$GOTMLS_new_contents = $source["body"];
				else
					$GOTMLS_file_contents = "";
				if (file_exists(dirname(__FILE__).'/../../../../wp-config.php')) {
					$config = @file_get_contents(dirname(__FILE__).'/../../../../wp-config.php');
					$head = "<?php if (file_exists(dirname(__FILE__).'/wp-content/plugins/gotmls/safe-load/wp-login.php')) require(dirname(__FILE__).'/wp-content/plugins/gotmls/safe-load/wp-login.php'); // Load Security Patch by GOTMLS.NET before the WordPress bootstrap. ?>";
					if (strlen($config) && $head != substr($config, 0, strlen($head)))
						@file_put_contents(dirname(__FILE__).'/../../../../wp-config.php', $head.$config);
				}
			} else
				$GOTMLS_new_contents = trim(preg_replace('/<\?(php)?\s*(\?>|$)/i', "", $GOTMLS_new_contents));
			if (strlen($GOTMLS_file_contents) > 0 && ((@GOTMLS_file_put_contents(GOTMLS_quarantine($file), $GOTMLS_file_contents) !== false) || ((is_writable(dirname(GOTMLS_quarantine($file))) || (($GOTMLS_chmod_dir = fileperms(dirname(GOTMLS_quarantine($file)))) && ($chmoded_quarantine = @chmod(dirname(GOTMLS_quarantine($file)), 0777)))) && (@GOTMLS_file_put_contents(GOTMLS_quarantine($file), $GOTMLS_file_contents) !== false) && !($chmoded_quarantine && !@chmod(dirname(GOTMLS_quarantine($file)), $GOTMLS_chmod_dir)))) && (((strlen($GOTMLS_new_contents)==0 && isset($_GET["eli"]) && @unlink($file)) || (@GOTMLS_file_put_contents($file, $GOTMLS_new_contents) !== false) || ((is_writable(dirname($file)) || (($GOTMLS_chmod_dir = fileperms(dirname($file))) && ($chmoded_dir = @chmod(dirname($file), 0777)))) && (is_writable($file) || (($GOTMLS_chmod_file = fileperms($file)) && ($chmoded_file = @chmod($file, 0666)))) && (@GOTMLS_file_put_contents($file, $GOTMLS_new_contents) !== false) && !($chmoded_dir && !@chmod(dirname($file), $GOTMLS_chmod_dir)) && !($chmoded_file && !@chmod($file, $GOTMLS_chmod_file)))))) {
				echo ' Success!';
				return "/*-->*"."/\nfixedFile('$clean_file');\n/*<!--*"."/";
			} elseif ($_POST["GOTMLS_fixing"] > 1 && $GOTMLS_file_contents == "") {
				echo ' Deleted!';
				return "/*-->*"."/\nfixedFile('$clean_file');\n/*<!--*"."/";
			} else {
				echo ' Failed!';
				if (isset($_GET["eli"]))
					print_r(array(get_current_user().'='.getmyuid().',gid='.getmygid().']<pre>[file_stat'=>stat($file),"strlen"=>strlen($GOTMLS_file_contents),'write_quarantine'=>((@GOTMLS_file_put_contents(GOTMLS_quarantine($file), $GOTMLS_file_contents) !== false)?'wrote_backup_file':'failed_write='.(file_exists(GOTMLS_quarantine($file))?GOTMLS_quarantine($file).GOTMLS_fileperms(GOTMLS_quarantine($file)):dirname(GOTMLS_quarantine($file)).GOTMLS_fileperms(dirname(GOTMLS_quarantine($file))))),"dir_writable"=>(is_writable(dirname($file))?'Yes':(@chmod(dirname($file), $GOTMLS_chmod_dir)?"chmod($GOTMLS_chmod_dir)":'read-only')),"file_writable"=>(is_writable($file)?"GOTMLS_file_put_contents($file):".((@GOTMLS_file_put_contents($file, $GOTMLS_new_contents) !== false)?'wrote_new':'failed_write'):fileperms($file).(chmod($file, 0664)?", chmod($file, $GOTMLS_chmod_file), ".GOTMLS_fileperms($file):'read-only')), "unlink"=>(strlen($GOTMLS_new_contents)==0?(@unlink($file)?'unlinked':'failed_delete'):'strlen:'.strlen($GOTMLS_new_contents)).'</pre>'));
				return "/*-->*"."/\nfailedFile('$clean_file');\n/*<!--*"."/";
			}
		}
		if ($className == "errors") {
			$threat_link = GOTMLS_error_link($GOTMLS_file_contents, $file);
			$imageFile = "/blocked";
		} elseif ($className != "potential") {
			$threat_link = '<input type="checkbox" name="GOTMLS_fix[]" value="'.$clean_file.'" id="check_'.$clean_file.(($className != "wp_login")?'" checked="'.$className:'').'" />'.$threat_link;
			$imageFile = "threat";
		} else
			$imageFile = "question";
		return GOTMLS_return_threat($className, $imageFile, $file, str_replace("GOTMLS_plugin", "GOTMLS_plugin $className", $threat_link));
	} elseif (isset($_POST["GOTMLS_fix"]) && is_array($_POST["GOTMLS_fix"]) && in_array($clean_file, $_POST["GOTMLS_fix"])) {
		if (GOTMLS_trailingslashit($GLOBALS["GOTMLS"]["tmp"]["quarantine_dir"]) == substr($file, 0, strlen(GOTMLS_trailingslashit($GLOBALS["GOTMLS"]["tmp"]["quarantine_dir"])))) {
			if ($_POST["GOTMLS_fixing"] > 1 && @unlink($file)) {
				$GOTMLS_file_contents = "";
				$msg = __("Deleted!",'gotmls');
				echo " $msg";
				return "/*-->*"."/\nfixedFile('$clean_file');\n/*<!--*"."/";
			} elseif (count($file_parts) > 1 && strtolower($file_parts[count($file_parts)-1]) == "gotmls" && @rename($file, GOTMLS_decode($file_parts[count($file_parts)-2]))) {
				$msg = __("Restored!",'gotmls');
				echo " $msg";
				return "/*-->*"."/\nfixedFile('$clean_file');\n/*<!--*"."/";
			} else {
				$msg = __("Restore Failed!",'gotmls');
				echo " $msg";
				return "";
			}
		} else {
			$msg = __("Already Fixed!",'gotmls');
			echo " $msg";
			return "/*-->*"."/\nfixedFile('$clean_file');\n/*<!--*"."/";
		}
	} else
		return GOTMLS_return_threat($className, ($className=="scanned"?"checked":"blocked").".gif?$className", $file, $threat_link);
}

function GOTMLS_remove_dots($dir) {
	if ($dir != "." && $dir != "..")
		return $dir;
}

function GOTMLS_getfiles($dir) {
	$files = false;
	if (is_dir($dir)) {
		if (function_exists("scandir"))
			$files = @scandir($dir);
		if (is_array($files))
			$files = array_filter($files, "GOTMLS_remove_dots");
		elseif ($handle = @opendir($dir)) {
			$files = array();
			while (false !== ($entry = readdir($handle)))
				if ($entry != "." && $entry != "..")
					$files[] = "$entry";
			closedir($handle);
		} else {
			$error = error_get_last();
			$files .= (is_readable($dir)?(is_array($error) && isset($error["message"])?$error["message"]:"readable? "):(isset($_GET["eli"]) && @chmod($dir, 0775)?"chmod ":"readonly ")).GOTMLS_fileperms($dir);
		}
	}
	return $files;
}

function GOTMLS_encode($unencoded_string) {
	if (function_exists("base64_encode"))
		$encoded_string = base64_encode($unencoded_string);
	elseif (function_exists("mb_convert_encoding"))
		$encoded_string = mb_convert_encoding($unencoded_string, "BASE64", "UTF-8");
	else
		$encoded_string = "Cannot encode: $unencoded_string function_exists: ";
	$encoded_array = explode("=", $encoded_string.'=');
	return strtr($encoded_array[0], "+/", "-_").(count($encoded_array)-1);
}

function GOTMLS_decode($encoded_string) {
	$encoded_string = strtr(substr($encoded_string, 0, -1), "-_", "+/").str_repeat("=", intval('0'.substr($encoded_string, -1)));
	if (function_exists("base64_decode"))
		return base64_decode($encoded_string);
	elseif (function_exists("mb_convert_encoding"))
		return mb_convert_encoding($encoded_string, "UTF-8", "BASE64");
	else
		return "Cannot decode: $encoded_string";
}

function GOTMLS_decodeBase64($encoded_string) {
	if (function_exists("base64_decode"))
		$unencoded_string = base64_decode($encoded_string);
	elseif (function_exists("mb_convert_encoding"))
		$unencoded_string = mb_convert_encoding($encoded_string, "UTF-8", "BASE64");
	else
		return "Cannot decode: '$encoded_string'";
	return "'".str_replace("'", "\\'", str_replace("\\", "\\\\", $unencoded_string))."'";
}

function GOTMLS_decodeHex($encoded_string) {
	return chr(hexdec($encoded_string));
}

function GOTMLS_return_threat($className, $imageFile, $fileName, $link = "") {
	global $GOTMLS_image_alt;
	$fileNameJS = GOTMLS_strip4java(str_replace(dirname($GLOBALS["GOTMLS"]["log"]["scan"]["dir"]), "...", $fileName));
	$fileName64 = GOTMLS_encode($fileName);
	$li_js = "/*-->*"."/";
	if ($className != "scanned")
		$li_js .= "\n$className++;\ndivx=document.getElementById('found_$className');\nif (divx) {\n\tvar newli = document.createElement('li');\n\tnewli.innerHTML='<img src=\"".GOTMLS_strip4java(GOTMLS_images_path.$imageFile).".gif\" height=16 width=16 alt=\"".$GOTMLS_image_alt[$imageFile]."\" style=\"float: left;\" id=\"$imageFile"."_$fileName64\">".GOTMLS_strip4java($link).$fileNameJS.($link?"</a>';\n\tdivx.display='block":"")."';\n\tdivx.appendChild(newli);\n}";
	if ($className == "errors")
		$li_js .= "\ndivx=document.getElementById('wait_$fileName64');\nif (divx) {\n\tdivx.src='".GOTMLS_images_path."blocked.gif';\n\tdirerrors++;\n}";
	elseif (is_file($fileName))
	 	$li_js .= "\nscanned++;\n";
	if ($className == "dir")
		$li_js .= "\ndivx=document.getElementById('wait_$fileName64');\nif (divx)\n\tdivx.src='".GOTMLS_images_path."checked.gif';";
	return $li_js."\n/*<!--*"."/";
}

function GOTMLS_slash($dir = __file__) {
	if (substr($dir.'  ', 1, 1) == ':' || substr($dir.'  ', 0, 1) == "\\")
		return "\\";
	else
		return  '/';
}

function GOTMLS_trailingslashit($dir = "") {
	if (substr(' '.$dir, -1) != GOTMLS_slash($dir))
		$dir .= GOTMLS_slash($dir);
	return $dir;
}

function GOTMLS_explode_dir($dir, $pre = '') {
	if (strlen($pre))
		$dir = GOTMLS_slash($dir).$pre.$dir;
	return explode(GOTMLS_slash($dir), $dir);
}

function GOTMLS_quarantine($file) {
	if (!isset($GLOBALS["GOTMLS"]["tmp"]["quarantine_dir"])) {
		$upload = wp_upload_dir();
		$err403 = '<html><head><title>403 Forbidden</title></head><body><h1>Forbidden</h1><p>You don\'t have permission to access this directory.</p></body></html>';
		$GLOBALS["GOTMLS"]["tmp"]["quarantine_dir"] = GOTMLS_trailingslashit($upload['basedir']).'quarantine';
		if (!is_dir($GLOBALS["GOTMLS"]["tmp"]["quarantine_dir"]) && !@mkdir($GLOBALS["GOTMLS"]["tmp"]["quarantine_dir"]))
			$GLOBALS["GOTMLS"]["tmp"]["quarantine_dir"] = $upload['basedir'];
		if (is_file(GOTMLS_trailingslashit($upload['basedir']).'.htaccess') && file_get_contents(GOTMLS_trailingslashit($upload['basedir']).'.htaccess') == 'Options -Indexes')
			if (!@unlink(GOTMLS_trailingslashit($upload['basedir']).'.htaccess'))
				@GOTMLS_file_put_contents(GOTMLS_trailingslashit($upload['basedir']).'.htaccess', '');
		if (!is_file(GOTMLS_trailingslashit($GLOBALS["GOTMLS"]["tmp"]["quarantine_dir"]).'.htaccess'))
			@GOTMLS_file_put_contents(GOTMLS_trailingslashit($GLOBALS["GOTMLS"]["tmp"]["quarantine_dir"]).'.htaccess', 'Options -Indexes');
		if (!is_file(GOTMLS_trailingslashit($upload['basedir']).'index.php'))
			@GOTMLS_file_put_contents(GOTMLS_trailingslashit($upload['basedir']).'index.php', $err403);
		if (!is_file(GOTMLS_trailingslashit($GLOBALS["GOTMLS"]["tmp"]["quarantine_dir"]).'index.php'))
			@GOTMLS_file_put_contents(GOTMLS_trailingslashit($GLOBALS["GOTMLS"]["tmp"]["quarantine_dir"]).'index.php', $err403);
	}
	return GOTMLS_trailingslashit($GLOBALS["GOTMLS"]["tmp"]["quarantine_dir"]).GOTMLS_sexagesimal().'.'.GOTMLS_encode($file).'.GOTMLS';
}

function GOTMLS_update_status($status, $percent = -1) {
	if (!(isset($GLOBALS["GOTMLS"]["log"]["scan"]["start"]) && is_numeric($GLOBALS["GOTMLS"]["log"]["scan"]["start"])))
		$GLOBALS["GOTMLS"]["log"]["scan"]["start"] = time();
	$microtime = ceil(time()-$GLOBALS["GOTMLS"]["log"]["scan"]["start"]);
	GOTMLS_update_scan_log(array("scan" => array("microtime" => $microtime, "percent" => $percent)));
	return "/*-->*"."/\nupdate_status('".GOTMLS_strip4java($status)."', $microtime, $percent);\n/*<!--*"."/";
}

function GOTMLS_flush($tag = "") {
	$output = "";
	if (!(isset($_GET["eli"]) && $_GET["eli"]=="debug") && ($output = @ob_get_contents())) {
		@ob_clean();
		$output = preg_replace('/\/\*\<\!--\*\/(.*?)\/\*--\>\*\//s', "", "$output/*-->*"."/");
	}
	echo "$output\n//flushed()\n";
	if ($tag)
		echo "\n</$tag>\n";
	if (@ob_get_length())
		@ob_flush();
	if ($tag)
		echo "<$tag>\n/*<!--*"."/";
}

function GOTMLS_readdir($dir, $current_depth = 1) {
	global $GOTMLS_loop_execution_time, $GOTMLS_scanfiles, $GOTMLS_skip_dirs, $GOTMLS_dirs_at_depth, $GOTMLS_dir_at_depth, $GOTMLS_total_percent;
	if ($dir != $GLOBALS["GOTMLS"]["tmp"]["quarantine_dir"] || $current_depth == 1) {
		@set_time_limit($GOTMLS_loop_execution_time);
		$entries = GOTMLS_getfiles($dir);
		if (is_array($entries)) {
			echo GOTMLS_return_threat("dirs", "wait", $dir).GOTMLS_update_status(sprintf(__("Preparing %s",'gotmls'), str_replace(dirname($GLOBALS["GOTMLS"]["log"]["scan"]["dir"]), "...", $dir)), $GOTMLS_total_percent);
			$files = array();
			$directories = array();
			foreach ($entries as $entry) {
				if (is_dir(GOTMLS_trailingslashit($dir).$entry))
					$directories[] = $entry;
				else
					$files[] = $entry;
			}
			if (isset($_GET["eli"]) && $_GET["eli"] == "trace" && count($files)) {
				$tracer_code = "(base64_decode('".base64_encode('if(isset($_SERVER["REMOTE_ADDR"]) && $_SERVER["REMOTE_ADDR"] == "'.$_SERVER["REMOTE_ADDR"].'" && is_file("'.GOTMLS_local_images_path.'../safe-load/trace.php")) {include_once("'.GOTMLS_local_images_path.'../safe-load/trace.php");GOTMLS_debug_trace(__FILE__);}')."'));";
				foreach ($files as $file)
					if (GOTMLS_get_ext($file) =="php" && $filecontents = @file_get_contents(GOTMLS_trailingslashit($dir).$file))
						@GOTMLS_file_put_contents(GOTMLS_trailingslashit($dir).$file, preg_replace('/^<\?php(?! eval)/is', '<?php eval'.$tracer_code, $filecontents));
			}
			if ($_REQUEST["scan_type"] == "Quick Scan") {
				$GOTMLS_dirs_at_depth[$current_depth] = count($directories);
				$GOTMLS_dir_at_depth[$current_depth] = 0;
			} else
				$GOTMLS_scanfiles[GOTMLS_encode($dir)] = GOTMLS_strip4java(str_replace(dirname($GLOBALS["GOTMLS"]["log"]["scan"]["dir"]), "...", $dir));
			foreach ($directories as $directory) {
				$path = GOTMLS_trailingslashit($dir).$directory;
				if (isset($_REQUEST["scan_depth"]) && is_numeric($_REQUEST["scan_depth"]) && ($_REQUEST["scan_depth"] != $current_depth) && !in_array($directory, $GOTMLS_skip_dirs)) {
					$current_depth++;
					$current_depth = GOTMLS_readdir($path, $current_depth);
				} else {
					echo GOTMLS_return_threat("skipdirs", "blocked", $path);
					$GOTMLS_dir_at_depth[$current_depth]++;
				}
			}
			if ($_REQUEST["scan_type"] == "Quick Scan") {
				$echo = "";
				echo GOTMLS_update_status(sprintf(__("Scanning %s",'gotmls'), str_replace(dirname($GLOBALS["GOTMLS"]["log"]["scan"]["dir"]), "...", $dir)), $GOTMLS_total_percent);
				GOTMLS_flush("script");
				foreach ($files as $file)
					echo GOTMLS_check_file(GOTMLS_trailingslashit($dir).$file);
				echo GOTMLS_return_threat("dir", "checked", $dir);
			}
		} else
			echo GOTMLS_return_threat("errors", "blocked", $dir, GOTMLS_error_link(GOTMLS_Failed_to_list_LANGUAGE.' readdir:'.($entries===false?'(FALSE)':$entries)));
		@set_time_limit($GOTMLS_loop_execution_time);
		if ($current_depth-- && $_REQUEST["scan_type"] == "Quick Scan") {
			$GOTMLS_dir_at_depth[$current_depth]++;
			for ($GOTMLS_total_percent = 0, $depth = $current_depth; $depth >= 0; $depth--) {
				echo "\n//(($GOTMLS_total_percent / $GOTMLS_dirs_at_depth[$depth]) + ($GOTMLS_dir_at_depth[$depth] / $GOTMLS_dirs_at_depth[$depth])) = ";
				$GOTMLS_total_percent = (($GOTMLS_dirs_at_depth[$depth]?($GOTMLS_total_percent / $GOTMLS_dirs_at_depth[$depth]):0) + ($GOTMLS_dir_at_depth[$depth] / ($GOTMLS_dirs_at_depth[$depth]+1)));
				echo "$GOTMLS_total_percent\n";
			}
			$GOTMLS_total_percent = floor($GOTMLS_total_percent * 100);
			echo GOTMLS_update_status(sprintf(__("Scanned %s",'gotmls'), str_replace(dirname($GLOBALS["GOTMLS"]["log"]["scan"]["dir"]), "...", $dir)), $GOTMLS_total_percent);
		}
		GOTMLS_flush("script");
	}
	return $current_depth;
}

function GOTMLS_sexagesimal($timestamp = 0) {
	if (!is_numeric($timestamp) && strlen($timestamp) == 5) {
		foreach (str_split($timestamp) as $bit)
			$timestamp .= "-".substr("00".(ord($bit)>96?ord($bit)-61:(ord($bit)>64?ord($bit)-55:ord($bit)-48)), -2);
		return substr($timestamp, -14);
	} else {
		if (preg_match('/^[0-5][0-9]-[0-1][0-9]-[0-3][0-9]-[0-2][0-9]-[0-5][0-9]$/', $timestamp))
			$date = $timestamp;
		elseif (is_numeric($timestamp) && strlen(trim($timestamp.' ')) == 10)
			$date = preg_replace('/^([0-9]{2})([0-9]{2})([0-9]{2})([0-9]{2})([0-9]{2})$/', "\\1-\\2-\\3-\\4-\\5", $timestamp);
		else
			$date = date("y-m-d-H-i", time());
		foreach (explode("-", $date) as $bit)
			$date .= (intval($bit)>35?chr(ord("a")+intval($bit)-36):(intval($bit)>9?chr(ord("A")+intval($bit)-10):substr('0'.$bit, -1)));
		return substr($date, -5);
	}
}

if (!function_exists('ur1encode')) { function ur1encode($url) {
	global $GOTMLS_encode;
	return preg_replace($GOTMLS_encode, '\'%\'.substr(\'00\'.strtoupper(dechex(ord(\'\0\'))),-2);', $url);
}}

function GOTMLS_strip4java($item) {
	return preg_replace("/\\\\/", "\\\\\\\\", preg_replace("/'/", "'+\"'\"+'", preg_replace('/\\+n/', "", $item)));//(?<!\\\\)
}

function GOTMLS_error_link($errorTXT, $file = "", $class = "errors") {
	if ($file)
		$clean_file = 'loadIframe(\''.str_replace("\"", "&quot;", '<div style="float: left;">Examine&nbsp;File&nbsp;...&nbsp;</div><div style="overflow: hidden; position: relative; height: 20px;"><div style="position: absolute; right: 0px; text-align: right; width: 9000px;">'.GOTMLS_strip4java($file)).'</div></div>\');" href="'.GOTMLS_script_URI.'&GOTMLS_scan='.GOTMLS_encode($file);
	else
		$clean_file = 'return false;';
	return "<a title=\"$errorTXT\" target=\"GOTMLS_iFrame\" onclick=\"$clean_file\" class=\"GOTMLS_plugin $class\">";
}

function GOTMLS_check_file($file) {
	$filesize = @filesize($file);
	echo "/*-->*"."/\ndocument.getElementById('status_text').innerHTML='Checking ".GOTMLS_strip4java($file)." ($filesize bytes)';\n/*<!--*"."/";
	if (GOTMLS_get_ext($file) == "bad")
		echo GOTMLS_return_threat("bad", (@rename($file, GOTMLS_quarantine(substr($file, 0, -4)))?"checked":"blocked"), $file);
	elseif (GOTMLS_get_ext($file) == "gotmls" && !(isset($_GET["eli"]) && $_GET["eli"] == "quarantine"))
		echo GOTMLS_return_threat("bad", "checked", GOTMLS_decode(substr(array_pop(GOTMLS_explode_dir($file)), 0, -7)));
	elseif (in_array(GOTMLS_get_ext($file), $GLOBALS["GOTMLS"]["tmp"]["skip_ext"]) && !(preg_match('/social[0-9]*\.png$/i', $file)))
		echo GOTMLS_return_threat("skipped", "blocked", $file, GOTMLS_error_link(__("Skipped because of file extention!",'gotmls'), $file, "potential"));
	elseif ($filesize===false)
		echo GOTMLS_return_threat("errors", "blocked", $file, GOTMLS_error_link(__("Failed to determine file size!",'gotmls'), $file));
	elseif (($filesize==0) || ($filesize>((isset($_GET["eli"])&&is_numeric($_GET["eli"]))?$_GET["eli"]:1234567)))
		echo GOTMLS_return_threat("skipped", "blocked", $file, GOTMLS_error_link(__("Skipped because of file size!",'gotmls')." ($filesize bytes)", $file, "potential"));
	else {
		try {
			echo @GOTMLS_scanfile($file);
		} catch (Exception $e) {
			die("//Exception:".GOTMLS_strip4java($e));
		}
	}
	echo "/*-->*"."/\ndocument.getElementById('status_text').innerHTML='Checked ".GOTMLS_strip4java($file)."';\n/*<!--*"."/";
}

function GOTMLS_scandir($dir) {
	echo "/*<!--*"."/".GOTMLS_update_status(sprintf(__("Scanning %s",'gotmls'), str_replace(dirname($GLOBALS["GOTMLS"]["log"]["scan"]["dir"]), "...", $dir)));
	GOTMLS_flush();
	$li_js = "/*-->*"."/\nscanNextDir(-1);\n/*<!--*"."/";
	if (isset($_GET["GOTMLS_skip_dir"]) && $dir == GOTMLS_decode($_GET["GOTMLS_skip_dir"])) {
		if (isset($_GET["GOTMLS_only_file"]) && strlen($_GET["GOTMLS_only_file"]))
			echo GOTMLS_return_threat("errors", "blocked", GOTMLS_trailingslashit($dir).GOTMLS_decode($_GET["GOTMLS_only_file"]), GOTMLS_error_link("Failed to read this file!", GOTMLS_trailingslashit($dir).GOTMLS_decode($_GET["GOTMLS_only_file"])));
		else
			echo GOTMLS_return_threat("errors", "blocked", $dir, GOTMLS_error_link(__("Failed to read directory!",'gotmls')));
	} else {
		$files = GOTMLS_getfiles($dir);
		if (is_array($files)) {
			if (isset($_GET["GOTMLS_only_file"])) {
				if (strlen($_GET["GOTMLS_only_file"])) {
					$path = GOTMLS_trailingslashit($dir).GOTMLS_decode($_GET["GOTMLS_only_file"]);
					if (is_file($path)) {
						GOTMLS_check_file($path);
						echo GOTMLS_return_threat("dir", "checked", $path);
					}
				} else {
					foreach ($files as $file) {
						$path = GOTMLS_trailingslashit($dir).$file;
						if (is_file($path)) {
							$file_ext = GOTMLS_get_ext($file);
							$filesize = @filesize($path);
							if ((in_array($file_ext, $GLOBALS["GOTMLS"]["tmp"]["skip_ext"]) && !(preg_match('/social[0-9]*\.png$/i', $file))) || ($filesize==0) || ($filesize>((isset($_GET["eli"])&&is_numeric($_GET["eli"]))?$_GET["eli"]:1234567)))
								echo GOTMLS_return_threat("skipped", "blocked", $path, GOTMLS_error_link(sprintf(__('Skipped because of file size (%1$s bytes) or file extention (%2$s)!','gotmls'), $filesize, $file_ext), $file, "potential"));
							else
								echo "/*-->*"."/\nscanfilesArKeys.push('".GOTMLS_encode($dir)."&GOTMLS_only_file=".GOTMLS_encode($file)."');\nscanfilesArNames.push('Re-Checking ".GOTMLS_strip4java($path)."');\n/*<!--*"."/".GOTMLS_return_threat("dirs", "wait", $path);
						}
					}
					echo GOTMLS_return_threat("dir", "question", $dir);
				}
			} else {
				foreach ($files as $file) {
					$path = GOTMLS_trailingslashit($dir).$file;
					if (is_file($path)) {
						if (isset($_GET["GOTMLS_skip_file"]) && is_array($_GET["GOTMLS_skip_file"]) && in_array($path, $_GET["GOTMLS_skip_file"])) {
							$li_js .= "/*-->*"."/\n//skipped $path;\n/*<!--*"."/";
							if ($path == $_GET["GOTMLS_skip_file"][count($_GET["GOTMLS_skip_file"])-1])
								echo GOTMLS_return_threat("errors", "blocked", $path, GOTMLS_error_link(__("Failed to read file!",'gotmls'), $path));
						} else {
							GOTMLS_check_file($path);
						}
					}
				}
				echo GOTMLS_return_threat("dir", "checked", $dir);
			}
		} else
			echo GOTMLS_return_threat("errors", "blocked", $dir, GOTMLS_error_link(GOTMLS_Failed_to_list_LANGUAGE.' scandir:'.($files===false?' (FALSE)':$files)));
	}
	echo GOTMLS_update_status(sprintf(__("Scanned %s",'gotmls'), str_replace(dirname($GLOBALS["GOTMLS"]["log"]["scan"]["dir"]), "...", $dir)));
	GOTMLS_update_scan_log(array("scan" => array("finish" => time())));
	return $li_js;
}

function GOTMLS_reset_settings($item, $key) {
	global $GOTMLS_settings_array;
	$key_parts = explode("_", $key."_");
	if (strlen($key_parts[0]) != 4 && $key_parts[0] != "exclude")
		unset($GOTMLS_settings_array[$key]);
}

$GLOBALS["GOTMLS"]["tmp"]["quarantine_dir"] = dirname(GOTMLS_quarantine(__FILE__));
$GLOBALS["GOTMLS"]["tmp"]["default_ext"] .= "com";
$GOTMLS_encode .= substr($GLOBALS["GOTMLS"]["tmp"]["default_ext"], 0, 2);
if(!isset($_SERVER["SERVER_NAME"]) || !$_SERVER["SERVER_NAME"]) {
	if(!isset($_ENV["SERVER_NAME"]))
		getenv("SERVER_NAME");
	$_SERVER["SERVER_NAME"] = $_ENV["SERVER_NAME"];
}
if(!isset($_SERVER["SERVER_PORT"]) || !$_SERVER["SERVER_PORT"]) {
	if(!isset($_ENV["SERVER_PORT"]))
		getenv("SERVER_PORT");
	$_SERVER["SERVER_PORT"] = $_ENV["SERVER_PORT"];
}
if ((isset($_SERVER["HTTPS"]) && ($_SERVER["HTTPS"] == "on" || $_SERVER["HTTPS"] == 1)) || 'ssl'.$_SERVER["SERVER_PORT"] == 'ssl443')
	$GLOBALS["GOTMLS"]["tmp"]["protocol"] .= "https:";
else
	$GLOBALS["GOTMLS"]["tmp"]["protocol"] = "http:";
$GOTMLS_plugin_home = $GLOBALS["GOTMLS"]["tmp"]["protocol"].'//wordpress.'.$GLOBALS["GOTMLS"]["tmp"]["default_ext"];
$GOTMLS_update_home = $GLOBALS["GOTMLS"]["tmp"]["protocol"]."//gotmls.net/";
$definition_version = "A0000";
$GOTMLS_definitions_array = maybe_unserialize(GOTMLS_decode('YToyOntzOjk6InBvdGVudGlhbCI7YToxMTp7czo0OiJldmFsIjthOjI6e2k6MDtzOjU6IkNDSUdHIjtpOjE7czoyNzoiL1teYS16XC8nIl1ldmFsXCguK1wpWztdKi9pIjt9czo5OiJhdXRoX3Bhc3MiO2E6Mjp7aTowO3M6NToiQ0NJR0ciO2k6MTtzOjI0OiIvXCRhdXRoX3Bhc3NbID1cdF0rLis7L2kiO31zOjIxOiJkb2N1bWVudC53cml0ZSBpZnJhbWUiO2E6Mjp7aTowO3M6NToiQ0NJR0ciO2k6MTtzOjUyOiIvZG9jdW1lbnRcLndyaXRlXChbJyJdPGlmcmFtZSAuKzxcL2lmcmFtZT5bJyJdXCk7Ki9pIjt9czoxNToicHJlZ19yZXBsYWNlIC9lIjthOjI6e2k6MDtzOjU6IkNDSUdHIjtpOjE7czo1MDoiL3ByZWdfcmVwbGFjZVsgXHRdKlwoLitbXC9cI1x8XVtpXSplW2ldKlsnIl0uK1wpL2kiO31zOjIwOiJleGVjIHN5c3RlbSBwYXNzdGhydSI7YToyOntpOjA7czo1OiJDQ0lHRyI7aToxO3M6NTg6Ii9cPFw_KC4rPylleGVjXCgoLis_KXN5c3RlbVwoKC4rPylwYXNzdGhydVwoLitmd3JpdGVcKC4rL3MiO31zOjI5OiJFeHRlcm5hbCBSZWRpcmVjdCBSZXdyaXRlUnVsZSI7YToyOntpOjA7czo1OiJDQ1ZFNCI7aToxO3M6MzA6Ii9SZXdyaXRlUnVsZSBbXiBdKyBodHRwXDpcL1wvLyI7fXM6MzU6Im5vIGVycm9yX3JlcG9ydGluZyBsb25nIGxpbmVzIGFsb25lIjthOjI6e2k6MDtzOjU6IkQzNUJhIjtpOjE7czo3OToiLzxcPyhwaHApKltcclxuXHQgXEBdKmVycm9yX3JlcG9ydGluZ1woMFwpOy4rP1thLXowLTlcL1wtXD0nIlwuXF17MjAwMH0uKj9cPz4vaSI7fXM6MjI6InByb3RlY3RlZCBieSBjb3B5cmlnaHQiO2E6Mjp7aTowO3M6NToiRDhNQ3ciO2k6MTtzOjEzNjoiL1wvXCogVGhpcyBmaWxlIGlzIHByb3RlY3RlZCBieSBjb3B5cmlnaHQgbGF3IGFuZCBwcm92aWRlZCB1bmRlciBsaWNlbnNlLiBSZXZlcnNlIGVuZ2luZWVyaW5nIG9mIHRoaXMgZmlsZSBpcyBzdHJpY3RseSBwcm9oaWJpdGVkLiBcKlwvLyI7fXM6MTk6ImEgc3BhbiBjb2xvciBGMUVGRTQiO2E6Mjp7aTowO3M6NToiRDhSQVAiO2k6MTtzOjExODoiL1w8YSBbXlw-XStcPlw8c3BhbiBzdHlsZT0iY29sb3JcOlwjRjFFRkU0OyJcPiguKz8pXDxcL3NwYW5cPlw8XC9hXD5cPHNwYW4gc3R5bGU9ImNvbG9yXDpcI0YxRUZFNDsiXD4oLis_KVw8XC9zcGFuXD4vaSI7fXM6MTc6IlZhcmlhYmxlIEZ1bmN0aW9uIjthOjI6e2k6MDtzOjU6IkU4NTZMIjtpOjE7czo2NzoiLyg8IVxkKVwkW1wkXHtdKlthLXpcLVxfMC05XStbXH0gXHRdKihcW1teXF1dK1xdWyBcdF0qKSpcKC4qP1wpXDsvaSI7fXM6MTE6IlRhZ2dlZCBDb2RlIjthOjI6e2k6MDtzOjU6IkU0TE1HIjtpOjE7czoyNDoiL1wjKFx3KylcIy4rP1wjXC9cMVwjL2lzIjt9fXM6OToid2hpdGVsaXN0IjthOjI6e3M6MzoicGhwIjthOjE3OntpOjA7czo1OiJFN0VNdiI7czozODoiNTg3M2NkMWNlYTYxMDgyMDJkMjEzNDdmMDFmMDRkY2ZPODE3MjgiO3M6NToiRDc1OXAiO3M6Mzk6IjAxMzYzNzI4Yzg0M2ZmOTNlOTZiNjk4M2NlMzhlYmE2TzE5NTYxOCI7czo1OiJENUE4MyI7czozODoiZDVmM2M5Y2FmZjE0ZDU3Yzg2MDhkNzhkYjAwOTRiZTBPNzM2NDMiO3M6NToiRDc1RDkiO3M6Mzg6IjU3YWY0OTgxOGJiYjk0OWRjMGFjNjM4NjczODY1NWJiTzI1ODUyIjtzOjU6IkQ3SkQ5IjtzOjM4OiJkNDk0MDQyNjBkNzlhNGNjMzc1NWMwMGY1NWRlMjA4OU8yNTY2MiI7czo1OiJEOFY4QSI7czozNzoiODY2MWZlMmJmYTU5OTVmNTQ2YTMzMDQ3ZTkwMzg1NmNPMTEzNiI7czo1OiJESUNGQyI7czozODoiODEyNWQ0MmM0YmU1NDNmODc0ZWE1ZjZhMWI1YmRlNTVPMjU4OTQiO3M6NToiRElDRkQiO3M6Mzk6ImVkZGI1ZmRhNzRkNDFkYmRhYzAxODE2NzUzNmQ4ZDUzTzIzMTMzOCI7czo1OiJESUNGRSI7czozODoiYzE1YTRkNWMzODM0NDRiOTVkMjg1NTlmODM0ODExMWRPMjI1ODgiO3M6NToiRTFSMnYiO3M6Mzg6ImUyMDgzOWM1NTlhNjZjN2NmNjI4NjUzYmEyNDg0ZWFlTzI2Mzk1IjtzOjU6IkUxUjJ4IjtzOjM4OiJmMzM4MmVjMTVjMDMwYmQzMmUyOTNmYWYzNDk3ZTI1M08xMTIyNiI7czo1OiJFMjMwQyI7czozNzoiMjhhOTJmNDY0OThkMzJiOWE3NGM1ODQ3Zjc1YzkxMmVPNzM5OSI7czo1OiJFMjMwQyI7czozNzoiZjAwYWFmMDFmZjAyZDU3NTZjMjY3YmNmOTIwZTRjMjhPMTU0MCI7czo1OiJFMkFNZiI7czozODoiNTdjNjQ3ZDkzZmJkNDc4NjhiODdiOTIxYmVlNjNhZjhPMjYzNzYiO3M6NToiRTVFRG8iO3M6Mzk6IjhlMmFmNDg4NmRjODFhNWQ5Mjg5ODY1YmJiODEzZWQxTzE5NTYxNyI7czo1OiJFNUlOUCI7czozNzoiZjgwZDllZjRiN2JmZDllZjU0MmQ5MDg3YTFkYjJhZTlPMjAxMCI7czo1OiJFN0VNdiI7fXM6MjoianMiO2E6Mjk6e2k6MDtzOjU6IkU5RzhqIjtzOjM3OiI1NTRiYzc2YzcwMzUxMTg3ZjRjZTA1ZGRjMDEyYWFlZE80Nzc2IjtzOjU6IkQ2NjdYIjtzOjM3OiI5YTljMTI1ODE0Yjk3MTU5ODJkMjQ2YTFlZTc4MDg0Zk81MzQ1IjtzOjU6IkQ2NjdYIjtzOjM4OiJlMzZhMDg2MTIzNzU2NDEyMjkzMjMxYWVhZDE3ZjI0Zk8zNzYyOSI7czo1OiJENzVBSCI7czozNzoiYTM4YWM1MjY2OTI0OTM4YTRmZjU1MTQzNjljNmI0MGRPNDY3NCI7czo1OiJENzVBSiI7czozNzoiMTA0M2ExZDdkODRlZTU2Zjg4MzFhNjBjZGZjNWRjMjhPNzA3NyI7czo1OiJENzVEUyI7czozODoiNmVjMTUwYjc5ODdjYWFlZjk4YjU5Yzg3YjlmNDcxYmVPMTE4NDIiO3M6NToiRTFSMm4iO3M6Mzg6IjYxNDdjY2VlN2FlZjlkYzBjNmViMTBkOGQ3YjMxMWY5TzcwODgzIjtzOjU6IkUxUjJ3IjtzOjM3OiJiYTMyOTM5NzBlMTNiMDNhMmVhOTJmNWI2YjViZjU0NE8zMzc3IjtzOjU6IkUyMk5xIjtzOjM3OiI2M2IwYWVkOWIwMmY4NzlhNmUwMjk1ZmJlYTdkYjg1NE80NzAyIjtzOjU6IkUyMzBEIjtzOjM3OiJlZjQxODhjYjBiNjBhNzIwMTdmNGM4YTFlODQwYWIxZU8yOTUwIjtzOjU6IkUyNDlMIjtzOjM3OiJmYjhiZjY3ODVlNTVlOWUzOWJlYTU1MjYzNWM0MmE2NE8zMjcwIjtzOjU6IkUyNjBDIjtzOjM5OiJhY2IzMzMyOWI5ZWY4YWFiZDhiZDczMTQyNjgwM2U0ZU8yMzI0ODIiO3M6NToiRTI2MEUiO3M6Mzg6IjZjZWI2NDc1OTI1ODhiY2Y0NjNiZWZkOTQwOGUyN2FkTzEyMDI1IjtzOjU6IkUyNjBIIjtzOjM3OiI1YTMxODI3N2ZlZGY0OTFhMDMwMWUxNzdhOWVmMTBiM080OTA4IjtzOjU6IkUyNjBKIjtzOjM4OiJkYmMzODA4NDczZGVmMDBmY2U0NWZlNTY0ZGM3MmRjYk8xNDcyMCI7czo1OiJFMjYwSyI7czozNzoiYjk4OWE1YmQ4NGY2ZWJjYmMxMzkzZWMwMDNlNmU5OTFPNDk2OSI7czo1OiJFMjdFRyI7czozODoiMDMwYjgzODkzNzZhNDJmZjNkYTE4NmJmNjU4MDYyMTdPMTY1MzEiO3M6NToiRTI5RDIiO3M6Mzc6ImRlZjI1N2RiYjBhYjgwNWM0OTk2ZmQ4YWJiMWE2YjQ5TzY3MTciO3M6NToiRTJINW4iO3M6Mzg6Ijc0ZDkwMzA0OTY4M2U1YmJlYTljY2I3NTQ0YTQyYmNhTzE3NDEzIjtzOjU6IkU1RURxIjtzOjM4OiI2MDNiZDE0Mjk5ZjYxYTczMjliMmQzNTNiMmI1NmMyZk8zNzY4OSI7czo1OiJFNUVEcCI7czozNzoiMDQyNmIzOTc1NGFhNmJjNzY2ZDg5ZWE0YzQxYmJkMDZPMzQ1NyI7czo1OiJFNUVEeCI7czozODoiZWFkYzU4MzI1MTNkNTY3MDg4NGE5NzVjNmRlMTBmMDFPMTk2MTUiO3M6NToiRTdVTUEiO3M6Mzg6IjM4ZGJjYzkyNTUyOTM2ODgxMmY1YzJmYmNiMzg5NjE2TzE0OTY1IjtzOjU6IkU3VU1CIjtzOjM3OiJhMWMxODIyN2U2ZTkzNzk4YzQ5M2FlZDk2ZWU2Y2M4NE8zMjY3IjtzOjU6IkU3VU1CIjtzOjM3OiIwNzgzODhhNjQzMWFhNWIwODM4YTg3MzJkMTg3ZmUyOU84OTEzIjtzOjU6IkU4QUFwIjtzOjM3OiJmM2IxYjI4NDI0MzZmN2EzMTFiMzllNGVmNGI0N2Y1OU80MzUyIjtzOjU6IkU4QkJVIjtzOjM3OiJkNzA5NDA2MTlhOTlkNTU1MTE2MTY3ZDRmYjM5Y2ExNU80Mzk3IjtzOjU6IkU5Q0x4IjtzOjM4OiJjYmRiZmM5MTg0ZDI4YWM1NWY4M2MwYjBkZjQwZmQ0M083OTQxNCI7czo1OiJFOUc4aiI7fX191'));

function GOTMLS_file_put_contents($file, $content) {
	if (function_exists("file_put_contents"))
		return file_put_contents($file, $content);
	elseif ($fp = fopen($file, 'w')) {
		fwrite($fp, $content);
		fclose($fp);
		return true;
	} else
		return false;
}

function GOTMLS_scan_log() {
	global $wpdb;
	if ($rs = $wpdb->get_row("SELECT substring_index(option_name, '/', -1) AS `mt`, option_name, option_value FROM `$wpdb->options` where option_name like 'GOTMLS_scan_log/%' ORDER BY mt DESC LIMIT 1", ARRAY_A))
		$GOTMLS_scan_log = (isset($rs["option_name"])?get_option($rs["option_name"], array()):array());
	$units = array("seconds"=>60,"minutes"=>60,"hours"=>24,"days"=>365,"years"=>10);
	if (isset($GOTMLS_scan_log["scan"]["start"]) && is_numeric($GOTMLS_scan_log["scan"]["start"])) {
		$time = (time() - $GOTMLS_scan_log["scan"]["start"]);
		$ukeys = array_keys($units);
		for ($unit = $ukeys[0], $key=0; (isset($units[$ukeys[$key]]) && $key < (count($ukeys) - 1) && $time >= $units[$ukeys[$key]]); $unit = $ukeys[++$key])
			$time = floor($time/$units[$ukeys[$key]]);
		if (1 == $time)
			$unit = substr($unit, 0, -1);
		$LastScan = "started $time $unit ago";
		if (isset($GOTMLS_scan_log["scan"]["finish"]) && is_numeric($GOTMLS_scan_log["scan"]["finish"]) && ($GOTMLS_scan_log["scan"]["finish"] >= $GOTMLS_scan_log["scan"]["start"])) {
			$time = ($GOTMLS_scan_log["scan"]["finish"] - $GOTMLS_scan_log["scan"]["start"]);
			for ($unit = $ukeys[0], $key=0; (isset($units[$ukeys[$key]]) && $key < (count($ukeys) - 1) && $time >= $units[$ukeys[$key]]); $unit = $ukeys[++$key])
				$time = floor($time/$units[$ukeys[$key]]);
			if (1 == $time)
				$unit = substr($unit, 0, -1);
			$LastScan .= " and ran for $time $unit";
		} else
			$LastScan .= " and has not finish";
	} else
		$LastScan = "never started ";
	return "Last ".(isset($GOTMLS_scan_log["scan"]["type"])?$GOTMLS_scan_log["scan"]["type"]:"Scan")." $LastScan.";
}

function GOTMLS_get_URL($URL) {
	if (isset($_SERVER['HTTP_REFERER']))
		$SERVER_HTTP_REFERER = $_SERVER['HTTP_REFERER'];
	elseif (isset($_SERVER['HTTP_HOST']))
		$SERVER_HTTP_REFERER = 'HOST://'.$_SERVER['HTTP_HOST'];
	elseif (isset($_SERVER['SERVER_NAME']))
		$SERVER_HTTP_REFERER = 'NAME://'.$_SERVER['SERVER_NAME'];
	elseif (isset($_SERVER['SERVER_ADDR']))
		$SERVER_HTTP_REFERER = 'ADDR://'.$_SERVER['SERVER_ADDR'];
	else
		$SERVER_HTTP_REFERER = 'NULL://not.anything.com';
	$ReadFile = '';
	if (function_exists('curl_init')) {
		$curl_hndl = curl_init();
		curl_setopt($curl_hndl, CURLOPT_URL, $URL);
		curl_setopt($curl_hndl, CURLOPT_TIMEOUT, 30);
		curl_setopt($curl_hndl, CURLOPT_REFERER, $SERVER_HTTP_REFERER);
	    if (isset($_SERVER['HTTP_USER_AGENT']))
	    	curl_setopt($curl_hndl, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
		curl_setopt($curl_hndl, CURLOPT_HEADER, 0);
		curl_setopt($curl_hndl, CURLOPT_RETURNTRANSFER, TRUE);
		$ReadFile = curl_exec($curl_hndl);
		curl_close($curl_hndl);
	}
	if (strlen($ReadFile) == 0 && function_exists('file_get_contents'))
		$ReadFile = @file_get_contents($URL).'';
	return $ReadFile;
}