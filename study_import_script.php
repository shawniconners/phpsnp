<?php
	//****************************************************************************************************************
	//	^--- PHP -- 1A - START of startup
	//****************************************************************************************************************
	include "startup.php";
	//****************************************************************************************************************
	//	v--- PHP -- 1A - END of startup
	//****************************************************************************************************************
	//****************************************************************************************************************
	//	^--- PHP -- 1B - START of receiving the id of the study, chunk start and chunk length
	//****************************************************************************************************************
	$objRequest = new stdClass();
	$objRequest->id = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);
	$objRequest->chunk_start = filter_input(INPUT_GET, "chunk_start", FILTER_VALIDATE_INT);
	$objRequest->chunk_length = filter_input(INPUT_GET, "chunk_length", FILTER_VALIDATE_INT);
	$objRequest->chunk_line = filter_input(INPUT_GET, "chunk_line", FILTER_VALIDATE_INT);
	//****************************************************************************************************************
	//	v--- PHP -- 1B - END of receiving the id of the study, chunk start and chunk length
	//****************************************************************************************************************
	//****************************************************************************************************************
	//	^--- PHP -- 1C - START of finding the chunk and coversion to array of relevant values
	//****************************************************************************************************************
	$objStudy = new stdClass();
	$objStudy->id = $objRequest->id;
	$objStudy->vcf_lines = explode(PHP_EOL, trim(file_get_contents("study_uploads/".$objStudy->id.".vcf", FALSE, NULL, $objRequest->chunk_start, $objRequest->chunk_length)));
	$objStudy->current_vcf_line = "";
	$objStudy->current_line_number = $objRequest->chunk_line - 1;
	$objStudy->batch = [];
	//****************************************************************************************************************
	//	v--- PHP -- 1C - END of finding the chunk and coversion to array of relevant values
	//****************************************************************************************************************
	//****************************************************************************************************************
	//	^--- PHP -- 1D - START of retrieving the Study vcf_fields settings
	//****************************************************************************************************************
	$objStudy->sql = "SELECT id, assembly_id, vcf_fields FROM tblStudies WHERE id = :id;";
	$objStudy->prepare = $objSettings->database->connection->prepare($objStudy->sql);
	$objStudy->prepare->bindValue(':id', $objStudy->id, PDO::PARAM_INT);
	$objStudy->prepare->execute();
	$objStudy->database_record = $objStudy->prepare->fetchAll(PDO::FETCH_ASSOC)[0];
	$objStudy->assembly_id = $objStudy->database_record["assembly_id"];
	$objStudy->vcf_fields = json_decode($objStudy->database_record["vcf_fields"]);
	$objStudy->snps = [];
	$objStudy->current_snp = [];
	$objStudy->current_structure_name = "";
	$objStudy->current_structure_sequence = "";
	$objStudy->current_structure_length = 0;
	//****************************************************************************************************************
	//	v--- PHP -- 1D - END of retrieving the Study vcf_fields settings
	//****************************************************************************************************************
	//****************************************************************************************************************
	//	^--- PHP -- 1E - START of looping through SNPs from chunk and creating clean array
	//****************************************************************************************************************
	foreach ($objStudy->vcf_lines as $objStudy->current_vcf_line) {
		array_push($objStudy->snps, explode("\t", trim($objStudy->current_vcf_line)));
	}
	unset($objStudy->vcf_lines);
	//****************************************************************************************************************
	//	v--- PHP -- 1E - END of looping through SNPs from chunk and creating clean array
	//****************************************************************************************************************
	//****************************************************************************************************************
	//	^--- PHP -- 1F - START of looping through all the SNPs in this chunk
	//****************************************************************************************************************
	$objStructure = new stdClass();
	$objStructure->id = 0;
	$objStructure->name = "";
	$objStructure->sequence = "";
	$objStructure->sequence_length = 0;
	foreach ($objStudy->snps as $objStudy->current_snp) {
		$objStudy->current_line_number++;
		$objError = new stdClass();
		if($objStructure->name !== funStructureNameConversion($objStudy->current_snp[$objStudy->vcf_fields->chromosome-1])){
			//****************************************************************************************************************
			//	^--- PHP -- 3A - START of a change in the current structure
			//****************************************************************************************************************
			if(count($objStudy->batch) > 0){
				$objSNP->sql = "INSERT INTO tblStudy".$objSNP->study_id."Structure".$objStructure->id."SNPs (position, names, reference, alternate, results) VALUES";
				for($intBatchCounter = 0; $intBatchCounter < count($objStudy->batch); $intBatchCounter++){
					if($intBatchCounter > 0){
						$objSNP->sql .= ",";
					}
					$objSNP->sql .= " (:position".$intBatchCounter.", :names".$intBatchCounter.", :reference".$intBatchCounter.", :alternate".$intBatchCounter.", :results".$intBatchCounter.")";
				}
				$objSNP->sql .= ";";
				$objSNP->prepare = $objSettings->database->connection->prepare($objSNP->sql);
				for($intBatchCounter = 0; $intBatchCounter < count($objStudy->batch); $intBatchCounter++){
					$objSNP->prepare->bindValue(':position'.$intBatchCounter, $objStudy->batch[$intBatchCounter]->position, PDO::PARAM_INT);
					$objSNP->prepare->bindValue(':names'.$intBatchCounter, json_encode($objStudy->batch[$intBatchCounter]->names), PDO::PARAM_STR);
					$objSNP->prepare->bindValue(':reference'.$intBatchCounter, $objStudy->batch[$intBatchCounter]->reference, PDO::PARAM_STR);
					$objSNP->prepare->bindValue(':alternate'.$intBatchCounter, json_encode($objStudy->batch[$intBatchCounter]->alternate), PDO::PARAM_STR);
					$objSNP->prepare->bindValue(':results'.$intBatchCounter, json_encode($objStudy->batch[$intBatchCounter]->results), PDO::PARAM_STR);
				}
				$objSNP->prepare->execute();
				$objStudy->batch = [];
			}
			$objStructure->id = 0;
			$objStructure->name = "";
			$objStructure->sequence = "";
			$objStructure->sequence_length = 0;
			$objNewStructure = new stdClass();
			$objNewStructure->sql = "SELECT id, sequence, sequence_length FROM tblStructures WHERE assembly_id = :assembly_id AND name = :name;";
			$objNewStructure->prepare = $objSettings->database->connection->prepare($objNewStructure->sql);
			$objNewStructure->prepare->bindValue(':assembly_id', $objStudy->assembly_id, PDO::PARAM_INT);
			$objNewStructure->prepare->bindValue(':name', funStructureNameConversion($objStudy->current_snp[$objStudy->vcf_fields->chromosome-1]), PDO::PARAM_STR);
			$objNewStructure->prepare->execute();
			if($objNewStructure->prepare->rowCount() === 0){
				$objError->line_number = $objStudy->current_line_number;
				$objError->category = "Invalid Structure";
				$objError->assembly_reference = "";
				$objError->snp_chromosome = $objStudy->current_snp[$objStudy->vcf_fields->chromosome-1];
				$objError->snp_position = $objStudy->current_snp[$objStudy->vcf_fields->position-1];
				$objError->snp_reference = $objStudy->current_snp[$objStudy->vcf_fields->reference-1];
				$objError->snp_alternate = $objStudy->current_snp[$objStudy->vcf_fields->alternate-1];
			}else{
				$objNewStructure->database_record = $objNewStructure->prepare->fetchAll(PDO::FETCH_ASSOC)[0];
				//echo "New Chromosome: we are ok<br />";
				$objStructure->id = $objNewStructure->database_record["id"];
				$objStructure->name = funStructureNameConversion($objStudy->current_snp[$objStudy->vcf_fields->chromosome-1]);
				$objStructure->sequence = $objNewStructure->database_record["sequence"];
				$objStructure->sequence_length = $objNewStructure->database_record["sequence_length"];
				//**********************************************************************************************************************************
				// BATCH CODE FIX - if we have a new structure, then we need to send the current batch if it has any ready, and start a new batch
				//**********************************************************************************************************************************
			}
			//****************************************************************************************************************
			//	v--- PHP -- 3A - END of a change in the current structure
			//****************************************************************************************************************
		}
		if(empty($objError->line_number)){
			//****************************************************************************************************************
			//	^--- PHP -- 3B - START of a valid chromosome for this SNP
			//****************************************************************************************************************
			if(($objStudy->current_snp[$objStudy->vcf_fields->position-1] > 0) && ($objStudy->current_snp[$objStudy->vcf_fields->position-1] <= $objStructure->sequence_length)){
				//****************************************************************************************************************
				//	^--- PHP -- 4A - START of a valid position for this SNP
				//****************************************************************************************************************
				//echo "Assembly Reference: ".."<br />";
				if($objStudy->current_snp[$objStudy->vcf_fields->reference-1] !== substr($objStructure->sequence, $objStudy->current_snp[$objStudy->vcf_fields->position-1]-1, 1)){
					//****************************************************************************************************************
					//	^--- PHP -- 5A - START of an invalid reference for this SNP
					//****************************************************************************************************************
					$objError->line_number = $objStudy->current_line_number;
					$objError->category = "Invalid Reference";
					$objError->assembly_reference = substr($objStructure->sequence, $objStudy->current_snp[$objStudy->vcf_fields->position-1]-1, 1);
					$objError->snp_chromosome = funStructureNameConversion($objStudy->current_snp[$objStudy->vcf_fields->chromosome-1]);
					$objError->snp_position = $objStudy->current_snp[$objStudy->vcf_fields->position-1];
					$objError->snp_reference = $objStudy->current_snp[$objStudy->vcf_fields->reference-1];
					$objError->snp_alternate = $objStudy->current_snp[$objStudy->vcf_fields->alternate-1];
					//****************************************************************************************************************
					//	v--- PHP -- 5A - END of an invalid reference for this SNP
					//****************************************************************************************************************
				}
				//****************************************************************************************************************
				//	v--- PHP -- 4A - END of a valid position for this SNP
				//****************************************************************************************************************
			}else{
				//****************************************************************************************************************
				//	^--- PHP -- 4B - START of a position error for this SNP
				//****************************************************************************************************************
				$objError->line_number = $objStudy->current_line_number;
				$objError->category = "Invalid Position";
				$objError->assembly_reference = substr($objStructure->sequence, $objStudy->current_snp[$objStudy->vcf_fields->position-1]-1, 1);
				$objError->snp_chromosome = funStructureNameConversion($objStudy->current_snp[$objStudy->vcf_fields->chromosome-1]);
				$objError->snp_position = $objStudy->current_snp[$objStudy->vcf_fields->position-1];
				$objError->snp_reference = $objStudy->current_snp[$objStudy->vcf_fields->reference-1];
				$objError->snp_alternate = $objStudy->current_snp[$objStudy->vcf_fields->alternate-1];
				//****************************************************************************************************************
				//	v--- PHP -- 4B - END of a position error for this SNP
				//****************************************************************************************************************
			}
			//****************************************************************************************************************
			//	v--- PHP -- 3B - END of a valid chromosome for this SNP
			//****************************************************************************************************************
		}
		if(empty($objError->line_number)){
			//****************************************************************************************************************
			//	^--- PHP -- 3C - START of a valid SNP to be added to appropriate table
			//****************************************************************************************************************
			$objSNP = new stdClass();
			$objSNP->study_id = $objStudy->id;
			$objSNP->position = $objStudy->current_snp[$objStudy->vcf_fields->position-1];
			$objSNP->names = [];
			foreach ($objStudy->vcf_fields->names as $intNameFieldNumber) {
				array_push($objSNP->names, $objStudy->current_snp[$intNameFieldNumber-1]);
			}
			$objSNP->reference = $objStudy->current_snp[$objStudy->vcf_fields->reference-1];
			$objSNP->alternate = explode(",",$objStudy->current_snp[$objStudy->vcf_fields->alternate-1]);
			$objSNP->results = array_slice($objStudy->current_snp, $objStudy->vcf_fields->first_cultivar-1);
			if(strpos($objSNP->results[0],":") > 1){
				for($intCultivarCounter = 0; $intCultivarCounter < count($objSNP->results); $intCultivarCounter++){
					$arrTempSNPValue = explode(":", $objSNP->results[$intCultivarCounter]);
					$objSNP->results[$intCultivarCounter] = $arrTempSNPValue[0];
				}
			}
			//**********************************************************************************************************************************
			// START BATCH CODE FIX - add this item to the current batch, see if it is ready to send, if so, do it, and reset the batch
			//**********************************************************************************************************************************
			array_push($objStudy->batch, $objSNP);
			if(count($objStudy->batch) === $objSettings->insert_batch_size){
				//****************************************************************************************************************
				//	^--- PHP -- 4B - START of a batch that is ready to go
				//****************************************************************************************************************
				$objSNP->sql = "INSERT INTO tblStudy".$objSNP->study_id."Structure".$objStructure->id."SNPs (position, names, reference, alternate, results) VALUES";
				for($intBatchCounter = 0; $intBatchCounter < $objSettings->insert_batch_size; $intBatchCounter++){
					if($intBatchCounter > 0){
						$objSNP->sql .= ",";
					}
					$objSNP->sql .= " (:position".$intBatchCounter.", :names".$intBatchCounter.", :reference".$intBatchCounter.", :alternate".$intBatchCounter.", :results".$intBatchCounter.")";
				}
				$objSNP->sql .= ";";
				$objSNP->prepare = $objSettings->database->connection->prepare($objSNP->sql);
				for($intBatchCounter = 0; $intBatchCounter < $objSettings->insert_batch_size; $intBatchCounter++){
					$objSNP->prepare->bindValue(':position'.$intBatchCounter, $objStudy->batch[$intBatchCounter]->position, PDO::PARAM_INT);
					$objSNP->prepare->bindValue(':names'.$intBatchCounter, json_encode($objStudy->batch[$intBatchCounter]->names), PDO::PARAM_STR);
					$objSNP->prepare->bindValue(':reference'.$intBatchCounter, $objStudy->batch[$intBatchCounter]->reference, PDO::PARAM_STR);
					$objSNP->prepare->bindValue(':alternate'.$intBatchCounter, json_encode($objStudy->batch[$intBatchCounter]->alternate), PDO::PARAM_STR);
					$objSNP->prepare->bindValue(':results'.$intBatchCounter, json_encode($objStudy->batch[$intBatchCounter]->results), PDO::PARAM_STR);
				}
				$objSNP->prepare->execute();
				$objStudy->batch = [];
				//****************************************************************************************************************
				//	v--- PHP -- 4B - END of a batch that is ready to go
				//****************************************************************************************************************
			}
			//**********************************************************************************************************************************
			// END BATCH CODE FIX - add this item to the current batch, see if it is ready to send, if so, do it, and reset the batch
			//**********************************************************************************************************************************
			//****************************************************************************************************************
			//	v--- PHP -- 3C - END of a valid SNP to be added to appropriate table
			//****************************************************************************************************************
		}else{
			//****************************************************************************************************************
			//	^--- PHP -- 3D - START of an erroneous SNP that needs to be added to the errors table
			//****************************************************************************************************************
			$objError->sql = "INSERT INTO tblErrors (study_id, line_number, category, assembly_reference, snp_chromosome, snp_position, snp_reference, snp_alternate) VALUES (:study_id, :line_number, :category, :assembly_reference, :snp_chromosome, :snp_position, :snp_reference, :snp_alternate);";
			$objError->prepare = $objSettings->database->connection->prepare($objError->sql);
			$objError->prepare->bindValue(':study_id', $objStudy->id, PDO::PARAM_INT);
			$objError->prepare->bindValue(':line_number', $objError->line_number, PDO::PARAM_INT);
			$objError->prepare->bindValue(':category', $objError->category, PDO::PARAM_STR);
			$objError->prepare->bindValue(':assembly_reference', $objError->assembly_reference, PDO::PARAM_STR);
			$objError->prepare->bindValue(':snp_chromosome', $objError->snp_chromosome, PDO::PARAM_STR);
			$objError->prepare->bindValue(':snp_position', $objError->snp_position, PDO::PARAM_STR);
			$objError->prepare->bindValue(':snp_reference', $objError->snp_reference, PDO::PARAM_STR);
			$objError->prepare->bindValue(':snp_alternate', $objError->snp_alternate, PDO::PARAM_STR);
			$objError->prepare->execute();
			//****************************************************************************************************************
			//	v--- PHP -- 3D - END of an erroneous SNP that needs to be added to the errors table
			//****************************************************************************************************************
		}
	}

	if(count($objStudy->batch) > 0){
		$objSNP = new stdClass();
		$objSNP->study_id = $objStudy->id;
		$objSNP->sql = "INSERT INTO tblStudy".$objSNP->study_id."Structure".$objStructure->id."SNPs (position, names, reference, alternate, results) VALUES";
		for($intBatchCounter = 0; $intBatchCounter < count($objStudy->batch); $intBatchCounter++){
			if($intBatchCounter > 0){
				$objSNP->sql .= ",";
			}
			$objSNP->sql .= " (:position".$intBatchCounter.", :names".$intBatchCounter.", :reference".$intBatchCounter.", :alternate".$intBatchCounter.", :results".$intBatchCounter.")";
		}
		$objSNP->sql .= ";";
		$objSNP->prepare = $objSettings->database->connection->prepare($objSNP->sql);
		for($intBatchCounter = 0; $intBatchCounter < count($objStudy->batch); $intBatchCounter++){
			$objSNP->prepare->bindValue(':position'.$intBatchCounter, $objStudy->batch[$intBatchCounter]->position, PDO::PARAM_INT);
			$objSNP->prepare->bindValue(':names'.$intBatchCounter, json_encode($objStudy->batch[$intBatchCounter]->names), PDO::PARAM_STR);
			$objSNP->prepare->bindValue(':reference'.$intBatchCounter, $objStudy->batch[$intBatchCounter]->reference, PDO::PARAM_STR);
			$objSNP->prepare->bindValue(':alternate'.$intBatchCounter, json_encode($objStudy->batch[$intBatchCounter]->alternate), PDO::PARAM_STR);
			$objSNP->prepare->bindValue(':results'.$intBatchCounter, json_encode($objStudy->batch[$intBatchCounter]->results), PDO::PARAM_STR);
		}
		$objSNP->prepare->execute();
		$objStudy->batch = [];
	}
	//****************************************************************************************************************
	//	^--- PHP -- 1F - END of looping through all the SNPs in this chunk
	//****************************************************************************************************************
?>
