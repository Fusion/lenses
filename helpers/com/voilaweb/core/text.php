<?php
/**
 * @package Lenses
 * @copyright (c) Chris F. Ravenscroft
 * @license See 'license.txt'
 */
class Text
{
	// Your friendly non-instantiable neighborhood class!
	private function __construct() {}

	static function escape($sourceText)
	{
		return str_replace(
			array('<', '>'),
			array('&lt;', '&gt;'),
			$sourceText);
	}

	static function unescape($sourceText)
	{
		return str_replace(
			array('&lt;', '&gt;'),
			array('<', '>'),
			stripslashes($sourceText));
	}

	static function format($sourceText)
	{
		return str_replace(
			array("\n"),
			array("<br />\n"),
			$sourceText);
	}
}
?>
