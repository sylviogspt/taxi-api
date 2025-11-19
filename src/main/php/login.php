<?php
session_start();
require_once 'api.php';

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_client = $_POST['client_id'];

    // On vérifie si ce client existe vraiment via l'API
    $client = callApi("GET", "/clients/" . $id_client);

    if ($client && isset($client['id'])) {
        // BINGO : On sauvegarde l'utilisateur en session PHP
        $_SESSION['user'] = $client;
        header("Location: app-web.php");
        exit();
    } else {
        $error = "Client introuvable. Vérifiez l'ID.";
    }
}
?>

<!DOCTYPE html>
<html>
<head><title>Login Taxi</title></head>
<body>
    <h2>Connexion</h2>
    <?php if(isset($_GET['success'])) echo "<p style='color:green'>Compte créé ! Connectez-vous.</p>"; ?>
    <?php if($error) echo "<p style='color:red'>$error</p>"; ?>

    <form method="post">
        <label>Entrez votre ID Client :</label>
        <input type="number" name="client_id" required>
        <button type="submit">Entrer</button>
    </form>
    <a href="register.php">Pas de compte ?</a>
</body>
</html>