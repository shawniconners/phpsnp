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
	$objResponse->redirect = "study_fields.php?id=";
	//****************************************************************************************************************
	//	v--- PHP -- 1B - END of creating the response
	//****************************************************************************************************************
	//****************************************************************************************************************
	//	^--- PHP -- 1C - START of receiving the name, source and file for the Study
	//****************************************************************************************************************
	$objRequest = new stdClass();
	$objRequest->name = filter_input(INPUT_POST, "elmStudyUploadNameValue", FILTER_SANITIZE_STRING);
	$objRequest->source = filter_input(INPUT_POST, "elmStudyUploadSourceValue", FILTER_SANITIZE_STRING);
	$objRequest->assembly_id = filter_input(INPUT_POST, "elmStudyUploadAssemblyValue", FILTER_VALIDATE_INT);
	$objRequest->vcf = $_FILES["elmStudyUploadFileSelected"]["tmp_name"];
	//****************************************************************************************************************
	//	v--- PHP -- 1C - END of receiving the name, source and file for the Study
	//****************************************************************************************************************
	//****************************************************************************************************************
	//	^--- PHP -- 1D - START of adding the Study record to the database and setting the insert id
	//****************************************************************************************************************
	$objStudy = new stdClass();
	$objStudy->sql = "INSERT INTO tblStudies (name, source, assembly_id, structures) VALUES (:name, :source, :assembly_id, '[]');";
	$objStudy->prepare = $objSettings->database->connection->prepare($objStudy->sql);
	$objStudy->prepare->bindValue(':name', $objRequest->name, PDO::PARAM_STR);
	$objStudy->prepare->bindValue(':source', $objRequest->source, PDO::PARAM_STR);
	$objStudy->prepare->bindValue(':assembly_id', $objRequest->assembly_id, PDO::PARAM_INT);
	$objStudy->prepare->execute();
	$objStudy->id = $objSettings->database->connection->lastInsertId();
	$objStudy->snp_count = 0;
	//****************************************************************************************************************
	//	v--- PHP -- 1D - END of adding the Study record to the database and setting the insert id
	//****************************************************************************************************************
	//****************************************************************************************************************
	//	^--- PHP -- 1E - START of moving the VCF file to uploads folder and opening it
	//****************************************************************************************************************
	$objVCF = new stdClass();
	$objVCF->upload_location = "study_uploads/".$objStudy->id.".vcf";
	move_uploaded_file($objRequest->vcf, $objVCF->upload_location);
	$objVCF->opened_file = fopen($objVCF->upload_location, "r");
	$objVCF->meta = "";
	$objVCF->header = "";
	$objVCF->fields = new stdClass();
	$objVCF->first_snp = "";
	$objVCF->line_counter = 0;
	$objVCF->file_current_line = "";
	$objVCF->character_count = 0;
	$objVCF->chunk_line_counter = 0;
	$objVCF->chunk_line = 0;
	$objVCF->chunk_start = 0;
	$objVCF->chunk_length = 0;
	$objVCF->chunk_positions = [];
	//****************************************************************************************************************
	//	v--- PHP -- 1E - END of moving the VCF file to uploads folder and opening it
	//****************************************************************************************************************
	//****************************************************************************************************************
	//	^--- PHP -- 1F - START of looping through VCF to find meta data, header and chunk information
	//****************************************************************************************************************
	while (($objVCF->file_current_line = fgets($objVCF->opened_file)) !== false) {
		//****************************************************************************************************************
		//	^--- PHP -- 2A - START of a single line in the VCF file
		//****************************************************************************************************************
		$objVCF->line_counter++;
		if(substr($objVCF->file_current_line, 0, 2) === "##"){
			//****************************************************************************************************************
			//	^--- PHP -- 3A - START of a meta line
			//****************************************************************************************************************
			$objVCF->meta .= $objVCF->file_current_line;
			$objVCF->character_count += strlen($objVCF->file_current_line);
			//****************************************************************************************************************
			//	v--- PHP -- 3A - END of a meta line
			//****************************************************************************************************************
		}else if(substr($objVCF->file_current_line, 0, 1) === "#"){
			//****************************************************************************************************************
			//	^--- PHP -- 3B - START of the header line
			//****************************************************************************************************************
			$objVCF->header = trim($objVCF->file_current_line);
			$objVCF->character_count += strlen($objVCF->file_current_line);
			//****************************************************************************************************************
			//	v--- PHP -- 3B - END of the header line
			//****************************************************************************************************************
		}else{
			//****************************************************************************************************************
			//	^--- PHP -- 3C - START of row of data (SNP)
			//****************************************************************************************************************
			$objStudy->snp_count++;
			$objVCF->chunk_line_counter++;
			if($objStudy->snp_count === 1){
				//****************************************************************************************************************
				//	^--- PHP -- 4A - START of first row of SNP data
				//****************************************************************************************************************
				$objVCF->first_snp = trim($objVCF->file_current_line);
				//****************************************************************************************************************
				//	v--- PHP -- 4A - END of first row of SNP data
				//****************************************************************************************************************
			}
			if($objVCF->chunk_line_counter === 1){
				//****************************************************************************************************************
				//	^--- PHP -- 4B - START of the first SNP in a chunk
				//****************************************************************************************************************
				$objVCF->chunk_line = $objVCF->line_counter;
				$objVCF->chunk_start = $objVCF->character_count;
				//****************************************************************************************************************
				//	v--- PHP -- 4B - END of the first SNP in a chunk
				//****************************************************************************************************************
			}
			$objVCF->character_count += strlen($objVCF->file_current_line);
			if($objVCF->chunk_line_counter === $objSettings->snp_chunk_size){
				//****************************************************************************************************************
				//	^--- PHP -- 4C - START of the last SNP in a chunk
				//****************************************************************************************************************
				$objVCF->chunk_length = $objVCF->character_count - $objVCF->chunk_start;
				array_push($objVCF->chunk_positions, [$objVCF->chunk_start, $objVCF->chunk_length, $objVCF->chunk_line]);
				$objVCF->chunk_start = 0;
				$objVCF->chunk_length = 0;
				$objVCF->chunk_line_counter = 0;
				//****************************************************************************************************************
				//	v--- PHP -- 4C - END of the last SNP in a chunk
				//****************************************************************************************************************
			}
			//****************************************************************************************************************
			//	v--- PHP -- 3C - END of row of data (SNP)
			//****************************************************************************************************************
		}
		//****************************************************************************************************************
		//	v--- PHP -- 2A - END of a single line in the VCF file
		//****************************************************************************************************************
    }
	if($objVCF->chunk_start !== 0){
		//****************************************************************************************************************
		//	^--- PHP -- 2B - START of an uncompleted last chunk
		//****************************************************************************************************************
		$objVCF->chunk_length = $objVCF->character_count - $objVCF->chunk_start;
		array_push($objVCF->chunk_positions, [$objVCF->chunk_start, $objVCF->chunk_length, $objVCF->chunk_line]);
		//****************************************************************************************************************
		//	v--- PHP -- 2B - END of an uncompleted last chunk
		//****************************************************************************************************************
	}
	fclose($objVCF->opened_file);
	//****************************************************************************************************************
	//	v--- PHP -- 1F - END of looping through VCF to find meta data, header and chunk information
	//****************************************************************************************************************
	//****************************************************************************************************************
	//	^--- PHP -- 1G - START of updating the database to include meta data, header and chunk information
	//****************************************************************************************************************
	$objStudy->sql = "UPDATE tblStudies SET vcf_meta = :vcf_meta, vcf_header = :vcf_header, vcf_first_snp = :vcf_first_snp, vcf_fields = :vcf_fields, vcf_chunks = :vcf_chunks, snp_count = :snp_count WHERE id = :id;";
	$objStudy->prepare = $objSettings->database->connection->prepare($objStudy->sql);
	$objStudy->prepare->bindValue(':vcf_meta', $objVCF->meta, PDO::PARAM_STR);
	$objStudy->prepare->bindValue(':vcf_header', $objVCF->header, PDO::PARAM_STR);
	$objStudy->prepare->bindValue(':vcf_first_snp', $objVCF->first_snp, PDO::PARAM_STR);
	$objStudy->prepare->bindValue(':vcf_fields', json_encode($objVCF->fields), PDO::PARAM_STR);
	$objStudy->prepare->bindValue(':vcf_chunks', json_encode($objVCF->chunk_positions), PDO::PARAM_STR);
	$objStudy->prepare->bindValue(':snp_count', $objStudy->snp_count, PDO::PARAM_INT);
	$objStudy->prepare->bindValue(':id', $objStudy->id, PDO::PARAM_INT);
	$objStudy->prepare->execute();
	//****************************************************************************************************************
	//	v--- PHP -- 1G - END of updating the database to include meta data, header and chunk information
	//****************************************************************************************************************
	//****************************************************************************************************************
	//	^--- PHP -- 1H - START of redirect to study fields page
	//****************************************************************************************************************
	header('Location: '.$objResponse->redirect.$objStudy->id);
	//****************************************************************************************************************
	//	v--- PHP -- 1H - END of redirect to study fields page
	//****************************************************************************************************************
?>
