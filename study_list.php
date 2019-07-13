<?php
	//****************************************************************************************************************
	//	^--- PHP -- 1A - START of startup
	//****************************************************************************************************************
	include "startup.php";
	//****************************************************************************************************************
	//	v--- PHP -- 1A - END of startup
	//****************************************************************************************************************
	//****************************************************************************************************************
	//	^--- PHP -- 1B - START of retrieving the studies
	//****************************************************************************************************************
	$objCollection = new stdClass();
	$objCollection->sql = "SELECT tblAssemblies.name AS assembly_name, tblStudies.id AS id, tblStudies.assembly_id AS assembly_id, tblStudies.name AS name, tblStudies.snp_count AS snp_count, tblStudies.cultivar_count AS cultivar_count  FROM tblAssemblies, tblStudies WHERE tblAssemblies.id = tblStudies.assembly_id ORDER BY name DESC;";
	$objCollection->prepare = $objSettings->database->connection->prepare($objCollection->sql);
	$objCollection->prepare->execute();
	$objCollection->studies = $objCollection->prepare->fetchAll(PDO::FETCH_ASSOC);
	//****************************************************************************************************************
	//	v--- PHP -- 1B - END of retrieving the studies
	//****************************************************************************************************************
	//echo "The time is newly " . date("h:i:sa");
?>
<!doctype html>
<html lang="en">
	<head>
    	<meta charset="utf-8">
    	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    	<meta name="description" content="">
    	<meta name="author" content="">
    	<title>phpSNP - Studies</title>
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
					<h2>Studies</h2>
					<p class="lead">Below is a list of every study that has been uploaded, parsed and saved to the database. Click on a study to view corresponding SNPs and Cultivars.</p>
					<p class="lead"><a class="btn btn-success" href="study_upload.php" role="button">Upload Study</a></p>
				</div>
				<div class="row">
					<table id="tblStudies" class="table table-striped table-bordered table-hover" cellspacing="0" width="100%">
						<thead>
							<tr>
								<th>Assembly</th>
								<th>Name</th>
								<th>Cultivars</th>
								<th>SNPs</th>
							</tr>
						</thead>
						<tbody>
							<?php
								//****************************************************************************************************************
								//	^--- PHP -- 8A - START of looping through studies
								//****************************************************************************************************************
								foreach ($objCollection->studies as $arrStudy) {
									echo "<tr><td><a href='assembly_view.php?id=".$arrStudy["assembly_id"]."'>".$arrStudy["assembly_name"]."</a></td><td><a href='study_view.php?id=".$arrStudy["id"]."'>".$arrStudy["name"]."</a></td><td>".number_format($arrStudy["cultivar_count"])."</td><td>".number_format($arrStudy["snp_count"])."</td></tr>";
								}
								//****************************************************************************************************************
								//	v--- PHP -- 8A - END of looping through studies
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
				$('#tblStudies').DataTable( {
					order: [[0, 'asc']],
					columns: [
						{ orderable: true, width: "20%" },
						{ orderable: true, width: "40%" },
						{ orderable: true, width: "20%" },
						{ orderable: true, width: "20%" }
					]
				} );
			} );
		</script>
	</body>
</html>
