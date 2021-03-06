<?php

error_reporting(E_ALL);
/*
abstract class sql-escaper
abstract function escapeStr(str)
#other escape functions. not abstract
;

class sql-query
private static pdo-instance
private sql-escaper
;
*/

abstract class sql_escaper{
	abstract function escapeStr($str);
	public function escapeInt($int){
		return intval($int);
	}
	public function escapeFloat($float){
		return floatval($float);
	}
}

class sql_query{
	private static $pdo_instance =null;
	private $sql_instance =null;
	private $db_prefix;
	
	public function escapeString($string)
	{
		return $this->sql_instance->escapeStr($string);
	}
	
	public function escapeInt($int)
	{
		return $this->sql_instance->escapeInt($int);
	}
	
	public function escapeFloat($float)
	{
		return $this->sql_instance->escapeFloat($float);
	}
	
	function __construct($db_prefix, $dsn, 
					$username ="", $password="", $driver_options=null)
	{
		global $server_dir;

		if(self::$pdo_instance === null){
			self::$pdo_instance= new PDO($dsn, $username, 
											$password, $driver_options);
		}
		
		$sql_type=substr($dsn, 0, strpos($dsn, ':'));
		$path=$server_dir .'/core/driver/'. $sql_type .'.php';
		if(!file_exists($path)){
			throw new ErrorNotFound("Driver ". $sql_type 
								  		." not found with path: $path");
		}
		include_once $path;
		$this->sql_instance=new $sql_type ();
		
		$this->db_prefix=$db_prefix;
	}
	
	function createQuery($query, $args){
		#Nur ausführen, wenn wir weitere Parameter haben.
		$query=preg_replace('#\{(\w[\w\d_]+)\}#', 
							$this->db_prefix .'\1', $query); 
		if(sizeof($args)>0){
			$offset=0;
			$pos=0;
			$q="";
			$len=strlen($query);
			$i=0;

			while(($pos=strpos($query, '%', $offset))!==false){
				if(isset($query[$pos+1])){
					$type=$query[$pos+1];
					if($type=='%'){
						$q.=substr($query, $offset, $pos-$offset).'%';
					}else{
						$q.=substr($query, $offset, $pos-$offset);
						if($type=='s'){
							$q.=$this->
								sql_instance->escapeStr($args[$i++]);
						}elseif($type=='d'){
							$q.=$this->
								sql_instance->escapeInt($args[$i++]);
						}elseif($type=='f'){
							$q.=$this->
								sql_instance->escapeFloat($args[$i++]);
						}
					}
					$offset=$pos+2;
				}
			}
			$q.=substr($query, $offset);
		}else{
			$q=$query;
		}
		return $q;
	}
	
	function query($q){
		$args= func_get_args();
		array_shift($args);
		$statement = self::$pdo_instance->query($this->createQuery($q, $args));
		if (!$statement)
		{
			$error= self::$pdo_instance->errorInfo();
			throw new SqlError("Database query: ".$error[2]. "\nfor query: ".$q);
		}
		return $statement->fetchAll()/*->fetch(PDO::FETCH_ASSOC)*/;
		
	}
	
}

function sql($db_prefix=""){
	$config=sql_config::get();
	return new sql_query($db_prefix, $config['dsn'], 
							$config['user'], $config['password']);
}

?>