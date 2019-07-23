<?php
	//****************************************************************************************************************
	//	^--- PHP -- 1A - START of startup
	//****************************************************************************************************************
	include "startup.php";
	//****************************************************************************************************************
	//	v--- PHP -- 1A - END of startup
	//****************************************************************************************************************
	//****************************************************************************************************************
	//	^--- PHP -- 1B - START of creating the response
	//****************************************************************************************************************
	$objResponse = new stdClass();
	$objResponse->redirect = "assembly.php?assembly_id=";
	//****************************************************************************************************************
	//	v--- PHP -- 1B - END of creating the response
	//****************************************************************************************************************
	//****************************************************************************************************************
	//	^--- PHP -- 1C - START of receiving the id for the study
	//****************************************************************************************************************
	$objRequest = new stdClass();
	$objRequest->id = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);
	//****************************************************************************************************************
	//	v--- PHP -- 1C - END of receiving the id for the study
	//****************************************************************************************************************
	//****************************************************************************************************************
	//	^--- PHP -- 1D - START of retrieving the study structures
	//****************************************************************************************************************
	$objStudy = new stdClass();
	$objStudy->assembly = new stdClass();
	$objStudy->id = $objRequest->id;
	$objStudy->sql = "SELECT assembly_id, structures FROM tblStudies WHERE id = :id;";
	$objStudy->prepare = $objSettings->database->connection->prepare($objStudy->sql);
	$objStudy->prepare->bindValue(':id', $objStudy->id, PDO::PARAM_INT);
	$objStudy->prepare->execute();
	$objStudy->database_record = $objStudy->prepare->fetchAll(PDO::FETCH_ASSOC)[0];
	$objStudy->assembly->id = $objStudy->database_record["assembly_id"];
	$objStudy->structures = json_decode($objStudy->database_record["structures"]);
	$objStudy->structures_count = count($objStudy->structures);
	//****************************************************************************************************************
	//	v--- PHP -- 1D - END of retrieving the study structures
	//****************************************************************************************************************
	//****************************************************************************************************************
	//	^--- PHP -- 1E - START of deleting the SNP tables for each structure in the assembly
	//****************************************************************************************************************
	for($intStructureCounter = 0; $intStructureCounter < $objStudy->structures_count; $intStructureCounter++) {
		$objStudy->sql = "DROP TABLE IF EXISTS tblStudy".$objStudy->id."Structure".$objStudy->structures[$intStructureCounter]."SNPs;";
		$objStudy->prepare = $objSettings->database->connection->prepare($objStudy->sql);
		$objStudy->prepare->execute();
	}
	//****************************************************************************************************************
	//	v--- PHP -- 1E - END of deleting the SNP tables for each structure in the assembly
	//****************************************************************************************************************
	//****************************************************************************************************************
	//	^--- PHP -- 1F - START of deleting the error records for this study
	//****************************************************************************************************************
	$objStudy->sql = "DELETE FROM tblErrors WHERE study_id = :study_id;";
	$objStudy->prepare = $objSettings->database->connection->prepare($objStudy->sql);
	$objStudy->prepare->bindValue(':study_id', $objStudy->id, PDO::PARAM_INT);
	$objStudy->prepare->execute();
	//****************************************************************************************************************
	//	v--- PHP -- 1F - END of deleting the error records for this study
	//****************************************************************************************************************
	//****************************************************************************************************************
	//	^--- PHP -- 1G - START of deleting the study record
	//****************************************************************************************************************
	$objStudy->sql = "DELETE FROM tblStudies WHERE id = :id;";
	$objStudy->prepare = $objSettings->database->connection->prepare($objStudy->sql);
	$objStudy->prepare->bindValue(':id', $objStudy->id, PDO::PARAM_INT);
	$objStudy->prepare->execute();
	//****************************************************************************************************************
	//	v--- PHP -- 1G - END of deleting the study record
	//****************************************************************************************************************
	//****************************************************************************************************************
	//	^--- PHP -- 1H - START of deleting the file
	//****************************************************************************************************************
	unlink("study_uploads/".$objStudy->id.".vcf");
	//****************************************************************************************************************
	//	v--- PHP -- 1H - END of deleting the file
	//****************************************************************************************************************
	//****************************************************************************************************************
	//	^--- PHP -- 1I - START of redirect
	//****************************************************************************************************************
	header('Location: '.$objResponse->redirect.$objStudy->assembly->id);
	//****************************************************************************************************************
	//	v--- PHP -- 1I - END of redirect
	//****************************************************************************************************************
?>
