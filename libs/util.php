<?php
function formatDate($dateValue, $customFormat="M j, Y, g:i a")
{
	return date($customFormat, $dateValue);
}
?>
