<?php
	//****************************************************************************************************************
	//	^--- PHP -- 1A - START of startup
	//****************************************************************************************************************
	include "startup.php";
	//****************************************************************************************************************
	//	v--- PHP -- 1A - END of startup
	//****************************************************************************************************************
	//****************************************************************************************************************
	//	^--- PHP -- 1B - START of receiving the id of the similarity job and the cultivar key
	//****************************************************************************************************************
	$objRequest = new stdClass();
	$objRequest->id = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);
	$objRequest->cultivar_key = filter_input(INPUT_GET, "cultivar_key", FILTER_VALIDATE_INT);
	//****************************************************************************************************************
	//	v--- PHP -- 1B - END of receiving the id of the study and structure_id
	//****************************************************************************************************************
	//****************************************************************************************************************
	//	^--- PHP -- 1C - START of finding the requested similarity job details
	//****************************************************************************************************************
	$objSimilarityJob = new stdClass();
	$objSimilarityJob->id = $objRequest->id;
	$objSimilarityJob->requested_cultivar_key = $objRequest->cultivar_key;
	$objSimilarityJob->study_id = 0;
	$objSimilarityJob->sequences = [];
	$objSimilarityJob->cultivars = [];
	$objSimilarityJob->sql = "SELECT * FROM tblSimilarityJobs WHERE id = :id";
	$objSimilarityJob->prepare = $objSettings->database->connection->prepare($objSimilarityJob->sql);
	$objSimilarityJob->prepare->bindValue(':id', $objSimilarityJob->id, PDO::PARAM_INT);
	$objSimilarityJob->prepare->execute();
	$objSimilarityJob->database_record = $objSimilarityJob->prepare->fetchAll(PDO::FETCH_ASSOC)[0];
	$objSimilarityJob->study_id = $objSimilarityJob->database_record["study_id"];
	$objSimilarityJob->sequences = json_decode($objSimilarityJob->database_record["sequences"]);
	$objSimilarityJob->cultivars = json_decode($objSimilarityJob->database_record["cultivars"]);
	unset($objSimilarityJob->sql);
	unset($objSimilarityJob->prepare);
	unset($objSimilarityJob->database_record);
	//****************************************************************************************************************
	//	v--- PHP -- 1C - END of finding the requested similarity job details
	//****************************************************************************************************************
	//****************************************************************************************************************
	//	^--- PHP -- 1D - START of preparing the similarity job result
	//****************************************************************************************************************
	$objSimilarityJobResult = new stdClass();
	$objSimilarityJobResult->similarity_job_id = $objSimilarityJob->id;
	$objSimilarityJobResult->cultivar_key = $objSimilarityJob->requested_cultivar_key;
	$objSimilarityJobResult->total_snps = 0;
	$objSimilarityJobResult->sql_fields = [];
	$objSimilarityJobResult->sql_fields_text = "";
	$objSimilarityJobResult->results = [];
	$objSimilarityJobResult->results = array_fill(0, count($objSimilarityJob->cultivars), 0);
	//****************************************************************************************************************
	//	v--- PHP -- 1D - END of preparing the similarity job result
	//****************************************************************************************************************
	//****************************************************************************************************************
	//	^--- PHP -- 1E - START of perparing the sql statement to retrieve only the relevant json array items
	//****************************************************************************************************************
	foreach($objSimilarityJob->cultivars as $intComparisonCultivarKey){
		if($objSimilarityJobResult->cultivar_key >= $intComparisonCultivarKey){
			array_push($objSimilarityJobResult->sql_fields, "JSON_UNQUOTE(JSON_EXTRACT(results, '$[".$intComparisonCultivarKey."]')) AS c_".$intComparisonCultivarKey);
		}
	}
	$objSimilarityJobResult->sql_fields_text = implode(", ", $objSimilarityJobResult->sql_fields);
	unset($objSimilarityJobResult->sql_fields);

	//****************************************************************************************************************
	//	v--- PHP -- 1E - END of perparing the sql statement to retrieve only the relevant json array items
	//****************************************************************************************************************
	//****************************************************************************************************************
	//	^--- PHP -- 1F - START of looping through each sequence and finding matching snps for all cultivars
	//****************************************************************************************************************
	foreach($objSimilarityJob->sequences as $objSequence){
		$objSimilarityJobResult->sql = "SELECT ".$objSimilarityJobResult->sql_fields_text." FROM tblStudy".$objSimilarityJob->study_id."Structure".$objSequence->id."SNPs WHERE position >= :start AND position <= :stop";
		$objSimilarityJobResult->prepare = $objSettings->database->connection->prepare($objSimilarityJobResult->sql);
		$objSimilarityJobResult->prepare->bindValue(':start', $objSequence->start, PDO::PARAM_INT);
		$objSimilarityJobResult->prepare->bindValue(':stop', $objSequence->stop, PDO::PARAM_INT);
		$objSimilarityJobResult->prepare->execute();
		$objSimilarityJobResult->snps = $objSimilarityJobResult->prepare->fetchAll(PDO::FETCH_ASSOC);
		$objSimilarityJobResult->total_snps += count($objSimilarityJobResult->snps);
		foreach($objSimilarityJobResult->snps as $arrSNP){
			foreach($objSimilarityJob->cultivars as $intComparisonCultivarKey){
				if($objSimilarityJobResult->cultivar_key > $intComparisonCultivarKey){
					if(strcmp($arrSNP["c_".$objSimilarityJobResult->cultivar_key], $arrSNP["c_".$intComparisonCultivarKey]) === 0){
						$objSimilarityJobResult->results[$intComparisonCultivarKey] += 1;
					}
				}
			}
		}
	}
	//****************************************************************************************************************
	//	v--- PHP -- 1F - END of looping through each sequence and finding matching snps for these two cultivars
	//****************************************************************************************************************
	//****************************************************************************************************************
	//	^--- PHP -- 1G - START of looping through and correcting all scores
	//****************************************************************************************************************
	foreach($objSimilarityJob->cultivars as $intComparisonCultivarKey){
		if($objSimilarityJobResult->cultivar_key > $intComparisonCultivarKey){
			// this is a score that should have been found
			$objSimilarityJobResult->results[$intComparisonCultivarKey] = $objSimilarityJobResult->results[$intComparisonCultivarKey] / $objSimilarityJobResult->total_snps;
		}else{
			// this is a score that should not have been calculated
			$objSimilarityJobResult->results[$intComparisonCultivarKey] = null;
		}
	}
	//****************************************************************************************************************
	//	v--- PHP -- 1G - END of looping through and correcting all scores
	//****************************************************************************************************************
	//****************************************************************************************************************
	//	^--- PHP -- 1H - START of adding similarity job results to database
	//****************************************************************************************************************
	$objSimilarityJobResult->sql = "INSERT INTO tblSimilarityJobResults (similarity_job_id, cultivar_key, results) VALUES (:similarity_job_id, :cultivar_key, :results);";
	$objSimilarityJobResult->prepare = $objSettings->database->connection->prepare($objSimilarityJobResult->sql);
	$objSimilarityJobResult->prepare->bindValue(':similarity_job_id', $objSimilarityJobResult->similarity_job_id, PDO::PARAM_INT);
	$objSimilarityJobResult->prepare->bindValue(':cultivar_key', $objSimilarityJobResult->cultivar_key, PDO::PARAM_INT);
	$objSimilarityJobResult->prepare->bindValue(':results', json_encode($objSimilarityJobResult->results), PDO::PARAM_STR);
	$objSimilarityJobResult->prepare->execute();
	unset($objSimilarityJob->prepare);
	unset($objSimilarityJob->sql);
	//****************************************************************************************************************
	//	v--- PHP -- 1H - END of adding similarity job results to database
	//****************************************************************************************************************



	//echo "<pre>";
	//print_r($objSimilarityJobResult);
	//echo "</pre>";
	//exit;



?>
