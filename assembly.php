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
	$objRequest->assembly_id = filter_input(INPUT_GET, "assembly_id", FILTER_VALIDATE_INT);
	//****************************************************************************************************************
	//	v--- PHP -- 1B - END of receiving the id for the assembly
	//****************************************************************************************************************
	//****************************************************************************************************************
	//	^--- PHP -- 1C - START of retrieving the assembly and related studies and structures
	//****************************************************************************************************************
	$objAssembly = new stdClass();
	$objAssembly->id = $objRequest->assembly_id;
	$objAssembly->sql = "SELECT * FROM tblAssemblies WHERE id = :id;";
	$objAssembly->prepare = $objSettings->database->connection->prepare($objAssembly->sql);
	$objAssembly->prepare->bindValue(':id', $objAssembly->id, PDO::PARAM_INT);
	$objAssembly->prepare->execute();
	$objAssembly->database_record = $objAssembly->prepare->fetchAll(PDO::FETCH_ASSOC)[0];
	$objAssembly->name = $objAssembly->database_record["name"];
	$objAssembly->source = $objAssembly->database_record["source"];
	$objAssembly->sequence_length = $objAssembly->database_record["sequence_length"];
	$objAssembly->sql = "SELECT id, assembly_id, name, sequence_length FROM tblStructures WHERE assembly_id = :assembly_id ORDER BY id ASC;";
	$objAssembly->prepare = $objSettings->database->connection->prepare($objAssembly->sql);
	$objAssembly->prepare->bindValue(':assembly_id', $objAssembly->id, PDO::PARAM_INT);
	$objAssembly->prepare->execute();
	$objAssembly->structures = $objAssembly->prepare->fetchAll(PDO::FETCH_ASSOC);
	$objAssembly->sql = "SELECT id, assembly_id, name, cultivar_count, snp_count FROM tblStudies WHERE assembly_id = :assembly_id ORDER BY name ASC;";
	$objAssembly->prepare = $objSettings->database->connection->prepare($objAssembly->sql);
	$objAssembly->prepare->bindValue(':assembly_id', $objAssembly->id, PDO::PARAM_INT);
	$objAssembly->prepare->execute();
	$objAssembly->studies = $objAssembly->prepare->fetchAll(PDO::FETCH_ASSOC);
	//****************************************************************************************************************
	//	v--- PHP -- 1C - END of retrieving the assembly and related studies and structures
	//****************************************************************************************************************
	//****************************************************************************************************************
	//	^--- PHP -- 1D - START of retrieving the error count for each study
	//****************************************************************************************************************
	for ($intLoopCounter = 0; $intLoopCounter < count($objAssembly->studies); $intLoopCounter++) {
		$objAssembly->sql = "SELECT count(id) as error_count FROM tblErrors WHERE study_id = :study_id;";
		$objAssembly->prepare = $objSettings->database->connection->prepare($objAssembly->sql);
		$objAssembly->prepare->bindValue(':study_id', $objAssembly->studies[$intLoopCounter]["id"], PDO::PARAM_INT);
		$objAssembly->prepare->execute();
		$objAssembly->studies[$intLoopCounter]["error_count"] = $objAssembly->prepare->fetchAll(PDO::FETCH_ASSOC)[0]["error_count"];
	}
	//****************************************************************************************************************
	//	v--- PHP -- 1D - END of retrieving the error count for each study
	//****************************************************************************************************************
?>
<!doctype html>
<html lang="en">
	<head>
    	<meta charset="utf-8">
    	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    	<meta name="description" content="">
    	<meta name="author" content="">
    	<title>phpSNP - Assembly</title>
    	<!-- Bootstrap core CSS -->
    	<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css" integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">
		<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/v/bs4/dt-1.10.16/b-1.4.2/b-html5-1.4.2/fc-3.2.3/fh-3.1.3/sc-1.4.3/sl-1.2.3/datatables.min.css"/>
		<script>
			function funAssemblyRemoveVerify(){
				if(window.confirm("Are you sure you want to remove this assembly?\nAny studies connected to this assembly will also be removed.\nYou will not be able to undo this action.")){
					elmAssemblyViewDownload = document.getElementById("elmAssemblyViewDownload");
					elmAssemblyViewRemove = document.getElementById("elmAssemblyViewRemove");
					elmAssemblyViewDownload.style.visibility = "hidden";
					elmAssemblyViewDownload.style.display = "none";
					elmAssemblyViewRemove.innerHTML = "Please Wait. Records and files are being deleted."
					elmAssemblyViewRemove.disabled = true;
					window.location.href = "assembly_delete_script.php?id=<?php echo $objAssembly->id; ?>";
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
					<h2><?php echo $objAssembly->name; ?></h2>
					<p class="lead">Below is a list of every uploaded study for the <strong><?php echo $objAssembly->name; ?></strong> assembly. Click on a study to view more information. Below this list of studies you will find a breakdown of every structure found within this assembly.</p>
					<p class="lead">
						<a id="elmAssemblyViewUploadStudy" class="btn btn-success" href="study_upload.php?assembly_id=<?php echo $objAssembly->id; ?>" role="button">Upload Study</a>
						<a id="elmAssemblyViewDownload" class="btn btn-primary" href="assembly_download_script.php?id=<?php echo $objAssembly->id; ?>" role="button">Download Assembly</a>
						<button id="elmAssemblyViewRemove" class="btn btn-danger" onclick="funAssemblyRemoveVerify()">Remove Assembly</button>
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
							      		<a href="<?php echo $objAssembly->source; ?>"><?php echo $objAssembly->source; ?></a>
							    	</div>
								</div>
								<div class="row">
							    	<div class="col-sm-2 font-weight-bold">
							      		Bases
							    	</div>
							    	<div class="col-sm-10">
							      		<?php echo number_format($objAssembly->sequence_length); ?>
							    	</div>
								</div>
								<div class="row">
							    	<div class="col-sm-2 font-weight-bold">
							      		Studies
							    	</div>
							    	<div class="col-sm-10">
							      		<?php echo number_format(count($objAssembly->studies)); ?>
							    	</div>
								</div>
								<div class="row">
							    	<div class="col-sm-2 font-weight-bold">
							      		Structures
							    	</div>
							    	<div class="col-sm-10">
							      		<?php echo number_format(count($objAssembly->structures)); ?>
							    	</div>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="row">
					<div class="card w-100 mt-4">
						<div class="card-header text-white bg-secondary font-weight-bold">
							Studies
						</div>
						<div class="card-body">
							<table id="tblStudies" class="table table-striped table-bordered table-hover" cellspacing="0" width="100%">
								<thead>
									<tr>
										<th>Name</th>
										<th>Cultivars</th>
										<th>SNPs</th>
										<th>Errors</th>
									</tr>
								</thead>
								<tbody>
									<?php
										//****************************************************************************************************************
										//	^--- PHP -- 8A - START of looping through studies
										//****************************************************************************************************************
										foreach ($objAssembly->studies as $arrStudy) {
											echo "<tr><td><a href='study.php?assembly_id=".$objAssembly->id."&study_id=".$arrStudy["id"]."'>".$arrStudy["name"]."</a></td><td>".number_format($arrStudy["cultivar_count"])."</td><td>".number_format($arrStudy["snp_count"])."</td><td>".number_format($arrStudy["error_count"])."</td></tr>";
										}
										//****************************************************************************************************************
										//	v--- PHP -- 8A - END of looping through studies
										//****************************************************************************************************************
									 ?>
								</tbody>
							</table>
						</div>
					</div>
				</div>
				<div class="row">
					<div class="card w-100 mt-4">
						<div class="card-header text-white bg-secondary font-weight-bold">
							Structures
						</div>
						<div class="card-body">
							<table id="tblStructures" class="table table-striped table-bordered table-hover" cellspacing="0" width="100%">
								<thead>
									<tr>
										<th>Name</th>
										<th>Sequence Length</th>
									</tr>
								</thead>
								<tbody>
									<?php
										//****************************************************************************************************************
										//	^--- PHP -- 8B - START of looping through structures
										//****************************************************************************************************************
										foreach ($objAssembly->structures as $arrStructure) {
											echo "<tr><td>".$arrStructure["name"]."</td><td>".number_format($arrStructure["sequence_length"])."</td></tr>";
										}
										//****************************************************************************************************************
										//	v--- PHP -- 8B - END of looping through structures
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
				$('#tblStudies').DataTable( {
					order: [[0, 'asc']],
					columns: [
						{ orderable: true, width: "40%" },
						{ orderable: true, width: "20%" },
						{ orderable: true, width: "20%" },
						{ orderable: true, width: "20%" }
					]
				} );
				$('#tblStructures').DataTable( {
					order: [[0, 'asc']],
					columns: [
						{ orderable: true, width: "60%" },
						{ orderable: true, width: "40%" }
					]
				} );
			} );
		</script>
	</body>
</html>
