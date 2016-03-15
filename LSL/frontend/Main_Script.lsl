// Define our price (in L$)
integer our_price = 1;
// Define the address of our server
string our_server = "http://www.dcarmichael.net:8080/avatar_im_backend.php";
// Define our strings
string our_region;
string show_data;
string show_id;
string show_filename;
// Define the keys for our HTTP requests
key start_request_id;
// Define the keys for our UUIDs
key creator_uuid;
key owner_uuid;

resetAllScripts()
{
    integer i = llGetInventoryNumber(INVENTORY_SCRIPT);
    integer x;
    for (x=0; x < i; ++x)
            {
                string scriptName = llGetInventoryName(INVENTORY_SCRIPT,x);
                if (scriptName != llGetScriptName())
                {
                    llResetOtherScript(scriptName); 
                }
            }
        llResetScript(); 
}

default
{
    on_rez(integer start_param)
    {
        llResetScript();
    }

    changed(integer change)
    {
        if (change & CHANGED_OWNER)
        {
            resetAllScripts();
        }
    }
    
    state_entry()
    {
        creator_uuid = llGetCreator();
        owner_uuid = llGetOwner();
        llSetPayPrice(PAY_HIDE, [our_price,PAY_HIDE,PAY_HIDE,PAY_HIDE]);
        our_region = llEscapeURL(llGetRegionName());
        start_request_id = llHTTPRequest(our_server, [HTTP_METHOD, "POST", HTTP_MIMETYPE, "application/x-www-form-urlencoded"], "action=start_show&my_region=" + our_region);
    }

    touch_start(integer num_detected)
    {
        if(llDetectedKey(0) == creator_uuid || llDetectedKey(0) == owner_uuid)
        {
            llMessageLinked(LINK_THIS, 1, show_id, (key)show_filename);     
        }
    }
    
  money(key giver, integer amount) 
  {
        string giver_key = (string)giver;
        llHTTPRequest(our_server, [HTTP_METHOD, "POST", HTTP_MIMETYPE, "application/x-www-form-urlencoded"], "action=buy_show&my_uuid=" + giver_key + "&show_id=" + show_id);
  }
  
 http_response(key request_id, integer status, list metadata, string body)
    {
         if (request_id == start_request_id)
         {
             show_data = llStringTrim(body, STRING_TRIM);
             list show_data_list = llParseString2List(show_data, [","], [""]);
             show_id = llList2String(show_data_list, 0);
             show_filename = llList2String(show_data_list, 1);
             llOwnerSay("Show started. ID: " + show_id + " " + "Filename: " + show_filename);
         }  
    }
}
