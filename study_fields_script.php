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
	$objResponse->redirect = "study_structures.php?id=";
	//****************************************************************************************************************
	//	v--- PHP -- 1B - END of creating the response
	//****************************************************************************************************************
	//****************************************************************************************************************
	//	^--- PHP -- 1C - START of receiving the id and vcf_fields for the Study
	//****************************************************************************************************************
	$objRequest = new stdClass();
	$objRequest->id = filter_input(INPUT_POST, "elmStudyIdValue", FILTER_VALIDATE_INT);
	$objRequest->vcf_fields = filter_input(INPUT_POST, "elmStudyFieldsValue", FILTER_UNSAFE_RAW);
	//****************************************************************************************************************
	//	v--- PHP -- 1C - END of receiving the id and vcf_fields for the Study
	//****************************************************************************************************************
	//****************************************************************************************************************
	//	^--- PHP -- 1D - START of updating vcf_fields for the study record
	//****************************************************************************************************************
	$objStudy = new stdClass();
	$objStudy->id = $objRequest->id;
	$objStudy->sql = "UPDATE tblStudies SET vcf_fields = :vcf_fields WHERE id = :id;";
	$objStudy->prepare = $objSettings->database->connection->prepare($objStudy->sql);
	$objStudy->prepare->bindValue(':vcf_fields', $objRequest->vcf_fields, PDO::PARAM_STR);
	$objStudy->prepare->bindValue(':id', $objStudy->id, PDO::PARAM_INT);
	$objStudy->prepare->execute();
	$objStudy->vcf_fields = json_decode($objRequest->vcf_fields);
	//****************************************************************************************************************
	//	v--- PHP -- 1D - END of updating vcf_fields for the study record
	//****************************************************************************************************************
	//****************************************************************************************************************
	//	^--- PHP -- 1E - START of retrieving vcf_header for the study record and finding the cultivar count
	//****************************************************************************************************************
	$objStudy->cultivar_count = 0;
	$objStudy->sql = "SELECT vcf_header FROM tblStudies WHERE id = :id;";
	$objStudy->prepare = $objSettings->database->connection->prepare($objStudy->sql);
	$objStudy->prepare->bindValue(':id', $objStudy->id, PDO::PARAM_INT);
	$objStudy->prepare->execute();
	$objStudy->database_record = $objStudy->prepare->fetchAll(PDO::FETCH_ASSOC)[0];
	$objStudy->cultivars = array_slice(explode("\t", $objStudy->database_record["vcf_header"]), ($objStudy->vcf_fields->first_cultivar - 1));
	$objStudy->cultivar_count = count($objStudy->cultivars);
	//****************************************************************************************************************
	//	v--- PHP -- 1E - END of retrieving vcf_header for the study record and finding the cultivar count
	//****************************************************************************************************************
	//****************************************************************************************************************
	//	^--- PHP -- 1F - START of updating cultivar_count and cultivars for the study record
	//****************************************************************************************************************
	$objStudy->sql = "UPDATE tblStudies SET cultivar_count = :cultivar_count, cultivars = :cultivars WHERE id = :id;";
	$objStudy->prepare = $objSettings->database->connection->prepare($objStudy->sql);
	$objStudy->prepare->bindValue(':cultivar_count', $objStudy->cultivar_count, PDO::PARAM_INT);
	$objStudy->prepare->bindValue(':cultivars', json_encode($objStudy->cultivars), PDO::PARAM_STR);
	$objStudy->prepare->bindValue(':id', $objStudy->id, PDO::PARAM_INT);
	$objStudy->prepare->execute();
	//****************************************************************************************************************
	//	v--- PHP -- 1F - END of updating cultivar_count and cultivars for the study record
	//****************************************************************************************************************
	//****************************************************************************************************************
	//	^--- PHP -- 1G - START of redirect to study structures page
	//****************************************************************************************************************
	header('Location: '.$objResponse->redirect.$objStudy->id);
	//****************************************************************************************************************
	//	v--- PHP -- 1G - END of redirect to study structures page
	//****************************************************************************************************************
?>
