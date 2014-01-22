<?php
namespace Radical\Web\Page\API\Module;

class Session extends APIBase {
	function getUserDetails(){
		$output=array();
		if(\Radical\Web\Session::$auth->isLoggedIn()){
			$user = \Radical\Web\Session::$auth->getUser();
			$output['id'] = $user->getId();
			$output['name'] = $user->getUsername();
			$output['admin'] = $user->isAdmin();
		}else{
			$output['id'] = null;
			$output['name'] = 'Guest';
			$output['admin'] = false;
		}
		return $output;
	}
	function login(){
		$success = \Radical\Web\Session::$auth->Login($this->data['username'],$this->data['password']);
		return $success;
	}
}