<?php
namespace Radical\Web\Page\API\Module;

interface IAPIModule {
	function _canType($type);
	function _can($method);
}