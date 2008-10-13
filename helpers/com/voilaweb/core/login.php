<?php
/**
 * @package Lenses
 * @copyright (c) Chris F. Ravenscroft
 * @license See 'license.txt'
 */
class Login
{
	// Your friendly non-instantiable neighborhood class!
	private function __construct() {}

	static function authenticate($username, $password, $persist = SESSION_PERSIST)
	{
		$member = new Member();
		if($member->find(
			FIRST,
			sprintf("username='$username' AND password='%s'",
			Member::encodePassword($password))))
		{
			if(SESSION_PERSIST == $persist)
			{
				$member->sessionPersist();
			}
			return true;
		}
		return false;
	}

	static function updateSession($member)
	{
		$member->sessionPersist();
	}

	static function logout()
	{
		Session::delete();
	}

	static function info()
	{
		return Session::get('Member');
	}
}
?>
