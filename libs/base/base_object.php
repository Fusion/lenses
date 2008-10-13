<?php
class BaseObject
{
/*
 * The code below provides very basic mixin support
 * Here is some usage example:
 * class MixMe
 * {
 *        function b($c)
 *        {
 *                return "You passed $c";
 *        }
 * }
 *
 * class TestController extends ApplicationController
 * {
 *        function index()
 *        {
 *                $this->mixin(MixMe);
 *                print $this->b('hello');
 *        }
 * }
 *
 */
protected $_methods = array();

	public function mixin($className)
	{
		$o = new $className();
		$c = new ReflectionObject($o);
		foreach($c->getMethods() as $m)
		{
			$this->_methods[$m->getName()] = $o;
		}
	}

	function __call($method, $arguments)
	{
		if(!isset($this->_methods[$method]))
			throw new Exception("Attempting to call unknown mixin method: $method");
		return $this->_methods[$method]->$method(implode(',', $arguments));
	}
}
?>
