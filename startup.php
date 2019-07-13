<?php
	//****************************************************************************************************************
	//	^--- PHP -- 1A - START of settings
	//****************************************************************************************************************
	include "settings.php";
	//****************************************************************************************************************
	//	v--- PHP -- 1A - END of settings
	//****************************************************************************************************************
	//****************************************************************************************************************
	//	^--- 1B - START of choosing database connection
	//****************************************************************************************************************
	if(isset($_POST["core"])){
		$objSettings->core = filter_input(INPUT_POST, "core", FILTER_VALIDATE_INT);
	}elseif(isset($_GET["core"])){
		$objSettings->core = filter_input(INPUT_GET, "core", FILTER_VALIDATE_INT);
	}
	$objSettings->database->host = $objSettings->database->pool[$objSettings->core]["host"];
	$objSettings->database->user = $objSettings->database->pool[$objSettings->core]["user"];
	$objSettings->database->password = $objSettings->database->pool[$objSettings->core]["password"];
	$objSettings->database->connection = new PDO('mysql:host='.$objSettings->database->host.';dbname='.$objSettings->database->name,$objSettings->database->user, $objSettings->database->password);
	//****************************************************************************************************************
	//	v--- 1B - END of choosing database connection
	//****************************************************************************************************************
	//****************************************************************************************************************
	//	^--- 1C - START of shared functions
	//****************************************************************************************************************
	function funStructureNameConversion($inc_structure_name){
		$inc_structure_name = str_replace("Chr", "Gm", $inc_structure_name);
		$inc_structure_name = str_replace("chr", "Gm", $inc_structure_name);
		$inc_structure_name = str_replace("GM", "Gm", $inc_structure_name);
		$inc_structure_name = str_replace("gm", "Gm", $inc_structure_name);
		if(strlen($inc_structure_name) == 5 && substr($inc_structure_name, 0, 2) == "Gm"){
			$inc_structure_name = str_replace("m0", "m", $inc_structure_name);
		}
		return $inc_structure_name;
	}
	//****************************************************************************************************************
	//	v--- 1C - END of shared functions
	//****************************************************************************************************************
?>
