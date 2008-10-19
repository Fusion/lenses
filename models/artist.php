<?php
class Artist extends ActiveRecord
{
	function __construct()
	{
		parent::__construct();
		$this->hasMany('songs');
	}
}
?>
