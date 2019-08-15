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
    	<title>phpSNP - Status</title>
    	<!-- Bootstrap core CSS -->
    	<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css" integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">
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
					<h2>Status</h2>
					<p class="lead">Below is an overview of the current configuration found within your <i>settings.php</i> file. To make changes to these settings you must update the <i>settings.php</i> file directly. Please note, depending on your server configuration, the storage section may not report accurate results.</p>
				</div>
				<div class="row">
					<div class="card w-100 mt-3">
						<div class="card-header text-white bg-secondary font-weight-bold">
							AJAX Settings
						</div>
						<div class="card-body">
							<div class="container">
								<div class="row">
							    	<div class="col-sm-3 font-weight-bold">
							      		SNP Chunk Size
							    	</div>
							    	<div class="col-sm-9">
							      		<?php echo $objSettings->snp_chunk_size; ?>
							    	</div>
								</div>
								<div class="row">
							    	<div class="col-sm-3 font-weight-bold">
							      		Loop Interval
							    	</div>
							    	<div class="col-sm-9">
							      		<?php echo $objSettings->loop_interval; ?>
							    	</div>
								</div>
								<div class="row">
							    	<div class="col-sm-3 font-weight-bold">
							      		Console Logging
							    	</div>
							    	<div class="col-sm-9">
							      		<?php
											if($objSettings->console_logging == "true"){
												echo "On";
											}else{
												echo "Off";
											}
										?>
							    	</div>
								</div>
								<div class="row">
							    	<div class="col-sm-3 font-weight-bold">
							      		Insert Batch Size
							    	</div>
							    	<div class="col-sm-9">
							      		<?php echo $objSettings->insert_batch_size; ?>
							    	</div>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="row">
					<div class="card w-100 mt-3">
						<div class="card-header text-white bg-secondary font-weight-bold">
							Connections
						</div>
						<div class="card-body">
							<div class="container">
								<div class="row">
							    	<div class="col-sm-3 font-weight-bold">
							      		Web Servers
							    	</div>
							    	<div class="col-sm-9">
							      		<?php echo count($objSettings->webservers); ?>
							    	</div>
								</div>
								<div class="row">
							    	<div class="col-sm-3 font-weight-bold">
							      		Database Pool
							    	</div>
							    	<div class="col-sm-9">
							      		<?php echo count($objSettings->database->pool); ?>
							    	</div>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="row">
					<div class="card w-100 mt-3">
						<div class="card-header text-white bg-secondary font-weight-bold">
							Storage
						</div>
						<div class="card-body">
							<div class="container">
								<div class="row">
							    	<div class="col-sm-3 font-weight-bold">
							      		Total Space
							    	</div>
							    	<div class="col-sm-9">
							      		<?php echo funReadableFilesize(disk_total_space("/")); ?>
							    	</div>
								</div>
								<div class="row">
							    	<div class="col-sm-3 font-weight-bold">
							      		Used
							    	</div>
							    	<div class="col-sm-9">
							      		<?php echo funReadableFilesize(disk_total_space("/") - disk_free_space("/")); ?>
							    	</div>
								</div>
								<div class="row">
							    	<div class="col-sm-3 font-weight-bold">
							      		Free
							    	</div>
							    	<div class="col-sm-9">
							      		<?php echo funReadableFilesize(disk_free_space("/")); ?>
							    	</div>
								</div>
							</div>
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
	</body>
</html>
