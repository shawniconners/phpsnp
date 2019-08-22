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
	$objAssembly->sql = "SELECT count(id) AS snp_count FROM tblStudy".$objAssembly->study->id."Structure".$objAssembly->structure->id."SNPs WHERE position >= :start_position AND position <= :stop_position ORDER BY position ASC;";
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
	//	^--- PHP -- 1G - START of determing the snp window for this analysis
	//****************************************************************************************************************
	if($objAssembly->study->snp_count > $objSettings->haplotype_max){
		$objAssembly->study->snp_window = $objSettings->haplotype_max;
	}else{
		$objAssembly->study->snp_window = $objAssembly->study->snp_count;
	}
	//****************************************************************************************************************
	//	v--- PHP -- 1G - END of determing the snp window for this analysis
	//****************************************************************************************************************
	//****************************************************************************************************************
	//	^--- PHP -- 1H - START of finding the snp windows snp names and positions
	//****************************************************************************************************************
	$objAssembly->sql = "SELECT position, names, reference, alternate FROM tblStudy".$objAssembly->study->id."Structure".$objAssembly->structure->id."SNPs WHERE position >= :start_position AND position <= :stop_position ORDER BY position ASC LIMIT ". $objAssembly->study->snp_window.";";
	$objAssembly->prepare = $objSettings->database->connection->prepare($objAssembly->sql);
	$objAssembly->prepare->bindValue(':start_position', $objAssembly->structure->start_position, PDO::PARAM_INT);
	$objAssembly->prepare->bindValue(':stop_position', $objAssembly->structure->stop_position, PDO::PARAM_INT);
	//$objAssembly->prepare->bindValue(':snp_window', $objAssembly->study->snp_window, PDO::PARAM_INT);
	$objAssembly->prepare->execute();
	$objAssembly->study->snps = $objAssembly->prepare->fetchAll(PDO::FETCH_ASSOC);
	//****************************************************************************************************************
	//	v--- PHP -- 1H - END of finding the snp windows snp names and positions
	//****************************************************************************************************************
	//****************************************************************************************************************
	//	^--- PHP -- 1I - START of converting the JSON database fields
	//****************************************************************************************************************
	for($intLoopCounter = 0; $intLoopCounter < count($objAssembly->study->snps); $intLoopCounter++){
		$objAssembly->study->snps[$intLoopCounter]["names"] = implode(", ", json_decode($objAssembly->study->snps[$intLoopCounter]["names"]));
		$objAssembly->study->snps[$intLoopCounter]["alternate"] = implode(", ", json_decode($objAssembly->study->snps[$intLoopCounter]["alternate"]));
	}
	//****************************************************************************************************************
	//	v--- PHP -- 1I - END of converting the JSON database fields
	//****************************************************************************************************************
	//****************************************************************************************************************
	//	^--- PHP -- 1I - START of converting the cultivars to core requests
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
		$objRequest = new stdClass();
		$objRequest->status = "ready"; // options: ready, active, complete
		$objRequest->url = "haplotype_groups_script.php?study_id=".$objAssembly->study->id."&structure_id=".$objAssembly->structure->id."&start_position=".$objAssembly->structure->start_position."&stop_position=".$objAssembly->structure->stop_position."&cultivar_key=".$intLoopCounter."&snp_window=".$objAssembly->study->snp_window;
		array_push($objCoreRequests->requests, $objRequest);
	}

	//****************************************************************************************************************
	//	v--- PHP -- 1I - END of converting the cultivars to core requests
	//****************************************************************************************************************
	//print_r($objCoreRequests->requests);
	//exit;
?>
<!doctype html>
<html lang="en">
	<head>
    	<meta charset="utf-8">
    	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    	<meta name="description" content="">
    	<meta name="author" content="">
    	<title>phpSNP - Haplotype Groups</title>
    	<!-- Bootstrap core CSS -->
    	<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css" integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">
		<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/v/bs4/dt-1.10.16/b-1.4.2/b-html5-1.4.2/fc-3.2.3/fh-3.1.3/sc-1.4.3/sl-1.2.3/datatables.min.css"/>
		<link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/clusterize.js/0.18.0/clusterize.min.css"/>
		<script src="scripts.js"></script>
		<script>
			boolConsoleLogging = <?php echo $objSettings->console_logging; ?>;
			objCoreRequests = <?php echo json_encode($objCoreRequests); ?>;
			arrData = [];
			arrHaplotypeGroups = [];
			arrCultivars = <?php echo json_encode($objAssembly->study->cultivars); ?>;
			arrSNPs = <?php echo json_encode($objAssembly->study->snps); ?>;

			objCoreRequests.interval = <?php echo $objSettings->loop_interval; ?>;
			objCoreRequests.requests_completed = 0;
			function funHaplotypeGroupsStart(){
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
				elmHaplotypeGroupsProgress = document.getElementById("elmHaplotypeGroupsProgress");
				elmHaplotypeGroupsProgressPercent = document.getElementById("elmHaplotypeGroupsProgressPercent");
				if(objCoreRequests.requests_completed < objCoreRequests.requests.length){
					funConsoleLog("Waiting for requests to complete. Relooping requested.");
					elmHaplotypeGroupsProgress.value = objCoreRequests.requests_completed / objCoreRequests.requests.length;
					elmHaplotypeGroupsProgressPercent.innerHTML = parseInt(elmHaplotypeGroupsProgress.value * 100) + "% Complete"
					setTimeout(function(){funLoop();}, objCoreRequests.interval);
				}else{
					funConsoleLog("All requests completed.");
					elmHaplotypeGroupsProgressPercent.innerHTML = "100% Complete. Displaying data...";
					document.getElementById("elmHaplotypeGroupsProgressContainer").style.visibility = "hidden";
					document.getElementById("elmHaplotypeGroupsProgressContainer").style.display = "none";
					document.getElementById("elmHaplotypeGroupsResultsContainer").style.visibility = "visible";
					document.getElementById("elmHaplotypeGroupsResultsContainer").style.display = "block";
					funFinalizeResults();
				}
				funConsoleLog("Loop has finished.");
			}
		</script>
		<script src="https://d3js.org/d3.v4.js"></script>
		<script src="https://unpkg.com/ag-grid-community/dist/ag-grid-community.min.noStyle.js"></script>
		<link rel="stylesheet" href="https://unpkg.com/ag-grid-community/dist/styles/ag-grid.css">
  		<link rel="stylesheet" href="https://unpkg.com/ag-grid-community/dist/styles/ag-theme-balham.css">
		<style>
			#elmHaplotypeGroupsResultsContainer{
				visibility: hidden;
				display: none;
			}
		</style>
	</head>
	<body onload="funHaplotypeGroupsStart()">
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
					<h2>Haplotype Groups</h2>
					<p class="lead">
						To determine haplotypes, SNP allele results are analyzed for each cultivar. Cultivars with matching SNP values are compiled together as a haplotype group.
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
							<?php
								if($objAssembly->study->snp_count > $objAssembly->study->snp_window){
									?>
									<div class="alert alert-warning mb-0 mt-3" role="alert">
										The region you selected for this study contains over <?php echo $objSettings->haplotype_max; ?> SNPs. To create the analysis that appears below we have limited the result set to the first 100 SNPs. The results below should not be considered accurate for the currently selected region. You may want to go back to the browse page and select a smaller region.
									</div>
									<?php
								}
							?>
						</div>
					</div>
				</div>
				<div class="row">
					<div class="card w-100 mt-3">
						<div class="card-header text-white bg-secondary font-weight-bold">
							SNPs
						</div>
						<div class="card-body">
							<div id="elmHaplotypeGroupsSNPsTable" style="height: 200px;width: 100%;" class="ag-theme-balham mt-1"></div>
						</div>
					</div>
				</div>
				<div class="row">
					<div class="card w-100 mt-3">
						<div class="card-header text-white bg-secondary font-weight-bold">
							Analysis Results
						</div>
						<div class="card-body">
							<div id="elmHaplotypeGroupsProgressContainer">
								<div class="row justify-content-center">
									Analyzing SNPs
								</div>
								<div class="row justify-content-center">
									<progress id="elmHaplotypeGroupsProgress" value="0" max="1"></progress>
								</div>
								<div class="row justify-content-center">
									<h6 class="text-muted" id="elmHaplotypeGroupsProgressPercent"></h6>
								</div>
							</div>
							<div id="elmHaplotypeGroupsResultsContainer">
								<div id="elmHaplotypeGroupsResultsText">
									To view details about a haplotype group, select the group from the table below. Cultivars that share this haplotype will then be displayed in the table to the right. The SNP allele values will also appear next to the list of cultivars within the selected haplotype group.
								</div>
								<div class="row">
									<div id="elmHaplotypeGroupsOverviewTable" style="height: 1px;width: 1px;" class="ag-theme-balham mt-3 col-6"></div>
									<div id="elmHaplotypeGroupsCultivarsTable" style="height: 1px;width: 1px;" class="ag-theme-balham mt-3 col-3"></div>
									<div id="elmHaplotypeGroupsAllelesTable" class="ag-theme-balham mt-4 col-3 pt-1"></div>
								</div>
								<div class="row">
									<form action="groups_download_script.php" method="post" class="col-12">
										<input type="hidden" name="elmHaplotypeGroupsStudy" id="elmHaplotypeGroupsStudy" value="<?php echo $objAssembly->study->name;?>" />
										<input type="hidden" name="elmHaplotypeGroupsCultivars" id="elmHaplotypeGroupsCultivars" value="" />
										<input type="hidden" name="elmHaplotypeGroupsResults" id="elmHaplotypeGroupsResults" value="" />
										<button class="btn btn-success float-right mr-1 mt-3 mb-0" role="submit">Download Analysis Results</button>
									</form>
								</div>
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

			// specify the columns
			var objHaplotypeGroupsSNPsTableColumnDefs = [
			  {headerName: "Position", field: "position", sortable: true, filter: true},
			  {headerName: "Name(s)", field: "names", sortable: true, filter: true},
			  {headerName: "Reference", field: "reference", sortable: true, filter: true},
			  {headerName: "Alternate(s)", field: "alternate", sortable: true, filter: true}
			];

			// let the grid know which columns and what data to use
			var objHaplotypeGroupsSNPsTableGridOptions = {
			  columnDefs: objHaplotypeGroupsSNPsTableColumnDefs,
			  rowData: arrSNPs
			};

			// lookup the container we want the Grid to use
			var elmHaplotypeGroupsSNPsTable = document.querySelector('#elmHaplotypeGroupsSNPsTable');

			// create the grid passing in the div to use together with the columns & data we want to use
			new agGrid.Grid(elmHaplotypeGroupsSNPsTable, objHaplotypeGroupsSNPsTableGridOptions);

			function funFinalizeResults(){

				for(intOutterLoopCounter = 0; intOutterLoopCounter < arrData.length; intOutterLoopCounter++){
					intHaplotypeFoundKey = -1;
					for(intInnerLoopCounter = 0; intInnerLoopCounter < arrHaplotypeGroups.length; intInnerLoopCounter++){
						if(arrHaplotypeGroups[intInnerLoopCounter].haplotype == arrData[intOutterLoopCounter].haplotype){
							intHaplotypeFoundKey = intInnerLoopCounter;
							break;
						}
					}
					if(intHaplotypeFoundKey < 0){
						arrHaplotypeGroups.push({"haplotype": arrData[intOutterLoopCounter].haplotype, "cultivar_keys" :[arrData[intOutterLoopCounter].cultivar_key]});
					}else{
						arrHaplotypeGroups[intHaplotypeFoundKey].cultivar_keys.push(arrData[intOutterLoopCounter].cultivar_key);
					}
				}
				arrHaplotypeGroups.sort((a, b) => (a.cultivar_keys.length < b.cultivar_keys.length) ? 1 : -1);

				strCSSWidthprop = "100%";
				strCSSHeightprop = "200px";

				document.getElementById("elmHaplotypeGroupsOverviewTable").style.width = strCSSWidthprop;
				document.getElementById("elmHaplotypeGroupsOverviewTable").style.height = strCSSHeightprop;

				for (intOutterLoopCounter = 0; intOutterLoopCounter < arrHaplotypeGroups.length; intOutterLoopCounter++) {
					arrHaplotypeGroups[intOutterLoopCounter].group_number = intOutterLoopCounter + 1;
					arrHaplotypeGroups[intOutterLoopCounter].cultivar_count = arrHaplotypeGroups[intOutterLoopCounter].cultivar_keys.length;
					arrHaplotypeGroups[intOutterLoopCounter].cultivars = [];
					for (intInnerLoopCounter = 0; intInnerLoopCounter < arrHaplotypeGroups[intOutterLoopCounter].cultivar_keys.length; intInnerLoopCounter++) {
						var objCultivar = {};
						objCultivar.name = arrCultivars[arrHaplotypeGroups[intOutterLoopCounter].cultivar_keys[intInnerLoopCounter]];
						arrHaplotypeGroups[intOutterLoopCounter].cultivars.push(objCultivar);
					}
				}

				delete arrData;

				elmHaplotypeGroupsResults = document.getElementById("elmHaplotypeGroupsResults");
				elmHaplotypeGroupsResults.value = JSON.stringify(arrHaplotypeGroups);

				elmHaplotypeGroupsCultivars = document.getElementById("elmHaplotypeGroupsCultivars");
				elmHaplotypeGroupsCultivars.value = JSON.stringify(arrCultivars);

			    // specify the columns
			    var objHaplotypeGroupsOverviewTableColumnDefs = [
			      {headerName: "Haplotype Group", field: "group_number", sortable: true, filter: true},
				  {headerName: "Cultivars", field: "cultivar_count", sortable: true, filter: true}
			    ];

			    // let the grid know which columns and what data to use
			    var objHaplotypeGroupsOverviewTableGridOptions = {
			      columnDefs: objHaplotypeGroupsOverviewTableColumnDefs,
				  rowSelection: 'single',
				  onSelectionChanged: function(){
					  var selectedRows = objHaplotypeGroupsOverviewTableGridOptions.api.getSelectedRows();
					  funShowGroupCultivarsResults(selectedRows[0].group_number-1);
				  },
			      rowData: arrHaplotypeGroups
			    };

			  	// lookup the container we want the Grid to use
			  	var elmHaplotypeGroupsOverviewTable = document.querySelector('#elmHaplotypeGroupsOverviewTable');

			  	// create the grid passing in the div to use together with the columns & data we want to use
				new agGrid.Grid(elmHaplotypeGroupsOverviewTable, objHaplotypeGroupsOverviewTableGridOptions);

				document.getElementById("elmHaplotypeGroupsCultivarsTable").style.width = strCSSWidthprop;
				document.getElementById("elmHaplotypeGroupsCultivarsTable").style.height = strCSSHeightprop;

				objHaplotypeGroupsOverviewTableGridOptions.api.forEachNode( function (node) {
			        if (node.data.group_number == 1) {
			            node.setSelected(true);
			        }
			    });

			}

			function funShowGroupCultivarsResults(inc_key){

				document.getElementById("elmHaplotypeGroupsCultivarsTable").innerHTML = "";
				// specify the columns
			    var objHaplotypeGroupsCultivarsTableColumnDefs = [
			      {headerName: "Group "+(inc_key+1)+" Cultivars", field: "name", sortable: true, filter: true}
			    ];

			    // let the grid know which columns and what data to use
			    var objHaplotypeGroupsCultivarsTableGridOptions = {
			      columnDefs: objHaplotypeGroupsCultivarsTableColumnDefs,
			      rowData: arrHaplotypeGroups[inc_key].cultivars
			    };

			  	// lookup the container we want the Grid to use
			  	var elmHaplotypeGroupsCultivarsTable = document.querySelector('#elmHaplotypeGroupsCultivarsTable');

			  	// create the grid passing in the div to use together with the columns & data we want to use
				new agGrid.Grid(elmHaplotypeGroupsCultivarsTable, objHaplotypeGroupsCultivarsTableGridOptions);

				var elmHaplotypeGroupsAllelesTable = document.querySelector('#elmHaplotypeGroupsAllelesTable');
				elmHaplotypeGroupsAllelesTable.innerHTML = "<p>Group "+(inc_key+1)+" SNP Alleles</p><hr /><p>"+arrHaplotypeGroups[inc_key].haplotype+"</p>";

			}




		</script>
	</body>
</html>
