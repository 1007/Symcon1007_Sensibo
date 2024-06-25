# Symcon1007 Sensibo

## 1. Funktionsumfang

Dieses Modul bietet benutzt die Sensibo API um Daten anzuzeigen
und die Klimaanlage zu steuern.


## 2. Systemanforderungen

- IP-Symcon ab Version 4.x

- Sensibo Account und API-Key (https://home.sensibo.com/me/api)

  

## 3. Installation

Über die Kern-Instanz "Module Control" folgende URL hinzufügen:

https://github.com/1007/Symcon1007_Sensibo

Instanz hinzufuegen ( Geraet! ).



## 4. Konfiguration

In der Konfiguration den API-Key eintragen.
Danach sollte im Formular nach dem Update Devices eine
Auswahl der GeraeteIDs moeglich sein.
Werden automatisch gefunden.



## 5. Script Befehle

 Folgende Befehle sind unter anderen im Moment moeglich:

SSB_SetACOn($InstanceID, "on");

SSB_SetACOn($InstanceID, "off");

SSB_SetACTemperatur($InstanceID, 22);

SSB_SetACFanLevel($InstanceID, "quiet");

SSB_SetACFanLevel($InstanceID, "low");

SSB_SetACFanLevel($InstanceID, "medium");

SSB_SetACFanLevel($InstanceID, "high");

SSB_SetACFanLevel($InstanceID, "auto");

SSB_SetACFanLevel($InstanceID, "strong");

SSB_SetACMode($InstanceID, "cool");

SSB_SetACMode($InstanceID, "heat");

SSB_SetACMode($InstanceID, "fan");

SSB_SetACMode($InstanceID, "auto");

SSB_SetACMode($InstanceID, "dry");

SSB_SetClimaReactOnOff($InstanceID, TRUE);

SSB_SetClimaReactOnOff($InstanceID, FALSE);


*Beispiele - siehe Instanz-Formular-Information*
SSB_SetACSwing($InstanceID, "rangeFull");                   

SSB_SetACSwing($InstanceID, "stopped");

SSB_SetACHorizontalSwing($InstanceID, "rangeFull");

SSB_SetACHorizontalSwing($InstanceID, "stopped");



