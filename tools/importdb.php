<?php
// Import the content of a MySQL dump

require 'tools/__dbclass.php';

class ImportDb extends DbClass
{
	function __construct()
	{
		parent::__construct();
	}

	/**
	 * High level: Import an SQL file
	 * @param dblayer Abstraction layer: ado or pear
	 * @param dbengine MySQL or other...
	 * @param dbhost DB Server hostname
	 * @param dbname Database name
	 * @param dbuser Database user name
	 * @param dbpassword Database user password
	 * @param filename Name of the file to import
	 * @param oldprefix Tables prefix to be replaced in the import file
	 * @param dbprefix Prefix to replace the old prefix with
	 * @param substitutions An associative array of values to dynamically replace while importing the file
	 * @return String: 'OK' upon success, another string otherwise.
	 */
	function importdb($dblayer, $dbengine, $dbhost, $dbname, $dbuser, $dbpassword, $filename, $oldprefix, $dbprefix, $substitutions, $filter=null)
	{
		if(null==$this->opendb($dblayer, $dbengine, $dbhost, $dbname, $dbuser, $dbpassword))
		{
			return "Sorry, unable to open database";
		}
		$fromvals = $tovals = array();
		if(!empty($substitutions))
			foreach($substitutions as $fromval => $toval)
			{
				$fromvals[] = $fromval;
				$tovals[] = $toval;
			}

		// First of all, check whether we have a filter...
		if($filter!=null)
		{
			$filterout = array();
			// filter: table name, column name
			foreach($filter as $key=>$value)
			{
				$qry = 'SELECT `'.$value.'` FROM `'.str_replace($oldprefix, $dbprefix, $key).'`';
				$res = $this->dbquery($qry);
				while($row = $res->fetchRow(DB_FETCHMODE_ASSOC))
				{
					$filterout[$key][] = $row[$value];
				}
			}
		}

		//
		$queries = array();
		$f=fopen($filename, "r");
		$qry = '';
		while (!feof($f))
		{
			$sql = fgets($f, 65535);
			$l = strlen($sql);
			while(ord($sql[$l-1])<32 && $l>1)
				$l--;
			$sql = substr($sql, 0, $l);
			if(strlen($sql)<2 || $sql[0]=='-')
				continue;
			$qry .= $sql;
			if($sql[$l-1]==';')
			{
				$qry = str_replace($oldprefix, $dbprefix, $qry);
				$qry = str_replace($fromvals, $tovals, $qry);
				$bSkipThis = false;
				if($filter!=null)
				{
					if(substr($qry, 0, 11)=='INSERT INTO')
					{
						foreach($filter as $key=>$value)
						{
							$token = 'INSERT INTO `'.str_replace($oldprefix, $dbprefix, $key).'`';
							if(substr($qry, 0, strlen($token)) != $token)
							{
								$bSkipThis = true; // we should skip if it isn't in the filters table
								// We do not break from the loop though as the next token may fit the bill
							}
							else
							{
								if(isset($filterout[$key]))
								{
									$bSkipThis = false; // well, now there is hope
									for($fili=0; $fili<count($filterout[$key]); $fili++)
									{
										if(false!==strpos($qry, "'".$filterout[$key][$fili]."'"))
										{
											$bSkipThis = true;
											break;
										}
									}
									break;
								}
							}
						}
					}
					else
					{
						$bSkipThis = true;
					}
				}
				if(!$bSkipThis)
				{
					$queries[] = $qry;
				}
				$qry = '';
			}
		}
		fclose($f);

		for($i=0;$i<count($queries);$i++)
		{
#			print "Importdb: ".$queries[$i].'<br />';
			if(null == $this->dbquery($queries[$i]))
			{
				return "Sorry, unable to run query:<br />".$queries[$i];
			}
		}

		return 'OK';
	}
}
?>
