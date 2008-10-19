<?php
if($message_type!=MESSAGE_ERROR)
{
?>
        <div id="colOne">
		<pre>
		<?php
		foreach($songs as $song)
		{
			print "<h1>{$song->title}</h1>";
			print "Author: {$song->artist->name}<br />";
			print "Genre: {$song->genre->name}<br />";
		}
		?>
		</pre>
	</div>
<?php
}
?>
