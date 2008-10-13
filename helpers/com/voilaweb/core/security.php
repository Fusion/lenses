<?php
class Security
{
	// Your friendly non-instantiable neighborhood class!
	private function __construct() {}

	static function presentCaptcha()
	{
		import('com.voilaweb.core.settings');
		require_once('libs/recaptchalib.php');
		$captchaInfo = Settings::load('users.registration.captcha');
		if($captchaInfo['users.registration.captcha.use']['true'])
		{
			return recaptcha_get_html($captchaInfo['users.registration.captcha.publickey']['value']);
		}
		return '';
	}

	static function checkCaptcha()
	{
		import('com.voilaweb.core.settings');
		require_once('libs/recaptchalib.php');
		$captchaInfo = Settings::load('users.registration.captcha');
		if($captchaInfo['users.registration.captcha.use']['true'])
		{
			$r = recaptcha_check_answer(
				$captchaInfo['users.registration.captcha.privatekey']['value'],
				$_SERVER['REMOTE_ADDR'],
				$_POST['recaptcha_challenge_field'],
				$_POST['recaptcha_response_field']);
			return ($r->is_valid);
		}
		return true;
	}
}
?>
