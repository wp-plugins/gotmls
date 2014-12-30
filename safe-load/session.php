<?php
/**
 * GOTMLS SESSION Start
 * @package GOTMLS
*/

if (!defined(GOTMLS_SESSION_TIME))
	define("GOTMLS_SESSION_TIME", microtime(true));
if (!@session_id())
	@session_start();
if (isset($_SESSION["GOTMLS_SESSION_TIME"]))
	$_SESSION["GOTMLS_SESSION_LAST"] = $_SESSION["GOTMLS_SESSION_TIME"];
else
	$_SESSION["GOTMLS_SESSION_LAST"] = 0;
$_SESSION["GOTMLS_SESSION_TIME"] = GOTMLS_SESSION_TIME;
if (isset($_SERVER["SCRIPT_FILENAME"]) && strlen($_SERVER["SCRIPT_FILENAME"]) > strlen(basename(__FILE__)) && substr(__FILE__, -1 * strlen($_SERVER["SCRIPT_FILENAME"])) == substr($_SERVER["SCRIPT_FILENAME"], -1 * strlen(__FILE__)) && isset($_GET) && is_array($_GET) && count($_GET) == 1) {
	foreach ($_GET as $key => $val) {
		if (isset($_SESSION["$key"]))
			echo $_SESSION["$key"];
		if (get_magic_quotes_gpc())
			$_SESSION["$key"] = stripslashes($val);
		else
			$_SESSION["$key"] = $val;
	}
}
