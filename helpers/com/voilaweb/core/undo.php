<?php
/**
 * @package Lenses
 * @copyright (c) Chris F. Ravenscroft
 * @license See 'license.txt'
 */
abstract class Undo
{
	private $_arguments;

	function __construct($arguments)
	{
		$this->_arguments = $arguments;
	}

	function arguments()
	{
		return $this->_arguments;
	}

	abstract function cmd();
	abstract function undo();

	static function store($object)
	{
		global $db;
		$qry = "INSERT INTO undostack(session_id, class, object) VALUES('".
			session_id()."','".
			get_class($object)."','".
			serialize($object)."')";
		$db->execute($qry);
	}

	static function recall()
	{
		global $db;
		$db->setFetchMode(ADODB_FETCH_ASSOC);
		$qry = "SELECT * FROM undostack WHERE session_id='".session_id()."' ORDER BY id DESC LIMIT 1";
        $rs = &$db->execute($qry);
		if(!$rs) throw new Exception("Nothing to undo");
		$object = unserialize($rs->fields['object']);
		return $object;
	}
}
?>
