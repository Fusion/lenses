<?php
class Pagination
{
	// Your friendly non-instantiable neighborhood class!
	private function __construct() {}

	static function paginate($offset, $limit, $total, $prefix, $suffix, $options=array())
	{
		if(isset($options['style']))
			$styleStr = $options['style'];
		else
			$styleStr = 'pagination';
		$nbPages = ceil($total / $limit);
		if($offset == 1)
			$previousStr = "<li class=\"previous-off\">&laquo;Previous</li>\n";
		else
			$previousStr = '<li class="previous"><a href="'.$prefix.($offset-1).$suffix."\">&laquo;Previous</a></li>\n";
		$block = '<ul class="'.$styleStr."\">\n".$previousStr;
		for($i=1; $i<=$nbPages; $i++)
		{
			if($offset == $i)
				$block .= '<li class="active">'.$i."</li>\n";
			else
				$block .= '<li><a href="'.$prefix.$i.$suffix.'">'.$i."</a></li>\n";
		}
		if(($offset+1) == $i)
			$block .= "<li class=\"next-off\">Next &raquo;</li>\n";
		else
			$block .= "<li class=\"next\"><a href='".$prefix.($offset+1).$suffix."'>Next &raquo;</a></li>\n";
		$block .= "</ul>\n";
		return $block;
	}
}
?>
