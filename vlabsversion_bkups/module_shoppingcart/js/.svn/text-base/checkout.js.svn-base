
var checkoutphpURL = '/modules/server/checkout/nopayment_checkout/checkout.php';

function checkout()
{

	$('#shoppingCart').hide();
	$("#checkout").show();
	
	$("#checkout .tableTop").empty();
    $("#checkout .tableTop").append( "<span class='page-title'>No Payment Checkout</span>");
	$("#checkout .tableTop").append( '<button id="back-to-cart" >Back to Shopping Cart</button>');
	
	$("#back-to-cart").button();
    $('#back-to-cart').click(function(){
    	backToShoppingCart();
    	reloadShoppingCart();
     });
    
    $("#checkoutContainer").empty();    
	createLoadingDivAfter("#checkoutContainer","Loading Checkout");	
	
    var cart = $('#sc_checkout_form');
    var form = "";

    var xml = "";
    $( '#cartxml', cart ).each( function () {
        form += "&";
        form += $(this).attr('name');
        form += '=';
        xml = escape($(this).attr('value'));
        form += xml;
        
    });


    $.ajax({
        type: 'POST',
        url: checkoutphpURL,
        dataType: 'json',
        data:"action=proceedToCheckout"+form,
        success: function(data){      	
        	removeLoadingDivAfter("#checkoutContainer");
            $("#checkoutContainer").html(data.tablecontent);
            $("#checkoutContainer").append(data.formcontent);

            $("#checkouttable").dataTable({
        		"bPaginate": false,
        		"bLengthChange": false,
        		"bFilter": false,
        		"bSort": true,
        		"bInfo": false,
        		"bAutoWidth": true,
        		"bJQueryUI": true
            });
            
            $("#checkouttable").removeAttr("style");
            $("#checkouttable").addClass("display");
        	
        	$('#placeorderbtn').button();
            $('#placeorderbtn').click(function(){
                placeOrder(xml,$("#userid").val());

            });

        },
        error: function(XMLHttpRequest, textStatus, errorThrown){
        	removeLoadingDivAfter("#checkoutContainer");
        	displayError("#checkoutContainer", errorThrown);

        }
    });

}

function placeOrder(cart,userid)
{
    $("#checkoutContainer").empty();
	createLoadingDivAfter("#checkoutContainer","Placing Order");	
   
    var nopayment_form = $('#nopayment_form');
    var form = "";
    form+="cart="+cart;
    form+="&"
    form+="userid="+userid;

    $( '#explanation', nopayment_form ).each( function () {
        form += "&";
        form += $(this).attr('name');
        form += "=";
        form += $(this).attr('value');
    });

    $.ajax({
        type: 'POST',
        url: checkoutphpURL,
        dataType: 'json',
        data:"action=placeOrder&"+form,
        success: function(data){
        	removeLoadingDivAfter("#checkoutContainer");
        	if(data.success){
        		
        		reloadShoppingCart();
        		ord_reload();
        		
               	$("#checkout .tableTop").empty();
                $("#checkout .tableTop").append( "<span class='page-title'>Confirmation</span>");
            	$("#checkout .tableTop").append( '<button href="#" class="back-to-orders">Back to Order Manager</a>');
            	$("#checkout .tableTop").append( '<button href="#" class="back-to-store">Back to Store</a>');
            	$("#checkout .tableTop").append( '<button id="back-to-cart" >Back to Shopping Cart</button>');
                
            	$("#back-to-cart").button();
                $('#back-to-cart').click(function(){
                	backToShoppingCart();
                 });
            	$(".back-to-orders").button();
            	$(".back-to-orders").click(function(){
       			 	reloadShoppingCart();
            		backToShoppingCart();
            		 ord_reload();
        			 openOrdersTab();

            	});
            	$(".back-to-store").button();
            	$(".back-to-store").click(function(){
       			 	reloadShoppingCart();
            		backToShoppingCart();
            		reloadStore();
    				openStoreTab();
            	});

            	$("#checkoutContainer").empty();
            	displayMessage("#checkoutContainer", data.content);       		
        		
        	}else{
        		$("#checkoutContainer").empty();
        		displayError("#checkoutContainer", "Order could not be placed. Please try again later.");  
        		
        	}

            
        },
        error: function(XMLHttpRequest, textStatus, errorThrown){
        	removeLoadingDivAfter("#checkoutContainer");
        	displayError("#checkoutContainer", errorThrown);

        }
    });
}


function backToShoppingCart(){
	$("#checkout").hide();
	$('#shoppingCart').show();

}

function clearAllTabs(){
	//alert('clearAllTabs');
	$('#ordersContainer').removeClass('ui-tabs-hide');
	$('#storeContainer').removeClass('ui-tabs-hide');
	$('#shoppingCartContainer').removeClass('ui-tabs-hide');
}
