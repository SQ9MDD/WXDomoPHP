#!/usr/bin/php
<?php
// ramka pogodowa ez pozycji
// _mm dd gg mm		       temp		       baro
// _03 29 06 58 c025 s009 g008 t030 r000 p000 P000 h00 b10218
//
// ramka pogodowa z pozycja tylko temp i baro
// ! 5215.01N / 02055.58E _ ... / ... g... t26 b10060
//
// jesli nie podaje parametru wstawiam kropki
// temp w fahrenheit F=(C-32)/9*5 lub mniej dokładnie F=(C-30)/2
//
//
// zmienne tutaj ustawiasz parametry dostepu do danych  oraz znak i
 pozycje
$callsign	= 'SQ9MDD-4';			// your WX callsign
$lat		= '5215.01N';			// coordinates APRS format
$lon		= '02055.58E';			// coordinates APRS format
$ip		= '10.9.48.3';			// domoticz IP adress
$temp_idx	= '37';				// Temp sensor IDX
$baro_idx	= '241';			// Baromether  IDX

///////////////// DO NOTE EDIT BELLOW THIS LINE /////////////////////////////
$url            = 'http://'.$ip.'/json.htm?type=devices&rid=';

//data i godzina
$time = date('mdHi');

// get data from DOMOTICZ
$obj_temp 	= file_get_contents($url.$temp_idx);
$obj_temp_res 	= json_decode($obj_temp,true);
$temp		= round(($obj_temp_res['result'][0]['Temp']*9/5)+32);
if($temp < 100){
 $zero = '0';
}else{
 $zero = '';
}

//print_r($obj_temp_res);

$obj_baro	= file_get_contents($url.$baro_idx);
$obj_baro_res	= json_decode($obj_baro,true);
$baro		= ($obj_baro_res['result'][0]['Barometer']*10);
if($baro < 10000){
 $bzero = '0';
}else{
 $bzero = '';
}

//dane pogodowe z pozycja
echo "!".$lat."/".$lon."_.../...g...t".$zero.$temp."b".$baro." Test WX DOMOTICZ\n";

?>
