<?php
function getGames($access_token, $client_id) {
    $url = "https://api.twitch.tv/helix/games/top?first=100";
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $access_token,
        'Client-ID: ' . $client_id      
    ]);

    $response = curl_exec($curl);
    curl_close($curl);
    return json_decode($response, true);
}

    function getUser($access_token,$client_id){
        $url="https://api.twitch.tv/helix/users";
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $access_token,
            'Client-ID: ' . $client_id      
        ]);

        $response = curl_exec($curl);
        curl_close($curl);
        return json_decode($response,true);
    }