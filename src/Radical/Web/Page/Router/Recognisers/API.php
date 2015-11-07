<?php
namespace Radical\Web\Page\Router\Recognisers;

use Radical\Core\Libraries;
use Radical\Utility\Net\URL;
use Radical\Web\Page\Handler;
use Radical\Web\Page\Router\IPageRecognise;

class API implements IPageRecognise {
	const DEFAULT_TYPE = 'json';
	static function error($string,$type){
		return Handler::Objectify ( 'API', array('error'=>$string,'type'=>$type) );
	}
	static function recognise(URL $url){
		$url = $url->getPath();
		if($url->firstPathElement() == 'api'){
			$url->removeFirstPathElement();
			$module = $url->firstPathElement();
			$url->removeFirstPathElement();
			$method = $url->firstPathElement();
			$url->removeFirstPathElement();
			$type = static::DEFAULT_TYPE;
			
			if(count($parts = explode('.',$method)) > 1){
				if(count($parts) != 2){
					throw new \Exception('Invalid API Method');
				}
				$method = $parts[0];
				$type = $parts[1];
			}
			
			//Query Data
			$query = $url->getQuery();
			if(!isset($_SERVER['HTTP_CONTENT_TYPE']) || $_SERVER['HTTP_CONTENT_TYPE'] != 'application/octet-stream'){
				if($query){
					$query = array_merge($query,$_POST);
				}else{
					$query = $_POST;
				}
			}

			return Handler::Objectify ( 'API', array('module'=>$module,'method'=>$method, 'type'=>$type, 'query'=>$query) );
		}
	}
}