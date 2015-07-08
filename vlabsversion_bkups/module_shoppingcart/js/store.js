/*
 *store.js
 *
 *@author Vanessa ramirez
 *
 *
 *
 */


var storephpURL = '/modules/module_shoppingcart/server/store.php';

var sto_table;
var sto_open_validation_forms = new Array();

function openStoreTab()
{
    $('#tabs').tabs("select","#storeTab");

}

function openStoreManagerTab()
{
    $('#tabs').tabs("select","#storeManagerTab");

}


function sto_reloadStore()
{
	createLoadingDivAfter("#storeContainer","Loading store inventory");


    $.ajax({
        type: 'POST',
        url: storephpURL,
        dataType: 'json',
        data: {
            action: 'reload',
            user:$("#userid").val()
        },
        success: function(data){
        	removeLoadingDivAfter("#storeContainer");
        	$('#storeContainer').html( '<table  cellpadding="0" cellspacing="0" border="0" class="display" id="storeTable"></table>' );
        	
        	sto_table = $("#storeTable").dataTable({
        		"bPaginate": true,
        		"bLengthChange": true,
        		"bFilter": true,
        		"bSort": true,
        		"bInfo": false,
        		"bAutoWidth": false, 
        		"aaData": data,
        		"aoColumnDefs": [ 
	     			{ "bVisible": false,  "aTargets": [ 0 ] },
	     			{ "sClass": "center", "aTargets": [ 1 ] },
	     			{ "sClass": "left", "aTargets": [ 2 ] },             
	     			{
	     				"sClass": "center",
	     				"fnRender": function ( oObj ) {
	     					return '<div id="addToCart'+oObj.aData[3]+'"><a class="add-to-cart" href="#" onclick="shc_addToCart('+oObj.aData[3]+',\''+oObj.aData[0]+'\' )">Add to Cart</a></div>';
	     				},
	     				"aTargets": [ 3 ]
	     			}
	     			

	     		],
        		"bJQueryUI": true,
        		"bAutoWidth": false,
        		"sPaginationType": "full_numbers"
	        });
	        	$(".add-to-cart").button();
	        	$("#storeTable thead").hide();
	        	
	    	if(data)
	    	{
	    		$('#storeContainer').append('<a href="#" id="view-cart" >View Cart </a>' );
	        	//View Cart Button
	        	$("#view-cart").button();
	        	$("#view-cart").click(function(){
	        		openShoppingCartTab();
	        	});	
	    		
	    	}

        
        },
        error: function(XMLHttpRequest, textStatus, errorThrown){
        	removeLoadingDivAfter("#storeContainer");
        	displayError("#storeContainer",errorThrown); 
        }
    });

}

//Store Manager

function sto_reloadStoreManager()
{	



	$("#storeManagerContainer").html("");
	
	createLoadingDivAfter("#storeManagerContainer","Loading Store Inventory");
	//alert("In sto_reloadStoreManager.");	
    $.ajax({
        type: 'POST',
        url: storephpURL,
        dataType: 'json',
        data: {
            action: 'getInventory'
        },
        success: function(data){
         //alert("In sto_reloadStoreManager AJAX call.");
        	removeLoadingDivAfter("#storeManagerContainer");
        	
        	$('#storeManagerContainer').html( '<table cellpadding="0" cellspacing="0" border="0" class="display" id="storeManagerTable"></table>' );
        	
        	sto_table = $("#storeManagerTable").dataTable({
                "aaData": data,
                "aaSorting": [[7,'desc']],
                "aoColumns": [
                {  "bVisible": false },
                { "sTitle": "Name" },
                {  "bVisible": false },
                { "sTitle": "Type" },
                {"sClass": "center","sTitle": "Price" , "fnRender": function (oObj) {  return oObj.aData[4]=='Not Billable'?oObj.aData[4]:'$'+oObj.aData[4]; } },
                { "sTitle": "Reference" },
                { "sTitle": "Active" , "fnRender": function (oObj) {  return oObj.aData[6]=="1" ? "Yes" : "No"; } },
                {  "bVisible": false }
                ],
        		"bJQueryUI": true,
        		"bAutoWidth": false,
        		"sPaginationType": "full_numbers"
        	});
        	
        	
        	
        	$("#storeManagerTable").removeAttr("style");

        	$('#storeManagerTable tbody tr td').die();
        	
        	$('#storeManagerTable tbody tr td').live('click', sto_rowClickHandler );


        },
        error: function(XMLHttpRequest, textStatus, errorThrown){
        	removeLoadingDivAfter("#storeManagerContainer");
        	displayError("#storeManagerContainer",errorThrown); 
        }
    });

}

function sto_rowClickHandler(){

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
		sto_table.fnClose( nTr );
		$(nTr).css("color","");
	}else{
		sto_openDetailsRow(nTr);
	}
}

function sto_openDetailsRow(nTr){

	sto_table.fnOpen( nTr, sto_formatStoreManagerDetails(sto_table, nTr), "ui-state-highlight" );
	
	var aData = sto_table.fnGetData( nTr );
		
	$("#modifyItem"+aData[0]).button();
	$("#deleteItem"+aData[0]).button();
	
	var divId = "#itemDetails"+aData[0];
	
	$("#modifyItem"+aData[0]).click(function(){
		$(nTr).css("color","#c5dbec");
		$(divId).empty();
		sto_openForm(divId, false, nTr, aData[0]);
			        				
	});
	
	$("#deleteItem"+aData[0]).click(function(){
		sto_deleteItem(divId, nTr);
	});
	
}

function sto_formatStoreManagerDetails ( oTable, nTr )
{
	var aData = oTable.fnGetData( nTr );
	var id = aData[0];
	var sOut = '';
	sOut += '<div id="itemDetails'+id+'">';
	sOut += '	<div class="buttonColumnDetails">';
	sOut += '		<button id="modifyItem'+id+'">Modify</button>';
	sOut += '		<button id="deleteItem'+id+'">Delete</button>';
	sOut += '	</div>';
	sOut += '</div>';
	return sOut;
}


function sto_openForm(containerId, add, nTr, itemId)
{
	$(containerId).empty();
    $(containerId).hide();

	$(containerId).load("forms/createItem.html", function(){
		sto_hideAllFormFields(containerId);
		sto_loadForm(containerId, add, nTr, itemId);
	});

}

function sto_loadForm(containerId, add, nTr, itemId)
{	
	//window.alert("On sto_loadForm function");
	createLoadingDivAfter(containerId,"Loading references");
	
	
    $.ajax({
        type: 'POST',
        url: storephpURL,
        dataType: 'json',
        data: {
            action: 'getReferences'

        },
        success: function(data){
        	
        	removeLoadingDivAfter(containerId);
                   	
        	//Load references
        	var references = data.references;
            var content =  '';
            for(var i in references)
            {
                if(references[i]!=null)
                {
                	content+="<option value='"+references[i].id+"'>"+references[i].name+"-"+references[i].policy.name+"</option>";                  			
                }

            }
            $(containerId+" .referenceItem").html(content);

            //Listener on billable 
            $(containerId+" .notbillableItem").change(function(){
	    	    if($(containerId+" .notbillableItem").is(':checked'))
	    	    {
	    	    	$(containerId+" .priceItemSpan").hide();
	    	    	$(containerId+" .priceItemSpan input").attr("disabled","disabled");
	    	    }else{
	    	    	$(containerId+" .priceItemSpan").show();
	    	    	$(containerId+" .priceItemSpan input").attr("disabled","");
	    	    }
            });
    	    
            if(add){

            	$(containerId).addClass("ui-state-highlight");
            	
            	sto_showForm(containerId);	    
            	
            	$(containerId+" .submit").button();
            	$(containerId+" .cancel").button();

            	$(containerId + " .activeItem").attr("checked","checked");
            	
            	$(containerId + " .submit").click(function() {
            		
            		if(sto_isValidForm(containerId)){
            			var name = $(containerId + " .nameItem").val();
            			var description = $(containerId + " .descriptionItem").val();
            			var price = $(containerId + " .priceItem").val();
            			var reference = $(containerId + " .referenceItem").val();
            			var referenceName = $(containerId+" .referenceItem option[value="+$(containerId+" .referenceItem").val()+"]").text();
            			var active = $(containerId + " .activeItem").is(':checked');
            			var type = $(containerId + " .typeItem").val();
            			var notbillable = $(containerId + " .notbillableItem").is(':checked');

            			$(containerId).slideUp(400, function(){	    			 
            				sto_addItem(containerId,name, description, price, reference, referenceName, active, type, notbillable);
            				$(containerId).empty();
            				$("#add-item").button("enable");
            				sto_removeValidationForm(containerId); 
            			});
            		}

            	});

            	$(containerId+" .cancel").click(function(){
            		$(containerId).slideUp(400, function(){	    			 
            			$(containerId).empty();
            			$("#add-item").button("enable");
            			sto_removeValidationForm(containerId); 
            		});

            	});

            }else{
            	
            	sto_fillOutForm(containerId,itemId, nTr);  	
            	
            	$(containerId+" .submit").button();
            	$(containerId+" .cancel").button();

            	$(containerId + " .submit").click(function() {

            		if(sto_isValidForm(containerId)){
            			var name = $(containerId + " .nameItem").val();
            			var description = $(containerId + " .descriptionItem").val();
            			var price = $(containerId + " .priceItem").val();
            			var reference = $(containerId + " .referenceItem").val();
            			var referenceName = $(containerId+" .referenceItem option[value="+$(containerId+" .referenceItem").val()+"]").text();
            			var active = $(containerId + " .activeItem").is(':checked');
            			var type = $(containerId + " .typeItem").val();
            			var notbillable = $(containerId + " .notbillableItem").is(':checked');
            	           			
            			$(containerId).slideUp(400, function(){	    			 
            				sto_modifyItem(containerId, nTr, itemId,name, description, price, reference, referenceName, type, notbillable,active);
            				$(containerId).empty();
            				sto_removeValidationForm(containerId); 
            			});


            		}

            	});

            	$(containerId+" .cancel").click(function(){
            		$(containerId).slideUp(400, function(){	    			 
            			$(containerId).empty();
                		$(nTr).css("color","");
    	                sto_table.fnClose( nTr );
            			sto_removeValidationForm(containerId); 
            		});

            	});
            }

            sto_addFormValidation(containerId);
        },
        error: function(XMLHttpRequest, textStatus, errorThrown){
        	removeLoadingDivAfter(containerId);
        	displayError(containerId,errorThrown);
        }
    });	
}


function sto_showForm(containerId){

	
    $(containerId+" .referenceItemSpan").show();
    $(containerId+" .nameItemSpan").show();
    $(containerId+" .nameItemSpan input").removeAttr("disabled");
    $(containerId+" .notbillableItemSpan").show();
    $(containerId+" .activeItemSpan").show();
    $(containerId+" .typeItemSpan").show();
    $(containerId+" .descriptionItemSpan").show();
    
    if($(containerId+" .notbillableItem").is(':checked'))
    {
    	$(containerId+" .priceItemSpan").hide();
    	$(containerId+" .priceItemSpan input").attr("disabled","disabled");
    }else{
    	$(containerId+" .priceItemSpan").show();
    	$(containerId+" .priceItemSpan input").removeAttr("disabled");
    }

    $(containerId).show();
}


function sto_fillOutForm(containerId,itemId, nTr){
	
	createLoadingDivAfter(containerId,"Loading item data");
	  $.ajax({
	        type: 'POST',
	        url: storephpURL,
	        dataType: 'json',
	        data: {
	            action: 'getItem',
	            itemid:itemId
	        },
	        success: function(data){
	        	removeLoadingDivAfter(containerId);
	        	var item = data; //jh original = data.item;
          		
  					$(containerId+" .nameItem").val(item.name);
      			$(containerId+" .typeItem").val(item.type);
      			$(containerId+" .notbillableItem").attr("checked", !(item.billable=="1"));
      			$(containerId+" .activeItem").attr("checked", item.active=="1");
          		$(containerId+" .descriptionItem").val(item.description);
          		$(containerId+" .priceItem").val(item.price);
          		$(containerId+" .referenceItem").val(item.referenceid);
          		$(containerId+" .priceItem").val(item.price);

	    	    if($(containerId+" .notbillableItem").is(':checked'))
	    	    {
	    	    	$(containerId+" .priceItemSpan").hide();
	    	    	$(containerId+" .priceItemSpan input").attr("disabled","disabled");
	    	    }else{
	    	    	$(containerId+" .priceItemSpan").show();
	    	    	$(containerId+" .priceItemSpan input").attr("disabled","");
	    	    }

          		sto_showBasicFormFields(containerId);
        	    $(containerId).slideDown(400);
	        },
	        error: function(XMLHttpRequest, textStatus, errorThrown){
	        	removeLoadingDivAfter(containerId);
	        	displayError(containerId,errorThrown, function(){
	        		$(nTr).css("color","");
	            	sto_table.fnClose( nTr );
	            });
	        }
	    });	
	  

}

function isArray(obj) {
    if (obj.constructor.toString().indexOf("Array") == -1)
        return false;
    else
        return true;
}


 
function sto_addItem(containerId, name, description, price, reference, referenceName,  active, type, notbillable)
{
	createLoadingDivAfter(containerId,"Creating Item");

    $.ajax({
        type: 'POST',
        url: storephpURL,
        dataType: 'json',
        data: {
            action: 'addItem',
            itemname:name,
            itemdesc:description,
            itemprice:price,
            referenceid:reference,
            active:active,
            type:type,
            billable:!notbillable

        },
        success: function(data){
        	removeLoadingDivAfter(containerId);
            if(data.success){
            	var item = data.item;
            	displayMessage(containerId,"Item ["+name+"] successfully added");
            	
            	if(notbillable) price = "Not billable";
            	
            	sto_table.fnAddData( [
									item.id,
									item.name,
									item.description,
									item.type,
									item.billable=="1" ? item.price:"Not Billable",
									referenceName,
									item.active,
									item.creationdate],
									true );


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

function sto_modifyItem(containerId, nTr,id, name, description,price, reference, referenceName, type, notbillable, active)
{
	createLoadingDivAfter(containerId,"Modifying Item");

    $.ajax({
        type: 'POST',
        url: storephpURL,
        dataType: 'json',
        data: {
            action: 'modifyItem',
            itemid:id,
            itemname:name,
            itemdesc:description,
            itemprice:price,
            referenceid:reference,
            active:active,
            type:type,
            billable:!notbillable

        },
        success: function(data){
        	removeLoadingDivAfter(containerId);
            if(data.success){
            	var item = data.item;
            	displayMessage(containerId,"Item "+name+" modified successfully!", function(){
    				$(nTr).css("color","");
                    sto_table.fnClose( nTr );
                });

            	sto_table.fnUpdate( [
				                      item.id,
				                      item.name,
				                      item.description,
				                      item.type,
				                      item.billable=="1" ? item.price:"Not Billable",
				                      referenceName,
				                      item.active,
				                      item.creationdate],
				                      nTr,
				                      false,
				                      false);

            	
            }else{
	        	displayError(containerId,data.message, function(){
	        		$(nTr).css("color","");
	            	sto_table.fnClose( nTr );
	            });
            }

        },
        error: function(XMLHttpRequest, textStatus, errorThrown){
        	removeLoadingDivAfter(containerId);
        	displayError(containerId,errorThrown, function(){
            	$(nTr).css("color","");
                sto_table.fnClose( nTr );
            });
        }
    });
}

function sto_deleteItem(divId, nTr)
{
	
	createLoadingDivAfter(divId,"Deleting Item");
	
	var aData = sto_table.fnGetData( nTr );
	var name = aData[1];
	var id = aData[0];
    
    $.ajax({
        type: 'POST',
        url: storephpURL,
        dataType: 'json',
        data: {
            action: 'deleteItem',
            itemid:id

        },
        success: function(data){
        	removeLoadingDivAfter(divId);
        	
            if(data.success){
                displayMessage(divId,"Item successfully deleted", function(){
                	sto_table.fnClose( nTr );
            		sto_table.fnDeleteRow(nTr);
                });
            }else{
            	displayError(divId,data.message, function(){
            		$(nTr).css("color","");
                	sto_table.fnClose( nTr );
                });
            }
        },
        error: function(XMLHttpRequest, textStatus, errorThrown){
        	removeLoadingDivAfter(divId);
        	displayError(divId,errorThrown, function(){
        		$(nTr).css("color","");
            	sto_table.fnClose( nTr );
            });
        }
    });


}

function sto_addFormValidation(containerId){

	var formFields = new Array();

	
	var name = containerId.substring(1)+"_nameItem";
	$(containerId+" .nameItem").attr("id",name);
	var nameValidator = new LiveValidation(name,{ wait: 500 });
	nameValidator.add( Validate.Presence );
	nameValidator.add( Validate.Length, { maximum: 45 });
	formFields.push(nameValidator);

	var price = containerId.substring(1)+"_priceItem";
	$(containerId+" .priceItem").attr("id",price);
	var priceValidator = new LiveValidation( price , {wait: 500});
	priceValidator.add( Validate.Presence );
	priceValidator.add( Validate.Numericality, { onlyInteger: false, minimum: 0.01 } );
	formFields.push(priceValidator);

	var liveValidationForm = { container_id: containerId, form_fields: formFields };
	sto_open_validation_forms.push(liveValidationForm);
}

function sto_removeValidationForm(containerId){

	var tempArray = new Array();
	for(var i=0; i < sto_open_validation_forms.length ; i++){
		if(sto_open_validation_forms[i].container_id != containerId){
			tempArray.push(sto_open_validation_forms[i]);
		}
	}

	sto_open_validation_forms = tempArray;
}

function sto_isValidForm(containerId){
	
	for(var i=0; i < sto_open_validation_forms.length ; i++){
		if(sto_open_validation_forms[i].container_id == containerId){
			return LiveValidation.massValidate(sto_open_validation_forms[i].form_fields);
		}
	}
	return false;
}


function sto_hideAllFormFields(containerId){
	$(containerId+" form fieldset div div").each(function(index) {
	    $(this).find('input[type="text"]').attr("disabled","disabled");
	    $(this).hide();
	  });
}

function sto_showBasicFormFields(containerId){

	
	$(containerId+" form fieldset div div").each(function(index) {
		
		if(index!=5){	    	
	    	$(this).find('input[type="text"]').attr("disabled","");
	    	$(this).show();
	    }
	  });

}


