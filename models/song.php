<?php
class Song extends ActiveRecord
{
	function __construct()
	{
		parent::__construct();
		$this->belongsTo('artist');
		$this->belongsTo('genre');
	}
}
?>
