<?php
	//****************************************************************************************************************
	//	^--- PHP -- 1A - START of startup
	//****************************************************************************************************************
	include "startup.php";
	//****************************************************************************************************************
	//	v--- PHP -- 1A - END of startup
	//****************************************************************************************************************
	//****************************************************************************************************************
	//	^--- PHP -- 1B - START of retrieving the assemblies
	//****************************************************************************************************************
	$objCollection = new stdClass();
	$objCollection->sql = "SELECT * FROM tblAssemblies ORDER BY name DESC;";
	$objCollection->prepare = $objSettings->database->connection->prepare($objCollection->sql);
	$objCollection->prepare->execute();
	$objCollection->assemblies = $objCollection->prepare->fetchAll(PDO::FETCH_ASSOC);
	//****************************************************************************************************************
	//	v--- PHP -- 1B - END of retrieving the assemblies
	//****************************************************************************************************************
	//****************************************************************************************************************
	//	^--- PHP -- 1C - START of retrieving the study count for each assembly
	//****************************************************************************************************************
	for ($intLoopCounter = 0; $intLoopCounter < count($objCollection->assemblies); $intLoopCounter++) {
		$objCollection->sql = "SELECT count(id) as study_count FROM tblStudies WHERE assembly_id = :assembly_id;";
		$objCollection->prepare = $objSettings->database->connection->prepare($objCollection->sql);
		$objCollection->prepare->bindValue(':assembly_id', $objCollection->assemblies[$intLoopCounter]["id"], PDO::PARAM_INT);
		$objCollection->prepare->execute();
		$objCollection->assemblies[$intLoopCounter]["study_count"] = $objCollection->prepare->fetchAll(PDO::FETCH_ASSOC)[0]["study_count"];
	}
	//****************************************************************************************************************
	//	v--- PHP -- 1C - END of retrieving the study count for each assembly
	//****************************************************************************************************************
?>
<!doctype html>
<html lang="en">
	<head>
    	<meta charset="utf-8">
    	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    	<meta name="description" content="">
    	<meta name="author" content="">
    	<title>phpSNP - Curate</title>
    	<!-- Bootstrap core CSS -->
    	<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css" integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">
		<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/v/bs4/dt-1.10.16/b-1.4.2/b-html5-1.4.2/fc-3.2.3/fh-3.1.3/sc-1.4.3/sl-1.2.3/datatables.min.css"/>
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
					<h2>Curate</h2>
					<p class="lead">Below is a list of every assembly that has been uploaded, parsed and saved to your collection. Click on an assembly to view corresponding studies and structures.</p>
					<p class="lead"><a class="btn btn-success" href="assembly_upload.php" role="button">Upload Assembly</a></p>
				</div>
				<div class="row">
					<div class="card w-100 mt-3">
						<div class="card-header text-white bg-secondary font-weight-bold">
							Assemblies
						</div>
						<div class="card-body">
							<table id="tblAssemblies" class="table table-striped table-bordered table-hover" cellspacing="0" width="100%">
								<thead>
									<tr>
										<th>Name</th>
										<th>Studies</th>
										<th>Structures</th>
										<th>Bases</th>
									</tr>
								</thead>
								<tbody>
									<?php
										//****************************************************************************************************************
										//	^--- PHP -- 8A - START of looping through assemblies
										//****************************************************************************************************************
										foreach ($objCollection->assemblies as $arrAssembly) {
											echo "<tr><td><a href='assembly.php?assembly_id=".$arrAssembly["id"]."'>".$arrAssembly["name"]."</a></td><td>".$arrAssembly["study_count"]."</td><td>".number_format($arrAssembly["structure_count"])."</td><td>".number_format($arrAssembly["sequence_length"])."</td></tr>";
										}
										//****************************************************************************************************************
										//	v--- PHP -- 8A - END of looping through assemblies
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
				$('#tblAssemblies').DataTable( {
					order: [[0, 'asc']],
					columns: [
						{ orderable: true, width: "40%" },
						{ orderable: true, width: "20%" },
						{ orderable: true, width: "20%" },
						{ orderable: true, width: "20%" }
					]
				} );
			} );
		</script>
	</body>
</html>
