<?php
	require_once('include/db_connect.php');
	require_once('include/sl_im_control.php');
	error_reporting(E_ALL);
	ini_set('display_errors', '1');
	$my_id = '71610693-8e53-4f95-9ea5-82c7ec435d4b';
	$our_secret = $shared_secret;
	$my_name = get_avatar_name($ourDBHandler, $my_id, $our_secret);
	print $my_name;
?>
