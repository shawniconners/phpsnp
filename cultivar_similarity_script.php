<?php
	//****************************************************************************************************************
	//	^--- PHP -- 1A - START of startup
	//****************************************************************************************************************
	include "startup.php";
	//****************************************************************************************************************
	//	v--- PHP -- 1A - END of startup
	//****************************************************************************************************************
	//****************************************************************************************************************
	//	^--- PHP -- 1B - START of receiving the id for the assembly, structure and study, along with start and stop
	//****************************************************************************************************************
	$objRequest = new stdClass();
	$objRequest->structure_id = filter_input(INPUT_GET, "structure_id", FILTER_VALIDATE_INT);
	$objRequest->start_position = filter_input(INPUT_GET, "start_position", FILTER_VALIDATE_INT);
	$objRequest->stop_position = filter_input(INPUT_GET, "stop_position", FILTER_VALIDATE_INT);
	$objRequest->study_id = filter_input(INPUT_GET, "study_id", FILTER_VALIDATE_INT);
	$objRequest->cultivar_key_1 = filter_input(INPUT_GET, "cultivar_key_1", FILTER_VALIDATE_INT);
	$objRequest->cultivar_key_2 = filter_input(INPUT_GET, "cultivar_key_2", FILTER_VALIDATE_INT);
	//****************************************************************************************************************
	//	v--- PHP -- 1B - END of receiving the id for the assembly, structure and study, along with start and stop
	//****************************************************************************************************************
	//****************************************************************************************************************
	//	^--- PHP -- 1C - START of preparing selections
	//****************************************************************************************************************
	$objAssembly = new stdClass();
	$objAssembly->structure = new stdClass();
	$objAssembly->structure->id = $objRequest->structure_id;
	$objAssembly->structure->start_position = $objRequest->start_position;
	$objAssembly->structure->stop_position = $objRequest->stop_position;
	$objAssembly->study = new stdClass();
	$objAssembly->study->id = $objRequest->study_id;
	$objAssembly->study->cultivar_key_1 = $objRequest->cultivar_key_1;
	$objAssembly->study->cultivar_key_2 = $objRequest->cultivar_key_2;
	$objAssembly->study->result = new stdClass();
	$objAssembly->study->result->cultivar_key = $objAssembly->study->cultivar_key_2;
	$objAssembly->study->result->similarity = 0;
	$objAssembly->study->result->name = " ";
	//****************************************************************************************************************
	//	v--- PHP -- 1C - END of preparing selections
	//****************************************************************************************************************
	//****************************************************************************************************************
	//	^--- PHP -- 1D - START of looping through cultivar comparisons
	//****************************************************************************************************************
	//for($intLoopCounter = $objAssembly->study->cultivar_key+1; $intLoopCounter < $objAssembly->study->cultivar_count; $intLoopCounter++){
		$objAssembly->sql = "SELECT JSON_EXTRACT(results, '$[".$objAssembly->study->cultivar_key_1."]') AS source_cultivar, JSON_EXTRACT(results, '$[".$objAssembly->study->cultivar_key_2."]') AS compare_cultivar FROM tblStudy".$objAssembly->study->id."Structure".$objAssembly->structure->id."SNPs WHERE position >= :start_position AND position <= :stop_position;";
		$objAssembly->prepare = $objSettings->database->connection->prepare($objAssembly->sql);
		$objAssembly->prepare->bindValue(':start_position', $objAssembly->structure->start_position, PDO::PARAM_INT);
		$objAssembly->prepare->bindValue(':stop_position', $objAssembly->structure->stop_position, PDO::PARAM_INT);
		$objAssembly->prepare->execute();
		$objAssembly->study->snps = $objAssembly->prepare->fetchAll(PDO::FETCH_ASSOC);
		$objAssembly->study->source_alleles = array_column($objAssembly->study->snps, "source_cultivar");
		$objAssembly->study->comparison_alleles = array_column($objAssembly->study->snps, "compare_cultivar");
		$objAssembly->study->mismatch_alleles = array_diff_assoc($objAssembly->study->source_alleles, $objAssembly->study->comparison_alleles);
		//$objAssembly->study->result->similarity = round(((count($objAssembly->study->snps) - count($objAssembly->study->mismatch_alleles)) / count($objAssembly->study->snps)) * 100);
		$objAssembly->study->result->similarity = count($objAssembly->study->snps) - count($objAssembly->study->mismatch_alleles);
		echo json_encode($objAssembly->study->result);
	//}
	//****************************************************************************************************************
	//	v--- PHP -- 1D - END of looping through cultivar comparisons
	//****************************************************************************************************************
?>
