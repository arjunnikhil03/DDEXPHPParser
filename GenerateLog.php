<?php

error_reporting(E_ALL);
ini_set('display_error',1);

class MovieSonytoTemp extends mysqli{

	private $logStorage = "LOG_STORAGE_PATH";

	private $mysql_host = "MYSQL_HOST";

	private $mysql_user = "MYSQL_USER";

	private $mysql_pass = "MYSQL_PASS";

	private $mysql_db = "DBNAME";

	private $language = array();

	public function MovieSonytoTemp(){
		parent::__construct($this->mysql_host, $this->mysql_user, $this->mysql_pass,$this->mysql_db);

		if (mysqli_connect_error()) {
			die('Connect Error (' . mysqli_connect_errno() . ') '. mysqli_connect_error());
		}

		$this->get_iso_693_2_codes();
	}


	public function get_iso_693_2_codes(){
		$sql = "SELECT * from iso_693_2_codes";
		$result = $this->query($sql);
		while($row = $result->fetch_assoc()){
			$this->language[$row['iso_693_1']] = $row['language_name'];

		}

	}

	public function generateResponse(){
	#echo	
	#$query = "select sony_xmllist.*,sony_albumlist.Grid from sony_xmllist inner join sony_albumlist where sony_xmllist.srno = sony_albumlist.xmlid and total_songs=download_songs and download_images>0 and log_send<>1 and total_songs > 0";
	$query = "select sony_xmllist.*,sony_albumlist.Grid from sony_xmllist inner join sony_albumlist where sony_xmllist.srno = sony_albumlist.xmlid and total_songs=download_songs and log_send<>1 and ((download_images>0 and total_songs > 0) or (sony_xmllist.Process_status=1 and update_indicator='UpdateMessage' and total_songs=0))";

	#$query = "select sony_xmllist.*,sony_albumlist.Grid from sony_xmllist inner join sony_albumlist where sony_xmllist.srno = sony_albumlist.xmlid and total_songs=download_songs and download_images>0 and total_songs > 0";
		if ($result = $this->query($query)) {
			while ($row = $result->fetch_assoc()) {
				//print_r($row);exit;
				$filename = $row['Grid'].".xml";
				$file = $this->logStorage.$filename;
				$myfile = fopen($file, "w") or die("Unable to open file!");
				$xml = '<ns3:FtpAcknowledgementMessage MessageVersionId="1.0" xmlns:ns2="http://www.w3.org/2000/09/xmldsig#" xmlns:ns3="http://ddex.net/xml/ern-c/14">
					<MessageHeader>
					<MessageSender>
					<PartyId>'.$row['MessageRecipientId'].'</PartyId>
					<PartyName>
					<FullName>'.$row['MessageRecipientName'].'</FullName>
					</PartyName>
					</MessageSender>
					<MessageRecipient>
					<PartyId>'.$row['MessageSenderId'].'</PartyId>
					<PartyName>
					<FullName>'.$row['MessageSenderName'].'</FullName>
					</PartyName>
					</MessageRecipient>
					<MessageCreatedDateTime>'.date(DATE_ATOM).'</MessageCreatedDateTime>
					</MessageHeader>
					<AcknowledgedFile>
					<ReleaseId>'.$row['MessageId'].'</ReleaseId>
					<Date>'.date(DATE_ATOM).'</Date>
					</AcknowledgedFile>
					<FileStatus>FileOK</FileStatus>
					</ns3:FtpAcknowledgementMessage>';
				fwrite($myfile, $xml);
				fclose($myfile);
				chmod($file,0755);
				echo "\n".$update = "UPDATE sony_xmllist SET log_send=1 where srno=".$row['srno'];
				#$update = "UPDATE sony_xmllist SET log_send=1 where srno=".$row['srno'];
				if(!($this->query($update))){
					$data = array('query'=>$update,'error'=>$mysqli->error);
					file_put_contents('sony_xmllist.log',print_r($data,true),FILE_APPEND);
				}
			}
		}

	}

}

if (PHP_SAPI === 'cli') {
	$argument1 = $argv[1];
	if($argument1 == "log"){
		$movieTemp = new MovieSonytoTemp();
		$movieTemp->generateResponse();
	}
}
