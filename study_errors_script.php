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
	$objStudy->sql = "SELECT name FROM tblStudies WHERE id = :id;";
	$objStudy->prepare = $objSettings->database->connection->prepare($objStudy->sql);
	$objStudy->prepare->bindValue(':id', $objStudy->id, PDO::PARAM_INT);
	$objStudy->prepare->execute();
	$objStudy->database_record = $objStudy->prepare->fetchAll(PDO::FETCH_ASSOC)[0];
	$objStudy->name = $objStudy->database_record["name"];
	//****************************************************************************************************************
	//	v--- PHP -- 1C - END of retrieving the study
	//****************************************************************************************************************
	//****************************************************************************************************************
	//	^--- PHP -- 1D - START of retrieving the study errors
	//****************************************************************************************************************
	$objStudy->sql = "SELECT line_number, category, assembly_reference, snp_chromosome, snp_position, snp_reference, snp_alternate FROM tblErrors WHERE study_id = :study_id ORDER BY line_number;";
	$objStudy->prepare = $objSettings->database->connection->prepare($objStudy->sql);
	$objStudy->prepare->bindValue(':study_id', $objStudy->id, PDO::PARAM_INT);
	$objStudy->prepare->execute();
	$objStudy->errors = $objStudy->prepare->fetchAll(PDO::FETCH_ASSOC);
	//****************************************************************************************************************
	//	v--- PHP -- 1D - END of retrieving the study errors
	//****************************************************************************************************************
	//****************************************************************************************************************
	//	^--- PHP -- 1E - START of creating the download
	//****************************************************************************************************************
	header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="'.$objStudy->name.'_Errors.tsv"');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
	echo "Line Number\t";
	echo "Category\t";
	echo "Assembly Reference\t";
	echo "SNP Chromosome\t";
	echo "SNP Position\t";
	echo "SNP Reference\t";
	echo "SNP Alternate\r\n";
	for($intErrorCounter = 0; $intErrorCounter < count($objStudy->errors); $intErrorCounter++){
		echo implode("\t", $objStudy->errors[$intErrorCounter]);
		if($intErrorCounter < (count($objStudy->errors) - 1)){
			echo "\r\n";
		}
	}
	//****************************************************************************************************************
	//	v--- PHP -- 1E - END of creating the download
	//****************************************************************************************************************
?>
