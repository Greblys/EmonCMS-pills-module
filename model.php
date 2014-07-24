<?php 
function get_all_data(){
	global $mysqli;
	$userId = 1;
	if($result = $mysqli->query("SELECT * FROM Cells WHERE user_id='$userId'")){
		$data = Array();
		for (; $row = $result->fetch_array();){
			$data[$row["cell_index"]]["deadline"] = $row["deadline"];
			$data[$row["cell_index"]]["importance"] = $row["importance"];
			$data[$row["cell_index"]]["names"] = Array();
		}
			
		if($result = $mysqli->query("SELECT * FROM Names_in_cells WHERE user_id='$userId'")) {
			for (; $row = $result->fetch_array();)
				array_push($data[$row["cell_index"]]["names"], $row["name"]);
		}
		return $data;
	} else 
		return null;
}