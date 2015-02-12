<?php
/**
Plugin Name: map works
Plugin URI: http://www.ifyouknowit.com/
Description: This plugin will keep a database of entities and search them provide map on front end. want professional version check <a href="http://magento.ifyouknowit.com">Go pro</a>
Author: ifyouknowit.com
Version: 1.0
Author URI: http://www.ifyouknowit.com/

Donate link: http://www.ifyouknowit.com/
*/
class mapWorks{
  
      private $my_plugin_screen_name;
      private static $instance;
        
      static function GetInstance()
      {
          
          if (!isset(self::$instance))
          {
              self::$instance = new self();
          }
          return self::$instance;
      }
      
      public function PluginMenu()
      {
       $this->my_plugin_screen_name = add_menu_page(
                                        'MAP Works', 
                                        'MAP Works', 
                                        'manage_options',
                                        __FILE__, 
                                        array($this,'RenderPage')                                         
                                        );

	  add_submenu_page(__FILE__, 'Add entities', 'Add entities', 'manage_options', __FILE__.'/addentities',array($this,'fn_addscreen'));
      }


      public function mygeocode($address){

	$mybase_url = "https://maps.googleapis.com/maps/api/geocode/json?";
	$request_url = $mybase_url . "&address=" . urlencode(trim($address));
	if(extension_loaded("curl") && function_exists("curl_init")) {
		$cURL = curl_init();
		curl_setopt($cURL, CURLOPT_URL, $request_url);
		curl_setopt($cURL, CURLOPT_RETURNTRANSFER, 1);
		$resp_json = curl_exec($cURL);
		curl_close($cURL);  
	} else{
		$resp_json = file_get_contents($request_url) or die("url not loading");
	}
	

	    $resp = json_decode($resp_json, true); 
	    $status = $resp['status']; 
	    $lat = (!empty($resp['results'][0]['geometry']['location']['lat']))? $resp['results'][0]['geometry']['location']['lat'] : "" ;
	    $lng = (!empty($resp['results'][0]['geometry']['location']['lng']))? $resp['results'][0]['geometry']['location']['lng'] : "" ;

	      if(strcmp($status, "OK") == 0){		
		$geocode_pending = false;
		$lat = $resp['results'][0]['geometry']['location']['lat'];
		$lng = $resp['results'][0]['geometry']['location']['lng'];
	      } else {
		$lat='';
		$lng='';
	      }	

	       $info['lat']=$lat;
	       $info['lng']=$lng;

		return $info;
    
	} 	


	public function fn_addscreen(){
	 global $wpdb;

	 if(isset($_POST['eid'])){
		$eid = $_POST['eid'];
		$table_name = $wpdb->prefix . 'mapworks_tbl';
		$edits=$wpdb->get_results("SELECT * FROM ".$table_name." WHERE id = $eid",ARRAY_A);		
         }


		$did = $_POST['did'];
		

		if($did){
		$table_name = $wpdb->prefix . 'mapworks_tbl';
		$wpdb->delete($table_name,array('id'=>$did));
		$location=get_site_url()."/wp-admin/admin.php?page=mapworks/mapworks.php";
      		echo "<meta http-equiv='refresh' content='0;url=$location'/>";
                exit();		
		}

	if(isset($_POST['save'])){

	     $address=$_POST['street'].','.$_POST['city'].','.$_POST['state'].','.$_POST['country'];
   	     $info=$this->mygeocode($address);	

	     $table_name = $wpdb->prefix.'mapworks_tbl';

	     $eid = $_POST['eid'];
	        
	     
	    if($eid){
		$x=$wpdb->update( 
		$table_name, 
		array( 
			'time' => current_time('mysql'), 
			'yourname' => $_POST['yourname'], 
			'city' => $_POST['city'],
			'state' => $_POST['state'],
			'country' => $_POST['country'],
			'street' => $_POST['street'],
			'latitude' => $info['lat'],
			'longitude' => $info['lng'],    			
		), array('id'=>$eid)
         	);

		
		$location=get_site_url()."/wp-admin/admin.php?page=mapworks/mapworks.php";
      		echo "<meta http-equiv='refresh' content='0;url=$location'/>";
                exit();
	     } else { 	
	     $wpdb->insert( 
		$table_name, 
		array( 
			'time' => current_time('mysql'), 
			'yourname' => $_POST['yourname'], 
			'city' => $_POST['city'],
			'state' => $_POST['state'],
			'country' => $_POST['country'],
			'street' => $_POST['street'],
			'latitude' => $info['lat'],
			'longitude' => $info['lng'],  
		)
            );	
		$location=get_site_url()."/wp-admin/admin.php?page=mapworks/mapworks.php";
      		echo "<meta http-equiv='refresh' content='0;url=$location'/>";
	}
	} else { ?>
        <form method="post">	
        <div class='wrap'>
         <h2>Add locations</h2>
	 <div>Name</div>
	 <div><input name="yourname" id="yourname" value='<?php if($edits){print $edits[0]['yourname'];}?>'></div>	 	 	 
	 <div>Street</div>
	 <div><textarea id="street" name="street"><?php if($edits){print $edits[0]['street'];}?></textarea></div>
	 <div>City</div>
	 <div><input name="city" id="city" value="<?php if($edits){print $edits[0]['city'];}?>"></div>
	 <div>State</div>
	 <div><input name="state" id="state" value="<?php if($edits){print $edits[0]['state'];}?>"></div>
	 <div>Country</div>
	 <div><input name="country" id="country" value="<?php if($edits){print $edits[0]['country'];}?>"></div>
	<!--<div>Latitude</div>
	<div><input name="lat" id="lat" value="<?php if($edits){print $edits[0]['latitude'];}?>"></div>
	 <div>Longitude</div>
	 <div><input name="long" id="long" value="<?php if($edits){print $edits[0]['longitude'];}?>"></div> -->
         <input id="eid" type="hidden" name="eid" value="<?php print $eid; ?>">
	 <div><input name="save" id="save" type="submit" value="Save locations"></div>																																
	 </div>
	 </form>
      <?php
       }
      }	
      
      public function RenderPage(){
	global $wpdb;
	$table_name = $wpdb->prefix . 'mapworks_tbl';
	$results=$wpdb->get_results("SELECT * FROM ".$table_name." WHERE street LIKE '%$stext%'",ARRAY_A);
       ?>
       <script type="text/javascript">
	  jQuery(document).on("click",".edit",function(){
             var eid=jQuery(this).attr('id');
	     var form=jQuery('<form id="edit_forms" action="admin.php?page=mapworks/mapworks.php/addentities" method="post"></form>');
	     form.append('<input type="hidden" id="eid" name="eid" value="'+eid+'">');		             	    
	     jQuery('body').append(form);

	     jQuery('#edit_forms').submit();	
		
	  });


	  jQuery(document).on("click",".delete",function(){
             var eid=jQuery(this).attr('id');
	     var form=jQuery('<form id="delete_forms" action="admin.php?page=mapworks/mapworks.php/addentities" method="post"></form>');
	     form.append('<input type="hidden" id="did" name="did" value="'+eid+'">');		             	    
	     jQuery('body').append(form);

	     jQuery('#delete_forms').submit();	
		
	  });		
       </script>	

       <div class='wrap'>
        <h2>List of entities</h2>
        <table border="1" cellspacing="1" cellpadding="1" style="width:100%;">
	  <tr>
	     <td>Name</td><td>Street</td><td>City</td><td>State</td><td>Country</td><td>Lat</td><td>Long</td><td>Action</td>	
	  <tr>
	  <?php foreach($results as $rows){ ?>
	   <tr>
	     <td><?php print $rows['yourname'];?></td><td><?php print $rows['street'];?></td><td><?php print $rows['city'];?></td><td><?php print $rows['state'];?></td><td><?php print $rows['country'];?></td><td><?php print $rows['latitude'];?></td><td><?php print $rows['longitude'];?></td><td><a class="edit" id="<?php print $rows['id'];?>" href="javascript:void(0)">Edit</a> | <a class="delete" id="<?php print $rows['id'];?>" href="javascript:void(0)">Delete</a></td>	
	  <tr>
	 <?php } ?>		
	<table> 
       </div>
       <?php
      }

      public function InitPlugin()
      {
          add_action('admin_menu',array($this,'PluginMenu'));
      }

     
  
 }
 
$MyPlugin = mapWorks::GetInstance();
$MyPlugin->InitPlugin();



global $jal_db_version;
$jal_db_version = '1.0';

function mapworks_install() {
	global $wpdb;
	global $mapworks_db_version;

	$table_name = $wpdb->prefix . 'mapworks_tbl';
	
	$charset_collate = $wpdb->get_charset_collate();

	$sql = "CREATE TABLE $table_name (
		id mediumint(9) NOT NULL AUTO_INCREMENT,
		time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
		yourname tinytext NOT NULL,
		city varchar(55) DEFAULT '' NOT NULL,
		state varchar(55) DEFAULT '' NOT NULL,
		street text NOT NULL,
		country varchar(55) DEFAULT '' NOT NULL,
		latitude varchar(55) DEFAULT '' NOT NULL,
		longitude varchar(55) DEFAULT '' NOT NULL,
		UNIQUE KEY id (id)
	) $charset_collate;";

	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	dbDelta($sql);

	add_option('mapworks_db_version', $mapworks_db_version );



	$installed_ver = get_option("mapworks_db_version" );

	if ( $installed_ver != $mapworks_db_version ) {

		$table_name = $wpdb->prefix . 'mapworks_tbl';

		$sql = "CREATE TABLE $table_name (
		id mediumint(9) NOT NULL AUTO_INCREMENT,
		time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
		yourname tinytext NOT NULL,
		city varchar(55) DEFAULT '' NOT NULL,
		state varchar(55) DEFAULT '' NOT NULL,
		street text NOT NULL,
		country varchar(55) DEFAULT '' NOT NULL,
		latitude varchar(55) DEFAULT '' NOT NULL,
		longitude varchar(55) DEFAULT '' NOT NULL,
		UNIQUE KEY id (id)
		);";

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql );

		update_option("mapworks_db_version",$mapworks_db_version );
	}

}

function mapWorks_install_data() {
	global $wpdb;
	
	$welcome_name = 'Mr. WordPres';
	$welcome_text = 'Congratulations, you just completed the installation!';
        
	$yourname = 'Prakash';
	$city = 'Bangalore';
        $state = 'Karnatak';
	$country = 'India';
        $street = '8/63 URS Nilay,Celebrity Road,Doddatgor,Electronic City,Phase I'; 
	
	$table_name = $wpdb->prefix.'mapworks_tbl';
	
	$wpdb->insert( 
		$table_name, 
		array( 
			'time' => current_time('mysql'), 
			'yourname' => $welcome_name, 
			'city' => $city,
			'state' => $state,
			'country' => $country,
			'street' => $street,        
		) 
	);
}

register_activation_hook( __FILE__, 'mapWorks_install' );
register_activation_hook( __FILE__, 'mapWorks_install_data');


function mapworks($stext=''){
global $wpdb;
$table_name = $wpdb->prefix . 'mapworks_tbl';

if($stext!=''){
$results=$wpdb->get_results("SELECT * FROM ".$table_name." WHERE street LIKE '%$stext%'",ARRAY_A);
} else {
$results=$wpdb->get_results("SELECT * FROM ".$table_name,ARRAY_A);
}

foreach($results as $row){
?>
<style>
 .wrap_mapworks{
    margin-left:10px;	
 }
</style>
<div class='wrap_mapworks'>
<div>Name: <?php print $row['yourname'];?></div>
<div>Address:<br/><?php print $row['street'].'<br>'.$row['city'].'<br>'.$row['state'].'<br>'.$row['country'];?></div>
<div>Lat: <?php print $row['latitude'];?></div>
<div>Long: <?php print $row['longitude'];?></div>
<div style="width:300px;height:300px;border:1px solid red;margin:0 auto;" id="map-canvas_<?php print $row['id'];?>"></div>
<div id="pano_<?php print $row['id'];?>"></div>

<script>
function initialize_<?php print $row['id'];?>() {
  var fenway_<?php print $row['id'];?> = new google.maps.LatLng(<?php print $row['latitude'];?>, <?php print $row['longitude'];?>);
  var mapOptions_<?php print $row['id'];?> = {
    center: fenway_<?php print $row['id'];?>,
    zoom: 14
  };
  var map_<?php print $row['id'];?> = new google.maps.Map(
      document.getElementById('map-canvas_<?php print $row[id];?>'), mapOptions_<?php print $row['id'];?>);
  var panoramaOptions_<?php print $row['id'];?> = {
    position: fenway_<?php print $row['id'];?>,
    pov: {
      heading: 34,
      pitch: 10
    }
  };

   var panorama_<?php print $row['id'];?> = new google.maps.StreetViewPanorama(document.getElementById('pano_<?php print $row[id];?>'), panoramaOptions_<?php print $row['id'];?>);
   map_<?php print $row['id'];?>.setStreetView(panorama_<?php print $row['id'];?>);
}

// google.maps.event.addDomListener(window, 'load', initialize);
jQuery(document).ready(function(){
initialize_<?php print $row['id'];?>();
})
</script>
</div>
<?php
}
}   

function mapWorks_head_scripts(){
?>
<script src="https://maps.googleapis.com/maps/api/js?v=3.exp&signed_in=true"></script>
<?php
}
add_action('wp_head','mapWorks_head_scripts');


function mapworksshort($atts){

$atts = shortcode_atts(array(
		'stext' => ''
	),$atts,'mapworks');

$stext=$atts['stext'];

ob_start();

$options = get_option('plugin_options');

if(!$options['allow_default_front']){
  return '';
}


global $wpdb;
$table_name = $wpdb->prefix . 'mapworks_tbl';

if($stext!=''){
$results=$wpdb->get_results("SELECT * FROM ".$table_name." WHERE street LIKE '%$stext%'",ARRAY_A);
} else {
$results=$wpdb->get_results("SELECT * FROM ".$table_name,ARRAY_A);
}

foreach($results as $row){
?>
<style>
 .wrap_mapworks{
    margin-left:10px;	
 }
</style>
<div class='wrap_mapworks'>
<div>Name: <?php print $row['yourname'];?></div>
<div>Address:<br/><?php print $row['street'].'<br>'.$row['city'].'<br>'.$row['state'].'<br>'.$row['country'];?></div>
<div>Lat: <?php print $row['latitude'];?></div>
<div>Long: <?php print $row['longitude'];?></div>
<div style="width:300px;height:300px;border:1px solid red;margin:0 auto;" id="map-canvas_<?php print $row['id'];?>"></div>
<div id="pano_<?php print $row['id'];?>"></div>

<script>
function initialize_<?php print $row['id'];?>() {
  var fenway_<?php print $row['id'];?> = new google.maps.LatLng(<?php print $row['latitude'];?>, <?php print $row['longitude'];?>);
  var mapOptions_<?php print $row['id'];?> = {
    center: fenway_<?php print $row['id'];?>,
    zoom: 14
  };
  var map_<?php print $row['id'];?> = new google.maps.Map(
      document.getElementById('map-canvas_<?php print $row[id];?>'), mapOptions_<?php print $row['id'];?>);
  var panoramaOptions_<?php print $row['id'];?> = {
    position: fenway_<?php print $row['id'];?>,
    pov: {
      heading: 34,
      pitch: 10
    }
  };

   var panorama_<?php print $row['id'];?> = new google.maps.StreetViewPanorama(document.getElementById('pano_<?php print $row[id];?>'), panoramaOptions_<?php print $row['id'];?>);
   map_<?php print $row['id'];?>.setStreetView(panorama_<?php print $row['id'];?>);
}

// google.maps.event.addDomListener(window, 'load', initialize);
jQuery(document).ready(function(){
initialize_<?php print $row['id'];?>();
})
</script>
</div>
<?php
}

$out2 = ob_get_contents();

ob_end_clean();
return $out2; 

}

add_shortcode('mapworks', 'mapworksshort');
?>
