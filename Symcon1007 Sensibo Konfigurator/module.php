<?php


class SensiboConfigurator extends IPSModule
{

    public function Create()
    {
        parent::Create();
        $this->RegisterPropertyInteger('RootId', 0);
        $this->RegisterPropertyString("APIKey", "");
        $this->RegisterPropertyString("objid", "");
        $this->RegisterPropertyString("model", "");
        $this->RegisterPropertyString("id", "");
        $this->RegisterPropertyString("mac", "");
        $this->RegisterPropertyString("serial", "");
        $this->RegisterPropertyString("room", "");
        $this->RegisterPropertyString("instanz", "");
        $this->RegisterAttributeString("AllDevices", "");


    }

    /**
     * Interne Funktion des SDK.
     */
    public function ApplyChanges()
    {
        parent::ApplyChanges();
    }

  
       	//**************************************************************************
		// Holen der Daten
		//**************************************************************************
		public function Update($Form)
        {

        $apikey = $this->ReadPropertyString("APIKey") ;

        $devices = $this->GetAllDevices();
        
        // $devices = $this->ReadAttributeString("AllDevices");	
       
        if ( $devices == FALSE )
            return false;

        //$this->SendDebug(__FUNCTION__,"Devices : " .$devices,0);    
        

        foreach($devices as $device)
            {  
            if ( isset($device['id']) == true ) 
                {
                $id = $device['id'] ;   
                $this->SendDebug(__FUNCTION__,"DeviceID: ".$id,0);

                $result = $this->GetSingleDevices($id);
                if ( $result == false )
                    continue;

        
                // $ok = $this->CheckResult($result);	
                // $this->SendDebug(__FUNCTION__,"Result : " .$ok,0);


                 //if ( $ok == false )
                 //   continue;
                    
                //$result = $result['result'];
                //print_r($result);
            
                
                
            
                //$Form = $this->DecodeData($result,$Form); 

                //$Form = json_encode($Form);

                $this->SendDebug(__FUNCTION__,"Form : " .$Form,0);

                }

            }    


        return;

        $this->SendDebug(__FUNCTION__,"Result : " .$resultcurl,0);

        $result = json_decode($resultcurl,true);

        $ok = $this->CheckResult($result);	
        if ( $ok == false )
            return false;
        
        $result = $result['result'];

    
        $this->SendDebug(__FUNCTION__,"Form : " .$Form,0);

        $Form = $this->DecodeData($result,$Form);    
        
        return $Form;

        }


		//**************************************************************************
		//
		//**************************************************************************
        function DecodeData($result,$Form)
            {

            $ag = 0 ;    
            foreach($result as $data)
                {
                // print_r($data);


                $macaddress = @$data['macAddress'];    
                $this->SendDebug(__FUNCTION__,"[".$ag."] MacAdresse : " .$macaddress,0);
                $id = @$data['id'];    
                $this->SendDebug(__FUNCTION__,"[".$ag."] ID : " .$id,0);
                $model = @$data['productModel'];    
                $this->SendDebug(__FUNCTION__,"[".$ag."] Modell : " .$model,0);
                $serial = @$data['serial'];    
                $this->SendDebug(__FUNCTION__,"[".$ag."] Seriennummer : " .$serial,0);
                $room = @$data['room']['name'];    
                $this->SendDebug(__FUNCTION__,"[".$ag."] Raum : " .$room,0);

                $instance = $this->CheckInstanceExist($id);

                $AddValue = [
                    'objid'     => $ag,
                    'id'        => $id,
                    'model'     => $model,
                    'serial'    => $serial,
                    'room'      => $room,
                    'mac'       => $macaddress,
                    'instanceID'=> $instance,
                ];

                $Values[] = $AddValue;

                $ag = $ag + 1;
                }    


            // Original Form ( String nach Array )   
            $this->SendDebug(__FUNCTION__,"GetForm:".$Form,0);    
            $Form = json_decode($Form,TRUE); 
           
            // neue Eintraege ( Array  )
            $this->SendDebug(__FUNCTION__,"GetForm2:".json_encode($Values),0);
               
            // Neue action Eintraege hinzufuegen    
            $Form['actions'][0]['values'] = $Values;

            $this->SendDebug(__FUNCTION__,"GetForm3:".json_encode($Form),0);
            return ($Form);

            }

        //**************************************************************************
		//
		//**************************************************************************
        private function CheckInstanceExist()
            {

            $InstanceIDListSensors = IPS_GetInstanceListByModuleID('{661213AB-C412-087F-7F96-4FCBAA704433}');
            
            $id = 18445;    
            // $id = 0  ;    
            return $id ;

            }


        //**************************************************************************
		//
		//**************************************************************************
        public function GetAPIKey()
            {

            $apikey = $this->ReadPropertyString("APIKey") ;

            return $apikey;

            }


        //**************************************************************************
		//
		//**************************************************************************
		public function GetConfigurationForm()
			{
			
            $this->SendDebug(__FUNCTION__,"GetForm",0);
            
            $Form =  file_get_contents(__DIR__ . '/form.json');   
            $this->SendDebug(__FUNCTION__,"FORM" .$Form,0);
        
            $Form = $this->UpdateConfigurationForm($Form);
            
            $Form = $this->Update($Form);

            return $Form;
			
			}
        

        //**************************************************************************
		//
		//**************************************************************************
		protected function UpdateConfigurationForm($Form)
			{
            
            $this->SendDebug(__FUNCTION__, "GetForm", 0);

            return $Form;

            }




		//**************************************************************************
        // Ergebniss checken und als Array zurueckgeben oder FALSE
        // Rueckgabewert ist der Inhalt von "result"
		//**************************************************************************
		private function CheckResult(string $result)
			{

            $result = json_decode($result,TRUE);    

			$status = false;	
			if ( isset($result['status']))
				$status = $result['status'];

			if ( $status != 'success' )
				{
				$this->SendDebug(__FUNCTION__,"Success NOK : " ,0);
				return false;	
				}	
            
            $status = false;

            if ( isset($result['result']))
                $status = true;
    
            if ( $status == false )        
                {
				$this->SendDebug(__FUNCTION__,"Result NOK : " ,0);
				return false;	
				}	
            else
                {
                // $this->SendDebug(__FUNCTION__,"Result OK : " ,0);
                $result = $result['result'];
                $this->SendDebug(__FUNCTION__,"Result OK : ".json_encode($result) ,0);
                return $result;    
                }
			
		

			}



  		//******************************************************************************
		//	Curl Abfrage ausfuehren
		//******************************************************************************
		function DoCurl(string $url,bool $debug=false)
			{

			$curl = curl_init($url);

			curl_setopt($curl,CURLOPT_RETURNTRANSFER,true);

			$output = curl_exec($curl);

			curl_close($curl);
		
			return $output;

			}


    /**
     * Liefert alle Geräte.
     *
     * @return array Array mit allen Geräten
     */
    private function GetDevices(): array
    {
        $Result = $this->SendData('api/table.json', [
            'content' => 'devices',
            'columns' => 'objid,group,device'
        ]);

        if (!array_key_exists('devices', $Result)) {
            return [];
        }
        return $Result['devices'];
    }


   		//**************************************************************************
		// 
		//**************************************************************************
		protected function GetSingleDevices($device)
			{
            
            $apikey = $this->GetAPIKey();
            
            $url = "https://home.sensibo.com/api/v2/pods/".$device."?fields=*&apiKey=".$apikey;

            $this->SendDebug(__FUNCTION__,$url,0);
    
            $result = $this->DoCurl($url);
    
            $this->SendDebug(__FUNCTION__,"Result : " .$result,0);
    
            // $result = json_decode($result,true);
    
            if ( $result == false )
                return false;

            $result = $this->CheckResult($result);	
            
            if ( $result == false )
                return false;
              
            return $result;
    
            }



   		//**************************************************************************
		// 
		//**************************************************************************
		protected function GetAllDevices()
			{
			
			$apikey = $this->GetAPIKey();

			$url = "https://home.sensibo.com/api/v2/users/me/pods/?apiKey=".$apikey;

			$this->SendDebug(__FUNCTION__,$url,0);

			$result = $this->DoCurl($url);
            $this->SendDebug(__FUNCTION__,"Result : " .$result,0);

            $result = $this->CheckResult($result);	
            
            if ( $result == false )
                {
                $this->WriteAttributeString("AllDevices","");
                return false;    
                }
            else
                {
                $result = '[{"id": "xxxx"},{"id": "xxxx"},{"id": "xxxx"}]';
                $this->WriteAttributeString("AllDevices",$result);
                $result = json_decode($result,true);
                return $result;
                }    
    		
            if ($result == false) 
                {
                $this->WriteAttributeString("AllDevices","");	
                return;
                }
                
			}
   

}

/* @} */
