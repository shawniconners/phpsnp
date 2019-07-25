<?php
	//****************************************************************************************************************
	//	^--- PHP -- 1A - START of startup
	//****************************************************************************************************************
	include "startup.php";
	//****************************************************************************************************************
	//	v--- PHP -- 1A - END of startup
	//****************************************************************************************************************
	//****************************************************************************************************************
	//	^--- PHP -- 1B - START of receiving the id for the assembly
	//****************************************************************************************************************
	$objRequest = new stdClass();
	$objRequest->id = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);
	//****************************************************************************************************************
	//	v--- PHP -- 1B - END of receiving the id for the assembly
	//****************************************************************************************************************
	//****************************************************************************************************************
	//	^--- PHP -- 1C - START of retrieving the assembly
	//****************************************************************************************************************
	$objAssembly = new stdClass();
	$objAssembly->sql = "SELECT * FROM tblAssemblies WHERE id = :id;";
	$objAssembly->prepare = $objSettings->database->connection->prepare($objAssembly->sql);
	$objAssembly->prepare->bindValue(':id', $objRequest->id, PDO::PARAM_INT);
	$objAssembly->prepare->execute();
	$objAssembly->database_record = $objAssembly->prepare->fetchAll(PDO::FETCH_ASSOC)[0];
	$objAssembly->id = $objAssembly->database_record["id"];
	$objAssembly->name = $objAssembly->database_record["name"];
	$objAssembly->source = $objAssembly->database_record["source"];
	unset($objAssembly->database_record);
	//****************************************************************************************************************
	//	v--- PHP -- 1C - END of retrieving the assembly
	//****************************************************************************************************************
	//****************************************************************************************************************
	//	^--- PHP -- 1D - START of retrieving and preparing the assembly structures
	//****************************************************************************************************************
	$objAssembly->structures = [];
	$objAssembly->sql = "SELECT id FROM tblStructures WHERE assembly_id = :assembly_id ORDER BY RAND();";
	$objAssembly->prepare = $objSettings->database->connection->prepare($objAssembly->sql);
	$objAssembly->prepare->bindValue(':assembly_id', $objAssembly->id, PDO::PARAM_INT);
	$objAssembly->prepare->execute();
	$objAssembly->structures = $objAssembly->prepare->fetchAll(PDO::FETCH_ASSOC);
	//****************************************************************************************************************
	//	v--- PHP -- 1D - END of retrieving and preparing the assembly structures
	//****************************************************************************************************************
	//****************************************************************************************************************
	//	^--- PHP -- 1E - START of converting the assembly structures to core requests
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
	foreach($objAssembly->structures as $arrStructure){
		$objRequest = new stdClass();
		$objRequest->status = "ready"; // options: ready, active, complete
		$objRequest->url = "assembly_import_script.php?id=".$arrStructure["id"];
		array_push($objCoreRequests->requests, $objRequest);
	}
	//****************************************************************************************************************
	//	v--- PHP -- 1E - END of converting the assembly structures to core requests
	//****************************************************************************************************************
?>
<!doctype html>
<html lang="en">
	<head>
    	<meta charset="utf-8">
    	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    	<meta name="description" content="">
    	<meta name="author" content="">
    	<title>phpSNP - Assembly - Import</title>
    	<!-- Bootstrap core CSS -->
    	<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css" integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">
		<script src="scripts.js"></script>
		<script>
			var boolConsoleLogging = <?php echo $objSettings->console_logging; ?>;
			var objCoreRequests = <?php echo json_encode($objCoreRequests); ?>;
			objCoreRequests.interval = <?php echo $objSettings->loop_interval; ?>;
			objCoreRequests.requests_completed = 0;
			function funAssemblyImportStart(){
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
				elmAssemblyImportProgress = document.getElementById("elmAssemblyImportProgress");
				elmAssemblyImportProgressPercent = document.getElementById("elmAssemblyImportProgressPercent");
				if(objCoreRequests.requests_completed < objCoreRequests.requests.length){
					funConsoleLog("Waiting for requests to complete. Relooping requested.");
					elmAssemblyImportProgress.value = objCoreRequests.requests_completed / objCoreRequests.requests.length;
					elmAssemblyImportProgressPercent.innerHTML = parseInt(elmAssemblyImportProgress.value * 100) + "% Complete"
					setTimeout(function(){funLoop();}, objCoreRequests.interval);
				}else{
					funConsoleLog("All requests completed.");
					elmAssemblyImportProgressPercent.innerHTML = "100% Complete. Redirecting..."
					window.location.href = "curate.php";
				}
				funConsoleLog("Loop has finished.");
			}
		</script>
		<link rel="stylesheet" href="styles.css">
  	</head>
	<body onload="funAssemblyImportStart()">
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
					<h2>Assembly - Import</h2>
					<p class="lead">Your assembly has been uploaded to the server and a preliminary scan has been completed. Assembly structure sequences are being imported into the database.</p>
		  		</div>
				<div class="row mt-2 mb-4">
					<ul class="step d-flex flex-nowrap">
						<li class="step-item">
							<span class="">Step 1<br />Details and File Upload</span>
					  	</li>
						<li class="step-item active">
							<span class="">Step 2<br />Database Import</span>
						</li>
					</ul>
				</div>
				<div class="row justify-content-center">
					<progress id="elmAssemblyImportProgress" value="0" max="1"></progress>
				</div>
				<div class="row justify-content-center">
					<h6 class="text-muted" id="elmAssemblyImportProgressPercent"></h6>
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
