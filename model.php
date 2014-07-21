<?php 
function get_all_data(){
	global $mysqli;
	$userId = 1;
	if($result = $mysqli->query("SELECT * FROM Cells WHERE user_id='$userId'"))
			$rows = $result->fetch_all(MYSQLI_ASSOC);
			foreach($rows as $row) {
				$data[$row["cell_index"]]["deadline"] = $row["deadline"];
				$data[$row["cell_index"]]["importance"] = $row["importance"];
				$data[$row["cell_index"]]["names"] = [];
			}
			
		if($result = $mysqli->query("SELECT * FROM Names_in_cells WHERE user_id='$userId'")) {
			$rows = $result->fetch_all(MYSQLI_ASSOC);
			foreach($rows as $row) 
				array_push($data[$row["cell_index"]]["names"], $row["name"]);
		}
	return $data;
}