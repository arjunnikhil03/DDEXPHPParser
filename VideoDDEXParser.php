<?php

error_reporting(E_ALL);
ini_set('display_error',1);
class SonyXmlParser extends mysqli{

	private $dirLocation = "/oldstorage/publisher/sonymusic/musicvideos/";

	private $destLocation = "/jiomediadata/kaltura/vod/VOD_Content/sony_xml/";

	private $mysql_host = "10.130.22.11";

	private $mysql_user = "root";

	private $mysql_pass = "rcp@idc980";

	private $mysql_db = "jiovod_sony";

	private $output  = array();

	public function SonyXmlParser(){
		parent::__construct($this->mysql_host, $this->mysql_user, $this->mysql_pass,$this->mysql_db);

		if (mysqli_connect_error()) {
			die('Connect Error (' . mysqli_connect_errno() . ') '. mysqli_connect_error());
		}
	}

	public function ListXmlFilesRecursively(){
		$ite=new RecursiveDirectoryIterator($this->dirLocation,FilesystemIterator::SKIP_DOTS);
 		$exclude = array('logs');
                $filter = function ($file, $key, $iterator) use ($exclude) {
                    if ($iterator->hasChildren() && !in_array($file->getFilename(), $exclude)) {
                        return true;
                    }
                    return $file->isFile();
                };
                $filter =  new RecursiveCallbackFilterIterator($ite, $filter);
                $Iterator = new RecursiveIteratorIterator($filter);

		//$Directory = new RecursiveDirectoryIterator($this->dirLocation,FilesystemIterator::SKIP_DOTS);
		//$Iterator = new RecursiveIteratorIterator($Directory);
		$RegexIterator = new RegexIterator($Iterator, '/^.+\.xml$/i', RecursiveRegexIterator::GET_MATCH);
		$xmlFiles =array();
		foreach($RegexIterator as $name => $object){
			$dir = $this->destLocation."/".date('Ymd');
			if( is_dir($dir) === false ) { mkdir($dir); }
			$info = pathinfo($name);
			$newfile = $dir."/".$info['basename'];
			if(copy($name, $newfile)){
				$insert = "INSERT INTO sony_xmllist(Filename,XML_status,Process_date,directory) VALUE('".$info['basename']."',1,now(),'".dirname($name)."')";
				$this->query($insert);
				unlink($name);
			}
			//unlink($name);
			$xmlFiles[] = $name;
		}

		return $xmlFiles;
	}

	public function ProcessSonyRawXML(){
		// WHERE Process_status is null"
		$query = "SELECT srno,Filename,Process_date  FROM sony_xmllist where XML_status=1 and Process_status is null";
		//$query = "SELECT srno,Filename,Process_date  FROM sony_xmllist"; //-- For Testing
		if ($result = $this->query($query)) {
			while ($row = $result->fetch_assoc()) {
				$foldername = date('Ymd',strtotime($row['Process_date']));
				$xml = $this->destLocation.$foldername."/".$row['Filename'];
				if(is_file($xml)){
					$oXml = new XMLReader();
					$oXml->open($xml);
					echo $xml2assoc['Filename'] = $row['Filename'];
					$xml2assoc['srno'] = $row['srno'];
					$xml2assoc['Process_date'] = $row['Process_date'];
					$xml2assoc['xml_data'] = $this->xml2assoc($oXml);
					//print_r( $xml2assoc['xml_data']);exit;
					if(!empty($xml2assoc['xml_data'][0]['val'])){
						foreach($xml2assoc['xml_data'][0]['val'] as  $value){
								
							switch($value['name']){
							       
								case "MessageHeader":
									$MessageHeaderData = $value['val'];
								$this->InsertMessageHeader($MessageHeaderData,$xml2assoc['srno']);
								break;
								case "ResourceList":
									$ResourceListData = $value['val'];
								$this->InsertResourceList($ResourceListData,$xml2assoc['srno'],$row['directory']);
								break;
								case "ReleaseList":
									$ReleaseListData = $value['val'];
								$this->InsertReleaseList($ReleaseListData,$xml2assoc['srno']);	    
								break;
								case "DealList":
									$DealListData = $value['val'];
								$this->InsertDealList($DealListData,$MessageHeaderData,$ResourceListData,$ReleaseListData,$xml2assoc['srno']);
								break;
								case "UpdateIndicator":
								$UpdateIndicator = $value['val'];
								break;

							}
						}
					}

					$oXml->close();
					$update ="update sony_xmllist set Process_status=1,update_indicator='$UpdateIndicator' where srno='".$row['srno']."'";
					$this->query($update);
				}
			}
			$result->free();
		}
	}
	public function InsertDealList($DealListData,$MessageHeaderData,$ResourceListData,$ReleaseListData,$srno){
		//return true;
		$party_id = !empty($MessageHeaderData[2]['val'][0]['val'])? $this->real_escape_string($MessageHeaderData[2]['val'][0]['val']):null;
		if(!$party_id){
			return false;
		}
		$sonyDeal = array();
		foreach($DealListData as $key=>$Deal){
			//			print_r($Deal);
			foreach($Deal['val'] as $innerDeal){
				if($innerDeal['name'] == "DealReleaseReference"){
					$sonyDeal[$key]["DealReleaseReference"][] = $innerDeal['val'];
				}
				//	if($innerDeal['name'] == 'Deal'){
				//		foreach($innerDeal['val'][0]['val'] as $DealTerms){
				//			 $sonyDeal[$key][$DealTerms['name']] = $DealTerms['val'];
				//		}
				//	}
				if($innerDeal['name'] == 'Deal'){
					foreach($innerDeal['val'][0]['val'] as $DealTerms){

						//			print_r($DealTerms);						
						$temp_name = $DealTerms['name'];
						//				echo "name is $temp_name \n";
						//	$modelarray = $DealTerms[0];
						if($temp_name == 'Usage')
						{
							$usagearray = array();
							foreach($DealTerms['val'] as $usage  )
							{
								echo "hi \n";
								$usagearray[] = $usage['val'] ;
								//print_r($usagearray);
							}
							//print_r($usagearray);
							$usagetype = implode(' / ',$usagearray);


							//	$usagearray[] = $DealTerms['val'][0];
						}
						if($temp_name == 'CommercialModelType')
						{
							$commercial = $DealTerms['val'];
						}
						if($temp_name == 'TerritoryCode')
						{
							$territory = $DealTerms['val'];
						}
						if($temp_name == 'ValidityPeriod')
						{
							$validaityarray = $DealTerms['val'];
						}
						if($temp_name == 'TakeDown')
						{
							$takedown = $DealTerms['val'];
						}

						//						$usagetype = implode(' / ',$usagearray);

						$startdate =  $validaityarray[0]['val'];
						$data['TerritoryCode']= $territory ;
						$data['Usage'] = $usagetype;
						$data['StartDate'] = $startdate;
						$data['TakeDown'] = $takedown;


						$final_deal[$commercial] = $data;


						//	$territoryarray = $DealTerms[2];
						//	$validityarray = $DealTerms[3];
						//						print_r($usagearray);
						//$sonyDeal[$key][$DealTerms['name']] = $DealTerms['val'];
						$sonyDeal[$key]['Deal'] = $final_deal;
					}
				}


				if($innerDeal['name'] == 'EffectiveDate'){
					$sonyDeal[$key]['EffectiveDate'] = $innerDeal['val'];
				}

			}
		}
		//		print_r($sonyDeal);
		/*foreach($ResourceListData as $Video){
		  if($Video['name'] == "Video"){
		  foreach($Video['val'] as $val){
		  }
		  }
		  }*/

		$Release = array();	
		foreach($ReleaseListData as $key=>$RData){
			foreach($RData['val'] as $ReleaseVal){
				if(is_array($ReleaseVal['val'])){
					foreach($ReleaseVal['val'] as $RData2){
						$Release[$key][$RData2['name']] = $RData2['val'];
					}
				}else{
					$Release[$key][$ReleaseVal['name']] = $ReleaseVal['val'];
				}
			}
		}

		foreach($Release as $insert_data){
			//print_r($insert_data);
			//exit;
			$catalog_number = $this->real_escape_string($Release[0]['CatalogNumber']);
			$ipcn = $this->real_escape_string($Release[0]['ICPN']);
			$grid = $this->real_escape_string($insert_data['GRid']);
			$isrc = $this->real_escape_string($insert_data['ISRC']);
			$artist = $this->real_escape_string($insert_data['DisplayArtistName']);
			$title = $this->real_escape_string($insert_data['TitleText']);
			$release_type = $this->real_escape_string($insert_data['ReleaseType']);

			$commercial_type = "";
			$use_type ="";
			$territories ="";
			$price_value_code="";
			$sales_start_date="";
			$sales_end_date="";
			foreach($sonyDeal as $deal){
				if(in_array($insert_data['ReleaseReference'],$deal["DealReleaseReference"])){


					$deal_type = $deal['Deal'];

					foreach($deal_type as $key => $deal_basedupon_commercialmodel)
					{
						//		if($catalog_number=='G010000244373P')
						//		{

						//			echo "key is $key \n";
						//		print_r($deal_basedupon_commercialmodel);
						//		}
						//	echo "$key \n";
						$commercial_type = $key;
						$use_type = $deal_basedupon_commercialmodel['Usage'];
						$territories = $deal_basedupon_commercialmodel['TerritoryCode'];
						$takedown = !empty($deal_basedupon_commercialmodel['TakeDown'])?$deal_basedupon_commercialmodel['TakeDown']:false;
						$sales_start_date =$deal_basedupon_commercialmodel['StartDate'];
						if($takedown)
						{

							$sales_end_date = $sales_start_date;
						}

						$xmlid = $srno;
						$inserted_date = date('Y-m-d H:i:s');

						//if(trim($commercial_type)=='')
						if($takedown == 'true')
						{
							//							echo "take down is $takedown\n";

							$update = "update sony_xml_output_1 set party_id='$party_id',catalog_number='$catalog_number',grid='$grid',artist='$artist',title='$title',release_type='$release_type',commercial_type='',use_type='',territories='',price_value_code='$price_value_code',sales_start_date='',sales_end_date='$sales_end_date',updated_date='$inserted_date',xmlid='$xmlid' where isrc='$isrc'  and  catalog_number='$catalog_number'";
							//							echo "$update \n";
							$this->query($update);
							//$count = 0;
							//$need_to_insert = false;

						}
						else
						{
							$select = "select isrc  from sony_xml_output_1 where isrc = '$isrc' and catalog_number='$catalog_number' and commercial_type='$commercial_type'";
							$result  = $this->query($select);
							$count = $result->num_rows;
							//	}
							if($count > 0  )
							{
								$update = "update sony_xml_output_1 set party_id='$party_id',catalog_number='$catalog_number',grid='$grid',artist='$artist',title='$title',release_type='$release_type',commercial_type='$commercial_type',use_type='$use_type',territories='$territories',price_value_code='$price_value_code',sales_start_date='$sales_start_date',sales_end_date='$sales_end_date',updated_date='$inserted_date',xmlid='$xmlid' where isrc='$isrc' and  catalog_number='$catalog_number' and commercial_type='$commercial_type'";
								$this->query($update);


							}
							else
							{
								echo "insert ,takedown is  $takedown \n";
								$insert = "INSERT INTO sony_xml_output_1(party_id,catalog_number,ipcn,grid,isrc,artist,title,release_type,commercial_type,use_type,territories,price_value_code,sales_start_date,sales_end_date,xmlid,inserted_date)
									values('$party_id','$catalog_number','$ipcn','$grid','$isrc','$artist','$title','$release_type','$commercial_type','$use_type','$territories','$price_value_code','$sales_start_date','$sales_end_date','$xmlid','$inserted_date')";
								$this->query($insert);


							}
					}


				}

			}
		}
		//	$xmlid = $srno;
		//	$inserted_date = date('Y-m-d H:i:s');
		//	$select = "select isrc  from sony_xml_output_1 where isrc = '$isrc' and catalog_number='$catalog_number'";
		//	$result  = $this->query($select);
		//	$count = $result->num_rows;	
		//	if($count > 0)
		//	{
		//		$update = "update sony_xml_output_1 set party_id='$party_id',catalog_number='$catalog_number',grid='$grid',artist='$artist',title='$title',release_type='$release_type',commercial_type='$commercial_type',use_type='$use_type',territories='$territories',price_value_code='$price_value_code',sales_start_date='$sales_start_date',sales_end_date='$sales_end_date',updated_date='$inserted_date' where isrc='$isrc' and ipcn='$ipcn'";
		//	//this->query($update);
		//	}
		//		else
		//		{
		//		$insert = "INSERT INTO sony_xml_output_1(party_id,catalog_number,ipcn,grid,isrc,artist,title,release_type,commercial_type,use_type,territories,price_value_code,sales_start_date,sales_end_date,xmlid,inserted_date)
		//		values('$party_id','$catalog_number','$ipcn','$grid','$isrc','$artist','$title','$release_type','$commercial_type','$use_type','$territories','$price_value_code','$sales_start_date','$sales_end_date','$xmlid','$inserted_date')";
		//	$this->query($insert);
		//		}
	}	
}















public function InsertReleaseList($ReleaseListData,$srno){
	//return true;
	$temp_count = count($ReleaseListData);
	echo "count is $temp_count \n";
	//exit;
	//var_dump($ReleaseListData);
	//exit;
	$AlbumDetails= array();
	$temp = $ReleaseListData[0]['val'] ;
	$temp1 =  $temp[4];
	$releasetype = !empty( $temp1['val'])? $this->real_escape_string($temp1['val']):null; ;
	//var_dump($temp1);
	//exit;
	foreach($ReleaseListData[0]['val'] as $Release){
		if(is_array($Release['val'])){
			foreach($Release['val'] as $arr){
				if($arr['name']=='CatalogNumber'){
					$AlbumDetails['DPID'] = str_replace("DPID:","",$arr['atr']['Namespace']);
				}
				$AlbumDetails[$arr['name']] = $arr['val'];	
			}
		}else{
			$AlbumDetails[$Release['name']] = $Release['val'];
		}
	}
	$Albumname = !empty($AlbumDetails['TitleText'])? $this->real_escape_string($AlbumDetails['TitleText']):null;
	$Grid = !empty($AlbumDetails['GRid'])? $this->real_escape_string($AlbumDetails['GRid']):null;
	$ICPN = !empty($AlbumDetails['ICPN'])? $this->real_escape_string($AlbumDetails['ICPN']):null;
	$DPID = !empty($AlbumDetails['DPID'])? $this->real_escape_string($AlbumDetails['DPID']):null;
	$IPID = !empty($AlbumDetails['ProprietaryId'])? $this->real_escape_string(str_replace("IPID:","",$AlbumDetails['ProprietaryId'])):null;
	$label = !empty($AlbumDetails['LabelName'])? $this->real_escape_string($AlbumDetails['LabelName']):null;
	$OriginalReleaseDate = !empty($AlbumDetails['OriginalReleaseDate'])? $this->real_escape_string($AlbumDetails['OriginalReleaseDate']):null;
	$Year = !empty($AlbumDetails['Year'])? $this->real_escape_string($AlbumDetails['Year']):null;
	$Genre = !empty($AlbumDetails['Genre'][0]['val'])? $this->real_escape_string($AlbumDetails['Genre'][0]['val']):null;
	//		$release_type = !empty($AlbumDetails[''][0]['val'])? $this->real_escape_string($AlbumDetails['Genre'][0]['val']):null;
	$catalog_number = !empty($AlbumDetails['catalognumber'][0]['val'])? $this->real_escape_string($AlbumDetails['Genre'][0]['val']):null;

	$select = "select ICPN  from sony_albumlist  where ICPN = '$ICPN' and catalog_number='$catalog_number'";
	//echo "select ICPN  from sony_albumlist  where ICPN = '$ICPN' and catalog_number='$catalog_number' \n";
	$result  = $this->query($select);
	$count = $result->num_rows;
	//echo    "echo count is  $count \n";
	if($count > 0)
	{
		$update = "update sony_albumlist set Albumname='$Albumname',Grid='$Grid',DPID='$DPID',IPID='$IPID',xmlid='$srno',label='$label',release_date='$OriginalReleaseDate',movie_release_date='$OriginalReleaseDate',year='$Year',genre='$Genre',release_type='$releasetype' where catalog_number='$catalog_number' and ICPN = '$ICPN'";
		//			echo "$update \n";
		if($this->query($update)){
			$data = array('query'=>$update,'error'=>$mysqli->error,"line_number"=>__LINE__);
			file_put_contents('sony_xmllist.log',print_r($data,true),FILE_APPEND);
		}

	}
	else
	{

		$insert = "INSERT INTO sony_albumlist(Albumname,Grid,ICPN,DPID,IPID,xmlid,label,release_date,movie_release_date,year,genre,release_type,catalog_number) VALUES('$Albumname','$Grid','$ICPN','$DPID','$IPID','$srno','$label','$OriginalReleaseDate','$OriginalReleaseDate','$Year','$Genre','$releasetype','$catalog_number');";
		if($this->query($insert)){
			$data = array('query'=>$insert,'error'=>$mysqli->error,"line_number"=>__LINE__);
			file_put_contents('sony_xmllist.log',print_r($data,true),FILE_APPEND);
		}
	}
}

public function InsertMessageHeader($MessageHeaderData,$srno){
	//return true;
	//print_r($MessageHeaderData);exit;
	$MessageThreadId = !empty($MessageHeaderData[0]['val'])? $this->real_escape_string($MessageHeaderData[0]['val']):null;
	$MessageId = !empty($MessageHeaderData[1]['val'])? $this->real_escape_string($MessageHeaderData[1]['val']):null;
	$MessageSenderId = !empty($MessageHeaderData[2]['val'][0]['val'])? $this->real_escape_string($MessageHeaderData[2]['val'][0]['val']):null;
	$MessageSenderName = !empty($MessageHeaderData[2]['val'][1]['val'][0]['val'])? $this->real_escape_string($MessageHeaderData[2]['val'][1]['val'][0]['val']):null;
	$MessageRecipientId = !empty($MessageHeaderData[3]['val'][0]['val'])? $this->real_escape_string($MessageHeaderData[3]['val'][0]['val']):null;
	$MessageRecipientName = !empty($MessageHeaderData[3]['val'][1]['val'][0]['val'])? $this->real_escape_string($MessageHeaderData[3]['val'][1]['val'][0]['val']):null;
	$MessageCreatedDateTime = !empty($MessageHeaderData[4]['val'])? $this->real_escape_string($MessageHeaderData[4]['val']):null;

	$update = "UPDATE  sony_xmllist SET MessageThreadId='".$MessageThreadId."',MessageId='".$MessageId."',MessageSenderId='".$MessageSenderId."',MessageSenderName='".$MessageSenderName."',MessageRecipientId='".$MessageRecipientId."',MessageRecipientName='".$MessageRecipientName."',MessageCreatedDateTime='".$MessageCreatedDateTime."' WHERE srno='".$srno."'";
	if($this->query($update)){
		$data = array('query'=>$update,'error'=>$this->error);
		file_put_contents('sony_xmllist.log',print_r($data,true),FILE_APPEND);
	}
}

public function InsertResourceList($ResourceListData,$srno,$dir){
	//return true;
	//print_r($ResourceListData);exit;
	$VideoType = null;
	$ISRC = null;
	$ResourceReference = null;
	$ReferenceTitle = null;
	$LanguageOfPerformance = null;
	$Duration = null;
	$Title = null;
	$Label = null;
	$ParentalWarningType = null;
	$Genre = null;
	$Year = null;
	$plinetext =null;

	$i=0;

	foreach($ResourceListData as $Video){
		if($Video['name'] == "Video"){
			foreach($Video['val'] as $val){
				switch($val['name']){
					case 'VideoType':
						$VideoType = $this->real_escape_string($val['val']); 
						break;
					case 'VideoId':
						$ISRC = !empty($val['val'][0]['val'])? $this->real_escape_string($val['val'][0]['val']):null;
						break;
					case 'ResourceReference':
						$ResourceReference = $this->real_escape_string($val['val']);
						break;
					case 'ReferenceTitle':
						$ReferenceTitle = !empty($val['val'][0]['val'])? $this->real_escape_string($val['val'][0]['val']):null;
						break;
					case 'LanguageOfPerformance':
						$LanguageOfPerformance = $this->real_escape_string($val['val']);
						break;
					case 'Duration':
						$Duration = $this->real_escape_string($val['val']);
						break;
					case 'VideoDetailsByTerritory':
						foreach($val['val'] as $VideoDetails){

							switch($VideoDetails['name']){
								case "Title":
									$Title = !empty($VideoDetails['val'][0]['val'])? $this->real_escape_string($VideoDetails['val'][0]['val']):null;
								break;
								case "DisplayArtist":
									foreach($VideoDetails['val'] as $arrDA){
										if($arrDA['name'] == 'PartyName'){
											$DisplayArtist[$VideoDetails['atr']['SequenceNumber']]['PartyName'] = $arrDA['val'][0]['val'];
										}
										if($arrDA['name'] == 'ArtistRole'){
											$DisplayArtist[$VideoDetails['atr']['SequenceNumber']]['ArtistRole'][] = $arrDA['val'];
										}
									}
								/*foreach($VideoDetails['val'] as $arrDA){
								  if($arrDA['name'] == "PartyName"){
								  foreach($arrDA['val'] as $PartyName){
								  $DisplayArtist[] = $PartyName["val"];
								  }
								  }
								  }*/
								/*if(!empty($VideoDetails['val'][0]['val'][0]['val'])){
								  $DisplayArtist[] = $VideoDetails['val'][0]['val'][0]['val'];
								  }*/

								break;
								case "ResourceContributor":
									foreach($VideoDetails['val'] as $arrRC){
										if($arrRC['name'] == 'PartyName'){
											$ResourceContributor[$VideoDetails['atr']['SequenceNumber']]['PartyName'] = $arrRC['val'][0]['val'];
										}
										if($arrRC['name'] == 'ResourceContributorRole'){
											$ResourceContributor[$VideoDetails['atr']['SequenceNumber']]['ResourceContributorRole'][] = !empty($arrRC['atr']['UserDefinedValue'])?$arrRC['atr']['UserDefinedValue']:$arrRC['val'];
										}
									}
								/*if(!empty($VideoDetails['val'][0]['val'][0]['val'])){
								  $UserDefinedValue = !empty($VideoDetails['val'][1]['atr']['UserDefinedValue'])? $this->real_escape_string($VideoDetails['val'][1]['atr']['UserDefinedValue']): $VideoDetails['val'][1]['val'];
								  $ResourceContributor[$UserDefinedValue][] = $VideoDetails['val'][0]['val'][0]['val'];
								  }*/
								break;
								case "LabelName":
									$Label = $this->real_escape_string($VideoDetails['val']);
								break;
								case "ParentalWarningType":
									$ParentalWarningType = $this->real_escape_string($VideoDetails['val']);
								break;
								case "Genre":
									$Genre = $this->real_escape_string($VideoDetails['val'][0]['val']);
								break;
								case "PLine":
									$Year = $this->real_escape_string($VideoDetails['val'][0]['val']);
								$plinetext = $this->real_escape_string($VideoDetails['val'][1]['val']);
								break;
								case "TechnicalVideoDetails":
									foreach($VideoDetails['val'] as $techDetails){
										$TechnicalVideoDetails[$ISRC][$techDetails['name']] = $techDetails['val'];	
									}
								$i++;
								break;


							}
						}

						break;
				}
			}

			//$DisplayArtist = array_unique($DisplayArtist);
			//$Artist = !empty($DisplayArtist) ? $this->real_escape_string(implode(',', $DisplayArtist)) : null;
			unset($ArtistRole);
			$ArtistRole = array();

			foreach($DisplayArtist as $resArt){
				$DisPartyName = $resArt['PartyName'];
				foreach($resArt['ArtistRole'] as $resRole){
					$ArtistRole[$resRole][] = $DisPartyName;
				}
			}

			$arrArtist = array();
			if(!empty($ArtistRole['AssociatedPerformer']) || !empty($ArtistRole['MainArtist']) || !empty($ArtistRole['FeaturedArtist'])){
				$arrArtist = array_merge(   (array) $ArtistRole['AssociatedPerformer'],  (array) $ArtistRole['MainArtist'],  (array) $ArtistRole['FeaturedArtist']);
			}

			$Artist = "";
			if(!empty($arrArtist)){
				$arrArtist = array_unique($arrArtist);
				$Artist .= !empty($arrArtist) ? $this->real_escape_string(implode(',', $arrArtist)) : null;
			}


			foreach($ResourceContributor as $ResValue){
				$ResPartyName = $ResValue['PartyName'];
				foreach($ResValue['ResourceContributorRole'] as $resRole){
					$ArtistRole[$resRole][] = $ResPartyName;
				}
			}
			//print_r($ArtistRole);exit;
			$ArtistRole['Producer'] = array_unique($ArtistRole['Producer']);
			$ArtistRole['Composer'] = array_unique($ArtistRole['Composer']);
			$ArtistRole['Lyricist'] = array_unique($ArtistRole['Lyricist']);
			$ArtistRole['Actor'] = array_unique($ArtistRole['Actor']);

			$Actor = "";
			if(!empty($ArtistRole['Actor'])){
				$Actor .= !empty($ArtistRole['Actor']) ? $this->real_escape_string(implode(',', $ArtistRole['Actor'])) : null;
			}

			$Composer = "";
			if(!empty($ArtistRole['Composer'])){
				$Composer .= !empty($ArtistRole['Composer']) ? $this->real_escape_string(implode(',', $ArtistRole['Composer'])) : null;
			}

			$Lyricist = "";
			if(!empty($ArtistRole['Lyricist'])){
				$Lyricist .= !empty($ArtistRole['Lyricist']) ? $this->real_escape_string(implode(',', $ArtistRole['Lyricist'])) : null;
			}

			$Producer = "";
			if(!empty($ArtistRole['Lyricist'])){
				$Producer .= !empty($ArtistRole['Producer']) ? $this->real_escape_string(implode(',', $ArtistRole['Producer'])) : null;
			}

			$NumberOfChannels = !empty($TechnicalVideoDetails[$ISRC]['NumberOfChannels'])? $this->real_escape_string($TechnicalVideoDetails[$ISRC]['NumberOfChannels']):null;
			$SamplingRate  = !empty($TechnicalVideoDetails[$ISRC]['SamplingRate'])? $this->real_escape_string($TechnicalVideoDetails[$ISRC]['SamplingRate']):null;
			$IsPreview  = !empty($TechnicalVideoDetails[$ISRC]['IsPreview'])? $this->real_escape_string($TechnicalVideoDetails[$ISRC]['IsPreview']):null;
			$FileURL  = !empty($TechnicalVideoDetails[$ISRC]['File'][0]['val'])? $this->real_escape_string($TechnicalVideoDetails[$ISRC]['File'][0]['val']):null;
			$HashSum  = !empty($TechnicalVideoDetails[$ISRC]['File'][1]['val'][0]['val'])? $this->real_escape_string($TechnicalVideoDetails[$ISRC]['File'][1]['val'][0]['val']):null;
			$AudioCodecType  =!empty($TechnicalVideoDetails[$ISRC]['AudioCodecType'])? $this->real_escape_string($TechnicalVideoDetails[$ISRC]['AudioCodecType']):null;

			$FileURL  = $dir."/".$FilePath.$FileURL; 

			$Process_status =0;
			$xmlid = $srno;
			$select = "select  ISRC  from sony_songlist where ISRC = '$ISRC' ";
			$result  = $this->query($select);
			$count = $result->num_rows;
			echo    "echo count is  $count \n";
			$count = 0;
			if($count > 0)
			{
				$update = "update sony_songlist set ResourceReference='$ResourceReference',ReferenceTitle='$ReferenceTitle',LanguageOfPerformance='$LanguageOfPerformance',Duration='$Duration',Title='$Title',Artist='$Artist',Producer='$Producer',Composer='$Composer',Lyricist='$Lyricist',Label='$Label',Year='$Year',plinetext='$plinetext',Genre='$Genre',ParentalWarningType='$ParentalWarningType',AudioCodecType='$AudioCodecType',NumberOfChannels='$NumberOfChannels',SamplingRate='$SamplingRate',IsPreview='$IsPreview',xmlid='$xmlid',FileURL='$FileURL',HashSum='$HashSum',Actor='$Actor' where ISRC='$ISRC' ";
				echo "$update \n";
				$this->query($update);
			}else{

				if(!empty($FileURL) && !empty($ISRC)){
					$insert ="INSERT INTO sony_songlist(ISRC,ResourceReference,ReferenceTitle,LanguageOfPerformance,Duration,Title,Artist,Producer,Composer,Lyricist,Label,Year,plinetext,Genre,ParentalWarningType,AudioCodecType,NumberOfChannels,SamplingRate,IsPreview,FileURL,HashSum,Process_status,xmlid,Actor ) values ('$ISRC','$ResourceReference','$ReferenceTitle','$LanguageOfPerformance','$Duration','$Title','$Artist','$Producer','$Composer','$Lyricist','$Label','$Year','$plinetext','$Genre','$ParentalWarningType','$AudioCodecType','$NumberOfChannels','$SamplingRate','$IsPreview','$FileURL','$HashSum','$Process_status','$xmlid','$Actor' )";
					if (!$this->query($insert)){
						printf("Errormessage: %s\n", $this->error);
						$data = array('query'=>$insert,'error'=>$this->error,"line_number"=>__LINE__);
						file_put_contents('sony_xmllist.log',print_r($data,true),FILE_APPEND);
					}
				}
			}
		}
		if($Video['name'] == "Image"){
			foreach($Video['val'] as $img){
				switch($img["name"]){
					case "ImageType":
						$imgarr["ImageType"] = $img['val'];
					break;
					case "ImageId":
						$imgarr["ImageId"] = $img['val'][0]['val'];
					break;
					case "ImageDetailsByTerritory":
						foreach($img['val'] as $ImageDetailsByTerritory){
							switch($ImageDetailsByTerritory["name"]){
								case "TerritoryCode":
									$imgarr["TerritoryCode"] = $ImageDetailsByTerritory["val"];
								break;
								case "TechnicalImageDetails":
									foreach($ImageDetailsByTerritory["val"] as $TechnicalImageDetails){
										$imgarr[$TechnicalImageDetails["name"]] = $TechnicalImageDetails["val"];			
									}

								break;
							}
						}
					break;
				}
			}

	
			if(!empty($imgarr['File'][0]['val'])){

				$ImageURL  = $dir."/".$imgarr['File'][1]['val'].$imgarr['File'][0]['val'];
				$update ="UPDATE sony_songlist SET ImageURL = '".$ImageURL."',ImageHashSum='".$imgarr['File'][1]['val'][0]['val']."',ImageCodecType='".$imgarr['ImageCodecType']."',ImageHeight='".$imgarr['ImageHeight']."',ImageWidth='".$imgarr['ImageWidth']."',ImageResolution='".$imgarr['ImageResolution']."' where xmlid='".$xmlid."'";
				if (!$this->query($update)){
					printf("Errormessage: %s\n", $mysqli->error);
					$data = array('query'=>$update,'error'=>$mysqli->error,"line_number"=>__LINE__);
					file_put_contents('sony_xmllist.log',print_r($data,true),FILE_APPEND);
				}

			}
		}

	}
}

public function xml2assoc(&$xml){ 
	$assoc = NULL; 
	$n = 0; 
	while($xml->read()){ 
		if($xml->nodeType == XMLReader::END_ELEMENT) break; 
		if($xml->nodeType == XMLReader::ELEMENT and !$xml->isEmptyElement){ 
			$assoc[$n]['name'] = $xml->name; 
			if($xml->hasAttributes) while($xml->moveToNextAttribute()) $assoc[$n]['atr'][$xml->name] = $xml->value; 
			$assoc[$n]['val'] = $this->xml2assoc($xml); 
			$n++; 
		} 
		else if($xml->isEmptyElement){ 
			$assoc[$n]['name'] = $xml->name; 
			if($xml->hasAttributes) while($xml->moveToNextAttribute()) $assoc[$n]['atr'][$xml->name] = $xml->value; 
			$assoc[$n]['val'] = ""; 
			$n++;                
		} 
		else if($xml->nodeType == XMLReader::TEXT) $assoc = $xml->value; 
	} 
	return $assoc; 
}


public function OutputXml($complete_xml){
	echo "<pre>";print_r($complete_xml);exit;	
}

public function __destruct(){
	$this->close();
} 
}

$xmlParser = new SonyXmlParser();
$xmlFiles = $xmlParser->ListXmlFilesRecursively();
$xmlParser->ProcessSonyRawXML();
