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
	//	^--- PHP -- 1C - START of finding the row count for the selected study structure
	//****************************************************************************************************************
	$objStudy = new stdClass();
	$objStudy->id = $objRequest->id;
	$objStudy->structure_id = $objRequest->structure_id;
	$objStudy->sql = "SELECT count(*) as row_count FROM tblStudy".$objStudy->id."Structure".$objStudy->structure_id."SNPs;";
	$objStudy->prepare = $objSettings->database->connection->prepare($objStudy->sql);
	$objStudy->prepare->execute();
	$objStudy->database_record = $objStudy->prepare->fetchAll(PDO::FETCH_ASSOC)[0];
	$objStudy->row_count = $objStudy->database_record["row_count"];
	//****************************************************************************************************************
	//	v--- PHP -- 1C - END of finding the row count for the selected study structure
	//****************************************************************************************************************
	//****************************************************************************************************************
	//	^--- PHP -- 1D - START of removing the table if empty or appending to the structures list for the study
	//****************************************************************************************************************
	if($objStudy->row_count > 0){
		//****************************************************************************************************************
		//	^--- PHP -- 2A - START of a table with rows that needs to be added to the study record
		//****************************************************************************************************************
		$objStudy->sql = "UPDATE tblStudies SET structures = JSON_ARRAY_APPEND(structures, '$', :structure_id) WHERE id = :id;";
		$objStudy->prepare = $objSettings->database->connection->prepare($objStudy->sql);
		$objStudy->prepare->bindValue(':id', $objStudy->id, PDO::PARAM_INT);
		$objStudy->prepare->bindValue(':structure_id', $objStudy->structure_id, PDO::PARAM_INT);
		$objStudy->prepare->execute();
		//****************************************************************************************************************
		//	v--- PHP -- 2A - END of a table with rows that needs to be added to the study record
		//****************************************************************************************************************
	}else{
		//****************************************************************************************************************
		//	^--- PHP -- 2B - START of an empty study structure table that will be removed
		//****************************************************************************************************************
		$objStudy->sql = "DROP TABLE IF EXISTS tblStudy".$objStudy->id."Structure".$objStudy->structure_id."SNPs;";
		$objStudy->prepare = $objSettings->database->connection->prepare($objStudy->sql);
		$objStudy->prepare->execute();
		//****************************************************************************************************************
		//	v--- PHP -- 2B - END of an empty study structure table that will be removed
		//****************************************************************************************************************
	}
	//****************************************************************************************************************
	//	v--- PHP -- 1D - END of removing the table if empty or appending to the structures list for the study
	//****************************************************************************************************************
?>
