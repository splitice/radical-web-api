<?php
namespace Radical\Web\Page\Controller;

use Radical\Core\Libraries;
use Radical\Web\Page\Handler\PageBase;

class API extends PageBase {
	protected $module;
	protected $method;
	protected $type;
	protected $error;
	protected $query;
	protected $classPrefix;
	
	function __construct($data, $classPrefix = '\\*\\Web\\Page\\API\\Module\\'){
		$this->classPrefix = $classPrefix;
		$this->type = $data['type'];
		if(isset($data['error'])){
			$this->error = $data['error'];
		}else{
			$this->module = $data['module'];
			$this->method = $data['method'];
			$this->query = $data['query'];
		}
	}
	private function make_object($http_method, $type){
		//Find Class
		$c = $this->classPrefix.$this->module;
		$classes = Libraries::get($c);
		if(!count($classes)){
			return null;
		}
		$c = $classes[0];
		return new $c($this->query, $type, $http_method);
	}

	function _addChild($v, $k,$xml){
        if(is_object($v)){
            $d = array($k=>get_object_vars($v));
            array_walk_recursive($d, array ($this, '_addChild'), $xml);
        }else{
		    $xml->addChild($k,$v);
        }
	}
	function toXML(array $array){
		$xml = new \SimpleXMLElement('<API/>');
		array_walk_recursive($array, array ($this, '_addChild'), $xml);
		return @$xml->asXML();
	}

    protected function process_exception(\Exception $ex){
        $ret = array();
        $ret['error'] = $ex->getMessage();
        if($ex->getCode()){
            $ret['code'] = $ex->getCode();
        }
        return $ret;
    }

	private function error($message, $type){
		if(is_string($message)){
			$message = array('error'=>$message);
		}

		return $this->do_output($message, $type);
	}

	private function do_output($ret, $type){
		$headers = \Radical\Web\Page\Handler::$stack->top()->headers;
		switch($type){
			case 'json':
				if(isset($_GET['callback'])){
					echo $_GET['callback'],'(';
				}
				echo \Radical\Basic\JSON::encode($ret);
				if(isset($_GET['callback'])){
					echo ');';
					$headers->Add('Content-Type','text/javascript');
				}else{
					$headers->Add('Content-Type','application/json');
				}
				break;

			case 'xml':
				echo $this->toXML($ret);
				$headers->Add('Content-Type','text/xml');
				break;

			case 'ps':
				echo serialize($ret);
				$headers->Add('Content-Type','text/plain');
				break;

			case 'plain':
				break;

			default:
				if(isset($ret['response'])){
					echo $ret['response'];
				}elseif(isset($ret['error'])){
					echo \Radical\Basic\JSON::encode($ret);
				}
		}
	}
	
	/**
	 * Handle GET request
	 *
	 * @throws \Exception
	 */
	function execute_request($http_method){
		$ret = array();

		$type = 'json';
		if($this->error){
			return $this->error($this->error, $this->type);
		}else{
			$object = $this->make_object($http_method, $this->type);

			if(!$object){
				return $this->error('Invalid Module: '.$this->module, $this->type);
			}

			$type = $object->_output_type($this->type);

			if(!$object->_canType($this->type)){
				return $this->error('Invalid response type: '.$this->type, $this->type);
			}

			if(!$object->_can($this->method)){
				return $this->error('Invalid Method',$this->type);
			}

			try {
				$ret[$object->_response_container()] = $object->{$this->method}();
			}catch(\Exception $ex){
				if(ob_get_level()) ob_clean();
				$ret = $this->process_exception($ex);
				return $this->error($ret,$this->type);
			}
		}

		return $this->do_output($ret, $type);
	}

	function can($method){
		return in_array($method,['GET','POST']);//TODO: more options?
	}
}