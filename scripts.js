function funValidURL(inc_url) {
	if(inc_url.substring(0, 4).toLowerCase() == "http"){
		return true;
	}else{
		return false;
	}
}

function funConsoleLog(inc_message){
	if(boolConsoleLogging){
		console.log(inc_message);
	}
}
