<?php
if($message_type!=MESSAGE_ERROR)
{
?>
        <div id="colOne">
		<pre>
		<?php
		foreach($artists as $artist)
		{
			print "<h1>{$artist->name}</h1>";
			foreach($artist->songs as $song)
			{
				print "&nbsp;&nbsp;&nbsp;&nbsp;TITLE:{$song->title}<br />";
			}
		}
		?>
		</pre>
	</div>
<?php
}
?>
