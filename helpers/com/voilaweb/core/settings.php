<?php
class Settings
{
	static function load($prefix)
	{
		global $db;
		$db->setFetchMode(ADODB_FETCH_ASSOC);

		$qry = 'SELECT * FROM settings';
		if($prefix!='')
		{
			$prefix_for_db = explode(' ',$prefix);
			$qry .= " WHERE";
			for($i=0;$i<count($prefix_for_db);$i++)
			{
				if($i>0)
					$qry .= " OR";
				$qry .= " `name` LIKE '".$prefix_for_db[$i]."%'";
			}
		}
		$qry .= ' ORDER BY `group`, `name`';
		$ret = array();
		$rs = &$db->execute($qry);
		while(!$rs->EOF)
		{
			// Quick shortcut for booleans
			if($rs->fields['type']=='S')
				$rs->fields['true'] = ($rs->fields['value'] == 'Yes');
			$ret[$rs->fields['name']] = $rs->fields;
			$rs->moveNext();
		}

		return $ret;
	}

	static function save($prefix, $fields)
	{
		global $db;

		$prefix_for_match = "/^(".str_replace(' ', '|', $prefix).")(.+)/";  
		foreach($fields as $name => $value)
		{
			$name = str_replace('_', '.', $name);
			$p = preg_match($prefix_for_match , $name);
			if($p<=0) continue;
			// Is there a double quote? If so, there should be two. And so on.
			// This is a protection against invalid parameters that mess up the display ultimately
			if(1 & substr_count($value, '"')) $value .= '"';
			// \r could cause a lot of grief
			$value = str_replace("\r", '', $value);
			$qry = 'UPDATE settings SET value=\''.$value.'\' WHERE name=\''.$name.'\'';
			$db->execute($qry);
		}
	}

	static function present($type, $name, $value, $description, $options, $group)
	{
		switch($type)
		{
			case 'H': // Hidden
				$inputfield = "<input type='hidden' name='{$name}' value='{$value}' />\n";
				break;
			case 'S': // Select
				$inputfield = "<select name='{$name}'>\n";
				$options = explode('|', $options);
				foreach($options as $option)
				{
					$selected = ($option==$value?' selected':'');
					$inputfield .= "<option value='{$option}'{$selected}>{$option}</options>\n";
				}
				$inputfield .= "</select>\n";
				break;
			case 'M': // Multiple Select - not yet implemented
				break;
			case 'N': // Number
				$inputfield = "<input type='text' name='{$name}' value='{$value}' size='4' style='text-aligne:right;'>\n";
				break;
			case 'A': // ASCII
			default:
				$inputfield = "<input type='text' name='{$name}' value='{$value}'>\n";
		}
		return $inputfield;
	}

	static function modules($type = null)
	{
		global $db;
		$db->setFetchMode(ADODB_FETCH_ASSOC);

		$qry = 'SELECT * FROM modules';
		if($type)
			$qry .= " WHERE type='$type'";
		$qry .= ' ORDER BY `type`,`default` DESC';

		$ret = array();
		$rs = &$db->execute($qry);
		while(!$rs->EOF)
		{
			$ret[] = $rs->fields;
			$rs->moveNext();
		}

		return $ret;
	}
}
?>
