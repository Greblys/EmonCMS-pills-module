<?php

// no direct access
defined('EMONCMS_EXEC') or die('Restricted access');
include_once "model.php";

function pills_controller()
{
    global $mysqli,$session, $route, $data;

    $result = false;

    if (!$session['read']) return array('content'=>false);
	
	//form
	if($route->action == 'configure' && $session['write']){
		if($_SERVER['REQUEST_METHOD'] == 'POST')
			$result = view("Modules/pills/form_submitted.php", array());
		else
			$result = view("Modules/pills/form.php", array());
	}
		
	
	//Showing the whole schedule configuration in json format
	if($route->action == 'configure' && $route->format == 'json') {
		$result = (object)get_all_data();
	}
	
	//Used by JQuery in form to provide suggestions when user is entering pills names in field.
	if($route->action == "pillNames" && $route->format == 'json') {
		$names = Array();
		if($result = $mysqli->query("SELECT name FROM Pill_names")){
			$rows = $result->fetch_all(MYSQLI_ASSOC);
			foreach($rows as $row) 
				array_push($names, $row['name']);
		}
		$result = $names;
	}
	
	if($route->action == "publish") {
		$result = view("Modules/pills/publish.php", array());
	}
	
    return array('content'=>$result);
}