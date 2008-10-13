<?php
/**
 * @package Lenses
 * @copyright (c) Chris F. Ravenscroft
 * @license See 'license.txt'
 */
class Session
{
	public	$userId,
		$loggedIn;

	function __construct($userId = -1)
	{
		// If we are passing a new user id OR have no user id yet...
		if($userId != -1 || empty($_SESSION['uid']))
			$_SESSION['uid'] = $userId;
		$this->userId = $_SESSION['uid'];
	}

	static function start()
	{
		session_start();
		// 'in' is set when we create this session
		// If it isn't, then we just arrived.
		if(!self::defined('In'))
		{
			// Create session object in database
			$_SESSION['In'] = true;
			// If we have a cookie, then we're coming back, aren't we?
			if(!empty($_COOKIE[Config::$cookie]))
			{
				$cookie = unserialize($_COOKIE[Config::$cookie]);
				// TODO MEMBER NEEDS TO BE MOVED TO LIBS
			}
		}
		else
		{
			// We are not coming back...
			// Are we a member?
			if(self::defined('Member'))
			{
				// If no cookie set, then create one and persist
				if(empty($_COOKIE[Config::$cookie]))
				{
					$persistKey = sha1(time());
					$member = self::get('Member');
					// Update member with SID so that cookie can persist
					$member->persist_key = $persistKey;
					$member->save();
					// Create cookie for peristence
					setCookie(
						Config::$cookie,
						serialize(
							array(
								'uid' => $member->id,
								'persist_key' => $member->persist_key
							)
						),
						time()+60*60*24*365
					);
				}
			}
		}
	}

	static function persist($obj)
	{
		$_SESSION[$obj->name] = $obj;
	}

	static function delete()
	{
		// Persistence cookie
		unset($_COOKIE[Config::$cookie]);
		setCookie(Config::$cookie, '', time()-3600);
		// Current session
		foreach($_SESSION as $key => $value)
		{
			unset($_SESSION[$key]);
		}
		session_destroy();
	}

	static function defined($key)
	{
		return !(empty($_SESSION[$key]));
	}

	static function get($key)
	{
		return empty($_SESSION[$key]) ? null : $_SESSION[$key];
	}

	function logMeIn($username, $password)
	{
		global $db, $salt;
		if(empty($_SESSION['username']) || empty($_SESSION['password']))
			return false;
		if(	$_SESSION['username'] != $username ||
			$_SESSION['password'] != sha1(Config::$salt . $password))
			return false;
		$this->loggedIn = true;
		return true;
	}
}

#global $user;
#$user = new Session();
?>
