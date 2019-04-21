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
$temp_idx		= '4';				// Temp sensor IDX		//
$tempi_idx		= '0';				// inside temperature		//
$humi_idx		= '4';				// Humidity sensor IDX		//
$baro_idx		= '20';				// Baromether  IDX		//
$pm_25_idx		= '41';				// PM 2.5 sensor IDX		//
$pm_10_idx		= '42';				// PM 10 sensor IDX		//
$voltage_batt_idx	= '44';				// Battery voltage sensor	//
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

// get bacup battery voltage
if($voltage_batt_idx != 0){
   $obj_batt_v = @file_get_contents($url.$voltage_batt_idx);
   $obj_batt_res = json_decode($obj_batt_v,true);
   $bat_voltage = round($obj_batt_res['result'][0]['Voltage'],1);
   $bat_voltage_label = "Bat:".$bat_voltage."V ";
}else{
   $bat_voltage_label = '';
}


// get dust pm_2.5
if($pm_25_idx != 0){
  $obj_pm25 = @file_get_contents($url.$pm_25_idx);
  $obj_pm25_res = json_decode($obj_pm25,true);
  $pm_25_val = round($obj_pm25_res['result'][0]['Data'],1);
  $pm25_label = "PM2.5=".$pm_25_val."ug/m3 ";
}else{
  $pm_25_val = '';
  $pm25_label = '';
}

// get dust pm_10
if($pm_10_idx != 0){
  $obj_pm10 = @file_get_contents($url.$pm_10_idx);
  $obj_pm10_res = json_decode($obj_pm10,true);
  $pm_10_val = round($obj_pm10_res['result'][0]['Data'],1);
  $pm10_label = "PM10=".$pm_10_val."ug/m3 ";
}else{
  $pm_10_val = '';
  $pm10_label = '';
}

// get outside temp from DOMOTICZ
if($temp_idx != 0){
   $obj_temp = file_get_contents($url.$temp_idx);
   $obj_temp_res = @json_decode($obj_temp,true);
   $temp = $obj_temp_res['result'][0]['Temp'];
   if($temp < 100 and $temp != ''){
      $temp = round(($temp*9/5)+32);
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
$obj_baro	= @file_get_contents($url.$baro_idx);
$obj_baro_res	= json_decode($obj_baro,true);
$baro		= ($obj_baro_res['result'][0]['Barometer']*10);
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

$temp_labe = '...';
if($temp != ''){
   $temp_label = $zero.$temp;
}

if($temp == '' and $humi == ''){
   echo "!".$lat."/".$lon."_".$err_comment."\n";
}else{
   echo "!".$lat."/".$lon."_.../...g...t".$temp_label."h".$humi."b".$bzero.$baro." ".$internal_temp_label.$bat_voltage_label.$pm25_label."".$pm10_label.$comment."\n";
}
// EOF
?>
