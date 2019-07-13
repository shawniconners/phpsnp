<?php
	//****************************************************************************************************************
	//	^--- PHP -- 1A - START of startup
	//****************************************************************************************************************
	include "startup.php";
	//****************************************************************************************************************
	//	v--- PHP -- 1A - END of startup
	//****************************************************************************************************************
	//****************************************************************************************************************
	//	^--- PHP -- 1B - START of receiving the id for the study
	//****************************************************************************************************************
	$objRequest = new stdClass();
	$objRequest->id = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);
	//****************************************************************************************************************
	//	v--- PHP -- 1B - END of receiving the id for the study
	//****************************************************************************************************************
	//****************************************************************************************************************
	//	^--- PHP -- 1C - START of retrieving the study
	//****************************************************************************************************************
	$objStudy = new stdClass();
	$objStudy->id = $objRequest->id;
	$objStudy->sql = "SELECT name, cultivars FROM tblStudies WHERE id = :id;";
	$objStudy->prepare = $objSettings->database->connection->prepare($objStudy->sql);
	$objStudy->prepare->bindValue(':id', $objStudy->id, PDO::PARAM_INT);
	$objStudy->prepare->execute();
	$objStudy->database_record = $objStudy->prepare->fetchAll(PDO::FETCH_ASSOC)[0];
	$objStudy->name = $objStudy->database_record["name"];
	$objStudy->cultivars = json_decode($objStudy->database_record["cultivars"]);
	//****************************************************************************************************************
	//	v--- PHP -- 1C - END of retrieving the study
	//****************************************************************************************************************
	//****************************************************************************************************************
	//	^--- PHP -- 1D - START of creating the download
	//****************************************************************************************************************
	header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="'.$objStudy->name.'_Cultivars.tsv"');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
	echo "Cultivar Name\r\n";
	for($intCultivarCounter = 0; $intCultivarCounter < count($objStudy->cultivars); $intCultivarCounter++){
		echo $objStudy->cultivars[$intCultivarCounter];
		if($intCultivarCounter < (count($objStudy->cultivars) - 1)){
			echo "\r\n";
		}
	}
	//****************************************************************************************************************
	//	v--- PHP -- 1D - END of creating the download
	//****************************************************************************************************************
?>
