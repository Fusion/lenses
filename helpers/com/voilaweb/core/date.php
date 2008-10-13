<?php
class Date
{
	// Your friendly non-instantiable neighborhood class!
	private function __construct() {}

	static function format($dateValue,$customFormat="M j, Y, g:i a")
	{
		// First, adjust date
		// @todo Use timezones $offset = ($USER->timezone - $CONFIG->board->timezone) * 60;
		$offset = 0; //
		$dateValue += $offset;
		//
		return date($customFormat, $dateValue);
	}

	/**
	 * $cutoff represents the number of days after which we'd rather
	 * display a "unfancy" date
	 */
	static function fancyFormat($dateValue, $cutoff=7)
	{
		$delta = time() - $dateValue;
		if($delta < 60)
			return 'less than a minute ago';
		else if($delta < 120)
			return 'about a minute ago';
		else if($delta < 2700)
			return intval($delta / 60) . ' minutes ago';
		else if($delta < 5400)
			return 'about an hour ago';
		else if ($delta < 86400)
		{
			if(intval($delta / 3600) == 1)
				return 'about 2 hours ago';
			else
				return 'about ' + intval($delta / 3600) . ' hours ago';
		}
		else if($delta < 172800)
			return 'a day ago';
		else
		{
			$elapsed = intval($delta / 86400);
			if($elapsed <= $cutoff)
				return $elapsed . ' days ago';
			else
				return self::format($dateValue);
		}
	}
}
?>
