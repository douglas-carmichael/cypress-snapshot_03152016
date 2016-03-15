integer our_listener_handle;
string our_server = "http://www.dcarmichael.net:8080/avatar_im_backend.php";
string show_id;
string new_name;
key name_request_id;

default
{
    link_message (integer sender, integer number, string our_string, key our_key)
    {
        if (number == 2)
        {
            key id = llGetOwner();
            show_id = our_string;
            llListenRemove(our_listener_handle);
            our_listener_handle = llListen(-98, "", NULL_KEY, "");
            llTextBox(id, "Please enter a name for this show.\nSpaces will be replaced with '_'.\n(Date will be added automatically.)\n", -98);
        }
    }
    
    /* Adapted from the SL Wiki: http://wiki.secondlife.com/wiki/LlTextBox */
    
    listen (integer channel, string name, key id, string message)
    {
        if (channel == -98)
        {
            llListenRemove(our_listener_handle);
            integer messageLength = llStringLength(message);
            string lastMessageCharacter = llGetSubString(message, messageLength - 1, messageLength - 1);
 
            if (lastMessageCharacter == llUnescapeURL("%0A"))
            {
             //  Ignore final carriage return embedded in the message
                message = llGetSubString(message, 0, -2);
            }   
                if (message != "")
                {
                    name_request_id = llHTTPRequest(our_server, [HTTP_METHOD, "POST", HTTP_MIMETYPE, "application/x-www-form-urlencoded"], "action=name_show&show_id=" + show_id + "&show_name=" + message);            
                }
                else
                {
                    llOwnerSay("Name of show ID " + show_id + " not changed.");
                }
        }   
    }
    
    http_response(key request_id, integer status, list metadata, string body)
    {
        if (request_id == name_request_id)
        {
            new_name = llStringTrim(body, STRING_TRIM);
            llOwnerSay("Renaming of show ID " + show_id + " successful. New name: " + new_name");
        }
    }
}