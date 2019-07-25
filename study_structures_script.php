<?php
	//****************************************************************************************************************
	//	^--- PHP -- 1A - START of startup
	//****************************************************************************************************************
	include "startup.php";
	//****************************************************************************************************************
	//	v--- PHP -- 1A - END of startup
	//****************************************************************************************************************
	//****************************************************************************************************************
	//	^--- PHP -- 1B - START of receiving the id of the study and structure_id
	//****************************************************************************************************************
	$objRequest = new stdClass();
	$objRequest->id = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);
	$objRequest->structure_id = filter_input(INPUT_GET, "structure_id", FILTER_VALIDATE_INT);
	//****************************************************************************************************************
	//	v--- PHP -- 1B - END of receiving the id of the study and structure_id
	//****************************************************************************************************************
	//****************************************************************************************************************
	//	^--- PHP -- 1C - START of creating the table
	//****************************************************************************************************************
	$objStudy = new stdClass();
	$objStudy->id = $objRequest->id;
	$objStudy->structure_id = $objRequest->structure_id;
	$objStudy->sql =  "CREATE TABLE tblStudy".$objStudy->id."Structure".$objStudy->structure_id."SNPs (";
	$objStudy->sql .= "id int(11) unsigned NOT NULL AUTO_INCREMENT, ";
	$objStudy->sql .= "position int(11) DEFAULT NULL, ";
	$objStudy->sql .= "names json DEFAULT NULL, ";
	$objStudy->sql .= "reference char(1) DEFAULT NULL, ";
	$objStudy->sql .= "alternate json DEFAULT NULL, ";
	$objStudy->sql .= "results json DEFAULT NULL, ";
	$objStudy->sql .= "PRIMARY KEY (id), ";
	$objStudy->sql .= "KEY k_position (position) ";
	$objStudy->sql .= ") ENGINE=InnoDB DEFAULT CHARSET=latin1;";
	$objStudy->prepare = $objSettings->database->connection->prepare($objStudy->sql);
	$objStudy->prepare->execute();
	//****************************************************************************************************************
	//	v--- PHP -- 1C - END of creating the table
	//****************************************************************************************************************
?>
