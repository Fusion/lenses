<?php
class ToolClass
{
	function __construct()
	{
		require '../config.php';
	}

	function getVar($var_name)
	{
		return Config::$$var_name;
	}
}
?>
