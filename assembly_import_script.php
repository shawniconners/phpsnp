<?php
	//****************************************************************************************************************
	//	^--- PHP -- 1A - START of startup
	//****************************************************************************************************************
	include "startup.php";
	//****************************************************************************************************************
	//	v--- PHP -- 1A - END of startup
	//****************************************************************************************************************
	//****************************************************************************************************************
	//	^--- PHP -- 1B - START of receiving the id of the structure
	//****************************************************************************************************************
	$objRequest = new stdClass();
	$objRequest->id = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);
	//****************************************************************************************************************
	//	v--- PHP -- 1B - END of receiving the id of the structure
	//****************************************************************************************************************
	//****************************************************************************************************************
	//	^--- PHP -- 1C - START of selecting the structure from the database
	//****************************************************************************************************************
	$objStructure = new stdClass();
	$objStructure->sql = "SELECT * FROM tblStructures WHERE id = :id;";
	$objStructure->prepare = $objSettings->database->connection->prepare($objStructure->sql);
	$objStructure->prepare->bindValue(':id', $objRequest->id, PDO::PARAM_INT);
	$objStructure->prepare->execute();
	$objStructure->database_record = $objStructure->prepare->fetchAll(PDO::FETCH_ASSOC)[0];
	$objStructure->id = $objStructure->database_record["id"];
	$objStructure->assembly_id = $objStructure->database_record["assembly_id"];
	$objStructure->name = $objStructure->database_record["name"];
	$objStructure->chunk_start = $objStructure->database_record["chunk_start"];
	$objStructure->chunk_length = $objStructure->database_record["chunk_length"];
	unset($objAssembly->database_record);
	//****************************************************************************************************************
	//	v--- PHP -- 1C - END of selecting the structure from the database
	//****************************************************************************************************************
	//****************************************************************************************************************
	//	^--- PHP -- 1D - START of retrieving the sequence from the FASTA file
	//****************************************************************************************************************
	$objStructure->sequence = str_replace(PHP_EOL, "", file_get_contents("assembly_uploads/".$objStructure->assembly_id.".fasta", FALSE, NULL, $objStructure->chunk_start, $objStructure->chunk_length));
	$objStructure->sequence_length = strlen($objStructure->sequence);
	//****************************************************************************************************************
	//	v--- PHP -- 1D - END of retrieving the sequence from the FASTA file
	//****************************************************************************************************************
	//****************************************************************************************************************
	//	^--- PHP -- 1E - START of updating the structure record
	//****************************************************************************************************************
	$objStructure->sql = "UPDATE tblStructures SET sequence_length = :sequence_length, sequence = :sequence WHERE id = :id;";
	$objStructure->prepare = $objSettings->database->connection->prepare($objStructure->sql);
	$objStructure->prepare->bindValue(':sequence_length', $objStructure->sequence_length, PDO::PARAM_INT);
	$objStructure->prepare->bindValue(':sequence', $objStructure->sequence, PDO::PARAM_STR);
	$objStructure->prepare->bindValue(':id', $objStructure->id, PDO::PARAM_INT);
	$objStructure->prepare->execute();
	unset($objStructure);
	//****************************************************************************************************************
	//	v--- PHP -- 1E - END of updating the structure record
	//****************************************************************************************************************
?>
