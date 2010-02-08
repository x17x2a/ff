<?php
	error_Reporting(E_ALL);
	$controllername;
	$actionname;
	class dispatcher{
		
		static function loadController($controller){
			global $server_dir;

			$path = "/web/".$controller."/".$controller.".php";
			$absolutepath = realpath($server_dir.$path);
			if (file_exists($absolutepath)){
				include_once($absolutepath);
			}else{
				throw new ErrorNotFound("Controller '".$path."' not found.");			}
		}

		static function executeControllerAction($controller, $action, $args){
			self::loadController($controller);
			if(!class_exists($controller))
				throw new ErrorNotFound("Controller ".$controller.
					" not found.");
			if(!method_exists($controller, $action))
				throw new ErrorNotFound("Classmethod ".$controller.".".
					$action." not found.");
			
			//TODO: find out if this really can't throw a exception
			$method = new ReflectionMethod($controller, $action);
			$parametercount = $method->getNumberOfRequiredParameters();
			if (count($args) < $parametercount)
				throw new ErrorNotAllowed("can't call ".
					$controller.".".$action."\n".
					"Got ".count($args)." parameter but at least needs ".
					$parametercount." arguments.");#400 Bad Request
			
			$instance = new $controller();
			call_user_func_array(array($instance, $action), $args);
		}
		
		static function dispatchRequest(){
			global $actionname, $controllername;
			$controller;
			$action;
			$args = array();
			
			if (!isset($_GET['id'])){
				$controller = "main";
				$action = "index";
			}else{
				$parts = preg_split('_/_', $_GET['id'], -1,
										PREG_SPLIT_NO_EMPTY); 
				if (count($parts) == 0){
					$controller = "main";
					$action = "index";
				}elseif(count($parts) == 1){
					$controller = $parts[0];
					$action = "index";
				}else{ // count($parts)>2
					$controller = $parts[0];
					$action = $parts[1];
					$args = array_slice($parts, 2); // all the rest
				}
			}
			$controllername = $controller;
			$actionname = $action;
			self::executeControllerAction($controller, $action, $args);
		}
	}
?>
