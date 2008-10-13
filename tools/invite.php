<?php
for($i=0;$i<10;$i++)
print "INSERT INTO invites SET code='".md5(uniqid())."';\n";
?>
