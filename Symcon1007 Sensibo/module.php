<?php

//******************************************************************************
//	Name		:	Sensibo Modul
//	Info		:	
//
//******************************************************************************



	//******************************************************************************
	//	Klassendefinition
	//******************************************************************************
	class Sensibo extends IPSModule 
		{

		//******************************************************************************
		//	ueberschreibt die interne IPS_Create($id) Funktion
		//******************************************************************************        
		public function Create() 
			{

			$this->RegisterPropertyInteger("Intervall", 10);

			$this->RegisterPropertyString("APIKey", "");
			$this->RegisterPropertyString("GeraeteID", "");
			$this->RegisterTimer("SSB_UpdateTimer", 10, 'SSB_Update($_IPS["TARGET"]);');
			$this->RegisterAttributeString("AllDevices", "");
			$this->RegisterPropertyBoolean("Modulaktiv", true);
			$this->RegisterPropertyBoolean("ShowMoreDebug", false);

            // Diese Zeile nicht loeschen.
            parent::Create();

        	}

        
   		//**************************************************************************
		// ueberschreibt die intere IPS_ApplyChanges($id) Funktion
		//**************************************************************************        
		public function ApplyChanges() 
			{

			// Diese Zeile nicht loeschen
			parent::ApplyChanges();
				
			$this->RegisterAllProfile();

			$this->GetConfigurationForm();

        	//Timer stellen
			$interval = $this->ReadPropertyInteger("Intervall") ;
			$this->SetTimerInterval("SSB_UpdateTimer", $interval*1000);
			

			$aktiv = $this->ReadPropertyBoolean("Modulaktiv") ;
			if ( $aktiv == true )	
				$this->SetStatus(102);
			else
				$this->SetStatus(104);	


			
        	}

	//******************************************************************************
	// Register alle Profile
	//******************************************************************************
	protected function RegisterAllProfile()
		{
			
		$this->SendDebug(__FUNCTION__."[".__LINE__."]","",0);

		$this->RegisterProfile(1,"Sensibo.Sekunden"  	,"Clock"  		,"",$this->translate(" seconds"));
		$this->RegisterProfile(1,"Sensibo.RSSI"  		,"Intensity"  	,"",$this->translate(" dbm"));
		$this->RegisterProfile(1,"Sensibo.Entfernung"  	,"Distance"  	,"",$this->translate(" Meter"));

		$this->RegisterProfile(2,"Sensibo.Solltemperatur"  	,"Temperature"  ,""," °C",15,30,1);
		$this->RegisterProfile(2,"Sensibo.Threshold"  		,""  ,""," °C %",false,false,false,1);

		$this->RegisterProfileEinAus("Sensibo.EinAus", "Power", "", "", Array(
			Array(0, $this->translate("off"),  	"", 0x0000FF),
			Array(1, $this->translate("on"),   	"", 0x00FF00)
			));
	 
		$this->RegisterProfileInteger("Sensibo.Swing", "", "", "", Array(
				Array(0, $this->translate("stopped") ,  "",0),
				Array(1, $this->translate("full range") 	,  "",0)
				
				));

		$this->RegisterProfileInteger("Sensibo.Fanlevel", "", "", "", Array(
				Array(0, $this->translate("quiet")		,  	"Ventilation",0),
				Array(1, $this->translate("low")		,   "Ventilation",0),
				Array(2, $this->translate("medium")		,   "Ventilation",0),
				Array(3, $this->translate("high")		,   "Ventilation",0),
				Array(4, $this->translate("auto")		,   "Ventilation",0),
				Array(5, $this->translate("strong")		,   "Ventilation",0)					
				));


		$this->RegisterProfileInteger("Sensibo.HomekitModus", "", "", "", Array(
				Array(0, $this->translate("Off")	,  	"Climate",0),	// Homekit
				Array(1, $this->translate("Heat")	,   "Climate",0),	// Homekit
				Array(2, $this->translate("Cool")	,   "Climate",0),	// Homekit
				Array(3, $this->translate("Auto")	,   "Climate",0),	// Homekit
				Array(4, $this->translate("Fan")	,   "Climate",0),
				Array(5, $this->translate("Dry")	,   "Climate",0),
											
				));

		$this->RegisterProfileInteger("Sensibo.Modus", "", "", "", Array(
				Array(0, $this->translate("Off")	,  	"Climate",0),	// Homekit
				Array(1, $this->translate("Heat")	,   "Climate",0),	// Homekit
				Array(2, $this->translate("Cool")	,   "Climate",0),	// Homekit
				Array(3, $this->translate("Auto")	,   "Climate",0),	// Homekit
				Array(4, $this->translate("Fan")	,   "Climate",0),
				Array(5, $this->translate("Dry")	,   "Climate",0),
												
				));
				
										
		}

	//**************************************************************************
	// 
	//**************************************************************************    
	protected function RegisterProfileInteger($Name, $Icon, $Prefix, $Suffix, $Associations) 
		{
		if ( sizeof($Associations) === 0 )
			{
			$MinValue = 0;
			$MaxValue = 0;
			}
		else 
			{
			$MinValue = $Associations[0][0];
			$MaxValue = $Associations[sizeof($Associations)-1][0];
			}

		$this->RegisterProfile(1,$Name, $Icon, $Prefix, $Suffix, $MinValue, $MaxValue, 0);

		foreach($Associations as $Association) 
			{
			IPS_SetVariableProfileAssociation($Name, $Association[0], $Association[1], $Association[2], $Association[3]);
			}

		}


	//**************************************************************************
	//
	//**************************************************************************    
	protected function RegisterProfileEinAus($Name, $Icon, $Prefix, $Suffix, $Associations) 
		{
		if ( sizeof($Associations) === 0 )
			{
			$MinValue = 0;
			$MaxValue = 0;
			}
		else 
			{
			$MinValue = $Associations[0][0];
			$MaxValue = $Associations[sizeof($Associations)-1][0];
			}

		$this->RegisterProfile(0,$Name, $Icon, $Prefix, $Suffix, $MinValue, $MaxValue, 0);

		foreach($Associations as $Association) 
			{
			IPS_SetVariableProfileAssociation($Name, $Association[0], $Association[1], $Association[2], $Association[3]);
			}

		}


   		//**************************************************************************
		// 
		//**************************************************************************
		public function RequestAction($Ident, $Value) 
			{

			$this->SendDebug(__FUNCTION__."[".__LINE__."]",$Ident,0);
			
			switch($Ident) 
					{
					case "acStateon":
						
						//Neuen Wert in die Statusvariable schreiben
						$this->SetValue($Ident, $Value);

						if ( $Value == true )
							$state = "on";
						else
							$state = "off";
						$this->SetACOn($state);
						break;
					
					case "acStatemode":
						
						if ($Value == 0)	// Homekit Aus
						   {
							// $this->SetValue($Ident, $Value);

							if ( $Value == true )
								$state = "on";
							else
								$state = "off";
							$this->SetACOn($state);
							
						   	break;	
						   }


						//Neuen Wert in die Statusvariable schreiben
						$this->SetValue($Ident, $Value);
	
						$state = $this->DecodeMode($Value,true);
		
						$this->SetACMode($state);
						
						break;

					case "acStatefanLevel":
						
			 
							//Neuen Wert in die Statusvariable schreiben
							$this->SetValue($Ident, $Value);
		
							$state = $this->DecodeMode($Value,true);
			
							$this->SetACFanLevel($state);
							
							break;

					case "acStateswing":
						
			 
							//Neuen Wert in die Statusvariable schreiben
							$this->SetValue($Ident, $Value);
			
							$state = $this->DecodeSwing($Value,true);
				
							$this->SetACFanLevel($state);
								
							break;
			

					case "acStatetargetTemperature":
						
			 
						//Neuen Wert in die Statusvariable schreiben
						$this->SetValue($Ident, $Value);
						$this->SetACTemperatur($Value);

						break;


					case "climareactonoff":
						
						//Neuen Wert in die Statusvariable schreiben
						$this->SetValue($Ident, $Value);
						$this->SetClimaReactOnOff($Value);
						break;
	

					default:
					$this->SendDebug(__FUNCTION__."[".__LINE__."]","Ident unbekannt : " . $Ident,0);
					}
			 
			}

   		//**************************************************************************
		// 
		//**************************************************************************
		public function GetClimateReact()
			{
                $apikey = $this->GetAPIKey();

				$deviceID = $this->GetDeviceID();

                $url = "https://home.sensibo.com/api/v2/pods/".$deviceID."/smartmode/?apiKey=".$apikey;

                $this->SendDebug(__FUNCTION__."[".__LINE__."]", $url, 0);

                $resultcurl = $this->DoCurl($url);

				$this->SendDebug(__FUNCTION__."[".__LINE__."]", "Result : " .$resultcurl, 0);

				$result = json_decode($resultcurl,true);

				$ok = $this->CheckResult($result);	
				if ($ok == false) 
					{
                    $this->SendDebug(__FUNCTION__."[".__LINE__."]", "Status NOK: ", 0);
                    return;
                	}	

				if ( isset($result['result']))
					$result = $result['result'];
				else
					{
					$this->SendDebug(__FUNCTION__."[".__LINE__."]", "Result NOK: ", 0);
					return false;
                    }
			
			$this->DecodeClimateReact($result);		

            }

   		//**************************************************************************
		// 
		//**************************************************************************
		protected function CheckIdentExist($ident)
			{
			
			$id = @$this->GetIDForIdent ($ident);	
			if ( $id == FALSE )
				$this->SendDebug(__FUNCTION__."[".__LINE__."]", "Ident existiert nicht : ".$ident, 0);

			return $id;

            }	


   		//**************************************************************************
		// 
		//**************************************************************************
		protected function DecodeClimateReact($result)
			{
			// $this->SendDebug(__FUNCTION__."[".__LINE__."]", "", 0);

			

			if ( isset($result['deviceUid']))
				$deviceuid = $result['deviceUid'];
			else
				{
				$this->SendDebug(__FUNCTION__."[".__LINE__."]", "deviceUid NOK! Keine ClimaReact Einstellungen vorhanden", 0);
				
				$result = $this->CheckIdentExist("climareactonoff");
				if ( $result == TRUE )
					$this->SetValue("climareactonoff", false); // wenn vorhanden
				return false;
				}

			if ( isset($result['enabled']))
				$enabled = $result['enabled'];
			else
				{
				$this->SendDebug(__FUNCTION__."[".__LINE__."]", "enabled not found", 0);
				return false;
				}

			$name = $this->translate("Clima React State");	
			$this->SetValueToVariable($name,$enabled,"climareactonoff","Sensibo.EinAus",70);	
			
			if ( isset($result['type']))
				$type = $result['type'];
			else
				{
				$this->SendDebug(__FUNCTION__."[".__LINE__."]", "type not found", 0);
				return false;
				}

			if ( $type == 'temperature')
				$type = "Temperatur";	
			if ( $type == 'humidity')
				$type = "Luftfeuchtigkeit";	
				
			$name = $this->translate("Clima React Type");	
			$this->SetValueToVariable($name,$type,"climareactontype","",71);	
			
			
			if ( isset($result['highTemperatureThreshold']))
				$high = $result['highTemperatureThreshold'];
			else
				{
				$this->SendDebug(__FUNCTION__."[".__LINE__."]", "highTemperatureThreshold not found", 0);
				return false;
				}

			$name = $this->translate("Clima React High");	
			$this->SetValueToVariable($name,$high,"climareacthigh","Sensibo.Threshold",80);	
	

			if ( isset($result['lowTemperatureThreshold']))
				$low = $result['lowTemperatureThreshold'];
			else
				{
				$this->SendDebug(__FUNCTION__."[".__LINE__."]", "lowTemperatureThreshold not found", 0);
				return false;
				}
				
			$name = $this->translate("Clima React Low");	
			$this->SetValueToVariable($name,$low,"climareactlow","Sensibo.Threshold",90);	
	

			if ( isset($result['lowTemperatureState']['on']))
				$on = $result['lowTemperatureState']['on'];
			else
				{
				$this->SendDebug(__FUNCTION__."[".__LINE__."]", "lowTemperatureState on not found", 0);
				return false;
				}
			
			$name = $this->translate("Low Temperature State");	
			$this->SetValueToVariable($name,$on,"lowtemperaturestateon","Sensibo.EinAus",91);	
			
			if ( isset($result['highTemperatureState']['on']))
				$on = $result['highTemperatureState']['on'];
			else
				{
				$this->SendDebug(__FUNCTION__."[".__LINE__."]", "highTemperatureState on not found", 0);
				return false;
				}
			
			$name = $this->translate("High Temperature State");	
			$this->SetValueToVariable($name,$on,"hightemperaturestateon","Sensibo.EinAus",81);	


			// FAN  *********************************************************************************
			if ( isset($result['lowTemperatureState']['fanLevel']))
				$on = $result['lowTemperatureState']['fanLevel'];
			else
				{
				$this->SendDebug(__FUNCTION__."[".__LINE__."]", "lowTemperatureState fanLevel not found", 0);
				return false;
				}
			
			$on = $this->DecodeFanlevel($on,false);
			$name = $this->translate("Low Temperature Fanlevel");	
			$this->SetValueToVariable($name,$on,"lowtemperaturestatefanlevel","Sensibo.Fanlevel",92);	
			
			if ( isset($result['highTemperatureState']['fanLevel']))
				$on = $result['highTemperatureState']['fanLevel'];
			else
				{
				$this->SendDebug(__FUNCTION__."[".__LINE__."]", "highTemperatureState fanlevel not found", 0);
				return false;
				}

			$on = $this->DecodeFanlevel($on,false);
			$name = $this->translate("High Temperature Fanlevel");	
			$this->SetValueToVariable($name,$on,"hightemperaturestatefanlevel","Sensibo.Fanlevel",82);	


			// Modus  *********************************************************************************
			if ( isset($result['lowTemperatureState']['mode']))
				$on = $result['lowTemperatureState']['mode'];
			else
				{
				$this->SendDebug(__FUNCTION__."[".__LINE__."]", "lowTemperatureState mode not found", 0);
				return false;
				}
			
			$on = $this->DecodeMode($on,false);
			$name = $this->translate("Low Temperature Mode");	
			$this->SetValueToVariable($name,$on,"lowtemperaturestatemode","Sensibo.Modus",92);	
			
			if ( isset($result['highTemperatureState']['mode']))
				$on = $result['highTemperatureState']['mode'];
			else
				{
				$this->SendDebug(__FUNCTION__."[".__LINE__."]", "highTemperatureState mode not found", 0);
				return false;
				}

			$on = $this->DecodeMode($on,false);
			$name = $this->translate("High Temperature Modus");	
			$this->SetValueToVariable($name,$on,"hightemperaturestatemode","Sensibo.Modus",82);	


			// Zielwert Temperatur/Luftfeuchte  ********************************************************
			if ( isset($result['lowTemperatureState']['targetTemperature']))
				$on = $result['lowTemperatureState']['targetTemperature'];
			else
				{
				$this->SendDebug(__FUNCTION__."[".__LINE__."]", "lowTemperatureThreshold targetTemperature not found", 0);
				return false;
				}
			
			$on = floatval($on);
			$name = $this->translate("Low Temperature Target");	
			$this->SetValueToVariable($name,$on,"lowtemperaturestatetarget","Sensibo.Threshold",92);	
			
			if ( isset($result['highTemperatureState']['targetTemperature']))
				$on = $result['highTemperatureState']['targetTemperature'];
			else
				{
				$this->SendDebug(__FUNCTION__."[".__LINE__."]", "highTemperatureThreshold targetTemperature not found", 0);
				return false;
				}
			$on = floatval($on);	
			$name = $this->translate("High Temperature Target");	
			$this->SetValueToVariable($name,$on,"hightemperaturestatetarget","Sensibo.Threshold",82);	




			// $this->SendDebug(__FUNCTION__."[".__LINE__."]", "", 0);

            }


   		//**************************************************************************
		// 
		//**************************************************************************
		public function GetAllDevices()
			{
			
			$apikey = $this->GetAPIKey();

			$url = "https://home.sensibo.com/api/v2/users/me/pods/?apiKey=".$apikey;

			$this->SendDebug(__FUNCTION__."[".__LINE__."]",$url,0);

			$resultcurl = $this->DoCurl($url);

    		$this->SendDebug(__FUNCTION__."[".__LINE__."]","Result : " .$resultcurl,0);

			if ( $resultcurl == false )
				return;
				
			$this->WriteAttributeString("AllDevices",$resultcurl);
			
			$result = json_decode($resultcurl,true);

			if ( isset($result['result']))
				$result = $result['result'];
			else
				return false;

			foreach($result as $r )
				{
				if ( isset($r['id']))	
					$this->SendDebug(__FUNCTION__."[".__LINE__."]","Geraete ID : " .$r['id'],0);

				}


			}
			

		//**************************************************************************
		// 
		//**************************************************************************
		public function UpdateDevices()
			{

			$this->SendDebug(__FUNCTION__."[".__LINE__."]","Suche Geraete",0);

			$this->GetAllDevices();

			$this->ReloadForm();

			}
				
   		//**************************************************************************
		// manuelles Holen der Daten oder ueber Timer
		//**************************************************************************
		public function Update()
			{
	
			$currentStatus = $this->GetStatus();
			if ( $currentStatus == 103 )			// wird geloescht, kein Update machen
				return;

			if ( $this->ReadPropertyBoolean("Modulaktiv") == false )
				{
				$this->SetStatus(104);
				return;
				}

			$aktiv = $this->ReadPropertyBoolean("Modulaktiv") ;
			if ( $aktiv == true )	
				$this->SetStatus(102);
			else
				$this->SetStatus(104);	
	


			$this->CheckHomeKitProfil();

			$this->GetAllDevices();	

			$apikey = $this->GetAPIKey();

			$deviceID = $this->GetDeviceID();

			if ( $deviceID == "" )
				{
				$this->SendDebug(__FUNCTION__."[".__LINE__."]","Keine Device ID",0);
				return;
				}

			$url = "https://home.sensibo.com/api/v2/pods/".$deviceID."?fields=*&apiKey=".$apikey;

			$this->SendDebug(__FUNCTION__."[".__LINE__."]",$url,0);

    		$resultcurl = $this->DoCurl($url);

    		$this->SendDebug(__FUNCTION__."[".__LINE__."]","Result : " .$resultcurl,0);

			$result = json_decode($resultcurl,true);

			if ( $result == false )
				{
				$this->SendDebug(__FUNCTION__."[".__LINE__."]","Result : NOK ",0);

				return;	
				}

			$ok = $this->CheckResult($result);	
			if ( $ok == TRUE )
				{	
				$this->DoDatas($result);	
				}

			$this->GetClimateReact();


			


			}

		//**************************************************************************
		//
		//**************************************************************************
		protected function GetAPIKey()
			{
			$apikey = $this->ReadPropertyString("APIKey") ;
			return $apikey;	
			}


		//**************************************************************************
		//
		//**************************************************************************
		protected function DoDatas($result)
			{
			
			$status = false;	
			if ( isset($result['result']))
					$result = $result['result'];
			else
				return false;
				
			$level = $result;	
			$keys = array( 	array('macAddress',$this->translate("MAC address"),0,"",50), 
							array('isGeofenceOnExitEnabled',$this->translate("geofency on exit"),0,"~Switch",61),
							// array('isClimateReactGeofenceOnExitEnabled',$this->translate("geofency on exit react"),0,"~Switch",32),
							array("currentlyAvailableFirmwareVersion",$this->translate("available firmware"),0,"",56),
							array("cleanFiltersNotificationEnabled",$this->translate("clean filter notification"),0,"~Switch",50),
							array("id",$this->translate("device id"),0,"",50),
							array("firmwareVersion",$this->translate("current firmware"),0,"",56),
							array("roomIsOccupied",$this->translate("room occupied"),0,"~Switch",50),
							array("firmwareType",$this->translate("type firmware"),0,"",55),
							array("productModel",$this->translate("model"),0,"",50),
							// array("temperatureUnit","Temperatureinheit",0),
							array("remoteFlavor",$this->translate("remote control"),0,"",50),
						);
			$this->DoKeys($level,$keys,"");
			

			if (isset($result['acState']) == true) 
				{
                $level = $result['acState'];

                $keys = array( 	array('on',$this->translate("air conditioning state"),0,"Sensibo.EinAus",2),
                                array('fanLevel',$this->translate("fan level"),3,"Sensibo.Fanlevel",11),
                                array("temperatureUnit",$this->translate("temperature unit"),0),
                                array("targetTemperature",$this->translate("target temperature"),2,"Sensibo.Solltemperatur",4),
                                array("mode",$this->translate("mode"),3,"Sensibo.Modus",10),
								array("swing",$this->translate("swing"),3,"Sensibo.Swing",12),
                            
                                    );
				$this->DoKeys($level, $keys, "acState");
				
				}
				
			if (isset($result['measurements']) == true) 
				{
                $level = $result['measurements'];

                $keys = array( 	array('temperature',$this->translate("temperature"),2,"Sensibo.Solltemperatur",5),
								array('humidity',$this->translate("humidity"),2,"~Humidity.F",6),
								array('rssi',$this->translate("rssi level"),3,"Sensibo.RSSI",15),        
                            
                                    );
                $this->DoKeys($level, $keys, "measurements");
				}
				
				

			if (isset($result['connectionStatus']) == true) 
				{
                $level = $result['connectionStatus'];

                $keys = array( 	array('isAlive',$this->translate("connection state"),0,"~Alert.Reversed",50),
                    
                        );
                
                $this->DoKeys($level, $keys, "connectionStatus");
				}

			if (isset($result['location']) == true) 
				{
                $level = $result['location'];

                $keys = array( 	array('geofenceTriggerRadius',$this->translate("Geofency Trigger Radius"),0,"Sensibo.Entfernung",60),
                    
                        );
                
                $this->DoKeys($level, $keys, "location");
				}

				

			if (isset($result['room']) == true) 
				{
                $level = $result['room'];

                $keys = array( 	array('name',$this->translate("room name"),0,"",1),
                    
                        );
                
                $this->DoKeys($level, $keys, "room");
				}
			

			if (isset($result['connectionStatus']['lastSeen']) == true) 
				{
                $level = $result['connectionStatus']['lastSeen'];

                $keys = array( 	array('secondsAgo',$this->translate("last connection seconds"),3,"Sensibo.Sekunden",50),
                            	array('time',$this->translate("last connection"),1,"~UnixTimestamp",50),
                            );

                $this->DoKeys($level, $keys, "connectionStatus");
            	}


			}	

		
		//******************************************************************************
		// Auswertung der Keys
		// Keys : 	0
		//			1	Timestamp
		//          2   Float
		//          3   Integer 
		//******************************************************************************
		protected function DoKeys($result,$keys,$prefix)
			{

			foreach ($keys as $key) 
				{
				$value = $this->LookingForkey($result,$key[0],$status);

				if ($key[0] == "temperatureUnit" )
					{
					$this->SetTemperaturUnit($value);
					continue;	
					}



				if ( $status == true )
					{
					$profil = "";	
					$ident = $prefix.$key[0];
					$name = $key[1];


				
						
					// $value von String in Integer wandeln
					if ($ident == "acStateswing" )
						{
						if ( $value == "stopped" )
							$value = 0;
						else
							{
							if ( $value == "rangeFull" )
								$value = 1;
							else
								$this->SendDebug(__FUNCTION__."[".__LINE__."]", "acStateswing Fehler: ".$value, 0);			
							}		

						}	

					if ($ident == "acStatefanLevel" )
						{
									
						$value1 = -1;

						if ( $value == "quiet" )
							$value1 = 0;
						if ( $value == "low" )
							$value1 = 1;
						if ( $value == "medium" )
							$value1 = 2;
						if ( $value == "high" )
							$value1 = 3;
						if ( $value == "auto" )
							$value1 = 4;
						if ( $value == "strong" )
							$value1 = 5;
						
						if ( $value1 == -1 )	
							$this->SendDebug(__FUNCTION__."[".__LINE__."]", "acStatefanLevel Fehler: ".$value, 0);			
						else
							$value = $value1;

									

						}	

					if ($ident == "acStatemode" )
						{
						//$this->SendDebug(__FUNCTION__."[".__LINE__."]", "acStatemode Fehler: ".$value, 0);			
									
						$value1 = -1;

						if ( $value == "cool" )
							$value1 = 2;
						if ( $value == "heat" )
							$value1 = 1;
						if ( $value == "fan" )
							$value1 = 4;
						if ( $value == "dry" )
							$value1 = 5;
						if ( $value == "auto" )
							$value1 = 3;
						
						if ( $value1 == -1 )	
							$this->SendDebug(__FUNCTION__."[".__LINE__."]", "acStatemode Fehler: ".$value, 0);			
						else
							$value = $value1;

						// $this->SendDebug(__FUNCTION__."[".__LINE__."]", "acStatemode : ".$value, 0);			
									

						}	

					$position = 0;	

					if ( $key[2] == 1 )
						{
						$value = strtotime($value);	
						$profil = $key[3];
						}	
					
					if ( $key[2] == 2 )
						{
						$value = floatval($value);	
						$profil = $key[3];
						}	

					if ( $key[2] == 3 )
						{
						$value = intval($value);	
						$profil = $key[3];
						}	
	

					if ( isset ($key[3]) )
						$profil = $key[3];

					if ( isset ($key[4]) )
						$position = $key[4];
	
					


					$this->SetValueToVariable($name,$value,$ident,$profil,$position);	
					}	
				else
					{
					$this->SendDebug(__FUNCTION__."[".__LINE__."]", "Key nicht gefunden : ".$key[0], 0);	
					}	
				}	
	
			}

		
		//******************************************************************************
		// Finde Keys im Result
		//******************************************************************************
		protected function LookingForKey($result,$key,&$status)
			{
		
			if (isset($result[$key]) == false) 
				{
				$this->SendDebug(__FUNCTION__."[".__LINE__."]", " Result Key NOK : ".$key, 0);
				$status = false ;
				
				return FALSE;	
				}
			
			
			$s = $result[$key];	
			if ( isset($s) ) 
				{
				$value = $s;	
				// $this->SendDebug(__FUNCTION__."[".__LINE__."]", "Key: ".$key . " Value: ".$value, 0);
				$status = true;
				return $value;
				}
			else
				{
				// $this->SendDebug(__FUNCTION__."[".__LINE__."]", "Nicht gefunden Key: ".$key, 0);
				$status = false ;
				}	
			}
			
			
		//******************************************************************************
		// Gefundene Werte in Variable schreiben (erstellen/Profil)
		//******************************************************************************
		protected function SetValueToVariable($name,$value,$ident,$profil=false,$position=0)
			{
			
            $VariableID = $this->CheckIdentExist($ident);	
            
            $enableAction = false;
            
			if ($ident == "acStateon") 
				{
                $enableAction = true;
				}

			if ($ident == "acStatetargetTemperature") 
				{
                $enableAction = true;
                }
			
			if ($ident == "acStatemode") 
				{
                $enableAction = true;                
                }	

			if ($ident == "acStatefanLevel") 
				{
                $enableAction = true;    
				}	

			if ($ident == "acStateswing") 
				{
                $enableAction = true;    
                }	
				
			if ($ident == "climareactonoff") 
				{
                $enableAction = true;    
				}	

			if ( $VariableID == false )	
				$this->SendDebug(__FUNCTION__."[".__LINE__."]", $name . "Ident NOK :" . $VariableID . " Ident: ".$ident, 0);
			
			// Variable ist Typ String
			if (is_string($value) == true) 
				{ 
				if ( $VariableID == false )
					$VariableID = $this->RegisterVariableString($ident, $name,$profil,$position);

				if ($VariableID != false) 
					{
					if ( $enableAction == true )
						$this->EnableAction($ident);

                    $old = $this->GetValue($ident);
                    if ($old != $value) 
                        $this->SetValue($ident, $value);
                    }
                }	
				
				
			// Variable ist Typ Bool
			if (is_bool($value) == true) 
				{
				// noch nicht vorhanden	
				if ( $VariableID == false )
					$VariableID = $this->RegisterVariableBoolean($ident,$name,$profil,$position);

				if ($VariableID != false) 
					{
					if ( $enableAction == true )
						$this->EnableAction($ident);

                    $old = $this->GetValue($ident);
                    if ($old != $value) 
                        $this->SetValue($ident, $value);
                    }
				}	

			// Variable ist Typ Integer	
			if (is_integer($value) == true) 
				{ 
				if ($VariableID == false) 
					$VariableID = $this->RegisterVariableInteger($ident, $name, $profil, $position);
				
				if ($VariableID != false) 
					{
					if ( $enableAction == true )
						$this->EnableAction($ident);

                    $old = $this->GetValue($ident);
                    if ($old != $value) 
                        $this->SetValue($ident, $value);
                    }

				}

			// Variable ist Typ Float
			if (is_float($value) == true) 
				{ 		
				if ( $VariableID == false )
					$VariableID = $this->RegisterVariableFloat($ident, $name,$profil,$position);

				if ($VariableID != false) 
					{
					if ( $enableAction == true )
						$this->EnableAction($ident);

                    $old = $this->GetValue($ident);
                    if ($old != $value) 
                        $this->SetValue($ident, $value);
                    }
				}		

            }	

		//**************************************************************************
		//
		//**************************************************************************
		protected function SetTemperaturUnit($unit)
			{
				
			if ( $unit != 'C' and $unit != 'F' )
				{
				$this->SendDebug(__FUNCTION__."[".__LINE__."]", "Temperatureinheit unbekannt :" . $unit , 0);
                return;
				}	

			$ident = "acStatetargetTemperature";	

			$VariableID = $this->CheckIdentExist($ident);
			
			// Variable noch nicht vorhanden
			if ( $VariableID == false )	
				return;

			$array = IPS_GetVariable ($VariableID);	
			$aktProfil = ($array['VariableCustomProfile']);	
			

			if ( $VariableID == true )
				{
				if ( $unit == "C" )
					{
					
                    }	
					
				if ( $unit == "F" )
					{
						
					}

				}	

			


			}	

		//**************************************************************************
		// DeviceID von Instanz zurueck geben
		//**************************************************************************
		public function GetDeviceID()
			{

			$id = $this->ReadPropertyString("GeraeteID") ;
			return $id;	
			
			}

			
		//**************************************************************************
		// ueberpruefe Result ob status auf success 
		//**************************************************************************
		protected function CheckResult(array $result)
			{
			$status = false;	
			if ( isset($result['status']))
				$status = $result['status'];

			if ( $status != 'success' )
				{
				$this->SendDebug(__FUNCTION__."[".__LINE__."]","Success NOK : " ,0);
				return false;	
				}	
			
			// $this->SendDebug(__FUNCTION__."[".__LINE__."]","Success OK : " ,0);
			return true;	

			}


		//**************************************************************************
		// Instanz loeschen
		//**************************************************************************
		public function Destroy()
			{
			//Never delete this line!
			parent::Destroy();
			}
		
		//**************************************************************************
		// Timer loeschen
		//**************************************************************************    
		protected function UnregisterTimer($Name)
			{
			$id = @IPS_GetObjectIDByIdent($Name, $this->InstanceID);
			if ($id > 0)
				{
				if (!IPS_EventExists($id))
					throw new Exception('Timer not present', E_USER_NOTICE);
				IPS_DeleteEvent($id);
				}
			}

		//******************************************************************************
		//	Clima React Mode
		// 	true - false
		//******************************************************************************
    	public function SetClimaReactOnOff($state)
    		{
			$this->SendDebug(__FUNCTION__."[".__LINE__."]", $state, 0);
			
			$this->SetValue('climareactonoff',$state);

			$apikey = $this->GetAPIKey();
			$deviceID = $this->GetDeviceID();

			$postfields = json_encode( array('enabled'=> $state) );

			$url = "https://home.sensibo.com/api/v2/pods/".$deviceID."/smartmode?apiKey=".$apikey;
	
			$this->SendDebug(__FUNCTION__."[".__LINE__."]", $url, 0);


			$resultcurl = $this->DoCurlPUT($url,$postfields);

    		$this->SendDebug(__FUNCTION__."[".__LINE__."]","Result : " .$resultcurl,0);

			$result = json_decode($resultcurl,TRUE);

			$status = $this->CheckResult($result);

			return $status;

			}

		//******************************************************************************
		//	AC Mode On
		// 	on	= true - false
		//******************************************************************************
    	public function SetACOn(string $state)
    		{
			$this->SendDebug(__FUNCTION__."[".__LINE__."]",$state,0);
			if ( $state == 'on' )
				$this->SetACState(true);
			if ( $state == 'off' )
				$this->SetACState(false);
			}

		//******************************************************************************
		//	AC Mode Umschalten
		// 	mode = heat - fan - auto - dry
		//******************************************************************************
    	public function SetACMode(string $mode)
    		{
			$mode = $this->DecodeMode($mode,false);	
			// $this->SendDebug(__FUNCTION__."[".__LINE__."]",": ".$mode ,0);	
			$this->SetACState(true,false,false,false,$mode);	
    		}

		//******************************************************************************
		//	AC Fan Level Umschalten
		// 	fanLevel = low - medium - high - auto 
		//******************************************************************************
    	public function SetACFanLevel(string $level)
    		{
			$level = $this->DecodeFanlevel($level,false);	
			$this->SetACState(true,false,false,$level);	
    		}

		//******************************************************************************
		//	AC Swing Umschalten
		// 	swing = stopped - rangeFull Beispiel
		//******************************************************************************
    	public function SetACSwing(string $mode)
    		{
			$mode = $this->DecodeSwing($mode,false);	
			$this->SetACState(true,false,$mode);	
    		}

		//******************************************************************************
		//	AC Solltemperatur
		//******************************************************************************
    	public function SetACTemperatur(int $temperatur)
    		{
    		$this->SetACState(true,$temperatur);	
    		}

		//******************************************************************************
		//	AC 
		//******************************************************************************
    	protected function SetACState(bool $status,$Soll=false,$Swing=false,$Fan=false,$Mode=false)
			{
			
			if ($status == true) 
				{
                $msg = "An";
                // $status = "on";
            	}
			else
				{
                $msg = "Aus";
                // $status = "off";
                }		

			$this->SendDebug(__FUNCTION__."[".__LINE__."]","".$msg ." : " . $Mode,0);
		
			$apikey = $this->GetAPIKey();
			$deviceID = $this->GetDeviceID();

			if ($Mode == false) 
				{
				$Mode   = $this->GetValue("acStatemode");
				}	

			if ($Soll == false) 
				{
				$Soll   = $this->GetValue("acStatetargetTemperature");				
				}
					
			$this->SendDebug(__FUNCTION__."[".__LINE__."]","Soll : " .$Soll,0); 	

			if ($Swing == false) 
				{
				$Swing   = $this->GetValue("acStateswing");
				}
					
			$this->SendDebug(__FUNCTION__."[".__LINE__."]","Swing : " .$Swing,0); 	

			if ($Fan == false) 
				{
				$Fan  = $this->GetValue("acStatefanLevel");
				}
					
			$this->SendDebug(__FUNCTION__."[".__LINE__."]","Fan : " .$Fan,0); 	

			$Mode = $this->DecodeMode($Mode,true);	
				 
			$Fan = $this->DecodeFanlevel($Fan,true);	


			if ( $Swing == 0 )
				$Swing1 = "stopped";
			if ( $Swing == 1 )
				$Swing1 = "rangeFull";
			$Swing = $Swing1;
			
			// "temperatureUnit" => "C",

			$postfields = json_encode(
								array( "acState" => 
										array( 
												'on'=> $status,
										 		'mode'=> $Mode,
												'fanLevel' => $Fan,
												"targetTemperature" => $Soll,
												
												"swing" => $Swing
											)
									)
									);

			$this->SendDebug(__FUNCTION__."[".__LINE__."]","Fields : " .$postfields,0);

			// $postfields = '"{acState": {"on": true, "swing": "stopped", "mode": "fan", "fanLevel": "low", "targetTemperature":22 ,"temperatureUnit":"C" ,"swing":"stopped" }}';						

			// $url = "https://home.sensibo.com/api/v2/pods/".$deviceID."/acStates/on?&apiKey=".$apikey;
			$url = "https://home.sensibo.com/api/v2/pods/".$deviceID."/acStates?apiKey=".$apikey;
	
			
			$resultcurl = $this->DoCurlPOST($url,$postfields);

    		$this->SendDebug(__FUNCTION__."[".__LINE__."]","Result : " .$resultcurl,0);

			$result = json_decode($resultcurl,true);

			$this->Update();

        	}

	//**************************************************************************
	//
	//**************************************************************************
	protected function DecodeFanlevel($value,$modus)
			{
			
			$return = false;
	
			if ( $modus == true )	// wandele Integer in Namen
				{
				if ( $value == 0 )
					$return = "quiet";
				if ( $value == 1 )
					$return = "low";
				if ( $value == 2 )
					$return = "medium";
				if ( $value == 3 )
					$return = "high";
				if ( $value == 4 )
					$return = "auto";
				if ( $value == 5 )
					$return = "strong";
	
				}	
			else					// wandele Namen in Integer
				{
				if ( $value == "quiet" )
					$return = 0;
				if ( $value == "low" )
					$return = 1;
				if ( $value == "medium" )
					$return = 2;
				if ( $value == "high" )
					$return = 3;
				if ( $value == "auto" )
					$return = 4;
				if ( $value == "strong" )
					$return = 5;
	
				}	
	
			return $return;	
	
			}		

		//**************************************************************************
		//
		//**************************************************************************
		protected function DecodeSwing($value,$modus)
			{
			
			$return = false;
	
			if ( $modus == true )	// wandele Integer in Namen
				{
				if ( $value == 0 )
					$return = "stopped";
				if ( $value == 1 )
					$return = "rangeFull";
				
				}	
			else					// wandele Namen in Integer
				{
				if ( $value == "stopped" )
					$return = 0;
				if ( $value == "rangeFull" )
					$return = 1;
	
				}	
	
			return $return;	
	
			}		
				

		//**************************************************************************
		//
		//**************************************************************************
		protected function DecodeMode($value,$modus)
		{
		
		$return = false;

		if ( $modus == true )	// wandele Integer in Namen
			{
			
			if ( $value == 1 )
				$return = "heat";	
			if ( $value == 2 )
				$return = "cool";
			if ( $value == 3 )
				$return = "auto";
			if ( $value == 4 )
				$return = "fan";
			if ( $value == 5 )
				$return = "dry";

			}	
		else					// wandele Namen in Integer
			{

			if ( $value == "heat" )
				$return = 1;	
			if ( $value == "cool" )
				$return = 2;
			if ( $value == "auto" )
				$return = 3;
			if ( $value == "fan" )
				$return = 4;
			if ( $value == "dry" )
				$return = 5;

			}	

		return $return;	

		}		

	//**************************************************************************
	//  0 - Bool
	//  1 - Integer
	//  2 - Float
	//  3 - String
	//**************************************************************************    
	protected function RegisterProfile($Typ, $Name, $Icon, $Prefix, $Suffix, $MinValue=false, $MaxValue=false, $StepSize=false, $Digits=0) 
		{
		if(!IPS_VariableProfileExists($Name)) 
			{
			IPS_CreateVariableProfile($Name, $Typ);  
			} 
		else 
			{
			$profile = IPS_GetVariableProfile($Name);
			if($profile['ProfileType'] != $Typ)
				{
				$this->Logmessage("Profil falsch : " . $Name, KL_WARNING);
				//throw new Exception("Variable profile type does not match for profile ".$Name);

				}
			}

		IPS_SetVariableProfileIcon($Name, $Icon);
		IPS_SetVariableProfileText($Name, $Prefix, $Suffix);
		IPS_SetVariableProfileValues($Name, $MinValue, $MaxValue, $StepSize);
 
		if ( $Typ == 2 )
			IPS_SetVariableProfileDigits($Name, $Digits);
		}


   		//******************************************************************************
		//	Check Homekit Profil
		//******************************************************************************
		protected function CheckHomeKitProfil()
			{
			
			$ProfilName = "Sensibo.Modus";
	
			if(!IPS_VariableProfileExists($ProfilName)) 
				{
				$s = "Profil nicht vorhanden";	
				$this->SendDebug(__FUNCTION__."[".__LINE__."]", $s ,0);
				return;
				}
			else
				{
				//$s = "Profil vorhanden";	
				//$this->SendDebug(__FUNCTION__."[".__LINE__."]", $s ,0);
				}
			
			$profil = IPS_GetVariableProfile ($ProfilName);
	
			if ( $profil['MaxValue'] == 5 )	// Neues Profil Homekit bereits vorhanden
				{
				$s = "Profil ist neu ( Homekit )";	
				// $this->SendDebug(__FUNCTION__."[".__LINE__."]", $s ,0);
				return;
				}
			else
				{
				$s = "Profil ist alt";	
				$this->SendDebug(__FUNCTION__."[".__LINE__."]", $s ,0);
				}	

			}

   		//******************************************************************************
		//	Curl GET Abfrage ausfuehren
		//******************************************************************************
		protected function DoCurl(string $url,bool $debug=false)
			{
			$curl = curl_init($url);
			curl_setopt($curl,CURLOPT_RETURNTRANSFER,true);
			$output = curl_exec($curl);
			curl_close($curl);
			return $output;
			}

   		//******************************************************************************
		//	Curl POST Abfrage ausfuehren
		//******************************************************************************
		protected function DoCurlPOST(string $url,$postfields,bool $debug=false)
			{
			$this->SendDebug(__FUNCTION__."[".__LINE__."]","URL:  " .$url,0);

			$curl = curl_init($url);
			curl_setopt($curl, CURLOPT_POST, 1);
			curl_setopt($curl, CURLOPT_POSTFIELDS,$postfields);
			curl_setopt($curl,CURLOPT_RETURNTRANSFER,true);
			$output = curl_exec($curl);
			curl_close($curl);
			return $output;
			}

   		//******************************************************************************
		//	Curl PUT Abfrage ausfuehren
		//******************************************************************************
		protected function DoCurlPUT(string $url,$postfields,bool $debug=false)
			{
			$this->SendDebug(__FUNCTION__."[".__LINE__."]","URL:  " .$url,0);

			$curl = curl_init($url);
			curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PUT");
			curl_setopt($curl, CURLOPT_POSTFIELDS,$postfields);
			curl_setopt($curl,CURLOPT_RETURNTRANSFER,true);
			$output = curl_exec($curl);
			curl_close($curl);
			return $output;
			}

		//******************************************************************************
		//	Curl PATCH Abfrage ausfuehren
		//******************************************************************************
		protected function DoCurlPATCH(string $url,$postfields,bool $debug=false)
			{
			$this->SendDebug(__FUNCTION__."[".__LINE__."]","URL:  " .$url,0);
			$this->SendDebug(__FUNCTION__."[".__LINE__."]","FIELDS:  " .$postfields,0);
			
			$headers = array('Content-Type: application/json');
			$curl = curl_init($url);
			curl_setopt($curl,CURLOPT_RETURNTRANSFER,true);
			curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'PATCH');
			curl_setopt($curl, CURLOPT_POSTFIELDS,$postfields);
			curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
			$output = curl_exec($curl);
			curl_close($curl);
			return $output;
			}

		
		//******************************************************************************
		//	
		//******************************************************************************
		protected function GetDevicesFormular()
			{
			
			$AllDevices	= $this->ReadAttributeString("AllDevices");

			$form = '{ "caption": "No Device", "value": "" }';


			$result = json_decode($AllDevices,true);

			if ( isset($result['result']))
				$result = $result['result'];
			else
				return $form;

			foreach($result as $r )
				{
				if ( isset($r['id']))	
					$this->SendDebug(__FUNCTION__."[".__LINE__."]","Geraete ID : " .$r['id'],0);

					$form = $form . ',{ "caption": "'.$r['id'].'", "value": "'.$r['id'].'" }';

				}
			



			// $form = '';
			return $form;


			}

		//******************************************************************************
		//	Unixtimestamp wandeln	
		//******************************************************************************
		protected function TimestampToString($timestamp)
			{
			return date('d.m.Y H:i:s',$timestamp);
			}




		//******************************************************************************
		//	Konfigurationsformular dynamisch erstellen
		//******************************************************************************
		public function GetConfigurationForm() 
			{

				$library = IPS_GetLibrary("{369541F7-A037-96E4-A4A4-611C4EA6B925}");
				$name = $library['Name'];
				$version = $library['Version'];
				$build = $library['Build'];
				$date = $library['Date'];
				$date = $this->TimestampToString($date);

				$version = $name." " . $version . "#".$build ."[".$date."]"."10";
				$form = '
			
				{
					"elements":
					[
						{ "type": "Label"             , "label":  "'.$version.'" },
					  
					  	{
						"type":  "ExpansionPanel", "caption": "Settings",
						"items": 	[
										{ "type": "CheckBox"          	, "name" :  "Modulaktiv",  	"caption": "Modul aktiv" },
										{ "type": "ValidationTextBox"	, "name" : 	"APIKey", 		"caption": "API Key" },
										{ "type": "IntervalBox"       	, "name" :  "Intervall", 	"caption": "Sekunden" },
								
										{ "type": "Select", "name": "GeraeteID", "caption": "Device ID",
											"options": 	[
															'.
																$this->GetDevicesFormular()
															.'
														]
										}								
									]
						  },
						  
						  

						  {
							"type":  "ExpansionPanel", "caption": "Expert Parameters",
							"items": 	[
							  			{"type": "CheckBox", "name": "ShowMoreDebug", "caption": "Show more Debug"}
										]
						  } 
					],
					
					"actions":
					[   
					  { "type": "Button", "label": "Update Data",     "width": "250px",           "onClick": "SSB_Update($id);" },
					  { "type": "Button", "label": "Update Devices",  "width": "250px",           "onClick": "SSB_UpdateDevices($id);" }
					],
				  
					"status":
					  [
						  { "code": 101, "icon": "active", 		"caption": "Sensibo is created" },
						  { "code": 102, "icon": "active", 		"caption": "Sensibo is activ" },
						  { "code": 103, "icon": "active", 		"caption": "Sensibo is deleting" },
						  { "code": 104, "icon": "inactive", 	"caption": "Sensibo is inactiv" },
						
						  { "code": 202, "icon": "error",  		"caption": "API Key not valid" },
						  { "code": 203, "icon": "error",  		"caption": "Device ID not valid" }
					  ]
				    
				  }
					
				';

                return $form;
            }




    	}