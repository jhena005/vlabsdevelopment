/*
 * shoppingcart.js
 * @author Vanessa Ramirez
 *
 *
 */


var shoppingcartphpURL = '/modules/module_shoppingcart/server/shoppingcart.php';

function openShoppingCartTab()
{
    $('#tabs').tabs("select","#shoppingCartTab");

}

function reloadShoppingCart()
{
	$("#shoppingCartContainer").empty();	
	createLoadingDivAfter("#shoppingCartContainer","Updating");	

	var userid= $("#userid").val();

    $.ajax({
        type: 'POST',
        url: shoppingcartphpURL,
        dataType: 'json',
        data: {
            action: 'reloadShoppingCart',
				userid:userid
        },
        success: function(data){
        	removeLoadingDivAfter("#shoppingCartContainer");
    		$('#shoppingCartContainer').html( '<table  cellpadding="0" cellspacing="0" border="0" class="display" id="cartTable"></table>' );
            
            //Load preview info
            $('#previewcount').html(data.cartpreview);
            $('#preview').html(data.preview);

            var items ;
            if(data.cart.shoppingCart=="null" || !data.cart.shoppingCart)
            	data.cart.shoppingCart = "[]";
            eval("items="+data.cart.shoppingCart);
            
            $('#cartTable').dataTable({
            	"aaData": items,
	        	"aoColumns": [
 		             {"sTitle": "Item",
 		            	  "fnRender": function ( oObj ) {
 		     					return '<strong>'+oObj.aData[0]+'</strong><br />'+oObj.aData[1];
 		     				}
 		     		  },
 		     		  { "bVisible": false,  "aTargets": [ 1 ] },
  		              { "sTitle": "Price",
  		            	  "fnRender": function ( oObj ) {
  		     					return "$"+oObj.aData[2];
  		     				}
  		     		  },
 		              { "sTitle": "Quantity",
 		            	  "fnRender": function ( oObj ) {
 		     					return '<input type="text" class="qty"  name="qty' + oObj.aData[4] + '" value="' + oObj.aData[3] + '" size="3" maxlength="3" />';
 		     				}
 		     		  },
 		     		  { "bVisible": false,  "aTargets": [ 4 ] },
  		              { "sTitle": "Subtotal",
   		            	  "fnRender": function ( oObj ) {
   		     					return "$"+oObj.aData[5];
   		     				}
   		     		  },
 		              {	"sTitle": "Remove",
 		            	  "fnRender": function ( oObj ) {
 		     					return '<div id="removeButton'+oObj.aData[6]+'"><a class="remove-item-from-sc" href="#" id="removeItemBtn"'+oObj.aData[6]+' onclick="shc_deleteItem('+oObj.aData[6]+',\''+oObj.aData[7]+'\' )">Remove Item</a></div>';
 		     				}
 		     		  },
 		     		  { "bVisible": false,  "aTargets": [ 7 ] }
 	              ],
	    		"bPaginate": false,
	    		"bLengthChange": false,
	    		"bFilter": false,
	    		"bSort": true,
	    		"bInfo": false,
	    		"bAutoWidth": false,
	    		"bJQueryUI": true
            });
            

            $('.remove-item-from-sc').button();

            if(data.cart.shoppingCart!="[]")
            { 	
            	
            	$('#shoppingCartContainer').append("<div id ='updateCartContainer'><button style='text-align:right' id='update-cart'>Update Cart</button></div>"); 
	            $('#update-cart').button();
	            $('#update-cart').click(function(){
	            	shc_updateCart();
	            });
	            
	            if(data.cart.buttonsCount=="2"){
	            	$('#shoppingCartContainer').append("<div class='ui-state-error' style='padding: 0pt 0.7em;'><p><span class='ui-icon ui-icon-alert' style='float: left; margin-right: 0.3em;'></span>Not billable items can obly be purchased with the Checkout button.</p></div>");
	            	$('#shoppingCartContainer').append("<div class='ui-state-error' style='padding: 0pt 0.7em;'><p><span class='ui-icon ui-icon-alert' style='float: left; margin-right: 0.3em;'></span>Billable items can only be purchased with Google Checkout button.</p></div>")
	            }
	            
	            $('#shoppingCartContainer').append(data.cart.checkoutButtons);
	            

	            
            }
            
            
        },
        error: function(XMLHttpRequest, textStatus, errorThrown){
        	removeLoadingDivAfter("#shoppingCartContainer");
        	displayError('#shoppingCartContainer', errorThrown);
        }
    });


}



function shc_addToCart(id, type)
{
	$("#addToCart"+id).empty();
	createLoadingDivAfter("#addToCart"+id,"Adding item to cart");
	
    $.ajax({
        type: 'POST',
        url: shoppingcartphpURL,
        dataType: 'json',
        data: "action=add&type="+type+"&id="+id,
        success: function(data){
        	removeLoadingDivAfter("#addToCart"+id);
        	reloadShoppingCart();
        	
        	if(data){            	
            	displayMessage("#addToCart"+id, "Item added to cart", function(){
            		$("#addToCart"+id).html('<a class="add-to-cart" href="#" onclick="shc_addToCart('+id+',\''+type+'\' )">Add to Cart</a>');        		
            		$(".add-to-cart").button();       	
            	});
        		
        		
        	}else{
            	displayError("#addToCart"+id, "Item is not available", function(){
            		$("#addToCart"+id).html('<a class="add-to-cart" href="#" onclick="shc_addToCart('+id+',\''+type+'\' )">Add to Cart</a>');        		
            		$(".add-to-cart").button();
            	});
        	}	

        },
        error: function(XMLHttpRequest, textStatus, errorThrown){
        	removeLoadingDivAfter("#addToCart"+id);
        	reloadShoppingCart();
        	
        	displayError("#addToCart"+id, errorThrown, function(){     		     		
        		$("#addToCart"+id).html('<a class="add-to-cart" href="#" onclick="shc_addToCart('+id+',\''+type+'\' )">Add to Cart</a>');
        		$(".add-to-cart").button();
        		
        	});
        	
        	
        }
    });

}

function shc_deleteItem(id, type)
{
	$("#removeButton"+id).empty();
	createLoadingDivAfter("#removeButton"+id,"Updating");	
	
    var prefix = "i-";
    if(type=='p')
        prefix="p-";

    
    $.ajax({
        type: 'POST',
        url: shoppingcartphpURL,
        dataType: 'json',
        data: "action=delete&&id="+prefix+""+id,
        success: function(data){
        	removeLoadingDivAfter("#removeButton"+id);
        	displayMessage("#removeButton"+id, "Item was deleted");
            reloadShoppingCart();
            $("#removeButton"+id).html('<div id="removeButton'+id+'"><a class="remove-item-from-sc" href="#" id="removeItemBtn"'+id+' onclick="shc_deleteItem('+id+',\''+type+'\' )">Remove Item</a></div>');

        },
        error: function(XMLHttpRequest, textStatus, errorThrown){
        	removeLoadingDivAfter("#removeButton"+id);
        	displayError("#removeButton"+id, errorThrown);
        	$("#removeButton"+id).html('<div id="removeButton'+id+'"><a class="remove-item-from-sc" href="#" id="removeItemBtn"'+id+' onclick="shc_deleteItem('+id+',\''+type+'\' )">Remove Item</a></div>');
        	reloadShoppingCart();
        }
    });

}

function shc_updateCart()
{
    var cart = $('#cartTable');
	$("#shoppingCartContainer").empty();
	createLoadingDivAfter("#shoppingCartContainer","Updating");	
    var form = "";


    $( 'input', cart ).each( function () {
        form += "&";
        form += $(this).attr('name');
        form += "=";
        form += $(this).attr('value');
    });


    $.ajax({
        type: 'POST',
        url: shoppingcartphpURL,
        dataType: 'json',
        data:"action=update"+form,
        success: function(data){           
        	removeLoadingDivAfter("#shoppingCartContainer");
        	reloadShoppingCart();
                   
        },
        error: function(XMLHttpRequest, textStatus, errorThrown){       	
        	removeLoadingDivAfter("#shoppingCartContainer"); 
        	reloadShoppingCart();

        }
    });

}


