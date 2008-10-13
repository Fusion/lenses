                <div id="main">
<?php
				switch($crumb) {
					case 'registration': require 'registration.html.php'; break;
					default: print "<div>&nbsp;</div><div>Select a section in the left-hand side menu.</div><div>&nbsp;</div>";
				}
?>
                </div>
                <!-- // #main -->
