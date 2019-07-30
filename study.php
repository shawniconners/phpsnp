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
	$objRequest->assembly_id = filter_input(INPUT_GET, "assembly_id", FILTER_VALIDATE_INT);
	$objRequest->study_id = filter_input(INPUT_GET, "study_id", FILTER_VALIDATE_INT);
	//****************************************************************************************************************
	//	v--- PHP -- 1B - END of receiving the id for the study
	//****************************************************************************************************************
	//****************************************************************************************************************
	//	^--- PHP -- 1C - START of retrieving the study
	//****************************************************************************************************************
	$objStudy = new stdClass();
	$objStudy->assembly = new stdClass();
	$objStudy->assembly->id = $objRequest->assembly_id;
	$objStudy->id = $objRequest->study_id;
	$objStudy->sql = "SELECT name, source, snp_count, cultivar_count, structures FROM tblStudies WHERE id = :id AND assembly_id = :assembly_id;";
	$objStudy->prepare = $objSettings->database->connection->prepare($objStudy->sql);
	$objStudy->prepare->bindValue(':id', $objStudy->id, PDO::PARAM_INT);
	$objStudy->prepare->bindValue(':assembly_id', $objStudy->assembly->id, PDO::PARAM_INT);
	$objStudy->prepare->execute();
	$objStudy->database_record = $objStudy->prepare->fetchAll(PDO::FETCH_ASSOC)[0];
	$objStudy->name = $objStudy->database_record["name"];
	$objStudy->source = $objStudy->database_record["source"];
	$objStudy->snp_count = $objStudy->database_record["snp_count"];
	$objStudy->cultivar_count = $objStudy->database_record["cultivar_count"];
	$objStudy->structures = json_decode($objStudy->database_record["structures"]);
	sort($objStudy->structures);
	//****************************************************************************************************************
	//	v--- PHP -- 1C - END of retrieving the study
	//****************************************************************************************************************
	//****************************************************************************************************************
	//	^--- PHP -- 1D - START of retrieving the assembly
	//****************************************************************************************************************
	$objStudy->sql = "SELECT name, sequence_length FROM tblAssemblies WHERE id = :id;";
	$objStudy->prepare = $objSettings->database->connection->prepare($objStudy->sql);
	$objStudy->prepare->bindValue(':id', $objStudy->assembly->id, PDO::PARAM_INT);
	$objStudy->prepare->execute();
	$objStudy->database_record = $objStudy->prepare->fetchAll(PDO::FETCH_ASSOC)[0];
	$objStudy->assembly->name = $objStudy->database_record["name"];
	$objStudy->assembly->seuence_length = $objStudy->database_record["name"];
	//****************************************************************************************************************
	//	v--- PHP -- 1D - END of retrieving the assembly
	//****************************************************************************************************************
	//****************************************************************************************************************
	//	^--- PHP -- 1E - START of retrieving the assembly structures
	//****************************************************************************************************************
	$objStudy->sql = "SELECT id, name, sequence_length FROM tblStructures WHERE id IN (";
	$objStudy->sql .= implode(",", $objStudy->structures);
	$objStudy->sql .= ");";
	$objStudy->prepare = $objSettings->database->connection->prepare($objStudy->sql);
	$objStudy->prepare->execute();
	$objStudy->assembly->structures = $objStudy->prepare->fetchAll(PDO::FETCH_ASSOC);
	$objStudy->assembly->structures_count = count($objStudy->assembly->structures);
	//****************************************************************************************************************
	//	v--- PHP -- 1E - END of retrieving the assembly structures
	//****************************************************************************************************************
	//****************************************************************************************************************
	//	^--- PHP -- 1F - START of retrieving the SNP counts for each structure in the assembly
	//****************************************************************************************************************
	for($intStructureCounter = 0; $intStructureCounter < $objStudy->assembly->structures_count; $intStructureCounter++) {
		$objStudy->sql = "SELECT COUNT(*) AS snp_count FROM tblStudy".$objStudy->id."Structure".$objStudy->assembly->structures[$intStructureCounter]["id"]."SNPs;";
		$objStudy->prepare = $objSettings->database->connection->prepare($objStudy->sql);
		$objStudy->prepare->execute();
		$objStudy->database_record = $objStudy->prepare->fetchAll(PDO::FETCH_ASSOC)[0];
		$objStudy->assembly->structures[$intStructureCounter]["snp_count"] = $objStudy->database_record["snp_count"];
	}
	//****************************************************************************************************************
	//	v--- PHP -- 1F - END of retrieving the SNP counts for each structure in the assembly
	//****************************************************************************************************************
	//****************************************************************************************************************
	//	^--- PHP -- 1G - START of retrieving the error count for this study
	//****************************************************************************************************************
	$objStudy->sql = "SELECT COUNT(id) AS error_count FROM tblErrors WHERE study_id = :study_id;";
	$objStudy->prepare = $objSettings->database->connection->prepare($objStudy->sql);
	$objStudy->prepare->bindValue(':study_id', $objStudy->id, PDO::PARAM_INT);
	$objStudy->prepare->execute();
	$objStudy->database_record = $objStudy->prepare->fetchAll(PDO::FETCH_ASSOC)[0];
	$objStudy->error_count = $objStudy->database_record["error_count"];
	//****************************************************************************************************************
	//	v--- PHP -- 1G - END of retrieving the error count for this study
	//****************************************************************************************************************
?>
<!doctype html>
<html lang="en">
	<head>
    	<meta charset="utf-8">
    	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    	<meta name="description" content="">
    	<meta name="author" content="">
    	<title>phpSNP - Study</title>
    	<!-- Bootstrap core CSS -->
    	<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css" integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">
		<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/v/bs4/dt-1.10.16/b-1.4.2/b-html5-1.4.2/fc-3.2.3/fh-3.1.3/sc-1.4.3/sl-1.2.3/datatables.min.css"/>
		<script>
			function funStudyRemoveVerify(){
				if(window.confirm("Are you sure you want to remove this study?\nYou will not be able to undo this action.")){
					elmStudyViewDownload = document.getElementById("elmStudyViewDownload");
					elmStudyViewCultivars = document.getElementById("elmStudyViewCultivars");
					elmStudyViewRemove = document.getElementById("elmStudyViewRemove");
					elmStudyViewDownload.style.visibility = "hidden";
					elmStudyViewDownload.style.display = "none";
					elmStudyViewCultivars.style.visibility = "hidden";
					elmStudyViewCultivars.style.display = "none";
					if(document.getElementById("elmStudyViewErrors")){
						document.getElementById("elmStudyViewErrors").style.visibility = "hidden";
						document.getElementById("elmStudyViewErrors").style.display = "none";
					}
					elmStudyViewRemove.innerHTML = "Please Wait. Records and files are being deleted. This may take a few minutes."
					elmStudyViewRemove.disabled = true;
					window.location.href = "study_delete_script.php?id=<?php echo $objStudy->id; ?>";
				}
			}
		</script>
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
					<h2><?php echo $objStudy->name; ?></h2>
					<p class="lead">Below is an overview of the <strong><?php echo $objStudy->name; ?></strong> study, along with a list of structures where SNPs were found in the <strong><?php echo $objStudy->assembly->name; ?></strong> assembly.</p>
					<p class="lead">
						<a id="elmStudyViewDownload" class="btn btn-success" href="study_download_script.php?id=<?php echo $objStudy->id; ?>" role="button">Download Study</a>
						<a id="elmStudyViewCultivars" class="btn btn-info" href="study_cultivars_script.php?id=<?php echo $objStudy->id; ?>" role="button">Download Cultivar List</a>
						<?php
							//****************************************************************************************************************
							//	^--- PHP -- 7A - START of VCF error detection
							//****************************************************************************************************************
							if($objStudy->error_count > 0){
								//****************************************************************************************************************
								//	^--- PHP -- 8A - START of VCF error reporting
								//****************************************************************************************************************
								echo "<a id='elmStudyViewErrors' class='btn btn-warning' href='study_errors_script.php?id=".$objStudy->id."' role='button'>Download Errors <span class='badge badge-light'>".number_format($objStudy->error_count)."</span></a>";
								//****************************************************************************************************************
								//	v--- PHP -- 8A - END of VCF error reporting
								//****************************************************************************************************************
							}
							//****************************************************************************************************************
							//	v--- PHP -- 7A - END of VCF error detection
							//****************************************************************************************************************
						?>
						<button id="elmStudyViewRemove" class="btn btn-danger" onclick="funStudyRemoveVerify()">Remove Study</button>
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
							    	<div class="col-sm-2 font-weight-bold">
							      		Source
							    	</div>
							    	<div class="col-sm-10">
							      		<a href="<?php echo $objStudy->source; ?>"><?php echo $objStudy->source; ?></a>
							    	</div>
								</div>
								<div class="row">
							    	<div class="col-sm-2 font-weight-bold">
							      		Cultivars
							    	</div>
							    	<div class="col-sm-10">
							      		<?php echo number_format($objStudy->cultivar_count); ?>
							    	</div>
								</div>
								<div class="row">
							    	<div class="col-sm-2 font-weight-bold">
							      		SNPs
							    	</div>
							    	<div class="col-sm-10">
							      		<?php echo number_format($objStudy->snp_count); ?>
							    	</div>
								</div>
							</div>
						</div>
					</div>
					<div class="card w-100 mt-4">
						<div class="card-header text-white bg-secondary font-weight-bold">
							Structures
						</div>
						<div class="card-body">
							<table id="tblStructures" class="table table-striped table-bordered table-hover" cellspacing="0" width="100%">
								<thead>
									<tr>
										<th>Name</th>
										<th>SNP Count</th>
										<th>Sequence Length</th>
									</tr>
								</thead>
								<tbody>
									<?php
										//****************************************************************************************************************
										//	^--- PHP -- 10A - START of looping through structures and looking for structures with SNPs
										//****************************************************************************************************************
										foreach ($objStudy->assembly->structures as $arrStructure) {
											if($arrStructure["snp_count"] > 0){
												//****************************************************************************************************************
												//	^--- PHP -- 12A - START of a structure with found SNPs within this study
												//****************************************************************************************************************
												echo "<tr><td>".$arrStructure["name"]."</td><td>".number_format($arrStructure["snp_count"])."</td><td>".number_format($arrStructure["sequence_length"])."</td></tr>";
												//****************************************************************************************************************
												//	v--- PHP -- 12A - END of a structure with found SNPs within this study
												//****************************************************************************************************************
											}
										}
										//****************************************************************************************************************
										//	v--- PHP -- 10A - END of looping through structures and looking for structures with SNPs
										//****************************************************************************************************************
									 ?>
								</tbody>
							</table>
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
			$(document).ready(function() {
				$('#tblStructures').DataTable( {
					order: [[0, 'asc']],
					columns: [
						{ orderable: true, width: "40%" },
						{ orderable: true, width: "30%" },
						{ orderable: true, width: "30%" }
					]
				} );
			} );
		</script>
	</body>
</html>
