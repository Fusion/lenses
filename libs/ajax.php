<?php
/**
 * @package Lenses
 * @copyright (c) Chris F. Ravenscroft
 * @license See 'license.txt'
 */
require 'libs/Sajax.php';

class Ajax
{
	function __construct()
	{
		sajax_init();
		sajax_handle_client_request();
	}
}

function ajaxExport($clName, $fnName)
{
	sajax_export($clName . '$' . $fnName);
}

?>
