<?php


    // Klassendefinition
	class Sensibo extends IPSModule 
		{

        // Überschreibt die interne IPS_Create($id) Funktion
		public function Create() 
			{

        	$this->RegisterPropertyInteger("Intervall", 10);

             $this->RegisterPropertyString("APIKey", "");
             $this->RegisterTimer("SSB_UpdateTimer", 10, 'SSB_Update($_IPS["TARGET"]);');

            // Diese Zeile nicht löschen.
            parent::Create();

        	}

        // Überschreibt die intere IPS_ApplyChanges($id) Funktion
		public function ApplyChanges() 
			{

        	//Timer stellen
			$interval = $this->ReadPropertyInteger("Intervall") ;
			$this->SetTimerInterval("SSB_UpdateTimer", $interval*1000);

			$this->SetStatus(102);

			// Diese Zeile nicht löschen
            parent::ApplyChanges();
        	}


   		//**************************************************************************
		// manuelles Holen der Daten oder ueber Timer
		//**************************************************************************
		public function Update()
			{
	
			$apikey = $this->ReadPropertyString("APIKey") ;

    		$url = "https://home.sensibo.com/api/v2/users/me/pods?fields=*&apiKey=".$apikey;

			$this->SendDebug(__FUNCTION__,$url,0);

    		$resultcurl = $this->DoCurl($url);

    		$this->SendDebug(__FUNCTION__,"Result : " .$resultcurl,0);

			$result = json_decode($resultcurl,true);

			$ok = $this->CheckResult($result);	
			if ( $ok == false )
				return false;
				
				
				
			}


			
		//**************************************************************************
		//
		//**************************************************************************
		public function CheckResult($result)
			{
			$status = false;	
			if ( isset($result['status']))
				$status = $result['status'];

			if ( $status != 'success' )
				{
				$this->SendDebug(__FUNCTION__,"Success NOK : " ,0);
				return false;	
				}	
			
			$this->SendDebug(__FUNCTION__,"Success OK : " ,0);
			return true;	


			}


		//**************************************************************************
		//
		//**************************************************************************
		public function Destroy()
			{
			$this->UnregisterTimer("SSB_UpdateTimer");

			//Never delete this line!
			parent::Destroy();
			}


    	public function SetACState($status)
			{
		
		


        	}

    	public function GetAllDevices($status)
			{
		
			

        	}



   		//******************************************************************************
		//	Curl Abfrage ausfuehren
		//******************************************************************************
		function DoCurl($url,$debug=false)
			{

			$curl = curl_init($url);

			curl_setopt($curl,CURLOPT_RETURNTRANSFER,true);

			$output = curl_exec($curl);

			curl_close($curl);
		
			return $output;

			}

    	}