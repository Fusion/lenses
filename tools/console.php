<?php
/**
 * @package Lenses
 * @copyright (c) Chris F. Ravenscroft
 * @license See 'license.txt'
 */
global $WHEREAMI, $medium;
$postinput = '';
if(empty($_ENV['SHELL']))
{
	// Web invocation
	$WHEREAMI = dirname(getcwd());
	include($WHEREAMI.'/config.php');
	$ip = $_SERVER['REMOTE_ADDR'];
	if(!Config::$webcli || !in_array($ip, Config::$webcliips))
		die("Sorry, command-line only!");
	$medium = 'w';
	$prompt = '<body onload="document.getElementById(\'input\').focus();"><form method="post" action="console.php">Console > <input type="text" name="input" id="input" value="" style="width:800px;" /></form>';
	if(!empty($_POST['input']))
		$postinput = $_POST['input'];
}
else
{
	// Shell
	if(empty($argv[1]))
		die("Use 'please' rather than invoking this script directly!\n");
	$WHEREAMI = $argv[1];
	include($WHEREAMI.'/config.php');
	$medium = 'c';
	$prompt = 'Console > ';
}
$cli = array(
	'halp' => 'lolcat',
	'create' => array(
		'model' => array(
			'-1' => 'create model [context] <name>',
			'1' => "create_model",
			'2' => "create_model"),
		'controller' => array(
			'-1' => 'create controller [context] <name>',
			'1' => "create_controller",
			'2' => "create_controller"),
		'view' => array(
			'-1' => 'create view [context] <name> <page>',
			'2' => "create_view",
			'3' => "create_view"),
		'mvc' => array(
			'-1' => 'create mvc [context] <name>',
			'1' => "create_mvc",
			'2' => "create_mvc"),
		'helper' => array(
			'-1' => 'create helper <name>',
			'1' => "create_helper"),
		'setting' => array(
			'-1' => 'create setting <name> <value> <type> <options> <group> "<description>"',
			'6' => "create_setting"),
		'test' => array(
			'awesome' => 1,
		),
	),
	'delete' => array(
		'setting' => array(
			'-1' => 'delete setting <name>',
			'1' => "delete_setting"),
	),
	'set' => array(
		'setting' => array(
			'-1' => 'set setting <name> <value>',
			'2' => "set_setting"),
	),
	'show' => array(
		'settings' => array(
			'0' => "show_settings"),
		'setting' => array(
			'-1' => 'show setting <prefix>',
			'1' => "show_setting"),
	),
	'migrate' => array(
		'up' => array(
			'-1' => 'migrate up [version]',
			'0' => "migrate_model_up",
			'1' => "migrate_model_up"),
		'down' => array(
			'-1' => 'migrate down <version>',
			'1' => "migrate_model_down"),
	),
);

$depth = 0;

$innerWelcome = ($medium == 'w' ? '' : "Type '.' to exit.");
echo _format("Welcome to the Console. $innerWelcome\n");

$stdin = fopen('php://stdin', 'r');
echo _format($prompt);
$tokens = array();
$incomplete = false;
while(($line = fgets($stdin)) || !empty($postinput))
{
	if(!empty($postinput))
	{
		$line = $postinput;
		$postinput = '';
	}
	$probe = &$cli;
	$collectingArgs = false;
	$args = array();
	$line = str_replace(array("\n", "\r"), array('', ''), $line);
	if(empty($line))
	{
		$incomplete = false;
		echo _format($prompt);
		continue;
	}
	if($line == '.') break;
	$input = explode(' ', $line);
	if($incomplete)
	{
		if(empty($line))
		{
			$input = $tokens;
		}
		else
		{
			$input = array_merge($tokens, $input);
		}
	}
	$tokens = array();
	$incomplete = true;
	$curArgStr = '';
	foreach($input as $token)
	{
		if($collectingArgs)
		{
			if(empty($curArgStr))
			{
				if($token{0} == '"')
					$curArgStr = substr($token, 1);
				else
					$args[] = $token;
			}
			else
			{
				if($token{strlen($token)-1} == '"')
				{
					$args[] = $curArgStr.' '.substr($token, 0, strlen($token)-1);
					$curArgStr = '';
				}
				else
					$curArgStr .= ' '.$token;
			}
		}
		else if(isset($probe[$token]))
		{
			$tokens[] = $token;
			$probe = &$probe[$token];	
			if(!is_array($probe))
			{
				$arrptr = array($probe);
				$incomplete = false;
				$collectingArgs = true;
			}
			else
			{
				$keys = array_keys($probe);
				if(is_int($keys[0]))
				{
					$arrptr = &$probe;
					$incomplete = false;
					$collectingArgs = true;
				}
			}
		}
		else
		{
			echo _format("Syntax error: {$token}?\n");
			break;
		}
	}
	if($incomplete)
	{
		$comma = '';
		echo _format("Syntax: " . implode(' ', $tokens) . " < ");
		foreach($probe as $potentialToken => $whatever)
		{
			echo _format("$comma$potentialToken");
			$comma = ' | ';
		}
		echo _format(" >\n");
	}
	else if($collectingArgs)
	{
		$idx = count($args);
		if(!empty($arrptr[$idx]))
		{
			$fn = $arrptr[$idx];
			$fn($args);
		}
		else
		{
			echo _format("Wrong number of arguments: ".$arrptr[-1]."\n");
			$incomplete = true;
		}
	}
	if($medium != 'w')
		echo $prompt;
	if($incomplete)
	{
		if(0 < count($tokens))
			$value = implode(' ', $tokens) . ' ';
		else
			$value = '';
		if($medium != 'w')
			echo $value;
		else
			echo "<script>\ndocument.getElementById('input').value = '$value';\n</script>\n";
	}
}

if($medium != 'w')
	echo _format("Good bye!\n");

function _format($str)
{
global $medium;

	if($medium == 'w')
		$str = str_replace("\n", "<br />\n", $str);
	return $str;
}

function _ensure_context($contextname)
{
global $WHEREAMI;

	if(file_exists($WHEREAMI.'/app/'.$contextname))
	{
		if(is_dir($WHEREAMI.'/app/'.$contextname))
			return true;
		echo _format("This context exists but it's a file, not a directory!\n");
		return false;
	}
	if(
		!mkdir($WHEREAMI.'/app/'.$contextname) ||
		!mkdir($WHEREAMI.'/app/'.$contextname.'/models') ||
		!mkdir($WHEREAMI.'/app/'.$contextname.'/controllers') ||
		!mkdir($WHEREAMI.'/app/'.$contextname.'/views')
	)
	{
		echo _format("Problem creating context '$contextname'\n");
		return false;
	}
	return true;
}

function _create_file($filename, $contents='')
{
global $WHEREAMI;

	$filename = $WHEREAMI.'/'.$filename;

	$f = fopen($filename, 'w+');
	if(!$f)
	{
		echo _format("Unable to create $filename\n");
		return false;
	}
	if(!fputs($f, $contents))
	{
		echo _format("Unable to create contents of $filename\n");
		return false;
	}
	fclose($f);
	return true;
}

function _open_db()
{
	global $db, $WHEREAMI;
	if(!isset($db))
	{
		include($WHEREAMI.'/libs/adodb/adodb.inc.php');
		$db = NewADOConnection(
			Config::$dbengine.'://'.
			Config::$dbuser.':'.
			Config::$dbpassword.'@'.
			Config::$dbhost.'/'.
			Config::$dbname);
	}
	if(!$db)
	{
		echo _format("Unable to access database. Bad configuration in config.php?\n");
		return false;
	}
	return true;
}

function _create_dict()
{
	global $db, $dict, $WHEREAMI;
	if(!isset($dict))
	{
		include($WHEREAMI.'/libs/adodb/adodb-datadict.inc.php');
		$dict = NewDataDictionary($db);
	}
	return $dict;
}

function _create_yml_parser()
{
	global $parserLoaded, $WHEREAMI;
	if(!isset($parserLoaded))
	{
		include($WHEREAMI.'/libs/spyc/spyc-php5.php');
		$parserLoaded = true;
	}
}

function lolcat($args)
{
	echo _format("Type '?' for a short syntax help message.\n");
}


function create_model($args)
{
	if(empty($args)) { echo _format("Argument please! ([contextname] <modelname>)\n"); return false; }
	if(count($args)>1)
	{
		$contextname = $args[0];
		if(!_ensure_context($contextname)) return false;
		$partname    = $args[1];
		$filename    = 'app/'.$contextname.'/models/'.$partname.'.php';
	}
	else
	{
		$partname    = $args[0];
		$filename    = 'models/'.$partname.'.php';
	}
	$classname = ucfirst($partname);
	$contents = <<<EOB
<?php
class {$classname} extends ActiveRecord
{
        function __construct()
        {
                parent::__construct('{$partname}');
        }
}
?>
EOB;
	if(!_create_file($filename, $contents)) return false;
	echo _format("Model: Success.\n");
	return true;
}

function create_controller($args)
{
	if(empty($args)) { echo _format("Argument please! ([contextname] <controllername>)\n"); return false; }
	if(count($args)>1)
	{
		$contextname = $args[0];
		if(!_ensure_context($contextname)) return false;
		$partname    = $args[1];
		$filename    = 'app/'.$contextname.'/controllers/'.$partname.'_controller.php';
	}
	else
	{
		$partname    = $args[0];
		$filename    = 'controllers/'.$partname.'_controller.php';
	}
	$classname = ucfirst($partname).'Controller';
	$contents = <<<EOB
<?php
class {$classname} extends ApplicationController
{
        function index()
        {
        }
}
?>
EOB;
	if(!_create_file($filename, $contents)) return false;
	echo _format("Controller: Success.\n");
	return true;
}

function create_view($args)
{
	if(empty($args)) { echo _format("Argument please! ([contextname] <viewname> <pagename>)\n"); return false; }
	if(count($args)>2)
	{
		$contextname = $args[0];
		if(!_ensure_context($contextname)) return false;
		$viewname    = $args[1];
		$pagename    = $args[2];
		$pathname    = 'app/'.$contextname.'/views/'.$viewname;
	}
	else
	{
		$viewname    = $args[0];
		$pagename    = $args[1];
		$pathname    = 'views/'.$viewname;
	}
	if(!mkdir($pathname)) { echo _format("Problem creating view '$pathname'\n"); return false; }
	$contents = <<<EOB
<?php
if(\$message_type!=MESSAGE_ERROR):
?>

<?php
endif
?>
EOB;
	if(!_create_file($pathname.'/'.$pagename.'.html.php', $contents)) return false;
	echo _format("View: Success.\n");
	return true;
}

function create_mvc($args)
{
	if(!create_model($args)) return false;
	if(!create_controller($args)) return false;
	array_push($args, 'index');
	if(!create_view($args)) return false;
	echo _format("All created.\n");
	return true;
}

function create_setting($args)
{
	global $db;

	if(!_open_db()) return false;
	list($name, $value, $type, $options, $group, $description) = $args;
	$qry =	"INSERT INTO settings(`name`, `value`, `type`, `options`, `group`, `description`) VALUES('".
		addslashes($name)."', '".
		addslashes($value)."', '".
		addslashes(strtoupper($type))."', '".
		addslashes($options)."', '".
		intval($group)."', '".
		addslashes($description)."')";
	if(!$db->execute($qry))
	{
		echo _format("Unable to create setting '$name'.\n");
		return false;
	}
	echo _format("Setting '$name' created.\n");
	return true;
}

function delete_setting($args)
{
	global $db;

	$name = $args[0];
	if(!_open_db()) return false;
	$qry = "DELETE FROM settings WHERE name='{$name}'";
	if(!$db->execute($qry))
	{
		echo _format("Unable to delete setting '$name'.\n");
		return false;
	}
	echo _format("Setting '$name' deleted.\n");
	return true;
}

function show_settings()
{
	return show_setting();
}

function show_setting($args = null)
{
	global $db;

	if(!_open_db()) return false;
	$qry = 'SELECT * FROM settings';
	if(!empty($args)) $qry .= " WHERE `name` like '{$args[0]}%'";
	$rs = &$db->execute($qry);
	echo _format("\n");
	while(!$rs->EOF)
	{
		echo	_format("Description:\t".$rs->fields['description']."\n".
			"Name:\t\t".$rs->fields['name']."\n".
			"Value:\t\t".$rs->fields['value']."\n\n");
		$rs->moveNext();
	}
	return true;
}

function _m_check_table($table_name)
{
	global $db;

	if(!_open_db()) return false;
	$qry = "SELECT id FROM $table_name";
	$rs = &$db->execute($qry);
	return (false !== $rs);
}

function _m_execute($qry)
{
	global $db;

	if(!_open_db()) return false;
	if(!$db->execute($qry))
	{
		echo _format("! Error executing '$qry'\n");
		return false;
	}
	return true;
}

function _m_execute_multi($qries)
{
        foreach($qries as $qry)
        {
                if(!_m_execute($qry))
                        return false;
        }
	return true;
}

function _m_query($qry)
{
	global $db;

	if(!_open_db()) return false;
	$rs = &$db->execute($qry);
	if(!$rs)
	{
		echo _format("! Error executing '$qry'\n");
		return false;
	}
	$ret = array();
	while(!$rs->EOF)
	{
		$ret[] = $rs->fields;
		$rs->moveNext();
	}
	return $ret;
}

function _m_create_table($table_name, $field_defs)
{
	global $db, $dict;
	if(!_open_db() || !_create_dict()) return false;
	$sql = $dict->CreateTableSQL($table_name, $field_defs, array());
	if(!$sql)
	{
		echo _format("! Error creating table '$table_name'\n");
		return false;
	}
	foreach($sql as $qry)
	{
		if(!$db->execute($qry))
		{
			echo _format("! Error creating table '$table_name':\n\"$qry\"\n");
			return false;
		}
	}
	print "Created table '$table_name'\n";
	return true;
}

function _m_drop_table($table_name)
{
	global $db, $dict;
	if(!_open_db() || !_create_dict()) return false;
	$sql = $dict->DropTableSQL($table_name);
	if(!$sql)
	{
		echo _format("! Error dropping table '$table_name'\n");
		return false;
	}
	foreach($sql as $qry)
	{
		if(!$db->execute($qry))
		{
			echo _format("! Error dropping table '$table_name':\n\"$qry\"\n");
			return false;
		}
	}
	print "Dropped table '$table_name'\n";
	return true;
}

function _m_error()
{
	throw new Exception('Migration problem');
}

function _migrate($mo)
{
	if(!empty($mo['drop']))
	{
		foreach($mo['drop'] as $row)
		{
			if(!_m_drop_table($row['name']))
				_m_error();
		}
	}
	if(!empty($mo['create']))
	{
		foreach($mo['create'] as $row)
		{
			if(!_m_create_table(
				$row['name'],
				$row['info']))
				_m_error();
		}
	}
	if(!empty($mo['execute']))
	{
		foreach($mo['execute'] as $row)
		{
			if(!_m_execute($row['query']))
				_m_error();
		}
	}
}

function _prepare_to_migrate()
{
	// First, does system table exist?
	if(!_m_check_table('system'))
	{
		if(!_m_create_table(
			'system',
			"
			id	I		AUTO KEY,
			setting	VARCHAR(32)	INDEX setting NOTNULL,
			value   VARCHAR(64)     NOTNULL
			"))
			return false;
		if(!_m_execute("INSERT INTO `system`(`setting`,`value`) VALUES('version', 0)"))
			return false;
	}
	$row = _m_query("SELECT * FROM `system` WHERE `setting`='version'");
	if(count($row) != 1)
	{
		echo _format("! Wrong row number when reading version from system table\n");
		return false;
	}
	$curVersion  = intval($row[0]['value']);
	if(0 > $curVersion)
	{
		echo _format("Sorry, but it appears that the database got corrupted while trying to migrate to version ".
			(-1 * $curVersion).".\nThe database needs to be fixed before any new migration.\n");
		return false;
	}
	return $curVersion;
}

function migrate_model_down($args = null)
{
global $WHEREAMI;

	$targetVersion = intval($args[0]);
	if(0 > $targetVersion)
	{
		echo _format("Wrong parameter for version number\n");
		return false;
	}
	$curVersion = _prepare_to_migrate();
	if(false === $curVersion)
		return false;
	if($curVersion <= $targetVersion)
	{
		echo _format("Nothing to do: current version=$curVersion, target version=$targetVersion\n");
		return true;
	}
	$nextVersion = $curVersion - 1;
	try
	{
		while($nextVersion >= $targetVersion)
		{
			$fName = $WHEREAMI . '/migrations/' . sprintf('%03d', ($nextVersion + 1)) . '.yml';
			if(!file_exists($fName))
				break;
			echo _format("----------------------------------------\n");
			echo _format("Migrating from version $curVersion to version $nextVersion\n");
			echo _format("----------------------------------------\n");
			_create_yml_parser();
			$arr = Spyc::YAMLLoad($fName);
			$down   = &$arr['down'];
			_migrate($down);
			unset($arr);
			$curVersion = $nextVersion;
			$nextVersion --;
		}
	}
	catch(Exception $e)
	{
		// Oh no I failed! Store wannabe version number...with a twist: it's negative!
		_m_execute("UPDATE `system` SET `value`=".(-1 * $nextVersion)." WHERE `setting`='version'");
		echo _format("Alas, there was an issue migrating from #$curVersion to $nextVersion!\n");
		return false;
	}
	_m_execute("UPDATE `system` SET `value`=".($nextVersion + 1)." WHERE `setting`='version'");
	echo _format("Database fully migrated to version ".($nextVersion + 1).".\n");
	return true;
}

function migrate_model_up($args = null)
{
global $WHEREAMI;

	if(!empty($args) && !empty($args[0]))
		$targetVersion = intval($args[0]);
	else
		$targetVersion = 999999;
	if(0 >= $targetVersion)
	{
		echo _format("Wrong parameter for version number\n");
		return false;
	}
	$curVersion = _prepare_to_migrate();
	if(false === $curVersion)
		return false;
	if($curVersion >= $targetVersion)
	{
		echo _format("Nothing to do: current version=$curVersion, target version=$targetVersion\n");
		return true;
	}
	$nextVersion = $curVersion + 1;
	try
	{
		while($nextVersion <= $targetVersion)
		{
			$fName = $WHEREAMI . '/migrations/' . sprintf('%03d', $nextVersion) . '.yml';
			if(!file_exists($fName))
				break;
			echo _format("----------------------------------------\n");
			echo _format("Migrating from version $curVersion to version $nextVersion\n");
			echo _format("----------------------------------------\n");
			_create_yml_parser();
			$arr = Spyc::YAMLLoad($fName);
			$up   = &$arr['up'];
			_migrate($up);
			unset($arr);
			$curVersion = $nextVersion;
			$nextVersion ++;
		}
	}
	catch(Exception $e)
	{
		// Oh no I failed! Store wannabe version number...with a twist: it's negative!
		_m_execute("UPDATE `system` SET `value`=".(-1 * $nextVersion)." WHERE `setting`='version'");
		echo _format("Alas, there was an issue migrating from #$curVersion to $nextVersion!\n");
		return false;
	}
	_m_execute("UPDATE `system` SET `value`=".($nextVersion - 1)." WHERE `setting`='version'");
	echo _format("\n# Database fully migrated to version ".($nextVersion - 1).".\n");
	return true;
}
?>
