<?php
/**
 * @package Lenses
 * @copyright (c) Chris F. Ravenscroft
 * @license See 'license.txt'
 */
$nbbs_vars = array();
$used_vars = array();
$runner = 0;
global $trace_offset;
$trace_offset = 1;

/** @todo syslog management as well */

// Happy, flower-people-like messages
define('MESSAGE_BACKGROUND', 'bg');
define('MESSAGE_INFO', 'in');
define('MESSAGE_OK', 'ok');
define('MESSAGE_ERROR', 'ko');

function displayMessage($type, $message, $nextAction = DISPLAY_BREAK)
{
global $message_type, $message_css, $message_text;

	$message_type = $type;
	switch($type)
	{
		case MESSAGE_BACKGROUND:
			$message_css = 'clean-background';
			break;
		case MESSAGE_INFO:
			$message_css = 'clean-info';
			break;
		case MESSAGE_OK:
			$message_css = 'clean-ok';
			break;
		case MESSAGE_ERROR:
			$message_css = 'clean-error';
			break;
	}
	$message_text = $message;
	if(DISPLAY_BREAK == $nextAction)
		throw new GoodException();
}

// Display real hard-ass errors

function displayExceptionScreen($ex)
{
	displayErrorScreen(
		E_ERROR,
		$ex->getMessage(),
		$ex->getFile(),
		$ex->getLine(),
		array('Exception'=>true));
}

function displayErrorScreen($type, $message, $file, $line, $context = null)
{
	global $nbbs_vars, $used_vars, $trace_offset;

	if(0 == (error_reporting() & $type))
		return;

        @ob_end_clean(); // get rid of any half-parsed page

	// Fix for exceptions
	if(!empty($context) && !empty($context['Exception']))
	{
		$type = -1;
	}

	$backTrace = debug_backtrace();
	$ERROR_TYPES = array(
		-1 => 'Exception',
		E_ERROR => 'Error',
		E_WARNING => 'Warning',
		E_PARSE => 'Parse',
		E_NOTICE => 'Notice',
		E_CORE_ERROR => 'Core Error',
		E_CORE_WARNING => 'Core Warning',
		E_COMPILE_ERROR => 'Compile Error',
		E_COMPILE_WARNING => 'Compile Warning',
		E_USER_ERROR => 'User Error',
		E_USER_WARNING => 'User Warning',
		E_USER_NOTICE => 'User Notice',
		E_STRICT => 'Strict Notice',
		E_RECOVERABLE_ERROR => 'Recoverable Error');

	$splitSourceCode = preg_split('#<br />#i', highlight_string(file_get_contents($file, true), true));
	$formattedSourceCode = '';
	$lb = $line - 10; if ($lb < 0) $lb = 0; // < 10 lines before?
	for($i = $lb; $i < ($line + 10); $i ++)
	{
		if(!isset($splitSourceCode[$i])) // < 10 lines after?
			break;
		$curLine = rtrim($splitSourceCode[$i]);
		$formattedSourceCode .=
			($line == ($i + 1)
				? '<div style="background-color:red;">' : '<div>') .
				'<strong>' . sprintf('%03s', ($i+1)) . '</strong>&nbsp;' . $curLine . '</div>';
	}

	$stackTrace = '';
	for($i=$trace_offset; $i<count($backTrace); $i++) // 1 == Ignore this function
	{
		switch($backTrace[$i]['type'])
		{
			case '->': // Instance
				if($backTrace[$i]['class']==$backTrace[$i]['function'])
					$s1 = $backTrace[$i]['class'].'()';
				else
					$s1 = $backTrace[$i]['class'].'->'.$backTrace[$i]['function'];
				break;
			case '::': // Static
				$s1 = $backTrace[$i]['class'].'::'.$backTrace[$i]['function'];
				break;
			default:   // Function
				$s1 = $backTrace[$i]['function'];
		}
		if($s1 == 'handleShutdown') continue;
		$s3 = ''; $comma = '';
		for($j=0; $j<count($backTrace[$i]['args']); $j++)
		{
			if(0==strlen($backTrace[$i]['args'][$j]))
				$s3 .= '""';
			else if(is_numeric($backTrace[$i]['args'][$j]))
				$s3 .= $comma.$backTrace[$i]['args'][$j];
			else
				$s3 .= $comma.'"'.$backTrace[$i]['args'][$j].'"';
			$comma = ', ';
		}
		if(empty($backTrace[$i]['file']))
			$s2 = '(?)';
		else
			$s2 = '('.$backTrace[$i]['file'].':'.$backTrace[$i]['line'].')';
		$stackTrace .= 'at '.$s1.'('.$s3.') '.$s2.'<br />';
	}
	if(empty($stackTrace))
		$stackTrace = '(no stack trace)<br />';

	$trace_offset = 1;
	_canonizeVars('', $GLOBALS, true);
	foreach($nbbs_vars as $key=>$value)
	{
		$formattedSourceCode = preg_replace_callback(
			'/\$'.$key.'/',
			create_function(
				'$matches',
				'global $used_vars, $runner; $runner++; $used_vars[$matches[0]] = $runner; return "<a href=\'#v{$runner}\'>{$matches[0]}</a>";'),
			$formattedSourceCode);
	}

	$varValues = '';
	if(!empty($used_vars))
	{
		foreach($used_vars as $key=>$value)
		{
			$varValues .= '<a name="v'.$value.'">'.$key.'</a> == '.$nbbs_vars[substr($key,1)].'<br />';
		}
	}

	$r = <<<EOB
[FW]<br /><br />
<strong>{$ERROR_TYPES[$type]}: {$message} in {$file} ({$line})</strong><br />
{$stackTrace}<br />
<strong>Source Code:</strong>
<pre>
{$formattedSourceCode}
</pre>
<strong>Variables:</strong><br />
{$varValues}
EOB;

	if(Config::$notifyonerror)
	{
		if(!@mail(Config::$developer, "[ERROR: {$ERROR_TYPES[$type]}]", $r))
			$r = <<<EOB
<strong>### Please Help! ###</strong><br />
The application developer wishes to receive notification whenever an error occurs.<br />
However, I was unable to send an email (oops).<br />
Would you mind trying to contact them yourself?<br />
Thank you.<br />
<br />
{$r}
EOB;
	}

	die($r);
}

function traceOffset($offset = 1)
{
	global $trace_offset;
	$trace_offset = $offset;
}

function handleShutdown()
{
	if($err = error_get_last()) 
		displayErrorScreen($err['type'], $err['message'], $err['file'], $err['line']);
}

function _canonizeVars($whos, $daddy, $isArray)
{
	global $nbbs_vars;

	foreach($daddy as $key=>$value)
	{
		// Note regarding the following exception list:
		// Only GLOBALS and nbbs_var must absolutely be removed.
		// Other variables are memory hogs that you think may kill php
		if($key=='GLOBALS' || $key=='nbbs_vars' || $key=='TEMPLATE' || $key=='MAIN' || $key=='PARSER')
			continue;
		if(is_array($value) || is_object($value))
		{
			if(empty($whos))
				$idx = $key;
			else if($isArray)
				$idx = $whos.'['.$key.']';
			else
				$idx = $whos.'-&gt;'.$key;
			_canonizeVars($idx, $value, is_array($value));
		}
		else
		{
			if(empty($whos))
				$idx = $key;
			else if($isArray)
				$idx = $whos.'['.$key.']';
			else
				$idx = $whos.'-&gt;'.$key;
			$nbbs_vars[$idx] = $value;
		}
	}
}
?>
