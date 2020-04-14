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
	//	^--- PHP -- 1C - START of retrieving the assemblies
	//****************************************************************************************************************
	$objCollection = new stdClass();
	$objCollection->sql = "SELECT * FROM tblAssemblies ORDER BY name DESC;";
	$objCollection->prepare = $objSettings->database->connection->prepare($objCollection->sql);
	$objCollection->prepare->execute();
	$objCollection->assemblies = $objCollection->prepare->fetchAll(PDO::FETCH_ASSOC);
	//****************************************************************************************************************
	//	v--- PHP -- 1C - END of retrieving the assemblies
	//****************************************************************************************************************
	//****************************************************************************************************************
	//	^--- PHP -- 1D - START of retrieving the select status for each assembly option
	//****************************************************************************************************************
	for ($intLoopCounter = 0; $intLoopCounter < count($objCollection->assemblies); $intLoopCounter++) {
		if($objCollection->assemblies[$intLoopCounter]["id"] == $objRequest->assembly_id){
			$objCollection->assemblies[$intLoopCounter]["select_status"] = " selected='selected'";
		}else{
			$objCollection->assemblies[$intLoopCounter]["select_status"] = "";
		}
	}
	//****************************************************************************************************************
	//	v--- PHP -- 1D - END of retrieving the select status for each assembly option
	//****************************************************************************************************************
?>
<!doctype html>
<html lang="en">
	<head>
    	<meta charset="utf-8">
    	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    	<meta name="description" content="">
    	<meta name="author" content="">
    	<title>phpSNP - Study - Upload</title>
    	<!-- Bootstrap core CSS -->
    	<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css" integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">
		<script src="scripts.js"></script>
		<script>
			function funStudyUploadSubmit(){
				elmStudyUploadSourceValue = document.getElementById("elmStudyUploadSourceValue");
				if(funValidURL(elmStudyUploadSourceValue.value)){
					elmStudyUploadSubmitButton = document.getElementById("elmStudyUploadSubmitButton");
					elmStudyUploadSubmitButton.innerHTML = "Please wait. Study is uploading...";
					elmStudyUploadSubmitButton.disabled = true;
					return true;
				}else{
					alert("Please provide a valid URL for your Source field.");
					return false;
				}
			}
		</script>
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
					<h2>Study - Upload</h2>
					<p class="lead">It takes a few steps to upload a new Study. To start the process, fill out the form below and select a properly formatted VCF file. The file you select must end with '.vcf' or it will be rejected. When you are ready, click the upload study button below to begin processing your new Study.</p>
		  		</div>
				<div class="row mt-2 mb-4">
					<ul class="step d-flex flex-nowrap">
						<li class="step-item active">
							<span class="">Step 1<br />Details and File Upload</span>
					  	</li>
						<li class="step-item">
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
					<form action="study_upload_script.php" method="post" id="elmStudyUploadForm" name="elmStudyUploadForm"  enctype="multipart/form-data" onsubmit="return funStudyUploadSubmit();">
						<div class="form-group row">
							<label for="elmStudyUploadNameValue" class="col-sm-3 col-form-label text-right">Name</label>
							<div class="col-sm-9">
								<input type="text" class="form-control" id="elmStudyUploadNameValue" name="elmStudyUploadNameValue" placeholder="Name of Study" required>
							</div>
						</div>
						<div class="form-group row">
							<label for="elmStudyUploadSourceValue" class="col-sm-3 col-form-label text-right">Source</label>
							<div class="col-sm-9">
								<input type="text" class="form-control" id="elmStudyUploadSourceValue" name="elmStudyUploadSourceValue" placeholder="Source URL" required>
							</div>
						</div>
						<div class="form-group row">
							<label for="elmStudyUploadFileSelected" class="col-sm-3 col-form-label text-right">VCF</label>
							<div class="col-sm-9">
								<input type="file" class="form-control-file" id="elmStudyUploadFileSelected" name="elmStudyUploadFileSelected" required>
							</div>
						</div>
						<div class="form-group row">
							<label for="elmStudyUploadAssemblyValue" class="col-sm-3 col-form-label text-right">Assembly</label>
							<div class="col-sm-9">
								<select class="form-control" id="elmStudyUploadAssemblyValue" name="elmStudyUploadAssemblyValue" required>
									<?php
										//****************************************************************************************************************
										//	^--- PHP -- 10A - START of looping through assemblies
										//****************************************************************************************************************
										foreach ($objCollection->assemblies as $arrAssembly) {
											echo "<option value='".$arrAssembly["id"]."'".$arrAssembly["select_status"].">".$arrAssembly["name"]."</option>";
										}
										//****************************************************************************************************************
										//	v--- PHP -- 10A - END of looping through assemblies
										//****************************************************************************************************************
									 ?>
								</select>
							</div>
						</div>
						<div class="form-group row">
							<label for="elmStudyUploadSubmitButton" class="col-sm-3 col-form-label text-right"></label>
							<div class="col-sm-9">
								<button id="elmStudyUploadSubmitButton" name="elmStudyUploadSubmitButton" type="submit" class="btn btn-primary">Upload Study</button>
							</div>
						</div>
					</form>
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
