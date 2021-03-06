<?php

// A hack to retreive the profile picture from the HTML of a private profile
function getProfilePictureWhenPrivate($username) {
    $username = strtolower($username);
    $url = 'http://instagram.com/' . $username;
    $get = file_get_contents($url);
    // Create a DOM object and load profile HTML
    libxml_use_internal_errors(true);
    $doc = new DOMDocument();
    $doc->loadHTML($get);
    // Search for meta element
    $metas = $doc->getElementsByTagName('meta');
    // Iterate through metas and get picture URL
    foreach ($metas as $meta) {
        if($meta->getAttribute('property') == 'og:image') { 
            return $meta->getAttribute('content');
        }
    }
    return -1; // Error while getting picture 
}

// Get the profile ID through username
function getID($username, $client_id) {
    $username = strtolower($username); // Instagram usernames are always in lowercase
    // Send request to API and suppress any warnings
    $get = @file_get_contents("https://api.instagram.com/v1/users/search?q={$username}&client_id={$client_id}");
    // False means bad request
    if ($get === false) {
	return -1;
    } else {
        $json = json_decode($get);
        // Iterate through json result and search for our username
        foreach ($json->data as $user) {
            if ($user->username == $username) {
                return $user->id;
            }
        }
        return false; // User was not found
    }
}

// Check profile state
function isPrivate($username, $client_id) {
    $user_id = getID($username, $client_id);
    if ($user_id != false && $user_id != -1) {
	// Send request to API and suppress any warnings
	$get = @file_get_contents("https://api.instagram.com/v1/users/{$user_id}/media/recent/?client_id={$client_id}");
	// False means that we cannot retreive profile data (private profile)
	if ($get === false) {
	    // Get profile picture
	    if (($pic = getProfilePictureWhenPrivate($username)) != -1) {
		echo "<br /><img src='{$pic}' /><br />";
	    }
	    return true;
	} else {
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
	}
    } else {
	return -1; // User not found or bad request sent through getID
    }
}

?>
