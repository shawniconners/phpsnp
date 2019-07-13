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
	//	^--- PHP -- 1C - START of retrieving the assembly and structures
	//****************************************************************************************************************
	$objAssembly = new stdClass();
	$objAssembly->id = $objRequest->id;
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
	//****************************************************************************************************************
	//	v--- PHP -- 1C - END of retrieving the assembly and structures
	//****************************************************************************************************************
?>
<!doctype html>
<html lang="en">
	<head>
    	<meta charset="utf-8">
    	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    	<meta name="description" content="">
    	<meta name="author" content="">
    	<title>phpSNP - Assembly - View</title>
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
					<p class="lead">Below is a list of every structure found in the <strong><?php echo $objAssembly->name; ?></strong> assembly. Click on a structure to view more information, or to select a partial sequence.</p>
					<p class="lead">
						<a id="elmAssemblyViewDownload" class="btn btn-success" href="assembly_download_script.php?id=<?php echo $objAssembly->id; ?>" role="button">Download Assembly</a>
						<button id="elmAssemblyViewRemove" class="btn btn-danger" onclick="funAssemblyRemoveVerify()">Remove Assembly</button>
					</p>
				</div>
				<div class="row">
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
								//	^--- PHP -- 8A - START of looping through structures
								//****************************************************************************************************************
								foreach ($objAssembly->structures as $arrStructure) {
									echo "<tr><td><a href='structure_view.php?id=".$arrStructure["id"]."'>".$arrStructure["name"]."</a></td><td>".number_format($arrStructure["sequence_length"])."</td></tr>";
								}
								//****************************************************************************************************************
								//	v--- PHP -- 8A - END of retrieving the structures
								//****************************************************************************************************************
							 ?>
						</tbody>
					</table>
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
						{ orderable: true, width: "30%" },
						{ orderable: true, width: "70%" }
					]
				} );
			} );
		</script>
	</body>
</html>
