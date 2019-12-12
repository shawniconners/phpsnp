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
	//****************************************************************************************************************
	//	v--- PHP -- 1B - END of receiving the id for the assembly, structure and study, along with start and stop
	//****************************************************************************************************************
	//****************************************************************************************************************
	//	^--- PHP -- 1C - START of preparing selections
	//****************************************************************************************************************
	$objCollection = new stdClass();
	$objCollection->selected = new stdClass();
	$objCollection->selected->assembly = new stdClass();
	$objCollection->selected->assembly->ready = false;
	$objCollection->selected->assembly->index = 0;
	$objCollection->selected->assembly->id = $objRequest->assembly_id;
	$objCollection->selected->structure = new stdClass();
	$objCollection->selected->structure->ready = false;
	$objCollection->selected->structure->index = 0;
	$objCollection->selected->structure->id = $objRequest->structure_id;
	$objCollection->selected->start = new stdClass();
	$objCollection->selected->start->ready = false;
	$objCollection->selected->start->position = $objRequest->start_position;
	$objCollection->selected->stop = new stdClass();
	$objCollection->selected->stop->ready = false;
	$objCollection->selected->stop->position = $objRequest->stop_position;
	//****************************************************************************************************************
	//	v--- PHP -- 1C - END of preparing selections
	//****************************************************************************************************************
	//****************************************************************************************************************
	//	^--- PHP -- 1D - START of retrieving the assemblies
	//****************************************************************************************************************
	$objCollection->sql = "SELECT * FROM tblAssemblies ORDER BY name DESC;";
	$objCollection->prepare = $objSettings->database->connection->prepare($objCollection->sql);
	$objCollection->prepare->execute();
	$objCollection->assemblies = $objCollection->prepare->fetchAll(PDO::FETCH_ASSOC);
	//****************************************************************************************************************
	//	v--- PHP -- 1D - END of retrieving the assemblies
	//****************************************************************************************************************
	//****************************************************************************************************************
	//	^--- PHP -- 1E - START of determining the selected (or default) assembly
	//****************************************************************************************************************
	for ($intLoopCounter = 0; $intLoopCounter < count($objCollection->assemblies); $intLoopCounter++) {
		$objCollection->assemblies[$intLoopCounter]["option_selected"] = "";
		if($objCollection->assemblies[$intLoopCounter]["id"] == $objCollection->selected->assembly->id){
			$objCollection->assemblies[$intLoopCounter]["option_selected"] = " selected";
			$objCollection->selected->assembly->ready = true;
			$objCollection->selected->assembly->index = $intLoopCounter;
			$objCollection->selected->assembly->id = $objCollection->assemblies[$intLoopCounter]["id"];
		}
	}
	if(!$objCollection->selected->assembly->ready){
		$objCollection->assemblies[0]["option_selected"] = " selected";
		$objCollection->selected->assembly->ready = true;
		$objCollection->selected->assembly->index = 0;
		$objCollection->selected->assembly->id = $objCollection->assemblies[0]["id"];
	}
	//****************************************************************************************************************
	//	v--- PHP -- 1E - END of determining the selected (or default) assembly
	//****************************************************************************************************************
	//****************************************************************************************************************
	//	^--- PHP -- 1F - START of retrieving the structures for the selected assembly
	//****************************************************************************************************************
	$objCollection->sql = "SELECT id, name, sequence_length FROM tblStructures WHERE assembly_id = :assembly_id ORDER BY id ASC;";
	$objCollection->prepare = $objSettings->database->connection->prepare($objCollection->sql);
	$objCollection->prepare->bindValue(':assembly_id', $objCollection->selected->assembly->id, PDO::PARAM_INT);
	$objCollection->prepare->execute();
	$objCollection->assemblies[$objCollection->selected->assembly->index]["structures"] = $objCollection->prepare->fetchAll(PDO::FETCH_ASSOC);
	//****************************************************************************************************************
	//	v--- PHP -- 1F - END of retrieving the structures for the selected assembly
	//****************************************************************************************************************
	//****************************************************************************************************************
	//	^--- PHP -- 1G - START of retrieving the studies for the selected assembly
	//****************************************************************************************************************
	$objCollection->sql = "SELECT id, name, source, snp_count, cultivar_count, structures FROM tblStudies WHERE assembly_id = :assembly_id ORDER BY name ASC;";
	$objCollection->prepare = $objSettings->database->connection->prepare($objCollection->sql);
	$objCollection->prepare->bindValue(':assembly_id', $objCollection->selected->assembly->id, PDO::PARAM_INT);
	$objCollection->prepare->execute();
	$objCollection->assemblies[$objCollection->selected->assembly->index]["studies"] = $objCollection->prepare->fetchAll(PDO::FETCH_ASSOC);
	//****************************************************************************************************************
	//	v--- PHP -- 1G - END of retrieving the studies for the selected assembly
	//****************************************************************************************************************
	//****************************************************************************************************************
	//	^--- PHP -- 1H - START of determining the selected (or default) structure
	//****************************************************************************************************************
	for ($intLoopCounter = 0; $intLoopCounter < count($objCollection->assemblies[$objCollection->selected->assembly->index]["structures"]); $intLoopCounter++) {
		$objCollection->assemblies[$objCollection->selected->assembly->index]["structures"][$intLoopCounter]["option_selected"] = "";
		if($objCollection->assemblies[$objCollection->selected->assembly->index]["structures"][$intLoopCounter]["id"] == $objCollection->selected->structure->id){
			$objCollection->assemblies[$objCollection->selected->assembly->index]["structures"][$intLoopCounter]["option_selected"] = " selected";
			$objCollection->selected->structure->ready = true;
			$objCollection->selected->structure->index = $intLoopCounter;
			$objCollection->selected->structure->id = $objCollection->assemblies[$objCollection->selected->assembly->index]["structures"][$intLoopCounter]["id"];
		}
	}
	if(!$objCollection->selected->structure->ready){
		$objCollection->assemblies[$objCollection->selected->assembly->index]["structures"][0]["option_selected"] = " selected";
		$objCollection->selected->structure->index = 0;
		$objCollection->selected->structure->id = $objCollection->assemblies[$objCollection->selected->assembly->index]["structures"][0]["id"];
	}
	//****************************************************************************************************************
	//	v--- PHP -- 1H - END of determining the selected (or default) structure
	//****************************************************************************************************************
	//****************************************************************************************************************
	//	^--- PHP -- 1I - START of determining the selected (or default) start position
	//****************************************************************************************************************
	if(($objCollection->selected->start->position < 1) || ($objCollection->selected->start->position > $objCollection->assemblies[$objCollection->selected->assembly->index]["structures"][$objCollection->selected->structure->index]["sequence_length"])){
		$objCollection->selected->start->position = 1;
	}
	//****************************************************************************************************************
	//	v--- PHP -- 1I - END of determining the selected (or default) start position
	//****************************************************************************************************************
	//****************************************************************************************************************
	//	^--- PHP -- 1J - START of determining the selected (or default) stop position
	//****************************************************************************************************************
	if(($objCollection->selected->stop->position < $objCollection->selected->start->position) || ($objCollection->selected->stop->position > $objCollection->assemblies[$objCollection->selected->assembly->index]["structures"][$objCollection->selected->structure->index]["sequence_length"])){
		$objCollection->selected->stop->position = $objCollection->assemblies[$objCollection->selected->assembly->index]["structures"][$objCollection->selected->structure->index]["sequence_length"];
	}
	//****************************************************************************************************************
	//	v--- PHP -- 1J - END of determining the selected (or default) stop position
	//****************************************************************************************************************
?>
<!doctype html>
<html lang="en">
	<head>
    	<meta charset="utf-8">
    	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    	<meta name="description" content="">
    	<meta name="author" content="">
    	<title>phpSNP - Browse</title>
    	<!-- Bootstrap core CSS -->
    	<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css" integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">
		<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/v/bs4/dt-1.10.16/b-1.4.2/b-html5-1.4.2/fc-3.2.3/fh-3.1.3/sc-1.4.3/sl-1.2.3/datatables.min.css"/>
		<script>
			function funBrowseChangeRegion(){
				elmBrowseAssemblyValue = document.getElementById("elmBrowseAssemblyValue");
				elmBrowseStructureValue = document.getElementById("elmBrowseStructureValue");
				elmBrowseStartValue = document.getElementById("elmBrowseStartValue");
				elmBrowseStopValue = document.getElementById("elmBrowseStopValue");
				intAssemblyId = elmBrowseAssemblyValue.options[elmBrowseAssemblyValue.selectedIndex].value;
				intStructureId = elmBrowseStructureValue.options[elmBrowseStructureValue.selectedIndex].value;
				intStartPosition = elmBrowseStartValue.value;
				intStopPosition = elmBrowseStopValue.value;
				window.location.href = "browse.php?assembly_id="+intAssemblyId+"&structure_id="+intStructureId+"&start_position="+intStartPosition+"&stop_position="+intStopPosition;
			}
		</script>
		<script src="https://d3js.org/d3.v4.js"></script>
		<style>
		</style>
	</head>
	<body>
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
					<h2>Browse</h2>
					<p class="lead">The following is a breakdown of SNP density from your uploaded studies. The selected region (x-axis) is graphed against the number of SNPs found (y-axis) within it. Change the assembly, structure and region by using the dropdowns and fields below. Additional views and research options are available by clicking the explore button within each study.</p>
				</div>
				<div class="row">
					<div class="col-3">
						<div class="card mt-3 pb-4">
							<div class="card-header text-white bg-secondary font-weight-bold">
								Assembly
							</div>
							<div class="card-body">
								<select class="form-control" id="elmBrowseAssemblyValue" name="elmBrowseAssemblyValue" onchange="funBrowseChangeRegion();">
									<?php
										//****************************************************************************************************************
										//	^--- PHP -- 10A - START of looping through assemblies
										//****************************************************************************************************************
										foreach ($objCollection->assemblies as $arrAssembly) {
											echo "<option value='".$arrAssembly["id"]."'".$arrAssembly["option_selected"].">".$arrAssembly["name"]."</option>";
										}
										//****************************************************************************************************************
										//	v--- PHP -- 10A - END of looping through assemblies
										//****************************************************************************************************************
									 ?>
								</select>
							</div>
						</div>
					</div>
					<div class="col-3">
						<div class="card mt-3 pb-4">
							<div class="card-header text-white bg-secondary font-weight-bold">
								Structure
							</div>
							<div class="card-body">
								<select class="form-control" id="elmBrowseStructureValue" name="elmBrowseStructureValue" onchange="funBrowseChangeRegion();">
									<?php
										//****************************************************************************************************************
										//	^--- PHP -- 10B - START of looping through structures
										//****************************************************************************************************************
										foreach ($objCollection->assemblies[$objCollection->selected->assembly->index]["structures"] as $arrStructure) {
											echo "<option value='".$arrStructure["id"]."'".$arrStructure["option_selected"].">".$arrStructure["name"]."</option>";
										}
										//****************************************************************************************************************
										//	v--- PHP -- 10B - END of looping through structures
										//****************************************************************************************************************
									 ?>
								</select>
							</div>
						</div>
					</div>
					<div class="col-3">
						<div class="card mt-3">
							<div class="card-header text-white bg-secondary font-weight-bold">
								Start
							</div>
							<div class="card-body">
								<input type="text" class="form-control" id="elmBrowseStartValue" name="elmBrowseStartValue" value="<?php echo $objCollection->selected->start->position; ?>" onchange="funBrowseChangeRegion();">
								<small>Minimum: 1</small>
							</div>
						</div>
					</div>
					<div class="col-3">
						<div class="card mt-3">
							<div class="card-header text-white bg-secondary font-weight-bold">
								Stop
							</div>
							<div class="card-body">
								<input type="text" class="form-control" id="elmBrowseStopValue" name="elmBrowseStopValue" value="<?php echo $objCollection->selected->stop->position; ?>" onchange="funBrowseChangeRegion();">
								<small>Maximum: <?php echo $objCollection->assemblies[$objCollection->selected->assembly->index]["structures"][$objCollection->selected->structure->index]["sequence_length"] ?></small>
							</div>
						</div>
					</div>
				</div>
				<?php
					//****************************************************************************************************************
					//	^--- PHP -- 5A - START of looping through studies and creating a row for each
					//****************************************************************************************************************
					foreach ($objCollection->assemblies[$objCollection->selected->assembly->index]["studies"] as $arrStudy) {
						?>
							<div class="card mt-3">
								<div class="card-header text-white bg-secondary font-weight-bold">
									<?php echo $arrStudy["name"];?>
								</div>
								<div class="card-body">
									<div class="row">
										<div class="col-2">
											<h4 class="mb-0 mt-0" id="elmBrowseSNPCountStudy<?php echo $arrStudy["id"];?>"></h4>
											<small class="mb-0 mt-0">SNPs</small>
											<h4 class="mb-0 mt-2"><?php echo number_format($arrStudy["cultivar_count"]);?></h4>
											<small class="mb-0 mt-0">Cultivars</small><br />
											<div class="btn-group mt-3 ml-0" role="group" id="elmBrowseSNPCountStudy<?php echo $arrStudy["id"];?>Explore">
											    <button id="elmBrowseSNPCountStudy<?php echo $arrStudy["id"];?>Dropdown" type="button" class="btn btn-sm btn-light dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
											    	Explore
											    </button>
											    <div class="dropdown-menu" aria-labelledby="elmBrowseSNPCountStudy<?php echo $arrStudy["id"];?>Dropdown">
											    	<a class="dropdown-item" href="cultivar_similarity.php?assembly_id=<?php echo $objCollection->selected->assembly->id;?>&structure_id=<?php echo $objCollection->selected->structure->id;?>&start_position=<?php echo $objCollection->selected->start->position;?>&stop_position=<?php echo $objCollection->selected->stop->position;?>&study_id=<?php echo $arrStudy["id"];?>">Cultivar Similarity</a>
											    	<a class="dropdown-item" href="haplotype_groups.php?assembly_id=<?php echo $objCollection->selected->assembly->id;?>&structure_id=<?php echo $objCollection->selected->structure->id;?>&start_position=<?php echo $objCollection->selected->start->position;?>&stop_position=<?php echo $objCollection->selected->stop->position;?>&study_id=<?php echo $arrStudy["id"];?>">Haplotype Groups</a>
											    </div>
											</div>
										</div>
										<div id="elmBrowseDensityStudy<?php echo $arrStudy["id"];?>" class="col-10"></div>
									</div>
								</div>
							</div>
						<?php
					}
					//****************************************************************************************************************
					//	v--- PHP -- 5A - END of looping through studies and creating a row for each
					//****************************************************************************************************************
				?>
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
	<?php
		//****************************************************************************************************************
		//	^--- PHP -- 2A - START of looping through studies and initializing the density histogram
		//****************************************************************************************************************
		foreach ($objCollection->assemblies[$objCollection->selected->assembly->index]["studies"] as $arrStudy) {
			?>
				<script>

					var elmBrowseDensityStudy<?php echo $arrStudy["id"];?> = document.getElementById("elmBrowseDensityStudy<?php echo $arrStudy["id"];?>");
					var strCSSWidthprop<?php echo $arrStudy["id"];?> = window.getComputedStyle(elmBrowseDensityStudy<?php echo $arrStudy["id"];?>, null).getPropertyValue("width");
					intElemWidth<?php echo $arrStudy["id"];?> = parseInt(strCSSWidthprop<?php echo $arrStudy["id"];?>.substring(0, strCSSWidthprop<?php echo $arrStudy["id"];?>.length - 2));

					// set the dimensions and margins of the graph
					var margin<?php echo $arrStudy["id"];?> = {top: 10, right: 40, bottom: 20, left: 40},
						width<?php echo $arrStudy["id"];?> = intElemWidth<?php echo $arrStudy["id"];?> - margin<?php echo $arrStudy["id"];?>.left - margin<?php echo $arrStudy["id"];?>.right,
						height<?php echo $arrStudy["id"];?> = 150 - margin<?php echo $arrStudy["id"];?>.top - margin<?php echo $arrStudy["id"];?>.bottom;

					// append the svg object to the body of the page
					var svg<?php echo $arrStudy["id"];?> = d3.select("#elmBrowseDensityStudy<?php echo $arrStudy["id"];?>")
					  	.append("svg")
						.attr("width", width<?php echo $arrStudy["id"];?> + margin<?php echo $arrStudy["id"];?>.left + margin<?php echo $arrStudy["id"];?>.right)
						.attr("height", height<?php echo $arrStudy["id"];?> + margin<?php echo $arrStudy["id"];?>.top + margin<?php echo $arrStudy["id"];?>.bottom)
					  	.append("g")
						.attr("transform",
							  "translate(" + margin<?php echo $arrStudy["id"];?>.left + "," + margin<?php echo $arrStudy["id"];?>.top + ")");

					// get the data
					d3.json("snp_positions_script.php?study_id=<?php echo $arrStudy["id"];?>&structure_id=<?php echo $objCollection->selected->structure->id; ?>&start_position=<?php echo $objCollection->selected->start->position; ?>&stop_position=<?php echo $objCollection->selected->stop->position; ?>", function(data) {

						if(data.length == 0){
							elmBrowseDensityStudy<?php echo $arrStudy["id"];?>.parentNode.removeChild(elmBrowseDensityStudy<?php echo $arrStudy["id"];?>);
							elmBrowseSNPCountStudy<?php echo $arrStudy["id"];?>Explore = document.getElementById("elmBrowseSNPCountStudy<?php echo $arrStudy["id"];?>Explore");
							elmBrowseSNPCountStudy<?php echo $arrStudy["id"];?>Explore.style.visibility = "hidden";
							elmBrowseSNPCountStudy<?php echo $arrStudy["id"];?>Explore.style.display = "none";
							document.getElementById("elmBrowseSNPCountStudy<?php echo $arrStudy["id"];?>").innerHTML = "0";
						}else{
							// X axis: scale and draw:
						  	var x<?php echo $arrStudy["id"];?> = d3.scaleLinear()
							  	.domain([<?php echo $objCollection->selected->start->position; ?>, <?php echo $objCollection->selected->stop->position; ?>+1])     // can use this instead of 1000 to have the max of data: d3.max(data, function(d) { return +d.price })
							  	.range([0, width<?php echo $arrStudy["id"];?>]);
						  	svg<?php echo $arrStudy["id"];?>.append("g")
							  	.attr("transform", "translate(0," + height<?php echo $arrStudy["id"];?> + ")")
							  	.call(d3.axisBottom(x<?php echo $arrStudy["id"];?>));

						  	// set the parameters for the histogram
						  	var histogram<?php echo $arrStudy["id"];?> = d3.histogram()
							  	.value(function(d) { return d.position; })   // I need to give the vector of value
							  	.domain(x<?php echo $arrStudy["id"];?>.domain())  // then the domain of the graphic
							  	.thresholds(x<?php echo $arrStudy["id"];?>.ticks(70)); // then the numbers of bins

						  	// And apply this function to data to get the bins
						  	var bins<?php echo $arrStudy["id"];?> = histogram<?php echo $arrStudy["id"];?>(data);

						  	// Y axis: scale and draw:
						  	var y<?php echo $arrStudy["id"];?> = d3.scaleLinear()
							  	.range([height<?php echo $arrStudy["id"];?>, 0]);
							y<?php echo $arrStudy["id"];?>.domain([0, 1.02 * d3.max(bins<?php echo $arrStudy["id"];?>, function(d) { return d.length; })]);   // d3.hist has to be called before the Y axis obviously
						  	svg<?php echo $arrStudy["id"];?>.append("g")
								.call(d3.axisLeft(y<?php echo $arrStudy["id"];?>));

						  	// append the bar rectangles to the svg element
						  	svg<?php echo $arrStudy["id"];?>.selectAll("rect")
							  	.data(bins<?php echo $arrStudy["id"];?>)
							  	.enter()
							  	.append("rect")
								.attr("x", 1)
								.attr("transform", function(d) { return "translate(" + x<?php echo $arrStudy["id"];?>(d.x0) + "," + y<?php echo $arrStudy["id"];?>(d.length) + ")"; })
								.attr("width", function(d) { return Math.round(x<?php echo $arrStudy["id"];?>(d.x1) - x<?php echo $arrStudy["id"];?>(d.x0) - 1 ); })
								.attr("height", function(d) { return height<?php echo $arrStudy["id"];?> - y<?php echo $arrStudy["id"];?>(d.length); })
								.style("fill", "#69b3a2");
								document.getElementById("elmBrowseSNPCountStudy<?php echo $arrStudy["id"];?>").innerHTML = data.length.toLocaleString();
						}


					});
				</script>
			<?php
		}
		//****************************************************************************************************************
		//	v--- PHP -- 2A - END of looping through studies and initializing the density histogram
		//****************************************************************************************************************
	?>
</html>
