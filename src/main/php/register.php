<?php
require_once 'api.php'; // Inclure la fonction callApi

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 1. Récupérer les données du formulaire
    $data = [
        "nom" => $_POST['nom'],
        "email" => $_POST['email'],
        "tel" => $_POST['tel'] // Assure-toi que ton API accepte "tel" (le README dit "tel")
    ];

    // 2. Envoyer à l'API Java
    $response = callApi("POST", "/clients", $data);

    // 3. Vérifier si succès (si l'API renvoie l'objet créé avec un ID)
    if (isset($response['id'])) {
        header("Location: login.php?success=1"); // Redirection vers le login
        exit();
    } else {
        $message = "Erreur lors de l'inscription.";
    }
}
?>

<!DOCTYPE html>
<html>
<head><title>Inscription Taxi</title></head>
<body>
    <h2>Créer un compte</h2>
    <?php if($message) echo "<p style='color:red'>$message</p>"; ?>
    
    <form method="post">
        <input type="text" name="nom" placeholder="Votre nom" required><br>
        <input type="email" name="email" placeholder="Votre email" required><br>
        <input type="text" name="tel" placeholder="Téléphone" required><br>
        <button type="submit">S'inscrire</button>
    </form>
    <a href="login.php">Déjà un compte ? Connectez-vous</a>
</body>
</html>