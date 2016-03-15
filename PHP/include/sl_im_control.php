<?php
		
	    function send_sim_url($our_handler, $our_url)
        {
        		/* send_sim_url: Send the current URL from
        			the Second Life simulator to our database. */

        		/* Delete from the URL singleton table
        			(there should be only one URL in it) */
        		$url_statement = $our_handler->prepare('DELETE FROM avatar_url');
        		$url_statement->execute();
        				
        		/* Insert the current URL in the table */
                $url_statement = $our_handler->prepare('INSERT INTO avatar_url 
                        								(our_url) VALUES (?)');
				$url_statement->execute(array($our_url));			
        }
        
        function get_sim_url($our_handler)
        {
        		/* get_sim_url: Get the current simulator URL
        			from the singleton table. */
        		
        		/* Query the table for the URL */
        		$url_statement = $our_handler->prepare('SELECT our_url FROM avatar_url');
        		$url_statement->execute();
        		
        		/* Fetch our one column and return it */
        		$our_url = $url_statement->fetchColumn();
        		return $our_url;
        }
        
        function send_audit($our_handler, $avatar_id, $avatar_name)
        {
        		/* send_audit: Send the record of an IM to the
        			auditing table in the database. */
        		
        		/* Prepare our insert query to the table */
        		$audit_statement = $our_handler->prepare('INSERT INTO avatar_im_log 
        								(avatar_uuid, avatar_name) VALUES (?, ?)');
        								
        		/* Execute it */
        		$audit_statement->execute(array($avatar_id, $avatar_name));
        }
        
        function get_avatar_name($our_handler, $avatar_id)
        {
        
        		/* get_avatar_name: Get the name of an avatar from the UUID */
        		
        		$our_url = get_sim_url($our_handler);
        		$fields = array('id' => $avatar_id, 'message' => 'null',
        						'action' => 'name_lookup', 'secret' => SHARED_SECRET);
	        	$options = array('timeout' => 600);

				/* Let's now try to get the information from SL */
        		$response = http_post_fields($our_url, $fields, NULL, $options);
        		if (sizeof($response) < 2)
        		{
        			$response = http_post_fields($our_url, $fields, NULL, $options);
        		}
        		        		
				list($header, $body) = preg_split("/\R\R/", $response, 2);
				
				/* Substitute an error message if the SL dataserver won't respond */
				if (preg_match("/cap not found/", $body))
				{
					$body = "(name not available)";
				}
				
        		return $body;
        
        }
        
        function send_avatar_im($our_handler, $avatar_id, $message,
        						$audit_flag)
        {
        	/* send_avatar_im: Send an avatar an IM */
        	
        	$our_url = get_sim_url($our_handler);
        	$fields = array('id' => $avatar_id, 'message' => $message, 
        					'action' => 'send_im', 'secret' => SHARED_SECRET);
        	$options = array('timeout' => 600);
        	
 			/* Send the IM */    	
        	$response = http_post_fields($our_url, $fields, NULL, $options);

			/* Try it again if SL's having issues */
      		if (sizeof($response) < 1)
       		{
       			$response = http_post_fields($our_url, $fields, NULL, $options);
       		}

        	if ($audit_flag)
        	{
        		if (preg_match("/sent/", $response))
        		{
        			$our_name = get_avatar_name($our_handler, $avatar_id);
        			send_audit($our_handler, $avatar_id, $our_name);
        		}
        	}
        }
?>