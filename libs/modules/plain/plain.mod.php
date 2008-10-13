<?php
function getModule_plain()
{
	return new plain();
}

class plain implements Editor
{
	function __construct()
	{
	}

	function present($areaId)
	{
		$path = Config::$path;
		$js = <<<EOB
var _editor = new Object();
_editor.command = function(type, arg)
{
	var post = \$('#textInput')[0];
	if(!post) return;
	if(type == 'insertImage')
	{
		arg = '<img src="' + arg + '" />';
	}
	if(post.selectionStart)
	{
		// Mozilla: soooooooo easy!!
		var start = post.selectionStart;
		var end = post.selectionEnd;
		if(start==end) // No selection
			post.value = post.value.substring(0, start) + arg + post.value.substring(end, post.value.length);
		else
			post.value =
				post.value.substring(0, start) + arg + post.value.substring(start,end) +
				post.value.substring(end, post.value.length);
	}
	else if (post.createTextRange)
	{
		// Apparently there's a bug in IE 5.x and it ends up creating this range on the current document
		// rather than the opener doc...
		post.focus(); // Focus so that current active selection is within that particular textarea
		post.caretPos = document.selection.createRange().duplicate(); // Duplicate the selection range to work with it
		post.caretPos.text = what + post.caretPos.text; // Insert
	}
	else
	{
		post.value += arg; // Meh...
	}
	post.focus();
}

_editor.addHTML = function(content)
{
	this.command('insertHTML', content);
};

_editor.addImage = function(uri)
{
	this.command('insertImage', uri);
};

EOB;
		addjs($js);
	}
}
?>
