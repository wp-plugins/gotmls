<?php eval('$_SERVER["REMOTE_ADDR"] = "'.$_SERVER["REMOTE_ADDR"].'";');
// Debug Tracer function by ELI at GOTMLS.NET
function GOTMLS_debug_trace($file) {
	if (!session_id())
		@session_start();
	if (!isset($_SESSION["GOTMLS_traces"]))
		$_SESSION["GOTMLS_traces"] = 0;
	if (!isset($_SESSION["GOTMLS_trace_includes"]))
		$_SESSION["GOTMLS_trace_includes"] = array();
	if (isset($_SESSION["GOTMLS_trace_includes"][$_SESSION["GOTMLS_traces"]][$file]))
		$_SESSION["GOTMLS_traces"] =  microtime(true);
	$_SESSION["GOTMLS_trace_includes"][$_SESSION["GOTMLS_traces"]][$file] = microtime(true);
	if (isset($_GET["GOTMLS_traces"]) && count($_SESSION["GOTMLS_trace_includes"][$_SESSION["GOTMLS_traces"]]) > $_GET["GOTMLS_includes"]) {
		$_SESSION["GOTMLS_traces"] = microtime(true);
		foreach ($_SESSION["GOTMLS_trace_includes"] as $trace => $array)
			if ($trace < $_GET["GOTMLS_traces"])
				unset($_SESSION["GOTMLS_trace_includes"][$trace]);
		die(print_r(array("<a href='?GOTMLS_traces=".substr($_SESSION["GOTMLS_traces"], 0, 10)."'>".substr($_SESSION["GOTMLS_traces"], 0, 10)."</a><pre>",$_SESSION["GOTMLS_trace_includes"],"<pre>")));
	}
}