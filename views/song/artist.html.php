<?php
if($message_type!=MESSAGE_ERROR)
{
?>
        <div id="colOne">
		<pre>
		<?php
		print "Artist: ".$artist->name.": ".$artist->songs->title;
		?>
		</pre>
		</div>
<?php
}
?>
