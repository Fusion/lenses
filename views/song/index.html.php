<?php
if($message_type!=MESSAGE_ERROR)
{
?>
        <div id="colOne">
		<pre>
		<?php
		print "Song: ".$song->title." by ".$song->artist->name." (".$song->genre->name.")";
		?>
		</pre>
		</div>
<?php
}
?>
