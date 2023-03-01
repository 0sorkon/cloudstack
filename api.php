<?php

$baseURL = 'http://1.1.1.1:8080/client/api?';
$user = 'user';
$password = 'password';
$apikey = 'key';
$secretkey = 'secret';

/*--------------------- functions -----------------------*/

function fMakeSignature($commands, $secret, $apikey) {
	global $apikey;
	$commands['apikey'] = $apikey;
	ksort($commands);
	$commands = array_map('strtolower', $commands);
	$commands = http_build_query($commands,'',null,PHP_QUERY_RFC3986);
	$sign = hash_hmac('sha1', $commands, $secret, true);
	$sign = base64_encode ($sign);
//	echo $sign;
	return($sign);
	
}
	
function fMakeRequest($url, $method='get') {
	global $user, $password;
	$ch = curl_init();
	curl_setopt($ch,CURLOPT_URL,$url); 
	if ($method == 'post') {
		curl_setopt($ch,CURLOPT_POST,true);
	}
	curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,false);
	curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
//	curl_setopt($ch, CURLOPT_USERPWD, $user . ":" . $password);
	$response = json_decode(curl_exec($ch),true);
//	$response = curl_exec($ch);
	curl_reset($ch);
	curl_close($ch);
	return($response);
}

function fGetTemplate (){
	global $apikey, $secretkey, $baseURL;
	;
	$data = array(
		"command" => 'listTemplates',			// main command here
		"templatefilter" => 'featured',
		'details' => 'min',
		"response" => "json",
		'apikey' => $apikey,
		);
	$signature = fMakeSignature ($data, $secretkey, $apikey);
	$data['signature'] = $signature;
	$url = http_build_query($data,'',null,PHP_QUERY_RFC3986);
	$url = $baseURL.$url;
	echo "URL is: ".$url."\n";
	$result = fMakeRequest($url);
	return($result);
/*	$count=$result['listtemplatesresponse']['count'];	// count of templates in response
	$i = 0;
	while ( $i < $count) {
		echo "Template name: ".$result['listtemplatesresponse']['template'][$i]['name'];
		echo ". ID: ".$result['listtemplatesresponse']['template'][$i]['id']."\n";
		$i++;
	
	}
*/		
}

function fGetServiceOffering (){
	global $apikey, $secretkey, $baseURL;
	$data = array(
		"command" => 'listServiceOfferings',			// main command here
		"response" => "json",
		'apikey' => $apikey,
		);
	$signature = fMakeSignature ($data, $secretkey, $apikey);
	$data['signature'] = $signature;
	$url = http_build_query($data,'',null,PHP_QUERY_RFC3986);
	$url = $baseURL.$url;
	echo "URL is: ".$url."\n";
	$result = fMakeRequest($url);
	return $result;
}

function fCreateVM($name, $serviceOfferingID, $templateID, $zoneID) {
	global $apikey, $secretkey, $baseURL;
	$name = uniqid($name . '-');
	$data = array(
		'command' => 'deployVirtualMachine',
		'serviceofferingid' => $serviceOfferingID,
		'templateid' => $templateID,
		'zoneid' => $zoneID,
		'name' => $name,
//		'networkids' => $netIDs,
		'response' => 'json',
		'apikey' => $apikey,
//		'hostid' => '4d8c33a1-cbe2-43c7-9a2e-e4c5d7f2f827',
	);
	$signature = fMakeSignature ($data, $secretkey, $apikey);
	$data['signature'] = $signature;
	$url = http_build_query($data,'',null,PHP_QUERY_RFC3986);
	$url = $baseURL.$url;
	echo "URL is: ".$url."\n";
	$result = fMakeRequest($url);
	return $result;
}

function fRemoveVM($id, $expurge) {
	global $apikey, $secretkey, $baseURL;
	$data = array(
		'command' => 'destroyVirtualMachine',
		'id' => $id,
		'expunge' => $expurge,
		'response' => 'json',
		'apikey' => $apikey,
	);
	$signature = fMakeSignature ($data, $secretkey, $apikey);
	$data['signature'] = $signature;
	$url = http_build_query($data,'',null,PHP_QUERY_RFC3986);
	$url = $baseURL.$url;
	echo "URL is: ".$url."\n";
	$result = fMakeRequest($url);
	return $result;
}

function fGetJobResult($id) {
	global $apikey, $secretkey, $baseURL;
	$data = array(
		'command' => 'queryAsyncJobResult',
		'jobid' => $id,
		'response' => 'json',
		'apikey' => $apikey,
	);
	$signature = fMakeSignature ($data, $secretkey, $apikey);
	$data['signature'] = $signature;
	$url = http_build_query($data,'',null,PHP_QUERY_RFC3986);
	$url = $baseURL.$url;
	//echo "URL is: ".$url."\n";
	$result = fMakeRequest($url);
	return $result['queryasyncjobresultresponse'];
}
/*
function fGetVmConsole($id) {														
	global $apikey, $secretkey, $baseURL;
	$consoleURL = 'http://controller.introserv.net:8080/client/console?';
	$data = array(
		'cmd' => 'access',
		'vm' => $id,
		'response' => 'json',
		'apikey' => $apikey,
	);
	$signature = fMakeSignature ($data, $secretkey, $apikey);
	$data['signature'] = $signature;
	$url = http_build_query($data,'',null,PHP_QUERY_RFC3986);
	$url = $consoleURL.$url;
	echo "URL is: ".$url."\n";
	$result = fMakeRequest($url);
	var_dump($result);
	return $result;	

}
*/
function fGetVmConsole($vmId, $durationMinutes) {
	global $apikey, $secretkey, $baseURL;
	$consoleURL = 'http://1.1.1.1:8080/client/console?';
	$token = bin2hex(random_bytes(16));
	$expireTime = time() + ($durationMinutes * 60);
	$data = array(
		'apikey' => $apikey,
		'cmd' => 'access',
		'vm' => $vmId,
		'vncpassword' => $token,
		'expires' => $expireTime,
		'response' => 'json',
	);
	$signature = fMakeSignature ($data, $secretkey, $apikey);
	$data['signature'] = $signature;
	$url = http_build_query($data,'',null,PHP_QUERY_RFC3986);
	$url = $consoleURL.$url;
	$ch = curl_init();
	curl_setopt($ch,CURLOPT_URL,$url); 
	curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,false);
	curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
	$response = curl_exec($ch);
	curl_reset($ch);
	curl_close($ch);
	$pattern = '/<frame\s+src="([^"]*1\\.1\\.1\\.1\\/resource\\/noVNC\\/vnc\\.html\\?[^"]*)">/';
	preg_match($pattern, $response, $matches);
	$url = "http:".$matches[1];
	$valid=date("r",$expireTime);
	$result = array ("valid" => $valid, "accessurl"=>$url);
	return $result;
}

/*
43f30c67-9ae4-48a7-bc74-127d6dc35347 - 1-1-20 offering
d7995593-d352-4244-8b69-45e9ffe27ab1 - template Ubuntu
42124526-9659-11ed-87af-2e36a97d30e2 - template CentOS
*/

$zoneID='a91975a5-72e6-4c4a-ad1a-46417d2d9e63';				//	NL test zone
$offeringID='43f30c67-9ae4-48a7-bc74-127d6dc35347';			// 	small instance 1-1-20
$offering = array(
	'1-1-20' => '43f30c67-9ae4-48a7-bc74-127d6dc35347',
	'2-4-20' => '98a2c425-b7f0-47f4-95e9-705d9800f8c6',
	'custom' => '24193961-9613-4f89-9551-5ebe472537f9',	// dynamic ?
	'custom-unconstrained' => '83da23ee-86ff-4236-9aac-0ed999b31c5d',
);
//$templateID='42124526-9659-11ed-87af-2e36a97d30e2';
$templates = array (
	'ubuntu2204' => 'cb962412-3066-4153-95f4-9f5f6ac8fe8f',	//	Ubuntu 22.04 password enabled template
	'centos5' => '42124526-9659-11ed-87af-2e36a97d30e2',		//	CentOS 5.5(64-bit) no GUI (KVM)
);
$netOfferings= array (
	'sharedWithSG' => '',								//	Offering for Shared Security group enabled networks
	'sharedNoVLAN' => 'd33509fe-f95f-45b5-84c7-c797b7049af2',		//	Offering for Shared networks
	'isolated' => '',
	'isolatedwithNAT' => '7a588b34-9f60-4046-a3db-cb3af5419338',	//	Offering for Isolated networks with Source Nat service enabled
	'isolatedNoNAT' => 'b9e00905-26e9-4e21-a667-2fff5285de35',	//	Offering for Isolated networks with no Source Nat service
	'sharedElasticIP' => '',								// 	Offering for Shared networks with Elastic IP and Elastic LB capabilities
	'l2' => '3edd91a3-c5c6-4146-9ce1-fbac23dc74eb',			// 	Offering for L2 networks
	'qcloud' => '',									//	Offering for QuickCloud with no services
	'sharedInBasic' => 'abc059ba-7af9-4b6e-8262-6f758a340e2d',	//	defaultGuestNetwork in Basic zone network 								
);

/*--------------- execution --------------------*/

//$result = fGetTemplate ();
//$result = fGetServiceOffering();

//$result = fCreateVM('test-instance',$offering['1-1-20'], $templates['centos5'], $zoneID, $netOfferings['sharedInBasic']);
/*$result = fCreateVM('test-instance',$offering['custom'], $templates['ubuntu2204'], $zoneID);
if (isset($result['deployvirtualmachineresponse']['errorcode'])) {
	echo "Error: " . $result['deployvirtualmachineresponse']['errortext'] . "\n";
	die();
} else {
	$vmID = $result['deployvirtualmachineresponse']['id'];
	$jodID = $result['deployvirtualmachineresponse']['jobid'];
	$jobstatus = fGetJobResult($jodID);
	$jobstatus = $jobstatus['jobstatus'];
	do {
		echo ".";
		sleep(1);
		$jobstatus = fGetJobResult($jodID);
		$jobstatus = $jobstatus['jobstatus'];
	} while ($jobstatus == '0');
	$jobstatus = fGetJobResult($jodID);
	if (isset($jobstatus['jobresult']['errorcode'])) {
		echo "Error: " . $jobstatus['jobresult']['errortext'] . "\n";
		echo "Error code: ". $jobstatus['jobresult']['errorcode'] . "\n";
		die();
	}
	echo "\n Done(?) job ID ". $jodID ."\n";
	echo "Result code: " . $jobstatus['jobresultcode'] ."\n";
	echo "Name: " . $jobstatus['jobresult']['virtualmachine']['name'] ."\n";
	echo "Instance ID: " . $jobstatus['jobresult']['virtualmachine']['id'] ."\n";
	echo "login: cloud-user" ."\n";
	echo "Password: " . $jobstatus['jobresult']['virtualmachine']['password'] ."\n";
	echo "IP: " . $jobstatus['jobresult']['virtualmachine']['nic']['0']['ipaddress'] ."\n";
	echo "Network name: " . $jobstatus['jobresult']['virtualmachine']['nic']['0']['networkname'] ."\n";
	echo "Network type: " . $jobstatus['jobresult']['virtualmachine']['nic']['0']['type'] ."\n";
	$consoleURL = fGetVmConsole($jobstatus['jobresult']['virtualmachine']['id']);
	echo "Console " . $consoleURL."\n";
};
*/

//$result = fGetJobResult('ad1ce632-1c2d-4d85-b644-682382edf0bf');
//$result = fRemoveVM('e995f676-c4f2-4ab5-9f46-44753d400ac9', 'false');
//$result = fGetVmConsole('fb2fca86-7812-4c08-891e-b238ead574c7');

//print_r($result);

$link = fGetVmConsole('14f67b7d-e81d-49f5-8154-7ab169946dc5', 1);
print_r($link);

?>
