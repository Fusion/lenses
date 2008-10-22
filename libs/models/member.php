<?php
class Member extends ActiveRecord
{
	function __construct()
	{
		parent::__construct();
	}

	static function encodePassword($password)
	{
		return sha1(Config::$salt . $password);
	}

	static function authenticated()
	{
		return Session::defined(get_class());
	}
}
?>
