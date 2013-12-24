<?php
namespace Radical\Web\Page\API\Module;

interface IAPIModule {
	static function canType($type);
	function can($method);
}