<?php


class SensiboConfigurator extends IPSModule
{

    public function Create()
    {
        parent::Create();
        $this->RegisterPropertyInteger('RootId', 0);
    }

    /**
     * Interne Funktion des SDK.
     */
    public function ApplyChanges()
    {
        parent::ApplyChanges();
    }

    /**
     * Liefert alle Sensoren.
     *
     * @return array Array mit allen Sensoren
     */
    private function GetSensors(): array
    {
        $Result = $this->SendData('api/table.json', [
            'content' => 'sensors',
            'columns' => 'objid,device,name,parentid'
        ]);

        if (!array_key_exists('sensors', $Result)) {
            return [];
        }
        return $Result['sensors'];
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

    /**
     * Interne Funktion des SDK.
     */
    public function GetConfigurationForm(): string
    {

		}

}

/* @} */
