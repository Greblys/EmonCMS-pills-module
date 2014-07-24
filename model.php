<?php 

class PillsModel {
	
	private $mysqli;
	private $broker;
	private $user;
	
	public function __construct($mysqli, $user, $host, $brokerUsername, $brokerPass) {
		$this->mysqli = $mysqli;
		$this->broker = new SecureMqtt($host, $brokerUsername, $brokerPass);
		$this->user = $user;
	}

	public function getAllData(){
		$userId = $this->user;
		if($result = $this->mysqli->query("SELECT * FROM Cells WHERE user_id='$userId'")){
			$data = Array();
			for (; $row = $result->fetch_array();){
				$data[$row["cell_index"]]["deadline"] = $row["deadline"];
				$data[$row["cell_index"]]["importance"] = $row["importance"];
				$data[$row["cell_index"]]["names"] = Array();
			}
				
			if($result = $this->mysqli->query("SELECT * FROM Names_in_cells WHERE user_id='$userId'")) {
				for (; $row = $result->fetch_array();)
					array_push($data[$row["cell_index"]]["names"], $row["name"]);
			}
			return $data;
		} else 
			return null;
	}
	
	public function getPillNames(){
		$names = Array();
		if($result = $this->mysqli->query("SELECT name FROM Pill_names")){
			while($row = $result->fetch_array())
				array_push($names, $row['name']);
		}
		return $names;
	}

	public function sendScheduleToBroker($json){
		//there is a bug either in the broker or either in this PHP
		//MQTT client library that you CAN NOT PUBLISH without a loop with some delays.
		$sent = false;
		for($i = 0; $i < 4; $i++) {
			$this->broker->loop();
			if(!$sent) {
				$this->broker->publish("pills/schedule", $json, 1, 1);
				$sent = true;
			}
			sleep(1);
		}
	}

}

class SecureMqtt extends Mosquitto\Client{
	
	public function __construct($host, $username, $pass){
		parent::__construct();
		parent::setCredentials($username, $pass);
		parent::setTlsCertificates(".");
		parent::setTlsOptions(0, "tlsv1.1", NULL);
		parent::connect($host, 8883);
	}
}