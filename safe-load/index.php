<?php
/**
 * GOTMLS Brute-Force protections
 * @package GOTMLS
*/

if (!$_SESSION["GOTMLS_detected_attacks"])
	$_SESSION["GOTMLS_detected_attacks"] = '&attack[]=DIRECT_LOAD';
foreach (array("REMOTE_ADDR", "HTTP_HOST", "REQUEST_URI", "HTTP_REFERER", "HTTP_USER_AGENT") as $var)
	$_SESSION["GOTMLS_detected_attacks"] .= (isset($_SERVER[$var])?"&SERVER_$var=".urlencode($_SERVER[$var]):"");
foreach (array("log") as $var)
	$_SESSION["GOTMLS_detected_attacks"] .= (isset($_POST[$var])?"&POST_$var=".urlencode($_POST[$var]):"");
header("location: http://safe-load.gotmls.net/report.php?ver=4.14.47".$_SESSION["GOTMLS_detected_attacks"]);
die();