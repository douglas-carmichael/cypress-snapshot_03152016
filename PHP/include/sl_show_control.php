<?php

function start_show($our_handler, $region_name)
{

    /* Prepare our insert query to the table */
    $audit_statement = $our_handler->prepare('INSERT INTO show_table 
        							(region_name) VALUES (?) RETURNING show_id,
        							file_name');

    /* Execute it */
    $audit_statement->execute(array(
        $region_name
    ));
    
    /* Did we fetch the show ID properly? */
    $audit_columns = $audit_statement->columnCount();
    if ($audit_columns > 0)
    {
    	if ($audit_columns == 2)
    	{
		$result = $audit_statement->fetch(PDO::FETCH_ASSOC);
		return $result['show_id'] . ',' . $result['file_name'];
        }
    }
    else
    {
        return FALSE;
    }
}

function check_show_id($show_id)
{
	/* Check the length of the link ID */
	$our_length = strlen($show_id);
	
	if ($our_length > 8) 
	{
		return FALSE;
	}
	
	/* Check for valid characters */
	if (preg_match("#[^0-9]#", $show_id))
	{
		return FALSE;
	}
	
	return TRUE;	
}

function display_console($our_string)
{
	$our_tty = trim(`tty`);
	file_put_contents($our_tty, $our_string);
	
}

function process_file($our_handler, $show_id, $file_name)
{
	/* Find the purchasers of a specific show */
	$purchaser_array = get_purchasers($our_handler, $show_id);
	
	if ($purchaser_array != FALSE)
	{
		foreach($purchaser_array as $purchaser_uuid)
		{				
			$my_linkid = get_linkid($our_handler, $purchaser_uuid);
			$my_string = "Thank you for your purchase!\n" .
			"Your download is now ready, please enjoy.\n" .
			DOWNLOAD_BASE . "?link_id=" . $my_linkid;
			send_avatar_im($our_handler, $purchaser_uuid, $my_string, false);
		}
	}
	else
	{
		die("purchaser_array not found.");
	}
}

function get_linkid($our_handler, $avatar_uuid)
{
	$show_statement = $our_handler->prepare('SELECT link_id FROM purchase_table WHERE
		avatar_uuid = ?');
	$show_statement->execute(array($avatar_uuid));

    /* Did we fetch the link properly? */
    $link_columns = $show_statement->columnCount();
    if ($link_columns > 0)
    {
    	if ($link_columns == 1)
    	{
		$result = $show_statement->fetch(PDO::FETCH_ASSOC);
		return $result['link_id'];
        }
    }
    else
    {
        return FALSE;
    }

}

function name_show($our_handler, $show_id, $new_name)
{
	$show_statement = $our_handler->prepare('SELECT update_filename(?, ?)');
	$show_statement->execute(array($show_id, $new_name));
	
	/* Check if a row has been returned to gauge the success of the update */
	$name_columns = $show_statement->columnCount();
	if ($name_columns > 0)
	{
		if ($name_columns == 1)
		{
			$result = $show_statement->fetch(PDO::FETCH_ASSOC);
			return $result['update_filename'];
		}
	}
	else
	{
		return FALSE;
	}	
}

function reset_filename($our_handler, $show_id)
{
	$show_statement = $our_handler->prepare('SELECT reset_filename(?)');
	$show_statement->execute(array($show_id));
	
	/* Check if a row has been returned to gauge the success of the update */
	$name_columns = $show_statement->columnCount();
	if ($name_columns > 0)
	{
		if ($name_columns == 1)
		{
			$result = $show_statement->fetch(PDO::FETCH_ASSOC);
			return $result['reset_filename'];
		}
	}
	else
	{
		return FALSE;
	}	
}

function delete_file($file_name)
{
	$true_name = UPLOAD_BASE . $file_name;
	if (file_exists($true_name) === FALSE)
	{
		return FALSE;
	}
	if (!unlink($true_name))
	{
		return FALSE;
	}
	return TRUE;
}

function get_filename($our_handler, $show_id)
{
	$show_statement = $our_handler->prepare('SELECT get_filename(?)');
	$show_statement->execute(array($show_id));
	
	/* Check if a row has been returned to gauge the success of the update */
	$name_columns = $show_statement->columnCount();
	if ($name_columns > 0)
	{
		if ($name_columns == 1)
		{
			$result = $show_statement->fetch(PDO::FETCH_ASSOC);
			return $result['get_filename'];
		}
	}
	else
	{
		return FALSE;
	}	
}

function get_purchasers($our_handler, $show_id)
{
    $show_statement = $our_handler->prepare('SELECT array_to_json(get_purchasers (?))');
    $show_statement->execute(array(
        $show_id
    ));
    
    /* Did we fetch it properly? */
    $show_columns = $show_statement->columnCount();
    if ($show_columns > 0)
    {
        $purchasers_json = $show_statement->fetchColumn();
		$purchaser_array = json_decode($purchasers_json, true);
		if ($purchaser_array != NULL)
		{
			return $purchaser_array;
		}
    }
    else
    {
        return FALSE;
    }
}

function check_purchased($our_handler, $show_id, $our_uuid)
{
    $show_statement = $our_handler->prepare('SELECT check_purchased (?, ?)');
    $show_statement->execute(array(
        $show_id,
        $our_uuid
    ));
    
    /* Did we fetch it properly? */
    $show_columns = $show_statement->columnCount();
    if ($show_columns > 0)
    {
        $purchase_result = $show_statement->fetchColumn();
        if ($purchase_result == "t")
        {
            return TRUE;
        }
    }
    else
    {
        return FALSE;
    }
}


function execute_purchase($our_handler, $show_id, $avatar_uuid)
{
    
    /* Update the database */
    
    $show_statement = $our_handler->prepare('INSERT INTO purchase_table (show_id, avatar_uuid)
    VALUES (?, ?)');
    $execute_var    = $show_statement->execute(array(
        $show_id,
        $avatar_uuid
    ));
    
    if ($execute_var == TRUE)
    {
        $my_name = get_avatar_name($our_handler, $avatar_uuid);
        send_avatar_im($our_handler, $avatar_uuid, "Thank you for buying a show, " . $my_name . "!", true);
        return TRUE;
    }
    else
    {
        send_avatar_im($our_handler, $avatar_uuid, "Show purchase failure.", FALSE);
    }
}

function buy_show($our_handler, $show_id, $avatar_uuid)
{
    
    $our_handler->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    /* Check to see if this avatar purchased this show */
    if (check_purchased($our_handler, $show_id, $avatar_uuid) == TRUE)
    {
        return FALSE;
    }

    execute_purchase($our_handler, $show_id, $avatar_uuid);
    return TRUE;
}

?>