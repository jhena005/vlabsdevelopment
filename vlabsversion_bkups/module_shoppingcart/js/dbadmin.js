/*
 * Orders.js
 * @author Vanessa Ramirez
 *
 */

var dbadmin_table;
var dbadminphpURL = "/modules/module_shoppingcart/server/dbadmin.php";   //jh original"../server/orders.php";



function openDbadminTab(){
	 $('#tabs').tabs("select","#dbadminTab");
}

function dbadmin_reload()
{


	 $("#dbadminWrapper").show();
    
    $('#dbadminContainer').empty();
    
	createLoadingDivAfter("#dbadminContainer","Loading DB Admin");

	var role = $("#role").val();
	//window.alert("userid = " + userid +  " role is: " + role);
	//var userid = "admin";  //jh changed:$("#userid").val()
	//var role = "admin";  //jh changed:$("#role").val();
	
	var action = 'getModules';

	 //window.alert("BEFORE ajax section role= "+ role);
    $.ajax({
        type: 'POST',
        url: dbadminphpURL,
        dataType: 'json',
        data: {
            action: action,
            role: role
        },
        success: function(data){
         //window.alert("in ajax section within success function");
        	removeLoadingDivAfter("#dbadminContainer");
          	
        	$('#dbadminContainer').html( '<table cellpadding="0" cellspacing="0" border="0" class="display" id="dbadminTable"></table>' ); //jh $('resource').html('something') adds something to resource
           
        	//console.log(data);
        	if(role=="administrator"){
        		
	        	
	        	dbadmin_table = $("#dbadminTable").dataTable({
	                "aaData": data,
	                "aaSorting": [[ 1, "desc" ]],
	                "aoColumns": [
	                {  "bVisible": false },
	                {  "bVisible": false },
	                { "sTitle": "Module" }
	                 ],
	        		"bJQueryUI": true,
	        		"bAutoWidth": false,
	        		"sPaginationType": "full_numbers"
	        	});
        		
        	}

            $("#dbadminTable").removeAttr("style");
            $('#dbadminTable tbody tr td').die();
            $('#dbadminTable tbody tr td').live('click', dbadmin_rowClickHandler );

        },
        error: function(XMLHttpRequest, textStatus, errorThrown){
        	removeLoadingDivAfter("#dbadminContainer");
        	displayError("#dbadminContainer",errorThrown);
        }
    });
}


function dbadmin_rowClickHandler(){

	var nTr = this.parentNode;
	var open=false;
	

	try{
		if($(nTr).next().children().first().hasClass("ui-state-highlight"))
			open=true;
	}catch(err){
		alert(err);
	}
	
	if (open){
		/* This row is already open - close it */
		dbadmin_table.fnClose( nTr );
		$(nTr).css("color","");
	}else{
		dbadmin_openDetailsRow(nTr);
	}
}


function dbadmin_openDetailsRow(nTr){


	dbadmin_table.fnOpen( nTr, dbadmin_formatDetails(dbadmin_table, nTr), "ui-state-highlight" );
	var aData = dbadmin_table.fnGetData( nTr );

	//window.alert("in ord_openDetailsRow: " + "#seeDetails"+aData[0] );	


	var containerId = "#dbadminDetails"+aData[0];
	jQuery("#schemaFunctions"+aData[0]).button();
	jQuery("#schemaFunctions"+aData[0]).click(function(){  //jh here is the event handler for this button!!
        $(nTr).css("font-weight","bold");
        $(containerId).empty();
        schemaFunctions_load(containerId, nTr, aData[0]);
	});

    jQuery("#dataFunctions"+aData[0]).button();
    jQuery("#dataFunctions"+aData[0]).click(function(){  //jh here is the event handler for this button!!
        dataFunctions_load(aData[0]);
    });


}

function dbadmin_formatDetails ( oTable, nTr )
{	
	var role = $("#role").val();	
	var aData = oTable.fnGetData( nTr );

	var sOut = '';
	sOut += '<div id="dbadminDetails'+ aData[0]+'">';
	
	sOut += '	<div class="buttonColumnDetails">';
	sOut += '	<button id="schemaFunctions'+aData[0]+'">Schema admin</button>';
	sOut += '	<button id="dataFunctions'+aData[0]+'">Data admin</button>';

	sOut += '	</div>';
	sOut += '</div>';
	//window.alert("in ord_formatDetails: " + sOut );
	return sOut;
}




function schemaFunctions_load(containerId, nTr, id)
{
        $(containerId).empty();
        $(containerId).hide();

        $(containerId).load("forms/manageSchema.html", function(){

        $(containerId).show();

                                $(containerId+" .cancel").click(function(){
                                    $(containerId).slideUp(400, function(){
                                        $(containerId).empty();
                                        $(nTr).css("color","");
                                        dbadmin_table.fnClose( nTr );
                                        pre_removeValidationForm(containerId);
                                    });
                                });
        });
}

function dataFunctions_load(moduleid)
{

    return 0;
}

function createLoadingDivAfter(containerId, message){

    var msg = message || "Loading";
    var loadingDivId = containerId.substring(1)+"_loading";
    var div ='<div id="'+loadingDivId+'" class="message">';
    div+= '<span class="image"><img alt="loading" src="/modules/css/loading_1.gif" /></span>';
    div+= '<p class="loading">'+msg+'</p>';
    div+='</div>';

    $(containerId).after(div);
}

