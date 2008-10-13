<?
/**
 * @package Lenses
 * @copyright (c) Chris F. Ravenscroft
 * @license See 'license.txt'
 */
class Inflector
{
	var $_inflectee;
	// Constants found in rails :)
	var $Irregular = array(
		'PERSON'	=> 'people',
		'MAN'		=> 'men',
		'WOMAN'		=> 'women',
		'CHILD'		=> 'children',
		'COW'		=> 'kine',
	);
	var $WeIsI = array(
		'EQUIPMENT'	=> true,
		'INFORMATION'	=> true,
		'RICE'		=> true,
		'MONEY'		=> true,
		'SPECIES'	=> true,
		'SERIES'	=> true,
		'FISH'		=> true,
		'SHEEP'		=> true,
	);
	/*

  singular: [
    [/(quiz)zes$/i,                                                    "$1"     ],
    [/(matr)ices$/i,                                                   "$1ix"   ],
    [/(vert|ind)ices$/i,                                               "$1ex"   ],
    [/^(ox)en/i,                                                       "$1"     ],
    [/(alias|status)es$/i,                                             "$1"     ],
    [/(octop|vir)i$/i,                                                 "$1us"   ],
    [/(cris|ax|test)es$/i,                                             "$1is"   ],
    [/(shoe)s$/i,                                                      "$1"     ],
    [/(o)es$/i,                                                        "$1"     ],
    [/(bus)es$/i,                                                      "$1"     ],
    [/([m|l])ice$/i,                                                   "$1ouse" ],
    [/(x|ch|ss|sh)es$/i,                                               "$1"     ],
    [/(m)ovies$/i,                                                     "$1ovie" ],
    [/(s)eries$/i,                                                     "$1eries"],
    [/([^aeiouy]|qu)ies$/i,                                            "$1y"    ],
    [/([lr])ves$/i,                                                    "$1f"    ],
    [/(tive)s$/i,                                                      "$1"     ],
    [/(hive)s$/i,                                                      "$1"     ],
    [/([^f])ves$/i,                                                    "$1fe"   ],
    [/(^analy)ses$/i,                                                  "$1sis"  ],
    [/((a)naly|(b)a|(d)iagno|(p)arenthe|(p)rogno|(s)ynop|(t)he)ses$/i, "$1$2sis"],
    [/([ti])a$/i,                                                      "$1um"   ],
    [/(n)ews$/i,                                                       "$1ews"  ],
    [/s$/i,                                                            ""       ]
    ],
	*/

	function inflectee($obj)
	{
		$this->_inflectee = $obj;
		return $this;
	}

	function pluralized()
	{
		$ut = strtoupper($this->_inflectee);
		if(isset($WeIsI[$ut]))
		{
			return $this;
		}
		if(isset($Irregular[$ut]))
		{
			$this->_inflectee = $Irregular[$ut];
			return $this;
		}
		$len = strlen($this->_inflectee);
		$lastc = $ut[$len-1];
		$lastc2 = substr($ut,$len-2);
		switch ($lastc)
		{
			case 'S':
			case 'X':
				$this->_inflectee = $this->_inflectee . 'es';	
				break;
			case 'Y':
				$this->_inflectee =  substr($this->_inflectee, 0, $len-1) . 'ies';
				break;
			case 'H': 
				if ($lastc2 == 'CH' || $lastc2 == 'SH')
				{
					$this->_inflectee = $this->_inflectee . 'es';
					break;
				}
			default:
				$this->_inflectee = $this->_inflectee . 's';
		}
		return $this;
	}

	/*
	function singularized()
	{
		for (var i = 0; i < Inflector.Inflections.singular.length; i++)
		{
			var regex          = Inflector.Inflections.singular[i][0];
			var replace_string = Inflector.Inflections.singular[i][1];
			if (regex.test(word))
			{
				return word.replace(regex, replace_string);
			}
		}
	}
	*/

	function is()
	{
		return $this->_inflectee;
	}

	function value()
	{
		return $this->is();
	}

	function toName()
	{
		$this->_inflectee = ucfirst($this->_inflectee);
		return $this;
	}

	function toFile()
	{
		$this->_inflectee =
			strtolower($this->_inflectee[0]) .
			preg_replace_callback(
				'/[A-Z]/',
				create_function(
					'$matches',
					'return \'_\'.strtolower($matches[0]);'),
			substr($this->_inflectee, 1));
		return $this;
	}

	function toPath()
	{
		$this->_inflectee = str_replace('.', '/', $this->_inflectee);
		return $this;
	}

	function fromPath()
	{
		$this->_inflectee = str_replace('/', '.', $this->_inflectee);
		return $this;
	}

	function toLower()
	{
		$this->_inflectee = strtolower($this->_inflectee);
		return $this;
	}
}

global $inflector;
$inflector = new Inflector();
?>
