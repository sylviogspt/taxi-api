<?php
session_start();
require_once 'api.php';

// 1. Sécurité : Si pas connecté, on dégage vers le login
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

$user = $_SESSION['user'];
$message_course = "";

// 2. Gestion de la commande de taxi (POST)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['commander'])) {
    $nouvelle_course = [
        "client" => ["id" => $user['id']], // On lie la course au client connecté
        "pointDepart" => $_POST['depart'],
        "pointArrivee" => $_POST['arrivee']
    ];

    $res = callApi("POST", "/courses", $nouvelle_course);
    if (isset($res['id'])) {
        $message_course = "Taxi commandé avec succès ! (Course #" . $res['id'] . ")";
    }
}

// 3. Récupérer la liste des courses (pour voir l'historique global)
$courses = callApi("GET", "/courses");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Taxi App</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Bienvenue, <?= htmlspecialchars($user['nom']) ?> !</h1>
        <a href="logout.php" class="btn btn-danger">Se déconnecter</a>
    </div>

    <div class="row">
        <div class="col-md-4">
            <div class="card p-3">
                <h3>Commander un Taxi</h3>
                <?php if($message_course) echo "<div class='alert alert-success'>$message_course</div>"; ?>
                
                <form method="post">
                    <div class="mb-3">
                        <label>Départ</label>
                        <input type="text" name="depart" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Arrivée</label>
                        <input type="text" name="arrivee" class="form-control" required>
                    </div>
                    <button type="submit" name="commander" class="btn btn-primary w-100">Valider la course</button>
                </form>
            </div>
        </div>

        <div class="col-md-8">
            <h3>Historique des courses</h3>
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Départ</th>
                        <th>Arrivée</th>
                        <th>Statut</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if($courses): ?>
                        <?php foreach($courses as $c): ?>
                            <?php $isMyCourse = ($c['client']['id'] == $user['id']); ?>
                            <tr class="<?= $isMyCourse ? 'table-active fw-bold' : '' ?>">
                                <td><?= $c['id'] ?></td>
                                <td><?= htmlspecialchars($c['pointDepart']) ?></td>
                                <td><?= htmlspecialchars($c['pointArrivee']) ?></td>
                                <td><?= htmlspecialchars($c['status'] ?? 'En attente') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>