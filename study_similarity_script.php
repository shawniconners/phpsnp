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
	$objSimilarityJobResult->results = [];
	//****************************************************************************************************************
	//	v--- PHP -- 1D - END of preparing the similarity job result
	//****************************************************************************************************************
	//****************************************************************************************************************
	//	^--- PHP -- 1E - START of looping through the similarity job cultivars
	//****************************************************************************************************************
	foreach($objSimilarityJob->cultivars as $intCultivarKey){
		if($objSimilarityJobResult->cultivar_key <= $intCultivarKey){
			array_push($objSimilarityJobResult->results, null);
		}else{


			//****************************************************************************************************************
			//	^--- PHP -- 3A - START of preparing the cultivar score
			//****************************************************************************************************************
			$objSimilarityJobCultivarScore = new stdClass();
			$objSimilarityJobCultivarScore->total_snps = 0;
			$objSimilarityJobCultivarScore->matching_snps = 0;
			//array_push($objSimilarityJobResult->results, .222);
			//****************************************************************************************************************
			//	v--- PHP -- 3A - END of preparing the cultivar score
			//****************************************************************************************************************


			//****************************************************************************************************************
			//	^--- PHP -- 3B - START of looping through each sequence and finding matching snps for these two cultivars
			//****************************************************************************************************************
			foreach($objSimilarityJob->sequences as $objSequence){
				$objSimilarityJobCultivarScore->sql = "SELECT JSON_UNQUOTE(JSON_EXTRACT(results, '$[".$objSimilarityJobResult->cultivar_key."]')) AS base_cultivar, JSON_UNQUOTE(JSON_EXTRACT(results, '$[".$intCultivarKey."]')) AS comparison_cultivar FROM tblStudy".$objSimilarityJob->study_id."Structure".$objSequence->id."SNPs WHERE position >= :start AND position <= :stop";
				$objSimilarityJobCultivarScore->prepare = $objSettings->database->connection->prepare($objSimilarityJobCultivarScore->sql);
				$objSimilarityJobCultivarScore->prepare->bindValue(':start', $objSequence->start, PDO::PARAM_INT);
				$objSimilarityJobCultivarScore->prepare->bindValue(':stop', $objSequence->stop, PDO::PARAM_INT);
				$objSimilarityJobCultivarScore->prepare->execute();
				$objSimilarityJobCultivarScore->snps = $objSimilarityJobCultivarScore->prepare->fetchAll(PDO::FETCH_ASSOC);
				$objSimilarityJobCultivarScore->total_snps += count($objSimilarityJobCultivarScore->snps);
				foreach($objSimilarityJobCultivarScore->snps as $arrSNP){
					if(strcmp($arrSNP["base_cultivar"], $arrSNP["comparison_cultivar"]) === 0){
						$objSimilarityJobCultivarScore->matching_snps += 1;
					}
				}
			}
			//****************************************************************************************************************
			//	v--- PHP -- 3B - END of looping through each sequence and finding matching snps for these two cultivars
			//****************************************************************************************************************

			//****************************************************************************************************************
			//	^--- PHP -- 3C - START of finalizing the similarity for these two cultivars
			//****************************************************************************************************************
			$objSimilarityJobCultivarScore->score = $objSimilarityJobCultivarScore->matching_snps / $objSimilarityJobCultivarScore->total_snps;
			array_push($objSimilarityJobResult->results, $objSimilarityJobCultivarScore->score);
			//****************************************************************************************************************
			//	v--- PHP -- 3C - END of finalizing the similarity for these two cultivars
			//****************************************************************************************************************

		}
	}
	//****************************************************************************************************************
	//	v--- PHP -- 1E - END of looping through the similarity job cultivars
	//****************************************************************************************************************
	//****************************************************************************************************************
	//	^--- PHP -- 1F - START of adding similarity job results to database
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
	//	v--- PHP -- 1F - END of adding similarity job results to database
	//****************************************************************************************************************



	//echo "<pre>";
	//print_r($objSimilarityJobResult);
	//echo "</pre>";
	//exit;



?>
