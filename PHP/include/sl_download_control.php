<?php

function init_get_filename($our_handler, $link_id, $ip_address)
{
    $our_statement = $our_handler->prepare('SELECT init_download (?, ?)');
    $our_statement->execute(array($link_id, $ip_address));
    
    /* Did we fetch it properly? */
    $show_columns = $our_statement->columnCount();
    if ($show_columns > 0)
    {
		$result = $our_statement->fetch(PDO::FETCH_ASSOC);
		return $result['init_download'];
    }
    else
    {
        return FALSE;
    }
}

function check_download_count($our_handler, $link_id)
{
	$our_statement = $our_handler->prepare('SELECT download_count FROM purchase_table
	WHERE link_id = ?');
	$our_statement->execute(array($link_id));
	
	/* Did we fetch it properly? */
	
    $show_columns = $our_statement->columnCount();
    if ($show_columns > 0)
    {
		$result = $our_statement->fetch(PDO::FETCH_ASSOC);
		return $result['download_count'];
    }
    else
    {
        return FALSE;
    }

}

function check_link_id($link_id)
{
	/* Check the length of the link ID */
	$our_length = strlen($link_id);
	
	if ($our_length > 16) 
	{
		return FALSE;
	}
	
	/* Check for valid characters */
	if (preg_match("/[^-a-z0-9_]/i", $link_id))
	{
		return FALSE;
	}
	
	return TRUE;	
}
	
/* From the original download.php script */

// Check if the file exists
// Check in subfolders too
function find_file ($dirname, $fname, &$file_path) 
{

  $dir = opendir($dirname);

  while ($file = readdir($dir)) {
    if (empty($file_path) && $file != '.' && $file != '..') {
      if (is_dir($dirname.'/'.$file)) {
        find_file($dirname.'/'.$file, $fname, $file_path);
      }
      else {
        if (file_exists($dirname.'/'.$fname)) {
          $file_path = $dirname.'/'.$fname;
          return;
        }
      }
    }
  }

} // find_file

?>