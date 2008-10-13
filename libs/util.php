<?php
/**
 * @package Lenses
 * @copyright (c) Chris F. Ravenscroft
 * @license See 'license.txt'
 */
function formatDate($dateValue, $customFormat="M j, Y, g:i a")
{
	return date($customFormat, $dateValue);
}
?>
