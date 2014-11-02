<?php

error_reporting(E_ALL | E_STRICT);
ini_set('display_errors', 1);
ini_set('html_errors', 1);

function getID($username, $client_id) {

    $username = strtolower($username);
    $get = file_get_contents("https://api.instagram.com/v1/users/search?q={$username}&client_id={$client_id}");
    $json = json_decode($get);

    foreach ($json->data as $user) {
        if ($user->username == $username) {
            return $user->id;
        }
    }

    return 0;

}

function getResponse($url) {
    $headers = get_headers($url);
    return substr($headers[0], 9, 3);
}

function isPrivate($username, $client_id) {
    $user_id = getID($username, $client_id);

    echo "User {$username} (ID: {$user_id}) is ";

    if ($user_id > 0) {
        
	if (getResponse("https://api.instagram.com/v1/users/{$user_id}/media/recent/?client_id={$client_id}") != "400") {
    	    
	    $get = file_get_contents("https://api.instagram.com/v1/users/{$user_id}/media/recent/?client_id={$client_id}");	
	    $json = json_decode($get);

	    if ($json->meta->code == 200) {
	        return 'public';
	    } else {
	        return 'unknown';
	    }

	} else {
	    return 'private';
	}

    } else {
	return 'id not found';    
    }

}

?>
