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
	$objRequest->cultivar_key = filter_input(INPUT_GET, "cultivar_key", FILTER_VALIDATE_INT);
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
	$objAssembly->study->cultivar_key = $objRequest->cultivar_key;
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
	//	^--- PHP -- 1G - START of determing the cultivar key 1
	//****************************************************************************************************************
	if(empty($objAssembly->study->cultivar_key)){
		$objAssembly->study->cultivar_key = 0;
	}
	//****************************************************************************************************************
	//	v--- PHP -- 1G - END of retrieving the cultivar key 1
	//****************************************************************************************************************
	//****************************************************************************************************************
	//	^--- PHP -- 1H - START of converting the cultivars to core requests
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
	for($intLoopCounter = 0; $intLoopCounter < count($objAssembly->study->cultivars); $intLoopCounter++){
	//for($intLoopCounter = 10000; $intLoopCounter < 10010; $intLoopCounter++){
	//for($intLoopCounter = (count($objAssembly->study->cultivars)-1); $intLoopCounter > 19000 ; $intLoopCounter--){
	//for($intLoopCounter = 10500; $intLoopCounter > 10000 ; $intLoopCounter--){
		if($intLoopCounter != $objAssembly->study->cultivar_key){
			$objRequest = new stdClass();
			$objRequest->status = "ready"; // options: ready, active, complete
			$objRequest->url = "cultivar_similarity_script.php?study_id=".$objAssembly->study->id."&structure_id=".$objAssembly->structure->id."&start_position=".$objAssembly->structure->start_position."&stop_position=".$objAssembly->structure->stop_position."&cultivar_key_1=".$objAssembly->study->cultivar_key."&cultivar_key_2=".$intLoopCounter;
			array_push($objCoreRequests->requests, $objRequest);
		}
	}

	//****************************************************************************************************************
	//	v--- PHP -- 1H - END of converting the cultivars to core requests
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
			var arrData = [];
			var arrCultivars = <?php echo json_encode($objAssembly->study->cultivars); ?>;

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
							arrData.push(JSON.parse(objCoreRequests.cores[intCoreCounter].ajax.responseText));
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
					elmCultivarSimilarityProgressPercent.innerHTML = "100% Complete. Displaying data...";
					document.getElementById("elmCultivarSimilarityProgressContainer").style.visibility = "hidden";
					document.getElementById("elmCultivarSimilarityProgressContainer").style.display = "none";
					document.getElementById("elmCultivarSimilarityResultsContainer").style.visibility = "visible";
					document.getElementById("elmCultivarSimilarityResultsContainer").style.display = "block";
					//window.location.href = "curate.php";
					funCreateGraph();
					funCreateTable();
					funCreateDownload();
				}
				funConsoleLog("Loop has finished.");
			}
		</script>
		<script src="https://d3js.org/d3.v4.js"></script>
		<script src="https://unpkg.com/ag-grid-community/dist/ag-grid-community.min.noStyle.js"></script>
		<link rel="stylesheet" href="https://unpkg.com/ag-grid-community/dist/styles/ag-grid.css">
  		<link rel="stylesheet" href="https://unpkg.com/ag-grid-community/dist/styles/ag-theme-balham.css">
		<style>
			#elmCultivarSimilarityResultsContainer{
				visibility: hidden;
				display: none;
			}
		</style>
	</head>
	<body onload="funCultivarSimilarityStart()">
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
					<h2>Cultivar Similarity</h2>
					<p class="lead">
						The following is an analysis of matching SNPs (x-axis) and number of cultivars (y-axis) for the currently selected region of this study, as compared to a base cultivar.
					</p>
				</div>
				<div class="row">
					<div class="card w-100 mt-3">
						<div class="card-header text-white bg-secondary font-weight-bold">
							Overview
						</div>
						<div class="card-body">
							<div class="container">
								<div class="row">
									<div class="col-sm-6">
										<div class="row">
											<div class="col-sm-4">
									      		<strong>Assembly</strong>
									    	</div>
									    	<div class="col-sm-8">
									      		<?php echo $objAssembly->name;?>
									    	</div>
										</div>
										<div class="row">
											<div class="col-sm-4">
									      		<strong>Structure</strong>
									    	</div>
									    	<div class="col-sm-8">
									      		<?php echo $objAssembly->structure->name;?>
									    	</div>
										</div>
										<div class="row">
											<div class="col-sm-4">
									      		<strong>Region</strong>
									    	</div>
									    	<div class="col-sm-8">
									      		<?php echo number_format($objAssembly->structure->start_position);?> - <?php echo number_format($objAssembly->structure->stop_position);?>
									    	</div>
										</div>
									</div>
									<div class="col-sm-6">
										<div class="row">
											<div class="col-sm-4">
									      		<strong>Study</strong>
									    	</div>
									    	<div class="col-sm-8">
									      		<?php echo $objAssembly->study->name;?>
									    	</div>
										</div>
										<div class="row">
											<div class="col-sm-4">
									      		<strong>SNPs</strong>
									    	</div>
									    	<div class="col-sm-8">
									      		<?php echo number_format($objAssembly->study->snp_count);?>
									    	</div>
										</div>
										<div class="row">
											<div class="col-sm-4">
									      		<strong>Cultivars</strong>
									    	</div>
									    	<div class="col-sm-8">
									      		<?php echo number_format($objAssembly->study->cultivar_count);?>
									    	</div>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="row">
					<div class="card w-100 mt-3">
						<div class="card-header text-white bg-secondary font-weight-bold">
							Analysis Results
						</div>
						<div class="card-body">
							<p>
								<?php echo $objAssembly->study->name;?> base cultivar used for this analysis: <strong><?php echo $objAssembly->study->cultivars[$objAssembly->study->cultivar_key]; ?></strong>.
							</p>
							<p>
								All other cultivars within this study were compared against this base cultivar to determine similarity. To use a different cultivar as a base for this comparison, please make your selection from the table that appears below.
							</p>
							<div id="elmCultivarSimilarityProgressContainer">
								<div class="row justify-content-center">
									Analyzing SNPs
								</div>
								<div class="row justify-content-center">
									<progress id="elmCultivarSimilarityProgress" value="0" max="1"></progress>
								</div>
								<div class="row justify-content-center">
									<h6 class="text-muted" id="elmCultivarSimilarityProgressPercent"></h6>
								</div>
							</div>
							<div id="elmCultivarSimilarityResultsContainer">
								<div id="elmCultivarSimilarityGraph"></div>
								<div id="myGrid" style="height: 1px;width: 1px;" class="ag-theme-balham mt-4 ml-4 mb-3"></div>
								<form action="similarity_download_script.php" method="post">
									<input type="hidden" name="elmCultivarSimilarityStudy" id="elmCultivarSimilarityStudy" value="<?php echo $objAssembly->study->name;?>" />
									<input type="hidden" name="elmCultivarSimilaritySNPs" id="elmCultivarSimilaritySNPs" value="<?php echo $objAssembly->study->snp_count;?>" />
									<input type="hidden" name="elmCultivarSimilarityBaseCultivar" id="elmCultivarSimilarityBaseCultivar" value="<?php echo $objAssembly->study->cultivars[$objAssembly->study->cultivar_key]; ?>" />
									<input type="hidden" name="elmCultivarSimilarityResults" id="elmCultivarSimilarityResults" value="" />
									<button class="btn btn-success float-right mr-3" role="submit">Download Analysis Results for <?php echo $objAssembly->study->cultivars[$objAssembly->study->cultivar_key]; ?></button>
								</form>
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
		<script>


			function funCreateGraph(){


				//document.write(JSON.stringify(arrData));
				var elmCultivarSimilarityGraph = document.getElementById("elmCultivarSimilarityGraph");
				var strCSSWidthprop = window.getComputedStyle(elmCultivarSimilarityGraph, null).getPropertyValue("width");
				intElemWidth = parseInt(strCSSWidthprop.substring(0, strCSSWidthprop.length - 2));

				// set the dimensions and margins of the graph
				var margin = {top: 10, right: 20, bottom: 20, left: 40},
					width = intElemWidth - margin.left - margin.right,
					height = 200 - margin.top - margin.bottom;

				// append the svg object to the body of the page
				var svg = d3.select("#elmCultivarSimilarityGraph")
				  .append("svg")
					.attr("width", width + margin.left + margin.right)
					.attr("height", height + margin.top + margin.bottom)
				  .append("g")
					.attr("transform",
						  "translate(" + margin.left + "," + margin.top + ")");

				  // X axis: scale and draw:
				  var intRangeBottom = 0;
				  if(Math.floor(Math.min.apply(Math,arrData.map(function(o){return o.similarity;}))) > 1){
					  intRangeBottom = Math.floor(Math.min.apply(Math,arrData.map(function(o){return o.similarity;}))) - 1;
				  }
				  var x = d3.scaleLinear()
				  	  //.domain([0, 105])
					  //.domain([Math.floor(Math.min.apply(Math,arrData.map(function(o){return o.similarity;}))), Math.ceil(Math.max.apply(Math,arrData.map(function(o){return o.similarity;}))) * 1.05])     // can use this instead of 1000 to have the max of data: d3.max(data, function(d) { return +d.price })
					  //.domain([Math.floor(Math.min.apply(Math,arrData.map(function(o){return o.similarity;}))) - 1, Math.ceil(Math.max.apply(Math,arrData.map(function(o){return o.similarity;}))) + 1])     // can use this instead of 1000 to have the max of data: d3.max(data, function(d) { return +d.price })
					  .domain([intRangeBottom, Math.ceil(Math.max.apply(Math,arrData.map(function(o){return o.similarity;}))) * 1.05])     // can use this instead of 1000 to have the max of data: d3.max(data, function(d) { return +d.price })
					  .range([0, width]);
				  svg.append("g")
					  .attr("transform", "translate(0," + height + ")")
					  .call(d3.axisBottom(x));

				  // set the parameters for the histogram
				  var histogram = d3.histogram()
					  .value(function(d) { return d.similarity; })   // I need to give the vector of value
					  .domain(x.domain())  // then the domain of the graphic
					  .thresholds(x.ticks(50)); // then the numbers of bins

				  // And apply this function to data to get the bins
				  var bins = histogram(arrData);

				  // Y axis: scale and draw:
				  var y = d3.scaleLinear()
					  .range([height, 0]);
					  y.domain([0, d3.max(bins, function(d) { return d.length; })]);   // d3.hist has to be called before the Y axis obviously
				  svg.append("g")
					  .call(d3.axisLeft(y));

				  // append the bar rectangles to the svg element
				  svg.selectAll("rect")
					  .data(bins)
					  .enter()
					  .append("rect")
						.attr("x", 1)
						.attr("transform", function(d) { return "translate(" + x(d.x0) + "," + y(d.length) + ")"; })
						.attr("width", function(d) { return x(d.x1) - x(d.x0) - 1 ; })
						.attr("height", function(d) { return height - y(d.length); })
						.style("fill", "#69b3a2");


						//document.getElementById("elmBrowseSNPCountStudy").innerHTML = data.length.toLocaleString();

			}

			function funCreateTable(){

				var elmCultivarSimilarityGraph = document.getElementById("elmCultivarSimilarityGraph");

				var strCSSWidthprop = window.getComputedStyle(elmCultivarSimilarityGraph, null).getPropertyValue("width");

				intElemWidth = Math.floor(parseInt(strCSSWidthprop.substring(0, strCSSWidthprop.length - 2)) - 40);
				strCSSWidthprop = intElemWidth.toString() + "px";
				//strCSSHeightprop = intElemHeight.toString() + "px";
				strCSSHeightprop = "300px";

				document.getElementById("myGrid").style.width = strCSSWidthprop;
				document.getElementById("myGrid").style.height = strCSSHeightprop;

				for (intLoopCounter = 0; intLoopCounter < arrData.length; intLoopCounter++) {
					//text += cars[i] + "<br>";
					arrData[intLoopCounter].name = arrCultivars[arrData[intLoopCounter].cultivar_key];
				}

				//alert(JSON.stringify(arrData));
			    // specify the columns
			    var columnDefs = [
			      {headerName: "Cultivar", field: "name", sortable: true, filter: true, cellRenderer: function(params) {
				      return '<a href="cultivar_similarity.php?assembly_id=<?php echo $objAssembly->id;?>&structure_id=<?php echo $objAssembly->structure->id;?>&start_position=<?php echo $objAssembly->structure->start_position;?>&stop_position=<?php echo $objAssembly->structure->stop_position;?>&study_id=<?php echo $objAssembly->study->id;?>&cultivar_key='+params.data.cultivar_key+'">'+ params.value+'</a>'
				  }},
				  {headerName: "Matching SNPs", field: "similarity", sortable: true, filter: true}
			    ];


			    // let the grid know which columns and what data to use
			    var gridOptions = {
			      columnDefs: columnDefs,
				  rowSelection: 'single',
			      rowData: arrData
			    };

			  	// lookup the container we want the Grid to use
			  	var eGridDiv = document.querySelector('#myGrid');

			  	// create the grid passing in the div to use together with the columns & data we want to use
				new agGrid.Grid(eGridDiv, gridOptions);

			}

			function funCreateDownload(){
				document.getElementById("elmCultivarSimilarityResults").value = JSON.stringify(arrData);
				//alert(document.getElementById("elmCultivarSimilarityResults").value);
			}

		</script>
	</body>
</html>
