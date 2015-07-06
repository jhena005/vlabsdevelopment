
$(document).ready(function() {
	tz_init();
    reloadStoreFront(); 
    //testCheckoutHandler();
    
});

function reloadStoreFront()
{

	
    var role = $("#role").val();


    $("#placeorderdiv").hide();


    //Clean tabs div to build it

    $("#tabs").html("");   
    $("#tabs").append("<ul></ul>");


      
    if(role=="admin"){
    	
    	//Orders
        $("#tabs ul").append('<li><a href="#ordersTab"><span>Orders Manager</span></a></li>');
        var div = '<div id="ordersTab">';   
        div+='			<div id="ordersWrapper"class="container">';
        div+='				<p class="tableTop">';
        div+='					<span class="page-title">Orders Manager</span>';
        div+='				</p>';
        div+='				<div id="ordersContainer"></div>';
        div+='			</div>'; 
        div+='			<div id="orderItemsWrapper" class="container"></div>';
        div+='	</div>';
        $("#tabs").append(div);
        ord_reload();

        //Store Manager
        $("#tabs ul").append('<li><a href="#storeManagerTab"><span>Store Manager</span></a></li>');
        div = '<div id="storeManagerTab">';
        div+='	<div id="storeWrapper" class="container">';
        div+='		<p class="tableTop">';
        div+='			<span class="page-title">Store Manager</span>';
        div+='			<button id="add-item" >Add Item</button>';
        div+='		</p>';
        div+='		<div id="addItemForm" class="addForm" style="display:none"></div>';
        div+='		<div id="storeManagerContainer"></div>';
        div+='	</div>';
        div+='</div>';
        $("#tabs").append(div);
        $("#add-item").button();
        sto_reloadStoreManager();

        //Custom Packages
        $("#tabs ul").append('<li><a href="#packageManagerTab"><span>Custom Packages</span></a></li>');
        div = '<div id="packageManagerTab">';  
        
        div+='	<div id="packagesWrapper" class="container">';
        div+='		<p class="tableTop">';
        div+='			<span class="page-title">Packages Manager</span>';
        div+='			<button id="add-package" >Add Package</button>';
        div+='		</p>';
        div+='		<div id="addPackageForm" class="addForm" style="display:none"></div>';
        div+='		<div id="packagesContainer"></div>';
        div+='	</div>';
        
        div+='	<div id="packageItemsWrapper" class="container"></div>';
        
        div+='</div>';
        
        $("#tabs").append(div);
        $("#add-package").button();
        $("#add-item-to-package").button();
        pac_reload();
        
        //Courses Pre-assignment
        $("#tabs ul").append('<li><a href="#preassignmentTab"><span>Pre-assignement</span></a></li>');
        div = '<div id="preassignmentTab">';
        div+='	<div id="preassignmentWrapper" class="container">';
        div+='		<p class="tableTop">';
        div+='			<span class="page-title">Item Preassignment</span>';
        div+='			<button id="add-preassignment" >Preassign</button>';
        div+='		</p>';
        div+='		<div id="preassignmentForm" class="addForm" style="display:none"></div>';
        div+='		<div id="preassignmentContainer"></div>';
        div+='	</div>';
        div+='</div>';
        $("#tabs").append(div);
        $("#add-preassignment").button();
        pre_reload();



    }else{
    	
    	//Orders
        $("#tabs ul").append('<li><a href="#ordersTab"><span>Orders Manager</span></a></li>');
        var div = '<div id="ordersTab">';   
        div+='			<div id="ordersWrapper"class="container">';
        div+='				<p class="tableTop">';
        div+='					<span class="page-title">Orders Manager</span>';
        div+='				</p>';
        div+='				<div id="ordersContainer"></div>';
        div+='			</div>'; 
        div+='			<div id="orderItemsWrapper" class="container"></div>';
        div+='	</div>';
        $("#tabs").append(div);
        ord_reload();

        //Store        
        $("#tabs ul").append('<li><a href="#storeTab"><span>Store</span></a></li>');
        div ='<div id="storeTab">';
        div+='	<div id="store" class="container">';
        div+='			<p class="tableTop">';
        div+='				<span class="page-title">Quota Store Catalog</span>';       
        div+='			</p>';    
        div+='		<div id="storeContainer"></div>';
        div+='	</div>';
        div+='</div>';
        $("#tabs").append(div);
        sto_reloadStore();

        //Shopping Cart
        $("#tabs ul").append('<li><a href="#shoppingCartTab"><span id="previewcount"> Cart</span></a></li>');
        div = '<div id="shoppingCartTab">';
        div+='	<div id="shoppingCart" class="container">';
        div+='			<p class="tableTop">';
        div+='				<span class="page-title">Shopping Cart</span>';      
        div+='			</p>';   
        div+='		<div id="shoppingCartContainer"></div>';
        div+='	</div>';

        
        div+='	<div id="checkout" style="display:none" class="container">';
        div+='			<p class="tableTop">';     
        div+='			</p>';
        div+='		<div id="checkoutContainer"></div>';
        div+='	</div>';
        
        div+='</div>';
        $("#tabs").append(div);
        $("#shoppingCart").show();
        reloadShoppingCart();

    }
    //Show tabs
    $('#tabs').tabs({
		select: function(event, ui) {
			clearAllTabs();
			//alert('select');
		}
	});
    clearAllTabs
    
    //	Form Buttons
    $("#add-item").click(function() {
    	sto_openForm("#addItemForm", true);
    	$("#add-item").button("disable");
    });
    
    $("#add-package").click(function() {
    	pac_openForm("#addPackageForm", true);
    	$("#add-package").button("disable");
    });
    
    $("#add-preassignment").click(function(){
    	pre_openForm("#preassignmentForm", true);
    	$("#add-preassignment").button("disable");
    });

}


function testCheckoutHandler(){
	
	   $.ajax({
	        type: 'POST',
	        url:  '../shoppingcart/server/checkout/google_checkout/checkouthandler.php',
	        dataType: 'json',
	        data: {
	        },
	        success: function(data){
	        	alert("success");
	        },
	        error: function(XMLHttpRequest, textStatus, errorThrown){
	        	alert("error");
	        }
	    });	
	        
}



