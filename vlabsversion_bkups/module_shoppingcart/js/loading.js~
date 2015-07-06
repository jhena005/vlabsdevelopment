
function createLoadingDivAfter(containerId, message){

	var msg = message || "Loading";
	var loadingDivId = containerId.substring(1)+"_loading";
    var div ='<div id="'+loadingDivId+'" class="message">';
    div+= '<span class="image"><img alt="loading" src="/modules/css/loading_1.gif" /></span>';
    div+= '<p class="loading">'+msg+'</p>';
    div+='</div>';

    $(containerId).after(div);
}

function removeLoadingDivAfter(containerId){
	var loadingDivId = containerId.substring(1)+"_loading";

	if($("#"+loadingDivId))
		$("#"+loadingDivId).remove();
}

function createLoadingInId(id){
	$(id).html('<img alt="loading" src="/modules/css/loading_2.gif" />');
}

function removeLoadingInId(id){
	$(id).empty();
}

function displayError(containerId, message, afterFunction){
	var msg = message || "Error";
	var errorDivId = containerId.substring(1)+"_error";
    var div ='<div id="'+errorDivId+'" class="message">';
    div+= '<span class="image"><img src="/modules/css/warning.png" /></span>';
    div+= '<p class="error">'+message+'</p>';
    div+='</div>';

    $(containerId).after(div);

    $("#"+errorDivId).fadeIn(100).delay(4500).slideUp(400, function(){
        if(afterFunction)
        	afterFunction();
        $("#"+errorDivId).remove();
    });

}

function displayMessage(containerId, message, afterFunction){
	var msg = message || "";
	var messageDivId = containerId.substring(1)+"_message";
    var div ='<div id="'+messageDivId+'" class="message">';
    div+= '<span class="image"><img src="/modules/css/check.png" /></span>';
    div+= '<p class="msg">'+message+'</p>';
    div+='</div>';

    $(containerId).after(div);

    $("#"+messageDivId).fadeIn(100).delay(2500).slideUp(400, function(){
        if(afterFunction)
        	afterFunction();
        $("#"+messageDivId).remove();
    });
}

// ui-state-highlight ui-corner-all ui-state-error
