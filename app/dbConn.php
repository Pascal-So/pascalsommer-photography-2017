<?php

class DBConn{

	private $db;

	private $show_mysqli_errors = true;

	function __construct(){
		include("config.php");

		$db = new mysqli($config['hostname'], $config['username'], $config['password'], $config['database']);

		if($db->connect_errno){
			die("DATABASE ERROR");
		}

		$this->db = $db;
	}

	function __destruct() {
    	$this->db->close();
   	}

	private function refValues($arr){
        $refs = array();
        foreach($arr as $key => $value)
            $refs[$key] = &$arr[$key];
        return $refs;

	    return $arr;
	}

	function get_insert_id() : int {
		$id = $this->db->insert_id;
		return $id;
	}

	function query() : array {
		// query("select ? from usrs", "id");

		$args = func_get_args();

		if(count($args)<1){
			die("QUERY ERROR: mising args");
		}

		if(gettype($args[0])!="string"){
			die("QUERY ERROR: incorrect argtype");
		}

		$query = $args[0];

		$nr_params = count($args) - 1;
		/*$nr_params = substr_count($query, '?');

		if(count($args)-1 != $nr_params){
			die("QUERY ERROR: incorrect nr of args");
		}*/

		if(!$stm = $this->db->prepare($query)){
			if($this->show_mysqli_errors){
				echo $this->db->error;
				echo "<br>";
			}
			echo $query;
			die("QUERY ERROR: couldn't prepare query");
		}

		if($nr_params>0){

			$params = array_slice($args, 1);

			$getTypeLetter = function($a){
				$type = gettype($a);
				if(!in_array($type, array("string", "integer", "double"))){
					die("QUERY ERROR: unknown type: " . $type);
				}
				return substr($type, 0,1);
			};

			$param_types = implode("",array_map($getTypeLetter, $params));

			array_unshift($params, $param_types);

			call_user_func_array(array($stm, "bind_param"), $this->refValues($params));
		}

		if(!$stm->execute()){
			if($this->show_mysqli_errors){
				echo $this->db->error . " - " . $stm->error;

				echo "<br>";
			}
			die("QUERY ERROR: error when executing query");
		}

		$res = $stm->get_result();

		$stm->close();

		//print_r($args);

		if($res){
			return $res->fetch_all(MYSQLI_ASSOC);
		}else{
			return array();
		}
	}
}

?>