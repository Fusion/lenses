<?php
// Migrate database schema

require 'tools/__dbclass.php';

class Migrate extends DbClass
{
var $_allsql;

	function __construct()
	{
		parent::__construct();

		$prediag = $this->prepareDbUpgrade(
			$this->getVar('dblayer'),
			$this->getVar('dbengine'),
			$this->getVar('dbhost'),
			$this->getVar('dbname'),
			$this->getVar('dbuser'),
			$this->getVar('dbpassword'),
			'dbschema.ser',
			'chris_',
			$this->getVar('dbprefix'));
			

		$diag = $this->performDbUpgrade();

		if($diag=='OK')
		{
		}
		else
		{
		}
	}

	function prepareDbUpgrade($dblayer, $dbengine, $dbhost, $dbname, $dbuser, $dbpassword, $schemafile, $oldprefix, $newprefix)
	{
		$this->_allsql = array();
		// First, update old structures...
                $this->opendb($dblayer, $dbengine, $dbhost, $dbname, $dbuser, $dbpassword);
		$this->prepareLegacy($newprefix);
		// deserialize new schema
		$h = fopen("dbschema.ser", "rb");
		$obj = fread($h, filesize("dbschema.ser"));
		$newtbl = unserialize($obj);
		fclose($h);
		// read current schema
		$tbl = $this->dumpdbschema($dblayer, $dbengine, $dbhost, $dbname, $dbuser, $dbpassword, $newprefix);
		// At this point, we have two objects:
		// 1-tbl == installed database structure
		// 2-newtbl == upgrade database structure
		// Let us compare them
		$diag = '';
		for($newi=0; $newi<count($newtbl); $newi++)
		{
			$newtable = &$newtbl[$newi];
			$newtable['tblname'] = str_replace("chris_", $newprefix, $newtable['tblname']);
			$bFound = false;
			// Does this table exist in current structure?
			for($curi=0; $curi<count($tbl); $curi++)
			{
				if($newtable['tblname']==$tbl[$curi]['tblname'])
				{
					$bFound = true;
					// Compare
					$diag .= "<b>Check table ".$newtable['tblname']."</b>";
					$diag .= $this->performTableUpgrade($tbl[$curi], $newtable);
					//
					break;
				}
			}
			if(!$bFound)
			{
				// Brand new table!
				$diag .= "<font color='blue'><b>Create table ".$newtable['tblname']."</b></font><br />";
				$diag .= $this->createTable($newtable);
			}
			$diag .= '<br />';
		}

		// Almost done! I need to get the very important stuff from bb_users
		$filter = array('chris_config' => 'config_key', 'chris_settings'=>'setting_key');
		$diag = $this->importdb(
			$this->getVar('dblayer'),
			$this->getVar('dbengine'),
			$this->getVar('dbhost'),
			$this->getVar('dbname'),
			$this->getVar('dbuser'),
			$this->getVar('dbpassword'),
			'safedump.sql',
			'chris_',
			$this->getVar('dbprefix'),
			$subs,
			$filter);
		return $diag;
	}

	/**
	 * Take care of fixing some potentially annoying old stuff, if any
	 */
	function prepareLegacy($prefix)
	{
		// in bb_users, add birthday mark in year
		$qry = "SELECT in_birthday_offset FROM {$prefix}users LIMIT 1";
		$res = $this->dbquery($qry);
		if(!$res)
		{
			$this->dbquery("ALTER TABLE {$prefix}users ADD in_birthday_offset int(6)");
			$this->dbquery("ALTER TABLE {$prefix}users ADD INDEX in_birthday_offset(in_birthday_offset)");
		}
		// in bb_config, search_ID became result_ID and a new search_ID column was added
		$qry = "SELECT result_ID FROM {$prefix}results LIMIT 1";
		$res = $this->dbquery($qry);
		if(!$res)
		{
			$this->dbquery("ALTER TABLE {$prefix}results CHANGE search_ID result_ID int(10) NOT NULL auto_increment");
		}
		// How 'bout we update the version number?
		$this->dbquery("DELETE FROM {$prefix}settings WHERE setting_key='se_version'");
	}

	function performDbUpgrade()
	{
		for($i=0; $i<count($this->_allsql); $i++)
		{
			$this->dbquery($this->_allsql[$i]);
		}
		return "OK";
	}

	function createTable($newtable)
	{
		$sql = 'CREATE TABLE `'.$newtable['tblname'].'` (';
		$c = count($newtable['tblfields']) -1;
		for($newi=0; $newi<=$c; $newi++)
		{
			$newfield = &$newtable['tblfields'][$newi];
			$sql .= '`'.$newfield['name'].'` '.$newfield['type'].' '.
				($newfield['null']==''?'NOT NULL':'').' '.
				($newfield['default']!=''?"DEFAULT '".$newfield['default']."'":'').' '.
				$newfield['extra'];
			if($newi<$c)
				$sql .= ', ';
		}
		$tblprimary = &$newtable['tblprimary'];
		if(count($tblprimary['colname']) > 0)
		{
			$sql .= ', PRIMARY KEY (';
			for($primi=0; $primi<count($tblprimary['colname']); $primi++)
			{
				if($primi>0)
					$sql .=',';
				$sql .= '`'.$tblprimary['colname'][$primi].'`';
			}
			$sql .= ') '; // .$tblprimary['type'];
		}
		$newindices = &$newtable['tblindices'];
		for($newi=0; $newi<count($newindices); $newi++)
		{
			$newindex = &$newindices[$newi];
			if($newindex['colname']=='')
				continue;

			$sql .= ',KEY `'.$newindex['idxname'].'` (';
			for($newj=0; $newj<count($newindex['colname']); $newj++)
			{
				if($newj>0)
					$sql .= ',';
				$sql .= '`'.$newindex['colname'][$newj].'`';
			}
			$sql .= ') '; // .$newindex['type'];
		}
		$sql .= ');';
		$this->_allsql[] = $sql;
		return $diag;
	}

	function performTableUpgrade(&$curtable, &$newtable)
	{
		$diag = '';
		$bComma = false;
		$newfields = $newtable['tblfields'];
		$curfields = $curtable['tblfields'];
		for($newi=0; $newi<count($newfields); $newi++)
		{
			$newfield = $newfields[$newi];

			if(!$bComma)
				$diag .= '<br /><u>Fields</u><br />';
			if($bComma)
				$diag .=', ';

			$bFound = false;
			$hdiag = "&nbsp;&nbsp;&nbsp;&nbsp;";
			for($curi=0; $curi<count($curfields); $curi++)
			{
				if($newfield['name']==$curfields[$curi]['name'])
				{
					$bFound = true;
					$diag .= $newfield['name'];
					$diag .= $this->performFieldUpgrade($newtable['tblname'], $curfields[$curi], $newfield);
					break;
				}
			}
			if(!$bFound)
			{
				$diag .= "<font color='blue'>+".$newfield['name']."</font>";
				$diag .= $this->createField($newtable['tblname'], $newfield);
			}
			$bComma = true;

			unset($newfield);
		}

		unset($curfields);
		unset($newfields);

		$bComma = false;
		$newindices = $newtable['tblindices'];
		$curindices = $curtable['tblindices'];
		for($newi=0; $newi<count($newindices); $newi++)
		{
			$newindex = $newindices[$newi];
			if($newindex['idxname']=='')
			{
				unset($newindex);
				continue;
			}

			if(!$bComma)
				$diag .= '<br /><u>Indices</u><br />';
			if($bComma)
				$diag .=', ';

			$bFound = false;
			$hdiag = "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
			for($curi=0; $curi<count($curindices); $curi++)
			{
				if($newindex['idxname']==$curindices[$curi]['idxname'])
				{
					$bFound = true;
					$diag .= $newindex['idxname'];
					break;
				}
			}
			if(!$bFound)
			{
				$diag .= "<font color='blue'>+".$newindex['idxname']."</font>";
				$diag .= $this->createIndex($newtable['tblname'], $newindex);
			}
			$bComma = true;

			unset($newindex);
		}

		unset($curindices);
		unset($newindices);

		$diag .= '<br />';
		return $diag;
	}

	function createField($tblname, $newfield)
	{
		$sql = 'ALTER TABLE `'.$tblname.'` ADD `'.$newfield['name'].'` '.$newfield['type'].' '.
			($newfield['null']==''?'NOT NULL':'').' '.
			($newfield['default']!=''?"DEFAULT '".$newfield['default']."'":'').' '.
			$newfield['extra'];
		$this->_allsql[] = $sql;
		return $diag;
	}

	function performFieldUpgrade(&$tblname, &$curfield, &$newfield)
	{
		$diag = '';
		$bSame = true;
		if($curfield['type']!=$newfield['type'])
		{
			$bSame = false;
		}
		else if($curfield['null']!=$newfield['null'])
		{
			$bSame = false;
		}
		else if($curfield['default']!=$newfield['default'])
		{
			$bSame = false;
		}
		else if($curfield['extra']!=$newfield['extra'])
		{
			$bSame = false;
		}
		if(!$bSame)
		{
			$sql = 'ALTER TABLE `'.$tblname.'` CHANGE `'.$newfield['name'].'` `'.$newfield['name'].'` '.
				$newfield['type'].' '.
				($newfield['null']==''?'NOT NULL':'').' '.
				($newfield['default']!=''?"DEFAULT '".$newfield['default']."'":'').' '.
				$newfield['extra'];
			$this->_allsql[] = $sql;
		}
		return $diag;
	}

	function createIndex($tblname, $newindex)
	{
		/** @todo ADD UNIQUE, depending on 'nonunique' */
		if($newindex['idxname']=='PRIMARY')
			$sql = 'ALTER  TABLE `'.$tblname.'` ADD PRIMARY KEY (';
		else
			$sql = 'ALTER  TABLE `'.$tblname.'` ADD INDEX `'.$newindex['idxname'].'` (';
		for($newi=0; $newi<count($newindex['colname']); $newi++)
		{
			if($newi>0)
				$sql .= ',';
			$sql .= '`'.$newindex['colname'][$newi].'`';
		}
		$sql .= ') '; // .$newindex['type'].';';
		$this->_allsql[] = $sql;
		return $diag;
	}
}
?>
