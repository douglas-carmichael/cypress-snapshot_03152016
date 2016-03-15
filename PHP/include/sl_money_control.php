<?php

	function refund_avatar($our_handler, $avatar_uuid)
	{
				$refund_price = 1500;
				$our_url = get_sim_url($our_handler);
	        	$fields = array('id' => $avatar_uuid, 'message' => 'null',
        						'action' => 'refund_money', 'secret' => SHARED_SECRET);
	        	$options = array('timeout' => 600);

				/* Let's now try to get the information from SL */
        		$response = http_post_fields($our_url, $fields, NULL, $options);
        		
        		/* Try it again if SL's having issues */
      			if (sizeof($response) < 1)
       			{
       			$response = http_post_fields($our_url, $fields, NULL, $options);
       			}

	}
	
?>