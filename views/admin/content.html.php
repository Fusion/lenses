                <div id="main">
<?php
				switch($crumb) {
					case 'areas': require 'areas.html.php'; break;
					case 'areas_edit': require 'areas_edit.html.php'; break;
					case 'attachments': require 'attachments.html.php'; break;
					default: print "<div>&nbsp;</div><div>Select a section in the left-hand side menu.</div><div>&nbsp;</div>";
				}
?>
                </div>
                <!-- // #main -->
