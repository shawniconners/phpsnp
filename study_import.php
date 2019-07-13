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
	$objStudy->sql = "SELECT * FROM tblStudies WHERE id = :id;";
	$objStudy->prepare = $objSettings->database->connection->prepare($objStudy->sql);
	$objStudy->prepare->bindValue(':id', $objStudy->id, PDO::PARAM_INT);
	$objStudy->prepare->execute();
	$objStudy->database_record = $objStudy->prepare->fetchAll(PDO::FETCH_ASSOC)[0];
	$objStudy->vcf_chunks = json_decode($objStudy->database_record["vcf_chunks"]);
	//****************************************************************************************************************
	//	v--- PHP -- 1C - END of retrieving study information
	//****************************************************************************************************************
	//****************************************************************************************************************
	//	^--- PHP -- 1D - START of converting the study chunks to core requests
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
	foreach($objStudy->vcf_chunks as $arrChunkPosition){
		$objCoreRequest = new stdClass();
		$objCoreRequest->status = "ready"; // options: ready, active, complete
		$objCoreRequest->url = "study_import_script.php?id=".$objStudy->id."&chunk_start=".$arrChunkPosition[0]."&chunk_length=".$arrChunkPosition[1]."&chunk_line=".$arrChunkPosition[2];
		array_push($objCoreRequests->requests, $objCoreRequest);
	}
	shuffle($objCoreRequests->requests);
	//****************************************************************************************************************
	//	v--- PHP -- 1D - END of converting the study chunks to core requests
	//****************************************************************************************************************
	//echo "<pre>";
	//print_r($objStudy->vcf_chunks);
	//print_r($objCoreRequests);
	//echo "</pre>";
?>
<!doctype html>
<html lang="en">
	<head>
    	<meta charset="utf-8">
    	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    	<meta name="description" content="">
    	<meta name="author" content="">
    	<title>phpSNP - Study - VCF to Database Import</title>
    	<!-- Bootstrap core CSS -->
    	<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css" integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">
		<script>
			var objCoreRequests = <?php echo json_encode($objCoreRequests); ?>;
			objCoreRequests.interval = <?php echo $objSettings->loop_interval; ?>;
			objCoreRequests.requests_completed = 0;
			function funStudyImportStart(){
				funLoop();
			}
			function funLoop(){
				//console.log("Loop has started.");
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
								objCoreRequests.cores[intCoreCounter].ajax.open("GET", "http://"+objCoreRequests.cores[intCoreCounter].server+"/"+objCoreRequests.requests[intRequestCounter].url+"&core="+intCoreCounter.toString(), true);
								objCoreRequests.cores[intCoreCounter].ajax.send();
								//console.log("Active and Requested: Core Key " + intCoreCounter + " with Request Key " + objCoreRequests.cores[intCoreCounter].request_key);
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
								console.log("Error: http://"+objCoreRequests.cores[intCoreCounter].server+"/"+objCoreRequests.requests[intRequestCounter].url+"&core="+intCoreCounter.toString());
								console.log(objCoreRequests.cores[intCoreCounter].ajax.responseText);
							}
						}
					}
				}
				elmStudyImportProgress = document.getElementById("elmStudyImportProgress");
				elmStudyImportProgressPercent = document.getElementById("elmStudyImportProgressPercent");
				if(objCoreRequests.requests_completed < objCoreRequests.requests.length){
					//console.log("Waiting for requests to complete. Relooping requested.");
					elmStudyImportProgress.value = objCoreRequests.requests_completed / objCoreRequests.requests.length;
					elmStudyImportProgressPercent.innerHTML = parseInt(elmStudyImportProgress.value * 100) + "% Complete"
					setTimeout(function(){funLoop();}, objCoreRequests.interval);
				}else{
					//console.log("All requests completed.");
					elmStudyImportProgressPercent.innerHTML = "100% Complete. Redirecting..."
					window.location.href = "study_clean.php?id=<?php echo $objStudy->id; ?>";
				}
				//console.log("Loop has finished.");
			}
		</script>
  	</head>
	<body onload="funStudyImportStart()">
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
					<h2>Study - VCF to Database Import</h2>
					<p class="lead">Your VCF field identification settings have been saved to the database. The SNPs within your study are now being imported into the database. SNPs are also being compared against the assigned assembly to ensure reference values are accurate.</p>
		  		</div>
				<div class="row">
					<progress id="elmStudyImportProgress" value="0" max="1"></progress>
				</div>
				<div class="row">
					<h6 class="text-muted" id="elmStudyImportProgressPercent"></h6>
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
