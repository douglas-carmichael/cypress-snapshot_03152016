<?php

function error_die($our_handler, $error_message)
{
	send_avatar_im($our_handler, DEBUG_UUID, $error_message, true);
	die($error_message);
}

?>
