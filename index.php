<?php
	if (isset($_POST["mg404_posturl"]) && isset($_POST["mg404_level"])) {
		$url =  parse_url($_POST["mg404_posturl"]);
		$_SERVER["REQUEST_URI"] = $url["path"];
		for ($i=0; $i<$_POST["mg404_level"]; $i++) 
			$chdir .= "../";
		chdir($chdir);
		if (is_file("index.php"))
			include("index.php");
	}
?>
