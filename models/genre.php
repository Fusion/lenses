<?php
class Genre extends ActiveRecord
{
	function __construct()
	{
		parent::__construct('genres');
		$this->hasMany('songs');
	}
}
?>
