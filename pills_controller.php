<?php

// no direct access
defined('EMONCMS_EXEC') or die('Restricted access');

function pills_controller()
{
    global $mysqli,$session, $route, $data, $redis, $timestore_adminkey;
	require "model.php";
	$model = new PillsModel($mysqli, $session['userid'], "gateway.cairnsolutions.com", "grebll", "St4pl3r", $redis, $timestore_adminkey);
	//$model = new PillsModel($mysqli, $session['userid'], "test.mosquitto.org", NULL, NULL, $redis, $timestore_adminkey);
    $result = false;
	
    if (!$session['read']) return array('content'=>false);
	
	//form
	if($route->action == 'configure' && $session['write']){
		if($_SERVER['REQUEST_METHOD'] == 'POST')
			$result = view("Modules/pills/form_submitted.php", array("model" => $model));
		else
			$result = view("Modules/pills/form.php", array("data" => $model->getAllData()));
	}
		
	
	//Showing the whole schedule configuration in json format
	if($route->action == 'configure' && $route->format == 'json') {
		$result = (object)$model->getAllData();
	}
	
	//Used by JQuery in form to provide suggestions when user is entering pills names in field.
	if($route->action == "pillNames" && $route->format == 'json') {
		$result = $model->getPillNames();
	}
	
	if($route->action == "updateCell" && $route->format == 'json') {
		$result = $model->updateCell(get('cellIndex'), get('snoozes'), get('time'), get('importance'), get('pills'));
	}
	
	if($route->action == "publish") {
		$result = view("Modules/pills/publish.php", array());
	}
	
    return array('content'=>$result);
}