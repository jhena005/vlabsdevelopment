/*
 * Orders.js
 * @author Vanessa Ramirez
 *
 */

var dbadmin_table;
var dbadminphpURL = "/modules/module_shoppingcart/server/dbadmin.php";   //jh original"../server/orders.php";

var dbadmin_open_validation_forms = new Array();


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
        schemaFunctions_load(containerId, nTr, aData[0],aData[2]);
	});

    jQuery("#dataFunctions"+aData[0]).button();
    jQuery("#dataFunctions"+aData[0]).click(function(){  //jh here is the event handler for this button!!
        $(nTr).css("font-weight","bold");
        $(containerId).empty();
        dataFunctions_load(containerId,nTr,aData[0],aData[2]);
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




function schemaFunctions_load(containerId, nTr, id,moduleName)
{

        $(containerId).empty();
        $(containerId).hide();

        $(containerId).load("forms/manageSchema.html", function(){
            $(containerId).show();
                $(containerId+" .cancel").click(function(){
                    $(containerId).slideUp(400, function(){
                        $(containerId).empty();
                        $(nTr).css("font-weight","normal");
                        dbadmin_table.fnClose( nTr );
                    });
                });
            $(containerId+" .cancel").click(function(){
                $(containerId).slideUp(400, function(){
                    $(containerId).empty();
                    $(nTr).css("font-weight","normal");
                    window.alert("id is: " + id );
                    dbadmin_table.fnClose( nTr );
                });
            });
        });
}

function dataFunctions_load(containerId, nTr, id,moduleName)
{

    $(containerId).empty();
    $(containerId).hide();

    $(containerId).load("forms/manageData.html", function(){

        $(containerId+" .cancel").button();
        $(containerId+" .exportD").button();
        $(containerId+" .importD").button();
        $(containerId+" .deleteD").button();
        //$(containerId+" .inputFile").input();
        $(containerId).show();


        $(containerId+" .importD").click(function(){
            /*alert("in dataFunctions_load");*/
            //alert("It works!");
                //$(containerId+" .inputF").click();

             document.getElementById("inputF").click();
            //document.getElementById("inputF").click();
            var control = document.getElementById("inputF");

            control.addEventListener("change", function(event) {

                // When the control has changed, there are new files

                var i = 0,
                    files = control.files,
                    len = files.length;

                for (; i < len; i++) {
                    alert("Filename: " + files[i].name);
                    console.log("Filename: " + files[i].name);
                    alert("Type: " + files[i].type);
                    alert("Size: " + files[i].size + " bytes");
                }

            }, false);

            var reader = new FileReader();
            reader.onload = function(event) {
                var contents = event.target.result;
                console.log("File contents: " + contents);
            };

            reader.onerror = function(event) {
                console.error("File could not be read! Code " + event.target.error.code);
            };

            reader.readAsText(control);
            //alert("wala!");
/*
            if (fullPath) {
                var startIndex = (fullPath.indexOf('\\') >= 0 ? fullPath.lastIndexOf('\\') : fullPath.lastIndexOf('/'));
                var filename = fullPath.substring(startIndex);
                if (filename.indexOf('\\') === 0 || filename.indexOf('/') === 0) {
                    filename = filename.substring(1);
                }
                alert(filename);
            }
*/

            //dbadmin_importData(id,moduleName);
        });

        $(containerId+" .exportD").click(function(){
            /*alert("in dataFunctions_load");*/
            dbadmin_exportData(id,moduleName);
        });

        $(containerId+" .deleteD").click(function(){
            /*alert("in dataFunctions_load");*/
            dbadmin_deleteData(id,moduleName);
        });

        $(containerId+" .cancel").click(function(){
            $(containerId).slideUp(400, function(){
                $(containerId).empty();
                $(nTr).css("font-weight","normal");
                dbadmin_table.fnClose( nTr );
                pre_removeValidationForm(containerId);
            });
             /*alert("in dataFunctions_load");*/
        });
    });

}

function dbadmin_exportData(id,moduleName)
{
    var role = $("#role").val();
    //window.alert("userid = " + userid +  " role is: " + role);
    //var userid = "admin";  //jh changed:$("#userid").val()
    //var role = "admin";  //jh changed:$("#role").val();

    var action = 'exportData';

    //window.alert("BEFORE ajax section role= "+ role);
    if(role=="administrator") {
        $.ajax({
            type: 'POST',
            url: dbadminphpURL,
            dataType: 'text',
            data: {
                action:action,
                modId:id
            },
            success: function (data) {
                //window.alert("in ajax section within success function");
                if(data=='pass'){
                    alert("Data export successful!");
                }else{
                    alert("Data export failed!");
                }

            },
            error: function (XMLHttpRequest, textStatus, errorThrown) {
                removeLoadingDivAfter("#dbadminContainer");
                displayError("#dbadminContainer", errorThrown);
            }
        });

    }

}

function dbadmin_deleteData(id,moduleName)
{
    var role = $("#role").val();
    //window.alert("userid = " + userid +  " role is: " + role);
    //var userid = "admin";  //jh changed:$("#userid").val()
    //var role = "admin";  //jh changed:$("#role").val();

    var action = 'deleteData';

    //window.alert("BEFORE ajax section role= "+ role);
    if(role=="administrator") {

        var r = confirm("Deleting data for module: " + moduleName + " Make sure you have a backup of the data and confirm.");
        if (r == true) {
            $.ajax({
                type: 'POST',
                url: dbadminphpURL,
                dataType: 'text',
                data: {
                    action:action,
                    modId:id
                },
                success: function (data) {
                    //window.alert("in ajax section within success function");
                    if (data == 'pass') {
                        alert("Data deletion successful!");
                    } else {
                        alert("Data deletion failed!");
                    }

                },
                error: function (XMLHttpRequest, textStatus, errorThrown) {
                    removeLoadingDivAfter("#dbadminContainer");
                    displayError("#dbadminContainer", errorThrown);
                }
            });

        }
    }
}


function dbadmin_importData(id,moduleName)
{
    var role = $("#role").val();
    //window.alert("userid = " + userid +  " role is: " + role);
    //var userid = "admin";  //jh changed:$("#userid").val()
    //var role = "admin";  //jh changed:$("#role").val();

    var action = 'importData';

    //window.alert("BEFORE ajax section role= "+ role);
    if(role=="administrator") {



            $.ajax({
                type: 'POST',
                url: dbadminphpURL,
                dataType: 'text',
                data: {
                    action:action,
                    modId:id
                },
                success: function (data) {
                    //window.alert("in ajax section within success function");
                    if (data == 'pass') {
                        alert("Data deletion successful!");
                    } else {
                        alert("Data deletion failed!");
                    }

                },
                error: function (XMLHttpRequest, textStatus, errorThrown) {
                    removeLoadingDivAfter("#dbadminContainer");
                    displayError("#dbadminContainer", errorThrown);
                }
            });

        }

}