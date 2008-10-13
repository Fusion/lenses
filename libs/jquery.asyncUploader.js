/*
	jQuery asynchronous fileuploader
	
	author: 	Jakob Dam Jensen
	contact:	jakobdj@gmail.com
	date:		2007-09-11
	version: 	0.1
	
*/

(function($) {
	
	$.fn.handleUpload = function(options) {
	
		// replace empty options with default option values.
		options = jQuery.extend({
			loadingMsg:		"Uploading, please wait.",
			completeMsg:	"File uploaded.",
			fileHandlerUrl:	"",
			iframeId:		"secretIframe",
			disableForm:	false,
			showErrors:		true,
			printTxtIn:		"",
			submitButtonId:	"",
			callback: null
		}, options);
		  
		var curForm = $(this);
		
		if(options['submitButtonId'] == ""){
			pe('submitButtonId not set. This will not work.', options['showErrors']);
			return false;
		}
		if(options['fileHandlerUrl'] == ""){
			pe('fileHandlerUrl not set. This will probably not work.', options['showErrors']);
			return false;
		}	
		
		$("#"+options['submitButtonId']).click(function(){
			$("#"+options['printTxtIn']).html(options['loadingMsg']);
			if(createIframe(options['iframeId'])){
				setupForm($(curForm).attr("id"), options['iframeId'], options['fileHandlerUrl']);
				bindIframeEvents(options['iframeId'], options);
			}
		});
	}
	
	// This is used to se if the iFrame content is done loading.
	var bindIframeEvents = function(id, options)
	{
		$("#"+id).load(function(){ 
			$("#"+id).ready(function(){
				$("#"+options['printTxtIn']).html(options['completeMsg']);
				$("#"+id).remove(); // This makes Firefox never stop loading. It makes sense, but how do I fix it?
				if(options['callback'])
					options['callback']();
			});
		});
	}
	
	
	var pe = function(errorMsg, showIt)
	{
		if(showIt)
			alert(errorMsg);	
	}
		
	var setupForm = function(formId, iframeId, url_action)
	{
		$("#"+formId).attr("target",iframeId);
		$("#"+formId).attr("action",url_action);
		$("#"+formId).attr("method","post");
		$("#"+formId).attr("enctype","multipart/form-data");
		$("#"+formId).attr("encoding","multipart/form-data");
		
		$("#"+formId).submit();
	}
	
	var createIframe = function(id)
	{
		if($("body").append('<iframe id="'+id+'" name="'+id+'" style="width: 0; height: 0; border: 0px;" width="0" height="0" border="0"/>'))
			return true;
	}
})(jQuery);
