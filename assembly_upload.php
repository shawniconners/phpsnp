<?php
	//****************************************************************************************************************
	//	^--- PHP -- 1A - START of startup
	//****************************************************************************************************************
	include "startup.php";
	//****************************************************************************************************************
	//	v--- PHP -- 1A - END of startup
	//****************************************************************************************************************
?>
<!doctype html>
<html lang="en">
	<head>
    	<meta charset="utf-8">
    	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    	<meta name="description" content="">
    	<meta name="author" content="">
    	<title>phpSNP - Assembly - Upload</title>
    	<!-- Bootstrap core CSS -->
    	<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css" integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">
		<script src="scripts.js"></script>
		<script>
			function funAssemblyUploadSubmit(){
				elmAssemblyUploadSourceValue = document.getElementById("elmAssemblyUploadSourceValue");
				if(funValidURL(elmAssemblyUploadSourceValue.value)){
					elmAssemblyUploadSubmitButton = document.getElementById("elmAssemblyUploadSubmitButton");
					elmAssemblyUploadSubmitButton.innerHTML = "Please wait. Assembly is uploading...";
					elmAssemblyUploadSubmitButton.disabled = true;
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
					<h2>Assembly - Upload</h2>
					<p class="lead">It takes a few steps to upload a new assembly. To start the process, fill out the form below and select a properly formatted FASTA file. The file you select must end with '.fasta' or it will be rejected. When you are ready, click the start button below to begin processing your new assembly.</p>
		  		</div>
				<div class="row mt-2 mb-4">
					<ul class="step d-flex flex-nowrap">
						<li class="step-item active">
							<span class="">Step 1<br />Details and File Upload</span>
					  	</li>
						<li class="step-item">
							<span class="">Step 2<br />Database Import</span>
						</li>
					</ul>
				</div>
				<div class="row">
					<form action="assembly_upload_script.php" method="post" id="elmAssemblyUploadForm" name="elmAssemblyUploadForm"  enctype="multipart/form-data" onsubmit="return funAssemblyUploadSubmit();">
						<div class="form-group row">
							<label for="elmAssemblyUploadNameValue" class="col-sm-3 col-form-label text-right">Name</label>
							<div class="col-sm-9">
								<input type="text" class="form-control" id="elmAssemblyUploadNameValue" name="elmAssemblyUploadNameValue" placeholder="Name of Assembly" required>
							</div>
						</div>
						<div class="form-group row">
							<label for="elmAssemblyUploadSourceValue" class="col-sm-3 col-form-label text-right">Source</label>
							<div class="col-sm-9">
								<input type="text" class="form-control" id="elmAssemblyUploadSourceValue" name="elmAssemblyUploadSourceValue" placeholder="Source URL" required>
							</div>
						</div>
						<div class="form-group row">
							<label for="elmAssemblyUploadFileSelected" class="col-sm-3 col-form-label text-right">FASTA</label>
							<div class="col-sm-9">
								<input type="file" class="form-control-file" id="elmAssemblyUploadFileSelected" name="elmAssemblyUploadFileSelected" required>
							</div>
						</div>
						<div class="form-group row">
							<label for="elmAssemblyUploadSubmitButton" class="col-sm-3 col-form-label text-right"></label>
							<div class="col-sm-9">
								<button id="elmAssemblyUploadSubmitButton" name="elmAssemblyUploadSubmitButton" type="submit" class="btn btn-primary">Upload Assembly</button>
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
