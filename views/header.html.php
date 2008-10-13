<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
		<title>!</title>
		<!-- The Basics -->
		<link rel="stylesheet" type="text/css" media="screen" href="<?=Config::$path?>views/assets/util.css" />
		<link rel="stylesheet" type="text/css" media="screen" href="<?=Config::$path?>views/assets/default.css" />
		<script type="text/javascript" src="<?=Config::$path?>libs/jquery.js"></script>
		<script type="text/javascript" src="<?=Config::$path?>libs/util.js"></script>
		<!-- Init -->
		<?php print $extraheaders; ?>
		<script type="text/javascript">
			<?php print $extrajs; ?>

		<?php sajax_show_javascript(); ?>
		</script>		
	</head>
	<body>
<div id="main">
	<div id="content">

<?php
	$cp = Config::$path;
	if(!Member::authenticated())
	{
		print <<<EOB
<div class='clean-info'>You are not logged in or *gasp* you have not registered yet.
<a rel='shadowbox' href="{$cp}user/login/">Log in</a> or <a rel='shadowbox' href="{$cp}user/register/">Register now</a>
</div>

EOB;
	}
	else
	{
		print <<<EOB
<span style='float:right'>
<a href="{$cp}user/logout/">Log out</a>
</span>
<div style='clear:both'></div>
EOB;
	}

	if($message_type)
	{
		print "<div class='{$message_css}'>{$message_text}</div>\n";
	}
?>
