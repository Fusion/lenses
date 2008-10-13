<?php
/**
 * @package Lenses
 * @copyright (c) Chris F. Ravenscroft
 * @license See 'license.txt'
 */
class UserController extends ApplicationController
{
	function index()
	{
		// Piss off
	}

	function register()
	{
		global $validator, $db;
					
		import('com.voilaweb.core.security');
		$skipOptions = false;

		if(isset($_POST['submitform']))
		{
			$msg = '';
			if(!Security::checkCaptcha())
				$msg .= 'You did not enter the correct verification words.<br />';
			if(empty($_POST['username']))
				$msg .= 'You must enter a username<br />';
			if(empty($_POST['password']))
				$msg .= 'You must enter your password<br />';
			if($_POST['password'] != $_POST['confirmpassword'])
				$msg .= 'Your confirmation password does not match your password<br />';
			if(empty($_POST['email']) || !$validator->validee($_POST['email'])->checkemail()->isok())
				$msg .= 'Please enter a valid email address<br />';
			if(Config::$settings['users.registration.requireinvite']=='Yes')
			{
				if(empty($_POST['invite']) || !$validator->validee($_POST['invite'])->checkinvite()->isok())
					$msg .= 'Sorry, you need to enter a valid invitation code<br />';
			}
			try
			{
				if(!empty($msg))
					displayMessage(MESSAGE_ERROR, $msg);		

				$username = $_POST['username'];
				$password = $_POST['password'];
				$email    = $_POST['email'];
				$invite   = $_POST['invite'];

				$member = new Member();
				if($member->find(FIRST, "username='$username'"))
					displayMessage(MESSAGE_ERROR, "User $username already exists!");

				$member->username = $username;
				$member->password = Member::encodePassword($password);
				$member->email    = $email;
				if(!$member->save() || !$member->find(FIRST, "username='$username'"))
					displayMessage(MESSAGE_ERROR, "Sorry, there was an unexpected problem while trying to create your account :(");

				if(!empty($invite))
					$db->query("UPDATE invites SET member_id='{$member->id}',used=1 WHERE code='{$invite}'");

				$skipOptions = true;

				// We exist, therefore we should be logged in
				import('com.voilaweb.core.login');
				Login::authenticate($username, $password);
				//

				redirect('main/index', "Welcome! You should now visit your control panel...");
			}
			catch(GoodException $e) {}
		}

		if(!$skipOptions)
		{
			$this->options['header'] = 'partial_header';
			$this->options['footer'] = 'partial_footer';
			$this->options['view']   = 'register';
			
			$this->input = array(
				'username' => empty($_POST['username']) ? '' : $_POST['username'],
				'email'    => empty($_POST['email']) ? '' : $_POST['email'],
				'invite'   => empty($_POST['invite']) ? '' : $_POST['invite'],
				);

			$this->captcha = Security::presentCaptcha();
		}
	}

	function inviteme()
	{
		global $validator, $db;

		if(isset($_POST['submitform']))
		{
			try
			{
				$msg = '';
				if(empty($_POST['email']) || !$validator->validee($_POST['email'])->checkemail()->isok())
					displayMessage(MESSAGE_ERROR, 'Please enter a valid email address');
				$email = $_POST['email'];
				$inviteme = new Inviteme();
				if(!$inviteme->find(FIRST, "email='$email'"))
					displayMessage(MESSAGE_ERROR, "You already requested an invitation. Do not worry, we did not forget!");
				$inviteme->email = $email;
				$inviteme->save();
				$this->options['view'] = 'invited';
			}
			catch(GoodException $e) {}
		}

		if(empty($this->options['view']))
		{
			$this->input = array(
				'email'    => empty($_POST['email']) ? '' : $_POST['email'],
				);
			$this->options['view']   = 'inviteme';
		}
	}

	function login()
	{
		global $validator, $db;

		$skipOptions = false;

		if(isset($_POST['submitform']))
		{
			$msg = '';
			if(empty($_POST['username']))
				$msg .= 'You must enter a username<br />';
			if(empty($_POST['password']))
				$msg .= 'You must enter your password<br />';

			try
			{
				if(!empty($msg))
					displayMessage(MESSAGE_ERROR, $msg);		

				import('com.voilaweb.core.login');
				if(!Login::authenticate($_POST['username'], $_POST['password']))
					displayMessage(MESSAGE_ERROR, "Sorry, this username and password combination is invalid");

				$skipOptions = true;
				redirect(
					(empty($_POST['fwc']) ? 'main' : $_POST['fwc']) .
					'/' .
					(empty($_POST['fwa']) ? 'index' : $_POST['fwa']),
					"Welcome back {$_POST['username']}!");
			}
			catch(GoodException $e) {}
		}

		if(!$skipOptions)
		{
			$this->options['header'] = 'partial_header';
			$this->options['footer'] = 'partial_footer';
			$this->options['view']   = 'login';

			$this->input = array(
				'username' => empty($_POST['username']) ? '' : $_POST['username'],
				);
		}
	}

	function logout()
	{
		import('com.voilaweb.core.login');
		Login::logout();
		redirect('main/index', "You logged out. Bummer!");
	}

//-----------------------------------------------------------------------------
// AJAX Calls
//-----------------------------------------------------------------------------

	static function checkUsernameAvailable($username)
	{
		global $db;
		$r = $db->query("SELECT id FROM members WHERE username='$username'");
		if($r->numrows()>0)
			return false;
		return true;
	}
}

ajaxExport(UserController, checkUsernameAvailable);
?>
