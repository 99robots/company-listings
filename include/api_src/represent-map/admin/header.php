<?php
include STARTUP_DIRECTORY_PLUGIN_DIR . "/include/api_src/represent-map/include/db.php";
// get task
if(isset($_GET['task'])) { $task = $_GET['task']; } 
else if(isset($_POST['task'])) { $task = $_POST['task']; }

// get view
if(isset($_GET['view'])) { $view = $_GET['view']; } 
else if(isset($_POST['view'])) { $view = $_POST['view']; }
else { $view = ""; }

// get page
if(isset($_GET['p'])) { $p = $_GET['p']; } 
else if(isset($_POST['p'])) { $p = $_POST['p']; }
else { $p = 1; }

// get search
if(isset($_GET['search'])) { $search = $_GET['search']; } 
else if(isset($_POST['search'])) { $search = $_POST['search']; }
else { $search = ""; }

// make sure admin is logged in
if(isset($page) && $page != "login") {
  if(isset($_COOKIE["representmap_user"]) && ($_COOKIE["representmap_user"] != crypt($admin_user, $admin_user) || $_COOKIE["representmap_pass"] != crypt($admin_pass, $admin_pass))) {
    include plugin_dir_path( __FILE__ ) . "login.php";
    //header("Location: login.php");
    exit;
  }
}

// connect to db
mysqli_connect($db_host, $db_user, $db_pass) or die(mysql_error());
mysql_select_db($db_name) or die(mysql_error());

// get marker totals
$total_approved = mysql_num_rows(mysql_query("SELECT id FROM places WHERE approved='1'"));
$total_rejected = mysql_num_rows(mysql_query("SELECT id FROM places WHERE approved='0'"));
$total_pending = mysql_num_rows(mysql_query("SELECT id FROM places WHERE approved IS null"));
$total_all = mysql_num_rows(mysql_query("SELECT id FROM places"));

// admin header
$admin_head = "
  <html>
  <head>
    <title>RepresentMap Admin</title>
    <link href='" . STARTUP_DIRECTORY_PLUGIN_URL . "/include/api_src/represent-map/bootstrap/css/bootstrap.css' rel='stylesheet' type='text/css' />
    <link href='" . STARTUP_DIRECTORY_PLUGIN_URL . "/include/api_src/represent-map/bootstrap/css/bootstrap-responsive.css' rel='stylesheet' type='text/css' />
    <link rel='stylesheet' href='" . plugins_url('admin.css', __FILE__) . "' type='text/css' />
    <script src='" . STARTUP_DIRECTORY_PLUGIN_URL . "/include/api_src/represent-map/bootstrap/js/bootstrap.js' type='text/javascript' charset='utf-8'></script>
    <script src='" . STARTUP_DIRECTORY_PLUGIN_URL . "/include/api_src/represent-map/scripts/jquery-1.7.1.js' type='text/javascript' charset='utf-8'></script>
    <script src='https://maps.googleapis.com/maps/api/js?v=3.exp&sensor=false' type='text/javascript' charset='utf-8'></script>
  </head>
  <body>
";
if($page != "login") {
  $admin_head .= "
    <div class='navbar navbar-fixed-top'>
      <div class='navbar-inner'>
        <div class='container'>
          <a class='brand' href='index.php'>
            RepresentMap
          </a>
          <ul class='nav'>
            <li"; if($view == "") { $admin_head .= " class='active'"; } $admin_head .= ">
              <a href='" . get_permalink() . "'>All Listings</a>
            </li>
            <li"; if($view == "approved") { $admin_head .= " class='active'"; } $admin_head .= ">
              <a href='" . get_permalink() . "&view=approved'>
                Approved
                <span class='badge badge-info'>$total_approved</span>
              </a>
            </li>
            <li"; if($view == "pending") { $admin_head .= " class='active'"; } $admin_head .= ">
              <a href='" . get_permalink() . "&view=pending'>
                Pending
                <span class='badge badge-info'>$total_pending</span>
              </a>
            </li>
            <li"; if($view == "rejected") { $admin_head .= " class='active'"; } $admin_head .= ">
              <a href='" . get_permalink() . "&view=rejected'>
                Rejected
                <span class='badge badge-info'>$total_rejected</span>
              </a>
            </li>
          </ul>
          <form class='navbar-search pull-left' method='get'>
          	<input type='hidden' name='page_id' value='" . get_the_ID() . "'/>
            <input type='text' name='search' class='search-query' placeholder='Search' autocomplete='off' value='$search'>
          </form>
          <ul class='nav pull-right'>
            <li><a href='" . get_permalink() . "&startup_page=login&task=logout'>Sign Out</a></li>
          </ul>
        </div>
      </div>
    </div>
  ";
}
$admin_head .= "
  <div id='content'>
";


// if startup genome enabled, show message here
if($sg_enabled) {
  $admin_head .= "
    <div class='alert alert-info'>
      Note: You have Startup Genome integration enabled in your config file (/include/db.php).
      If you want to make changes to the markers on your map, please do so from the 
      <a href='http://www.startupgenome.com'>Startup Genome website</a>. Any changes
      you make here may not persist on your map unless you turn off Startup Genome mode.
    </div>
  ";  
}




// admin footer 
$admin_foot = "
    </div>
  </body>
</html>
";




?>