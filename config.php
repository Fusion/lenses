<?php
/**
 * @package Lenses
 * @copyright (c) Chris F. Ravenscroft
 * @license See 'license.txt'
 */
class Config
{
	static public
			// Email address for the application's developer - use for notification
			$developer	= 'bogus@voilaweb.com',

			// Web application root path
			$path		= '/lenses/',

			// Database layer, engine, etc. as used by adodb
			$dblayer	= 'native',
			$dbengine	= 'mysql',
			$dbhost		= 'localhost',
			$dbname		= 'demo',
			$dbuser		= 'demo',
			$dbpassword	= 'demo',
			$dbprefix	= '',

			// Salt used for sha1 encryption
			$salt		= 'demo',

			// This site's cookie name
			$cookie		= 'demo',

			// When validating an email address, do we go so far as to
			// actually ask the MTU?
			$fullemailcheck	= false,

			// Which method are we using for logging, if any?
			$logger		= 'Tmp',

			// Debug active records?
			$debugger	= false,

			// Email error notifications to developer?
			$notifyonerror	= true,

			$webcli         = false,
			$webcliips	= array(),

			// Special context remapping
			$altroutes	= array(
				// ...or how a context can be remapped
				// note that providing an empty alias means 'default context'
				'admin'		=>	'',
				'main'		=>	'',
				'user'		=>	'',
				'test'		=>	'',
				'song'		=>	'',
			);

	static public $settings;
	static function initialize()
	{
		global $db;
		if(empty($db)) return false;
                $db->setFetchMode(ADODB_FETCH_ASSOC);

		$qry = 'SELECT * FROM settings';
		self::$settings = array();
		$rs = &$db->execute($qry);
		while(!$rs->EOF)
		{
			self::$settings[$rs->fields['name']] = $rs->fields['value'];
			$rs->moveNext();
		}
		return true;
	}
}
?>
