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

Instanz hinzufuegen.



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





