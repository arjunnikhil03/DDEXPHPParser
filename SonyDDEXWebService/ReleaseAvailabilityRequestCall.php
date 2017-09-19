<?php

/*$aContext = array(
    'http' => array(
        'proxy' => 'http://10.140.65.114:8080', // This needs to be the server and the port of the NTLM Authentication Proxy Server.
        'request_fulluri' => True,
        ),
    );
$cxContext = stream_context_create($aContext);
$xml = file_get_contents(SONY_RARCAPI,false,$cxContext);
print_r($xml);exit;*/

require_once "cUrl.php";
require_once "constant.php";
require_once "SonyXmlParser.php";

$request="<?xml version=\"1.0\" encoding=\"UTF-8\"?>\r\n<echo:ReleaseAvailabilityRequestMessage xmlns:ds=\"http://www.w3.org/2000/09/xmldsig#\"\r\nxmlns:echo=\"http://ddex.net/xml/ern-c/15\" MessageVersionId=\"1.5\">\r\n<MessageHeader>\r\n<WsMessageId>2348b71b-47be-4d51-963a-692526208048</WsMessageId>\r\n<MessageSender>\r\n<PartyId>PADPID2016011901E_TEST</PartyId>\r\n<PartyName>\r\n<FullName>Release Distributor</FullName>\r\n</PartyName>\r\n</MessageSender>\r\n<MessageRecipient>\r\n<PartyId>PADPIDA2007040502I</PartyId>\r\n<PartyName>\r\n<FullName>Release Creator</FullName>\r\n</PartyName>\r\n</MessageRecipient>\r\n<MessageCreatedDateTime>2009-11-20T09:30:47.0Z</MessageCreatedDateTime>\r\n<IsSymmetric>false</IsSymmetric>\r\n<Priority>Normal</Priority>\r\n</MessageHeader>\r\n<DSP>\r\n<PartyId>PADPID2016011901E_TEST</PartyId>\r\n</DSP>\r\n</echo:ReleaseAvailabilityRequestMessage>";

//echo "\n".SONY_RARCAPI;
//echo "\n".SONY_USERNAME;
//echo "\n".SONY_PASSWORD;
$response = cUrl_With_Basic_Authentication(SONY_RARCAPI,array(),SONY_USERNAME,SONY_PASSWORD,$request);
print_r($response);
if($response["HTTP_CODE"]=="200"){
	$xml = $response["response"];
        $obj = new SonyXmlParser();
	$obj->ProcessSonyRawXML($xml);			
}
