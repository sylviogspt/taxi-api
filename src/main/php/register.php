<?php
// On s'assure que la session est démarrée (bonne pratique)
session_start();
require_once 'api.php'; // Inclure la fonction callApi

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 1. Récupérer les données du formulaire
    $data = [
        "nom" => $_POST['nom'],
        "email" => $_POST['email'],
        "tel" => $_POST['tel']
    ];

    // 2. Envoyer à l'API Java
    // L'API est censée renvoyer l'objet client créé ou une erreur JSON
    $response = callApi("POST", "/clients", $data);

    // 3. Vérification du succès (On s'attend à recevoir l'ID du client créé)
    if (isset($response['id']) && $response['id'] > 0) {
        // SUCCÈS : Redirection vers la page de login
        header("Location: login.php?success=1");
        exit();
    } else {
        // ÉCHEC : Affichage du message d'erreur détaillé

        $message = "🚫 Erreur lors de l'inscription.";

        // On affiche la réponse brute du serveur pour le débogage
        $message .= "<br><b>Réponse brute du serveur :</b> " . json_encode($response);
        
        // Si l'API renvoie un champ 'message' ou 'error' (fréquent avec Spring Boot)
        if (isset($response['message'])) {
             $message .= "<br><b>Message API :</b> " . htmlspecialchars($response['message']);
        }
        
        // Arrête l'exécution pour afficher le message de débogage complet
        die($message); 
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Inscription Taxi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { display: flex; align-items: center; justify-content: center; height: 100vh; background-color: #f8f9fa; }
        .register-card { width: 100%; max-width: 400px; padding: 20px; border-radius: 10px; background: white; }
    </style>
</head>
<body>
    <div class="card register-card shadow">
        <div class="card-body">
            <h3 class="card-title text-center mb-4">Créer un compte Client</h3>
            
            <form method="post">
                <div class="mb-3">
                    <input type="text" name="nom" class="form-control" placeholder="Votre nom" required>
                </div>
                <div class="mb-3">
                    <input type="email" name="email" class="form-control" placeholder="Votre email" required>
                </div>
                <div class="mb-3">
                    <input type="text" name="tel" class="form-control" placeholder="Téléphone" required>
                </div>
                
                <button type="submit" class="btn btn-success w-100">S'inscrire</button>
            </form>
            
            <div class="mt-3 text-center">
                <a href="login.php">Déjà un compte ? Connectez-vous</a>
            </div>
        </div>
    </div>
</body>
</html>