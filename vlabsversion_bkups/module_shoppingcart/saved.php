<?php

//echo "current user is: ".$_REQUEST['currentUser'] ." and role: ". $_REQUEST['currentRole'];
//var_dump($_REQUEST);


?>



<!--

<link type="text/css" href="/modules/jquery/jquery-ui/css/redmond-light/jquery-ui-1.8.5.custom.css" rel="stylesheet" />
-->


<?php
	//grab the theme parameter passed from the url
	$themeset = $_REQUEST['currentTheme'];
	//echo '<script type="text/javascript">alert("current theme is: ' . $themeset . '")</script>';
	//include the approriate css for the theme 
	switch ($themeset) {
		case 'default':
			echo '<link type="text/css"  href="jquery/jquery-ui-themes/themes/default/jquery-ui.css" rel="stylesheet">';
			echo	'<style type="text/css" media="screen">';
	
			echo	'@import "jquery/jquery-ui-themes/themes/default/demo_table_jui.css";';
	
					/*
					 * Override styles needed due to the mix of three different CSS sources! For proper examples
					 * please see the themes example in the 'Examples' section of this site
					 */

			echo		'.dataTables_info { padding-top: 0; }';
			echo		'.dataTables_paginate { padding-top: 0; }';
			echo		'.css_right { float: right; }';
			echo		'#example_wrapper .fg-toolbar { font-size: 0.8em }';
			echo		'#theme_links span { float: left; padding: 2px 10px; }';
	
			echo 	'</style>';


			break;
		case 'blue':
			echo '<link type="text/css"  href="jquery/jquery-ui-themes/themes/blue/jquery-ui.css" rel="stylesheet">';
		

			echo	'<style type="text/css" media="screen">';
	
			echo	'@import "jquery/jquery-ui-themes/themes/blue/demo_table_jui.css";';
	
					/*
					 * Override styles needed due to the mix of three different CSS sources! For proper examples
					 * please see the themes example in the 'Examples' section of this site
					 */

			echo		'.dataTables_info { padding-top: 0; }';
			echo		'.dataTables_paginate { padding-top: 0; }';
			echo		'.css_right { float: right; }';
			echo		'#example_wrapper .fg-toolbar { font-size: 0.8em }';
			echo		'#theme_links span { float: left; padding: 2px 10px; }';
	
			echo 	'</style>';


			break;
		case 'bluehtml':
			echo '<link type="text/css"  href="jquery/jquery-ui-themes/themes/bluehtml/jquery-ui.css" rel="stylesheet">';
			echo	'<style type="text/css" media="screen">';
	
			echo	'@import "jquery/jquery-ui-themes/themes/bluehtml/demo_table_jui.css";';
	
					/*
					 * Override styles needed due to the mix of three different CSS sources! For proper examples
					 * please see the themes example in the 'Examples' section of this site
					 */

			echo		'.dataTables_info { padding-top: 0; }';
			echo		'.dataTables_paginate { padding-top: 0; }';
			echo		'.css_right { float: right; }';
			echo		'#example_wrapper .fg-toolbar { font-size: 0.8em }';
			echo		'#theme_links span { float: left; padding: 2px 10px; }';
	
			echo 	'</style>';


			break;
		case 'green':
			echo '<link type="text/css"  href="jquery/jquery-ui-themes/themes/green/jquery-ui.css" rel="stylesheet">';

			echo	'<style type="text/css" media="screen">';
	
			echo	'@import "jquery/jquery-ui-themes/themes/green/demo_table_jui.css";';
	
					/*
					 * Override styles needed due to the mix of three different CSS sources! For proper examples
					 * please see the themes example in the 'Examples' section of this site
					 */

			echo		'.dataTables_info { padding-top: 0; }';
			echo		'.dataTables_paginate { padding-top: 0; }';
			echo		'.css_right { float: right; }';
			echo		'#example_wrapper .fg-toolbar { font-size: 0.8em }';
			echo		'#theme_links span { float: left; padding: 2px 10px; }';
	
			echo 	'</style>';

			break;
		case 'flatgrey':
			echo '<link type="text/css"  href="jquery/jquery-ui-themes/themes/flatgrey/jquery-ui.css" rel="stylesheet">';

			echo	'<style type="text/css" media="screen">';
	
			echo	'@import "jquery/jquery-ui-themes/themes/flatgrey/demo_table_jui.css";';
	
					/*
					 * Override styles needed due to the mix of three different CSS sources! For proper examples
					 * please see the themes example in the 'Examples' section of this site
					 */

			echo		'.dataTables_info { padding-top: 0; }';
			echo		'.dataTables_paginate { padding-top: 0; }';
			echo		'.css_right { float: right; }';
			echo		'#example_wrapper .fg-toolbar { font-size: 0.8em }';
			echo		'#theme_links span { float: left; padding: 2px 10px; }';
	
			echo 	'</style>';


			break;
		default:
			echo '<link type="text/css"  href="jquery/jquery-ui-themes/themes/default/jquery-ui.css" rel="stylesheet">';
			echo	'<style type="text/css" media="screen">';
	
			echo	'@import "jquery/jquery-ui-themes/themes/default/demo_table_jui.css";';
	
					/*
					 * Override styles needed due to the mix of three different CSS sources! For proper examples
					 * please see the themes example in the 'Examples' section of this site
					 */

			echo		'.dataTables_info { padding-top: 0; }';
			echo		'.dataTables_paginate { padding-top: 0; }';
			echo		'.css_right { float: right; }';
			echo		'#example_wrapper .fg-toolbar { font-size: 0.8em }';
			echo		'#theme_links span { float: left; padding: 2px 10px; }';
	
			echo 	'</style>';


			break;
	}
?>

<!--
<script type="text/javascript" src="/modules/jquery/jquery-ui/js/jquery-1.4.2.min.js"></script>
jh repaced below
-->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.4.2/jquery.min.js"></script> 
<script type="text/javascript" src="jquery/jquery-ui/js/jquery-ui-1.8.4.custom.min.js"></script>
<!--
<script type="text/javascript" src="/modules/jquery/jquery-ui/jquery-ui.js"></script>
jh replaced below
-->
<script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.11.4/jquery-ui.min.js"></script> 

<script type='text/javascript' src='jquery/jquery-ui/dataTables/media/js/jquery.dataTables.min.js'></script>
<script type='text/javascript' src='jquery/jquery-ui/dataTables/examples/examples_support/jquery.jeditable.js'></script>
<link type="text/css" href="css/validation.css" rel="stylesheet" />



<!-- LiveValidation -->
<script type="text/javascript" src="js/livevalidation/livevalidation.js"></script>


<!--Our scripts-->
<script type='text/javascript' src='js/loading.js'></script>
<script type='text/javascript' src='js/tabs.js'></script>
<script type='text/javascript' src='js/shoppingcart.js'></script>
<script type='text/javascript' src='js/packages.js'></script>
<script type='text/javascript' src='js/store.js'></script>
<script type='text/javascript' src='js/checkout.js'></script>
<script type='text/javascript' src='js/orders.js'></script>
<script type='text/javascript' src='js/preassignment.js'></script>
<script type='text/javascript' src='js/timezone.js'></script>
<script type='text/javascript' src='js/dbadmin.js'></script>



<!--CSS-->
<link rel="stylesheet" type="text/css" href="css/styles.css" />
<!-- <link rel="stylesheet" type="text/css" href="css/shoppingcartcss/style.css" /> -->


<!--
<style type="text/css" media="screen">
	
	@import "jquery/jquery-ui/dataTables/media/css/demo_table_jui.css";
	
	/*
	 * Override styles needed due to the mix of three different CSS sources! For proper examples
	 * please see the themes example in the 'Examples' section of this site
	 */
	.dataTables_info { padding-top: 0; }
	.dataTables_paginate { padding-top: 0; }
	.css_right { float: right; }
	#example_wrapper .fg-toolbar { font-size: 0.8em }
	#theme_links span { float: left; padding: 2px 10px; }
	
</style>
-->


<!--
<style type="text/css">
    div#users-contain { width: 350px; margin: 20px 0; }
    div#users-contain table { margin: 1em 0; border-collapse: collapse; width: 100%; }
    div#users-contain table td, div#users-contain table th { border: 1px solid #eee; padding: .6em 10px; text-align: left; }
    .ui-dialog .ui-state-error { padding: .3em; }
    .validateTips { border: 1px solid transparent; padding: 0.3em; }
    .ui-widget {
        font-family:Lucida Grande,Lucida Sans,Arial,sans-serif;
        font-size:0.8em;
    }
</style>
-->
<input id ="userid" type="hidden" value="<?= $_REQUEST['currentUser'] ?>" />
<input id ="username" type="hidden" value="<?= '' ?>" />
<input id ="role" type="hidden" value="<?= $_REQUEST['currentRole'] ?>" />
<input id ="email" type="hidden" value="<?= '' ?>" />



<div id="wrapper">
    
    <div id="message" title="System message"></div>
    <div id="createitem-form" class="form" title="Item Details"></div>
    <div id="createpackage-form" class="form" title="Package Information"></div>
    <div id="additemtopkg-form" class="form"  title="Package Item details"></div>
    <div id="tabs"></div>

</div>




