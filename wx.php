#!/usr/bin/php
<?php
// DOMOTICZ to APRX WX script
// SQ9MDD@2019 released under GPL.v.2
// http://sq9mdd.qrz.pl
//
// ramka pogodowa bez pozycji
// _mm dd gg mm		           temp		           hum baro
// _03 29 06 58 c025 s009 g008 t030 r000 p000 P000 h00 b10218
//
// ramka pogodowa z pozycja bez czasu
// 						  _		           temp		           hum baro
// ! 5215.01N / 02055.58E _ ... / ... g... t030 r000 p000 P000 h00 b10218
//
// jesli nie podaje parametru wstawiam kropki
// temp z sieci APRSjest w fahrenheit przeliczanie na C =(F-32)/9*5 
// temp w celsjusz do sieci APRS trzeba wyslac jako fahrenheit F = (C*9/5)+32 
//
//////// zmienne tutaj ustawiasz parametry dostepu do danych  oraz znak i pozycje ////////
$callsign		= 'SQ9MDD-4';			// your WX callsign		//
$lat			= '5215.01N';			// coordinates APRS format	//
$lon			= '02055.58E';			// coordinates APRS format	//
$ip			= '10.9.48.3';			// domoticz IP adress		//
$temp_idx		= '246';			// Temp sensor IDX		//
$tempi_idx		= '0';				// inside temperature		//
$humi_idx		= '246';			// Humidity sensor IDX		//
$baro_idx		= '241';			// Baromether  IDX		//
$comment		= 'Domoticz & APRX WX test';	// beacon comment		//
$err_comment		= 'ERROR NO WX DATA';		// comment if can't connect     //
///////////////// DO NOTE EDIT BELLOW THIS LINE //////////////////////////////////////////

$url = 'http://'.$ip.'/json.htm?type=devices&rid=';

//data i godzina
$time = date('mdHi');

// get inside temp from DOMOTICZ
if($tempi_idx != 0){
   $obj_tempi = @file_get_contents($url.$tempi_idx);
   $obj_tempi_res = json_decode($obj_tempi,true);
   $tempi = round($obj_tempi_res['result'][0]['Temp'],1);
}else{
   $tempi = '';
}

// get outside temp from DOMOTICZ
if($temp_idx != 0){
   $obj_temp = @file_get_contents($url.$temp_idx);
   $obj_temp_res = json_decode($obj_temp,true);
   $temp = $obj_temp_res['result'][0]['Temp'];
   if($temp != ''){
      $temp = round(($temp*9/5)+32);
   }
   if($temp < 100){
      $zero = '0';
   }else{
      $zero = '';
   }
}else{
   $temp = '';
}

// get outside humidity from DOMOTICZ
if($humi_idx != 0){
   $obj_humi = @file_get_contents($url.$humi_idx);
   $obj_humi_res = json_decode($obj_humi,true);
   $humi = $obj_humi_res['result'][0]['Humidity'];
   if($humi == 100) {
      $humi = '00';
   }
}else{
   $humi = '';
}

// get pressure from DOMOTICZ
$baro = '';
$obj_baro = @file_get_contents($url.$baro_idx);
$obj_baro_res = json_decode($obj_baro,true);
$baro = @$obj_baro_res['result'][0]['Barometer'];
if($baro != ''){
   $baro = $baro * 10;
}
if($baro < 10000){
	$bzero = '0';
}else{
	$bzero = '';
}

// print APRS WX data frame without time
$internal_temp_label = '';
if($tempi != ''){
   $internal_temp_label = " Int.T: ".$tempi."C ";
}

$temp_label = @$zero.$temp;

$humi_label = '';
if($humi != ''){
   $humi_label = "h".$humi;
}

$baro_label = '';
if($baro != ''){
   $baro_label = "b".$bzero.$baro;
}

if(($temp == '' )){
   echo "!".$lat."/".$lon."_".$err_comment."\n";
}else{
   echo "!".$lat."/".$lon."_.../...g...t".$temp_label.$humi_label.$baro_label.$internal_temp_label." ".$comment."\n";
}
// EOF
?>
