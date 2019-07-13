<?php
	//****************************************************************************************************************
	//	^--- PHP -- 1A - START of startup
	//****************************************************************************************************************
	include "startup.php";
	//****************************************************************************************************************
	//	v--- PHP -- 1A - END of startup
	//****************************************************************************************************************
	//****************************************************************************************************************
	//	^--- PHP -- 1B - START of receiving the id for the structure
	//****************************************************************************************************************
	$objRequest = new stdClass();
	$objRequest->id = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);
	$objRequest->start = filter_input(INPUT_GET, "start", FILTER_VALIDATE_INT);
	$objRequest->stop = filter_input(INPUT_GET, "stop", FILTER_VALIDATE_INT);
	//****************************************************************************************************************
	//	v--- PHP -- 1B - END of receiving the id for the structure
	//****************************************************************************************************************
	//****************************************************************************************************************
	//	^--- PHP -- 1C - START of retrieving the structure
	//****************************************************************************************************************
	$objStructure = new stdClass();
	$objStructure->id = $objRequest->id;
	$objStructure->sql = "SELECT ";
	$objStructure->sql .= "tblAssemblies.name AS assembly_name, ";
	$objStructure->sql .= "tblStructures.name AS name, ";
	$objStructure->sql .= "tblStructures.sequence_length AS sequence_length ";
	$objStructure->sql .= "FROM ";
	$objStructure->sql .= "tblAssemblies, tblStructures ";
	$objStructure->sql .= "WHERE ";
	$objStructure->sql .= "(tblAssemblies.id = tblStructures.assembly_id) ";
	$objStructure->sql .= "AND ";
	$objStructure->sql .= "(tblStructures.id = :id);";
	$objStructure->prepare = $objSettings->database->connection->prepare($objStructure->sql);
	$objStructure->prepare->bindValue(':id', $objStructure->id, PDO::PARAM_INT);
	$objStructure->prepare->execute();
	$objStructure->database_record = $objStructure->prepare->fetchAll(PDO::FETCH_ASSOC)[0];
	$objStructure->assembly_name = $objStructure->database_record["assembly_name"];
	$objStructure->name = $objStructure->database_record["name"];
	$objStructure->sequence_length = $objStructure->database_record["sequence_length"];
	//****************************************************************************************************************
	//	v--- PHP -- 1C - END of retrieving the structure
	//****************************************************************************************************************
?>
<!doctype html>
<html lang="en">
	<head>
    	<meta charset="utf-8">
    	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    	<meta name="description" content="">
    	<meta name="author" content="">
    	<title>phpSNP - Structure - View</title>
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
					<h2><?php echo $objStructure->assembly_name; ?> - <?php echo $objStructure->name; ?></h2>
					<p class="lead">Below is the first 1,000 bases of the <strong><?php echo $objStructure->name; ?></strong> structure from the <strong><?php echo $objStructure->assembly_name; ?></strong> assembly. Use the form below to change starting and ending points for a custom sequence download.</p>
					<p class="lead">
						<a class="btn btn-success" href="structure_download_script.php?id=<?php echo $objStructure->id; ?>" role="button">Download Structure</a>
					</p>
				</div>
				<div class="row">

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
