<?php
	require_once('include/config.php');
        require_once('include/db_connect.php');
        require_once('include/sl_im_control.php');
        require_once('include/sl_show_control.php');
        require_once('include/sl_money_control.php');
        require_once('include/error_control.php');
		error_reporting(E_ALL);
		ini_set('display_errors', '1');
			/* Check if this is a Linden Lab server */
			$my_sourceaddr = $_SERVER['REMOTE_ADDR'];
			$my_sourcename = gethostbyaddr($my_sourceaddr);
			if (!preg_match("/lindenlab.com/", $my_sourcename))
			{
				die("Invalid source address!");
			}
			

			if (!isset($_POST['action']))
			{
				die("No action specified!");
			}
			
			$our_action = $_POST['action'];
			switch ($our_action)
			{
				case "set_url":		
					if(!isset($_POST['our_url']))
					{
						die("No URL specified!");
					}
                       	$my_url = $_POST['our_url'];
                        send_sim_url($ourDBHandler, $my_url);
                        send_avatar_im($ourDBHandler, DEBUG_UUID, "URL setting received.", false);        				
            		break;
            	case "start_show":
            	{
            		if(!isset($_POST['my_region']))
            		{
            			die("No region specified!");
            		}
            			$my_region = urldecode($_POST['my_region']);
            			$show_id = start_show($ourDBHandler, $my_region);
				if ($show_id != FALSE)
				  {
				    echo $show_id;
				    break;
				  }
				else
				  {
				    die("Internal error: could not find show ID");
				    break;
				  }
            	}
            	case "buy_show":
            	{
            		if (!isset($_POST['my_uuid']))
            		{
            			die("No avatar UUID specified!");
            		}
					if (!isset($_POST['show_id']))
					{
						die("No show ID specified!");
					}
         			$my_uuid = $_POST['my_uuid'];
					$show_id = $_POST['show_id'];
	            	if (check_show_id($show_id) === FALSE)
	            	{
	            		die("Invalid show ID!");
	            	}
					$my_show = buy_show($ourDBHandler, $show_id, $my_uuid);
					if ($my_show == FALSE)
					{
					send_avatar_im($ourDBHandler, $my_uuid,
						"You have already purchased this show.", false);
					refund_avatar($ourDBHandler, $my_uuid);
					}
					break;
	            }
	            case "name_show":
	            {
	            	if (!isset($_POST['show_id']))
	            	{
	            		die("No show ID specified!");
	            	}
	            	if (!isset($_POST['show_name']))
	            	{
	            		die("No show name specified!");
	            	}
	            	$show_id = $_POST['show_id'];
	            	$show_name = $_POST['show_name'];
	            	if (check_show_id($show_id) === FALSE)
	            	{
	            		die("Invalid show ID!");
	            	}
	            	$name_success = name_show($ourDBHandler, $show_id, $show_name);
	            	if ($name_success === FALSE)
	            	{
	            		die("Error naming show!");
	            	}
	        		echo $name_success;
	            	break;
	            }
	            case "reset_name":
	            {
	            	if (!isset($_POST['show_id']))
	            	{
	            		die("No show ID specified!");
	            	}
	            	$show_id = $_POST['show_id'];
	            	if (check_show_id($show_id) === FALSE)
	            	{
	            		die("Invalid show ID!");
	            	}
					$reset_success = reset_filename($ourDBHandler, $show_id);
					if ($reset_success === FALSE)
					{
						die("Error resetting name of show!");
					}
					echo $reset_success;
					break;
				}
	            case "get_filename":
	            {
	            	if (!isset($_POST['show_id']))
	            	{
	            		die("No show ID specified!");
	            	}
	            	$show_id = $_POST['show_id'];
	            	if (check_show_id($show_id) === FALSE)
	            	{
	            		die("Invalid show ID!");
	            	}
	            	$show_name = get_filename($ourDBHandler, $show_id);
	            	if ($show_name === FALSE)
	            	{
	            		die("Error receiving show name!");
	            	}
	            	echo $show_name;
	            	break;
				}	            
	            case "process_file":
	            {
	            	if(!isset($_POST['my_file']))
	            	{
	            		die("No file name specified!");
	            	}
					if (!isset($_POST['show_id']))
					{
						die("No show ID specified!");
					}
	            	$our_file = $_POST['my_file'];
	            	$show_id = $_POST['show_id'];
	            	if (check_show_id($show_id) === FALSE)
	            	{
	            		die("Invalid show ID!");
	            	}
	            	process_file($ourDBHandler, $show_id, $our_file);
	            	break;
	            }
	            case "delete_file":
	            {
	            	if(!isset($_POST['my_file']))
	            	{
	            		die("No file name specified!");
	            	}
	            	$our_file = $_POST['my_file'];
	            	$delete_file = delete_file($our_file);
	            	if($delete_file == FALSE)
	            	{
	            		echo "delete_error";
	            		die("Error deleting file " + $our_file + "!");
	            	}
	            	else
	            	{
	            		echo $our_file;
	            	}
	            	break;
	            }
	            	break;
            	}
        $ourDBHandler = null;
?>