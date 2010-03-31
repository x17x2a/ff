<?php
	/*class post {
		private static $nice_names=array(

			'type'=> array(
				'int'=>FILER_VALIDATE_INT,
				'bool'=>FILTER_VALIDATE_BOOLEAN,
				'float'=>FILTER_VALIDATE_FLOAT,
				'url'=>FILTER_VALIDATE_URL,
				'email'=>FILTER_VALIDATE_EMAIL,
				'string'=>FILTER_DEFAULT
			),

			'filter' => array(
				'sanitize' => FILTER_SANITIZE_STRING,
				'escape' => FILTER_SANITIZE_ENCODED
			)

			/*'flag'=>array(
				
			//)//*
		);

		private $form=array();

		private static function process(&$array, $data){
			if(array_key_exists($vs, self::$nice_names['type'])){
		    	$array['type'] |= self::$nice_names['type'][$vs];
			}#else throw error

		}

		function __construct($controller_name){
			global $server_dir;
			$yaml=sfYaml::load( $server_dir .'/web/'. 
								$controller_name .'/form.yml');

			foreach($yaml as $form_name=>$form){
				foreach($form as $name=>$data){
				
					if(is_array($data)){
						foreach($data as $k=>$v){
							if($k === 'type'){
								$this->form[$form_name][$name]['type'] |= 
									self::$nice_names['type'][$v];
							}elseif($k === 'filter'){
								if(is_array($v)){
									foreach($v as $i){
										$this->form[$form_name][$name]['type'] |= 
											self::$nice_names['type'][$i];
									}
								}else{
									$this->form[$form_name][$name]['type'] |= 
										self::$nice_names['type'][$v];
								}
							}
						}
					}else{
						#TODO: check if $data exists as a key
						$this->form[$form_name][$name] = self::$nice_names[$data];
					}
				
				
				}
			}

			/*
			$data=sfYaml::load($yaml);
			
			$type_array=array();

			foreach($data as $k=>$v){
				$type_array[$k]=array();
				
				foreach($v as $ks=>$vs){
					if(is_array($vs)){
						foreach($vs as $kss=>$vss){
						}
					}else{
						
					}

				}
			}
			///
		}


	}
*/

	class post /*implements ArrayAccess*/ {
		private static $nice_names=array();
	
		private $form=array();

		private function parseFields($name, $fields){
			foreach($fields as $name_=>$data){
				if(is_array($data)){

					if(isset($data['filter']) and ! is_array($data['filter'])){
						$data['filter']=array($data['filter']);
					}

					$this->form=$data;
				}else{
					$this->form[$name][$name_]=array(
						'type'=>$data,
						'filter'=>array(),
						'default'=>null
					);
				}
			}
		}

		private function checkDefault($k, $v){
			if(! isset($_POST[$k])){
            	if(isset($v['default'])){
                	return $v['default'];
				}else{
                	throw some Exception('Required value not set');
				}
			}else{
            	return $_POST[$k];
            }		
		}

		private function checkType($value, $k, $v){
			if(! isset($v['type'])){
            	throw new Exception('Type required');
            }

            $tmp=filter_var($value, self::$nice_names[$v['type']]);
            if($tmp===false){
                throw new Exception('Type error');
            }
            return $tmp;
		}

		private function applyFilter($value, $filter){
			if(! isset(self::$nice_names[$filter])){
				throw new Exception('Unknown Filter');
			}
			return 
			 filter_var($value, FILTER_FLAG_NONE, self::$nice_names[$filter]);
		}
		
		private function checkBounds($value, $min, $max){
			$type=gettype($value);
			if($type=='string')
				$value=strlen($value);
			elseif(! ($type=='integer' || $type=='double'))
				throw new Exception('Unsupported type for bound-checking');
			
			if(isset($min) and $value < $min){
				throw new Exception('Value not in range');
			}
			if(isset($max) and $value > $max){
				throw new Exception('Value not in range');
			}
		}
		
		private function checkRegex($value, $regex){
			if(! isset($regex))
				return;
				
			if(gettype($value) != 'string'){
				throw new Exception('Only strings are allowed '.
									'to be pattern-matched');
			}
			
			if(is_array($regex)){
				foreach($regex as $r){
					$this->regexHelper($value, $r);
				}
			}else{
				$this->regexHelper($value, $regex);
			}
		}#where
		private function regexHelper($value, $r){
			if(! preg_match($r, $value)){
				throw new Exception('Value does not match pattern');
			}
		}

		function __get($name){
			$ret=array();
			foreach($this->form[$name] as $k=>$v){
				
				#check for default values
				$ret[$k]=$this->checkDefault($k, $v);

				#typechecking
				$ret[$k]=$this->checkType($ret[$k], $k, $v);				

				#filter
				foreach($v['filter'] as $filter){
					$ret[$k]=$this->applyFilter($ret[$k], $filter);
				}
				
				#min-max
				$this->checkBounds($ret[$k], @$v['min'], @$v['max']);
				
				#regex
				$this->checkRegex($ret[$k], @$v['pattern']);
			}
			return $ret;
		}


		function __construct(){
			global $server_dir;
			
			$yaml=sfYaml::load( $server_dir .'/web/'. 
								$controller_name .'/form.yml');
		
			foreach($yaml as $name=>$fields){
				$this->parseFields($name, $fields);
			}
		}
	}

?>
