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
	$objResponse->redirect = "curate.php";
	//****************************************************************************************************************
	//	v--- PHP -- 1B - END of creating the response
	//****************************************************************************************************************
	//****************************************************************************************************************
	//	^--- PHP -- 1C - START of receiving the id for the assembly
	//****************************************************************************************************************
	$objRequest = new stdClass();
	$objRequest->id = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);
	//****************************************************************************************************************
	//	v--- PHP -- 1C - END of receiving the id for the assembly
	//****************************************************************************************************************
	//****************************************************************************************************************
	//	^--- PHP -- 1D - START of retrieving all the structure ids for this assembly
	//****************************************************************************************************************
	$objAssembly = new stdClass();
	$objAssembly->id = $objRequest->id;
	$objAssembly->sql = "SELECT id FROM tblStructures WHERE assembly_id = :assembly_id;";
	$objAssembly->prepare = $objSettings->database->connection->prepare($objAssembly->sql);
	$objAssembly->prepare->bindValue(':assembly_id', $objAssembly->id, PDO::PARAM_INT);
	$objAssembly->prepare->execute();
	$objAssembly->structures = $objAssembly->prepare->fetchAll(PDO::FETCH_ASSOC);
	//****************************************************************************************************************
	//	v--- PHP -- 1D - END of retrieving all the structure ids for this assembly
	//****************************************************************************************************************
	//****************************************************************************************************************
	//	^--- PHP -- 1E - START of retrieving all the study ids for this assembly
	//****************************************************************************************************************
	$objAssembly->sql = "SELECT id FROM tblStudies WHERE assembly_id = :assembly_id;";
	$objAssembly->prepare = $objSettings->database->connection->prepare($objAssembly->sql);
	$objAssembly->prepare->bindValue(':assembly_id', $objAssembly->id, PDO::PARAM_INT);
	$objAssembly->prepare->execute();
	$objAssembly->studies = $objAssembly->prepare->fetchAll(PDO::FETCH_ASSOC);
	//****************************************************************************************************************
	//	v--- PHP -- 1E - END of retrieving all the study ids for this assembly
	//****************************************************************************************************************
	//****************************************************************************************************************
	//	^--- PHP -- 1F - START of looping through study structures and deleting SNP tables
	//****************************************************************************************************************
	foreach ($objAssembly->studies as $arrStudy) {
		foreach ($objAssembly->structures as $arrStructure) {
			$objAssembly->sql =  "DROP TABLE IF EXISTS tblStudy".$arrStudy["id"]."Structure".$arrStructure["id"]."SNPs;";
			$objAssembly->prepare = $objSettings->database->connection->prepare($objAssembly->sql);
			$objAssembly->prepare->execute();
		}
	}
	//****************************************************************************************************************
	//	v--- PHP -- 1F - END of looping through study structures and deleting SNP tables
	//****************************************************************************************************************
	//****************************************************************************************************************
	//	^--- PHP -- 1G - START of looping through studies and deleting errors and files
	//****************************************************************************************************************
	foreach ($objAssembly->studies as $arrStudy) {
		$objAssembly->sql = "DELETE FROM tblErrors WHERE study_id = :study_id;";
		$objAssembly->prepare = $objSettings->database->connection->prepare($objAssembly->sql);
		$objAssembly->prepare->bindValue(':study_id', $arrStudy["id"], PDO::PARAM_INT);
		$objAssembly->prepare->execute();
		unlink("study_uploads/".$arrStudy["id"].".vcf");
	}
	//****************************************************************************************************************
	//	v--- PHP -- 1G - END of looping through studies and deleting errors and files
	//****************************************************************************************************************
	//****************************************************************************************************************
	//	^--- PHP -- 1H - START of deleting the assembly and related structures
	//****************************************************************************************************************
	$objAssembly->sql = "DELETE FROM tblAssemblies WHERE id = :id;";
	$objAssembly->prepare = $objSettings->database->connection->prepare($objAssembly->sql);
	$objAssembly->prepare->bindValue(':id', $objAssembly->id, PDO::PARAM_INT);
	$objAssembly->prepare->execute();
	$objAssembly->sql = "DELETE FROM tblStructures WHERE assembly_id = :assembly_id;";
	$objAssembly->prepare = $objSettings->database->connection->prepare($objAssembly->sql);
	$objAssembly->prepare->bindValue(':assembly_id', $objAssembly->id, PDO::PARAM_INT);
	$objAssembly->prepare->execute();
	$objAssembly->sql = "DELETE FROM tblStudies WHERE assembly_id = :assembly_id;";
	$objAssembly->prepare = $objSettings->database->connection->prepare($objAssembly->sql);
	$objAssembly->prepare->bindValue(':assembly_id', $objAssembly->id, PDO::PARAM_INT);
	$objAssembly->prepare->execute();
	//****************************************************************************************************************
	//	v--- PHP -- 1H - END of deleting the assembly and related structures
	//****************************************************************************************************************
	//****************************************************************************************************************
	//	^--- PHP -- 1I - START of deleting the file
	//****************************************************************************************************************
	unlink("assembly_uploads/".$objAssembly->id.".fasta");
	//****************************************************************************************************************
	//	v--- PHP -- 1I - END of deleting the file
	//****************************************************************************************************************
	//****************************************************************************************************************
	//	^--- PHP -- 1J - START of redirect to curate page
	//****************************************************************************************************************
	header('Location: '.$objResponse->redirect);
	//****************************************************************************************************************
	//	v--- PHP -- 1J - END of redirect to curate page
	//****************************************************************************************************************
?>
