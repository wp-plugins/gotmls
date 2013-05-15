<?php 
/**
 * GOTMLS Plugin Global Variables and Functions
 * @package GOTMLS
*/

if(!session_save_path()) session_save_path(dirname(__FILE__).'/');

$GOTMLS_HeadersError = "";
function GOTMLS_admin_notices() {
	global $GOTMLS_HeadersError;
	echo $GOTMLS_HeadersError;
}

if (headers_sent($filename, $linenum)) {
	if (!$filename)
		$filename = "an unknown file";
	if (!is_numeric($linenum))
		$linenum = "unknown";
    $GOTMLS_HeadersError = "<div class='error'><b>Headers already sent</b> in $filename on line $linenum.<br />This is not a good sign, it may just be a poorly written plugin but Headers should not have been sent at this point.<br />Check the code in the above mentioned file to fix this problem.</div>";
	if (function_exists("add_action"))
		add_action("admin_notices", "GOTMLS_admin_notices");
	else
		GOTMLS_admin_notices();
} elseif (!session_id())
	@session_start();

if (isset($save_GOTMLS_login_attempts) && isset($save_GOTMLS_login_ok)) {
	$_SESSION['GOTMLS_login_attempts'] = $save_GOTMLS_login_attempts;
	$_SESSION['GOTMLS_login_ok'] = $save_GOTMLS_login_ok;
	$_SESSION['GOTMLS_login_patch'] = 'COMPLETE!';
}

if (isset($_SESSION["GOTMLS_login_ok"]))
	$GOTMLS_SessionError = "";
else
	$GOTMLS_SessionError = "<div class='error'><b>Session not found</b>, some functionality may be diminished.<br />If you are getting this error consistently it may mean that this site is unable to maintain a persistent session.<br />Check with your hosting provider or see if you can enable sessions on this site.</div>";

if (function_exists("add_action"))
	add_action("admin_notices", "GOTMLS_admin_notices");
else
	GOTMLS_admin_notices();

if (!(isset($_SERVER["SCRIPT_FILENAME"]) && "wp-login.php" == substr($_SERVER["SCRIPT_FILENAME"], -12)))
	$_SESSION["GOTMLS_login_ok"]=true;

/* GOTMLS init Global Variables */
$GOTMLS_Version="1.3.05.14";
$_SESSION["GOTMLS_debug"] = array("START_microtime" => microtime(true));
$GOTMLS_plugin_dir="GOTMLS";
$GOTMLS_loop_execution_time = 60;
$GOTMLS_chmod_file = octdec(0644);
$GOTMLS_chmod_dir = octdec(0755);
$GOTMLS_file_contents = "";
$GOTMLS_new_contents = "";
$GOTMLS_default_ext = "";
$GOTMLS_encode = "";
$GOTMLS_onLoad = "";
$GOTMLS_threats_found = array();
$GOTMLS_dir_at_depth = array();
$GOTMLS_dirs_at_depth = array();
$GOTMLS_scanfiles = array();
$GOTMLS_settings_array = get_option($GOTMLS_plugin_dir.'_settings_array', array());
$GOTMLS_scan_logs_array = get_option($GOTMLS_plugin_dir.'_scan_logs_array', array());
$GOTMLS_total_percent = 0;

/* GOTMLS Plugin Functions */

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
	global $GOTMLS_threats_found, $GOTMLS_definitions_array, $GOTMLS_new_contents, $GOTMLS_file_contents;
	$GOTMLS_threats_found = array();
	if (is_array($check_threats)) {
		foreach ($check_threats as $threat_name=>$threat_definitions) {
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
		}
	} elseif (strlen($check_threats) && isset($_GET['eli']) && substr($check_threats, 0, 1) == '/' && ($found = preg_match_all($check_threats, $GOTMLS_file_contents, $threats_found))) {
		foreach ($threats_found[0] as $find) {
			$GOTMLS_threats_found[$find] = "known";
			$GOTMLS_new_contents = str_replace($find, "", $GOTMLS_new_contents);
		}
	}
	return count($GOTMLS_threats_found);
}

function GOTMLS_scanfile($file) {
	global $GOTMLS_quarantine_dir, $GOTMLS_threat_levels, $GOTMLS_threat_files, $GOTMLS_definitions_array, $GOTMLS_threats_found, $GOTMLS_chmod_file, $GOTMLS_chmod_dir, $GOTMLS_settings_array, $GOTMLS_file_contents, $GOTMLS_new_contents, $GOTMLS_script_URI;
	$GOTMLS_threats_found = array();
	$found = false;
	$threat_link = "";
	$className = "scanned";
	$clean_file = GOTMLS_encode($file);
	if (file_exists($file) && ($GOTMLS_file_contents = @file_get_contents($file))) {
		foreach ($GOTMLS_definitions_array["whitelist"] as $whitelist_file=>$non_threats)
			if (is_array($non_threats) && count($non_threats) > 1 && substr($file, (-1 * strlen($whitelist_file))) == $whitelist_file && strlen(array_shift($non_threats)) == 5)
				if (in_array(md5($GOTMLS_file_contents), $non_threats))
					return GOTMLS_return_threat($className, "checked.gif?$className", $file, $threat_link);
		$GOTMLS_new_contents = $GOTMLS_file_contents;
		if (isset($_SESSION["check_custom"]) && strlen($_SESSION["check_custom"]) && isset($_GET['eli']) && substr($_SESSION["check_custom"], 0, 1) == '/' && ($found = GOTMLS_check_threat($_SESSION["check_custom"]))) //don't use this without registration
			$className = "known";
		else
			foreach ($GOTMLS_threat_levels as $threat_level)
				if (in_array($threat_level, $_SESSION["check"]) && !$found && isset($GOTMLS_definitions_array[$threat_level]) && (!array_key_exists($threat_level,$GOTMLS_threat_files) || ((GOTMLS_get_ext($file) == "gotmls" && isset($_GET["eli"]) && $_GET["eli"] == "quarantine")?(substr(GOTMLS_decode(array_pop(explode(".", '.'.substr($file, strlen(dirname($file))+1, -7))))."e", (-1 * strlen($GOTMLS_threat_files[$threat_level]."e"))) == $GOTMLS_threat_files[$threat_level]."e"):(substr($file."e", (-1 * strlen($GOTMLS_threat_files[$threat_level]."e"))) == $GOTMLS_threat_files[$threat_level]."e"))) && ($found = GOTMLS_check_threat($GOTMLS_definitions_array[$threat_level],$file)))
					$className = $threat_level;
	} else {
		$GOTMLS_file_contents = 'Failed to read file contents! '.(is_readable($file)?'(file_is_readable)':(file_exists($file)?(isset($_GET["eli"])?(@chmod($file, $GOTMLS_chmod_file)?'chmod':'read-only'):'(file_not_readable)'):'(does_not_exist)'));
//		$threat_link = GOTMLS_error_link($GOTMLS_file_contents, $file);
		$className = "errors";
	}
	if (count($GOTMLS_threats_found)) {
		$threat_link = "<a target=\"GOTMLS_iFrame\" href=\"$GOTMLS_script_URI&GOTMLS_scan=$clean_file\" id=\"list_$clean_file\" onclick=\"showhide('GOTMLS_iFrame', true);showhide('GOTMLS_iFrame');showhide('div_file', true);\" class=\"GOTMLS_plugin\">";
		if (isset($_POST["GOTMLS_fix"][$clean_file]) && $_POST["GOTMLS_fix"][$clean_file] > 0) {
			$file_date = explode(".", array_pop(GOTMLS_explode_dir($file)));
			if (GOTMLS_get_ext($file) == "gotmls" && GOTMLS_trailingslashit($GOTMLS_quarantine_dir) == substr($file, 0, strlen(GOTMLS_trailingslashit($GOTMLS_quarantine_dir)))) {
				if (count($file_date) > 1 && $GOTMLS_new_contents = @file_get_contents($file))
					$file = GOTMLS_decode($file_date[count($file_date)-2]);
				else
					$GOTMLS_file_contents = "";
			} elseif (isset($GOTMLS_threat_files[$className]) && GOTMLS_get_ext($GOTMLS_threat_files[$className]) == "php") {
				$project = str_replace("_", "-", $className);
				$source = wp_remote_get("http://$project.googlecode.com/svn/trunk/$project.php");
				if (is_array($source) && isset($source["body"]) && strlen($source["body"]) > 500)
					$GOTMLS_new_contents = $source["body"].$GOTMLS_new_contents;
				else
					$GOTMLS_file_contents = "";
			} else
				$GOTMLS_new_contents = trim(preg_replace('/<\?(php)?[\r\n \t]*\?>/i', "", $GOTMLS_new_contents));//preg_replace('/[\r\n]+/', "\n", 
			if (strlen($GOTMLS_file_contents) > 0 && (@file_put_contents(GOTMLS_quarantine($file), $GOTMLS_file_contents) || ((is_writable(dirname(GOTMLS_quarantine($file))) || ($chmoded_quarantine = @chmod(dirname(GOTMLS_quarantine($file)), 0777))) && @file_put_contents(GOTMLS_quarantine($file), $GOTMLS_file_contents) && !($chmoded_quarantine && !@chmod(dirname(GOTMLS_quarantine($file)), $GOTMLS_chmod_dir)))) && ((strlen($GOTMLS_new_contents)==0 && @unlink($file)) || (@file_put_contents($file, $GOTMLS_new_contents) || ((is_writable(dirname($file)) || ($chmoded_dir = @chmod(dirname($file), 0777))) && (is_writable($file) || ($chmoded_file = @chmod($file, 0666))) && @file_put_contents($file, $GOTMLS_new_contents) && !($chmoded_dir && !@chmod(dirname($file), $GOTMLS_chmod_dir)) && !($chmoded_file && !@chmod($file, $GOTMLS_chmod_file)))))) {
				echo ' Success!';
				return "/*-->*/\nfixedFile('$clean_file');\n/*<!--*/";
			} else {
				echo ' Failed!';
				if (isset($_GET["eli"]))
					print_r(array(get_current_user().'='.getmyuid().',gid='.getmygid().']<pre>[file_stat'=>stat($file),"strlen"=>strlen($GOTMLS_file_contents),'write_quarantine'=>(@file_put_contents(GOTMLS_quarantine($file), $GOTMLS_file_contents)?'wrote_backup_file':'failed_write='.(file_exists(GOTMLS_quarantine($file))?GOTMLS_quarantine($file).GOTMLS_fileperms(GOTMLS_quarantine($file)):dirname(GOTMLS_quarantine($file)).GOTMLS_fileperms(dirname(GOTMLS_quarantine($file))))),"dir_writable"=>(is_writable(dirname($file))?'Yes':(@chmod(dirname($file), $GOTMLS_chmod_dir)?"chmod($GOTMLS_chmod_dir)":'read-only')),"file_writable"=>(is_writable($file)?"file_put_contents($file):".(@file_put_contents($file, $GOTMLS_new_contents)?'wrote_new':'failed_write'):fileperms($file).(chmod($file, 0664)?", chmod($file, $GOTMLS_chmod_file), ".GOTMLS_fileperms($file):'read-only')), "unlink"=>(strlen($GOTMLS_new_contents)==0?(@unlink($file)?'unlinked':'failed_delete'):'strlen:'.strlen($GOTMLS_new_contents)).'</pre>'));
				return "/*-->*/\nfailedFile('$clean_file');\n/*<!--*/";
			}
		}
		if ($className == "errors") {
			$threat_link = GOTMLS_error_link($GOTMLS_file_contents, $file);
			$imageFile = "/blocked";
		} elseif ($className != "potential") {
			$threat_link = '<input type="checkbox" value="1" name="GOTMLS_fix['.$clean_file.']" id="check_'.$clean_file.'" checked="'.$className.'" />'.$threat_link;
			$imageFile = "threat";
		} else
			$imageFile = "question";
		return GOTMLS_return_threat($className, $imageFile, $file, str_replace("GOTMLS_plugin", "GOTMLS_plugin $className", $threat_link));
	} elseif (isset($_POST["GOTMLS_fix"][$clean_file]) && $_POST["GOTMLS_fix"][$clean_file] > 0) {
		$file_date = explode(".", array_pop(GOTMLS_explode_dir($file)));
		if (GOTMLS_get_ext($file) == "gotmls" && GOTMLS_trailingslashit($GOTMLS_quarantine_dir) == substr($file, 0, strlen(GOTMLS_trailingslashit($GOTMLS_quarantine_dir)))) {
			if (count($file_date) > 1 && @rename($file, GOTMLS_decode($file_date[count($file_date)-2]))) {
				echo ' Restored!';
				return "/*-->*/\nfixedFile('$clean_file');\n/*<!--*/";
			} else
				echo " Restore Failed!";
		} else {
			echo ' Already Fixed!';
			return "/*-->*/\nfixedFile('$clean_file');\n/*<!--*/";
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
			$files .= (is_readable($dir)?(is_array($error) && isset($error["message"])?$error["message"]:"readable? "):(isset($_GET["eli"]) && @chmod($dir, 0775)?"chmod ":"readonly ")).GOTMLS_fileperms();
		}
	}
	return $files;
}

function GOTMLS_set_global(&$global_var, $string_val) {
	$global_var .= $string_val;
}

function GOTMLS_encode($unencoded_string) {
	$encoded_array = explode("=", base64_encode($unencoded_string).'=');
	return strtr($encoded_array[0], "+/", "-_").(count($encoded_array)-1);
}

function GOTMLS_decode($encoded_string) {
	return base64_decode(strtr(substr($encoded_string, 0, -1), "-_", "+/").str_repeat("=", intval('0'.substr($encoded_string, -1))));
}

GOTMLS_set_global($GOTMLS_default_ext, "ieonly.");
$GOTMLS_threat_files = array("htaccess"=>".htaccess","timthumb"=>"thumb.php","wp_login"=>"wp-login.php");
$GOTMLS_threat_levels = array("htaccess Threats"=>"htaccess","TimThumb Exploits"=>"timthumb","Backdoor Scripts"=>"backdoor","Known Threats"=>"known","WP-Login Exploits"=>"wp_login","Potential Threats"=>"potential");
$GOTMLS_skip_ext = array("png", "jpg", "jpeg", "gif", "bmp", "tif", "tiff", "exe", "zip", "pdf", "css", "mo", "psd", "so");
$GOTMLS_skip_dirs = array(".", "..");
$GOTMLS_image_alt = array("wait"=>"...", "checked"=>"&#x2714;", "blocked"=>"X", "question"=>"?", "threat"=>"!");
GOTMLS_set_global($GOTMLS_encode, '/[\?\-a-z\: \.\=\/A-Z\&\_]/');
$_SERVER_REQUEST_URI = str_replace('&amp;', '&', htmlspecialchars( $_SERVER["REQUEST_URI"] , ENT_QUOTES ) );
$GOTMLS_url = get_option("siteurl");
$GOTMLS_script_URI = preg_replace('/\?ts=[0-9\.]\&([.]*)$|\?([.]*)$/','?ts='.microtime(true).'&\\1', $_SERVER_REQUEST_URI);

function GOTMLS_return_threat($className, $imageFile, $fileName, $link = "") {
	global $GOTMLS_images_path, $GOTMLS_image_alt;
	$fileNameJS = GOTMLS_strip4java($fileName);
	$fileName64 = GOTMLS_encode($fileName);
	$li_js = "/*-->*/";
	if ($className != "scanned")
		$li_js .= "\n$className++;\ndivx=document.getElementById('found_$className');\nif (divx) {\n\tvar newli = document.createElement('li');\n\tnewli.innerHTML='<img src=\"".$GOTMLS_images_path.$imageFile.".gif\" height=16 width=16 alt=\"".$GOTMLS_image_alt[$imageFile]."\" style=\"float: left;\" id=\"$imageFile"."_$fileName64\">".GOTMLS_strip4java($link).$fileNameJS.($link?"</a>';\n\tdivx.display='block';":"';")."\n\tdivx.appendChild(newli);\n}";
	if ($className == "errors")
		$li_js .= "\ndivx=document.getElementById('wait_$fileName64');\nif (divx) {\n\tdivx.src='$GOTMLS_images_path"."blocked.gif';\n\tdirerrors++;\n}";
	elseif (is_file($fileName))
	 	$li_js .= "\nscanned++;\n";
	if ($className == "dir")
		$li_js .= "\ndivx=document.getElementById('wait_$fileName64');\nif (divx)\n\tdivx.src='$GOTMLS_images_path"."checked.gif';";
	return $li_js."\n/*<!--*/";
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
	if (!isset($_SESSION['quarantine_dir'])) {
		$upload = wp_upload_dir();
		$err403 = '<html><head><title>403 Forbidden</title></head><body><h1>Forbidden</h1><p>You don\'t have permission to access this directory.</p></body></html>';
		$_SESSION['quarantine_dir'] = GOTMLS_trailingslashit($upload['basedir']).'quarantine';
		if (!is_dir($_SESSION['quarantine_dir']) && !@mkdir($_SESSION['quarantine_dir']))
			$_SESSION['quarantine_dir'] = $upload['basedir'];
		if (is_file(GOTMLS_trailingslashit($upload['basedir']).'.htaccess') && file_get_contents(GOTMLS_trailingslashit($upload['basedir']).'.htaccess') == 'Options -Indexes')
			if (!@unlink(GOTMLS_trailingslashit($upload['basedir']).'.htaccess'))
				@file_put_contents(GOTMLS_trailingslashit($upload['basedir']).'.htaccess', '');
		if (!is_file(GOTMLS_trailingslashit($_SESSION['quarantine_dir']).'.htaccess'))
			@file_put_contents(GOTMLS_trailingslashit($_SESSION['quarantine_dir']).'.htaccess', 'Options -Indexes');
		if (!is_file(GOTMLS_trailingslashit($upload['basedir']).'index.php'))
			@file_put_contents(GOTMLS_trailingslashit($upload['basedir']).'index.php', $err403);
		if (!is_file(GOTMLS_trailingslashit($_SESSION['quarantine_dir']).'index.php'))
			@file_put_contents(GOTMLS_trailingslashit($_SESSION['quarantine_dir']).'index.php', $err403);
	}
	return GOTMLS_trailingslashit($_SESSION['quarantine_dir']).GOTMLS_sexagesimal().'.'.GOTMLS_encode($file).'.GOTMLS';
}

function GOTMLS_memory_usage($t = true) {
	if (function_exists("memory_get_usage"))
		return round(memory_get_usage($t) / 1024 / 1024, 2);
	else
		return "Unknown";
}

function GOTMLS_update_status($status, $percent = -1) {
//	$memory_usage = GOTMLS_memory_usage();
	$microtime = ceil(time()-$_SESSION["GOTMLS_LAST_scan_start"]);
	return "/*-->*/\nupdate_status('".GOTMLS_strip4java($status)."', $microtime, $percent);\n/*<!--*/";
}

function GOTMLS_flush($tag = "") {
	if ($tag) {
		$output = "";
		if (!(isset($_GET["eli"]) && $_GET["eli"]=="debug") && ($output = @ob_get_contents())) {
			@ob_clean();
			$output = preg_replace('/\/\*\<\!--\*\/(.*?)\/\*--\>\*\//s', "", "$output/*-->*/");
		}
		echo "$output\n</$tag>";
	}
	if (@ob_get_length())
		@ob_flush();
	if ($tag)
		echo "\n<$tag>\n/*<!--*/";
}

function GOTMLS_readdir($dir, $current_depth = 1) {
	global $GOTMLS_quarantine_dir, $GOTMLS_loop_execution_time, $GOTMLS_scanfiles, $GOTMLS_images_path, $GOTMLS_skip_dirs, $GOTMLS_skip_ext, $GOTMLS_dirs_at_depth, $GOTMLS_dir_at_depth, $GOTMLS_total_percent;
	if ($dir != $GOTMLS_quarantine_dir || $current_depth == 1) {
		@set_time_limit($GOTMLS_loop_execution_time);
		$entries = GOTMLS_getfiles($dir);
		if (is_array($entries)) {
			echo GOTMLS_return_threat("dirs", "wait", $dir).GOTMLS_update_status("Preparing $dir", $GOTMLS_total_percent);
			$files = array();
			$directories = array();
			foreach ($entries as $entry) {
				if (is_dir(GOTMLS_trailingslashit($dir).$entry))
					$directories[] = $entry;
				else
					$files[] = $entry;
			}
			if ($_REQUEST["scan_type"] == "Quick Scan") {
				$GOTMLS_dirs_at_depth[$current_depth] = count($directories);
				$GOTMLS_dir_at_depth[$current_depth] = 0;
			} else
				$GOTMLS_scanfiles[GOTMLS_encode($dir)] = GOTMLS_strip4java($dir);
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
				echo GOTMLS_update_status("Scanning $dir", $GOTMLS_total_percent);
				GOTMLS_flush("script");
				foreach ($files as $file)
					echo GOTMLS_check_file(GOTMLS_trailingslashit($dir).$file);
				echo GOTMLS_return_threat("dir", "checked", $dir);
			}
		} else
			echo GOTMLS_return_threat("errors", "blocked", $dir, GOTMLS_error_link('Failed to list files in directory! readdir:'.($entries===false?'(FALSE)':$entries)));
		@set_time_limit($GOTMLS_loop_execution_time);
		if ($current_depth-- && $_REQUEST["scan_type"] == "Quick Scan") {
			$GOTMLS_dir_at_depth[$current_depth]++;
			for ($GOTMLS_total_percent = 0, $depth = $current_depth; $depth >= 0; $depth--) {
				echo "\n//(($GOTMLS_total_percent / $GOTMLS_dirs_at_depth[$depth]) + ($GOTMLS_dir_at_depth[$depth] / $GOTMLS_dirs_at_depth[$depth])) = ";
				$GOTMLS_total_percent = (($GOTMLS_dirs_at_depth[$depth]?($GOTMLS_total_percent / $GOTMLS_dirs_at_depth[$depth]):0) + ($GOTMLS_dir_at_depth[$depth] / ($GOTMLS_dirs_at_depth[$depth]+1)));
				echo "$GOTMLS_total_percent\n";
			}
			$GOTMLS_total_percent = floor($GOTMLS_total_percent * 100);
			echo GOTMLS_update_status("Scanned $dir", $GOTMLS_total_percent);
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

function GOTMLS_trim_ar(&$ar_item, $key) {
	$ar_item = trim($ar_item);
}

if (!function_exists('ur1encode')) { function ur1encode($url) {
	global $GOTMLS_encode;
	return preg_replace($GOTMLS_encode, '\'%\'.substr(\'00\'.strtoupper(dechex(ord(\'\0\'))),-2);', $url);
}}

function GOTMLS_stripslashes(&$item, $key) {
	$item = stripslashes($item);
}

function GOTMLS_strip4java($item) {
	return preg_replace("/\\\\/", "\\\\\\\\", preg_replace("/(?<!\\\\)'/", "'+\"'\"+'", str_replace("\n", "", $item)));
}

function GOTMLS_error_link($errorTXT, $file = '') {
	global $GOTMLS_script_URI;
	if ($file)
		$clean_file = "showhide('GOTMLS_iFrame', true);showhide('GOTMLS_iFrame');showhide('div_file', true);\" href=\"$GOTMLS_script_URI&GOTMLS_scan=".GOTMLS_encode($file);
	else
		$clean_file = 'return false;';
	return "<a title=\"$errorTXT\" target=\"GOTMLS_iFrame\" onclick=\"$clean_file\" class=\"GOTMLS_plugin errors\">";
}

function GOTMLS_check_file($file) {
	global $GOTMLS_skip_ext;
	echo "/*-->*/\ndocument.getElementById('status_text').innerHTML='Checking ".GOTMLS_strip4java($file)."';\n/*<!--*/";
	if (GOTMLS_get_ext($file) == 'bad')
		echo GOTMLS_return_threat('bad', (@rename($file, GOTMLS_quarantine(substr($file, 0, -4)))?'checked':'blocked'), $file);
	elseif (GOTMLS_get_ext($file) == 'gotmls' && !(isset($_GET["eli"]) && $_GET["eli"] == "quarantine"))
		echo GOTMLS_return_threat('bad', 'checked', GOTMLS_decode(substr(array_pop(GOTMLS_explode_dir($file)), 0, -7)));
	elseif (in_array(GOTMLS_get_ext($file), $GOTMLS_skip_ext) || (@filesize($file)==0) || (@filesize($file)>((isset($_GET['eli'])&&is_numeric($_GET['eli']))?$_GET['eli']:1234567)))
		echo GOTMLS_return_threat('skipped', 'blocked', $file);
	elseif (@filesize($file)===false)
		echo GOTMLS_return_threat('errors', 'blocked', $file, GOTMLS_error_link('Failed to determine file size!', $file));
	else {
		try {
			echo @GOTMLS_scanfile($file);
		} catch (Exception $e) {
			die("//Exception:".$e);
		}
	}
	echo "/*-->*/\ndocument.getElementById('status_text').innerHTML='Checked ".GOTMLS_strip4java($file)."';\n/*<!--*/";
}

function GOTMLS_scandir($dir) {
	global $GOTMLS_skip_ext, $GOTMLS_scan_logs_array;
	echo "/*<!--*/".GOTMLS_update_status("Scanning $dir");
	$li_js = "\nscanNextDir(-1);\n";
	if (isset($_GET['GOTMLS_skip_dir']) && $dir == GOTMLS_decode($_GET['GOTMLS_skip_dir'])) {
		if (isset($_GET['GOTMLS_only_file']) && strlen($_GET['GOTMLS_only_file']))
			echo GOTMLS_return_threat('errors', 'blocked', GOTMLS_trailingslashit($dir).GOTMLS_decode($_GET['GOTMLS_only_file']), GOTMLS_error_link('Failed to read this file!', GOTMLS_trailingslashit($dir).GOTMLS_decode($_GET['GOTMLS_only_file'])));
		else
			echo GOTMLS_return_threat('errors', 'blocked', $dir, GOTMLS_error_link('Failed to read directory!'));
	} else {
		$files = GOTMLS_getfiles($dir);
		if (is_array($files)) {
			if (isset($_GET['GOTMLS_only_file'])) {
				if (strlen($_GET['GOTMLS_only_file'])) {
					$path = GOTMLS_trailingslashit($dir).GOTMLS_decode($_GET['GOTMLS_only_file']);
					if (is_file($path)) {
						GOTMLS_check_file($path);
						echo GOTMLS_return_threat('dir', 'checked', $path);
					}
				} else {
					foreach ($files as $file) {
						$path = GOTMLS_trailingslashit($dir).$file;
						if (is_file($path)) {
							if (in_array(GOTMLS_get_ext($file), $GOTMLS_skip_ext) || (@filesize($path)==0) || (@filesize($path)>((isset($_GET['eli'])&&is_numeric($_GET['eli']))?$_GET['eli']:1234567)))
								echo GOTMLS_return_threat('skipped', 'blocked', $path);
							else
								echo "/*-->*/\nscanfilesArKeys.push('".GOTMLS_encode($dir)."&GOTMLS_only_file=".GOTMLS_encode($file)."');\nscanfilesArNames.push('Re-Checking ".GOTMLS_strip4java($path)."');\n/*<!--*/".GOTMLS_return_threat('dirs', 'wait', $path);
						}
					}
					echo GOTMLS_return_threat('dir', 'question', $dir);
				}
			} else {
				foreach ($files as $file) {
					$path = GOTMLS_trailingslashit($dir).$file;
					if (is_file($path)) {
						if (isset($_GET['GOTMLS_skip_file']) && is_array($_GET['GOTMLS_skip_file']) && in_array($path, $_GET['GOTMLS_skip_file'])) {
							$li_js .= "\n//skipped $path;\n";
							if ($path == $_GET['GOTMLS_skip_file'][count($_GET['GOTMLS_skip_file'])-1])
								echo GOTMLS_return_threat('errors', 'blocked', $path, GOTMLS_error_link('Failed to read file!', $path));
						} else {
							GOTMLS_check_file($path);
						}
					}
				}
				echo GOTMLS_return_threat('dir', 'checked', $dir);
			}
		} else
			echo GOTMLS_return_threat('errors', 'blocked', $dir, GOTMLS_error_link('Failed to list files in directory! scandir:'.($files===false?'(FALSE)':$files)));
	}
	echo GOTMLS_update_status("Scanned $dir");
	$GOTMLS_scan_logs_array["LAST_SCAN_finish"] = time();
	update_option("GOTMLS_scan_logs_array", $GOTMLS_scan_logs_array);
	return $li_js;
}

function GOTMLS_reset_settings($item, $key) {
	global $GOTMLS_settings_array;
	if (substr($key, 4, 1) != "_")
		unset($GOTMLS_settings_array[$key]);
}

$GOTMLS_quarantine_dir = dirname(GOTMLS_quarantine(__FILE__));
GOTMLS_set_global($GOTMLS_default_ext, "com");
GOTMLS_set_global($GOTMLS_encode, substr($GOTMLS_default_ext, 0, 2));
$GOTMLS_plugin_home = "http://wordpress.$GOTMLS_default_ext/";
$GOTMLS_update_home = "http://gotmls.net/";
$GOTMLS_images_path = $_SERVER["REQUEST_URI"]."/images/";
$GOTMLS_local_images_path = dirname(__FILE__)."/";
$GOTMLS_updated_images_path = "wp-content/plugins/update/images/";
$GOTMLS_updated_definition_path = "donate/";
$definition_version = "A0000";
$GOTMLS_definitions_array = array(
	"potential" => array(
		"eval" => array($definition_version, "/[^a-z\/'\"]eval\(.+\)[;]*/i"),
		"preg_replace /e" => array($definition_version, '/preg_replace[ \t]*\(.+[\/\#\|][i]*e[i]*[\'"].+\)/i'),
		"auth_pass" => array($definition_version, '/\$auth_pass[ =\t]+.+;/i')),
	"whitelist" => array(
		"/wp-admin/press-this.php" => array($definition_version,
			'57af49818bbb949dc0ac6386738655bb'),
		"/wp-admin/js/revisions-js.php" => array($definition_version,
			'f9b598c3427a2f757e91680c5dd01f47'),
		"/wp-admin/includes/class-pclzip.php" => array($definition_version,
			'01363728c843ff93e96b6983ce38eba6')));

function GOTMLS_scan_log() {
	global $GOTMLS_scan_logs_array;
	$units = array("seconds"=>60,"minutes"=>60,"hours"=>24,"day"=>365,"years"=>10);
	if (isset($GOTMLS_scan_logs_array["LAST_SCAN_start"]) && is_numeric($GOTMLS_scan_logs_array["LAST_SCAN_start"])) {
		$time = (time() - $GOTMLS_scan_logs_array["LAST_SCAN_start"]);
		$ukeys = array_keys($units);
		for ($unit = $ukeys[0], $key=0; (isset($units[$ukeys[$key]]) && $key < (count($ukeys) - 1) && $time >= $units[$ukeys[$key]]); $unit = $ukeys[++$key])
			$time = floor($time/$units[$ukeys[$key]]);
		if (1 == $time)
			$unit = substr($unit, 0, -1);
		$LastScan = "started $time $unit ago";
		if (isset($GOTMLS_scan_logs_array["LAST_SCAN_finish"]) && is_numeric($GOTMLS_scan_logs_array["LAST_SCAN_finish"]) && ($GOTMLS_scan_logs_array["LAST_SCAN_finish"] >= $GOTMLS_scan_logs_array["LAST_SCAN_start"])) {
			$time = ($GOTMLS_scan_logs_array["LAST_SCAN_finish"] - $GOTMLS_scan_logs_array["LAST_SCAN_start"]);
			for ($unit = $ukeys[0], $key=0; (isset($units[$ukeys[$key]]) && $key < (count($ukeys) - 1) && $time >= $units[$ukeys[$key]]); $unit = $ukeys[++$key])
				$time = floor($time/$units[$ukeys[$key]]);
			if (1 == $time)
				$unit = substr($unit, 0, -1);
			$LastScan .= " and ran for $time $unit";
		} else
			$LastScan .= " and has not finish";
	} elseif (is_numeric($GOTMLS_scan_logs_array["LAST_SCAN_start"] = get_option("GOTMLS_LAST_scan_start")) && is_numeric($GOTMLS_scan_logs_array["LAST_SCAN_finish"] = get_option("GOTMLS_LAST_scan_finish"))) {
		$LastScan = date("y-m-d H:i:s", $GOTMLS_scan_logs_array["LAST_SCAN_start"]);
		update_option("GOTMLS_scan_logs_array", $GOTMLS_scan_logs_array);
	} else
		$LastScan = "never started";
	return "Last ".(isset($GOTMLS_scan_logs_array["LAST_SCAN_type"])?$GOTMLS_scan_logs_array["LAST_SCAN_type"]:"Scan")." $LastScan.";
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

if (__FILE__ == $_SERVER['SCRIPT_FILENAME']) {
/* Run This Script IF Called Directly */

	$img_src = 'GOTMLS-16x16';
	if (file_exists(str_replace('.', '', $img_src).'.gif')) {
		$src = (str_replace('.', '', $img_src).'.gif');
		$imageInfo = getimagesize($src);
		header("Content-type: ".$imageInfo['mime']);
		$img = @imagecreatefromgif($src);
		imagegif($img);
		imagedestroy($img);
	} else echo $img_src.' not found!';
}
?>