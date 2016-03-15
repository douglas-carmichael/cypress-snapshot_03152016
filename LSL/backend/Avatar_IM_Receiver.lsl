// Avatar IM back-end script (interfaces with avatar_im_backend.php)
// Creator: Douglas Carmichael (dcarmich@dcarmichael.net)
// Originally from LSL Wiki: http://wiki.secondlife.com/wiki/LSL_HTTP_server/examples

// Define our key for the requested URL
key requestURL;
// Define our key for avatar name lookups
key avatar_name_lookup;
// Define our key for the HTTP request
key global_id;
// Define our avatar name string
string avatar_name;
// Define the address of our server
string our_server = "http://www.dcarmichael.net:8080/avatar_im_backend.php";
// Define our shared secret between PHP and LSL
string sharedSecret = "fQxywNjFTx06Ll";
// Define the flag for debit permissions
integer debit_flag;
// Define the flag whether or not to refund money
integer refund_flag = 0;
// Define the key for debit transactions
key debit_trans_id;
// Define the amount to debit
integer ourPrice;

// strReplace: Replace a character in a string with another character
// From: LSL Combined Library
// http://wiki.secondlife.com/wiki/Combined_Library
    
string strReplace(string str, string search, string replace) 
{
        return llDumpList2String(llParseStringKeepNulls((str = "") + str, [search], []), replace);
}

default 
{
 
    state_entry() 
    {
        requestURL = llRequestURL();     // Request that an URL be assigned to me.
    }
 
     http_request(key id, string method, string body) 
     {
        if ((method == URL_REQUEST_GRANTED) && (id == requestURL))
        {
            // An URL has been assigned to me.
            llOwnerSay("Obtained URL: " + body);
            llHTTPRequest(our_server, [HTTP_METHOD, "POST", HTTP_MIMETYPE, "application/x-www-form-urlencoded"], "action=set_url&our_url=" + body);
            requestURL = NULL_KEY;
        }
        else if ((method == URL_REQUEST_DENIED) && (id == requestURL)) 
        {
            // I could not obtain a URL
            llOwnerSay("There was a problem, and an URL was not assigned: " + body);
            llReleaseURL(requestURL);
            requestURL = NULL_KEY;
        }
 
        else if (method == "POST") 
        {
            // An incoming message was received.

            // Parse the incoming string to a list (avatar key, string to send)
            list bodyList = llParseString2List(body, ["=", "&"], []);
            
            // Grab the variables from the list into separate variables
            key ourKey = (key)llList2String(bodyList, 1);
            string ourString = llList2String(bodyList, 3);
            string ourAction = llList2String(bodyList, 5);
            string ourSecret = llList2String(bodyList, 7);
            
            if (ourSecret != sharedSecret)
            {
                llHTTPResponse(id, 403, "Credentials invalid.");
                return;
            }
                        
            // Were we asked to send an IM?
            if (ourAction == "send_im") 
            {
                // Replace the '+' in a POST with spaces
                string ourMessage = strReplace(ourString, "+", " ");

                // Send the IM
                llInstantMessage(ourKey, llUnescapeURL(ourMessage));
            
                // Tell the script that it's been successfully delivered.
                llHTTPResponse(id,200,"IM sent");
            }
            else if (ourAction == "name_lookup")
            {
                 avatar_name_lookup = llRequestAgentData(ourKey, DATA_NAME);
                 // Store our HTTP request ID in a global variable for the dataserver event
                 global_id = id;
            }
            else if (ourAction == "refund_money")
            {
                if(refund_flag == 1)
                {
                    // Tell the owner that a user is requesting a debit
                    llOwnerSay("Avatar requesting refund. Please grant debit permissions to enable it.");
                    // Request debit permissions to the owner's account
                    llRequestPermissions(llGetOwner(), PERMISSION_DEBIT);
                    // Store our HTTP request ID in a global variable for the transaction_result event
                    global_id = id;
                    // Have the debit permissions been granted?
                    if(debit_flag)
                    {
                        // Define how much to transfer
                        ourPrice = (integer)ourString;
                        // Transfer the money
                        debit_trans_id = llTransferLindenDollars(ourKey, ourPrice);
                    }
                }
            }
        }
        else 
        {
            // An incoming message has come in using a method that has not been anticipated.
            llHTTPResponse(id,405,"Unsupported Method");
        }
    }   
    
    run_time_permissions(integer our_perms)
    {
        if (our_perms & PERMISSION_DEBIT)
            debit_flag = TRUE;
    }
     
    transaction_result(key trans_id, integer trans_success, string data)
    {
        if (trans_id != debit_trans_id)
        {
            return;
        }
        if (trans_success)
        {
            llHTTPResponse(global_id, 200, "Refund successful.");
        }
        else
        {
            llHTTPResponse(global_id, 200, "Refund failure.");
        }
    }
    dataserver(key our_query, string data)
    {
        // Dataserver routine to handle HTTP lookups of an avatar name
        if (avatar_name_lookup == our_query)
        {
            avatar_name = data;
            llHTTPResponse(global_id, 200, avatar_name);
        }
    }
}

