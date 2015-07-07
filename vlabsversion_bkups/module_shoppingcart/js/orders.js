/*
 * Orders.js
 * @author Vanessa Ramirez
 *
 */

var ord_table;
var orditems_table;
var ordersphpURL = "/modules/module_shoppingcart/server/orders.php";   //jh original"../server/orders.php";



function openOrdersTab(){
	 $('#tabs').tabs("select","#ordersTab");
}

function ord_reload()
{


	$("#orderItemsWrapper").hide();

    $("#ordersWrapper").show();
    
    $('#ordersContainer').empty();
    
	createLoadingDivAfter("#ordersContainer","Loading orders");

	var userid= $("#userid").val();
	var role = $("#role").val();	
	//window.alert("userid = " + userid +  " role is: " + role);
	//var userid = "admin";  //jh changed:$("#userid").val()
	//var role = "admin";  //jh changed:$("#role").val();
	
	var action = 'reloadOrders';
	if(role=="administrator"){
		//echo '<script type="text/javascript">alert("in orders.jh role is admin")</script>';
		action = 'reloadOrdersAll';
		//window.alert("target url is: " + ordersphpURL);
	}

	 //window.alert("BEFORE ajax section role= "+ role);
    $.ajax({
        type: 'POST',
        url: ordersphpURL,
        dataType: 'json',
        data: {
            action: action,
            userid: userid
        },
        success: function(data){
         //window.alert("in ajax section within success function");
        	removeLoadingDivAfter("#ordersContainer");
          	
        	$('#ordersContainer').html( '<table cellpadding="0" cellspacing="0" border="0" class="display" id="ordersTable"></table>' ); //jh $('resource').html('something') adds something to resource 
           
        	//console.log(data);
        	if(role=="administrator"){
        		
	        	
	        	ord_table = $("#ordersTable").dataTable({
	                "aaData": data,
	                "aaSorting": [[ 3, "desc" ]],
	                "aoColumns": [
	                {  "bVisible": false },
	                { "sTitle": "OrdersNumber" },
	                { "sTitle": "Buyer" },
	                { "sTitle": "Purchased on" },
	                { "bVisible": false  },
	                { "sTitle": "Fulfillment State" }, 
	                { "sTitle": "Financial State" },
	                { "sTitle": "Total","fnRender": function ( oObj ) {return '$ '+oObj.aData[7];}}
	                ],
	        		"bJQueryUI": true,
	        		"bAutoWidth": false,
	        		"sPaginationType": "full_numbers"
	        	});
        		
        	}else{
	        	
	        	ord_table = $("#ordersTable").dataTable({
	                "aaData": data,
	                "aaSorting": [[ 3, "desc" ]],
	                "aoColumns": [
	                {  "bVisible": false },
	                { "sTitle": "OrdersNumber" },
	                { "bVisible": false},
	                { "sTitle": "Purchased on" },
	                { "bVisible": false  },
	                { "sTitle": "Fulfillment State" }, 
	                { "sTitle": "Financial State" },
	                { "sTitle": "Total","fnRender": function ( oObj ) {return '$ '+oObj.aData[7];}}
	                ],
	        		"bJQueryUI": true,
	        		"bAutoWidth": false,
	        		"sPaginationType": "full_numbers"
	        	});
        	}

            $("#ordersTable").removeAttr("style");
            $('#ordersTable tbody tr td').die();
            $('#ordersTable tbody tr td').live('click', ord_rowClickHandler );

        },
        error: function(XMLHttpRequest, textStatus, errorThrown){
        	removeLoadingDivAfter("#ordersContainer");
        	displayError("#ordersContainer",errorThrown);
        }
    });
}


function ord_rowClickHandler(){

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
		ord_table.fnClose( nTr );
		$(nTr).css("color","");
	}else{
		ord_openDetailsRow(nTr);
	}
}


function ord_openDetailsRow(nTr){


	ord_table.fnOpen( nTr, ord_formatDetails(ord_table, nTr), "ui-state-highlight" );	
	var aData = ord_table.fnGetData( nTr );

	//window.alert("in ord_openDetailsRow: " + "#seeDetails"+aData[0] );	

	var containerId = "#orderDetails"+aData[0];			
	jQuery("#seeDetails"+aData[0]).button();
	jQuery("#seeDetails"+aData[0]).click(function(){  //jh here is the event handler for this button!!
		orditems_reload(aData[0],aData[1]);
	});
	/*
	jh NOTE:  I do not think that this was functioning on the old system for roles of type student!!! double check with Professor
	The buttons approveOrder,declineOrder,cancelOrder are defined for the administrator only on function: ord_formatDetails
	below I have matched the logic accordingly.
	*/	
	var role = $("#role").val();

	if(role=="administrator" && aData[5] == "PENDING APPROVAL")
	{
		$("#approveOrder"+aData[0]).button();
		$("#declineOrder"+aData[0]).button();
		$("#approveOrder"+aData[0]).click(function(){
		ord_approve(containerId,nTr, aData[0],aData[1]);
		});
		$("#declineOrder"+aData[0]).click(function(){
			ord_decline(containerId,nTr, aData[0],aData[1]);
		});
	}
	if(role=="administrator" && (aData[5] == "APPROVED" || aData[5] == "CHARGED" || aData[5] == "DELIVERED"))
	{
		$("#cancelOrder"+aData[0]).button();

	
		$("#cancelOrder"+aData[0]).click(function(){
			ord_cancel(containerId,nTr,aData[0],aData[1]);
		});	
	}

}

function ord_formatDetails ( oTable, nTr )
{	
	var role = $("#role").val();	
	var aData = oTable.fnGetData( nTr );

	var sOut = '';
	sOut += '<div id="orderDetails'+ aData[0]+'">';
	
	sOut += '	<div class="buttonColumnDetails">';
	
	sOut += '		<button id="seeDetails'+aData[0]+'">Details</button>';
	
	if(role=="administrator" && aData[5] == "PENDING APPROVAL")
	{
		sOut += '	<button id="approveOrder'+aData[0]+'">Approve</button>';
		sOut += '	<button id="declineOrder'+aData[0]+'">Decline</button>';	
	}
	if(role=="administrator" && (aData[5] == "APPROVED" || aData[5] == "CHARGED" || aData[5] == "DELIVERED"))
	{
		sOut += '	<button id="cancelOrder'+aData[0]+'">Cancel</button>';	
	}

	sOut += '	</div>';
	sOut += '</div>';
	//window.alert("in ord_formatDetails: " + sOut );
	return sOut;
}

function orditems_reload(orderid, ordernumber)
{

	//window.alert("in orditems_reload");
	$("#ordersWrapper").hide();
	
	//construct the container for order items 
	var div = '';
    div+='		<p class="tableTop">';
    div+='			<span class="page-title">Order '+ordernumber+'</span>';
    div+='			<a href="#" class="back-to-orders">Back to Order Manager</a>';
    div+='		</p>';
    div+='		<div id="orderItemsContainer"></div>';
   
	$('#orderItemsWrapper').hide();
	$('#orderItemsWrapper').empty();
	$('#orderItemsWrapper').html(div);

	//Add listener to back button
	$("#orderItemsWrapper .back-to-orders").button();
	$("#orderItemsWrapper .back-to-orders").die();
	$("#orderItemsWrapper .back-to-orders").live("click",function() {
		ord_reload();
	});
	
	$('#orderItemsWrapper').show();
	
	createLoadingDivAfter("#orderItemsContainer","Loading orders items");

	//window.alert("in orders.js , orderitems_reload, before ajax call");
	//Load Table
    $.ajax({
        type: 'POST',
        url: ordersphpURL,
        dataType: 'json',
        data: {
            action: 'reloadOrderItems',
            orderid:orderid
        },
        success: function(data){
        	
        	removeLoadingDivAfter("#orderItemsContainer");

        	$('#orderItemsContainer').html( '<table cellpadding="0" cellspacing="0" border="0" class="display" id="orderItemsTable"></table>' );
            
        	orditems_table = $("#orderItemsTable").dataTable({
                "aaData": data.orderItems,
                "aoColumns": [
                    { "bVisible": false },
                    { "bVisible": false },
	                { "sTitle": "Name" },
	                { "bVisible": false },
	                { "bVisible": false }, 
	                { "sClass": "center", "sTitle": "Quantity" },
	                { "sTitle": "Unit Price","fnRender": function ( oObj ) {return '$'+oObj.aData[6];}},
	                { "sTitle": "Subtotal", "fnRender": function ( oObj ) { return '$'+oObj.aData[7];}},
	                { "sTitle": "Cancelled","fnRender": function ( oObj ) { return oObj.aData[8]=="1" ? "Yes" : "No"; }},
	                {"bVisible": false}
                ],
        		"bJQueryUI": true,
        		"bAutoWidth": false,
        		"sPaginationType": "full_numbers"
        	});
        	
        	$('#orderItemsContainer').append("<span style='font-size:14px' class='ui-state-highlight'><strong>Order Total: $"+data.orderTotal+"<strong></span>");
        	
            $("#orderItemsTable").removeAttr("style");
            $('#orderItemsTable tbody tr td').die();
            $('#orderItemsTable tbody tr td').live('click', orditems_rowClickHandler );

        },
        error: function(XMLHttpRequest, textStatus, errorThrown){
        	removeLoadingDivAfter("#orderItemsContainer");
        	displayError("#orderItemsContainer", errorThrown);
        }
    });
    
}

function orditems_rowClickHandler(){

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
		orditems_table.fnClose( nTr );
		$(nTr).css("color","");
	}else{
		orditems_openDetailsRow(nTr);
	}
}


function orditems_openDetailsRow(nTr){

	orditems_table.fnOpen( nTr, orditems_formatDetails(orditems_table, nTr), "ui-state-highlight" );
	
	var aData = orditems_table.fnGetData( nTr );
	var containerId = "#orderItemDetails"+aData[0];	
	
	$("#cancelOrderItem"+aData[0]).button();
	$("#cancelOrderItem"+aData[0]).click(function(){
		orditems_cancel(containerId, nTr, aData[0]);
	});
	
}

function orditems_formatDetails ( oTable, nTr )
{

	
	var role = $("#role").val();	
	var aData = oTable.fnGetData( nTr );
	var id = aData[0];
	var sOut = '';
	var sOut = '';
	sOut += '<div id="orderItemDetails'+id+'">';
	// Description
	sOut += '<div class="descriptionDetails">';
	sOut += '	<p><strong>Description:</strong>'+aData[9]+'</p>';
	sOut += '</div>';
	sOut += '	<div class="buttonColumnDetails">';
	if(role =="administrator" && aData[8]=="No")
		sOut += '	<button id="cancelOrderItem'+aData[0]+'">Cancel</button>';	
	sOut += '	</div>';
	sOut += '</div>';
	return sOut;
}

function ord_approve(containerId, nTr, orderid)
{
	$(containerId).empty();

	createLoadingDivAfter(containerId,"Approving order");

	var userid= $("#userid").val();
	
    $.ajax({
        type: 'POST',
        url: ordersphpURL,
        dataType: 'json',
        data: {
            action: 'approveOrder',
            orderid:orderid,
				userid:userid
        },
        success: function(data){
        	removeLoadingDivAfter(containerId);
            
            var order = data.order; 
            
            if(data.success)
        	{
            	          	
            	displayMessage(containerId,  "Order ["+order.ordernumber+"] has been approved",function(){
            		$(nTr).css("color","");
                	ord_table.fnClose( nTr );
                });
              	
        	}else{
        		var error = "Order could not be approved and its status has changed to declined. "+data.message;
           
        		displayError(containerId,error, function(){
	        		$(nTr).css("color","");
	            	ord_table.fnClose( nTr );
	            });
           }

        	ord_table.fnUpdate( [
          	                    order.id,
          	         			order.ordernumber,
          	        			order.username,
          	        			order.purchasedate,
          	        			order.lastmodification ,
          	        			order.fulfillmentorderstate,
          	        			order.financialorderstate ,
          	        			order.total
          	                     ],
			                      nTr,
			                      false,
			                      false); 


        },
        error: function(XMLHttpRequest, textStatus, errorThrown){
        	removeLoadingDivAfter(containerId);
        	displayError(containerId, errorThrown,function(){
        		$(nTr).css("color","");
            	ord_table.fnClose( nTr );
            });
        	
        }
    });

}

function orditems_cancel(containerId, nTr, id)
{
	$(containerId).empty();
	
	createLoadingDivAfter(containerId,"Cancelling order item");
	
	 $.ajax({
	        type: 'POST',
	        url: ordersphpURL,
	        dataType: 'json',
	        data: {
	            action: 'cancelOrderItem',
	            id:id
	        },
	        success: function(data){
	        		        	
	        	removeLoadingDivAfter(containerId);

	            if(data.success)
	        	{
	            	displayMessage(containerId,  "Item has been cancelled", function(){
	            		$(nTr).css("color","");
	                	orditems_table.fnClose( nTr );
	                });
  
	        	
	        	}else{
	        		displayError(containerId, data.message,function(){
	            		$(nTr).css("color","");
	                	orditems_table.fnClose( nTr );
	                });
	        	}
	            
            	var orderitem = data.orderitem;
            	orditems_table.fnUpdate( [
		             	                orderitem.id,
		             	                orderitem.itemid,
		             	                orderitem.name,
		             	                orderitem.type,
		             	                orderitem.description ,
		             	                orderitem.quantity,
		             	                orderitem.price ,
		             	              	orderitem.subtotal,
		             	              	orderitem.cancelled,
		             	              	orderitem.description
             	                     ],
 			                      nTr,
 			                      false,
 			                      false); 
	                


	        },
	        error: function(XMLHttpRequest, textStatus, errorThrown){
	        	removeLoadingDivAfter(containerId);
	        	displayError(containerId, errorThrown, function(){
            		$(nTr).css("color","");
                	orditems_table.fnClose( nTr );
                });
	        }
	    });
}

function ord_cancel(containerId, nTr, orderid)
{
	$(containerId).empty();
	
	createLoadingDivAfter(containerId,"Cancelling order");
	var userid= $("#userid").val();
	
	 $.ajax({
	        type: 'POST',
	        url: ordersphpURL,
	        dataType: 'json',
	        data: {
	            action: 'cancelOrder',
	            orderid:orderid,
					userid:userid
	        },
	        success: function(data){
	        	
	        	removeLoadingDivAfter(containerId);

	            var order = data.order; 

	            if(data.success)
	        	{	            	
	            	displayMessage(containerId,  "Order ["+order.ordernumber+"] has been cancelled",function(){
	            		$(nTr).css("color","");
	                	ord_table.fnClose( nTr );
	                });
   
	        	
	        	}else{
	        		displayError(containerId, "Order could not be cancelled.",function(){
	            		$(nTr).css("color","");
	                	ord_table.fnClose( nTr );
	                });
	        	}
         	
            	ord_table.fnUpdate( [
              	                    order.id,
              	         			order.ordernumber,
              	        			order.username,
              	        			order.purchasedate,
              	        			order.lastmodification ,
              	        			order.fulfillmentorderstate,
              	        			order.financialorderstate ,
              	        			order.total
              	                     ],
  			                      nTr,
  			                      false,
  			                      false); 

	        },
	        error: function(XMLHttpRequest, textStatus, errorThrown){
	        	removeLoadingDivAfter(containerId);
	        	displayError(containerId, errorThrown,function(){
	        		$(nTr).css("color","");
	            	ord_table.fnClose( nTr );
	            });
	        }
	    });
}


function ord_decline(containerId, nTr, orderid)
{
	$(containerId).empty();
	
	createLoadingDivAfter(containerId,"Declining order");
	var userid= $("#userid").val();
	
    $.ajax({
        type: 'POST',
        url: ordersphpURL,
        dataType: 'json',
        data: {
            action: 'declineOrder',
            orderid:orderid,
				userid:userid
        },
        success: function(data){
        	
        	removeLoadingDivAfter(containerId);
        	
            var order = data.order; 
            
            if(data.success)
        	{            	
            	displayMessage(containerId,  "Order ["+order.ordernumber+"] has been declined",function(){
            		$(nTr).css("color","");
                	ord_table.fnClose( nTr );
                });
 
        	
        	}else{
        		displayError(containerId, "Order could not be declined.",function(){
            		$(nTr).css("color","");
                	ord_table.fnClose( nTr );
                });
        	}
      	
        	ord_table.fnUpdate( [
         	                    order.id,
         	         			order.ordernumber,
         	        			order.username,
         	        			order.purchasedate,
         	        			order.lastmodification ,
         	        			order.fulfillmentorderstate,
         	        			order.financialorderstate ,
         	        			order.total
         	                     ],
			                      nTr,
			                      false,
			                      false);  
                
        },
        error: function(XMLHttpRequest, textStatus, errorThrown){
        	removeLoadingDivAfter(containerId);
        	displayError(containerId, errorThrown,function(){
        		$(nTr).css("color","");
            	ord_table.fnClose( nTr );
            });
        }
    });
}
