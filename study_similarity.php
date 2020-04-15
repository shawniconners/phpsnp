<?php
	//****************************************************************************************************************
	//	^--- PHP -- 1A - START of startup
	//****************************************************************************************************************
	include "startup.php";
	//****************************************************************************************************************
	//	v--- PHP -- 1A - END of startup
	//****************************************************************************************************************
	//****************************************************************************************************************
	//	^--- PHP -- 1B - START of receiving the id for the study
	//****************************************************************************************************************
	$objRequest = new stdClass();
	$objRequest->id = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);
	//****************************************************************************************************************
	//	v--- PHP -- 1B - END of receiving the id for the study
	//****************************************************************************************************************
	//****************************************************************************************************************
	//	^--- PHP -- 1C - START of retrieving study information
	//****************************************************************************************************************
	$objStudy = new stdClass();
	$objStudy->id = $objRequest->id;
	$objStudy->sql = "SELECT id, assembly_id, cultivars, structures FROM tblStudies WHERE id = :id;";
	$objStudy->prepare = $objSettings->database->connection->prepare($objStudy->sql);
	$objStudy->prepare->bindValue(':id', $objStudy->id, PDO::PARAM_INT);
	$objStudy->prepare->execute();
	$objStudy->database_record = $objStudy->prepare->fetchAll(PDO::FETCH_ASSOC)[0];
	$objStudy->assembly_id = $objStudy->database_record["assembly_id"];
	$objStudy->cultivars = json_decode($objStudy->database_record["cultivars"]);
	$objStudy->structures = json_decode($objStudy->database_record["structures"]);
	$objStudy->database_record = [];
	//****************************************************************************************************************
	//	v--- PHP -- 1C - END of retrieving study information
	//****************************************************************************************************************
	//****************************************************************************************************************
	//	^--- PHP -- 1D - START of preparing similarity job information
	//****************************************************************************************************************
	$objSimilarityJob = new stdClass();
	$objSimilarityJob->study_id = $objStudy->id;
	$objSimilarityJob->cultivars = [];
	$objSimilarityJob->sequences = [];
	foreach($objStudy->cultivars as $intCultivarKey => $strCultivarName){
		array_push($objSimilarityJob->cultivars, $intCultivarKey);
	}
	foreach($objStudy->structures as $intStructureId){
		$objSimilarityJobStructure = new stdClass();
		$objSimilarityJobStructure->id = $intStructureId;
		$objSimilarityJobStructure->start = 1;
		$objSimilarityJobStructure->sql = "SELECT sequence_length FROM tblStructures WHERE id = :id;";
		$objSimilarityJobStructure->prepare = $objSettings->database->connection->prepare($objSimilarityJobStructure->sql);
		$objSimilarityJobStructure->prepare->bindValue(':id', $objSimilarityJobStructure->id, PDO::PARAM_INT);
		$objSimilarityJobStructure->prepare->execute();
		$objSimilarityJobStructure->database_record = $objSimilarityJobStructure->prepare->fetchAll(PDO::FETCH_ASSOC)[0];
		$objSimilarityJobStructure->stop = $objSimilarityJobStructure->database_record["sequence_length"];
		unset($objSimilarityJobStructure->database_record);
		unset($objSimilarityJobStructure->prepare);
		unset($objSimilarityJobStructure->sql);
		array_push($objSimilarityJob->sequences, $objSimilarityJobStructure);
	}
	//****************************************************************************************************************
	//	v--- PHP -- 1D - END of preparing similarity job information
	//****************************************************************************************************************
	//****************************************************************************************************************
	//	^--- PHP -- 1E - START of adding the Similarity Jobs record to the database and getting the insert id
	//****************************************************************************************************************
	$objSimilarityJob->sql = "INSERT INTO tblSimilarityJobs (study_id, sequences, cultivars) VALUES (:study_id, :sequences, :cultivars);";
	$objSimilarityJob->prepare = $objSettings->database->connection->prepare($objSimilarityJob->sql);
	$objSimilarityJob->prepare->bindValue(':study_id', $objSimilarityJob->study_id, PDO::PARAM_INT);
	$objSimilarityJob->prepare->bindValue(':sequences', json_encode($objSimilarityJob->sequences), PDO::PARAM_STR);
	$objSimilarityJob->prepare->bindValue(':cultivars', json_encode($objSimilarityJob->cultivars), PDO::PARAM_STR);
	$objSimilarityJob->prepare->execute();
	$objSimilarityJob->id = $objSettings->database->connection->lastInsertId();
	unset($objSimilarityJob->prepare);
	unset($objSimilarityJob->sql);
	//****************************************************************************************************************
	//	v--- PHP -- 1E - END of adding the Similarity Jobs record to the database and getting the insert id
	//****************************************************************************************************************
	//****************************************************************************************************************
	//	^--- PHP -- 1F - START of converting the cultivars to core requests
	//****************************************************************************************************************
	$objCoreRequests = new stdClass();
	$objCoreRequests->cores = [];
	$objCoreRequests->requests = [];
	for($intCoreCounter = 0; $intCoreCounter < count($objSettings->webservers); $intCoreCounter++){
		$objCore = new stdClass();
		$objCore->status = "ready"; // options: ready, active
		$objCore->request_key = -1;
		$objCore->server = $objSettings->webservers[$intCoreCounter];
		array_push($objCoreRequests->cores, $objCore);
	}
	foreach($objSimilarityJob->cultivars as $intCultivarKey){
		$objCoreRequest = new stdClass();
		$objCoreRequest->status = "ready"; // options: ready, active, complete
		$objCoreRequest->url = "study_similarity_script.php?id=".$objSimilarityJob->id."&cultivar_key=".$intCultivarKey;
		array_push($objCoreRequests->requests, $objCoreRequest);
	}



	//****************************************************************************************************************
	//	v--- PHP -- 1F - END of converting the cultivars to core requests
	//****************************************************************************************************************
?>
<!doctype html>
<html lang="en">
	<head>
    	<meta charset="utf-8">
    	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    	<meta name="description" content="">
    	<meta name="author" content="">
    	<title>phpSNP - Study - Cultivar Similarity Analysis</title>
    	<!-- Bootstrap core CSS -->
    	<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css" integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">
		<script src="scripts.js"></script>
		<script>
			var boolConsoleLogging = <?php echo $objSettings->console_logging; ?>;
			var objCoreRequests = <?php echo json_encode($objCoreRequests); ?>;
			objCoreRequests.interval = <?php echo $objSettings->loop_interval; ?>;
			objCoreRequests.requests_completed = 0;
			function funStudySimilarityStart(){
				funLoop();
			}
			function funLoop(){
				funConsoleLog("Loop has started.");
				for(intCoreCounter = 0; intCoreCounter < objCoreRequests.cores.length; intCoreCounter++){
					if(objCoreRequests.cores[intCoreCounter].status === "ready"){
						// at least one core is ready, we need to check to see if a request is also ready
						for(intRequestCounter = 0; intRequestCounter < objCoreRequests.requests.length; intRequestCounter++){
							if(objCoreRequests.requests[intRequestCounter].status === "ready"){
								// a core and a request are ready and we can send
								objCoreRequests.requests[intRequestCounter].status = "active";
								objCoreRequests.cores[intCoreCounter].status = "active";
								objCoreRequests.cores[intCoreCounter].request_key = intRequestCounter;
								objCoreRequests.cores[intCoreCounter].ajax = new XMLHttpRequest();
								objCoreRequests.cores[intCoreCounter].ajax.open("GET", location.protocol+"//"+objCoreRequests.cores[intCoreCounter].server+"/"+objCoreRequests.requests[intRequestCounter].url+"&core="+intCoreCounter.toString(), true);
								objCoreRequests.cores[intCoreCounter].ajax.send();
								funConsoleLog("Active and Requested: Core Key " + intCoreCounter + " with Request Key " + objCoreRequests.cores[intCoreCounter].request_key);
								break;
							}
						}
					}else if(objCoreRequests.cores[intCoreCounter].status === "active"){
						// this core is active and we need to check to see if a response has been received
						if(objCoreRequests.cores[intCoreCounter].ajax.readyState === 4 && objCoreRequests.cores[intCoreCounter].ajax.status === 200){
							//a response from a request has been received, mark the request as complete and set the core to ready
							//console.log("Response Complete: Core Key " + intCoreCounter + " with Request Key " + objCoreRequests.cores[intCoreCounter].request_key);
							objCoreRequests.requests_completed++;
							objCoreRequests.requests[objCoreRequests.cores[intCoreCounter].request_key].status = "complete";
							objCoreRequests.cores[intCoreCounter].status = "ready";
							if(objCoreRequests.cores[intCoreCounter].ajax.responseText > ""){
								funConsoleLog("Error: http://"+objCoreRequests.cores[intCoreCounter].server+"/"+objCoreRequests.requests[intRequestCounter].url+"&core="+intCoreCounter.toString());
								funConsoleLog(objCoreRequests.cores[intCoreCounter].ajax.responseText);
							}
						}
					}
				}
				elmStudySimilarityProgress = document.getElementById("elmStudySimilarityProgress");
				elmStudySimilarityProgressPercent = document.getElementById("elmStudySimilarityProgressPercent");
				if(objCoreRequests.requests_completed < objCoreRequests.requests.length){
					funConsoleLog("Waiting for requests to complete. Relooping requested.");
					elmStudySimilarityProgress.value = objCoreRequests.requests_completed / objCoreRequests.requests.length;
					elmStudySimilarityProgressPercent.innerHTML = parseInt(elmStudySimilarityProgress.value * 100) + "% Complete"
					setTimeout(function(){funLoop();}, objCoreRequests.interval);
				}else{
					funConsoleLog("All requests completed.");
					elmStudySimilarityProgressPercent.innerHTML = "100% Complete. Redirecting..."
					window.location.href = "assembly.php?assembly_id=<?php echo $objStudy->assembly_id; ?>";
				}
				funConsoleLog("Loop has finished.");
			}
		</script>
		<link rel="stylesheet" href="styles.css">
  	</head>
	<body onload="funStudySimilarityStart()">
		<?php
			//****************************************************************************************************************
			//	^--- PHP -- 3A - START of header
			//****************************************************************************************************************
			include "header.php";
			//****************************************************************************************************************
			//	v--- PHP -- 3A - END of header
			//****************************************************************************************************************
		?>
	    <main role="main">
	      	<div class="container">
		        <div class="row">
					<h2>Study - Cultivar Similarity Analysis</h2>
					<p class="lead">Your database has been cleaned. The final step in the process is a genome-wide similarity anaylsis. Due to the complexity of this analysis, this may take a very long time. Please make sure to leave this window open until the processing is complete.</p>
		  		</div>
				<div class="row mt-2 mb-4">
					<ul class="step d-flex flex-nowrap">
						<li class="step-item">
							<span class="">Step 1<br />Details and File Upload</span>
					  	</li>
						<li class="step-item">
							<span class="">Step 2<br />Field Identification</span>
						</li>
						<li class="step-item">
							<span class="">Step 3<br />Structure Tables Creation</span>
						</li>
						<li class="step-item">
							<span class="">Step 4<br />Database Import</span>
						</li>
						<li class="step-item">
							<span class="">Step 5<br />Cleaning Up</span>
						</li>
						<li class="step-item active">
							<span class="">Step 6<br />Cultivar Similarity Analysis</span>
						</li>
					</ul>
				</div>
				<div class="row justify-content-center">
					<progress id="elmStudySimilarityProgress" value="0" max="1"></progress>
				</div>
				<div class="row justify-content-center">
					<h6 class="text-muted" id="elmStudySimilarityProgressPercent"></h6>
				</div>
				<hr/>
			</div> <!-- /container -->
		</main>
		<?php
			//****************************************************************************************************************
			//	^--- PHP -- 3B - START of footer
			//****************************************************************************************************************
			include "footer.php";
			//****************************************************************************************************************
			//	v--- PHP -- 3B - END of footer
			//****************************************************************************************************************
		?>
	</body>
</html>
