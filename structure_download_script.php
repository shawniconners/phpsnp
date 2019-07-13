<?php
	//****************************************************************************************************************
	//	^--- PHP -- 1A - START of startup
	//****************************************************************************************************************
	include "startup.php";
	//****************************************************************************************************************
	//	v--- PHP -- 1A - END of startup
	//****************************************************************************************************************
	//****************************************************************************************************************
	//	^--- PHP -- 1B - START of receiving the id for the assembly
	//****************************************************************************************************************
	$objRequest = new stdClass();
	$objRequest->id = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);
	//****************************************************************************************************************
	//	v--- PHP -- 1B - END of receiving the id for the assembly
	//****************************************************************************************************************
	//****************************************************************************************************************
	//	^--- PHP -- 1C - START of retrieving the assembly and structures
	//****************************************************************************************************************
	$objStructure = new stdClass();
	$objStructure->assembly = new stdClass();
	$objStructure->id = $objRequest->id;
	$objStructure->sql = "SELECT assembly_id, name, sequence FROM tblStructures WHERE id = :id;";
	$objStructure->prepare = $objSettings->database->connection->prepare($objStructure->sql);
	$objStructure->prepare->bindValue(':id', $objStructure->id, PDO::PARAM_INT);
	$objStructure->prepare->execute();
	$objStructure->database_record = $objStructure->prepare->fetchAll(PDO::FETCH_ASSOC)[0];
	$objStructure->assembly->id = $objStructure->database_record["assembly_id"];
	$objStructure->name = $objStructure->database_record["name"];
	$objStructure->sequence = $objStructure->database_record["sequence"];
	$objStructure->sql = "SELECT name FROM tblAssemblies WHERE id = :id;";
	$objStructure->prepare = $objSettings->database->connection->prepare($objStructure->sql);
	$objStructure->prepare->bindValue(':id', $objStructure->assembly->id, PDO::PARAM_INT);
	$objStructure->prepare->execute();
	$objStructure->database_record = $objStructure->prepare->fetchAll(PDO::FETCH_ASSOC)[0];
	$objStructure->assembly->name = $objStructure->database_record["name"];
	//****************************************************************************************************************
	//	v--- PHP -- 1C - END of retrieving the assembly and structures
	//****************************************************************************************************************
	//****************************************************************************************************************
	//	^--- PHP -- 1D - START of updating the sequence to 80 col format
	//****************************************************************************************************************
	$objStructure->sequence = chunk_split($objStructure->sequence, 80);
	//****************************************************************************************************************
	//	v--- PHP -- 1D - END of updating the sequence to 80 col format
	//****************************************************************************************************************
	//****************************************************************************************************************
	//	^--- PHP -- 1E - START of creating the download
	//****************************************************************************************************************
	header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="'.$objStructure->assembly->name.'_'.$objStructure->name.'.fasta"');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . strlen($objStructure->sequence));
	echo ">".$objStructure->name.PHP_EOL;
    echo $objStructure->sequence;
	//****************************************************************************************************************
	//	v--- PHP -- 1E - END of creating the download
	//****************************************************************************************************************
?>
