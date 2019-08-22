<?php
	//****************************************************************************************************************
	//	^--- PHP -- 1A - START of startup
	//****************************************************************************************************************
	include "startup.php";
	//****************************************************************************************************************
	//	v--- PHP -- 1A - END of startup
	//****************************************************************************************************************
	//****************************************************************************************************************
	//	^--- PHP -- 1B - START of receiving the study name, number of snps, base cultivar and similarity reults
	//****************************************************************************************************************
	$objRequest = new stdClass();
	$objRequest->study = filter_input(INPUT_POST, "elmCultivarSimilarityStudy", FILTER_SANITIZE_STRING);
	$objRequest->snps = filter_input(INPUT_POST, "elmCultivarSimilaritySNPs", FILTER_VALIDATE_INT);
	$objRequest->cultivar = filter_input(INPUT_POST, "elmCultivarSimilarityBaseCultivar", FILTER_SANITIZE_STRING);
	$objRequest->results = filter_input(INPUT_POST, "elmCultivarSimilarityResults", FILTER_UNSAFE_RAW);
	//****************************************************************************************************************
	//	v--- PHP -- 1B - END  of receiving the study name, number of snps, base cultivar and similarity reults
	//****************************************************************************************************************
	//****************************************************************************************************************
	//	^--- PHP -- 1C - START of preparing the results
	//****************************************************************************************************************
	$objCultivar = new stdClass();
	$objCultivar->study = $objRequest->study;
	$objCultivar->snps = $objRequest->snps;
	$objCultivar->cultivar = $objRequest->cultivar;
	$objCultivar->results = json_decode($objRequest->results, true);
	$objCultivar->download = new stdClass();
	$objCultivar->download->name = $objCultivar->study."_".$objCultivar->cultivar."_similarity.tsv";
	$objCultivar->download->content = "Cultivar\tMatching SNPs (Max. ".$objCultivar->snps.")".PHP_EOL;
	foreach ($objCultivar->results as $arrCultivar) {
		$objCultivar->download->content .= $arrCultivar["name"]."\t".$arrCultivar["similarity"].PHP_EOL;
	}
	//****************************************************************************************************************
	//	v--- PHP -- 1C - END of preparing the results
	//****************************************************************************************************************
	//****************************************************************************************************************
	//	^--- PHP -- 1E - START of creating the download
	//****************************************************************************************************************
	header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="'.$objCultivar->download->name.'"');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . strlen($objCultivar->download->content));
	echo $objCultivar->download->content;
	//****************************************************************************************************************
	//	v--- PHP -- 1E - END of creating the download
	//****************************************************************************************************************
?>
