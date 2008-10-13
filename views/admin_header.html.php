<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title><?=T('admin_panel', 'admincp')?></title>

<!-- CSS -->
<link href="<?=Config::$path?>views/admin/style/css/transdmin.css" rel="stylesheet" type="text/css" media="screen" />
<!--[if IE 6]><link rel="stylesheet" type="text/css" media="screen" href="<?=Config::$path?>views/admin/style/css/ie6.css" /><![endif]-->
<!--[if IE 7]><link rel="stylesheet" type="text/css" media="screen" href="<?=Config::$path?>views/admin/style/css/ie7.css" /><![endif]-->

<!-- JavaScripts-->
<script type="text/javascript" src="<?=Config::$path?>views/admin/style/js/jquery.js"></script>
<script type="text/javascript" src="<?=Config::$path?>views/admin/style/js/jNice.js"></script>
</head>

<body>
	<div id="wrapper">
    	<!-- h1 tag stays for the logo, you can use the a tag for linking the index page -->
    	<h1><a href="#"><span><?=T('admin_panel', 'admincp')?></span></a></h1>
        
        <!-- You can name the links with lowercase, they will be transformed to uppercase by CSS, we prefered to name them with uppercase to have the same effect with disabled stylesheet -->
        <ul id="mainNav">
        	<li><a href="<?=url_for('admin/index')?>"<?php if($action=='index'): ?> class="active"<?php endif?>><?=T('nav_homepage', 'admincp')?></a></li>
        	<li><a href="<?=url_for('admin/users')?>"<?php if($action=='users'): ?> class="active"<?php endif?>><?=T('nav_users', 'admincp')?></a></li>
        	<li class="logout"><a href="<?=url_for('.')?>"><?=T('nav_exit', 'admincp')?></a></li>
        </ul>
        <!-- // #end mainNav -->
        
        <div id="containerHolder">
			<div id="container">
        		<div id="sidebar">
                	<ul class="sideNav">
<?php
	switch($action) {
		case 'users':
			$parentname = T('h_users', 'admincp');
?>
				<li><a<?php if($crumb=='registration'): ?> class="active"<?php endif?> href="<?=url_for('admin/users/registration')?>"><?=T('nav_registration', 'admincp')?></a></li>
<?php
			break;
		default:
			$parentname = T('nav_index', 'admincp');
			$crumb      = T('nav_top', 'admincp');
?>
                    	<li><a href="#">..</a></li>
<?php
	}
?>
                    </ul>
                    <!-- // .sideNav -->
                </div>    
                <!-- // #sidebar -->
                
<?php
    if($message_type)
    {
        print "<div class='{$message_css}'>{$message_text}</div>\n";
    }
?>

                <!-- h2 stays for breadcrumbs -->
                <h2><a href="#"><?=$parentname?></a> &raquo; <a href="#" class="active"><?=ucfirst($crumb)?></a></h2>
