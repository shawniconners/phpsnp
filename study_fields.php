<?php
	//****************************************************************************************************************
	//	^--- PHP -- 1A - START of startup
	//****************************************************************************************************************
	include "startup.php";
	//****************************************************************************************************************
	//	v--- PHP -- 1A - END of startup
	//****************************************************************************************************************
	//****************************************************************************************************************
	//	^--- PHP -- 1B - START of receiving the id for the Study
	//****************************************************************************************************************
	$objRequest = new stdClass();
	$objRequest->id = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);
	//****************************************************************************************************************
	//	v--- PHP -- 1B - END of receiving the id for the Study
	//****************************************************************************************************************
	//****************************************************************************************************************
	//	^--- PHP -- 1C - START of retrieving the Study
	//****************************************************************************************************************
	$objStudy = new stdClass();
	$objStudy->sql = "SELECT * FROM tblStudies WHERE id = :id;";
	$objStudy->prepare = $objSettings->database->connection->prepare($objStudy->sql);
	$objStudy->prepare->bindValue(':id', $objRequest->id, PDO::PARAM_INT);
	$objStudy->prepare->execute();
	$objStudy->database_record = $objStudy->prepare->fetchAll(PDO::FETCH_ASSOC)[0];
	$objStudy->id = $objStudy->database_record["id"];
	$objStudy->name = $objStudy->database_record["name"];
	$objStudy->source = $objStudy->database_record["source"];
	$objStudy->vcf_header = array_slice(explode("\t", $objStudy->database_record["vcf_header"]), 0, 20);
	$objStudy->vcf_header[0] = str_replace("#", "", $objStudy->vcf_header[0]);
	$objStudy->vcf_first_snp = array_slice(explode("\t", $objStudy->database_record["vcf_first_snp"]), 0, 20);
	//****************************************************************************************************************
	//	v--- PHP -- 1C - END of retrieving the Study
	//****************************************************************************************************************
?>
<!doctype html>
<html lang="en">
	<head>
    	<meta charset="utf-8">
    	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    	<meta name="description" content="">
    	<meta name="author" content="">
    	<title>phpSNP - Study - Field Identification</title>
    	<!-- Bootstrap core CSS -->
    	<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css" integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">
		<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/v/bs4/dt-1.10.16/b-1.4.2/b-html5-1.4.2/fc-3.2.3/fh-3.1.3/sc-1.4.3/sl-1.2.3/datatables.min.css"/>
		<script>
			function funStudyFieldsSubmit(){
				document.getElementById("elmStudyFieldsSubmitButton").disabled = true;
				document.getElementById("elmStudyFieldsSubmitButton").innerHTML = "Please wait...";
				boolSuccess = true;
				objFields = {
					"chromosome" : 0,
					"position" : 0,
					"names" : [],
					"reference" : 0,
					"alternate" : 0,
					"first_cultivar" : 0
				};
				for (intFieldCounter = 0; intFieldCounter < 20; intFieldCounter++) {
					elmSelectField = document.getElementById("elmStudyField" + intFieldCounter);
					switch(elmSelectField.options[elmSelectField.selectedIndex].value){
						case "Unassigned":
							break;
						case "Chromosome":
							if(objFields.chromosome == 0){
								objFields.chromosome = intFieldCounter + 1;
							}else{
								boolSuccess = false;
							}
							break;
						case "Position":
							if(objFields.position == 0){
								objFields.position = intFieldCounter + 1;
							}else{
								boolSuccess = false;
							}
							break;
						case "Name":
							objFields.names.push(intFieldCounter + 1);
							break;
						case "Reference":
							if(objFields.reference == 0){
								objFields.reference = intFieldCounter + 1;
							}else{
								boolSuccess = false;
							}
							break;
						case "Alternate":
							if(objFields.alternate == 0){
								objFields.alternate = intFieldCounter + 1;
							}else{
								boolSuccess = false;
							}
							break;
						case "FirstCultivar":
							if(objFields.first_cultivar == 0){
								objFields.first_cultivar = intFieldCounter + 1;
							}else{
								boolSuccess = false;
							}
							break;
					}
				}
				if((boolSuccess) && (objFields.chromosome > 0) && (objFields.position > 0) && (objFields.reference > 0) && (objFields.alternate > 0) && (objFields.first_cultivar > 0)){
					document.getElementById("elmStudyFieldsValue").value = JSON.stringify(objFields);
					document.getElementById("elmStudyFieldsForm").submit();
				}else{
					document.getElementById("elmStudyFieldsSubmitButton").innerHTML = "Save and Continue";
					document.getElementById("elmStudyFieldsSubmitButton").disabled = false;
					alert("There is a problem with your identification of VCF fields. Please adjust your selections and resubmit.");
				}
			}
		</script>
		<style>
			#tblStudyFields_length,
			#tblStudyFields_filter,
			#tblStudyFields_info,
			#tblStudyFields_paginate{
				display:none;
			}
			#elmBottomHeader{
				margin-top: 60px;
			}
			#elmStudyFieldsSubmitButton{
				margin-top: 10px;
			}
		</style>
		<link rel="stylesheet" href="styles.css">
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
					<h2>Study - Field Identification</h2>
					<p class="lead">Your Study has been uploaded to the server and a preliminary scan has been completed. Below is the first 20 fields of the header from your uploaded VCF. Please identify the Chromosome, Position, Name(s), Reference Allele, Alternate Allele and First Cultivar fields. When you are finished identifying fields, please click the Save and Continue button below.</p>
		  		</div>
				<div class="row mt-2 mb-4">
					<ul class="step d-flex flex-nowrap">
						<li class="step-item">
							<span class="">Step 1<br />Details and File Upload</span>
					  	</li>
						<li class="step-item active">
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
						<li class="step-item">
							<span class="">Step 6<br />Cultivar Similarity Analysis</span>
						</li>
					</ul>
				</div>
				<div class="row">
					<table id="tblStudyFields" class="table table-striped table-bordered table-hover" cellspacing="0" width="100%">
						<thead>
							<tr>
								<th>Field</th>
								<th>Example Value</th>
								<th>Identification</th>
							</tr>
						</thead>
						<tbody>
							<?php
								//****************************************************************************************************************
								//	^--- PHP -- 8A - START of looping through fields
								//****************************************************************************************************************
								for($intFieldCounter = 0; $intFieldCounter < 20; $intFieldCounter++){
									echo "<tr>";
									echo "<td>".$objStudy->vcf_header[$intFieldCounter]."</td>";
									echo "<td>".$objStudy->vcf_first_snp[$intFieldCounter]."</td>";
									echo "<td>";
									echo "<select id='elmStudyField".$intFieldCounter."' name='elmStudyField".$intFieldCounter."'>";
									echo "<option value='Unassigned'>Unassigned</option>";
									echo "<option value='Chromosome'>Chromosome</option>";
									echo "<option value='Position'>Position</option>";
									echo "<option value='Name'>Name / ID</option>";
									echo "<option value='Reference'>Reference</option>";
									echo "<option value='Alternate'>Alternate</option>";
									echo "<option value='FirstCultivar'>First Cultivar</option>";
									echo "</select>";
									echo "</td>";
									echo "</tr>";
								}
								//****************************************************************************************************************
								//	v--- PHP -- 8A - END of looping through fields
								//****************************************************************************************************************
							 ?>
						</tbody>
					</table>
				</div>
				<form action="study_fields_script.php" method="post" id="elmStudyFieldsForm" name="elmStudyFieldsForm">
					<input type="hidden" id="elmStudyIdValue" name="elmStudyIdValue" value="<?php echo $objRequest->id; ?>" />
					<input type="hidden" id="elmStudyFieldsValue" name="elmStudyFieldsValue" value="" />
					<button id="elmStudyFieldsSubmitButton" name="elmStudyFieldsSubmitButton" type="button" class="btn btn-primary float-right" onclick="funStudyFieldsSubmit()">Save and Continue</button>
				</form>
				<hr id="elmBottomHeader"/>
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
				$('#tblStudyFields').DataTable( {
					"pageLength": 20,
					"bSort": false,
					columns: [
						{ width: "30%" },
						{ width: "30%" },
						{ width: "40%" }
					]
				} );
			} );
		</script>
	</body>
</html>
