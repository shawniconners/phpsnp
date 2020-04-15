<?php
	//****************************************************************************************************************
	//	^--- PHP -- 1A - START of settings
	//****************************************************************************************************************
	include "settings.php";
	//****************************************************************************************************************
	//	v--- PHP -- 1A - END of settings
	//****************************************************************************************************************
	//****************************************************************************************************************
	//	^--- PHP -- 1B - START of choosing database connection
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
	//	^--- PHP -- 1B - END of choosing database connection
	//****************************************************************************************************************
	//****************************************************************************************************************
	//	^--- PHP -- 1C - START of shared functions
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
	function funReadableFilesize($inc_bytes) {
    		$arrSizeSuffixes = array('B','kB','MB','GB','TB','PB');
    		$intSizeFactor = floor((strlen($inc_bytes) - 1) / 3);
    		return sprintf("%.2f", $inc_bytes / pow(1024, $intSizeFactor))." ".$arrSizeSuffixes[$intSizeFactor];
	}
	function funSignificantFigures($inc_value, $inc_digits){
	    if ($inc_value == 0) {
	        $intDecimalPlaces = $inc_digits - 1;
	    } elseif ($inc_value < 0) {
	        $intDecimalPlaces = $inc_digits - floor(log10($inc_value * -1)) - 1;
	    } else {
	        $intDecimalPlaces = $inc_digits - floor(log10($inc_value)) - 1;
	    }
	    $fltResult = ($intDecimalPlaces > 0) ?
	        number_format($inc_value, $intDecimalPlaces) : round($inc_value, $intDecimalPlaces);
	    return $fltResult;
	}
	//****************************************************************************************************************
	//	^--- PHP -- 1C - END of shared functions
	//****************************************************************************************************************
?>
