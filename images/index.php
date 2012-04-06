<?php
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
function GOTMLS_listfiles($dir) {
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
$recovered_files = 0;
function GOTMLS_recoverfile($file) {
	global $recovered_files;
	$file_contents = '';
	if(file_exists($file)) {
		$file_contents = @file_get_contents($file);
		if (strlen($file_contents) > 0 && @file_put_contents(substr($file, 0, -4), $file_contents)) {
			$recovered_files++;
			return '<li>RECOVERED: '.substr($file, 0, -4).'</li>';
		} else
			return '<li>Failed to write to: '.substr($file, 0, -4).'</li>';
	}
}
function GOTMLS_recoverdir($dir, $current_depth = 0) {
	$dirs = explode('/', '/.'.$dir);
	$skip_dirs = array('.', '..');
	set_time_limit(30);
	if ((!in_array($dirs[count($dirs)-1], $skip_dirs)) && is_dir($dir)) {
//		echo '<li>Scanning '.($dir).' ...</li>';
		if (($files = GOTMLS_listfiles($dir)) !== false) {
			foreach ($files as $file) {
				$path = str_replace('//', '/', $dir.'/'.$file);
				if (is_dir($path)) {
					if (isset($_GET['scan_depth']) && is_numeric($_GET['scan_depth']) && ($_GET['scan_depth'] != $current_depth) && !in_array($file, $skip_dirs)) {
						$current_depth++;
						$current_depth = GOTMLS_recoverdir($path, $current_depth);
					}
				} else {
					if (substr($path, -4) == '.bad') {
						echo GOTMLS_recoverfile($path);
					}
				}
			}
		}
	}
	set_time_limit(30);
	$current_depth--;
	return $current_depth;
}
$img_src = 'GOTMLS-16x16';
$all_colors = Array('black' => Array(0,0,0),
					'red' => Array(255,0,0),
					'blue' => Array(0,0,255),
					'white' => Array(255,255,255),
					'trans' => Array(1,2,3));
import_request_variables("gP", "img_");
if (isset($_GET['ver']) && isset($_GET['key'])) {
	$img_src = 'blocked';
	$e = 'e';
	$update = GOTMLS_get_URL('http://gotmls.net/wp-content/plugins/update/images/index.php?ver='.$_GET['ver'].'&key='.$_GET['key'].'&p=GOTMLS');
	$test = preg_replace('/GOTMLS\_definitions\_version \= ([0-9]*);/'.$e, '\$chkDver = \1;', $update);
	if (is_numeric($chkDver) && ($chkDver > $_GET['ver'])) {
		if (@file_put_contents('definitions.php', $update))
			$img_src = 'checked';
	} else
		@file_put_contents('error.txt', $update);
}
if (isset($_GET['check_site']) && $_GET['check_site'] == 1) {
	echo '<html><body onload="location.replace(\''.$_SERVER['HTTP_REFERER'].'&check_site=1\');"><div id="check_site" style="position: absolute; top: 0px; left: 0px; width: 100%; height: 100%;"><img id="waiting" src="wait.gif"> Testing your site...</div></body></html>';
} else if (isset($_GET['scan_what']) && is_numeric($_GET['scan_what'])) {
	$dirs = explode('/', __file__);
	$dir = implode('/', array_slice($dirs, 0, -1 * (2 + $_GET['scan_what'])));
	echo '<html><body onload="document.getElementById(\'waiting\').src = \'checked.gif\';"><div id="check_site" style="position: absolute; top: 0px; left: 0px; width: 100%; height: 100%;"><img id="waiting" src="wait.gif"> Reverting repaired files to recover your site...';
	if (is_dir($dir))
		GOTMLS_recoverdir($dir);
	else echo '<li>'.($dir).' is not a directory!</li>';
	echo '<li>'.$recovered_files.' file recovered</li></div></body></html>';
} else if (file_exists(str_replace('.', '', $img_src).'.gif')) {
	$src = (str_replace('.', '', $img_src).'.gif');
	$imageInfo = getimagesize($src);
	header("Content-type: ".$imageInfo['mime']);
	$img = @imagecreatefromgif($src);
	imagegif($img);
	imagedestroy($img);
} else echo $img_src.' not found!';
?>
