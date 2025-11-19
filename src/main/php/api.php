<?php
function callApi($method, $url, $data = false) {
    $curl = curl_init();
    
    $base_url = "http://taxi-api:8080"; 

    curl_setopt($curl, CURLOPT_URL, $base_url . $url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

    // Gestion correcte DES MÉTHODES
    switch (strtoupper($method)) {

        case "POST":
            curl_setopt($curl, CURLOPT_POST, true);
            if ($data !== false) {
                curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
                curl_setopt($curl, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
            }
            break;

        case "DELETE":
        case "PUT":
        case "PATCH":
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
            if ($data !== false) {
                curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
                curl_setopt($curl, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
            }
            break;

        case "GET":
        default:
            // GET n'a rien à faire de spécial
            break;
    }

    $result = curl_exec($curl);

    if ($result === false) {
        $error_msg = curl_error($curl);
        curl_close($curl);
        die("Erreur C-URL : $error_msg");
    }

    curl_close($curl);

    return json_decode($result, true);
}
?>
