<?php
 
	ini_set('max_execution_time', 90000); //300 seconds = 5 minutes
	$rootpassword = 'CHANGEME';    /*  <== put in the root password for WHM which also works for cpanel for any user */
	
	$accountlist_file = file_get_contents("accounts.csv");

	foreach (explode("\r",$accountlist_file) as $csv_row) {

		$csv_row = str_getcsv($csv_row);
				
		$user = $csv_row[0];
		$domain = $csv_row[1];
		$server = $csv_row[2];
		$mxcheck = $csv_row[3];
		

		$user = preg_replace('/\s+/', '', $user);
		$domain = preg_replace('/\s+/', '', $domain);
		$server = preg_replace('/\s+/', '', $server);
		$mxcheck = preg_replace('/\s+/', '', $mxcheck);
		
		// Domain Park Query
		$domain_park = 'https://' . $server . ':2083/json-api/cpanel?cpanel_jsonapi_user=' . $user . '&cpanel_jsonapi_apiversion=2&cpanel_jsonapi_module=Park&cpanel_jsonapi_func=park&domain=' . $domain;
		
		// Domain Mail Flow Query
		$domain_mxset = 'https://' . $server . ':2083/json-api/cpanel?cpanel_jsonapi_user=' . $user . '&cpanel_jsonapi_apiversion=2&cpanel_jsonapi_module=Email&cpanel_jsonapi_func=setmxcheck&domain=' . $domain . '&mxcheck=' . $mxcheck;
		
		$curl = curl_init();       
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER,0);  
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST,0);
		curl_setopt($curl, CURLOPT_HEADER,0);          
		curl_setopt($curl, CURLOPT_RETURNTRANSFER,1);  
		$header[0] = "Authorization: Basic " . 
		base64_encode($user.":".$rootpassword) . "\n\r";
		curl_setopt($curl, CURLOPT_HTTPHEADER, $header); 
		
		// Domain Parker
		curl_setopt($curl, CURLOPT_URL, $domain_park); 
		$domain_park_result = curl_exec($curl);
		if ($domain_park_result == false) {
			error_log("curl_exec threw error \"" . curl_error($curl) . "\" for $domain_park_result<br /><br />"); 
															// log error if curl exec fails
		}
		
		// Domain Email Set MX Check
		curl_setopt($curl, CURLOPT_URL, $domain_mxset); 
		$domain_mxset_result = curl_exec($curl);
		if ($domain_mxset_result == false) {
			error_log("curl_exec threw error \"" . curl_error($curl) . "\" for $domain_mxset_result<br /><br />"); 
															// log error if curl exec fails
		}

		curl_close($curl);
		
		echo "<h3>" . $domain . " parking attempt</h3>";
		echo "Attempted to park the domain: " . $domain . " by referencing the username " . $user . " on the server " . $server . "<br /><br />";
		print $domain_park_result;
		
		echo "<br /><br />";
		
		echo "Attempted to set the correct mail flow settings for the domain: " . $domain . " on the server " . $server . "<br /><br />";
		print $domain_park_result;
		
		echo "<br /><hr /><br />";
		
	}
 
?>