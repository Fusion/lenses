<?php
abstract class ApplicationController extends BaseObject
{
	protected	$_controller, $_action, $_args;
	public		$options = array(); // Property
	
	function __construct($controller, $action, $args)
	{
		$this->_controller = $controller;
		$this->_action     = $action;
		$this->_args       = $args;
	}
	
	abstract function index();
	
	function render($info)
	{
		if(!empty($info[action]))
		{
			$this->_view = $info[action];
		}
	}
	
	// FIXME
	function include_helper($helper_name)
	{		
	}
}
?>
