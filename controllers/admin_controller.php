<?php
/**
 * @package Lenses
 * @copyright (c) Chris F. Ravenscroft
 * @license See 'license.txt'
 */

class AdminController extends ApplicationController
{
	function _checkAdmin()
	{
		import('com.voilaweb.core.login');
		$member = Login::info();
		// Note that to make your life easier, user #1 is admin.
		// This is dangerous, too!
		if($member->admin!=1 && $member->id!=1)
		{
			$this->options['body'] = 'empty';
			displayMessage(MESSAGE_ERROR, "Dude! No!");
		}
	}

	function index()
	{
		$this->_checkAdmin();
		$this->options['header'] = 'admin_header';
		$this->options['footer'] = 'admin_footer';
	}

	function users()
	{
		$this->_checkAdmin();
		$this->options['header'] = 'admin_header';
		$this->options['footer'] = 'admin_footer';
		$this->options['view']   = 'users';
		if(!empty($this->_args[0]))
		{
			$this->crumb = $this->_args[0];
			switch($this->crumb)
			{
				case 'registration':
					import('com.voilaweb.core.settings');
					if(isset($_POST['submitform']))
					{
						Settings::save('users.registration', $_POST);
					}
					$this->fields = Settings::load('users.registration');
					break;
			}
		}
		else
			$this->crumb = 'Index';
	}
}
?>
