<?php

$baseURL = 'http://1.1.1.1:8080/client/api?';
$user = 'apiuser';
$password = '';
$apikey = '';
$secretkey = '';

/*--------------------- functions -----------------------*/

function fMakeSignature($commands, $secret, $apikey) {
        global $apikey;
        $commands['apikey'] = $apikey;
        ksort($commands);
        $commands = array_map('strtolower', $commands);
        $commands = http_build_query($commands,'',null,PHP_QUERY_RFC3986);
        $sign = hash_hmac('sha1', $commands, $secret, true);
        $sign = base64_encode ($sign);
//      echo $sign;
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
//      curl_setopt($ch, CURLOPT_USERPWD, $user . ":" . $password);
        $response = json_decode(curl_exec($ch),true);
//      $response = curl_exec($ch);
        curl_reset($ch);
        curl_close($ch);
        return($response);
}

function fGetTemplate (){
        global $apikey, $secretkey, $baseURL;
        ;
        $data = array(
                "command" => 'listTemplates',                   // main command here
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
/*      $count=$result['listtemplatesresponse']['count'];       // count of templates in response
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
                "command" => 'listServiceOfferings',                    // main command here
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
                'hostid' => '4d8c33a1-cbe2-43c7-9a2e-e4c5d7f2f827',
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

function fGetVmConsole($id) {                                                                                                           //  НЕ РАБОТАЕТ
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

// просто массивы с ID объектов для удобства применения в функциях
$offeringID='43f30c67-9ae4-48a7-bc74-127d6dc35347';                     //      small instance 1-1-20
$offering = array(
        '1-1-20' => '43f30c67-9ae4-48a7-bc74-127d6dc35347',
        '2-4-20' => '98a2c425-b7f0-47f4-95e9-705d9800f8c6',
        'custom' => '24193961-9613-4f89-9551-5ebe472537f9',     // dynamic ?
        'custom-unconstrained' => '83da23ee-86ff-4236-9aac-0ed999b31c5d',
);
//$templateID='42124526-9659-11ed-87af-2e36a97d30e2';
$templates = array (
        'ubuntu2204' => 'd7995593-d352-4244-8b69-45e9ffe27ab1', //      Ubuntu 22.04 password enabled template
        'centos5' => '42124526-9659-11ed-87af-2e36a97d30e2',            //      CentOS 5.5(64-bit) no GUI (KVM)
);
$zoneID='5fd41be9-cb8a-4dd9-89fc-67f6536655fd';                         //      NL test zone
$netOfferings= array (
        'sharedWithSG' => '',                                                           //      Offering for Shared Security group enabled networks
        'shared' => 'ca604310-6305-4ac4-8844-604b46718d33',             //      Offering for Shared networks
        'isolated-custom' => '590ec07d-3703-4af9-898f-d8d2e5c00f56',
        'isolatedNAT' => '6771b23e-3ce1-492b-b9e5-2e9332444e0b',        //      Offering for Isolated networks with Source Nat service enabled
        'isolatedNoNAT' => 'bd810e0f-80cf-4558-9af7-9894d2b85fb0',      //      Offering for Isolated networks with no Source Nat service
        'sharedElasticIP' => '',                                                                //      Offering for Shared networks with Elastic IP and Elastic LB capabilities
        'l2' => '4c1154f1-aaec-4e07-b1a7-a2fdb3a210ca',                 //      Offering for L2 networks
        'extL2' => 'a65d4d9e-2131-43ab-aff3-e495402195f9',
        'qcloud' => '',                                                                 //      Offering for QuickCloud with no services
);

/*--------------- execution --------------------*/

//$result = fGetTemplate ();		// получаем шаблоны
//$result = fGetServiceOffering();	// получаем планы
$result = fCreateVM('test-instance',$offering['1-1-20'], $templates['ubuntu2204'], $zoneID, $netOfferings['extL2']);	// создаём ВМ
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
        echo "login: cloud-user" ."\n";
        echo "Password: " . $jobstatus['jobresult']['virtualmachine']['password'] ."\n";
        echo "IP: " . $jobstatus['jobresult']['virtualmachine']['nic']['0']['ipaddress'] ."\n";
        echo "Network name: " . $jobstatus['jobresult']['virtualmachine']['nic']['0']['networkname'] ."\n";
        echo "Network type: " . $jobstatus['jobresult']['virtualmachine']['nic']['0']['type'] ."\n";
}

print_r($result);

?>
