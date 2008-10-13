/**
 * @package Lenses
 * @copyright (c) Chris F. Ravenscroft
 * @license See 'license.txt'
 */

notify_view = function(notify_msg)
{
	if(parent)
		var myDoc = parent.document;
	else
		var myDoc = document;
	var notifyfield = myDoc.getElementById('notifyfield');
	if(!notifyfield)
	{
		var div = myDoc.createElement('div');
		div.id = 'notifyfield';
		myDoc.body.appendChild(div);
	}

	if(parent)
		parent.document.getElementById('notifyfield').innerHTML = notify_msg;
	else
		document.getElementById('notifyfield').innerHTML = notify_msg;
}

reset_notify_view = function()
{
	notify_view('');
}

notify_msg = function()
{
	if(parent)
		var myDoc = parent.document;
	else
		var myDoc = document;
	var notifyfield = myDoc.getElementById('notifyfield');
	if(!notifyfield)
		return '';
	return notifyfield.innerHTML;
}

redirect = function(uri, message)
{
	// Hmm this is interesting: Javascript doesn't know what uri is and as a result,
	// string methods are not available.
	var uris = new String(uri);
	var p = uris.indexOf("?");
	if(-1 < p)
		var l = uris.substr(0, p);
	else
		var l = uris;
	window.location = l + '?msg=' + message;
}

// ---------------------------
jQuery.fn.selectMulti = function()
{
	var _allCheckboxes = this;
	var _lastSelected;

	jQuery(this).click(
		function(e)
		{
			if(!_lastSelected)
			{
				_lastSelected = this;
				return;
			}

			if(e.shiftKey)
			{
				var si = _allCheckboxes.index(this);
				var li = _allCheckboxes.index(_lastSelected);
				if(li == si) return;
				else if(li < si)
				{
					var tmp = si;
					si = li;
					li = tmp;
				}

				var isChecked = _lastSelected.checked;

				for(var i=si; i<li; i++)
				{
					_allCheckboxes[i].checked = isChecked;
				}
			}

			_lastSelected = this;
		});
}


/** @todo WTH is all the crap below? */
// I suspect it's completely dead code.
var _b1, _b2;
var elements = new Array();

// ---------------------------

visually_promote = function(b1, b2) {
	_b1 = $(b1);
	_b2 = $(b2);
	_b1.hide('normal', function() {
		_b1.insertAfter(_b2);
		_b2.hide('normal', function() {
			_b1.show('normal', function() {
				_b2.show('normal');
			});
		});
	});
}

complete_promotion = function(idx, count) {
	alert(count);
}

try_promoting = function(e) {
	var idx = e.target.parentNode.id;
	// We just elected to promote this feature.
	// Let's ask the backend: what is our current setup? promote thru clicks?
	// do I need to be logged in? is there a donation involved?
	//alert(elements['b1']);
//	visually_promote('#b1', '#b2');
	x_try_promoting_item(function(r) {
		if(r.diag == 'ok')
		{
			complete_promotion(idx, r.count);
		}
	});
}

// ---------------------------

$(document).ready(function() {
	$('.item .plus').bind('click', try_promoting);
});
