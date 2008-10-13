<?php
/**
 * @package Lenses
 * @copyright (c) Chris F. Ravenscroft
 * @license See 'license.txt'
 */
// Modules types
require 'libs/base/editor.php';

class Modules
{
	static function load($name)
	{
		$modulePath = 'libs/modules/'.$name.'/'.$name.'.mod.php';
		require $modulePath;
		$getModule = 'getModule_'.$name;
		return $getModule();
/*
		global $db;
		$db->setFetchMode(ADODB_FETCH_ASSOC);

		$qry = "SELECT * FROM modules WHERE type='$type' AND name='$name'";
		$rs = &$db->execute($qry);
		if($rs->EOF)
			throw new Exception("Unknown module: '$name' ['$type']");
*/
	}
}
?>
