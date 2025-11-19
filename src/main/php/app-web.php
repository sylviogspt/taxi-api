<?php
session_start();
require_once 'api.php'; // Assurez-vous que api.php est inclus

// --- 1. Sécurité : Vérification de la connexion ---
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}
$user = $_SESSION['user'];

// --- Variables pour les messages flash ---
// Récupérer le message flash s'il existe et le supprimer immédiatement
$flash_message = isset($_SESSION['flash_message']) ? $_SESSION['flash_message'] : null;
unset($_SESSION['flash_message']);


// --- 2. Gestion de la COMMANDE de taxi (POST) ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['commander'])) {
    $nouvelle_course = [
        "client" => ["id" => $user['id']], // On lie la course au client connecté
        "pointDepart" => $_POST['depart'],
        "pointArrivee" => $_POST['arrivee']
    ];

    $res = callApi("POST", "/courses", $nouvelle_course);
    if (isset($res['id'])) {
        $_SESSION['flash_message'] = ['type' => 'success', 'text' => "Taxi commandé avec succès ! (Course #".$res['id'].")"];
    } else {
        $error_text = "Erreur lors de la commande.";
        if (is_array($res) && isset($res['message'])) {
             $error_text .= " Détails: " . htmlspecialchars($res['message']);
        }
        $_SESSION['flash_message'] = ['type' => 'danger', 'text' => $error_text];
    }
    // Redirection après le POST pour éviter la soumission multiple
    header("Location: app-web.php");
    exit();
}


// --- 3. Gestion de la SUPPRESSION de course (POST) ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['supprimer_course'])) {
    $course_id = $_POST['course_id'];
    
    // 3a. Sécurité : Vérifier que la course existe et appartient à l'utilisateur
    $course_details = callApi("GET", "/courses/" . $course_id);
    
    if ($course_details && isset($course_details['client']['id']) && $course_details['client']['id'] == $user['id']) {
        
        // 3b. Vérifier que la course est encore 'demandee' (non acceptée/finie)
        $status = strtolower($course_details['status'] ?? 'demandee');
        
        if ($status === 'demandee') {
            
            // 3c. Appel DELETE. L'API Java renvoie 200 OK avec corps vide, ce qui donne $res === null
            $res = callApi("DELETE", "/courses/" . $course_id);
            
            // On considère $res = null comme un succès (corps vide)
            if ($res === null || !is_array($res) || empty($res)) {
                 $_SESSION['flash_message'] = ['type' => 'info', 'text' => "Course #".$course_id." annulée avec succès."];
            } else {
                 // Si l'API renvoie un corps non-vide (donc une erreur JSON), on l'affiche.
                 $error_text = "Erreur API lors de la suppression.";
                 if (is_array($res) && isset($res['message'])) {
                     $error_text .= " Détails: " . htmlspecialchars($res['message']);
                 } else {
                     $error_text .= " Réponse serveur : " . json_encode($res);
                 }
                 $_SESSION['flash_message'] = ['type' => 'danger', 'text' => $error_text];
            }

        } else {
            $_SESSION['flash_message'] = ['type' => 'warning', 'text' => "La course #".$course_id." ne peut plus être annulée (statut : ".htmlspecialchars($status).")."];
        }

    } else {
        $_SESSION['flash_message'] = ['type' => 'danger', 'text' => "Action non autorisée : Course non trouvée ou non vous appartenant."];
    }
    
    // Redirection après le POST pour éviter la soumission multiple
    header("Location: app-web.php");
    exit();
}


// --- 4. Récupérer la liste des courses (pour affichage) ---
$courses = callApi("GET", "/courses");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Taxi App - Tableau de bord</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Bienvenue, <?= htmlspecialchars($user['nom']) ?> !</h1>
        <a href="logout.php" class="btn btn-danger">Se déconnecter</a>
    </div>

    <!-- Affichage du message flash -->
    <?php if($flash_message): ?>
        <div class="alert alert-<?= $flash_message['type'] ?> alert-dismissible fade show" role="alert">
            <?= $flash_message['text'] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="row">
        <!-- COLONNE GAUCHE : Commander -->
        <div class="col-md-4 mb-4">
            <div class="card p-3 shadow-sm">
                <h3>Commander un Taxi</h3>
                
                <form method="post">
                    <div class="mb-3">
                        <label class="form-label">Point de départ</label>
                        <input type="text" name="depart" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Point d'arrivée</label>
                        <input type="text" name="arrivee" class="form-control" required>
                    </div>
                    <button type="submit" name="commander" class="btn btn-primary w-100">Valider la course</button>
                </form>
            </div>
        </div>

        <!-- COLONNE DROITE : Liste des courses -->
        <div class="col-md-8">
            <h3>Historique des courses</h3>
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Départ</th>
                            <th>Arrivée</th>
                            <th>Statut</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if($courses && is_array($courses)): ?>
                            <?php foreach($courses as $c): ?>
                                <?php 
                                    $isMyCourse = (isset($c['client']['id']) && $c['client']['id'] == $user['id']); 
                                    $status = strtolower($c['status'] ?? 'demandee');
                                    // Le client ne peut supprimer que SES courses qui sont encore "demandee"
                                    $canDelete = $isMyCourse && $status === 'demandee';
                                ?>
                                <tr class="<?= $isMyCourse ? 'table-active fw-bold' : '' ?>">
                                    <td><?= htmlspecialchars($c['id']) ?></td>
                                    <td><?= htmlspecialchars($c['pointDepart']) ?></td>
                                    <td><?= htmlspecialchars($c['pointArrivee']) ?></td>
                                    <td><span class="badge text-bg-<?= ($status === 'demandee' ? 'info' : ($status === 'acceptee' ? 'warning' : 'success')) ?>"><?= htmlspecialchars($status) ?></span></td>
                                    <td>
                                        <?php if($canDelete): ?>
                                            <!-- Formulaire pour la suppression -->
                                            <form method="post" style="display: inline;" onsubmit="return confirm('Êtes-vous sûr de vouloir annuler la course #<?= $c['id'] ?> ?');">
                                                <input type="hidden" name="course_id" value="<?= htmlspecialchars($c['id']) ?>">
                                                <button type="submit" name="supprimer_course" class="btn btn-sm btn-outline-danger">
                                                    Annuler
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center">Aucune course trouvée.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <!-- Script Bootstrap pour les alertes dismissibles -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>