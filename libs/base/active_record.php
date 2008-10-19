<?php
/**
 * This class offers very light scaffolding;
 * however is it not my intent to offer full-fledged record management here.
 * For instance, if more than one table is mapped, *poof* goes the scaffold.
 *
 * Note: I know that I should be using a decorator; unfortunately, the parent class
 * is counting on its own name when deserializing from session...
 */
class ActiveRecord extends ADOdb_Active_Record implements ApplicationModel
{
	var $name;

	function __construct($modelname = false)
	{
		global $inflector;
		/*
		 *  No: let db layer take care of this. 
		if(empty($modelname))
			$modelname = get_class($this);
		parent::__construct($inflector->inflectee($modelname)->pluralized()->is());
		 */
		parent::__construct($modelname);
		$this->name = get_class($this);
	}

	function sessionPersist()
	{
		Session::persist($this);
	}

	function load($key)
	{
		return parent::load('id='.$key);
	}

	function find($mode, $condition=null, $extra=array(), $bindArr=false, $pKeyArr=false)
	{
		if(Config::$debugger)
		{
			$comma = '';
			$dextra = 'array(';
			foreach($extra as $key=>$value)
			{
				switch($key)
				{
					case 'loading':
						switch($value)
						{
							case ADODB_JOIN_AR: $value = 'ADODB_JOIN_AR'; break;
							case ADODB_WORK_AR: $value = 'ADODB_WORK_AR'; break;
							case ADODB_LAZY_AR: $value = 'ADODB_LAZY_AR'; break;
						}
						break;
				}
				$dextra .= $comma.'\''.$key.'\' => \''.$value.'\'';
				$comma = ', ';
			}
			$dextra .= ')';
			global $_debug;
			$_debug .=	'<br />AR: '.
					$this->name.
					'.find('.
					(FIRST==$mode?'FIRST':'ALL').
					',\''.
					$condition.
					'\','.
					$dextra.
					')';
		}

		// id?
		if(ctype_digit($mode))
			return $this->load($mode);

		// return one row
		if(FIRST == $mode)
			return parent::load($condition);

		// Assumption: ALL == $mode - return multiple rows
		return parent::find($condition, $bindArr, $pKeyArr, $extra);
	}

	function __toString()
	{
		return "<pre>\n" . var_export($this) . "</pre>\n";
	}
}
?>
