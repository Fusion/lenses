<?php
// Dump the schema of a MySQL dump

require 'tools/__dbclass.php';

class ImportDb extends DbClass
{
	function __construct()
	{
		parent::__construct();

		$tbl = $this->dumpdbschema(
			$this->getVar('dblayer'),
			$this->getVar('dbengine'),
			$this->getVar('dbhost'),
			$this->getVar('dbname'),
			$this->getVar('dbuser'),
			$this->getVar('dbpassword'),
			$this->getVar('dbprefix'));

		$f = fopen('dbschema.ser', 'w+b');
		fwrite($f, serialize($tbl));
		fclose($f);
	}

	/**
	 * High level: Dump a database's current SQL Schema
	 * @param dblayer Abstraction layer: ado or pear
	 * @param dbengine MySQL or other...
	 * @param dbhost DB Server hostname
	 * @param dbname Database name
	 * @param dbuser Database user name
	 * @param dbpassword Database user password
	 * @param dbprefix Current tables prefix
	 * @return Array: schema
	 */
	function dumpdbschema($dblayer, $dbengine, $dbhost, $dbname, $dbuser, $dbpassword, $dbprefix)
	{
		// read current schema
		if(null==$this->opendb($dblayer, $dbengine, $dbhost, $dbname, $dbuser, $dbpassword))
		{
			return "Sorry, unable to open database";
		}
		// Build a list of tables
		$tbl = array();
		$qry = "SHOW TABLES LIKE '{$dbprefix}%'";
		$res = $this->dbquery($qry);
		while($row = $res->fetchRow(DB_FETCHMODE_ASSOC))
		{
			$tblname = $row[0];
			$tblfields = array();
			$qry = "SHOW COLUMNS FROM `".$tblname."`";
			$res2 = $this->dbquery($qry);
			while($row2 = $res2->fetchRow(DB_FETCHMODE_ASSOC))
			{
				$tblfields[] = array(
						'name' => $row2[0],
					'type' => $row2[1],
					'null' => $row2[2],
					'index' => $row2[3],
					'default' => $row2[4],
					'extra' => $row2[5]
				);
			}
			$tblprimary = null;
			$tblindices = array();
			$qry = "SHOW INDEX FROM `".$tblname."`";
			$res2 = $this->DB->query($qry);
			while($row2 = $res2->fetchRow(DB_FETCHMODE_ASSOC))
			{
				$c = count($tblindices)-1;
				if($c>=0 && $row2[2]==$tblindices[$c]['idxname'])
				{
					$cols = &$tblindices[$c]['colname'];
					$cols[] = $row2[4];
					continue;
				}
				$tblindices[] = array(
					'nonunique' => $row2[1],
					'idxname' => $row2[2],
					'sequence' => $row2[3],
					'colname' => array($row2[4]),
					'collation' => $row2[5],
					'cardinality' => $row2[6],
					'subpart' => $row2[7],
					'packed' => $row2[8],
					'null' => $row2[9],
					'tpe' => $row2[10]
					);
				if($row2[2]=='PRIMARY')
					$tblprimary = &$tblindices[($c+1)];
			}
			$tbl[] = array(
				'tblname' => $tblname,
				'tblfields' => $tblfields,
				'tblindices' => $tblindices,
				'tblprimary' => $tblprimary
                        );
		}

		return $tbl;
	}
}
