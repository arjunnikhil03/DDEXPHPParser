<?php

function cUrl_With_Basic_Authentication($host,$additionalHeaders,$username,$password,$payloadName){
	$process = curl_init($host);
	curl_setopt($process, CURLOPT_HTTPHEADER, array('Content-Type: application/xml', $additionalHeaders));
	//curl_setopt($process, CURLOPT_HEADER, 1);
	curl_setopt($process, CURLOPT_SSL_VERIFYPEER, 0);
	//curl_setopt($process, CURLOPT_HTTPPROXYTUNNEL, 1);
    //curl_setopt($process, CURLOPT_PROXY, "PROXY");
	curl_setopt($process, CURLOPT_USERPWD, $username . ":" . $password);
	curl_setopt($process, CURLOPT_TIMEOUT, 30);
	curl_setopt($process, CURLOPT_POST, 1);
        curl_setopt($process, CURLOPT_HTTPAUTH, CURLAUTH_BASIC); 
	curl_setopt($process, CURLOPT_POSTFIELDS, $payloadName);
	curl_setopt($process, CURLOPT_RETURNTRANSFER, TRUE);
	$return = curl_exec($process);
        $returnCode = (int)curl_getinfo($process, CURLINFO_HTTP_CODE);
	$err = curl_error($curl);
	curl_close($process);
	return array("HTTP_CODE"=>$returnCode,"response"=>$return,"error"=>$err);
}
