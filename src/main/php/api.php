<?php
// api.php
function callApi($method, $url, $data = false) {
    $curl = curl_init();
    $base_url = "http://localhost:8080"; 

    curl_setopt($curl, CURLOPT_URL, $base_url . $url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    
    if ($method === "POST") {
        curl_setopt($curl, CURLOPT_POST, 1);
        if ($data) {
            $jsonData = json_encode($data);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $jsonData);
            curl_setopt($curl, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        }
    }

    $result = curl_exec($curl);
    curl_close($curl);
    return json_decode($result, true);
}
?>