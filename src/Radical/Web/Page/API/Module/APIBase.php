<?php
namespace Radical\Web\Page\API\Module;

abstract class APIBase implements IAPIModule {
	protected $data;
	protected $type;
	protected $method;

	function __construct($data,$type,$http_method='GET'){
		$this->data = $data;
		$this->type = $type;
		$this->method = $http_method;
	}
	function _canType($type){
		return in_array($type, ['json','xml','plain','ps']);
	}
	function _can($method){
		return method_exists($this, $method);
	}
	function _output_type($type){
		return $type;
	}
    function _response_container(){
        return 'response';
    }
}