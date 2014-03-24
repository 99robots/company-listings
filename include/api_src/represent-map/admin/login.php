<?php
$page = "login";
include plugin_dir_path( __FILE__ ) . "header.php";

$is_loggedin = false;
$alert = "";

// logout
if($task == "logout") {
  setcookie("representmap_user", "", time()+3600000);
  setcookie("representmap_pass", "", time()+3600000);
  //include plugin_dir_path( __FILE__ ) . "login.php";
  //header("Location: login.php");
  //exit;
  
  ?> 
  <script>
  	window.location = "<?php echo get_permalink(); ?>";
  </script>
  <?php
}

// attempt login
if($_GET['task'] == "dologin") {
  $input_user = htmlspecialchars($_GET['user']);
  $input_pass = htmlspecialchars($_GET['pass']);
  if(trim($input_user) == "" || trim($input_pass) == "") {
    $alert = "Nope. Wanna try that again?";
  } else {
    if(crypt($input_user, $admin_user) == crypt($admin_user, $admin_user) && crypt($input_pass, $admin_pass) == crypt($admin_pass,$admin_pass)) {
      setcookie("representmap_user", crypt($input_user, $admin_user), time()+3600000);
      setcookie("representmap_pass", crypt($input_pass, $admin_pass), time()+3600000);
      //include plugin_dir_path( __FILE__ ) . "index.php";
      //header("Location: index.php");
      //exit;
      
      ?> 
	  <script>
	  	window.location = "<?php echo get_permalink(); ?>";
	  </script>
	  <?php
    } else {
      $alert = "The information you provided was invalid. :(";
    }
  }
}

?>






<? echo $admin_head; ?>

<form class="well form-inline" id="login" method="GET">
	<input type="hidden" name="page_id" value="<?php echo get_the_ID(); ?>" />
  <h1>
    RepresentMap Admin
  </h1>
  <?
    if($alert != "") {
      echo "
        <div class='alert alert-danger'>
          $alert
        </div>
      ";
    }
  ?>
  <input type="text" name="user" class="input-large" placeholder="Username">
  <input type="password" name="pass" class="input-large" placeholder="Password">
  <button type="submit" class="btn btn-info">Sign in</button>
  <input type="hidden" name="task" value="dologin" />
</form>

<? echo $admin_foot; ?>