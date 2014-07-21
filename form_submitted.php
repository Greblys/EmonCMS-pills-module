<?php
defined('EMONCMS_EXEC') or die('Restricted access');

global $mysqli;
$userId = 1; //Currently using only one user, but in the future there should be more. Database schema is ready for multiple users.
$cellsTableExists = $mysqli->query("SHOW TABLES LIKE 'cells'")->num_rows > 0;
$namesTableExists = $mysqli->query("SHOW TABLES LIKE 'pills'")->num_rows > 0;
$namesInCellsTableExists  = $mysqli->query("SHOW TABLES LIKE 'pills'")->num_rows > 0;

if(!$cellsTableExists){
	$mysqli->query("CREATE TABLE Cells (
				   user_id INT,
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
$mqttData = [];
echo date("r", $weekBase)."<br>";
for($index = 0; $index < 28; $index++){
	$time = 0;
	$pillNames = [];
	$importance = 0;
	foreach($_POST[$index] as $key => $value) $$key = $value;
	list($hours, $mins) = explode(":", $time);
	$deadline = $mins * 60 + $hours * 3600 + ($index / 4) * $daySecs + $weekBase;
	echo date("r", $deadline)."<br>";
	$mqtt[$index]["time"] = $deadline;
	$mqtt[$index]["importance"] = $importance;
	$mqtt[$index]["names"] = [];
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
		array_push($mqtt[$index]["names"], $name);
		$rows = $mysqli->query("SELECT * FROM Pill_names WHERE name='$name'");
		$rows2 = $mysqli->query("SELECT * FROM Names_in_cells WHERE user_id='$userId' AND cell_index='$index' AND name='$name'");
		if(!$rows || $rows->num_rows == 0) 
			$mysqli->query("INSERT INTO Pill_names VALUES('$name')");
		if(!$rows2 || $rows2->num_rows == 0)
			$mysqli->query("INSERT INTO Names_in_cells VALUES('$userId', '$index', '$name')");
	}
}

echo json_encode((object) $mqtt);
/*
foreach($_POST as $key => $value) {
	
	if($key < 28 && $key >= 0 && preg_match("~(([01]?[0-9]|2[0-3]):[0-5][0-9])?~", $value, $matches) == 1): 
		$value = $matches[0] ? "'".$matches[0]."'" : "NULL";
		$mysqli->query("UPDATE Cells
					   SET deadline=$value
					   WHERE id=$key");

	else: 
	?>
	<div class="alert alert-danger"><h4 class="alert-heading">Something went wrong. Try again.</h4></div>
	<?php  
	break;
	endif;
}
if ($key == 27): 
?>
	<div class="alert alert-success">Schedule is updated now.</div>
<?php 
endif; 
*/