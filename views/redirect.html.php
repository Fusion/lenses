<?php
// Note: POST becomes GET...this is not ideal
$redirectUrl  = $page_parameters['redirect_url'];
if(false === strpos($page_parameters['redirect_url'], '?'))
	$redirectUrl .= '?';
else
	$redirectUrl .= '&';
$comma  = '';
foreach($_REQUEST as $key => $value)
{
	$redirectUrl .= $comma . $key . '=' . $value;
	$comma   = '&';
}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
		<link rel="stylesheet" type="text/css" media="screen" href="<?=Config::$path?>views/assets/util.css" />
		<link rel="stylesheet" type="text/css" media="screen" href="<?=Config::$path?>views/assets/default.css" />
		<script type="text/javascript"> 
			// Redirects are expected to break out of frame
			if(top.location != location)
			{
				top.location.href = '<?=$redirectUrl?>';
			}
			else
			{
				location.href = '<?=$redirectUrl?>';
			}
		</script>
	</head>
	<body>
	<div class='clean-info'>
	<a href='<?=$redirectUrl?>' target='_top'>Please click here if you are not automatically redirected</a>
	</div>
	</body>
</html>
