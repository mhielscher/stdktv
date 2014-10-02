<?php

if (isset($_POST) && isset($_POST['time']))
{
	$time_raw = $_POST['time'];
	if (strpos($time, ':') !== FALSE) // hh:mm (:ss ignored)
	{
		$time_elements = explode(':', $time_raw);
		$time_hours = filter_var($time_elements[0], FILTER_SANITIZE_NUMBER_INT, FILTER_NULL_ON_FAILURE);
		$time_minutes = filter_var($time_elements[1], FILTER_SANITIZE_NUMBER_INT, FILTER_NULL_ON_FAILURE);
		$time = $time_hours * $time_minutes;
	}
	else // in minutes
		$time = filter_var($_POST['time'], FILTER_SANITIZE_NUMBER_INT, FILTER_NULL_ON_FAILURE);
	$time = $time/60; // hours
	
	$days = filter_var($_POST['days'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_NULL_ON_FAILURE | FILTER_FLAG_ALLOW_FRACTION);
	$prebun = filter_var($_POST['prebun'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_NULL_ON_FAILURE | FILTER_FLAG_ALLOW_FRACTION);
	$postbun = filter_var($_POST['postbun'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_NULL_ON_FAILURE | FILTER_FLAG_ALLOW_FRACTION);
	$uf = filter_var($_POST['uf'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_NULL_ON_FAILURE | FILTER_FLAG_ALLOW_FRACTION);
	$postweight = filter_var($_POST['postweight'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_NULL_ON_FAILURE | FILTER_FLAG_ALLOW_FRACTION);
	
	if ($_POST['access'] == "cath")
		$C = 22.0; // AVF access constant
	else
		$C = 35.0; // Central catheter constant
	
	// Calculations
	//$water_volume = 2.447 - (0.09516 * $age) + 0.1074 * $height + 0.3362 * $postweight;
	$water_volume = .2265 * $postweight * 3;
	//$spKtV = -log($postbun/$prebun - 0.008*$time) + (4 - 3.5*$postbun/$prebun) * ($uf/$postWeight);
	//$eKtV = 0.924 * $spKtV - 0.395 * $spKtV/$time + 0.056;
	//$stdKtV = (168 * (1 - exp(-$eKtV))/$time) / ((1 - exp(-$eKtV))/$spKtV + (168 / ($days*$time) - 1));

	
	//$spKtV = log($prebun/$postbun);
	//$eKtV = $spKtV * ($time/($time+$C));
	
	// Daugirdas II
	$R = $postbun/$prebun;
	$short_break_GFAC = 0.0174 / (floor(7/$days) - $time/24);
	$long_break_GFAC = 0.0174 / (ceil(7/$days) - $time/24);
	$avg_GFAC = 0.0174 / (7/$days - $time/24);
	$short_break_spKtV = -log($R - $short_break_GFAC * $time) + (4 - 3.5*$R) * 0.55 * $uf/$water_volume;
	$long_break_spKtV = -log($R - $long_break_GFAC * $time) + (4 - 3.5*$R) * 0.55 * $uf/$water_volume;
	$spKtV = -log($R - $avg_GFAC * $time) + (4 - 3.5*$R) * 0.55 * $uf/$water_volume;


	$eKtV = 0.924 * $spKtV - 0.395 * $spKtV/$time + 0.056;
	$stdKtV = ((10080*(1-exp(-$eKtV)))/$time)/(((1-exp(-$eKtV))/$spKtV)+(10080/($days*$time))-1);

	header("Content-type: text/json");
	$return = array();
	$return['std'] = $stdKtV;
	$return['short_sp'] = $short_break_spKtV;
	$return['long_sp'] = $long_break_spKtV;
	$return['avg_sp'] = $spKtV;
	echo json_encode($return);
}

else
{
	header("Location: /ktv.php?sp=$spKtV&std=$stdKtV");
}

?>