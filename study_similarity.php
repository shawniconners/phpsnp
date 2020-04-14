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
	// one time fix start
	/*
	$objSimilarityJob->predefined_cultivars = [
		"PI68731",
		"PI404182",
		"PI548503",
		"PI548574",
		"PI561321",
		"PI567731",
		"FC29333",
		"FC31571",
		"PI54591",
		"PI54615-1",
		"PI68423",
		"PI68521-1",
		"PI68523",
		"PI68604-1",
		"PI70080",
		"PI70201",
		"PI79616",
		"PI79797",
		"PI81041",
		"PI81785",
		"PI82302",
		"PI84509",
		"PI84656",
		"PI84973",
		"PI87618",
		"PI87620",
		"PI87634",
		"PI88306",
		"PI88448",
		"PI88788",
		"PI90486",
		"PI90723",
		"PI91083",
		"PI91089",
		"PI91160",
		"PI92728",
		"PI93563",
		"PI97139",
		"PI103088",
		"PI153231",
		"PI209331",
		"PI209334",
		"PI253661B",
		"PI398881",
		"PI404166",
		"PI407659B",
		"PI417077",
		"PI427136",
		"PI437321",
		"PI437476",
		"PI437487",
		"PI437578",
		"PI437655",
		"PI437685D",
		"PI437776",
		"PI438047",
		"PI438112B",
		"PI438310",
		"PI438335",
		"PI438496B",
		"PI438500",
		"PI458510",
		"PI464920B",
		"PI468408B",
		"PI475812B",
		"PI479735",
		"PI490766",
		"PI509044",
		"PI518671",
		"PI532463B",
		"PI534645",
		"PI534648",
		"PI538386A",
		"PI542044",
		"PI547862",
		"PI548298",
		"PI548316",
		"PI548318",
		"PI548335",
		"PI548348",
		"PI548349",
		"PI548362",
		"PI548381",
		"PI548383",
		"PI548388",
		"PI548502",
		"PI548523",
		"PI548527",
		"PI548542",
		"PI548543",
		"PI548558",
		"PI548562",
		"PI548612",
		"PI548628",
		"PI548631",
		"PI548632",
		"PI548634",
		"PI548635",
		"PI549031",
		"PI552538",
		"PI555396",
		"PI556511",
		"PI561370",
		"PI564718",
		"PI567250B",
		"PI567351B",
		"PI567357",
		"PI567361",
		"PI567366A",
		"PI567404A",
		"PI567435B",
		"PI567519",
		"PI567558",
		"PI567576",
		"PI567583A",
		"PI567599",
		"PI574486",
		"PI591539",
		"PI592952",
		"PI593258",
		"PI594409B",
		"PI594456A",
		"PI597387",
		"PI597478B",
		"PI598124",
		"PI602492",
		"PI603442",
		"PI603451A",
		"PI603488",
		"PI603502A",
		"PI603549",
		"PI603556",
		"PI603675",
		"PI603911B",
		"PI612611",
		"PI633729",
		"PI639283",
		"PI88814",
		"PI227320",
		"PI248515",
		"PI567675",
		"PI587575A",
		"FC3654-1",
		"FC31697",
		"FC33243",
		"PI54614",
		"PI58955",
		"PI62202-2",
		"PI63945",
		"PI68679-2",
		"PI70208",
		"PI70242-2",
		"PI70466-3",
		"PI79870-4",
		"PI80837",
		"PI81042-2",
		"PI83925",
		"PI84660",
		"PI84946-2",
		"PI86972-2",
		"PI87571",
		"PI88499",
		"PI89772",
		"PI90406-1",
		"PI90479P",
		"PI90495N",
		"PI91132-3",
		"PI91159-4",
		"PI91731-1",
		"PI92604",
		"PI92651",
		"PI94159-3",
		"PI157487B",
		"PI171428",
		"PI235347",
		"PI253652B",
		"PI377574",
		"PI398965",
		"PI404161",
		"PI404198B",
		"PI404199",
		"PI407729",
		"PI408105A",
		"PI417345B",
		"PI424608A",
		"PI430595",
		"PI430598B",
		"PI437127A",
		"PI437679",
		"PI437725",
		"PI437798",
		"PI438496C",
		"PI458515",
		"PI464912",
		"PI495020",
		"PI507467",
		"PI507553",
		"PI508269",
		"PI510670",
		"PI518668",
		"PI518673",
		"PI525454",
		"PI533654",
		"PI534646",
		"PI539936",
		"PI540555",
		"PI548158",
		"PI548169",
		"PI548193",
		"PI548200",
		"PI548260",
		"PI548309",
		"PI548314",
		"PI548330",
		"PI548337",
		"PI548359",
		"PI548364",
		"PI548400",
		"PI548402",
		"PI548410",
		"PI548415",
		"PI548427",
		"PI548517",
		"PI548532",
		"PI548533",
		"PI548546",
		"PI548547",
		"PI548555",
		"PI548563",
		"PI548603",
		"PI548619",
		"PI548622",
		"PI548626",
		"PI548633",
		"PI549027A",
		"PI559931",
		"PI561371",
		"PI567265",
		"PI567272A",
		"PI567352A",
		"PI567353",
		"PI567426",
		"PI567432B",
		"PI567458",
		"PI567476",
		"PI567482B",
		"PI567488A",
		"PI567489A",
		"PI567532",
		"PI567556",
		"PI567562A",
		"PI567564",
		"PI567582A",
		"PI567593B",
		"PI567604A",
		"PI567611",
		"PI567638",
		"PI567726",
		"PI567746",
		"PI567751A",
		"PI567772",
		"PI567780B",
		"PI574477",
		"PI578490",
		"PI578495",
		"PI583366",
		"PI586981",
		"PI587588A",
		"PI588015B",
		"PI588028",
		"PI592937",
		"PI592940",
		"PI593256",
		"PI593654",
		"PI594398B",
		"PI597382",
		"PI597384",
		"PI602502B",
		"PI602993",
		"PI603162",
		"PI603170",
		"PI603174A",
		"PI603175",
		"PI603397",
		"PI603490",
		"PI603496B",
		"PI603505",
		"PI603515",
		"PI603545A",
		"PI603555",
		"PI603559",
		"PI603574",
		"PI603583",
		"PI620883",
		"PI632431",
		"PI633983",
		"PI639740",
		"PI643146",
		"PI652442",
		"PI656647",
		"PI60970",
		"PI68732-1",
		"PI79691-4",
		"PI83881",
		"PI84631",
		"PI87617",
		"PI90369",
		"PI90499-1",
		"PI91100-3",
		"PI91340",
		"PI92688-2",
		"PI92718-2",
		"PI200471",
		"PI229343",
		"PI253656B",
		"PI423926",
		"PI437654",
		"PI464913",
		"PI495017C",
		"PI506862",
		"PI507180",
		"PI507293B",
		"PI507458",
		"PI507471",
		"PI543794",
		"PI548162",
		"PI548178",
		"PI548313",
		"PI548317",
		"PI567354",
		"PI567370A",
		"PI567395",
		"PI567396B",
		"PI567415A",
		"PI567416",
		"PI567541B",
		"PI567548",
		"PI567552",
		"PI567614C",
		"PI567651",
		"PI567690",
		"PI567719",
		"PI567721",
		"PI587804",
		"PI594012",
		"PI594599",
		"PI594883",
		"PI603176A",
		"PI603401",
		"PI603421B",
		"PI603457B",
		"PI603458A",
		"PI603477A",
		"PI603492",
		"PI603494",
		"PI603526",
		"PI603543B",
		"PI603554B",
		"PI603557",
		"PI603687A",
		"PI605839A"
	];
	*/

	//$arrQACultivarNameList = [];


	// go through each known cultivar in this study and see if we have a match in our predefined list
	//foreach($objStudy->cultivars as $intCultivarKey => $strCultivarName){
		//if(in_array($strCultivarName, $objSimilarityJob->predefined_cultivars)){
			//array_push($arrQACultivarNameList, $strCultivarName);
			//array_push($objSimilarityJob->cultivars, $intCultivarKey);
		//}
	//}

	// go through our predefined cultivars and see if they each have an item in our new list
	//foreach($objSimilarityJob->predefined_cultivars as $strPredefinedCultivarName){
		//if(in_array($strPredefinedCultivarName, $arrQACultivarNameList)){
			//
		//}else{
			//echo "NOT FOUND: '".$strPredefinedCultivarName. "'<br>";
		//}
	//}

	// one time fix stop

	//good code
	foreach($objStudy->cultivars as $intCultivarKey => $strCultivarName){
		array_push($objSimilarityJob->cultivars, $intCultivarKey);
	}





	//echo "<pre>";
	//print_r($objSimilarityJob);
	//echo "</pre>";
	//exit;






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
