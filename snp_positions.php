<?php
	//****************************************************************************************************************
	//	^--- PHP -- 1A - START of startup
	//****************************************************************************************************************
	include "startup.php";
	//****************************************************************************************************************
	//	v--- PHP -- 1A - END of startup
	//****************************************************************************************************************
	//****************************************************************************************************************
	//	^--- PHP -- 1B - START of receiving the id for the study and structure along with start and stop
	//****************************************************************************************************************
	$objRequest = new stdClass();
	$objRequest->study_id = filter_input(INPUT_GET, "study_id", FILTER_VALIDATE_INT);
	$objRequest->structure_id = filter_input(INPUT_GET, "structure_id", FILTER_VALIDATE_INT);
	$objRequest->start_position = filter_input(INPUT_GET, "start_position", FILTER_VALIDATE_INT);
	$objRequest->stop_position = filter_input(INPUT_GET, "stop_position", FILTER_VALIDATE_INT);
	//****************************************************************************************************************
	//	v--- PHP -- 1B - END of receiving the id for the study and structure along with start and stop
	//****************************************************************************************************************
	//****************************************************************************************************************
	//	^--- PHP -- 1C - START of preparing study and structure
	//****************************************************************************************************************
	$objStudy = new stdClass();
	$objStudy->id = $objRequest->study_id;
	$objStudy->structure = new stdClass();
	$objStudy->structure->id = $objRequest->structure_id;
	$objStudy->structure->start_position = $objRequest->start_position;
	$objStudy->structure->stop_position = $objRequest->stop_position;
	//****************************************************************************************************************
	//	v--- PHP -- 1C - END of preparing study and structure
	//****************************************************************************************************************
	//****************************************************************************************************************
	//	^--- PHP -- 1D - START of retrieving the snp positions
	//****************************************************************************************************************
	$objStudy->sql = "SELECT position FROM tblStudy".$objStudy->id."Structure".$objStudy->structure->id."SNPs WHERE position >= :start_position AND position <= :stop_position;";
	$objStudy->prepare = $objSettings->database->connection->prepare($objStudy->sql);
	$objStudy->prepare->bindValue(':start_position', $objStudy->structure->start_position, PDO::PARAM_INT);
	$objStudy->prepare->bindValue(':stop_position', $objStudy->structure->stop_position, PDO::PARAM_INT);
	$objStudy->prepare->execute();
	$objStudy->structure->snp_positions = $objStudy->prepare->fetchAll(PDO::FETCH_ASSOC);
	//****************************************************************************************************************
	//	v--- PHP -- 1D - END of retrieving the snp positions
	//****************************************************************************************************************
	//****************************************************************************************************************
	//	^--- PHP -- 1E - START of outputting the snp positions with returns
	//****************************************************************************************************************
	echo json_encode($objStudy->structure->snp_positions, JSON_NUMERIC_CHECK);
	//****************************************************************************************************************
	//	v--- PHP -- 1E - END of outputting the snp positions with returns
	//****************************************************************************************************************
?>
