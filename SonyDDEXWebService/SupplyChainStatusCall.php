<?php

require_once "cUrl.php";
require_once "constant.php";
require_once "SonyXmlParser.php";

$obj = new SonyXmlParser();

$sql = "SELECT sony_ws_albumlist.* FROM `sony_ws_xmllist` INNER JOIN sony_ws_albumlist ON(sony_ws_xmllist.srno = sony_ws_albumlist.xmlid) WHERE 	sony_ws_xmllist.log_send=0";
$stmt = $obj->prepare($sql);
$stmt->execute();
$result = $stmt->fetchAll ( PDO::FETCH_ASSOC );
foreach($result as $data){
	$request = '<?xml version="1.0" encoding="UTF-8"?>
<echo:SupplyChainStatusMessage xmlns:ds="http://www.w3.org/2000/09/xmldsig#"
xmlns:echo="http://ddex.net/xml/ern-c/15" MessageVersionId="1.5">
<MessageHeader>
<WsMessageId>2348b71b-47be-4d51-963a-692526208048</WsMessageId>
<MessageSender>
<PartyId>PADPID2016011901E_TEST</PartyId>
<PartyName>
<FullName>Release Distributor</FullName>
</PartyName>
</MessageSender>
<MessageRecipient>
<PartyId>PADPIDA2007040502I</PartyId>
<PartyName>
<FullName>Release Creator</FullName>
</PartyName>
</MessageRecipient>
<MessageCreatedDateTime>2009-11-20T09:30:47.0Z</MessageCreatedDateTime>
<IsSymmetric>false</IsSymmetric>
<Priority>Normal</Priority>
</MessageHeader>
<DSP>
<PartyId>PADPID2016011901E_TEST</PartyId>
</DSP>
<ReleaseId>
<GRid>'.$data["Grid"].'</GRid>
</ReleaseId>
<Status>SuccessfullyIngestedByReleaseDistributor</Status>
</echo:SupplyChainStatusMessage>';

$response = cUrl_With_Basic_Authentication(SONY_SCSCAPI,array(),SONY_USERNAME,SONY_PASSWORD,$request);
print_r($response);
if($response["HTTP_CODE"]=="200"){
	$update = "UPDATE sony_ws_xmllist SET log_send=1 WHERE srno=:srno";
	$update_data = array(":srno"=>$data["xmlid"]);
	$ustmt = $obj->prepare($update);
	$ustmt->execute($update_data);	

}
}
?>
