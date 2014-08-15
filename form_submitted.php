<?php
defined('EMONCMS_EXEC') or die('Restricted access');

function sanitize($value){
	$value = trim($value);
	$value = stripslashes($value);
	$value = htmlspecialchars($value);
	return $value;
}

global $mysqli, $session;
$userId = $session['userid'];
$cellsTableExists = $mysqli->query("SHOW TABLES LIKE 'cells'")->num_rows > 0;
$namesTableExists = $mysqli->query("SHOW TABLES LIKE 'pills'")->num_rows > 0;
$namesInCellsTableExists  = $mysqli->query("SHOW TABLES LIKE 'pills'")->num_rows > 0;

if(!$cellsTableExists){
	$mysqli->query("CREATE TABLE Cells (
				   user_id INT, //#19
				   deadline INT,
				   importance TINYINT,
				   cell_index TINYINT,
				   PRIMARY KEY (user_id, cell_index)
				  )");		
}
if(!$namesTableExists){
	$mysqli->query("CREATE TABLE Pill_names (
				   name VARCHAR(50) PRIMARY KEY NOT NULL
				  )");
}
if(!$namesInCellsTableExists){
	$mysqli->query("CREATE TABLE Names_in_cells (
					user_id INT NOT NULL,
					cell_index TINYINT NOT NULL,
					name VARCHAR(50) NOT NULL,
					PRIMARY KEY (user_id, cell_index, name),
					FOREIGN KEY (user_id, cell_index) REFERENCES Cells(user_id, cell_index),
					FOREIGN KEY (name) REFERENCES Pill_names(name)
					)");
}

$daySecs = 60 * 60 * 24;
$weekBase = $_POST["weekNumber"];
$mqttData = Array();
$isError = false;
for($index = 0; $index < 28; $index++){
	$time = 0;
	$pillNames = Array();
	$importance = 0;
	foreach($_POST[$index] as $key => $value) {
		if($key != "pillNames") {
			$key = sanitize($key);
			$value = sanitize($value);
		}
		$$key = $value;
	}
	
	//validating deadline
	if(preg_match("~(([01]?[0-9]|2[0-3]):[0-5][0-9])?~", $time, $matches) == 1){
		$time = $matches[0] ? $matches[0] : "NULL"; //NULL if no deadline entered. In that case Cell is empty.
	} else {
		$isError = true;
		break;
	}
	
	if($time != "NULL") { //if cell is not empty
		list($hours, $mins) = explode(":", $time);
		$deadline = $mins * 60 + $hours * 3600 + floor($index / 4) * $daySecs + $weekBase;
	} else {
		$deadline = $time;
	}
	
	//validating importance
	if($importance < 0 || $importance > 3){
		$isError = true;
		break;
	}
	
	$mqtt[$index]["time"] = $deadline;
	$mqtt[$index]["importance"] = $importance;
	$mqtt[$index]["pills"] = Array();
	$rows = $mysqli->query("SELECT * FROM Cells WHERE user_id='$userId' AND cell_index='$index'");
	if($rows && $rows->num_rows > 0)
		$mysqli->query("UPDATE Cells
						SET deadline='$deadline', importance='$importance'
						WHERE user_id='$userId' AND cell_index='$index'");
	else
		$mysqli->query("INSERT INTO Cells VALUES ('$userId', '$deadline', '$importance', '$index')");
	
	if(count($pillNames) > 0)
		$mysqli->query("DELETE FROM Names_in_cells WHERE user_id='$userId' AND cell_index='$index'");
	foreach($pillNames as $name) {
		$name = sanitize($name);
		if(!empty($name)) { //validating pill name
			array_push($mqtt[$index]["pills"], $name);
			$rows = $mysqli->query("SELECT * FROM Pill_names WHERE name='$name'");
			$rows2 = $mysqli->query("SELECT * FROM Names_in_cells WHERE user_id='$userId' AND cell_index='$index' AND name='$name'");
			if(!$rows || $rows->num_rows == 0) 
				$mysqli->query("INSERT INTO Pill_names VALUES('$name')");
			if(!$rows2 || $rows2->num_rows == 0)
				$mysqli->query("INSERT INTO Names_in_cells VALUES('$userId', '$index', '$name')");
		}
	}
}

if($isError):
?>
<div class="alert alert-danger"><h4 class="alert-heading">Something went wrong. Try again.</h4></div>
<?php else: ?>
<div class="alert alert-success">Schedule is updated now.</div>
<?php endif; ?>

<?php
$json = json_encode((object) $mqtt, JSON_NUMERIC_CHECK);

$model->sendScheduleToBroker($json);
print_r("<pre>$json</pre>");