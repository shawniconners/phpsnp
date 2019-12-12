<?php
	//****************************************************************************************************************
	//	^--- PHP -- 1A - START of startup
	//****************************************************************************************************************
	include "startup.php";
	//****************************************************************************************************************
	//	v--- PHP -- 1A - END of startup
	//****************************************************************************************************************
	//****************************************************************************************************************
	//	^--- PHP -- 1B - START of receiving the study name and haplotype groups results
	//****************************************************************************************************************
	$objRequest = new stdClass();
	$objRequest->study = filter_input(INPUT_POST, "elmHaplotypeGroupsStudy", FILTER_SANITIZE_STRING);
	$objRequest->cultivars = filter_input(INPUT_POST, "elmHaplotypeGroupsCultivars", FILTER_UNSAFE_RAW);
	$objRequest->results = filter_input(INPUT_POST, "elmHaplotypeGroupsResults", FILTER_UNSAFE_RAW);
	//****************************************************************************************************************
	//	v--- PHP -- 1B - END of receiving the study name and haplotype groups results
	//****************************************************************************************************************
	//****************************************************************************************************************
	//	^--- PHP -- 1C - START of preparing the results
	//****************************************************************************************************************
	$objHaplotype = new stdClass();
	$objHaplotype->study = $objRequest->study;
	$objHaplotype->cultivars = json_decode($objRequest->cultivars, true);
	$objHaplotype->results = json_decode($objRequest->results, true);
	$objHaplotype->download = new stdClass();
	$objHaplotype->download->name = $objHaplotype->study."_haplotype_groups.tsv";
	$objHaplotype->download->content = "Haplotype Group\tCultivar\tSNP Alleles".PHP_EOL;
	foreach ($objHaplotype->results as $arrHaplotypeGroup) {
		foreach ($arrHaplotypeGroup["cultivars"] as $arrCultivar) {
			$objHaplotype->download->content .= $arrHaplotypeGroup["group_number"]."\t".$arrCultivar["name"]."\t".$arrHaplotypeGroup["haplotype"].PHP_EOL;
		}
	}
	//****************************************************************************************************************
	//	v--- PHP -- 1C - END of preparing the results
	//****************************************************************************************************************
	//****************************************************************************************************************
	//	^--- PHP -- 1E - START of creating the download
	//****************************************************************************************************************
	header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="'.$objHaplotype->download->name.'"');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . strlen($objHaplotype->download->content));
	echo $objHaplotype->download->content;
	//****************************************************************************************************************
	//	v--- PHP -- 1E - END of creating the download
	//****************************************************************************************************************
?>
