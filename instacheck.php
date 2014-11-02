<?php

// Enable error reporting
error_reporting(E_ALL | E_STRICT);
ini_set('display_errors', 1);
ini_set('html_errors', 1);

// Get the profile ID through username
function getID($username, $client_id) {
    $username = strtolower($username); // Instagram usernames are always in lowercase
    // Make sure that the request sent is formed correctly
    if (getResponse("https://api.instagram.com/v1/users/search?q={$username}&client_id={$client_id}") != "400") {
        $get = file_get_contents("https://api.instagram.com/v1/users/search?q={$username}&client_id={$client_id}");
        $json = json_decode($get);
        // Iterate through json result and search for our username
        foreach ($json->data as $user) {
            if ($user->username == $username) {
                return $user->id;
            }
        }
        return false; // User was not found
    } else {
	return -1; // Bad request
    }
}

// Get the HTTP response code
function getResponse($url) {
    $headers = get_headers($url);
    return substr($headers[0], 9, 3);
}

// Check profile state
function isPrivate($username, $client_id) {
    $user_id = getID($username, $client_id);
    if ($user_id != false && $user_id != -1) {
	// Response code 400 (bad request) means that we can't get any info (private profile)
	if (getResponse("https://api.instagram.com/v1/users/{$user_id}/media/recent/?client_id={$client_id}") != "400") {
	    $get = file_get_contents("https://api.instagram.com/v1/users/{$user_id}/media/recent/?client_id={$client_id}");	
	    $json = json_decode($get);
	    // Code 200 means that the profile is public
	    if ($json->meta->code == 200) {
		$pic;
		// Iterate through data to get profile picture
		foreach ($json->data as $res) {
		    $pic = $res->user->profile_picture;
		}
		echo "<br /><img src='{$pic}' /><br />";
	        return false;
	    } else { // Code other than 200 or 400
	        return 'unknown';
	    }
	} else {
	    return true;
	}
    } else {
	return -1;
    }
}

?>
