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
	$objResponse->redirect = "assembly_import.php?id=";
	//****************************************************************************************************************
	//	v--- PHP -- 1B - END of creating the response
	//****************************************************************************************************************
	//****************************************************************************************************************
	//	^--- PHP -- 1C - START of receiving the name, source and file for the assembly
	//****************************************************************************************************************
	$objRequest = new stdClass();
	$objRequest->name = filter_input(INPUT_POST, "elmAssemblyUploadNameValue", FILTER_SANITIZE_STRING);
	$objRequest->source = filter_input(INPUT_POST, "elmAssemblyUploadSourceValue", FILTER_SANITIZE_STRING);
	$objRequest->fasta = $_FILES["elmAssemblyUploadFileSelected"]["tmp_name"];
	//****************************************************************************************************************
	//	v--- PHP -- 1C - END of receiving the name, source and file for the assembly
	//****************************************************************************************************************
	//****************************************************************************************************************
	//	^--- PHP -- 1D - START of adding the assembly record to the database and setting the insert id
	//****************************************************************************************************************
	$objAssembly = new stdClass();
	$objAssembly->sql = "INSERT INTO tblAssemblies (name, source) VALUES (:name, :source);";
	$objAssembly->prepare = $objSettings->database->connection->prepare($objAssembly->sql);
	$objAssembly->prepare->bindValue(':name', $objRequest->name, PDO::PARAM_STR);
	$objAssembly->prepare->bindValue(':source', $objRequest->source, PDO::PARAM_STR);
	$objAssembly->prepare->execute();
	$objAssembly->id = $objSettings->database->connection->lastInsertId();
	//****************************************************************************************************************
	//	v--- PHP -- 1D - END of adding the assembly record to the database and setting the insert id
	//****************************************************************************************************************
	//****************************************************************************************************************
	//	^--- PHP -- 1E - START of moving the fasta file to uploads folder and opening it
	//****************************************************************************************************************
	$objFASTA = new stdClass();
	$objFASTA->upload_location = "assembly_uploads/".$objAssembly->id.".fasta";
	move_uploaded_file($objRequest->fasta, $objFASTA->upload_location);
	$objFASTA->opened_file = fopen($objFASTA->upload_location, "r");
	$objFASTA->character_count = 0;
	$objFASTA->structure_names = array();
	$objFASTA->structure_chunk_starts = array();
	$objFASTA->structure_chunk_lengths = array();
	$objFASTA->file_current_line = "";
	$objFASTA->sequence_length = 0;
	//****************************************************************************************************************
	//	v--- PHP -- 1E - END of moving the fasta file to uploads folder and opening it
	//****************************************************************************************************************
	//****************************************************************************************************************
	//	^--- PHP -- 1F - START of looping through fasta to create structure starts array
	//****************************************************************************************************************
	while (($objFASTA->file_current_line = fgets($objFASTA->opened_file)) !== false) {
		//****************************************************************************************************************
		//	^--- PHP -- 2A - START of a single line in the FASTA file
		//****************************************************************************************************************
		if($objFASTA->file_current_line[0] === ">"){
			//****************************************************************************************************************
			//	^--- PHP -- 3A - START of a new structure
			//****************************************************************************************************************
			array_push($objFASTA->structure_names, trim(substr($objFASTA->file_current_line, 1, strpos($objFASTA->file_current_line, "\n"))));
			array_push($objFASTA->structure_chunk_starts, $objFASTA->character_count+strlen($objFASTA->file_current_line));
			array_push($objFASTA->structure_chunk_lengths, 0);
			if(count($objFASTA->structure_chunk_lengths) > 1){
				//****************************************************************************************************************
				//	^--- PHP -- 4A - START of a non-first structure, so the previous structure sequence length can be found
				//****************************************************************************************************************
				$objFASTA->structure_chunk_lengths[count($objFASTA->structure_chunk_lengths)-2] = $objFASTA->character_count - $objFASTA->structure_chunk_starts[count($objFASTA->structure_chunk_lengths)-2];
				//****************************************************************************************************************
				//	v--- PHP -- 4A - END of a non-first structure, so the previous structure sequence length can be found
				//****************************************************************************************************************
			}
			//****************************************************************************************************************
			//	v--- PHP -- 3A - END of a new structure
			//****************************************************************************************************************
		}else{
			//****************************************************************************************************************
			//	^--- PHP -- 3B - START of a line of sequence
			//****************************************************************************************************************
			$objFASTA->sequence_length += strlen(trim($objFASTA->file_current_line));
			//****************************************************************************************************************
			//	v--- PHP -- 3B - END of a line of sequence
			//****************************************************************************************************************
		}
        $objFASTA->character_count += strlen($objFASTA->file_current_line);
		//****************************************************************************************************************
		//	v--- PHP -- 2A - END of a single line in the FASTA file
		//****************************************************************************************************************
    }
	$objFASTA->structure_chunk_lengths[count($objFASTA->structure_chunk_lengths)-1] = $objFASTA->character_count - $objFASTA->structure_chunk_starts[count($objFASTA->structure_chunk_lengths)-1];
	fclose($objFASTA->opened_file);
	unset($objFASTA->opened_file);
	//****************************************************************************************************************
	//	v--- PHP -- 1F - END of looping through fasta to create structure starts array
	//****************************************************************************************************************
	//****************************************************************************************************************
	//	^--- PHP -- 1G - START of looping through and creating structure records
	//****************************************************************************************************************
	$objStructure = new stdClass();
	$objStructure->sql = "INSERT INTO tblStructures (assembly_id, name, chunk_start, chunk_length) VALUES (:assembly_id, :name, :chunk_start, :chunk_length);";
	$objStructure->assembly_id = $objAssembly->id;
	for ($intLoopCounter = 0; $intLoopCounter < count($objFASTA->structure_chunk_starts); $intLoopCounter++) {
		$objStructure->prepare = $objSettings->database->connection->prepare($objStructure->sql);
		$objStructure->prepare->bindValue(':assembly_id', $objStructure->assembly_id, PDO::PARAM_INT);
		$objStructure->prepare->bindValue(':name', $objFASTA->structure_names[$intLoopCounter], PDO::PARAM_STR);
		$objStructure->prepare->bindValue(':chunk_start', $objFASTA->structure_chunk_starts[$intLoopCounter], PDO::PARAM_INT);
		$objStructure->prepare->bindValue(':chunk_length', $objFASTA->structure_chunk_lengths[$intLoopCounter], PDO::PARAM_INT);
		$objStructure->prepare->execute();
	}
	//****************************************************************************************************************
	//	v--- PHP -- 1G - END of looping through and creating structure records
	//****************************************************************************************************************
	//****************************************************************************************************************
	//	^--- PHP -- 1H - START of updating the assembly record for sequence_length
	//****************************************************************************************************************
	$objAssembly->sql = "UPDATE tblAssemblies SET sequence_length = :sequence_length, structure_count = :structure_count WHERE id = :id;";
	$objAssembly->prepare = $objSettings->database->connection->prepare($objAssembly->sql);
	$objAssembly->prepare->bindValue(':sequence_length', $objFASTA->sequence_length, PDO::PARAM_INT);
	$objAssembly->prepare->bindValue(':structure_count', count($objFASTA->structure_chunk_starts), PDO::PARAM_INT);
	$objAssembly->prepare->bindValue(':id', $objAssembly->id, PDO::PARAM_INT);
	$objAssembly->prepare->execute();
	unset($objFASTA);
	//****************************************************************************************************************
	//	v--- PHP -- 1H - END of updating the assembly record for sequence_length
	//****************************************************************************************************************
	//****************************************************************************************************************
	//	^--- PHP -- 1I - START of redirect to assembly import page
	//****************************************************************************************************************
	header('Location: '.$objResponse->redirect.$objAssembly->id);
	//****************************************************************************************************************
	//	v--- PHP -- 1I - END of redirect to assembly import page
	//****************************************************************************************************************
?>
