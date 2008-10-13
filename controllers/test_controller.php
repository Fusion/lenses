<?php
/**
 * @package Lenses
 * @copyright (c) Chris F. Ravenscroft
 * @license See 'license.txt'
 */

class MixMe
{
	function a($c)
	{
		print "B($c)<br />";
		return "You passed $c";
	}
}

class TestController extends ApplicationController
{
	function index()
	{
	}

	function testmixin()
	{
		$this->mixin(MixMe);
		print $this->a('hello');
	}

	function crunchbase()
	{
		if(isset($_POST['submitform']))
		{
			try
			{
				$msg = '';
				if(empty($_POST['keyword']))
					$msg .= 'You must enter a keyword to perform a search<br />';

				$this->input = array(
					'keyword' => empty($_POST['keyword']) ? '' : $_POST['keyword'],
					);

				if(!empty($msg))
					displayMessage(MESSAGE_ERROR, $msg);

				$c = new Crunchbase();
				$this->results = $c->find(ALL, $_POST['keyword']);
			}
			catch(GoodException $e) {}
		}
		$this->options['view']   = 'crunchbase';
	}
}
?>
