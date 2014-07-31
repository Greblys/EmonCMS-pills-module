<?php 

class PillsModel {
	
	private $mysqli;
	private $broker;
	private $user;
	private $redis;
	private $timestoreAdminkey;
	
	public function __construct($mysqli, $user, $host, $brokerUsername, $brokerPass, $redis, $timestoreAdminkey) {
		$this->mysqli = $mysqli;
		$this->broker = new SecureMqtt($host, $brokerUsername, $brokerPass);
		$this->user = $user;
		$this->redis = $redis;
		$this->timestoreAdminkey = $timestoreAdminkey;
	}

	public function getAllData(){
		$userId = $this->user;
		if($result = $this->mysqli->query("SELECT * FROM Cells WHERE user_id='$userId'")){
			$data = Array();
			for (; $row = $result->fetch_array();){
				$data[$row["cell_index"]]["time"] = $row["deadline"];
				$data[$row["cell_index"]]["importance"] = $row["importance"];
				$data[$row["cell_index"]]["pills"] = Array();
			}
				
			if($result = $this->mysqli->query("SELECT * FROM Names_in_cells WHERE user_id='$userId'")) {
				while($row = $result->fetch_array())
					array_push($data[$row["cell_index"]]["pills"], $row["name"]);
			}
			
			//Getting schedule state			
			$allNames = Array();
			$days = Array("mon", "tue", "wed", "thu", "fri", "sat", "sun");
			$daytimes = Array("morn", "noon", "aft", "eve");
			foreach($days as $day)
				foreach($daytimes as $time)
					array_push($allNames, "$day $time");
			$userid = $this->user;
			for($i = 0; $i < 28; $i++) {
				$name = $allNames[$i];
				$result = $this->mysqli->query("SELECT * FROM feeds WHERE userid = '$userid' AND name = '$name'");
				$row = $result->fetch_array();
				$id = $row['id'];
				$lastvalue = $this->redis->hmget("feed:lastvalue:$id",array('time','value'));
				$data[$i]["state"] = $lastvalue['value'];
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
				$this->broker->publish("house/pill/schedule", $json, 1, 1);
				$sent = true;
			}
			sleep(1);
		}
	}

}

class SecureMqtt extends Mosquitto\Client{
	
	public function __construct($host, $username, $pass){
		parent::__construct();
		//parent::setCredentials($username, $pass);
		//parent::setTlsCertificates(".");
		//parent::setTlsOptions(Mosquitto\Client::SSL_VERIFY_NONE, "tlsv1", NULL);
		parent::connect($host/*, 8883*/);
	}
}