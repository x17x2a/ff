<?php
	session_start();
	ini_set('display_errors',1);
	error_Reporting(E_ALL);
	$server_dir=realpath(dirname(__FILE__)) .'/';
	$web_dir=dirname($_SERVER['PHP_SELF']).'/';
	#include_once($server_dir."config.php");
	include_once($server_dir."core/exception.php");
	include_once($server_dir."core/template.php");
	include_once($server_dir."core/common.php");
	include_once($server_dir."core/controller.php");
	#include_once($server_dir."core/spyc.php");
	include_once($server_dir."core/dispatch.php");
	include_once($server_dir."core/sql.php");
	include_once($server_dir."core/config.php");
	include_once($server_dir."core/post.php");
	include_once($server_dir."lib/HTMLPurifier/HTMLPurifier.auto.php");
	spl_autoload_register('dispatcher::loadController');
	
	try{
		dispatcher::dispatchRequest();
	}catch(Exception $e){
		handleException($e);
	}

?>
