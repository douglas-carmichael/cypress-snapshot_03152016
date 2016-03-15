<?php

###############################################################
# File Download 1.31
###############################################################
# Visit http://www.zubrag.com/scripts/ for updates
# Modified by dcarmich on 7/29/2013 for cypress project
###############################################################

// our includes
require_once('include/config.php');
require_once('include/db_connect.php');
require_once('include/sl_download_control.php');

// Allowed extensions list in format 'extension' => 'mime type'
// If mime type is set to empty string then script will try to detect mime type 
// itself, which would only work if you have Mimetype or Fileinfo extensions
// installed on server.
$allowed_ext = array (

  // archives
  'zip' => 'application/zip',

  // audio
  'mp3' => 'audio/mpeg',
  'wav' => 'audio/x-wav',

  // video
  'mpeg' => 'video/mpeg',
  'mpg' => 'video/mpeg',
  'mpe' => 'video/mpeg',
  'mov' => 'video/quicktime',
  'avi' => 'video/x-msvideo'
);



####################################################################
###  DO NOT CHANGE BELOW
####################################################################

// If hotlinking not allowed then make hackers think there are some server problems
if (ALLOWED_REFERRER !== ''
&& (!isset($_SERVER['HTTP_REFERER']) || strpos(strtoupper($_SERVER['HTTP_REFERER']),strtoupper(ALLOWED_REFERRER)) === false)
) {
  die("Internal server error. Please contact system administrator.");
}

// Make sure program execution doesn't time out
// Set maximum script execution time in seconds (0 means no limit)
set_time_limit(0);

## Modified by dcarmich for cypress project: 7/29/2013

if (!isset($_GET['link_id']) || empty($_GET['link_id'])) {
  die("Please specify link ID for download.");
}

// Nullbyte hack fix
if (strpos($_GET['link_id'], "\0") !== FALSE) die('');

// Get the link ID into a variable
$link_id = $_GET['link_id'];

// Check the link ID for validity 
$chk_link_id = check_link_id($link_id);

if ($chk_link_id === FALSE)
{
	die("Invalid link ID. Please contact support.");
}

// Check to see if we're within our download count
$dl_count = check_download_count($ourDBHandler, $link_id);

if ($dl_count === FALSE) 
{
	die("Error checking download count. Please contact support.");
}

if ($dl_count > 5)
{
	die("You have exceeded your allowed downloads for this link. 
		Please contact support.");
}
	
// Get our IP address into a variable
$our_ip_address = $_SERVER['REMOTE_ADDR'];

// Initialize the download and log it into the database
$fname = init_get_filename($ourDBHandler, $link_id, $our_ip_address);

if ($fname == FALSE) 
{
	die("Invalid link entry in database. Please contact support.");
}

// get full file path (including subfolders)
// $file_path = '';
// find_file(BASE_DIR, $fname, $file_path);
error_log($fname);
$file_path = UPLOAD_BASE . $fname;

if (!is_file($file_path)) {
  die("File does not exist. Please contact support."); 
}

// file size in bytes
$fsize = filesize($file_path); 

// file extension
$fext = strtolower(substr(strrchr($fname,"."),1));

// check if allowed extension
if (!array_key_exists($fext, $allowed_ext)) {
  die("Not allowed file type."); 
}

// get mime type
if ($allowed_ext[$fext] == '') {
  $mtype = '';
  // mime type is not set, get from server settings
  if (function_exists('mime_content_type')) {
    $mtype = mime_content_type($file_path);
  }
  else if (function_exists('finfo_file')) {
    $finfo = finfo_open(FILEINFO_MIME); // return mime type
    $mtype = finfo_file($finfo, $file_path);
    finfo_close($finfo);  
  }
  if ($mtype == '') {
    $mtype = "application/force-download";
  }
}
else {
  // get mime type defined by admin
  $mtype = $allowed_ext[$fext];
}

// Browser will try to save file with this filename, regardless original filename.
// You can override it if needed.

if (!isset($_GET['fc']) || empty($_GET['fc'])) {
  $asfname = $fname;
}
else {
  // remove some bad chars
  $asfname = str_replace(array('"',"'",'\\','/'), '', $_GET['fc']);
  if ($asfname === '') $asfname = 'NoName';
}

// set headers
header("Pragma: public");
header("Expires: 0");
header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
header("Cache-Control: public");
header("Content-Description: File Transfer");
header("Content-Type: $mtype");
header("Content-Disposition: attachment; filename=\"$asfname\"");
header("Content-Transfer-Encoding: binary");
header("Content-Length: " . $fsize);

// download
// @readfile($file_path);
$file = @fopen($file_path,"rb");
if ($file) {
  while(!feof($file)) {
    print(fread($file, 1024*8));
    flush();
    if (connection_status()!=0) {
      @fclose($file);
      die();
    }
  }
  @fclose($file);
}

?>