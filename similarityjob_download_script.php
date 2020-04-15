<?php
	//****************************************************************************************************************
	//	^--- PHP -- 1A - START of startup
	//****************************************************************************************************************
	include "startup.php";
	//****************************************************************************************************************
	//	v--- PHP -- 1A - END of startup
	//****************************************************************************************************************
	//****************************************************************************************************************
	//	^--- PHP -- 1B - START of receiving the similarity job id
	//****************************************************************************************************************
	$objRequest = new stdClass();
	$objRequest->similarity_job_id = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);
	//****************************************************************************************************************
	//	v--- PHP -- 1B - END  of receiving the similarity job id
	//****************************************************************************************************************
	//****************************************************************************************************************
	//	^--- PHP -- 1C - START of preparing the similarity job object
	//****************************************************************************************************************
	$objSimilarityJob = new stdClass();
	$objSimilarityJob->id = $objRequest->similarity_job_id;
	$objSimilarityJob->study_id = 0;
	$objSimilarityJob->study_name = 0;
	$objSimilarityJob->cultivar_names = [];
	$objSimilarityJob->cultivar_keys = [];
	$objSimilarityJob->results_raw = [];
	$objSimilarityJob->results_clean = [];
	//****************************************************************************************************************
	//	v--- PHP -- 1C - END of preparing the similarity job object
	//****************************************************************************************************************
	//****************************************************************************************************************
	//	^--- PHP -- 1D - START of retrieving information about the similarity job
	//****************************************************************************************************************
	$objSimilarityJob->sql = "SELECT id, study_id, cultivars FROM tblSimilarityJobs WHERE id = :id;";
	$objSimilarityJob->prepare = $objSettings->database->connection->prepare($objSimilarityJob->sql);
	$objSimilarityJob->prepare->bindValue(':id', $objSimilarityJob->id, PDO::PARAM_INT);
	$objSimilarityJob->prepare->execute();
	$objSimilarityJob->database_record = $objSimilarityJob->prepare->fetchAll(PDO::FETCH_ASSOC)[0];
	$objSimilarityJob->study_id = $objSimilarityJob->database_record["study_id"];
	$objSimilarityJob->cultivar_keys = json_decode($objSimilarityJob->database_record["cultivars"]);
	//****************************************************************************************************************
	//	v--- PHP -- 1D - END of retrieving information about the similarity job
	//****************************************************************************************************************
	//****************************************************************************************************************
	//	^--- PHP -- 1E - START of retrieving the cultivar names from the study table
	//****************************************************************************************************************
	$objSimilarityJob->sql = "SELECT id, name, cultivars FROM tblStudies WHERE id = :id;";
	$objSimilarityJob->prepare = $objSettings->database->connection->prepare($objSimilarityJob->sql);
	$objSimilarityJob->prepare->bindValue(':id', $objSimilarityJob->study_id, PDO::PARAM_INT);
	$objSimilarityJob->prepare->execute();
	$objSimilarityJob->database_record = $objSimilarityJob->prepare->fetchAll(PDO::FETCH_ASSOC)[0];
	$objSimilarityJob->study_name = $objSimilarityJob->database_record["name"];
	$objSimilarityJob->cultivar_names = json_decode($objSimilarityJob->database_record["cultivars"]);
	//****************************************************************************************************************
	//	v--- PHP -- 1E - END of retrieving the cultivar names from the study table
	//****************************************************************************************************************
	//****************************************************************************************************************
	//	^--- PHP -- 1F - START of retrieving the similarity job results
	//****************************************************************************************************************
	$objSimilarityJob->sql = "SELECT * FROM tblSimilarityJobResults WHERE similarity_job_id = :id ORDER BY cultivar_key ASC;";
	$objSimilarityJob->prepare = $objSettings->database->connection->prepare($objSimilarityJob->sql);
	$objSimilarityJob->prepare->bindValue(':id', $objSimilarityJob->id, PDO::PARAM_INT);
	$objSimilarityJob->prepare->execute();
	$objSimilarityJob->results_raw = $objSimilarityJob->prepare->fetchAll(PDO::FETCH_ASSOC);
	unset($objSimilarityJob->sql);
	unset($objSimilarityJob->prepare);
	//****************************************************************************************************************
	//	v--- PHP -- 1F - END of retrieving the similarity job results
	//****************************************************************************************************************
	//****************************************************************************************************************
	//	^--- PHP -- 1G - START of converting the raw results to a clean array
	//****************************************************************************************************************
	foreach($objSimilarityJob->results_raw as $arrSimilarityJobResult){
		$objSimilarityJob->results_clean[intval($arrSimilarityJobResult["cultivar_key"])] = json_decode($arrSimilarityJobResult["results"]);
	}
	unset($objSimilarityJob->results_raw);
	//****************************************************************************************************************
	//	v--- PHP -- 1G - END of converting the raw results to a clean array
	//****************************************************************************************************************
	//****************************************************************************************************************
	//	^--- PHP -- 1H - START of traversing the clean results array and filling it out
	//****************************************************************************************************************
	$intGridXPosition = -1; // column position
	$intGridYPosition = -1; // row position
	foreach($objSimilarityJob->results_clean as $intStudyCultivarKey => $arrSingleCultivarResults){
		$intGridXPosition = -1;
		$intGridYPosition++;
		foreach($arrSingleCultivarResults as $fltCultivarToCultivarResult){
			$intGridXPosition++;
			if($intGridXPosition === $intGridYPosition){
				// this can be set to 1.000
				$objSimilarityJob->results_clean[$intStudyCultivarKey][$intGridXPosition] = 1;
			}else if($intGridXPosition > $intGridYPosition){
				// this must be found and set to the found value
				$objSimilarityJob->results_clean[$intStudyCultivarKey][$intGridXPosition] = $objSimilarityJob->results_clean[$objSimilarityJob->cultivar_keys[$intGridXPosition]][$intGridYPosition];
			}
			$objSimilarityJob->results_clean[$intStudyCultivarKey][$intGridXPosition] = funSignificantFigures($objSimilarityJob->results_clean[$intStudyCultivarKey][$intGridXPosition], 3);
		}
	}
	//****************************************************************************************************************
	//	v--- PHP -- 1H - END of traversing the clean results array and filling it out
	//****************************************************************************************************************
	//****************************************************************************************************************
	//	^--- PHP -- 1I - START of preparing the download file
	//****************************************************************************************************************
	$objSimilarityJob->download = new stdClass();
	$objSimilarityJob->download->name = $objSimilarityJob->study_name."_Similarity_".count($objSimilarityJob->results_clean)."_Cultivars.tsv";
	$objSimilarityJob->download->content = "Cultivar";
	foreach($objSimilarityJob->results_clean as $intStudyCultivarKey => $arrSingleCultivarResults){
		$objSimilarityJob->download->content .= "\t".$objSimilarityJob->cultivar_names[$intStudyCultivarKey];
	}
	foreach($objSimilarityJob->results_clean as $intStudyCultivarKey => $arrSingleCultivarResults){
		$objSimilarityJob->download->content .= PHP_EOL;
		$objSimilarityJob->download->content .= $objSimilarityJob->cultivar_names[$intStudyCultivarKey]."\t".implode("\t", $arrSingleCultivarResults);
	}
	//****************************************************************************************************************
	//	v--- PHP -- 1I - END of preparing the download file
	//****************************************************************************************************************
	//****************************************************************************************************************
	//	^--- PHP -- 1J - START of creating the download
	//****************************************************************************************************************
	header('Content-Description: File Transfer');
    	header('Content-Type: application/octet-stream');
	header('Content-Disposition: attachment; filename="'.$objSimilarityJob->download->name.'"');
	header('Expires: 0');
	header('Cache-Control: must-revalidate');
	header('Pragma: public');
	header('Content-Length: ' . strlen($objSimilarityJob->download->content));
	echo $objSimilarityJob->download->content;
	//****************************************************************************************************************
	//	v--- PHP -- 1J - END of creating the download
	//****************************************************************************************************************
?>
