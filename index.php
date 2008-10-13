<?php
/**
 * @package Lenses
 * @copyright (c) Chris F. Ravenscroft
 * @license See 'license.txt'
 */

global $_debug;

function __autoload($class_name)
{ 
global $inflector, $context;

	// CamelCase => camel_case
	$file_name = $inflector->inflectee($class_name)->toFile()->value();
	ClassLoader::instance()->loadClass($file_name);
} 

function import($path)
{
	ClassLoader::instance()->import($path);
}

function redirect($url, $redirect_message)
{
global $page_parameters;

	$page_parameters['action'] = 'redirect';
	$page_parameters['redirect_url'] = Config::$path . $url . '?msg=' . urlencode($redirect_message);
}

function notifyView($msg)
{
	$notify_msg = addslashes($msg);
// Copied from util.js...
	print <<<EOB
<script type="text/javascript">
	if(parent)
		var myDoc = parent.document;
	else
		var myDoc = document;
	var notifyfield = myDoc.getElementById('notifyfield');
	if(!notifyfield)
	{
		var div = myDoc.createElement('div');
		div.id = 'notifyfield';
		myDoc.body.appendChild(div);
	}

	if(parent)
		parent.document.getElementById('notifyfield').innerHTML = '{$notify_msg}';
	else
		document.getElementById('notifyfield').innerHTML = '{$notify_msg}';
</script>
EOB;
}

global $extraheaders;
function addheader($header)
{
	global $extraheaders;
	$extraheaders .= $header;
}

global $extrajs;
function addjs($js)
{
	global $extrajs;
	$extrajs .= $js;
}

function quick_log($txt)
{
	$f = @fopen('/tmp/fw.log', 'a+');
	if(!$f) return;
	@fputs($f, $txt);
	@fclose($f);
}

function include_here($path, $file)
{
	include(dirname($path).'/'.$file);
}

class GoodException extends Exception {};

// ---------------------------
// Loggers

class FirePHPLogger
{
	function __construct()
	{
		ob_start();
		include('libs/FirePHPCore/fb.php');
	}

	public function info($arg)
	{
		fb($arg, FirePHP::INFO);
	}

	public function write($arg)
	{
		fb($arg);
	}

	public function error($arg)
	{
		fb($arg, FirePHP::ERROR);
	}
}
class TmpLogger
{
	private function _write($str)
	{
		$f = fopen('/tmp/fw.log', 'a+');
		fwrite($f, $str);
		fclose($f);
	}

	public function info($arg)
	{
		$this->_write("INFO: $arg\n");
	}

	public function write($arg)
	{
		$this->_write("$arg\n");
	}

	public function error($arg)
	{
		$this->_write("ERROR: $arg\n");
	}
}
class EchoLogger
{
	public function info($arg)
	{
		echo "INFO: $arg\n";
	}

	public function write($arg)
	{
		echo "$arg\n";
	}

	public function error($arg)
	{
		echo "ERROR: $arg\n";
	}
}
class NullLogger
{
	public function info($arg) {}
	public function write($arg) {}
	public function error($arg) {}
}
class Logger
{
static private $_logger;

	private function __construct() {}

	static function instance()
	{
		if(!self::$_logger)
		{
			switch(strtolower(Config::$logger))
			{
				case 'firephp':
					self::$_logger = new FirePHPLogger();
					break;
				case 'tmp':
					self::$_logger = new TmpLogger();
					break;
				case 'echo':
					self::$_logger = new EchoLogger();
					break;
				default: // default - use in production
					self::$_logger = new NullLogger();
			}
		}
		return self::$_logger;
	}

	public function info($arg)
	{
		self::$_logger->info($arg);
	}
}

// ---------------------------
// Translation
function T($token, $context='g')
{
global $_translations, $db;
	if(!isset($_translations))
		$_translations = array();
	if(!isset($_translations[$context]))
	{
        $qry = 'SELECT token, translation FROM dict';
		$_translations[$context] = array();
        $rs = &$db->execute($qry);
        while(!$rs->EOF)
        {
			$_translations[$context][$rs->fields['token']] =  $rs->fields['translation'];
            $rs->moveNext();
        }
	}
	return (isset($_translations[$context][$token]) ? $_translations[$context][$token] : $token);
}

// ---------------------------

class ClassLoader
{
private $_classpath, $_imports, $_wildcardFiles;
static private $_classLoader;

	private function __construct()
	{
		$this->_classpath	= array(
						'models',
						'helpers',
					);
		$this->_imports		= array();
		$this->_wildcardFiles	= array();

		// Constants for classpath management
		define('SEARCH_CP',	true);
		define('NO_SEARCH',	false);

		// Constants for displayMessage()
		define('DISPLAY_BREAK',    true);
		define('DISPLAY_NO_BREAK', false);

		// Session persistence constants
		define('SESSION_PERSIST',   true);
		define('SESSION_NO_PERSIST',true);

		// Active records constants
		define('FIRST', 'f');
		define('ALL', 'a');
	}

	static function instance()
	{
		if(!self::$_classLoader)
			self::$_classLoader = new ClassLoader();
		return self::$_classLoader;
	}

	public function addToClasspath($path)
	{
		// Take precedence
		array_unshift($this->_classpath, $path);
	}

	public function import($path)
	{
		global $inflector;

		$c = $this->_getClass($path);
		if($c == '*')
		{
			$searchPath =
				$inflector->
				inflectee($this->_getPath($path))->
				toPath()->value();
			foreach($this->_classpath as $classpath)
			{
				$fullPath = $classpath . '/' . $searchPath;
				if(file_exists($fullPath) && is_dir($fullPath))
				{
					foreach(glob($fullPath . '/*.php') as $filename)
					{
						$filename = str_replace('.php', '', $filename);
						$p = strrpos($filename, '/');
						$c_name = substr($filename, $p+1);
						$this->_import($c_name, $filename, NO_SEARCH);
					}
				}
			}
		}
		else
		{
			$this->_import($c, $inflector->inflectee($path)->toPath()->value(), SEARCH_CP);
		}
	}

	function loadClass($fileName)
	{
		global $inflector, $context;

		if(isset($this->_imports[$fileName]))
		{
			include_once($this->_imports[$fileName] . '.php');
			return;
		}
		else
		{
			foreach($this->_classpath as $classpath)
			{
				$fullPath = $classpath . '/' . $fileName . '.php';
				if(file_exists($fullPath))
				{
					include_once($fullPath);
					return;
				}
			}
			throw new Exception("Class not found error: $fileName");
		}
	}

	private function _import($name, $path, $searchClasspath)
	{
		global $inflector;

		if(SEARCH_CP == $searchClasspath)
		{
			$found = false;
			foreach($this->_classpath as $classpath)
			{
				$fullPath = $classpath . '/' . $path . '.php';
				if(file_exists($fullPath))
				{
					$found = true;
					$path = str_replace('.php', '', $fullPath);
					break;
				}
			}
			if(!$found)
				throw new Exception("Class not found error: $path");
		}

		$key = $inflector->inflectee($name)->toFile()->value();
		$value = $inflector->inflectee($path)->toLower()->value();
		if(isset($this->_imports[$key]) && $this->_imports[$key] != $value)
			throw new Exception(
				"Package conflict when importing from $value.php: conflicts with " .
				$this->_imports[$key] . '.php');
		$this->_imports[$key] = $value;
	}

	private function _getClass($path)
	{
		if(false !== ($p = strrpos($path, '.')))
			return substr($path, $p+1);
		else
			return $path;
#		throw new Exception("Sorry, no class found in $path");
	}

	private function _getPath($path)
	{
		if(false !== ($p = strrpos($path, '.')))
			return substr($path, 0, $p);
		return '';
	}
}

// ---------------------------

// It's always the same old story: controller does
// its magic, asks model for time of the day,
// then we render our view.

// --------------------------- router

class Router
{
	static function run()
	{
		// Does whoever came up with magic_quotes_gpc deserve to die? Discuss!
		if(get_magic_quotes_gpc())
		{
			// Oh no!
			$inputs = array(&$_GET, &$_POST, &$_COOKIE);
			while(list($key, $value) = each($inputs))
			{
				foreach($value as $akey => $avalue)
				{
					if(is_array($avalue))
						$inputs[] = &$inputs[$key][$akey];
					else
						$inputs[$key][$akey] = stripslashes($avalue);
				}
			}
			unset($inputs);
		}
		//
		include('libs/error_reporter.php');
		register_shutdown_function('handleShutdown');
		set_error_handler("displayErrorScreen");	
		set_exception_handler("displayExceptionScreen");
		include('config.php');
		Logger::instance()->info('Using FirePHP');
		include('libs/ajax.php');
		include('libs/inflector.php');
		include('libs/validator.php');
		include('libs/util.php');
		include('libs/form_tags.php');

		global $extraheaders, $extrajs;

		if(!empty(Config::$dblayer))
		{
			include('libs/adodb/adodb.inc.php');
			include('libs/adodb/adodb-active-record.inc.php');
			include('libs/adodb/adodb-exceptions.inc.php');
			include('libs/adodb/session/adodb-session2.php');
			global $db;
			$db = NewADOConnection(
				Config::$dbengine.'://'.
				Config::$dbuser.':'.
				Config::$dbpassword.'@'.
				Config::$dbhost.'/'.
				Config::$dbname);
			ADOdb_Active_Record::SetDatabaseAdapter($db);
			ADOdb_Session::config(
				Config::$dbengine,
				Config::$dbhost,
				Config::$dbuser,
				Config::$dbpassword,
				Config::$dbname,
				array('table' => 'sessions'));
		}
		else
		{
			// TODO Time to panic here!
			die("Please implement a better error handler.");
		}

		include('libs/base/base_object.php');
		include('libs/base/application_model.php');
		include('libs/base/application_controller.php');
		include('libs/base/active_record.php');
		include('libs/base/active_ws_record.php');

		// Member structure
		include('libs/models/member.php');

		include('libs/session.php');
		Session::start();

		new Ajax();

		// All done. Initialize configuration.
		Config::initialize();

		// Let's start routing. For real.
		global $context, $controller, $action;
		$controller = $action = null;
		// Are we using prettified URLs level 2?
		if(isset($_REQUEST['rewrite']) && $_REQUEST['rewrite']=='true')
		{
			$raw_url = preg_replace('#^' . Config::$path . '#', '', $_SERVER['REQUEST_URI']);
			// Restore good old GET variables
			if(false !== ($p = strpos($raw_url, '?')))
			{
				$gets = explode('&', substr($raw_url, $p + 1));
				foreach($gets as $get)
				{
					list($k, $v) = explode('=', $get);
					$_GET[$k] = $_REQUEST[$k] = urldecode($v);
				}
			}
		}
		else
			$raw_url = str_replace(Config::$path . 'index.php/', '', $_SERVER['REQUEST_URI']);
		$contextPrefix = '';
		$args = explode('/', $raw_url);
		if(count($args)>0)
		{
			if(!empty($args[0]))
			{
				if(isset(Config::$altroutes[$args[0]]))
				{
					$context = Config::$altroutes[$args[0]];
				}
				else
				{
					$context = $args[0];
					$contextPrefix = 'app/' . $context . '/';
					ClassLoader::instance()->addToClasspath('app/' . $context . '/models');
					ClassLoader::instance()->addToClasspath('app/' . $context . '/helpers');
				}
			}
			if(!empty($context))
				array_shift($args);

			if(count($args)>0)
			{
				$controller = $args[0];
				array_shift($args);
				if(count($args)>0)
				{
					$action = $args[0];
					array_shift($args);		
				}
				else
				{
					$action = 'index';
				}	
			}
		}
		if(empty($controller))
		{
			$controller = 'main';
			$action = 'index';
		}
		global $message_type, $message_css, $message_text, $page_parameters;
		$message_type = null;
		if(false !== ($p = strpos($action, '?')))
		{
			$action = substr($action, 0, $p);
		}
		// The code above is far from perfect...nothing beats a good old GET variable
		if(isset($_GET['msg']))
		{
				displayMessage(MESSAGE_INFO, $_GET['msg'], DISPLAY_NO_BREAK);
		}

		$controller_parts = explode('_', $controller);
		$controller_class = '';
		foreach($controller_parts as $part)
		{
			$controller_class .= ucwords($part);
		}
	
		$controller_class .= 'Controller';
		include($contextPrefix . 'controllers/' . $controller . '_controller.php');
		$c_class = new $controller_class($controller, $action, $args);

		if($action)
		{
			try
			{
				$c_class->$action();
			}
			catch(GoodException $ex)
			{
				// That's OK...just a quick way to exit an action callback
				// For instance, when invoking displayMessage()
			}

			// Give local scope to controller's vars
			$members = get_object_vars($c_class);
			foreach($members as $key => $value)
			{
				$$key = $value;
			}
			
			global $_debug;
			if(!empty($_debug))
				$debug = '<br />' . $_debug;
			else
				$debug = '';

			// Now, we have everything ready...let's grab a view, if any.
			if(empty($page_parameters['action']))
			{
				$v =	$contextPrefix .
					'views/' .
					(empty($options['controller']) ? $controller : $options['controller']) .
					'/' .
					(empty($options['view']) ? 'index' : $options['view']) .
					'.html.php';
				if(!(file_exists($v)))
					self::panic('Sorry, view [' . $v . '] does not exist.');

				if($options['header'])
					include("views/{$options['header']}.html.php");
				else
					include('views/header.html.php');
				if($options['body'])
					include("views/{$options['body']}.html.php");
				else
					include($v);
				if($options['footer'])
					include("views/{$options['footer']}.html.php");
				else
					include('views/footer.html.php');
			}
			else
			{
				switch($page_parameters['action'])
				{
					case 'redirect':
						include('views/redirect.html.php');
						break;
					case 'notify':
						notifyView($page_parameters['arg']->getMessage());
						break;
					case 'api':
						break;
					default:
				}
			}
		}
	}

	static function panic($ack)
	{
		die($ack);
	}
}

define(ROOT, dirname(__FILE__));

Router::run();
?>
