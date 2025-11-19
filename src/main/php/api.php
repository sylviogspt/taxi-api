<?php
function callApi($method, $url, $data = false) {
    $curl = curl_init();
    
    // DANS DOCKER : On utilise le nom du conteneur Java défini dans le docker-compose
    $base_url = "http://taxi-api:8080"; 

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
    
    if ($result === false) {
        $error_msg = curl_error($curl);
        curl_close($curl);
        // Affiche l'erreur cURL et l'URL tentée
        die("Erreur de connexion C-URL : [ERREUR $error_msg] - L'API Java à l'adresse http://taxi_api_app:8080 n'a pas pu être jointe.");
    }
    
    curl_close($curl);
    return json_decode($result, true);

}
?>