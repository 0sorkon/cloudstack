<?php

$baseURL = 'http://controller.test.net:8080/client/api?';
$user = 'apiuser';
$password = 'password';
$apikey = 'apikey';
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
/*	$count=$result['listtemplatesresponse']['count'];	// count of templates in return and filter output
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

function fCreateVM($name, $serviceOfferingID, $templateID, $zoneID, $netIDs) {
	global $apikey, $secretkey, $baseURL;
	$name = uniqid($name . '-');
	$data = array(
		'command' => 'deployVirtualMachine',
		'serviceofferingid' => $serviceOfferingID,
		'templateid' => $templateID,
		'zoneid' => $zoneID,
		'name' => $name,
		'networkids' => $netIDs,
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

function fGetJobResult($id) {                           //  НЕ РАБОТАЕТ
	global $apikey, $secretkey, $baseURL;
	$data = array(
		'command' => 'queryAsyncJobResult',
		'jobId' => $id,
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

function fGetVmConsole($id) {														//  НЕ РАБОТАЕТ
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
	return $result;	

}

/*--------------- execution --------------------*/

/*
43f30c67-9ae4-48a7-bc74-127d6dc35347 - 1-1-20 offering
d7995593-d352-4244-8b69-45e9ffe27ab1 - template Ubuntu
42124526-9659-11ed-87af-2e36a97d30e2 - template CentOS
*/

$offeringID='43f30c67-9ae4-48a7-bc74-127d6dc35347';			// 	small instance 1-1-20
$offering = array(
	'1-1-20' => '43f30c67-9ae4-48a7-bc74-127d6dc35347',
	'2-4-20' => '98a2c425-b7f0-47f4-95e9-705d9800f8c6',
	'custom' => '24193961-9613-4f89-9551-5ebe472537f9',	// dynamic ?
	'custom-unconstrained' => '83da23ee-86ff-4236-9aac-0ed999b31c5d',
);
$templateID='42124526-9659-11ed-87af-2e36a97d30e2';	
$templates = array (
	'ubuntu2204' => 'd7995593-d352-4244-8b69-45e9ffe27ab1',	//	Ubuntu 22.04 password enabled template
	'centos5' => '42124526-9659-11ed-87af-2e36a97d30e2',		//	CentOS 5.5(64-bit) no GUI (KVM)
);
$zoneID='5fd41be9-cb8a-4dd9-89fc-67f6536655fd';				//	NL test zone
$netOfferings= array (
	'sharedWithSG' => '',								//	Offering for Shared Security group enabled networks
	'shared' => 'a7820314-864b-424a-bf6f-248645a0d950',		//	Offering for Shared networks
	'isolatedNAT' => 'bed0f901-db01-4dea-8410-1575a435f187',	//	Offering for Isolated networks with Source Nat service enabled
	'isolatedNoNAT' => 'bd810e0f-80cf-4558-9af7-9894d2b85fb0',	//	Offering for Isolated networks with no Source Nat service
	'sharedElasticIP' => '',								// 	Offering for Shared networks with Elastic IP and Elastic LB capabilities
	'l2' => '4c1154f1-aaec-4e07-b1a7-a2fdb3a210ca',			// 	Offering for L2 networks
	'qcloud' => '',									//	Offering for QuickCloud with no services						
);
//$result = fGetTemplate ();
//$result = fGetServiceOffering();
$result = fCreateVM('test-instance',$offering['1-1-20'], $templates['ubuntu2204'], $zoneID, $netOfferings['shared']);
//$result = fGetJobResult('501bbee4-6877-4680-aa5e-3f5603308e84');			
//$result = fRemoveVM('32a87ee9-44a6-4fe2-8c9f-4fa1b5e672f3', 'true');
//$result = fGetVmConsole('87913376-e908-4478-b667-2964c5cfebb0');			

print_r($result);

?>
