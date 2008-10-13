<?php
function getModule_nicEdit()
{
	return new NicEdit();
}

class NicEdit implements Editor
{
	function __construct()
	{
	}

	function present($areaId)
	{
		$path = Config::$path;
		$header = <<<EOB
<script language="javascript" type="text/javascript" src="{$path}libs/modules/nicEdit/nicEdit.js"></script>

EOB;
		addheader($header);
		$js = <<<EOB
var _editor = new Object();
_editor.command = function(type, arg)
{
	var instance = this.editor.selectedInstance;
	if(!instance) instance = this.editor.lastSelectedInstance;
	if(!instance) return;
	instance.nicCommand(type, arg);
}
_editor.addHTML = function(content)
{
	this.command('insertHTML', content);
};

_editor.addImage = function(uri)
{
	this.command('insertImage', uri);
};

$(document).ready(
	function()
	{
		_editor.editor = new nicEditor({
			iconsPath:'{$path}libs/modules/nicEdit/nicEditorIcons.gif',
			fullPanel:true
		}).panelInstance('$areaId');
	}
); 

EOB;
		addjs($js);
	}
}
?>
