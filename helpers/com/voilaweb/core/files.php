<?php
class FileException extends Exception
{
private $_type;
static	$ERROR = 'error',
	$EXT   = 'ext',
	$SIZE  = 'size',
	$DIM   = 'dimensions',
	$TYPE  = 'type',
	$MOVE  = 'move';

	public function __construct($type, $msg = '')
	{
		$this->_type = $type;
		parent::__construct($msg);
	}

	function type()
	{
		return $this->_type;
	}

	function __toString()
	{
		return $this->type() . ' - ' . parent::__toString();
	}
}

class Files
{
	// Your friendly non-instantiable neighborhood class!
	private function __construct() {}

	static function neuter($fileName)
	{
		return str_replace(
			array(
				'.', '/', '\\',
			),
			array(
				'_', '', '',
			),
			$fileName
		);
	}

	static function upload($fieldName  = null, $options  = array())
	{
		$allowedExt = isset($options['exts']) ? $options['exts'] : array('jpg', 'jpeg', 'gif', 'png');
		$maxSize    = isset($options['size']) ? $options['size'] : 0;
		if($fieldName)
			$file = $_FILES[$fieldName];
		else
			$file = array_shift($_FILES);

		if(!empty($file['error']))
		{
			throw new FileException(FileException::$ERROR, "Error: ".$file['error']);
		}
		list($fileName, $fileExt) = explode('.', basename($file['name']));
		if(empty($fileExt) || !in_array($fileExt, $allowedExt))
		{
			throw new FileException(FileException::$EXT,
				'Invalid file extension: ' . $fileExt . ' - Expected: ' .
				implode(',', $allowedExt));
		}
		if($maxSize>0 && $file['size'] > $maxSize)
		{
			throw new FileException(FileException::$SIZE,
				'Over allowed size: ' . $file['size'] . ' > ' . $maxSize);
		}
		$destPath = ROOT . '/files/' . (isset($options['dir']) ? ($options['dir'] . '/') : '');
		$name = isset($options['name']) ? $options['name'] : self::neuter($fileName);
		if(isset($options['prefix']))
			$name = $options['prefix'] . $name;
		$fullDestName = $destPath . $name . '.' . $fileExt;
		if(!move_uploaded_file($file['tmp_name'], $fullDestName))
			throw new FileException(FileExceptiom::$MOVE,
				'Unable to move ' . $file['tmp_name']." to ". $fullDestName);
		// Return files info
		$info = array(
			'filename' => $fullDestName,
			'ext'      => $fileExt,
			'size'     => $file['size']);
		switch($fileExt)
		{
			case 'png':
			case 'jpg':
			case 'jpeg':
			case 'gif':
				list($info['width'], $info['height']) = getImageSize($fullDestName);
				break;
		}
		return $info;
	}

	static function makeThumbnail($sourcePath, $destPath, $maxWidth, $maxHeight, $forcedType=null)
	{
		list($width, $height, $type) = getImageSize($sourcePath);
		if($width <= 0 || $height <= 0)
			throw new FileException(
					FileException::$DIM,
				'Problem with source image dimensions');
		if($width > $height)
		{
			$desiredWidth  = $maxWidth;
			$desiredHeight = intval($height * ( $desiredWidth / $width));
		}
		else
		{
			$desiredHeight = $maxHeight;
			$desiredWidth  = intval($width * ($desiredHeight / $height));
		}
		switch($type)
		{
			case IMAGETYPE_PNG:
				$img = imagecreatefrompng($sourcePath);
				break;
			case IMAGETYPE_JPEG:
				$img = imagecreatefromjpeg($sourcePath);
				break;
			case IMAGETYPE_GIF:
				$img = imagecreatefromgif($sourcePath);
				break;
			default:
				throw new FileException(
					FileException::$TYPE,
					'Problem with source image type: ' . image_type_to_mime_type($type));
		}
		$desiredImage = imagecreatetruecolor($desiredWidth, $desiredHeight);
		imagecopyresampled(
			$desiredImage, $img,
			0, 0, 0, 0,
			$desiredWidth, $desiredHeight,
			$width, $height);
		if($forcedType)
			$type = $forcedType;
		switch($type)
		{
			case IMAGETYPE_PNG:
				imagepng($desiredImage, $destPath);
				break;
			case IMAGETYPE_JPEG:
				imagejpeg($desiredImage, $destPath);
				break;
			case IMAGETYPE_GIF:
				imagegif($desiredImage, $destPath);
				break;
			default:
				throw new FileException(
					FileException::$TYPE,
					'Problem with destination image type: ' . image_type_to_mime_type($type));
		}
		imagedestroy($desiredImage);
	}
}
?>
