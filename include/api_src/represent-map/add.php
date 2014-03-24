<?php
include_once plugin_dir_path( __FILE__ ) . "header.php";

// This is used to submit new markers for review.
// Markers won't appear on the map until they are approved.

$owner_name = mysql_real_escape_string(parseInput($_GET['owner_name']));
$owner_email = mysql_real_escape_string(parseInput($_GET['owner_email']));
$title = mysql_real_escape_string(parseInput($_GET['title']));
$type = mysql_real_escape_string(parseInput($_GET['type']));
$address = mysql_real_escape_string(parseInput($_GET['address']));
$uri = mysql_real_escape_string(parseInput($_GET['uri']));
$description = mysql_real_escape_string(parseInput($_GET['description']));

// validate fields
if(empty($title) || empty($type) || empty($address) || empty($uri) || empty($description) || empty($owner_name) || empty($owner_email)) {
  echo "All fields are required - please try again.";
  exit;
  
} else {
  
  
  
  // if startup genome mode enabled, post new data to API
  if($sg_enabled) {
    
    try {
      @$r = $http->doPost("/organization", $_POST);
      $response = json_decode($r, 1);
      if ($response['response'] == 'success') {
        //include_once(plugin_dir_path( __FILE__ ) . "startupgenome_get.php");
        //echo "success"; 
        //exit;
        ?> 
	  <script>
	  	window.location = "<?php echo get_permalink(); ?>";
	  </script>
	  <?php
      }
    } catch (Exception $e) {
      echo "<pre>";
      print_r($e);
    }
    
    
  // normal mode enabled, save new data to local db
  } else {

    // insert into db, wait for approval
    $insert = mysql_query("INSERT INTO places (approved, title, type, address, uri, description, owner_name, owner_email) VALUES (null, '$title', '$type', '$address', '$uri', '$description', '$owner_name', '$owner_email')") or die(mysql_error());

    // geocode new submission
    $hide_geocode_output = true;
    //include plugin_dir_path( __FILE__ ) . "geocode.php";
    
    //echo "success";
    //exit;
	?> 
	  <script>
	  	window.location = "<?php echo get_permalink(); ?>";
	  </script>
	  <?php
  }

  
}


?>
