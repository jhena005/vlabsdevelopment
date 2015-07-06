/*
 * packages.js
 * @author Vanessa Ramirez
 *
 */

var packagesphpURL = '/modules/module_shoppingcart/server/packages.php';

var pac_table;
var pac_open_validation_forms = new Array();

var pacitem_table;
var pacitem_open_validation_forms = new Array();

/**PACKAGES SECTION**/

function pac_reload()
{
	createLoadingDivAfter("#packagesContainer","Loading packages");
		
    $.ajax({
        type: 'POST',
        url: packagesphpURL,
        dataType: 'json',
        data: {
            action: 'reloadPackages'
        },
        success: function(data){
        	
        	removeLoadingDivAfter("#packagesContainer");
    	
        	$('#packagesContainer').html( '<table cellpadding="0" cellspacing="0" border="0" class="display" id="packagesTable"></table>' );
            
        	pac_table = $("#packagesTable").dataTable({
                "aaData": data,
                "aaSorting":[[6,'desc']],
                "aoColumns": [
                {  "bVisible": false },
                { "sTitle": "Name" },
                {  "bVisible": false },
           		{"sClass": "center", "sTitle": "Price" , "fnRender": function (oObj) {  return oObj.aData[3]=="1" ? '$'+oObj.aData[5] : "Not Billable"; } },
           		{ "sTitle": "Active" , "fnRender": function (oObj) {  return oObj.aData[4]=="1" ? "Yes" : "No"; } },
                {  "bVisible": false },
           		{  "bVisible": false }
                ],
        		"bJQueryUI": true,
        		"bAutoWidth": false,
        		"sPaginationType": "full_numbers"
        	});

        $("#packagesTable").removeAttr("style");
        $('#packagesTable tbody tr td').die();
        $('#packagesTable tbody tr td').live('click', pac_rowClickHandler );
               	
        },
        error: function(XMLHttpRequest, textStatus, errorThrown){
        	removeLoadingDivAfter("#packagesContainer");
        	displayError("#packagesContainer",errorThrown);

        }
    });

}

function pac_rowClickHandler(){

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
		pac_table.fnClose( nTr );
		$(nTr).css("color","");
	}else{
		pac_openDetailsRow(nTr);
	}
}


function pac_openDetailsRow(nTr){

	pac_table.fnOpen( nTr, pac_formatPackageDetails(pac_table, nTr), "ui-state-highlight" );
	
	
	var aData = pac_table.fnGetData( nTr );
	
	$("#showPackageDetails"+aData[0]).button();
	$("#modifyPackage"+aData[0]).button();
	$("#deletePackage"+aData[0]).button();
	
	var divId = "#packageDetails"+aData[0];
	
	$("#showPackageDetails"+aData[0]).click(function(){
		pacitem_reload(aData[0], aData[1]);
	});
	
	$("#modifyPackage"+aData[0]).click(function(){
		$(nTr).css("color","#c5dbec");
		$(divId).empty();
		pac_openForm(divId, false, nTr, aData[0]);
		
	});
	
	$("#deletePackage"+aData[0]).click(function(){
		pac_deletePackage(divId, nTr);
	});
	
}

function pac_formatPackageDetails ( oTable, nTr )
{
	var aData = oTable.fnGetData( nTr );
	var id = aData[0];
	var sOut = '';
	var sOut = '';
	sOut += '<div id="packageDetails'+id+'">';
	
	sOut += '	<div class="buttonColumnDetails">';
	sOut += '		<button id="showPackageDetails'+aData[0]+'">Details</button>';
	sOut += '		<button id="modifyPackage'+aData[0]+'">Modify</button>';
	sOut += '		<button id="deletePackage'+aData[0]+'">Delete</button>';
	sOut += '	</div>';
	if(aData[2]){
		sOut += '	<div class="descriptionDetails">';
		sOut += '		<p> Description:'+aData[2]+'</p>';
		sOut += '	</div>';
	}

	sOut += '</div>';
	
	return sOut;
}


function pac_openForm(containerId, add, nTr, packageId)
{
	
	$(containerId).empty();
	$(containerId).hide();

	$(containerId).load("forms/createPackage.html", function(){
		
		pac_hideAllFormFields(containerId);

		if(add){
			
			$(containerId).addClass("ui-state-highlight");
			
			pac_showForm(containerId);
			
			$(containerId+" .submit").button();
			$(containerId+" .cancel").button();

			$(containerId + " .submit").click(function() {

				if(pac_isValidForm(containerId)){
					var name  = $(containerId + " .namePackage").val();
					var description  = $(containerId + " .descriptionPackage").val();
					var notbillable = $(containerId + " .notbillablePackage").is(':checked');
					var active = $(containerId + " .activePackage").is(':checked');
	
					$(containerId).slideUp(400, function(){	    			 
						pac_addPackage(containerId,name, description, notbillable, active);
						$(containerId).empty();
						$("#add-package").button("enable");
						pac_removeValidationForm(containerId); 
					});
				}
			});

			$(containerId+" .cancel").click(function(){
				$(containerId).slideUp(400, function(){	    			 
					$(containerId).empty();
					$("#add-package").button("enable");
					pac_removeValidationForm(containerId); 
				});

			});

		}else{

			pac_fillOutForm(containerId,packageId, nTr);  
			$(containerId+" .submit").button();
			$(containerId+" .cancel").button();

			$(containerId + " .submit").click(function() {
				
				if(pac_isValidForm(containerId)){
					var name  = $(containerId + " .namePackage").val();
					var description  = $(containerId + " .descriptionPackage").val();
					var notbillable = $(containerId + " .notbillablePackage").is(':checked');
					var active = $(containerId + " .activePackage").is(':checked');
					
					$(containerId).slideUp(400, function(){	    			 
						pac_modifyPackage(containerId, nTr, packageId, name, description, notbillable, active);
						$(containerId).empty();
						pac_removeValidationForm(containerId); 
					});						

				}

			});

			$(containerId+" .cancel").click(function(){
				$(containerId).slideUp(400, function(){	    			 
					$(containerId).empty();
					$(nTr).css("color","");
					pac_table.fnClose( nTr );
					pac_removeValidationForm(containerId); 
				});

			});

		}
		
		pac_addFormValidation(containerId);

	});

}

function pac_fillOutForm(containerId, packageId, nTr) {
	createLoadingDivAfter(containerId, "Loading package data");
	$.ajax({
		type : 'POST',
		url : packagesphpURL,
		dataType : 'json',
		data : {
			action : 'getPackage',
			packageid : packageId
		},
		success : function(data) {
			removeLoadingDivAfter(containerId);


			$(containerId + " .namePackage").val(data.name);
			$(containerId + " .descriptionPackage").val(data.description);
			$(containerId + " .notbillablePackage").attr("checked",
					!(data.billable == "1"));
			$(containerId + " .activePackage").attr("checked",
					data.active == "1");
			pac_showBasicFormFields(containerId);
			if(!data.empty){
				$(containerId + " .notbillablePackage").attr("disabled","disabled");
			}
			$(containerId).slideDown(400);
		},
		error : function(XMLHttpRequest, textStatus, errorThrown) {
			removeLoadingDivAfter(containerId);
			displayError(containerId, errorThrown, function() {
				$(nTr).css("color", "");
				pac_table.fnClose(nTr);
			});
		}
	});
}


function pac_addPackage(containerId, name, description, notbillable, active)
{
	$(containerId).empty();
	createLoadingDivAfter(containerId,"Creating package");
    $.ajax({
        type: 'POST',
        url: packagesphpURL,
        dataType: 'json',
        data: {
            action: 'addPackage',
            name:name,
            description:description,
            billable:!notbillable,
            active:active

        },
        success: function(data){
        	removeLoadingDivAfter(containerId);
            if(data.success){
            	var pkg = data.package;
            	displayMessage(containerId,"Package ["+name+"] successfully added");

            	pac_table.fnAddData( [
				                      pkg.id,
				                      pkg.name,
				                      pkg.description,
				                      pkg.billable,
				                      pkg.active,
				                      pkg.price,
				                      pkg.creationdate]
            						);


            }else{
            	displayError(containerId,data.message);
            }

        },
        error: function(XMLHttpRequest, textStatus, errorThrown){
        	removeLoadingDivAfter(containerId);
        	displayError(containerId, errorThrown);
        }
    });

}


function pac_modifyPackage(containerId, nTr,id, name, description, notbillable, active)
{
	$(containerId).empty();
	createLoadingDivAfter(containerId,"Modifying package");
    $.ajax({
        type: 'POST',
        url: packagesphpURL,
        dataType: 'json',
        data: {
            action: 'editPackage',
            packagename:name,
            packagedesc:description,
            billable:!notbillable,
            type:'PACKAGE',
            id:id,
            active:active

        },
        success: function(data){
        	removeLoadingDivAfter(containerId);
            if(data.success){
            	var pkg = data.package;
            	displayMessage(containerId,"Package "+name+" modified successfully!", function(){
    				$(nTr).css("color","");
                    pac_table.fnClose( nTr );
                });

            	pac_table.fnUpdate( [
				                      pkg.id,
				                      pkg.name,
				                      pkg.description,
				                      pkg.billable,
				                      pkg.active,
				                      pkg.price,
				                      pkg.creationdate],
				                      nTr,
				                      false,
				                      false);

            	
            }else{
	        	displayError(containerId,data.message, function(){
	        		$(nTr).css("color","");
	        		pac_table.fnClose( nTr );
	            });
            }

        },
        error: function(XMLHttpRequest, textStatus, errorThrown){
        	removeLoadingDivAfter(containerId);
        	displayError(containerId,errorThrown, function(){
            	$(nTr).css("color","");
            	pac_table.fnClose( nTr );
            });
        }
    });
}


function pac_deletePackage(divId, nTr)
{
	$(divId).empty();
	createLoadingDivAfter(divId,"Deleting Package");
	
	var aData = pac_table.fnGetData( nTr );
	var name = aData[1];
	var id = aData[0];
	
    $.ajax({
        type: 'POST',
        url: packagesphpURL,
        dataType: 'json',
        data: {
            action: 'deletePackage',
            id:id
        },
        success: function(data){
        	removeLoadingDivAfter(divId);
        	
            if(data.success){
                displayMessage(divId,"Package successfully deleted", function(){
                	pac_table.fnClose( nTr );
            		pac_table.fnDeleteRow(nTr);
                });
            }else{
            	displayError(divId,data.message, function(){
            		$(nTr).css("color","");
                	pac_table.fnClose( nTr );
                });
            }
        },
        error: function(XMLHttpRequest, textStatus, errorThrown){
        	removeLoadingDivAfter(divId);
        	displayError(divId,errorThrown, function(){
        		$(nTr).css("color","");
            	pac_table.fnClose( nTr );
            });
        }
    });

}

function pac_addFormValidation(containerId){
	
	var formFields = new Array();
	var name = containerId.substring(1)+"_namePackage";
	$(containerId+" .namePackage").attr("id",name);
	var nameValidator = new LiveValidation(name,{ wait: 500 });
	nameValidator.add( Validate.Presence );
	nameValidator.add( Validate.Length, { maximum: 45 });
	formFields.push(nameValidator);

	
	var liveValidationForm = { container_id: containerId, form_fields: formFields };
	pac_open_validation_forms.push(liveValidationForm);
}

function pac_removeValidationForm(containerId){

	var tempArray = new Array();
	for(var i=0; i < pac_open_validation_forms.length ; i++){
		if(pac_open_validation_forms[i].container_id != containerId){
			tempArray.push(pac_open_validation_forms[i]);
		}
	}

	pac_open_validation_forms = tempArray;
}

function pac_isValidForm(containerId){
	
	for(var i=0; i < pac_open_validation_forms.length ; i++){
		if(pac_open_validation_forms[i].container_id == containerId){
			return LiveValidation.massValidate(pac_open_validation_forms[i].form_fields);
		}
	}
	return false;
}


function pac_hideAllFormFields(containerId){
	$(containerId+" form fieldset div div").each(function(index) {
	    $(this).find('input[type="text"]').attr("disabled","disabled");
	    $(this).hide();
	  });
}

function pac_showBasicFormFields(containerId){

	
	$(containerId+" form fieldset div div").each(function(index) {    	
	    	$(this).find('input[type="text"]').attr("disabled","");
	    	$(this).show();

	  });

}

function pac_showForm(containerId){


    $(containerId+" .namePackageSpan").show();
    $(containerId+" .namePackageSpan input").removeAttr("disabled");
    $(containerId+" .notbillablePackageSpan").show();
    $(containerId+" .activePackageSpan").show();
    $(containerId+" .descriptionPackageSpan").show();

    $(containerId).show();
}




/**PACKAGE ITEMS SECTION**/

function pacitem_reload(packageId, packageName){
	
	//hide package table
	$("#packagesWrapper").hide(); 
	

	//construct the container for package items 
	var div = '';
	div+='		<p class="tableTop">';
	div+='			<button id="back-to-packages">Back to Package Manager</button>';
	div+='			<span class="page-title">Package '+packageName+'</span>';
	div+='			<input type="hidden" class="package-id" value="'+packageId+'"/>';
	div+='			<button id="add-package_item'+packageId+'" >Add Item</button>';
	div+='		</p>';
	div+='		<div class="messageContainer" style="display:none"></div>';
	div+='		<div id="addItemToPackageForm'+packageId+'" class="addForm" style="display:none"></div>';
	div+='		<div id="packageItemsContainer'+packageId+'"></div>';

	$('#packageItemsWrapper').hide();
	$('#packageItemsWrapper').empty();
	$('#packageItemsWrapper').html(div);

	//Add listener to back button
	$("#packageItemsWrapper #back-to-packages").button();
	$("#packageItemsWrapper #back-to-packages").die();
	$("#packageItemsWrapper #back-to-packages").live("click",function() {
		pac_reload();
		$("#packageItemsWrapper").hide(); 
		$("#packagesWrapper").show(); 
	});
	
	//Add Listener to add item to package button	
	$("#packageItemsWrapper #add-package_item"+packageId).button();	
	$("#packageItemsWrapper #add-package_item"+packageId).die();	
	$("#packageItemsWrapper #add-package_item"+packageId).live("click", function() {	
		$("#packageItemsWrapper #add-package_item"+packageId).button("disable");	
		var containerId = "#addItemToPackageForm"+packageId;
		pacitem_openForm(containerId, true, packageId, packageName);
	});
	
	$("#packageItemsWrapper").show(); 

	createLoadingDivAfter("#packageItemsWrapper", "Loading package items");

	    $.ajax({
	        type: 'POST',
	        url: packagesphpURL,
	        dataType: 'json',
	        data: {
	            action: 'getPackageItems',
	            packageId:packageId

	        },
	        success: function(data){
	       
	        	var package = data.package;
	        	var items = data.items;
	        	removeLoadingDivAfter("#packageItemsWrapper");
	        	
	        	$('#packageItemsContainer'+packageId).empty();

	        	$('#packageItemsContainer'+packageId).html( '<table cellpadding="0" cellspacing="0" border="0" class="display" id="packageItemsTable'+packageId+'"></table>' );
	            
	        	
	        	if(package.billable=="1"){
	        		pacitem_table = $("#packageItemsTable"+packageId).dataTable({
		        		"bAutoWidth": false,
		                "aaData": items,
		                "aoColumns": [
		                {  "bVisible": false },
		                {  "bVisible": false },
		                {  "sTitle": "Name" },
		                {  "bVisible": false },
		                {  "sClass": "center", "sTitle": "Quantity" }, 
		                { "sTitle": "Unit price","fnRender": function ( oObj ) {return '$ '+oObj.aData[5];}},
		        		{ "sTitle": "New price", "fnRender": function ( oObj ) {return '$ '+oObj.aData[6];}},
		        		{ "sTitle": "Subtotal", "fnRender": function ( oObj ) {return '$ '+oObj.aData[7];}},
		                ],
		        		"bJQueryUI": true,
		        		"bAutoWidth": false,
		        		"sPaginationType": "full_numbers"
		        	});
		        	
	        	}else{
	        		pacitem_table = $("#packageItemsTable"+packageId).dataTable({
		        		"bAutoWidth": false,
		                "aaData": items,
		                "aaSorting": [[ 2, "desc" ]],
		                "aoColumns": [
		                {  "bVisible": false },
		                {  "bVisible": false },
		                { "sTitle": "Name" },
		                {  "bVisible": false },
		                { "sClass": "center", "sTitle": "Quantity" }, 
		                {  "bVisible": false },
		        		{  "bVisible": false },
		        		{  "bVisible": false },
		                ],
		        		"bJQueryUI": true,
		        		"bAutoWidth": false,
		        		"sPaginationType": "full_numbers"
		        	});
		        	
	        	}
	        	
	        	$("#packageItemsTable"+packageId).removeAttr("style");
	        	$("#packageItemsTable"+packageId+" tbody tr td").die();
	        	$("#packageItemsTable"+packageId+" tbody tr td").live('click', pacitem_rowClickHandler );	
    
	        },
	        error: function(XMLHttpRequest, textStatus, errorThrown){
	        	removeLoadingDivAfter('#packageItemsContainer'+packageId);
	        	displayError('#packageItemsContainer'+packageId,errorThrown); 

	        }
	    });
}





function pacitem_rowClickHandler()
{

	var nTr = this.parentNode;
	var aData = null;
	try{
		aData = pacitem_table.fnGetData( nTr );
	}catch(err){
		return;
	}
	var open=false;

	try{
		if($(nTr).next().children().first().hasClass("ui-state-highlight"))
			open=true;
	}catch(err){}

	var divId = "#packageItemDetails"+aData[0];

	if (open){
		/* This row is already open - close it */
		pacitem_table.fnClose( nTr );
		$(nTr).css("color","");
		pacitem_removeValidationForm(divId);
	}else{
		/* Open this row */
		pacitem_openDetailsRow(nTr);
	}
}

function pacitem_openDetailsRow(nTr)
{
	var packageId = $("#packageItemsWrapper .package-id").val();

	pacitem_table.fnOpen( nTr, pacitem_formatDetailsRow(pacitem_table, nTr ), 'ui-state-highlight' );
	var aData = pacitem_table.fnGetData( nTr );
	
	$("#modifyPackageItem"+aData[0]).button();
	$("#deletePackageItem"+aData[0]).button();

	var divId = "#packageItemDetails"+aData[0];

	$("#modifyPackageItem"+aData[0]).click(function(){
		$(nTr).css("color","#c5dbec");
		$(divId).empty();
		pacitem_openForm(divId, false, nTr, aData[0]);

	});
	$("#deletePackageItem"+aData[0]).click(function(){
		$(divId).empty();
		pacitem_delete(divId, nTr, aData[0], packageId);
	});

}



//Format Package Description and buttons in collapsible row
function pacitem_formatDetailsRow ( oTable, nTr )
{
	var aData = oTable.fnGetData( nTr );
	var id = aData[0];
	
	var aData = oTable.fnGetData( nTr );
	var sOut = '';
	sOut += '<div id="packageItemDetails'+id+'">';
	sOut += '	<div class="buttonColumnDetails">';
	sOut += '		<button id="modifyPackageItem'+aData[0]+'">Modify</button>';
	sOut += '		<button id="deletePackageItem'+aData[0]+'">Delete</button>';
	sOut += '	</div>';
	sOut += '</div>';
	
	return sOut;
}

function showPackageItemsForOrderItems(packageId, packageName, data, orderid, ordernumber)
{

	//Load package items table in its container
	loadOrderPackageItemsTable(packageId, packageName, data, orderid, ordernumber);
	
	$("#orderItemsPackageWrapper #packageItemsContainer #packageItemsTable").removeAttr("style");
    
	//Show package items table
	$('#orderItemsPackageWrapper').show();
	
}



//Show package table

function loadOrderPackageItemsTable(packageId,packageName, data, orderid, ordernumber){
	
	//Set Back button
	$("#orderItemsPackageWrapper .tableTop").empty();
	$("#orderItemsPackageWrapper .tableTop").append( '<span class="page-title">Order '+ordernumber+'- Package '+packageName+'</span>');
	$("#orderItemsPackageWrapper .tableTop").append( '<a href="#" class="back-to-orders">Back to Order Manager</a>');
	$("#orderItemsPackageWrapper .tableTop").append( '<a href="#" class="back-to-orders-items">Back to Order Items</a>');
	$(".back-to-orders").button();
	$(".back-to-orders").click(function(){
		 if($("#role").val() == "admin")
		 {
			 reloadOrdersAll();
		 }else{
			 reloadOrders();
		 }
	
	});
	$(".back-to-orders-items").button();
	$(".back-to-orders-items").click(function(){
		showOrderDetails(orderid, ordernumber)
	});
	
	

	$('#orderItemsPackageWrapper #packageItemsContainer').empty();
	$('#orderItemsPackageWrapper #packageItemsContainer').html( '<table cellpadding="0" cellspacing="0" border="0" class="display" id="packageItemsTable"></table>' );
    
	var packageItemsTable = $("#orderItemsPackageWrapper #packageItemsContainer #packageItemsTable").dataTable({
		"bAutoWidth": false,
        "aaData": data,
        "aaSorting": [[ 2, "desc" ]],
        "aoColumns": [
        {  "bVisible": false },
        { "sTitle": "Name" },
        {  "bVisible": false },
        { "sClass": "center","sTitle": "Quantity" }, 
        {
          	 "sClass": "center",
          	  "sTitle": "Unit price",
          	  "fnRender": function ( oObj ) {
   					return '$ '+oObj.aData[4];
   				},
   			 	"aTargets": [ 4 ]
   		},
   		 {
          	 "sClass": "center",
          	  "sTitle": "New price",
          	  "fnRender": function ( oObj ) {
   					return '$ '+oObj.aData[5];
   				},
   			 	"aTargets": [ 5 ]
   		},
   		 {
          	 "sClass": "center",
          	  "sTitle": "Subtotal",
          	  "fnRender": function ( oObj ) {
   					return '$ '+oObj.aData[6];
   				},
   			 	"aTargets": [ 6 ]
   		},
        ],
		"bJQueryUI": true,
		"sPaginationType": "full_numbers"
	});
	
}




function pacitem_openForm(containerId, add, nTr, id)
{
	
	var packageId = $("#packageItemsWrapper .package-id").val();

	
	$(containerId).empty();
	$(containerId).hide();

	$(containerId).load("forms/addItemsToPkg.html", function(){	
		
		
		createLoadingDivAfter(containerId,"Loading Elegible Items for this package");

		pacitem_hideAllFormFields(containerId);

		$.ajax({
			type: 'POST',
			url: packagesphpURL,
			dataType: 'json',
			data: {
				action:"getElegibleItems",
				packageid:packageId
			},
			success: function(data){

				removeLoadingDivAfter(containerId);

				var billable = data.billable;
				
				if(data.content){
					$(containerId+' .idItemPackageSpan select').html(data.content);
					if(billable){
						jQuery.each(data.prices, function() {	
							  if(this.id==$(containerId +" .idItemPackage").val()){
								  $(containerId +" .priceItemPackage").val(this.price);
							  }
							});												
					}
				}else{
					$(containerId+' .idItemPackageSpan select').html("<option value=''>No items elegible</option>");
				}

				
				$(containerId+' .idItemPackageSpan select').change(function(){
					if(billable){
						jQuery.each(data.prices, function() {	
							  if(this.id==$(containerId +" .idItemPackage").val()){
								  $(containerId +" .priceItemPackage").val(this.price);
							  }
							});												
					}           
                });

				
				if(add){

					$(containerId).addClass("ui-state-highlight");

					pacitem_showForm(containerId, billable);	    

					$(containerId+" .submit").button();
					$(containerId+" .cancel").button();


					$(containerId + " .submit").click(function() {
						if (pacitem_isValidForm(containerId)) {
							var item = $(containerId +" .idItemPackage").val();
							
							var price =  $(containerId +" .priceItemPackage").val();
							var quantity =  $(containerId +" .quantityItemPackage").val();
							if(!price) price=0;
							

							$.ajax({
								type: 'POST',
								url: packagesphpURL,
								dataType: 'json',
								data: {
									action:"isPkgBillable",
									packageid:packageId
								},
								success: function(data){
									if(data){
										$(containerId +" .priceItemPackageSpan").show();
										$(containerId +" .priceItemPackage").removeAttr("disabled");

									}

									$(containerId).slideUp(400, function(){	 
										$("#packageItemsWrapper #add-package_item"+packageId).button("enable");
										pacitem_add(containerId,packageId, item,quantity, price);
										$(containerId).empty();
										pacitem_removeValidationForm(containerId); 
									});
								},
								error: function(XMLHttpRequest, textStatus, errorThrown){

								}
							});
						}
					});

					$(containerId+" .cancel").click(function(){
						$(containerId).slideUp(400, function(){	    			 
							$(containerId).empty();
							$("#packageItemsWrapper #add-package_item"+packageId).button("enable");
							pacitem_removeValidationForm(containerId); 
						});

					});						


				}else{
					var aData = pacitem_table.fnGetData( nTr );
					
					
					pacitem_fillOutForm(containerId, nTr, id, billable);

					$(containerId+" .submit").button();
					$(containerId+" .cancel").button();


					$(containerId + " .submit").click(function() {

						if (pacitem_isValidForm(containerId)) {
							var item = aData[1];
							var price =  $(containerId +" .priceItemPackage").val();
							var quantity =  $(containerId +" .quantityItemPackage").val();
							if(!price) price=0;

							$(containerId).slideUp(400, function(){	            				
								pacitem_modify(containerId, nTr,id, packageId, item,quantity, price);							
								$(containerId).empty();
								pacitem_removeValidationForm(containerId); 
						
							});

						}
					});

					$(containerId+" .cancel").click(function(){
						$(containerId).slideUp(400, function(){	    			 
							$(containerId).empty();
							$(nTr).css("color","");
							pacitem_table.fnClose( nTr );
							pacitem_removeValidationForm(containerId); 
						});

					});	
				}

				pacitem_addFormValidation(containerId);

			},
			error: function(XMLHttpRequest, textStatus, errorThrown){
				removeLoadingDivAfter(containerId);
				displayError(containerId,errorThrown);
			}
		});
	});
}
	

function pacitem_fillOutForm(containerId, nTr, id, billable) {
	
	var packageId = $("#packageItemsWrapper .package-id").val();
	
	createLoadingDivAfter(containerId, "Loading item data");
	
	$.ajax({
		type : 'POST',
		url : packagesphpURL,
		dataType : 'json',
		data : {
			action : 'getPkgItem',
			id : id
		},
		success : function(data) {			
			removeLoadingDivAfter(containerId);			
			var item = data.item;			
			$(containerId+' .idItemPackageSpan select').html("<option value="+item.id+">"+item.name+"-$"+item.price+"</option>");
			
			$(containerId+" .idItemPackage").val(item.id);
		    $(containerId+" .quantityItemPackage").val(item.quantity);
		    $(containerId+" .priceItemPackage").val(item.newprice);

		    $(containerId+" .idItemPackageSpan").hide();
		    $(containerId+" .quantityItemPackageSpan").show();
		    $(containerId+" .quantityItemPackage").removeAttr("disabled")
		    
			if(billable){
				$(containerId +" .priceItemPackageSpan").show();
				$(containerId +" .priceItemPackage").removeAttr("disabled");
			}else{
				$(containerId+" .priceItemPackageSpan").hide();
			    $(containerId+" .priceItemPackage").attr("disabled","disabled");								
			}

			$(containerId).slideDown(400);
		},
		error : function(XMLHttpRequest, textStatus, errorThrown) {
			removeLoadingDivAfter(containerId);
			displayError(containerId, errorThrown, function() {
				$(nTr).css("color", "");
				pacitem_table.fnClose(nTr);
			});
		}
	});
}

function pacitem_add(containerId, packageid, itemid,  qty, price)
{
	$(containerId).empty();
	createLoadingDivAfter(containerId,"	Adding Item");	

    $.ajax({
        type: 'POST',
        url: packagesphpURL,
        dataType: 'json',
        data:{
        	action:'addPkgItem',
        	itemid:itemid,
        	itemqty:qty,
        	packageid:packageid,
        	price:price
        },
        success: function(data){
        	
        	var item = data.item;
        	removeLoadingDivAfter(containerId);
        	
            if(data.success == false){
            	displayError(containerId,data.message);
	        }else{
	            displayMessage(containerId,"Item added successfully!");
	
	            
	          	pacitem_table.fnAddData( [
				                      item.id,
				                      itemid,
				                      item.name,
				                      item.description,
				                      item.quantity,
				                      item.price,
				                      item.newprice,
				                      item.subtotal
				                      ]);

	        }
	    },
        error: function(XMLHttpRequest, textStatus, errorThrown){
        	removeLoadingDivAfter(containerId);
        	displayError(containerId,errorThrown);
        }
    });



}

function pacitem_modify(containerId, nTr, id, packageId, itemid,  qty, price)
{
	$(containerId).empty();
	createLoadingDivAfter(containerId,"	Modifying Item");	
    $.ajax({
        type: 'POST',
        url: packagesphpURL,
        dataType: 'json',
        data:{
        	action:'modifyPkgItem',
        	id:id,
        	itemid:itemid,
        	itemqty:qty,
        	packageid:packageId,
        	price:price
        },
        success: function(data){
        	
        	removeLoadingDivAfter(containerId);
        	
            if(data.success){
            	var item = data.item;
            	
            	displayMessage(containerId,"Item "+name+" modified successfully!", function(){
    				$(nTr).css("color","");
                    pacitem_table.fnClose( nTr );
                });
            	
            	pacitem_table.fnUpdate( [
            	                      item.id,
            	                      item.itemid,
   				                      item.name,
   				                      item.description,
   				                      item.quantity,
   				                      item.price,
   				                      item.newprice,
   				                      item.subtotal],
				                      nTr,
				                      false,
				                      false);

           	
            
            }else{
            	displayError(containerId,data.message, function(){
	        		$(nTr).css("color","");
	            	pacitem_table.fnClose( nTr );
	            });
            }
        },
        error: function(XMLHttpRequest, textStatus, errorThrown){
        	removeLoadingDivAfter(containerId);
        	displayError(containerId,errorThrown, function(){
        		$(nTr).css("color","");
            	pacitem_table.fnClose( nTr );
            });
        }
    });



}

function pacitem_delete(divId, nTr, id, packageId)
{
	$(divId).empty();
	createLoadingDivAfter(divId,"Deleting Item");

    $.ajax({
        type: 'POST',
        url: packagesphpURL,
        dataType: 'json',
        data:{
        	action:'deletePkgItem',
        	id:id,
        	packageid:packageId
        },
        success: function(data){
        	removeLoadingDivAfter(divId);
        	
            if(data.success){
            	 displayMessage(divId,"Item successfully deleted", function(){
                 	pacitem_table.fnClose( nTr );
             		pacitem_table.fnDeleteRow(nTr);
                 });
            
            }else{
            	displayError(divId,data.message, function(){
            		$(nTr).css("color","");
                	pacitem_table.fnClose( nTr );
                });
            	
            }
        },
        error: function(XMLHttpRequest, textStatus, errorThrown){
        	removeLoadingDivAfter(divId);
        	displayError(divId,errorThrown, function(){
        		$(nTr).css("color","");
            	pacitem_table.fnClose( nTr );
            });
        }
    });



}


function pacitem_addFormValidation(containerId){
	
	var formFields = new Array();
	
	var item = containerId.substring(1)+"_idItemPackage";
	$(containerId+" .idItemPackage").attr("id",item);
	var itemValidator = new LiveValidation( item , {wait: 500});
	itemValidator.add( Validate.Presence );
	formFields.push(itemValidator);
	
	var price = containerId.substring(1)+"_priceItemPackage";
	$(containerId+" .priceItemPackage").attr("id",price);
	var priceValidator = new LiveValidation( price , {wait: 500});
	priceValidator.add( Validate.Presence );
	priceValidator.add( Validate.Numericality, { onlyInteger: false, minimum: 0.01 } );
	formFields.push(priceValidator);

	var quantity = containerId.substring(1)+"_quantityItemPackage";
	$(containerId+" .quantityItemPackage").attr("id",quantity);
	var qtyValidator = new LiveValidation( quantity , {wait: 500});
	qtyValidator.add( Validate.Presence );
	qtyValidator.add( Validate.Numericality, { onlyInteger: true, minimum: 1 } );
	formFields.push(qtyValidator);
	
	var liveValidationForm = { container_id: containerId, form_fields: formFields };
	pacitem_open_validation_forms.push(liveValidationForm);
}

function pacitem_removeValidationForm(containerId){

	var tempArray = new Array();
	for(var i=0; i < pacitem_open_validation_forms.length ; i++){
		if(pacitem_open_validation_forms[i].container_id != containerId){
			tempArray.push(pacitem_open_validation_forms[i]);
		}
	}

	pacitem_open_validation_forms = tempArray;
}

function pacitem_isValidForm(containerId){
	
	for(var i=0; i < pacitem_open_validation_forms.length ; i++){
		if(pacitem_open_validation_forms[i].container_id == containerId){
			return LiveValidation.massValidate(pacitem_open_validation_forms[i].form_fields);
		}
	}
	return false;
}


function pacitem_hideAllFormFields(containerId){
	$(containerId+" form fieldset div div").each(function(index) {
	    $(this).find('input[type="text"]').attr("disabled","disabled");
	    $(this).hide();
	  });
}

function pacitem_showBasicFormFields(containerId){
	
	$(containerId+" form fieldset div div").each(function(index) {    	
		if(index!=2){
	    	$(this).find('input[type="text"]').attr("disabled","");
	    	$(this).show();
		}
	  });

}

function pacitem_showForm(containerId, billable){
	$(containerId+" .idItemPackageSpan").show();
    $(containerId+" .quantityItemPackageSpan").show();
    $(containerId+" .quantityItemPackageSpan input").removeAttr("disabled");
    

	if(billable)
	{
		$(containerId+" .priceItemPackageSpan").show();
		$(containerId+" .priceItemPackage").removeAttr("disabled");
	}else{
		$(containerId+" .priceItemPackageSpan").hide();
		$(containerId+" .priceItemPackage").attr("disabled","disabled");
	}
	
    $(containerId).show();
}





function isPackageBillable(packageid)
{
    $.ajax({
        type: 'POST',
        url: packagesphpURL,
        dataType: 'json',
        data: {
        	action:"isPkgBillable",
        	packageid:packageid
        },
        success: function(data){
        	
        	//alert(data);
            return (data=="true");
        },
        error: function(XMLHttpRequest, textStatus, errorThrown){
        	
        }
    });

}



