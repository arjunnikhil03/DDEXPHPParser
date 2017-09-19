<?php

require_once "constant.php";

class SonyXmlParser extends PDO{

	private $mysql_host = "kdb.ril.com";

	private $mysql_user = "kaltura";

	private $mysql_pass = "kaltura123";

	private $mysql_db = "jiobeats_sonymusic";

	private $output  = array();

	private $ResourceList = "";

	private $ReleaseList = "";

	private $DealList = "";

	private $MessageSenderPartyId = "";

	public function SonyXmlParser(){
		try {
			$dns = "mysql:host=$this->mysql_host;port=3306;dbname=$this->mysql_db";
			parent::__construct($dns, $this->mysql_user, $this->mysql_pass);
		}catch (PDOException $e) {
			echo 'Connection failed: ' . $e->getMessage();exit;
		}
	}

	public function ProcessSonyRawXML($xml){
		//$xml = file_get_contents("ReleaseAvailabilityCall2.xml");
		$objResponse = new SimpleXMLIterator($xml);
		$xmlid = 0;
		if($objResponse->ReleaseAvailabilityStatus == AVAILABLEFORDSP){
			$ns = $objResponse->getNamespaces(true);
			foreach($ns as $key=>$namespace){
				$child = $objResponse->children($namespace);
				foreach($child->children() as $key=>$value){
					switch($key){
						case "MessageHeader":
							$xmlid = $this->ProcessMessageHeader($value);
							$this->MessageSenderPartyId = $value->MessageSender->PartyId;
							break;
						case "ResourceList":
							$this->ResourceList = $value;;
							break;
						case "ReleaseList":
							$this->ReleaseList = $value;
							break;
						case "DealList":
							$this->DealList = $value;
							break;
						case "UpdateIndicator":
							$this->UpdateIndicator= $value;
							break;
					}
				}
			}

		}
		if(!empty($this->ReleaseList) && !empty($this->ResourceList) && !empty($xmlid) && !empty($this->DealList) && !empty($this->MessageSenderPartyId)){
			$this->ProcessResourceList($this->ReleaseList,$this->ResourceList,$xmlid,$this->DealList);
			$update = "UPDATE sony_ws_xmllist SET update_indicator=:update_indicator WHERE srno=:xmlid";
			$stmt = $this->prepare($update);
			$stmt->execute(array(":update_indicator"=>$this->UpdateIndicator,":xmlid"=>$xmlid));
			
		}

	}

	public function ProcessResourceList($ReleaseList,$ResourceList,$xmlid,$DealList){
		$Release = $this->ProcessReleaseList($ReleaseList);
		$Deal = $this->ProcessDealList($ReleaseList,$DealList,$xmlid);
		$SoundRecording = $this->ProcessSoundRecording($ResourceList,$xmlid);
		$Grid = "";
		$ICPN = "";
		$CatalogNumber= "";		
		$TitleText ="";	
		foreach($Release as $r){
			$Grid = $r["Grid"];
			$ICPN = $r["ICPN"];
			$CatalogNumber = $r["CatalogNumber"];
			$Albumname = $r["TitleText"];
			$insert = "INSERT INTO sony_ws_albumlist(Albumname,Grid,ICPN,xmlid,release_date,year,movie_release_date,genre,release_type,catalog_number) VALUES(:Albumname,:Grid,:ICPN,:xmlid,:release_date,:year,:movie_release_date,:genre,:release_type,:catalog_number)";
			$insert_data = array(":Albumname"=>$Albumname,
					":Grid"=>$Grid,
					":ICPN"=>$ICPN,
					":xmlid"=>$xmlid,
					":release_date"=>$r["GlobalOriginalReleaseDate"],
					":year"=>$r["Year"],
					":movie_release_date"=>$r["GlobalOriginalReleaseDate"],
					":genre"=>$r["Genre"],
					":release_type"=>$r["ReleaseType"],
					":catalog_number"=>$r["CatalogNumber"]
					);
			foreach($r["ReleaseResourceReference"] as $ReleaseResourceReference){
				if(!empty($SoundRecording[$ReleaseResourceReference])){
					$song_insert = "INSERT INTO sony_ws_songlist(ISRC,ResourceReference,ReferenceTitle,LanguageOfPerformance,Duration,Title,Artist,Producer,Composer,Lyricist,Label,Year,plinetext,Genre,ParentalWarningType,AudioCodecType,NumberOfChannels,SamplingRate,IsPreview,FileURL,HashSum,Grid,ICPN,xmlid,ImageURL,ImageHashSum,Actor) VALUES(:ISRC,:ResourceReference,:ReferenceTitle,:LanguageOfPerformance,:Duration,:Title,:Artist,:Producer,:Composer,:Lyricist,:Label,:Year,:plinetext,:Genre,:ParentalWarningType,:AudioCodecType,:NumberOfChannels,:SamplingRate,:IsPreview,:FileURL,:HashSum,:Grid,:ICPN,:xmlid,:ImageURL,:ImageHashSum,:Actor)";
					$song_insert_data = array(
							":ISRC"=>$SoundRecording[$ReleaseResourceReference]["ISRC"],
							":ResourceReference"=>$ReleaseResourceReference,
							":ReferenceTitle"=>$SoundRecording[$ReleaseResourceReference]["ReferenceTitle"],
							":LanguageOfPerformance"=>$SoundRecording[$ReleaseResourceReference]["LanguageOfPerformance"],
							":Duration"=>$SoundRecording[$ReleaseResourceReference]["Duration"],
							":Title"=>$SoundRecording[$ReleaseResourceReference]["Title"]["TitleText"],
							":Artist"=>$SoundRecording[$ReleaseResourceReference]["Artist"],
							":Producer"=>$SoundRecording[$ReleaseResourceReference]["Producer"],
							":Composer"=>$SoundRecording[$ReleaseResourceReference]["Composer"],
							":Lyricist"=>$SoundRecording[$ReleaseResourceReference]["Lyricist"],
							":Label"=>$SoundRecording[$ReleaseResourceReference]["Label"],
							":Year"=>$SoundRecording[$ReleaseResourceReference]["Year"],
							":plinetext"=>$SoundRecording[$ReleaseResourceReference]["plinetext"],
							":Genre"=>$SoundRecording[$ReleaseResourceReference]["Genre"],
							":ParentalWarningType"=>$SoundRecording[$ReleaseResourceReference]["ParentalWarningType"],
							":AudioCodecType"=>$SoundRecording[$ReleaseResourceReference]["AudioCodecType"],
							":NumberOfChannels"=>$SoundRecording[$ReleaseResourceReference]["NumberOfChannels"],
							":SamplingRate"=>$SoundRecording[$ReleaseResourceReference]["SamplingRate"],
							":IsPreview"=>$SoundRecording[$ReleaseResourceReference]["IsPreview"],
							":FileURL"=>$SoundRecording[$ReleaseResourceReference]["FileURL"],
							":HashSum"=>$SoundRecording[$ReleaseResourceReference]["HashSum"],
							":Grid"=>$Grid,
							":ICPN"=>$ICPN,
							":xmlid"=>$xmlid,
							":ImageURL"=>$SoundRecording['Image']["ImageFile"],
							":ImageHashSum"=>$SoundRecording['Image']["ImageHash"],
							":Actor"=>$SoundRecording[$ReleaseResourceReference]["Actor"]
								);
					$stmt = $this->prepare($song_insert);
					$stmt->execute($song_insert_data);
					//print_r($stmt->errorInfo());
					
				}			
			}
			$stmt2 = $this->prepare($insert);
                        $stmt2->execute($insert_data);
		}
	}

	public function ProcessDealList($ReleaseList,$DealList,$xmlid){
		//	print_r($DealList);exit;
		//	print_r($ReleaseList);exit;
		$DealArr = array();
		foreach ($DealList as $Deal){
			$DealReleaseReference = $Deal->DealReleaseReference->__toString();
			foreach($Deal->Deal as $DealTerms){
				$CommercialModelType = $DealTerms->DealTerms->CommercialModelType->__toString();
				$DealArr[$DealReleaseReference][$CommercialModelType]['CommercialModelType'] =$CommercialModelType;

				$Usage =$DealTerms->DealTerms->Usage->UseType;
				$UsageArr = array();					
				foreach($Usage as $u){
					$UsageArr[] = $u;
				}

				$DealArr[$DealReleaseReference][$CommercialModelType]['Usage']= implode(" / ",$UsageArr);
				$DealArr[$DealReleaseReference][$CommercialModelType]['TerritoryCode'] = $DealTerms->DealTerms->TerritoryCode->__toString();
				$DealArr[$DealReleaseReference][$CommercialModelType]['ValidityPeriod'] =$DealTerms->DealTerms->ValidityPeriod->StartDate->__toString();
				$DealArr[$DealReleaseReference][$CommercialModelType]['TakenDown'] =$DealTerms->DealTerms->TakenDown->__toString();
				if($DealTerms->DealTerms->TakenDown->__toString()){
					$DealArr[$DealReleaseReference][$CommercialModelType]['ValidityEndPeriod'] = $DealTerms->DealTerms->ValidityPeriod->StartDate->__toString();
				}else{
					$DealArr[$DealReleaseReference][$CommercialModelType]['ValidityEndPeriod'] ="";
				}

			}
		}

		$CatalogNumber = "";
		$ICPN ="";

		foreach ($ReleaseList as $Release){
			$ReleaseReference = $Release->ReleaseReference->__toString();
			$partyid = $this->MessageSenderPartyId->__toString();
			$GRid = $Release->ReleaseId->GRid->__toString();
			$ICPN = !empty($Release->ReleaseId->ICPN->__toString())?$Release->ReleaseId->ICPN->__toString():$ICPN;
			$CatalogNumber = !empty($Release->ReleaseId->CatalogNumber->__toString())?$Release->ReleaseId->CatalogNumber->__toString():$CatalogNumber;
			$ISRC = $Release->ReleaseId->ISRC->__toString();
			$TitleText = $Release->ReferenceTitle->TitleText->__toString();
			$DisplayArtistName = $Release->ReleaseDetailsByTerritory->DisplayArtistName->__toString();
			$ReleaseType =$Release->ReleaseType->__toString();
			foreach($DealArr as $DealReleaseReference=>$Deal){
				if($ReleaseReference == $DealReleaseReference){
					foreach($Deal as $d){
						$select = "SELECT count(*) as ct FROM sony_xml_ws_output_1 WHERE isrc=:isrc and catalog_number=:catalog_number and commercial_type=:commercial_type";
						$select_data =  array(
								":isrc"=>$ISRC,
								":commercial_type"=>$d["CommercialModelType"], 
								":catalog_number"=>$CatalogNumber,
								);
						$stmt = $this->prepare($select);
						$stmt->execute($select_data);
						$result = $stmt->fetchAll ( PDO::FETCH_ASSOC );
						$insert_data = array(":party_id"=>$partyid,
								":catalog_number"=>$CatalogNumber,
								":ipcn"=>$ICPN,
								":grid"=>$GRid,
								":isrc"=>$ISRC,
								":artist"=>$DisplayArtistName,
								":title"=>$TitleText,
								":release_type"=>$ReleaseType,
								":commercial_type"=>$d["CommercialModelType"],
								":use_type"=>$d["Usage"],
								":territories"=>$d["TerritoryCode"],
								":price_value_code"=>"",
								":sales_start_date"=>$d["ValidityPeriod"],
								":sales_end_date"=>$d["ValidityEndPeriod"],
								":xmlid"=>$xmlid
								);


						if(!empty($result[0]["ct"])){
							$update = "UPDATE sony_xml_ws_output_1 SET party_id=:party_id,catalog_number=:catalog_number,ipcn=:ipcn,grid=:grid,isrc=:isrc,artist=:artist,title=:title,release_type=:release_type,commercial_type=:commercial_type,use_type=:use_type,territories=:territories,price_value_code=:price_value_code,sales_start_date=:sales_start_date,sales_end_date=:sales_end_date,xmlid=:xmlid,updated_date=NOW() WHERE isrc=:isrc and catalog_number=:catalog_number and commercial_type=:commercial_type";
							$stmt  = $this->prepare($update);
							$stmt->execute($insert_data);
							//print_r($stmt->rowCount());

						}else{

							$insert = "INSERT INTO sony_xml_ws_output_1(party_id,catalog_number,ipcn,grid,isrc,artist,title,release_type,commercial_type,use_type,territories,price_value_code,sales_start_date,sales_end_date,xmlid,inserted_date) VALUE(:party_id,:catalog_number,:ipcn,:grid,:isrc,:artist,:title,:release_type,:commercial_type,:use_type,:territories,:price_value_code,:sales_start_date,:sales_end_date,:xmlid,now())";
							$stmt  = $this->prepare($insert);
							$stmt->execute($insert_data); 	
						}	
					} 	
				}
			}

		}
	}

	public function ProcessSoundRecording($ResourceList,$xmlid){
		$SoundRecordingArr = array();
		if(!empty($ResourceList->Image)){
			if(!empty($ResourceList->Image->ImageDetailsByTerritory->TechnicalImageDetails->File->URL)){
				$ImageFile = $ResourceList->Image->ImageDetailsByTerritory->TechnicalImageDetails->File->URL->__toString();
				$ImageHash = $ResourceList->Image->ImageDetailsByTerritory->TechnicalImageDetails->File->HashSum->HashSum->__toString();
			}

		}
		$SoundRecordingArr['Image'] = array("ImageFile"=>$ImageFile,"ImageHash"=>$ImageHash);

		foreach($ResourceList->SoundRecording as $resource){
			$ResourceReference = $resource->ResourceReference->__toString();
			$ISRC = $resource->SoundRecordingId->ISRC->__toString();
			$ReferenceTitle = $resource->ReferenceTitle->TitleText->__toString();
			$LanguageOfPerformance = $resource->LanguageOfPerformance->__toString();
			$Duration = $resource->Duration->__toString();
			$title = array();
			$artistArr = array();
			$RartistArr = array();
			foreach($resource->SoundRecordingDetailsByTerritory as $SoundRecording){
				$TerritoryCode = $SoundRecording->TerritoryCode->__toString();
				foreach($SoundRecording->Title as $t){
					$TitleType = $t->attributes()->TitleType->__toString();
					$TitleText = $t->TitleText->__toString();
					$title = array("TitleType" =>$TitleType , "TitleText" =>$TitleText);
				}
				$i = 0;
				foreach($SoundRecording->DisplayArtist as $artist){
					$FullName = $artist->PartyName->FullName->__toString();
					$DisplayArtist = array();
					foreach($artist->ArtistRole as $role){
						$DisplayArtist[] = $role->__toString();
					}

					$artistArr[$i] = array("FullName" => $FullName, "ArtistRole" =>$DisplayArtist);
					$i = $i+1;
				}
				unset($ArtistRole);
				$ArtistRole = array();

				foreach($artistArr as $resArt){
					$DisPartyName = $resArt["FullName"];
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
					$Artist .= !empty($arrArtist) ? implode(',', $arrArtist) : null;
				}

				foreach($SoundRecording->ResourceContributor as $resource){
					$FullName = $artist->PartyName->FullName->__toString();
					$ResourceArtistRole = array();
					foreach($artist->ResourceContributorRole as $role){
						$ArtistRole[$role->__toString()][] = $FullName;
					}
				}
				$ArtistRole['Producer'] = array_unique($ArtistRole['Producer']);
				$ArtistRole['Composer'] = array_unique($ArtistRole['Composer']);
				$ArtistRole['Lyricist'] = array_unique($ArtistRole['Lyricist']);
				$ArtistRole['Actor'] = array_unique($ArtistRole['Actor']);

				$Actor = "";
				if(!empty($ArtistRole['Actor'])){
					$Actor .= implode(',', $ArtistRole['Actor']);
				}

				$Composer = "";
				if(!empty($ArtistRole['Composer'])){
					$Composer .= implode(',', $ArtistRole['Composer']);
				}

				$Lyricist = "";
				if(!empty($ArtistRole['Lyricist'])){
					$Lyricist .= implode(',', $ArtistRole['Lyricist']);
				}

				$Producer = "";
				if(!empty($ArtistRole['Lyricist'])){
					$Producer .= implode(',', $ArtistRole['Producer']);
				}


				$Label  = $SoundRecording->LabelName->__toString();
				$Year  = $SoundRecording->PLine->Year->__toString();
				$plinetext  = $SoundRecording->PLine->PLineText->__toString();
				$Genre  = $SoundRecording->Genre->GenreText->__toString();
				$ParentalWarningType  = $SoundRecording->ParentalWarningType->__toString();
				$FileURL = "";
				$HashSum = "";
				$Duration = "";
				$AudioCodecType = "";
				$SamplingRate = "";
				$IsPreview = "";
				foreach($SoundRecording->TechnicalSoundRecordingDetails as $soundRecording){
					if(!empty($soundRecording->File->URL)){
						if($soundRecording->IsPreview == "false"){
							$IsPreview = $soundRecording->IsPreview->__toString();
							$FileURL = $soundRecording->File->URL->__toString();
							$AudioCodecType = $soundRecording->AudioCodecType->__toString();
							$NumberOfChannels = $soundRecording->NumberOfChannels->__toString();
							$SamplingRate = $soundRecording->SamplingRate->__toString();
							$HashSum = $soundRecording->File->HashSum->HashSum->__toString();
						}

					}

					if(!empty( $soundRecording->PreviewDetails->Duration)){
						$Duration = $soundRecording->PreviewDetails->Duration->__toString();
					}
				}

				$SoundRecordingArr[$ResourceReference]["ISRC"]= $ISRC;
				$SoundRecordingArr[$ResourceReference]["ReferenceTitle"]= $ReferenceTitle;
				$SoundRecordingArr[$ResourceReference]["LanguageOfPerformance"]= $LanguageOfPerformance;
				$SoundRecordingArr[$ResourceReference]["Duration"]= $Duration;
				$SoundRecordingArr[$ResourceReference]["Title"]= $title;
				$SoundRecordingArr[$ResourceReference]["Artist"]= $Artist;
				$SoundRecordingArr[$ResourceReference]["Producer"]= $Producer;
				$SoundRecordingArr[$ResourceReference]["Composer"]= $Composer;
				$SoundRecordingArr[$ResourceReference]["Lyricist"]= $Lyricist;
				$SoundRecordingArr[$ResourceReference]["Label"]= $Label;
				$SoundRecordingArr[$ResourceReference]["Year"]= $Year;
				$SoundRecordingArr[$ResourceReference]["plinetext"]= $plinetext;
				$SoundRecordingArr[$ResourceReference]["Genre"]= $Genre;
				$SoundRecordingArr[$ResourceReference]["ParentalWarningType"]= $ParentalWarningType;
				$SoundRecordingArr[$ResourceReference]["AudioCodecType"]= $AudioCodecType;
				$SoundRecordingArr[$ResourceReference]["NumberOfChannels"]= $NumberOfChannels;
				$SoundRecordingArr[$ResourceReference]["SamplingRate"]= $SamplingRate;
				$SoundRecordingArr[$ResourceReference]["IsPreview"]= $IsPreview;
				$SoundRecordingArr[$ResourceReference]["FileURL"]= $FileURL;
				$SoundRecordingArr[$ResourceReference]["HashSum"]= $HashSum;
				$SoundRecordingArr[$ResourceReference]["Process_status"]= $Process_status;
				$SoundRecordingArr[$ResourceReference]["xmlid"]= $xmlid;
				$SoundRecordingArr[$ResourceReference]["Actor"]= $Actor;

			}
		}

		return $SoundRecordingArr;
	}

	public function ProcessReleaseList($objXml){
		$ReleaseList = $objXml;
		$album = array();
		$ReleaseId = "";
		foreach($ReleaseList->Release as $Release){
			$GRid = $Release->ReleaseId->GRid->__toString();
			$ICPN = $Release->ReleaseId->ICPN->__toString();
			$CatalogNumber = $Release->ReleaseId->CatalogNumber->__toString();
			$TitleText = $Release->ReferenceTitle->TitleText->__toString();
			$ReleaseResourceReference = $Release->ReleaseResourceReferenceList->ReleaseResourceReference;
			$ReleaseReference = $Release->ReleaseReference->__toString();
			if(!empty($ICPN)){
				$album[$ReleaseReference]['Grid'] = $GRid;
				$album[$ReleaseReference]['ICPN'] = $ICPN;
				$album[$ReleaseReference]['CatalogNumber'] = $CatalogNumber;
				$album[$ReleaseReference]['TitleText'] = $TitleText;
				$album[$ReleaseReference]['GlobalOriginalReleaseDate'] = $Release->GlobalOriginalReleaseDate->__toString();
				$album[$ReleaseReference]['ReleaseResourceReference'] = (array) $ReleaseResourceReference;
				$album[$ReleaseReference]['Year'] = $Release->PLine->Year->__toString();
				$album[$ReleaseReference]['Label'] =$Release->PLine->PLineText->__toString();
				$album[$ReleaseReference]['Genre'] =$Release->ReleaseDetailsByTerritory->Genre->GenreText->__toString();
				$album[$ReleaseReference]['ReleaseType'] =$Release->ReleaseType->__toString();;
			}
		}
		return $album;

	}

	public function ProcessMessageHeader($objXml){
		$MessageThreadId = $objXml->MessageThreadId;
		$MessageId = $objXml->MessageId;
		$MessageSenderPartyId = $objXml->MessageSender->PartyId;
		$MessageSenderFullName = $objXml->MessageSender->PartyName->FullName;
		$MessageRecipientPartyId = $objXml->MessageRecipient->PartyId;
		$MessageRecipientFullName = $objXml->MessageRecipient->PartyName->FullName;
		$MessageCreatedDateTime = $objXml->MessageCreatedDateTime;
		$MessageControlType = $objXml->MessageControlType;
		//echo $MessageThreadId . "---->" .$MessageId .  "---->" .$MessageSenderPartyId."---->".$MessageSenderFullName."---->".$MessageRecipientPartyId."---->".$MessageRecipientFullName. "---->" .$MessageCreatedDateTime. "---->".$MessageControlType;
		$insert = "INSERT INTO sony_ws_xmllist(MessageThreadId,MessageId,MessageSenderId,MessageSenderName,MessageRecipientId,MessageRecipientName,MessageCreatedDateTime,Process_status,Process_date) VALUES(:MessageThreadId,:MessageId,:MessageSenderId,:MessageSenderName,:MessageRecipientId,:MessageRecipientName,NOW(),:Process_status,NOW())";
		$stmt = $this->prepare($insert);
		$stmt->execute(array(
					":MessageThreadId"=>$MessageThreadId,
					":MessageId"=>$MessageId,
					":MessageSenderId"=>$MessageSenderPartyId,
					":MessageSenderName"=>$MessageSenderFullName,
					":MessageRecipientId"=>$MessageRecipientPartyId,
					":MessageRecipientName"=>$MessageRecipientFullName,
					":Process_status"=>"0"
				    ));	
		return $this->lastInsertId();
	}

}

//$obj = new SonyXmlParser();
//$obj->ProcessSonyRawXML("ReleaseAvailabilityCall2.xml");
 
