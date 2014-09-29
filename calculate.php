<?php

if (isset($_POST) && isset($_POST['time']))
{
	$time = filter_var($_POST['time'], FILTER_SANITIZE_NUMBER_INT, FILTER_NULL_ON_FAILURE);
	$days = filter_var($_POST['days'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_NULL_ON_FAILURE);
	$prebun = filter_var($_POST['prebun'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_NULL_ON_FAILURE);
	$postbun = filter_var($_POST['postbun'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_NULL_ON_FAILURE);
	$uf = filter_var($_POST['uf'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_NULL_ON_FAILURE);

	// do calculations
	$answer = 2.011;

	header("Content-type: text/json");
	echo "{ \"answer\": \"$answer\" }";
}

else
	header("Location: /ktv.php?answer=2.011");

?>