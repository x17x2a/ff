<?php
	function error404(){
		header("HTTP/1.0 404 Not Found");
		die("404-Error");
	}
	
	function error405($req_m){
		header("HTTP/1.0 405 Method Not Allowed");
		header("Allow: $req_m");
		die("405-Error");
	}
	
	function redirect($to){
		header("Location: $to");
	}
	
	function local_redirect($to){
		global $web_dir;
		header("Location: ".$web_dir."$to");
	}
	
	function post_only(){
		if($_SERVER['REQUEST_METHOD']!='POST')
			error405('POST');
	}
	
	function inc($f){
		global $server_dir, $controllername;
		
		$local_path=($server_dir .'/web/'. $controllername ."/inc/");
		$global_path=($server_dir .'/inc/');
		
		if(file_exists($local_path . $f .'.php')){
			include_once $local_path . $f .'.php';
		}elseif(file_exists($global_path . $f .'.php')){
			include_once $global_path . $f .'.php';
		}else{
			throw new ErrorNotFound("File ". $f ." not found");
		}
	}
	
	function classHierachy($name){
		$ret=array();
		$r=new ReflectionClass($name);
		do{
			$ret[]= $r->getName();
		}while( ($r=$r->getParentClass()) != null );
		return $ret;
	}
	
	function cleanHTML($text, $filter="")
	{
		$config = HTMLPurifier_Config::createDefault();
		$config->set('Core.Encoding', 'utf-8'); // replace with your encoding
		$config->set('HTML.Doctype', 'HTML 4.01 Transitional'); // replace with your doctype
		$config->set('HTML.Allowed', $filter);
		$purifier = new HTMLPurifier($config);
		return $purifier->purify($text);
	}

?>