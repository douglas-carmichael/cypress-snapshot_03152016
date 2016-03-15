integer download_listener;
integer showname_listener;
string download_dlog;
string show_id;
string show_data;
string our_region;
string show_filename;
string new_name;

key process_request_id;
key getname_request_id;
key name_reset_request_id;
key delete_request_id;
key new_show_request_id;
key ourUser;
string our_server = "http://www.dcarmichael.net:8080/avatar_im_backend.php";

default
{
    link_message(integer sender, integer number, string our_string, key our_key)
    {
        // Check this script's number first
        if (number == 1)
        {
            // Let's get the ID and filename into some friendly variables
            show_id = our_string;
            show_filename = (string)our_key;
            our_region = llEscapeURL(llGetRegionName()); 
            // Remove all previous listeners, and set the new one up.
            llListenRemove(download_listener);
            download_listener = llListen(-99, "", ourUser, "");
            // Get the current show filename so the game can start.
            getname_request_id = llHTTPRequest(our_server, [HTTP_METHOD, "POST", HTTP_MIMETYPE, "application/x-www-form-urlencoded"],
"action=get_filename&show_id=" + show_id);
        }
    }
    
    listen(integer chan, string name, key id, string msg)
    {
        if (chan == -99)
        {
            
            if (msg == "Name Show")
            {
                llMessageLinked(LINK_THIS, 2, show_id, "");
            }
            if (msg == "New Show")
            {
                new_show_request_id = llHTTPRequest(our_server, [HTTP_METHOD, "POST", HTTP_MIMETYPE, "application/x-www-form-urlencoded"], "action=start_show&my_region=" + our_region);
                // Let's reset our naming script
                llResetOtherScript("Show Naming Script");
            }
            if (msg == "Reset Name")
            {
                name_reset_request_id = llHTTPRequest(our_server, [HTTP_METHOD, "POST", HTTP_MIMETYPE, "application/x-www-form-urlencoded"],
"action=reset_name&show_id=" + show_id);
            }
            if (msg == "Process File")
            {
                 process_request_id = llHTTPRequest(our_server, [HTTP_METHOD, "POST", HTTP_MIMETYPE, "application/x-www-form-urlencoded"], "action=process_file&my_file=" + show_filename + "&show_id=" + show_id);
            }
            if (msg == "Delete File")
            {
                delete_request_id = llHTTPRequest(our_server, [HTTP_METHOD, "POST", HTTP_MIMETYPE, "application/x-www-form-urlencoded"], "action=delete_file&my_file=" + show_filename);
            }
        }
    }
    
    http_response(key request_id, integer status, list metadata, string body)
    {
        if (request_id == getname_request_id)
        {
            if(llStringLength(body) > 63)
            {
                llOwnerSay("Invalid filename.");
            }
            else
            {
                show_filename = llStringTrim(body, STRING_TRIM);
                ourUser = llGetOwner();
                download_dlog = "\nPayment Processing Object\nCurrent show ID: " + show_id + "\nCurrent show filename: " + show_filename;
                llDialog(ourUser, download_dlog, ["Name Show", "New Show", "Reset Name", "Process File", "Delete File"], -99);    
                llSetTimerEvent(60.0);    
            }
        }
        if (request_id == name_reset_request_id)
        {
            show_filename = llStringTrim(body, STRING_TRIM);
            ourUser = llGetOwner();
            llOwnerSay("Show ID: " + show_id + " reset to filename " + show_filename);
        }
        if (request_id == delete_request_id)
        {
            show_filename = llStringTrim(body, STRING_TRIM);
            ourUser = llGetOwner();
            integer index = llSubStringIndex(show_filename, "delete_error");
            if (index == -1)
            {
                llOwnerSay("File " + show_filename + " successfully deleted.");
            }
            else 
            {
                llOwnerSay("Unable to delete file.");
            }
        }       
        if (request_id == new_show_request_id)
        {
             show_data = llStringTrim(body, STRING_TRIM);
             list show_data_list = llParseString2List(show_data, [","], [""]);
             show_id = llList2String(show_data_list, 0);
             show_filename = llList2String(show_data_list, 1);
             llOwnerSay("Show started. ID: " + show_id + " " + "Filename: " + show_filename);
        }  
    }
    
    timer()
    {
        llListenRemove(download_listener);
        llSetTimerEvent(0.0);
    }
}
