<?php
/* Unit tests
class Config
{
static $path = "/toppath";
}
define('WEBROOT', 'http://test/');
unit_test(); exit;
*/

function unit_test()
{
	print "link_to:\t".link_to('gato','link', array('class'=>'classy'))."\n";
	print "form_tag:\t".form_tag('link')."\n";
	print "button_to:\t".button_to('gato','link', array('class'=>'classy'))."\n";
	print "mail_to:\t".mail_to('Email me', 'test@domain.com', array('class'=>'classy'))."\n";
	print "label_for:\t".label_for('myinputtag', 'Awesome Label', array('class'=>'classy'))."\n";
	print "input_tag:\t".input_tag('myinputtag', 'defaultvalue', array('class'=>'classy'))."\n";
	print "input_password_tag:\t".input_password_tag('mypasswordinputtag', 'defaultvalue', array('class'=>'classy'))."\n";
	print "input_hidden_tag:\t".input_hidden_tag('myhiddeninputtag', 'defaultvalue', array('class'=>'classy'))."\n";
	print "input_file_tag:\t".input_file_tag('myfileinputtag', array('class'=>'classy'))."\n";
	print "textarea_tag:\t".textarea_tag('mytextarea', 'Original Content', array('class'=>'classy'))."\n";
	print "rich_textarea_tag:\t".rich_textarea_tag('mytextarea', 'Original Content', array('class'=>'classy'))."\n";
	print "click_tag:\t".click_tag('mytext', av_callback, array('class'=>'classy'))."\n";
	print "img_tag:\t".img_tag('http://image.gif', array('class'=>'classy'))."\n";
	print "click_img_tag:\t".click_img_tag('http://image.gif', av_callback, array('class'=>'classy'))."\n";
	print "submit_tag:\t".submit_tag('submittag', array('name'=>'ohdosubmit','class'=>'classy'))."\n";
	print "submit_image_tag:\t".submit_image_tag('http://image.gif', array('name'=>'ohdosubmit','class'=>'classy'))."\n";
	print "select_tag:\t".select_tag('selectboxid', null, array('name'=>'selectboxname','class'=>'classy'))."\n";
	print "select_tag:\t".select_tag('selectboxid', 
			array('one' => 'One', 'two' => 'Two'),
			array('name'=>'selectboxname','class'=>'classy'))."\n";
	print "constrained_img_tag:\t".constrained_img_tag("http://image.gif", array('width'=> 50,'height'=> 150,'max_width' =>100,'max_height'=>100))."\n";

#	print "flash_upload_form_tag:\t".flash_upload_form_tag('user/controlpaneluploadavatar', array('form_class'=>'uniForm', 'input_label'=>'New Avatar', 'magic_field'=>'submitform', 'submit_class'=>'submitButton', 'submit_caption'=>'Update Avatar','form_wrapper'=>"<fieldset class='blockLabels'>\n*\n</fieldset>", 'input_wrapper'=>"<div class='ctrlHolder'>\n*\n<div id='feedback'></div></div>", 'feedback_id'=>'feedback', 'submit_wrapper'=>"<div class='buttonHolder'>\n*\n</div>", 'callback'=>av_callback))."\n";

}

// ----------------------------------------------------------------------------
// Reserved stuff
// ----------------------------------------------------------------------------
function _options($options = array())
{
	$html = '';
	foreach ($options as $key => $value)
		$html .= ' '.$key.'="'.$value.'"';
	return $html;
}

function _get_id_from_name($name, $value = null)
{
	// check to see if we have an array variable for a field name
	if (strstr($name, '['))
		$name = str_replace(
			array('[]', '][', '[', ']'),
			array((($value != null) ? '_'.$value : ''), '_', '_', ''),
			$name);
	return $name;
}

// ----------------------------------------------------------------------------
// Links etc.
// ----------------------------------------------------------------------------

/**
 * Canonize a url - not a tag _per se_
 */
function url_for($url)
{
	if(empty($url))
		$url = $_SERVER['PHP_SELF'];
	return Config::$path . $url;
}

/**
 * Create a link to a given uri
 */
function link_to($text, $uri, $options = array())
{
	$options['href'] = url_for($uri);
	return tag('a', $text, $options, TAG_OPEN_CLOSE);
}

/**
 * Create a link to a given uri
 * But it's a button wrapped in its own small form
 */
function button_to($name, $uri, $options = array())
{
	$options['value'] = $name;
	$options['type'] = 'submit';
	return form_tag($target, array('method' => 'post')) . tag('input', null, $options).'</form>';
}

/**
 * Create a 'mail to' link
 */
function mail_to($name, $uri, $options = array())
{
	$options['href'] = 'mailto:'.$uri;
	return tag('a', $name, $options, TAG_OPEN_CLOSE);
}

// ----------------------------------------------------------------------------
// tag!
// ----------------------------------------------------------------------------

define('TAG_SELF_CONTAINED', 'tsc');
define('TAG_OPEN_ONLY', 'too');
define('TAG_OPEN_CLOSE', 'toc');
/**
 * The barest tag possible - used by everybody else but can also be used directly
 */
function tag($name, $content = null, $options = array(), $type = TAG_SELF_CONTAINED)
{
	if (!$name)
		throw new Exception("Sorry, need a tag name");
	$ret = '<' . $name . _options($options);
	switch($type)
	{
		case TAG_SELF_CONTAINED:
			$ret .= ' />';
			break;
		case TAG_OPEN_ONLY:
			$ret .= '>';
			if(!empty($content)) $ret .= $content;
			break;
		default:
			$ret .= '>';
			if(!empty($content)) $ret .= $content;
			$ret .= '</' . $name . '>';
	}
	return $ret;
}

// ----------------------------------------------------------------------------
// Web Forms
// ----------------------------------------------------------------------------

/**
 * Create a standard form opening tag
 */
function form_tag($url= '', $options = array())
{
	if (!isset($options['method']))
		$options['method'] = 'post';
	if(!empty($options['multipart']))
		$options['enctype'] = 'multipart/form-data';
	$options['action'] = url_for($url);
	return tag('form', null, $options, TAG_OPEN_ONLY);
}

// ----------------------------------------------------------------------------
// Input tags
// ----------------------------------------------------------------------------

function label_for($id, $label, $options = array())
{
	if (is_object($label) && method_exists($label, '__toString'))
		$label = $label->__toString();
	return tag('label', $label, array_merge(array('for' => _get_id_from_name($id, null)), $options), TAG_OPEN_CLOSE);
}

function input_tag($name, $value = null, $options = array())
{
	return tag('input', null, array_merge(array('type' => 'text', 'name' => $name, 'id' => _get_id_from_name($name, $value), 'value' => $value), $options));
}

function input_password_tag($name = 'password', $value = null, $options = array())
{
	$options['type'] = 'password';
	return input_tag($name, $value, $options);
}

function input_hidden_tag($name, $value = null, $options = array())
{
	$options['type'] = 'hidden';
	return input_tag($name, $value, $options);
}

function input_file_tag($name, $options = array())
{
	$options['type'] = 'file';
	return input_tag($name, null, $options);
}

function textarea_tag($name, $content = '', $options = array())
{
	return tag(
		'textarea', (is_object($content) ? $content->__toString() : $content),
		array_merge(array('name' => $name, 'id' => (empty($options['id']) ? $name : $options['id'])),
		$options),
		TAG_OPEN_CLOSE);
}

function rich_textarea_tag($name, $content = '', $options = array())
{
	$id = (empty($options['id']) ? $name : $options['id']);
	$ret = textarea_tag($name, $content, $options);
	$prefix = Config::$path;
	$ret .= <<<EOB

<script type="text/javascript" src="{$prefix}libs/nicEdit.js"></script>
<script type="text/javascript">
new nicEditor({iconsPath:'{$prefix}libs/nicEditorIcons.gif', bbcode:true}).panelInstance('{$id}');
</script>

EOB;

	return $ret;
}

// ----------------------------------------------------------------------------
// Submit buttons
// ----------------------------------------------------------------------------

function submit_tag($value = 'Save changes', $options = array())
{
	return tag('input', null, array_merge(array('type' => 'submit', 'name' => 'commit', 'value' => $value), $options));
}

function submit_image_tag($uri, $options = array())
{
	return tag('input', null, array_merge(array('type' => 'image', 'name' => 'commit', 'src' => $uri), $options));
}

function reset_tag($value = 'Reset', $options = array())
{
	return tag('input', null, array_merge(array('type' => 'reset', 'name' => 'reset', 'value' => $value), $options));
}

/**
 * Returns a link that will invoke a js callback
 */
function click_tag($text, $callback)
{
	return link_to($text, '#', array('onclick' => 'return '.$callback.'();'));
}

function img_tag($uri, $options)
{
	return tag('img', null, array_merge(array('src' => $uri), $options), TAG_SELF_CONTAINED);
}

function click_img_tag($uri, $callback)
{
	return tag('img', null, array('src' => $uri, 'onclick' => 'return '.$callback.'();'), TAG_SELF_CONTAINED);
}

function constrained_img_tag($uri = '', $options = array())
{
	if(empty($options['width']))  $options['width']  = 100;
	if(empty($options['height'])) $options['height'] = 100;
	if($options['width'] > $options['height'])
	{
		$desiredWidth  = $options['max_width'];
		$desiredHeight = intval($options['height'] * ($desiredWidth / $options['width']));
	}
	else
	{
		$desiredHeight = $options['max_height'];
		$desiredWidth  = intval($options['width'] * ($desiredHeight / $options['height']));
	}
	$options['width']  = $desiredWidth;
	$options['height'] = $desiredHeight;
	unset($options['max_width']);
	unset($options['max_height']);

	return tag('img', null, array_merge(array('src' => $uri), $options), TAG_SELF_CONTAINED);
}

function options_for_select($options = array())
{
	$html = '';
	foreach ($options as $key => $value)
	{
			$html .= tag('option', $value, array('value' => $key), TAG_OPEN_CLOSE)."\n";
	}

	return $html;
}

function select_tag($name, $option_tags = null, $options = array())
{
	$id = $name;
	if (isset($options['multiple']) && $options['multiple'] && substr($name, -2) !== '[]')
		$name .= '[]';
	if (is_array($option_tags))
		$option_tags = options_for_select($option_tags);
	return tag('select', $option_tags,
		array_merge(array('name' => $name, 'id' => _get_id_from_name($id)), $options), TAG_OPEN_CLOSE);
}

function radiobutton_tag($name, $value, $checked = false, $options = array())
{
	$options = array_merge(array('type' => 'radio', 'name' => $name, 'id' => _get_id_from_name($name.'[]', $value), 'value' => $value), $options);

	if ($checked)
		$options['checked'] = 'checked';

	return tag('input', null, $options);
}

function checkbox_tag($name, $value = '1', $checked = false, $options = array())
{
	$options = array_merge(array('type' => 'checkbox', 'name' => $name, 'id' => _get_id_from_name($name, $value), 'value' => $value), $options);

	if ($checked)
		$options['checked'] = 'checked';

	return tag('input', null, $options);
}

function util_checkbox_tag($name, $value=1)
{
	return checkbox_tag($name, true, false, array('value' => $value, 'class' => $name.' hide'));
}

function enable_select_multi_tag($name)
{
	$ret = <<<EOB

<script type="text/javascript">
$(document).ready(
	function()
	{
		$('.$name').selectMulti();
	}
);
</script>

EOB;
	return $ret;

}

function reveal_tags_tag($name, $label = null, $options = array())
{
	$ret = <<<EOB

<script type="text/javascript">
reveal_$name = function()
{
	$('.$name').toggle();
	$('.{$name}_extra').toggle();
}
</script>
<span>

EOB;
	$cbName = 'reveal_'.$name.'_db';
	if($label)
		$ret .= "<span class=\"{$name}_extra\">" . $label . "</span>\n";
	$ret .= checkbox_tag($cbName, true, false,
		array('onclick' => 'reveal_ms();') + $options).
		"\n</span>\n";
	return $ret;
}

static $flash_upload_form_tag_counter = 0;
function flash_upload_form_tag($uri = '', $options = array())
{
	$flash_upload_form_tag_counter ++;
	$uri = Config::$path . $uri;

	$prefix = Config::$path;
	$ret = <<<EOB

<script type="text/javascript" src="{$prefix}libs/jquery.asyncUploader.js"></script>

EOB;
	$form_id   = 'flash_upload_form_' . $flash_upload_form_tag_counter;
	$input_id  = 'flash_upload_input_' . $flash_upload_form_tag_counter;
	$submit_id = 'flash_upload_submit_' . $flash_upload_form_tag_counter;

	$form_fragment = '';

	$form_tag_options = array(
		'method'=>'post',
		'id'=>$form_id,
		'enctype'=>'multipart/form-data');
	if(isset($options['form_class']))
		$form_tag_options['class'] = $options['form_class'];
	$ret .= form_tag('user/controlpanel', $form_tag_options) . "\n";

	$input_fragment = '';
	if(isset($options['input_label']))
		$input_fragment .= label_for($input_id, $options['input_label']) . "\n";

	$input_fragment .= input_file_tag($input_id);

	if(isset($options['input_wrapper']))
		$input_fragment = str_replace('*', $input_fragment, $options['input_wrapper']) . "\n";
	$form_fragment .= $input_fragment;

	$submit_fragment = '';
	if(isset($options['magic_field']))
	{
		if(is_array($options['magic_field']))
			$submit_fragment .= input_hidden_tag($options['magic_field']) . "\n"; // FIXME
		else
			$submit_fragment .= input_hidden_tag($options['magic_field']) . "\n";
	}

	$submit_tag_options = array('id'=>$submit_id);
	if(isset($options['submit_class']))
		$submit_tag_options['class'] = $options['submit_class'];
	$submit_button_caption = isset($options['submit_caption']) ? $options['submit_caption'] : 'Submit';
	$submit_fragment .= submit_tag($submit_button_caption, $submit_tag_options);

	if(isset($options['submit_wrapper']))
		$submit_fragment = str_replace('*', $submit_fragment, $options['submit_wrapper']) . "\n";
	$form_fragment .= $submit_fragment;

	if(isset($options['feedback_id']))
		$feedback_id = $options['feedback_id'];

	if(isset($options['form_wrapper']))
		$form_fragment = str_replace('*', $form_fragment, $options['form_wrapper']);

	if(isset($options['loading_message']))
		$loading_msg = $options['loading_message'];
	else
		$loading_msg = 'Uploading, please wait.';
	if(isset($options['complete_message']))
		$complete_msg = $options['complete_message'];
	else
		$complete_msg = 'File Uploaded.';
	if(isset($options['callback']))
		$callback_name = $options['callback'];
	else
		$callback_name = 'null';

	$ret .= <<<EOB
{$form_fragment}
</form>
<script type="text/javascript">
$('#{$form_id}').handleUpload({
submitButtonId: '{$submit_id}',
fileHandlerUrl: '{$uri}',
printTxtIn: '{$feedback_id}',
loadingMsgs: '{$loading_msg}',
completeMsg: '{$complete_msg}',
callback: {$callback_name}
});
</script>

EOB;
	return $ret;
}

/**
 * Special Tags
 */
function attachments_tag($frame)
{
	$url = url_for('file/attachments/');
	$ret = <<<EOB

<style type="text/css">
#$frame {
	width: 100%;
	height: 280px;
	border: 0;
}
</style>
<script type="text/javascript">
	var revealed_attachments = false;
    function reveal_attachments()
	{
		$('#$frame').toggle();
		if(revealed_attachments) return false;
		revealed_attachments = true;
		window.frames['$frame'].location = "$url";
		return false;
	}
</script>

EOB;
	$ret .= click_tag('Attachments', 'reveal_attachments');
	return $ret;
}

function emoticons_tag($frame)
{
	$url = url_for('file/emoticons/');
	$ret = <<<EOB

<style type="text/css">
#$frame {
	width: 100%;
	height: 280px;
	border: 0;
}
</style>
<script type="text/javascript">
	var revealed_emoticons = false;
    function reveal_emoticons()
	{
		$('#$frame').toggle();
		if(revealed_emoticons) return false;
		revealed_emoticons = true;
		window.frames['$frame'].location = "$url";
		return false;
	}
</script>

EOB;
	$ret .= click_tag('Emoticons', 'reveal_emoticons');
	return $ret;
}
?>
