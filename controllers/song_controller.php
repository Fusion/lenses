<?php

class SongController extends ApplicationController
{
	function index()
	{
		$this->song = new Song();
		$this->song->find(FIRST, "artist_id=1", array('loading'=>ADODB_LAZY_AR));
	}

	function artist()
	{
global $db;
$db->debug = 1;
		$this->artist = new Artist();
		$this->artist->find(FIRST, "artists.id=1");
		$this->options['view'] = 'artist';
print "<pre>";
var_export($this->artist);
print "</pre>";
	}

	function artist2()
	{
global $db;
$db->debug = 1;
		$artist = new Artist();
		$this->artists = &$artist->find(ALL, "1=1", array('loading'=>ADODB_WORK_AR));
		$this->options['view'] = 'artist2';
print "<pre>";
var_export($this->artists);
print "</pre>";
	}

	function allsongs()
	{
global $db;
$db->debug = 1;
		$song = new Song();
		$this->songs = &$song->find(ALL, '1=1', array('loading'=>ADODB_WORK_AR));
		$this->options['view'] = 'allsongs';
print "<pre>";
var_export($this->songs);
print "</pre>";
	}

	function asong()
	{
global $db;
$db->debug = 1;
		$song = new Song();
//		$this->songs = &$song->find(ALL, 'id=1', array('loading'=>ADODB_WORK_AR));
		$this->songs = &$song->find(ALL, 'id=1');
		$this->options['view'] = 'allsongs';
print "<pre>";
var_export($this->songs);
print "</pre>";
	}
}
?>
