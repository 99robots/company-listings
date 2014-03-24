<?php

	
// Include File based off of given input

if (isset($_GET['startup_page']) && $_GET['startup_page'] == 'edit') {
	include_once(plugin_dir_path( __FILE__ ) . 'edit.php');
	$_REQUEST['place_id'] = isset($_GET['place_id']) ? $_GET['place_id'] : '';
	$_REQUEST['view'] = isset($_GET['view']) ? $_GET['view'] : '';
	$_REQUEST['search'] = isset($_GET['search']) ? $_GET['search'] : '';
	$_REQUEST['p'] = isset($_GET['p']) ? $_GET['p'] : '';
}

else if (isset($_GET['startup_page']) && $_GET['startup_page'] == 'login') {
	include_once(plugin_dir_path( __FILE__ ) . 'login.php');
	$_REQUEST['task'] = isset($_GET['task']) ? $_GET['task'] : '';
}

else if (isset($_GET['task']) && $_GET['task'] == 'doedit') {
	include_once(plugin_dir_path( __FILE__ ) . 'edit.php');
	$_REQUEST['title'] = isset($_GET['title']) ? $_GET['title'] : '';
	$_REQUEST['type'] = isset($_GET['type']) ? $_GET['type'] : '';
	$_REQUEST['address'] = isset($_GET['address']) ? $_GET['address'] : '';
	$_REQUEST['uri'] = isset($_GET['uri']) ? $_GET['uri'] : '';
	$_REQUEST['description'] = isset($_GET['description']) ? $_GET['description'] : '';
	$_REQUEST['owner_name'] = isset($_GET['owner_name']) ? $_GET['owner_name'] : '';
	$_REQUEST['owner_email'] = isset($_GET['owner_email']) ? $_GET['owner_email'] : '';
	$_REQUEST['lat'] = isset($_GET['lat']) ? $_GET['lat'] : '';
	$_REQUEST['lng'] = isset($_GET['lng']) ? $_GET['lng'] : '';
}

else if (isset($_GET['task']) && $_GET['task'] == 'dologin') {
	include_once(plugin_dir_path( __FILE__ ) . 'login.php');
	$_REQUEST['user'] = isset($_GET['user']) ? $_GET['user'] : '';
	$_REQUEST['pass'] = isset($_GET['pass']) ? $_GET['pass'] : '';
}

else {
	$page = "index";
	include plugin_dir_path( __FILE__ ) . "header.php";
	
	if (!isset($task) || empty($task))
		$task = "";
	
	// hide marker on map
	if($task == "hide") {
	  $place_id = htmlspecialchars($_GET['place_id']);
	  mysql_query("UPDATE places SET approved=0 WHERE id='$place_id'") or die(mysql_error());
	  ?> 
	  <script>
	  	window.location = "<?php echo get_permalink() . "&view=$view&search=$search&p=$p"; ?>";
	  </script>
	  <?php
	  //include plugin_dir_path( __FILE__ ) . "index.php?view=$view&search=$search&p=$p";
	  //header("Location: index.php?view=$view&search=$search&p=$p");
	  //exit;
	}
	
	// show marker on map
	if($task == "approve") {
	  $place_id = htmlspecialchars($_GET['place_id']);
	  mysql_query("UPDATE places SET approved=1 WHERE id='$place_id'") or die(mysql_error());
	  ?> 
	  <script>
	  	window.location = "<?php echo get_permalink() . "&view=$view&search=$search&p=$p"; ?>";
	  </script>
	  <?php
	  //include plugin_dir_path( __FILE__ ) . "index.php?view=$view&search=$search&p=$p";
	  //header("Location: index.php?view=$view&search=$search&p=$p");
	  //exit;
	}
	
	// completely delete marker from map
	if($task == "delete") {
	  $place_id = htmlspecialchars($_GET['place_id']);
	  mysql_query("DELETE FROM places WHERE id='$place_id'") or die(mysql_error());
	  ?> 
	  <script>
	  	window.location = "<?php echo get_permalink() . "&view=$view&search=$search&p=$p"; ?>";
	  </script>
	  <?php
	  //include plugin_dir_path( __FILE__ ) . "index.php?view=$view&search=$search&p=$p";
	  //header("Location: index.php?view=$view&search=$search&p=$p");
	  //exit;
	}
	
	// paginate
	$items_per_page = 15;
	$page_start = ($p-1) * $items_per_page;
	$page_end = $page_start + $items_per_page;
	
	// get results
	if($view == "approved") {
	  $places = mysql_query("SELECT * FROM places WHERE approved='1' ORDER BY title LIMIT $page_start, $items_per_page");
	  $total = $total_approved;
	} else if($view == "rejected") {
	  $places = mysql_query("SELECT * FROM places WHERE approved='0' ORDER BY title LIMIT $page_start, $items_per_page");
	  $total = $total_rejected;
	} else if($view == "pending") {
	  $places = mysql_query("SELECT * FROM places WHERE approved IS null ORDER BY id DESC LIMIT $page_start, $items_per_page");
	  $total = $total_pending;
	} else if($view == "") {
	  $places = mysql_query("SELECT * FROM places ORDER BY title LIMIT $page_start, $items_per_page");
	  $total = $total_all;
	}
	if($search != "") {
	  $places = mysql_query("SELECT * FROM places WHERE title LIKE '%$search%' ORDER BY title LIMIT $page_start, $items_per_page");
	  $total = mysql_num_rows(mysql_query("SELECT id FROM places WHERE title LIKE '%$search%'")); 
	}
	
	echo $admin_head;
	?>
	
	
	<div id="admin">
	  <h3>
	    <? if($total > $items_per_page) { ?>
	      <?=$page_start+1?>-<? if($page_end > $total) { echo $total; } else { echo $page_end; } ?>
	      of <?=$total?> markers
	    <? } else { ?>
	      <?=$total?> markers
	    <? } ?>
	  </h3>
	  <ul>
	    <?
	      while($place = mysql_fetch_assoc($places)) {
	        $place['uri'] = str_replace("http://", "", $place['uri']);
	        $place['uri'] = str_replace("https://", "", $place['uri']);
	        $place['uri'] = str_replace("www.", "", $place['uri']);
	        echo "
	          <li>
	            <div class='options'>
	              <a class='btn btn-small' href='" . get_permalink() . '&startup_page=edit' . "&place_id=" . $place['id'] . "&view=$view&search=$search&p=$p" . "'>Edit</a>
				  ";
	              if($place['approved'] == 1) {
	                echo "
	                  <a class='btn btn-small btn-success disabled'>Approve</a>
	                  <a class='btn btn-small btn-inverse' href='" . get_permalink() . "&task=hide&place_id=" . $place['id'] . "&view=$view&search=$search&p=$p" . "'>Reject</a>
	                ";
	              } else if(is_null($place['approved'])) {
	                echo "
	                  <a class='btn btn-small btn-success' href='" . get_permalink() . "&task=approve&place_id=" . $place['id'] . "&view=$view&search=$search&p=$p" . "'>Approve</a>
	                  <a class='btn btn-small btn-inverse' href='" . get_permalink() . "&task=hide&place_id=" . $place['id'] . "&view=$view&search=$search&p=$p" . "'>Reject</a>
	                ";
	              } else if($place['approved'] == 0) {
	                echo "
	                  <a class='btn btn-small btn-success' href='" . get_permalink() . "&task=approve&place_id=" . $place['id'] . "&view=$view&search=$search&p=$p" . "'>Approve</a>
	                  <a class='btn btn-small btn-inverse disabled'>Reject</a>
	                ";
	              }
	              echo "
	              <a class='btn btn-small btn-danger' href='" . get_permalink() . "&task=delete&place_id=" . $place['id'] . "&view=$view&search=$search&p=$p" . "'>Delete</a>
	            </div>
	            <div class='place_info'>
	              <a href='http://$place[uri]' target='_blank'>
	                $place[title]
	                <span class='url'>
	                  $place[uri]
	                </span>
	              </a>
	            </div>
	          </li>
	        ";
	      }
	    ?>
	  </ul>
	  
	  <? if($p > 1 || $total >= $items_per_page) { ?>
	    <ul class="pager">
	      <? if($p > 1) { ?>
	        <li class="previous">
	          <a href="<?php echo get_permalink(); ?>&view=<?=$view?>&search=<?=$search?>&p=<? echo $p-1; ?>">&larr; Previous</a>
	        </li>
	      <? } ?>
	      <? if($total >= $items_per_page * $p) { ?>
	        <li class="next">
	          <a href="<?php echo get_permalink(); ?>&view=<?=$view?>&search=<?=$search?>&p=<? echo $p+1; ?>">Next &rarr;</a>
	        </li>
	      <? } ?>
	    </ul>
	  <? } ?>
	
	</div><?php
}


echo $admin_foot ?>