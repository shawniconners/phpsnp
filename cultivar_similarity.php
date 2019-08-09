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
	$objRequest->assembly_id = filter_input(INPUT_GET, "assembly_id", FILTER_VALIDATE_INT);
	$objRequest->structure_id = filter_input(INPUT_GET, "structure_id", FILTER_VALIDATE_INT);
	$objRequest->start_position = filter_input(INPUT_GET, "start_position", FILTER_VALIDATE_INT);
	$objRequest->stop_position = filter_input(INPUT_GET, "stop_position", FILTER_VALIDATE_INT);
	$objRequest->study_id = filter_input(INPUT_GET, "study_id", FILTER_VALIDATE_INT);
	//****************************************************************************************************************
	//	v--- PHP -- 1B - END of receiving the id for the assembly, structure and study, along with start and stop
	//****************************************************************************************************************
	//****************************************************************************************************************
	//	^--- PHP -- 1C - START of preparing selections
	//****************************************************************************************************************
	$objAssembly = new stdClass();
	$objAssembly->id = $objRequest->assembly_id;
	$objAssembly->structure = new stdClass();
	$objAssembly->structure->id = $objRequest->structure_id;
	$objAssembly->structure->start_position = $objRequest->start_position;
	$objAssembly->structure->stop_position = $objRequest->stop_position;
	$objAssembly->study = new stdClass();
	$objAssembly->study->id = $objRequest->study_id;
	//****************************************************************************************************************
	//	v--- PHP -- 1C - END of preparing selections
	//****************************************************************************************************************
	//****************************************************************************************************************
	//	^--- PHP -- 1D - START of retrieving the assembly
	//****************************************************************************************************************
	$objAssembly->sql = "SELECT name FROM tblAssemblies WHERE id = :assembly_id;";
	$objAssembly->prepare = $objSettings->database->connection->prepare($objAssembly->sql);
	$objAssembly->prepare->bindValue(':assembly_id', $objAssembly->id, PDO::PARAM_INT);
	$objAssembly->prepare->execute();
	$objAssembly->database_record = $objAssembly->prepare->fetchAll(PDO::FETCH_ASSOC)[0];
	$objAssembly->name = $objAssembly->database_record["name"];
	//****************************************************************************************************************
	//	v--- PHP -- 1D - END of retrieving the assembly
	//****************************************************************************************************************
	//****************************************************************************************************************
	//	^--- PHP -- 1E - START of retrieving the selected structure
	//****************************************************************************************************************
	$objAssembly->sql = "SELECT name FROM tblStructures WHERE assembly_id = :assembly_id AND id = :structure_id;";
	$objAssembly->prepare = $objSettings->database->connection->prepare($objAssembly->sql);
	$objAssembly->prepare->bindValue(':assembly_id', $objAssembly->id, PDO::PARAM_INT);
	$objAssembly->prepare->bindValue(':structure_id', $objAssembly->structure->id, PDO::PARAM_INT);
	$objAssembly->prepare->execute();
	$objAssembly->database_record = $objAssembly->prepare->fetchAll(PDO::FETCH_ASSOC)[0];
	$objAssembly->structure->name = $objAssembly->database_record["name"];
	//****************************************************************************************************************
	//	v--- PHP -- 1E - END of retrieving the selected structure
	//****************************************************************************************************************
	//****************************************************************************************************************
	//	^--- PHP -- 1F - START of retrieving the selected study
	//****************************************************************************************************************
	$objAssembly->sql = "SELECT name, cultivar_count, cultivars FROM tblStudies WHERE assembly_id = :assembly_id AND id = :study_id;";
	$objAssembly->prepare = $objSettings->database->connection->prepare($objAssembly->sql);
	$objAssembly->prepare->bindValue(':assembly_id', $objAssembly->id, PDO::PARAM_INT);
	$objAssembly->prepare->bindValue(':study_id', $objAssembly->study->id, PDO::PARAM_INT);
	$objAssembly->prepare->execute();
	$objAssembly->database_record = $objAssembly->prepare->fetchAll(PDO::FETCH_ASSOC)[0];
	$objAssembly->study->name = $objAssembly->database_record["name"];
	$objAssembly->study->cultivar_count = $objAssembly->database_record["cultivar_count"];
	$objAssembly->study->cultivars = json_decode($objAssembly->database_record["cultivars"]);
	//****************************************************************************************************************
	//	v--- PHP -- 1F - END of retrieving the selected study
	//****************************************************************************************************************
	//****************************************************************************************************************
	//	^--- PHP -- 1F - START of retrieving the snp count
	//****************************************************************************************************************
	$objAssembly->sql = "SELECT count(id) AS snp_count FROM tblStudy".$objAssembly->study->id."Structure".$objAssembly->structure->id."SNPs WHERE position >= :start_position AND position <= :stop_position;";
	$objAssembly->prepare = $objSettings->database->connection->prepare($objAssembly->sql);
	$objAssembly->prepare->bindValue(':start_position', $objAssembly->structure->start_position, PDO::PARAM_INT);
	$objAssembly->prepare->bindValue(':stop_position', $objAssembly->structure->stop_position, PDO::PARAM_INT);
	$objAssembly->prepare->execute();
	$objAssembly->database_record = $objAssembly->prepare->fetchAll(PDO::FETCH_ASSOC)[0];
	$objAssembly->study->snp_count = $objAssembly->database_record["snp_count"];
	//****************************************************************************************************************
	//	v--- PHP -- 1F - END of retrieving the snp count
	//****************************************************************************************************************
	//****************************************************************************************************************
	//	^--- PHP -- 1G - START of converting the cultivars to core requests
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
	//for($intLoopCounter = 0; $intLoopCounter < (count($objAssembly->study->cultivars)-1); $intLoopCounter++){
	//for($intLoopCounter = 10000; $intLoopCounter < 10010; $intLoopCounter++){
	//for($intLoopCounter = (count($objAssembly->study->cultivars)-1); $intLoopCounter > 19000 ; $intLoopCounter--){
	for($intLoopCounter = 10500; $intLoopCounter > 10000 ; $intLoopCounter--){
		$objRequest = new stdClass();
		$objRequest->status = "ready"; // options: ready, active, complete
		$objRequest->url = "cultivar_similarity_script.php?study_id=".$objAssembly->study->id."&structure_id=".$objAssembly->structure->id."&start_position=".$objAssembly->structure->start_position."&stop_position=".$objAssembly->structure->stop_position."&cultivar_key=".$intLoopCounter."&cultivar_count=".$objAssembly->study->cultivar_count;
		array_push($objCoreRequests->requests, $objRequest);
	}
	//print_r($objCoreRequests);
	//****************************************************************************************************************
	//	v--- PHP -- 1G - END of converting the cultivars to core requests
	//****************************************************************************************************************
?>
<!doctype html>
<html lang="en">
	<head>
    	<meta charset="utf-8">
    	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    	<meta name="description" content="">
    	<meta name="author" content="">
    	<title>phpSNP - Cultivar Similarity</title>
    	<!-- Bootstrap core CSS -->
    	<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css" integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">
		<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/v/bs4/dt-1.10.16/b-1.4.2/b-html5-1.4.2/fc-3.2.3/fh-3.1.3/sc-1.4.3/sl-1.2.3/datatables.min.css"/>
		<link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/clusterize.js/0.18.0/clusterize.min.css"/>
		<script src="scripts.js"></script>
		<script>
			var boolConsoleLogging = <?php echo $objSettings->console_logging; ?>;
			var objCoreRequests = <?php echo json_encode($objCoreRequests); ?>;
			objCoreRequests.interval = <?php echo $objSettings->loop_interval; ?>;
			objCoreRequests.requests_completed = 0;
			function funCultivarSimilarityStart(){
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
							funConsoleLog("Response Complete: Core Key " + intCoreCounter + " with Request Key " + objCoreRequests.cores[intCoreCounter].request_key);
							objCoreRequests.requests_completed++;
							objCoreRequests.requests[objCoreRequests.cores[intCoreCounter].request_key].status = "complete";
							objCoreRequests.cores[intCoreCounter].status = "ready";
						}
					}
				}
				elmCultivarSimilarityProgress = document.getElementById("elmCultivarSimilarityProgress");
				elmCultivarSimilarityProgressPercent = document.getElementById("elmCultivarSimilarityProgressPercent");
				if(objCoreRequests.requests_completed < objCoreRequests.requests.length){
					funConsoleLog("Waiting for requests to complete. Relooping requested.");
					elmCultivarSimilarityProgress.value = objCoreRequests.requests_completed / objCoreRequests.requests.length;
					elmCultivarSimilarityProgressPercent.innerHTML = parseInt(elmCultivarSimilarityProgress.value * 100) + "% Complete"
					setTimeout(function(){funLoop();}, objCoreRequests.interval);
				}else{
					funConsoleLog("All requests completed.");
					elmCultivarSimilarityProgressPercent.innerHTML = "100% Complete. Redirecting..."
					//window.location.href = "curate.php";
				}
				funConsoleLog("Loop has finished.");
			}
		</script>
	</head>
	<body onload="funCultivarSimilarityStart()">
		<?php
			//****************************************************************************************************************
			//	^--- PHP -- 3A - START of header pop
			//****************************************************************************************************************
			include "header_pop.php";
			//****************************************************************************************************************
			//	v--- PHP -- 3A - END of header pop
			//****************************************************************************************************************
		?>
	    <main role="main">
	      	<div class="container-fluid">
				<div class="row">
					<div class="col-3">
						<div class="card-header text-white bg-secondary font-weight-bold">
							Study
						</div>
						<div class="card-body">
							<h4 class="mb-0 mt-0"><?php echo $objAssembly->name;?></h4>
							<small class="mb-0 mt-0">Assembly</small>
							<h4 class="mb-0 mt-2"><?php echo $objAssembly->structure->name;?></h4>
							<small class="mb-0 mt-0">Structure</small>
							<h4 class="mb-0 mt-2"><?php echo $objAssembly->structure->start_position;?></h4>
							<small class="mb-0 mt-0">Start</small>
							<h4 class="mb-0 mt-2"><?php echo $objAssembly->structure->stop_position;?></h4>
							<small class="mb-0 mt-0">Stop</small>
							<h4 class="mb-0 mt-2"><?php echo $objAssembly->study->name;?></h4>
							<small class="mb-0 mt-0">Study</small>
							<h4 class="mb-0 mt-2"><?php echo $objAssembly->study->cultivar_count;?></h4>
							<small class="mb-0 mt-0">Cultivars</small>
							<h4 class="mb-0 mt-2"><?php echo $objAssembly->study->snp_count;?></h4>
							<small class="mb-0 mt-0">SNPs</small>
						</div>
					</div>
					<div class="col-9">
						<div class="card-header text-white bg-secondary font-weight-bold">
							Cultivar Similarity
						</div>
						<div class="card-body">
							<div class="row justify-content-center">
								<progress id="elmCultivarSimilarityProgress" value="0" max="1"></progress>
							</div>
							<div class="row justify-content-center">
								<h6 class="text-muted" id="elmCultivarSimilarityProgressPercent"></h6>
							</div>
						</div>
					</div>
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
