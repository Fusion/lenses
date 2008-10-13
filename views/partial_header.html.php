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
		<!-- Light Box -->
		<link rel="stylesheet" type="text/css" media="screen" href="<?=Config::$path?>libs/shadow/css/shadowbox.css" />
		<script type="text/javascript" src="<?=Config::$path?>libs/shadow/js/shadowbox-jquery.js"></script>
		<script type="text/javascript" src="<?=Config::$path?>libs/shadow/js/shadowbox.js"></script>
		<!-- Forms -->
		<link rel="stylesheet" type="text/css" media="screen" href="<?=Config::$path?>libs/uniform/css/uni-form.css" />
		<script type="text/javascript" src="<?=Config::$path?>libs/uniform/js/uni-form.jquery.js"></script>

		<script>
		<?php print $addjs; ?>
		<?php sajax_show_javascript(); ?>
		</script>		
	</head>
	<body>
	<?php
	if($message_type)
	{
		print "<div class='{$message_css}'>{$message_text}</div>\n";
	}
	?>
