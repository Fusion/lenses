<?php
/**
 * WS Example: read-only Crunchbase access
 */
class Crunchbase extends ActiveWSRecord
{
	function __construct()
	{
		parent::__construct('gubb');
	}

	function _companies($parameter)
	{
		return $this->_request('companies', $parameter);
	}

	function _request($namespace, $parameter)
	{
		return $this->request(
			array(
				'host' => 'api.crunchbase.com',
				'url' => '/v/1/',
				'port' => 80),
			$parameter,
			'search.js?query=');
	}

	function load($key)
	{
	}

	function find($mode, $condition=null, $extra=array(), $bindArr=false, $pKeyArr=false)
	{
		$listStruct = $this->_companies($condition);
		if(FIRST == $mode)
		{
			if(count($listStruct['php']->results > 0))
				return $listStruct['php']->results[0];
			return array();
		}
		return $listStruct['php']->results;
	}

	function __toString()
	{
		return "<pre>\n" . var_export($this) . "</pre>\n";
	}
}
