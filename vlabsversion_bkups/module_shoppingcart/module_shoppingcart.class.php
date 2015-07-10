<?php
//jh for data export and import
$dirname = '/opt/lamp';
$filename = 'module_vlabs_shoppingcart_data.sql';

require_once((dirname(dirname(dirname(dirname(__FILE__))))).'/libraries/configuration.php');
session_start();

//This file cannot be called directly, only included.
if (str_replace(DIRECTORY_SEPARATOR, "/", __FILE__) == $_SERVER['SCRIPT_FILENAME']) {
    exit;
}

/*
 * Class defining the new module
 * The name must match the one provided in the module.xml file
 */
class module_shoppingcart extends EfrontModule {

	/**
	 * Get the module name, for example "Demo module"
	 *
	 * @see libraries/EfrontModule#getName()
	 */
    public function getName() {
    	//This is a language tag, defined in the file lang-<your language>.php
        return shoppingcart;
    }

	/**
	 * Return the array of roles that will have access to this module
	 * You can return any combination of 'administrator', 'student' or 'professor'
	 *
	 * @see libraries/EfrontModule#getPermittedRoles()
	 */
    public function getPermittedRoles() {
        return array("administrator","professor","student");		//This module will be available to administrators
    }

    /**
	 * (non-PHPdoc)
	 * @see libraries/EfrontModule#getCenterLinkInfo()
     */
    public function getCenterLinkInfo() {
    	return array('title' => $this -> getName(),
                     'image' => $this -> moduleBaseLink . 'img/logo.png',
                     'link'  => $this -> moduleBaseUrl);
    }
    
    /**
     * The main functionality
     *
	 * (non-PHPdoc)
	 * @see libraries/EfrontModule#getModule()
     */

	 public function exportData(){

      

	 }

    public function onInstall() {
        eF_executeQuery("drop table if exists module_vlabs_shoppingcart");
		  eF_executeQuery("drop table if exists module_vlabs_shoppingcart_payment_method");
		  eF_executeQuery("drop table if exists module_vlabs_shoppingcart_user_payment");
		  eF_executeQuery("drop table if exists module_vlabs_shoppingcart_order_summary");
		  eF_executeQuery("drop table if exists module_vlabs_shoppingcart_package_summary");
		  eF_executeQuery("drop table if exists module_vlabs_shoppingcart_preassignment");
	     eF_executeQuery("drop table if exists module_vlabs_shoppingcart_store_inventory");
		  eF_executeQuery("drop table if exists module_vlabs_shoppingcart_order");
		  eF_executeQuery("drop table if exists module_vlabs_shoppingcart_log");
        eF_executeQuery("CREATE TABLE `module_vlabs_shoppingcart` (
  `id` bigint(10) unsigned NOT NULL auto_increment,
  `course` bigint(10) unsigned NOT NULL default '0',
  `name` varchar(255) NOT NULL default '',
  `intro` mediumtext,
  `introformat` smallint(4) unsigned NOT NULL default '0',
  `timecreated` bigint(10) unsigned NOT NULL default '0',
  `timemodified` bigint(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COMMENT='Default comment for shoppingcart, please edit me';");
	     eF_executeQuery("CREATE TABLE `module_vlabs_shoppingcart_store_inventory` (
  `id` mediumint(6) unsigned NOT NULL auto_increment,
  `name` varchar(100) NOT NULL default '0',
  `description` varchar(100) default NULL,
  `price` double unsigned NOT NULL,
  `quantity` bigint(10) unsigned default NULL,
  `active` blob NOT NULL,
  `creationdate` datetime NOT NULL,
  `lastmodification` datetime NOT NULL,
  `unlimited` blob NOT NULL,
  `referenceid` varchar(100) default NULL,
  `type` enum('ITEM','PACKAGE','PACKAGE ITEM') NOT NULL default 'ITEM',
  `billable` blob NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=473 DEFAULT CHARSET=utf8 COMMENT='This table will contain all the items available in the shop';");
		  eF_executeQuery("CREATE TABLE `module_vlabs_shoppingcart_payment_method` (
  `id` mediumint(6) unsigned NOT NULL auto_increment,
  `name` varchar(100) NOT NULL default '0',
  `description` varchar(100) NOT NULL default '',
  `merchantid` varchar(100) default NULL,
  `merchantkey` varchar(15) default NULL,
  `servertype` varchar(22) default NULL,
  `currency` varchar(3) default NULL,
  `type` enum('PAYMENT','NO PAYMENT') NOT NULL default 'NO PAYMENT',
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COMMENT='This table will contain all the available payment methods';");
		  eF_executeQuery("CREATE TABLE `module_vlabs_shoppingcart_user_payment` (
  `id` mediumint(6) unsigned NOT NULL auto_increment,
  `email` varchar(100) NOT NULL default '0',
  `payment` bigint(10) unsigned NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='This table will contain the type of paymemt for every user';"); 		  
		  eF_executeQuery("CREATE TABLE `module_vlabs_shoppingcart_order` (
  `id` mediumint(6) unsigned NOT NULL auto_increment,
  `userid` mediumint(6) unsigned NOT NULL,
  `purchasedate` bigint(20) default NULL,
  `lastmodification` bigint(20) default NULL,
  `fulfillmentorderstate` varchar(100) NOT NULL default '',
  `financialorderstate` varchar(100) NOT NULL default '',
  `ordernumber` varchar(15) NOT NULL default '',
  `total` double(15,0) unsigned NOT NULL,
  `cancelled` binary(1) NOT NULL,
  `payment` binary(1) NOT NULL,
  `refund` double default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2919 DEFAULT CHARSET=utf8 COMMENT='This table will contain the order information';"); 
		  eF_executeQuery("CREATE TABLE `module_vlabs_shoppingcart_order_summary` (
  `id` mediumint(6) unsigned NOT NULL auto_increment,
  `orderid` mediumint(6) unsigned NOT NULL default '0',
  `itemid` mediumint(6) unsigned NOT NULL default '0',
  `quantity` bigint(10) unsigned NOT NULL default '0',
  `unitprice` double unsigned NOT NULL default '0',
  `cancelled` blob NOT NULL,
  PRIMARY KEY  (`id`),
  KEY itemid (itemid),
  FOREIGN KEY itemid_fk_1(itemid)
  REFERENCES module_vlabs_shoppingcart_store_inventory(id) ON DELETE CASCADE,
  KEY orderid (orderid),
  FOREIGN KEY orderid_fk_1(orderid)
  REFERENCES module_vlabs_shoppingcart_order(id) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4167 DEFAULT CHARSET=utf8 COMMENT='This table will contain the order description';");
/*
  Referential constraints for module_vlabs_shoppingcart_order_summary 

*/

		  eF_executeQuery("CREATE TABLE `module_vlabs_shoppingcart_log` (
  `id` mediumint(6) unsigned NOT NULL auto_increment,
  `description` varchar(300) NOT NULL default '0',
  `date` datetime NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='This table will contain the modification in the inventory';");

		  eF_executeQuery("CREATE TABLE `module_vlabs_shoppingcart_package_summary` (
  `id` mediumint(6) unsigned NOT NULL auto_increment,
  `packageid` mediumint(6) unsigned NOT NULL default '0',
  `itemid` mediumint(6) unsigned NOT NULL default '0',
  `quantity` bigint(10) unsigned NOT NULL default '0',
  `price` double unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY packageid (packageid),
  FOREIGN KEY packageid_fk_1(packageid)
  REFERENCES module_vlabs_shoppingcart_store_inventory(id) ON DELETE CASCADE,
  KEY itemid (itemid),
  FOREIGN KEY itemid_fk_2(itemid)
  REFERENCES module_vlabs_shoppingcart_store_inventory(id) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=242 DEFAULT CHARSET=utf8 COMMENT='This table will contain the packages items';");

		  ef_ExecuteQuery("CREATE TABLE `module_vlabs_shoppingcart_preassignment` (
  `id` varchar(20) NOT NULL default '',
  `courseid` bigint(10) unsigned NOT NULL default '0',
  `itemid` mediumint(6) unsigned NOT NULL default '0',
  `quantity` bigint(10) unsigned NOT NULL default '0',
  `assignmentdate` datetime NOT NULL,
  `lastmodification` datetime NOT NULL,
  `active` blob NOT NULL,
  PRIMARY KEY  (`id`),
  KEY itemid (itemid),
  FOREIGN KEY itemid_fk_3(itemid)
  REFERENCES module_vlabs_shoppingcart_store_inventory(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='This table will contain the assignments of items to courses';");

/* jh problem with the ending apostrophy
  	if (file_exists($dirname.'/'.$filename)){
	$output = shell_exec('mysql -u root -ppassword -D efront  <'.$dirname.'/'.$filename);
	//echo "mysqldump import output: " . $output;
	}

*/

	$output = shell_exec('mysql -u root -ppassword -D efront  </home/jhenao/development/vLabs/Code/WebSite/efront/www/modules/module_vlabs_shoppingcart_data.sql');

    	  return true;
 }   


//index(itemid),foreign key(itemid) references module_shoppingcart_store_inventory
 


 	/**
	 * Put any uninstallation operations here, usually deleting database tables
	 *
	 * @see libraries/EfrontModule#onUninstall()
	 */
    public function onUninstall() {

	 /*
	 echo '<script type="text/javascript">alert("dir and file name: '.$dirname.'/'.$filename.'")</script>';
	 if (file_exists($dirname)) {
		echo '<script type="text/javascript">alert("found directory'.$dirname.'")</script>';
    	if (file_exists($dirname.'/'.$filename)){
			echo '<script type="text/javascript">alert("found file'.$filename.'")</script>';
	 		unlink($dirname.'/'.$filename);
		}
		  $output = shell_exec('mysqldump -u root -ppassword --no-create-info efront module_vlabs_shoppingcart module_vlabs_shoppingcart_store_inventory module_vlabs_shoppingcart_order module_vlabs_shoppingcart_payment_method module_vlabs_shoppingcart_user_payment module_vlabs_shoppingcart_order_summary module_vlabs_shoppingcart_package_summary module_vlabs_shoppingcart_preassignment module_vlabs_shoppingcart_log >'. $dirname.'/'.$filename);

	} else {
    //directory does not exists skip data dump.
	}
   */

	 	$output = shell_exec('mysqldump -u root -ppassword --no-create-info efront module_vlabs_shoppingcart module_vlabs_shoppingcart_store_inventory module_vlabs_shoppingcart_order module_vlabs_shoppingcart_payment_method module_vlabs_shoppingcart_user_payment module_vlabs_shoppingcart_order_summary module_vlabs_shoppingcart_package_summary module_vlabs_shoppingcart_preassignment module_vlabs_shoppingcart_log >/home/jhenao/development/vLabs/Code/WebSite/efront/www/modules/module_vlabs_shoppingcart_data.sql');

        eF_executeQuery("drop table if exists module_vlabs_shoppingcart");
		  eF_executeQuery("drop table if exists module_vlabs_shoppingcart_payment_method");
		  eF_executeQuery("drop table if exists module_vlabs_shoppingcart_user_payment");
		  eF_executeQuery("drop table if exists module_vlabs_shoppingcart_order_summary");
		  eF_executeQuery("drop table if exists module_vlabs_shoppingcart_package_summary");
		  eF_executeQuery("drop table if exists module_vlabs_shoppingcart_preassignment");
	     eF_executeQuery("drop table if exists module_vlabs_shoppingcart_store_inventory");
		  eF_executeQuery("drop table if exists module_vlabs_shoppingcart_order");
		  eF_executeQuery("drop table if exists module_vlabs_shoppingcart_log");
    	return true;
    }

	/**
	 * Put any upgrade commands here, usually database table related
	 *
	 * @see libraries/EfrontModule#onUpgrade()
	 */
    public function onUpgrade() {
    	try {
	        eF_executeQuery("ALTER TABLE module_vlabs_shoppingcart change timestamp timestamp int(11) default 0");
    	} catch (Exception $e) {/*the table was already upgraded*/}

    	return true;
    }



    public function getModule() {
    	$smarty = $this -> getSmartyVar();
        $smarty -> assign("T_MODULE_BASEDIR" , $this -> moduleBaseDir);
        $smarty -> assign("T_MODULE_BASELINK" , $this -> moduleBaseLink);
        $smarty -> assign("T_MODULE_BASEURL" , $this -> moduleBaseUrl);
		  //$_SESSION['chatter'] = $currentUser -> login;
		  //$_SESSION['utype'] = $currentUser -> getType();
		  $currentUser = $this -> getCurrentUser();
 		  //jh original 7/5/2015 $currentRole = $currentUser -> getRole($this -> getCurrentLesson());
		  $currentRole =	$currentUser -> getType();
		  $currentUserId = $currentUser->login;
        //echo "current user is: ".$currentUser;
		  //echo '<script type="text/javascript>alert("current role is:")</script>';
		  $smarty -> assign("USERINFO", $currentUser);
		  $smarty -> assign("USERROLE", $currentRole);
		//?stu_id=$data[stu_id]&dept_id=$data[dept_id]'  currentUser=$currentUserId&currentRole=$currentRole
        

		$theme="";
		$tid = $_SESSION['s_theme'];
		//echo '<script type="text/javascript">alert("Current theme number is: ' . $tid . '")</script>';
		switch($tid){
			case '1':
			case '2':
			case '7':
				//default
 				$theme="default";	
				break;
			case '3':
				//green
				$theme="green";	
				break;
			case '4':
				//blue
				$theme="blue";	
				break;
			case '5':

			case '10':
				//bluehtml
				$theme="bluehtml";	
				break;
			case '6':
				//green
				$theme="green";	
				break;
			case '11':
				//flatgrey
				$theme="flatgrey";	
				break;
			default:
				$theme="default";	
				break;
		}

		  $smarty -> assign("FINALLY", $this -> moduleBaseLink ."saved.php?currentUser=$currentUserId&currentRole=$currentRole&currentTheme=$theme");

        return true;
    }

    /**
     * Specify which file to include for template
     *
	 * (non-PHPdoc)
	 * @see libraries/EfrontModule#getSmartyTpl()
     */
    public function getSmartyTpl() {
    	return $this -> moduleBaseDir."module_shoppingcart_page.tpl";
    }

    
    /**
	 * (non-PHPdoc)
	 * @see libraries/EfrontModule#getNavigationLinks()
     */
    public function getNavigationLinks() {
        return array (array ('title' => _HOME, 'link'  => $_SERVER['PHP_SELF']),
                      array ('title' => $this -> getName(), 'link'  => $this -> moduleBaseUrl));
    }

	//this will allow the module to appear in other places besides the admin level
    public function getToolsLinkInfo() {
	     return array('title' => $this -> getName(), // .' (getToolsLinkInfo())',
                     'image' => $this -> moduleBaseLink . 'img/logo.png',
                     'link'  => $this -> moduleBaseUrl);
    	
    }  




}
