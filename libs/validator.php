<?
/**
 * @package Lenses
 * @copyright (c) Chris F. Ravenscroft
 * @license See 'license.txt'
 */
class Validator
{
	var $_validee;
	var $_result;

	function validee($obj)
	{
		$this->_validee = $obj;
		$this->_result  = false;
		return $this;
	}
		
	function checkemail()
	{
		if(empty($this->_validee))
			$this->_result = false;
		elseif(!preg_match('/^([a-zA-Z0-9_-])+([\.a-zA-Z0-9_-])*@([a-zA-Z0-9_-])+(\.[a-zA-Z0-9_-]+)*\.([a-zA-Z]{2,6})$/', $this->_validee))
			$this->_result = false;
		elseif(Config::$fullemailcheck)
		{
			list($username, $domain) = split('@',$email);
			if(getmxrr($domain, $mxhosts))
				$this->_result = true;
			elseif(fsockopen($domain, 25, $errno, $errstr, 30))
				$this->_result = true;
			else
				$this->_result = false;
		}
		else
			$this->_result = true;
		return $this;
	}

	function checkinvite()
	{
		global $db;
		$r = $db->query("SELECT id FROM invites WHERE code='".$this->_validee."' AND used!=1");
		$this->_result = ($r->numrows()>0);
		return $this;
	}
	
	function isok()
	{
		return $this->_result;
	}
}

global $validator;
$validator = new Validator();
?>
