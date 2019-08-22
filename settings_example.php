<?php
	//****************************************************************************************************************
	//	^--- PHP -- 1A - START of settings
	//****************************************************************************************************************
	$objSettings = new stdClass();
	$objSettings->snp_chunk_size = 200; // lines of SNPs per ajax request
	$objSettings->loop_interval = 10; // milliseconds between ajax loops
	$objSettings->console_logging = "false"; // turn console logging on or off
	$objSettings->insert_batch_size = 20; // number of snps per insert
	$objSettings->haplotype_max = 100; // maximum number of snps to be used for haplotype groups
	$objSettings->core = 0; // current core being used for this request
	$objSettings->webservers = [ // note, number of webservers should match the number of database connections in the pool
		"10.0.1.101",
		"10.0.1.101",
		"10.0.1.101",
		"10.0.1.101",
		"10.0.1.101",
		"10.0.1.101",
		"10.0.1.101",
		"10.0.1.101",
		"10.0.1.101",
		"10.0.1.101",
		"10.0.1.101",
		"10.0.1.101"
	];
	$objSettings->database = new stdClass();
	$objSettings->database->name = "dbSNPs";
	$objSettings->database->pool = [
		[
			'host' => "localhost",
			'user' => "mysqlserver1",
			'password' => "mysqlserver1"
		],
		[
			'host' => "localhost",
			'user' => "mysqlserver2",
			'password' => "mysqlserver2"
		],
		[
			'host' => "localhost",
			'user' => "mysqlserver3",
			'password' => "mysqlserver3"
		],
		[
			'host' => "localhost",
			'user' => "mysqlserver4",
			'password' => "mysqlserver4"
		],
		[
			'host' => "localhost",
			'user' => "mysqlserver5",
			'password' => "mysqlserver5"
		],
		[
			'host' => "localhost",
			'user' => "mysqlserver6",
			'password' => "mysqlserver6"
		],
		[
			'host' => "localhost",
			'user' => "mysqlserver7",
			'password' => "mysqlserver7"
		],
		[
			'host' => "localhost",
			'user' => "mysqlserver8",
			'password' => "mysqlserver8"
		],
		[
			'host' => "localhost",
			'user' => "mysqlserver9",
			'password' => "mysqlserver9"
		],
		[
			'host' => "localhost",
			'user' => "mysqlserver10",
			'password' => "mysqlserver10"
		],
		[
			'host' => "localhost",
			'user' => "mysqlserver11",
			'password' => "mysqlserver11"
		],
		[
			'host' => "localhost",
			'user' => "mysqlserver12",
			'password' => "mysqlserver12"
		]
	];
	//****************************************************************************************************************
	//	^--- PHP -- 1A - END of settings
	//****************************************************************************************************************
?>
