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
	$objRequest->cultivar_key = filter_input(INPUT_GET, "cultivar_key", FILTER_VALIDATE_INT);
	$objRequest->snp_window = filter_input(INPUT_GET, "snp_window", FILTER_VALIDATE_INT);
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
	$objAssembly->study->cultivar_key = $objRequest->cultivar_key;
	$objAssembly->study->snp_window = $objRequest->snp_window;
	$objAssembly->study->result = new stdClass();
	$objAssembly->study->result->cultivar_key = $objAssembly->study->cultivar_key;
	$objAssembly->study->result->haplotype = "";
	$objAssembly->study->result->name = " ";
	//****************************************************************************************************************
	//	v--- PHP -- 1C - END of preparing selections
	//****************************************************************************************************************
	//****************************************************************************************************************
	//	^--- PHP -- 1D - START of looping through cultivar comparisons
	//****************************************************************************************************************
		$objAssembly->sql = "SELECT JSON_EXTRACT(results, '$[".$objAssembly->study->cultivar_key."]') AS cultivar_result FROM tblStudy".$objAssembly->study->id."Structure".$objAssembly->structure->id."SNPs WHERE position >= :start_position AND position <= :stop_position LIMIT :snp_window;";
		$objAssembly->prepare = $objSettings->database->connection->prepare($objAssembly->sql);
		$objAssembly->prepare->bindValue(':snp_window', $objAssembly->study->snp_window, PDO::PARAM_INT);
		$objAssembly->prepare->bindValue(':start_position', $objAssembly->structure->start_position, PDO::PARAM_INT);
		$objAssembly->prepare->bindValue(':stop_position', $objAssembly->structure->stop_position, PDO::PARAM_INT);
		$objAssembly->prepare->execute();
		$objAssembly->study->snps = $objAssembly->prepare->fetchAll(PDO::FETCH_ASSOC);
		//$objAssembly->study->result->haplotype = str_replace("/", "_", str_replace('"', "", implode(",",array_column($objAssembly->study->snps, "cultivar_result"))));
		$objAssembly->study->result->haplotype = str_replace('"', "", implode(", ",array_column($objAssembly->study->snps, "cultivar_result")));
		echo json_encode($objAssembly->study->result);
	//****************************************************************************************************************
	//	v--- PHP -- 1D - END of looping through cultivar comparisons
	//****************************************************************************************************************
?>
