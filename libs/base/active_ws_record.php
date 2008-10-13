<?php
abstract class ActiveWSRecord implements ApplicationModel
{
	var $name;
	static	$POST = 'post',
		$GET = 'get';

	function __construct($modelname = null)
	{
		global $inflector;
		if(empty($modelname))
			$modelname = get_class($this);
		$this->name = get_class($this);
	}

	abstract function load($key);

	abstract function find($mode, $condition=null, $extra=array(), $bindArr=false, $pKeyArr=false);

	function __toString()
	{
		return "<pre>\n" . var_export($this) . "</pre>\n";
	}

	function request($prefix, $action, $domain = null, $parameters = array(), $options = array())
	{
		$host = 'localhost';
		$url = '/';
		$port = 80;
		if(is_array($prefix))
		{
			if(isset($prefix['host']))
				$host = $prefix['host'];
			if(isset($prefix['url']))
				$url = $prefix['url'];
			if(isset($prefix['port']))
				$port = $prefix['port'];
		}
		else
		{
			$host = $prefix;
		}
		if(!isset($options['method']))
			$options['method'] = self::$GET;
		if(!isset($options['timeout']))
			$options['timeout'] = 10;

		switch($method['method'])
		{
			case self::$POST:
				$uri =	$host . $url .
					(empty($domain) ? '' : $domain . '/') .
					$action;

				$postData = ''; $separator = '';
				foreach($parameters as $key => $value)
				{
					$postData .= $separator . $key . '=' . $value;
					$separator = '&';
				}
				$curl = curl_init();
				curl_setopt($curl, CURLOPT_URL, $uri);
				curl_setopt($curl, CURLOPT_POSTFIELDS, $postData);
				curl_setopt($curl, CURLOPT_HEADER, 0);
				curl_setopt($curl, CURLOPT_POST, 1);
				curl_setopt($curl, CURLOPT_TIMEOUT, $options['timeout']);
				curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
				$reply = curl_exec($curl);
				$ret = array(
					'code' => curl_getinfo($curl, CURLINFO_HTTP_CODE),
					'raw' => $reply
				);
			default:
				$uri =	$url .
					(empty($domain) ? '' : $domain . '/') .
					$action;

				$out = "GET $uri HTTP/1.1\r\nHost: $host\r\nConnection: Close\r\n\r\n";
				$fp = fsockopen($host, $port);
				stream_set_timeout($fp, $options['timeout']);
				fwrite($fp, $out);
				$headers = '';
				$reply = '';
				$readingHeaders = true;
				while($s = fgets($fp))
				{
					if($readingHeaders)
					{
						if($s[0]=="\n" || $s[0]=="\r")
						{
							$readingHeaders = false;
							continue;
						}
						$headers .= $s;
					}
					else
					{
						$reply .= $s;
					}
				}
				$info = stream_get_meta_data($fp);
				fclose($fp);
				$code = 200;
				if($info['timed_out'])
					$code = 530;
				$ret = array(
					'code'  => $code,
					'headers' => $headers,
					'raw' => $reply
				);
		}

		// Use theses two lines instead if json is not compiled into
		// your version of PHP:
		//
		// $json = new Services_JSON();
		// $ret['php'] = $json->decode($ret['raw']);
		//
		// You will need to download the library from
		// http://pear.php.net/pepr/pepr-proposal-show.php?id=198
		// and put it in libs/

		$ret['php'] = json_decode($ret['raw']);
		return $ret;
	}
}
?>
