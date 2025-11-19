<?php
// --- CONFIGURATION ET CONNEXION À LA DB ---
$db_host = getenv('DB_HOST');
$db_name = getenv('DB_NAME');
$db_user = getenv('DB_USER');
$db_pass = getenv('DB_PASS');

$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$message = '';

// --- TRAITEMENT DES REQUÊTES POST (AJOUT / MODIFICATION) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    try {
        // --- CHAUFFEUR ---
        if ($action === 'save_chauffeur') {
            $stmt = $conn->prepare("INSERT INTO chauffeur (nom, immatriculation, vehicule, statut) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $_POST['nom'], $_POST['immatriculation'], $_POST['vehicule'], $_POST['statut']);
            $stmt->execute();
            $message = "Chauffeur ajouté avec succès !";
        } elseif ($action === 'update_chauffeur') {
            $stmt = $conn->prepare("UPDATE chauffeur SET nom=?, immatriculation=?, vehicule=?, statut=? WHERE id=?");
            $stmt->bind_param("ssssi", $_POST['nom'], $_POST['immatriculation'], $_POST['vehicule'], $_POST['statut'], $_POST['id']);
            $stmt->execute();
            $message = "Chauffeur mis à jour avec succès !";
        }

        // --- CLIENT ---
        elseif ($action === 'save_client') {
            $stmt = $conn->prepare("INSERT INTO client (nom, email, tel) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $_POST['nom'], $_POST['email'], $_POST['tel']);
            $stmt->execute();
            $message = "Client ajouté avec succès !";
        } elseif ($action === 'update_client') {
            $stmt = $conn->prepare("UPDATE client SET nom=?, email=?, tel=? WHERE id=?");
            $stmt->bind_param("sssi", $_POST['nom'], $_POST['email'], $_POST['tel'], $_POST['id']);
            $stmt->execute();
            $message = "Client mis à jour avec succès !";
        }

        // --- COURSE ---
        elseif ($action === 'save_course') {
            $stmt = $conn->prepare("INSERT INTO course (client_id, chauffeur_id, point_depart, point_arrivee, date_heure_depart, date_heure_arrivee, tarif, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("iissssis", $_POST['client_id'], $_POST['chauffeur_id'], $_POST['point_depart'], $_POST['point_arrivee'], $_POST['date_heure_depart'], $_POST['date_heure_arrivee'], $_POST['tarif'], $_POST['status']);
            $stmt->execute();
            $message = "Course ajoutée avec succès !";
        } elseif ($action === 'update_course') {
            $stmt = $conn->prepare("UPDATE course SET client_id=?, chauffeur_id=?, point_depart=?, point_arrivee=?, date_heure_depart=?, date_heure_arrivee=?, tarif=?, status=? WHERE id=?");
            $stmt->bind_param("iissssis", $_POST['client_id'], $_POST['chauffeur_id'], $_POST['point_depart'], $_POST['point_arrivee'], $_POST['date_heure_depart'], $_POST['date_heure_arrivee'], $_POST['tarif'], $_POST['status'], $_POST['id']);
            $stmt->execute();
            $message = "Course mise à jour avec succès !";
        }

        if (!empty($message)) {
            header("Location: index.php?message=" . urlencode($message));
            exit;
        }

    } catch (Exception $e) {
        $error_message = "Erreur: " . $e->getMessage();
        header("Location: index.php?error=" . urlencode($error_message));
        exit;
    }
}

// Récupérer le message de la redirection
if (isset($_GET['message'])) {
    $message = htmlspecialchars($_GET['message']);
}
if (isset($_GET['error'])) {
    $error_message = htmlspecialchars($_GET['error']);
}

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Console</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif; line-height: 1.6; color: #333; background-color: #f4f4f4; margin: 0; padding: 20px; }
        .container { max-width: 1200px; margin: 0 auto; background-color: #fff; padding: 25px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        h1, h2 { color: #2c3e50; border-bottom: 2px solid #ecf0f1; padding-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background-color: #3498db; color: white; }
        tr:nth-child(even) { background-color: #f2f2f2; }
        tr:hover { background-color: #e8f4fd; }
        .error { color: #c0392b; border: 1px solid #e74c3c; background-color: #fbeae5; padding: 15px; border-radius: 5px; margin-bottom: 20px; }
        .success { color: #27ae60; border: 1px solid #2ecc71; background-color: #eafaf1; padding: 15px; border-radius: 5px; margin-bottom: 20px; }
        .btn { display: inline-block; padding: 10px 15px; background-color: #3498db; color: white; text-decoration: none; border-radius: 5px; border: none; cursor: pointer; font-size: 14px; }
        .btn-edit { background-color: #f39c12; }
        .btn-add { margin-bottom: 15px; background-color: #2ecc71; }
        .action-links a { margin-right: 10px; }
        form { background-color: #ecf0f1; padding: 20px; border-radius: 8px; margin-bottom: 30px; }
        form .form-group { margin-bottom: 15px; }
        form label { display: block; font-weight: bold; margin-bottom: 5px; }
        form input, form select { width: 100%; padding: 10px; border: 1px solid #bdc3c7; border-radius: 4px; box-sizing: border-box; }
        form .btn-submit { background-color: #3498db; color: white; padding: 12px 20px; }
    </style>
</head>
<body>
    <div class="container">
        <h1><a href="index.php" style="text-decoration:none; color:inherit;">Console d'Administration</a></h1>

        <?php if (!empty($message)): ?>
            <div class="success"><?= $message ?></div>
        <?php endif; ?>
        <?php if (!empty($error_message)): ?>
            <div class="error"><?= $error_message ?></div>
        <?php endif; ?>

        <?php
        $action = $_GET['action'] ?? 'list';
        $table = $_GET['table'] ?? '';
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

        // --- ROUTAGE EN FONCTION DE L'ACTION GET ---
        switch ($action) {
            case 'add':
            case 'edit':
                display_form($conn, $table, $id);
                break;
            default:
                display_all_tables($conn);
                break;
        }

        // --- FONCTIONS D'AFFICHAGE ---

        function display_all_tables($conn) {
            display_table($conn, 'chauffeur', 'Chauffeurs');
            display_table($conn, 'client', 'Clients');
            display_table($conn, 'course', 'Courses');
        }

        function display_table($conn, $table_name, $title) {
            echo "<div style='display:flex; justify-content:space-between; align-items:center;'>";
            echo "<h2>" . htmlspecialchars($title) . "</h2>";
            echo "<a href='?action=add&table=$table_name' class='btn btn-add'>Ajouter</a>";
            echo "</div>";

            $result = $conn->query("SELECT * FROM " . $table_name);
            if ($result && $result->num_rows > 0) {
                echo "<table><tr>";
                $fields = $result->fetch_fields();
                foreach ($fields as $field) {
                    echo "<th>" . htmlspecialchars($field->name) . "</th>";
                }
                echo "<th>Actions</th></tr>";

                while($row = $result->fetch_assoc()) {
                    echo "<tr>";
                    foreach ($row as $data) {
                        echo "<td>" . htmlspecialchars($data ?? 'NULL') . "</td>";
                    }
                    echo "<td class='action-links'><a href='?action=edit&table=$table_name&id={$row['id']}' class='btn btn-edit'>Modifier</a></td>";
                    echo "</tr>";
                }
                echo "</table>";
            } else {
                echo "<p>Aucune donnée.</p>";
            }
        }

        function display_form($conn, $table, $id = 0) {
            $is_edit = $id > 0;
            $data = [];
            if ($is_edit) {
                $stmt = $conn->prepare("SELECT * FROM $table WHERE id = ?");
                $stmt->bind_param("i", $id);
                $stmt->execute();
                $result = $stmt->get_result();
                $data = $result->fetch_assoc();
            }

            echo "<h2>" . ($is_edit ? "Modifier" : "Ajouter") . " : " . ucfirst($table) . "</h2>";
            echo "<form method='POST' action='index.php'>";
            echo "<input type='hidden' name='action' value='" . ($is_edit ? 'update_' : 'save_') . "$table'>";
            if ($is_edit) {
                echo "<input type='hidden' name='id' value='$id'>";
            }

            // Générer les champs du formulaire
            switch ($table) {
                case 'chauffeur':
                    generate_input('nom', 'Nom', $data['nom'] ?? '');
                    generate_input('immatriculation', 'Immatriculation', $data['immatriculation'] ?? '');
                    generate_input('vehicule', 'Véhicule', $data['vehicule'] ?? '');
                    generate_select('statut', 'Statut', ['disponible', 'en_course', 'indisponible'], $data['statut'] ?? 'disponible');
                    break;
                case 'client':
                    generate_input('nom', 'Nom', $data['nom'] ?? '');
                    generate_input('email', 'Email', $data['email'] ?? '');
                    generate_input('tel', 'Téléphone', $data['tel'] ?? '');
                    break;
                case 'course':
                    $clients = fetch_related($conn, 'client', 'id', 'nom');
                    $chauffeurs = fetch_related($conn, 'chauffeur', 'id', 'nom');
                    generate_select('client_id', 'Client', $clients, $data['client_id'] ?? 0);
                    generate_select('chauffeur_id', 'Chauffeur', $chauffeurs, $data['chauffeur_id'] ?? 0);
                    generate_input('point_depart', 'Point de départ', $data['point_depart'] ?? '');
                    generate_input('point_arrivee', 'Point d\'arrivée', $data['point_arrivee'] ?? '');
                    generate_input('date_heure_depart', 'Date Départ', $data['date_heure_depart'] ?? '', 'datetime-local');
                    generate_input('date_heure_arrivee', 'Date Arrivée', $data['date_heure_arrivee'] ?? '', 'datetime-local');
                    generate_input('tarif', 'Tarif', $data['tarif'] ?? '0.00', 'number');
                    generate_select('status', 'Statut', ['demandee', 'en_cours', 'terminee', 'annulee'], $data['status'] ?? 'demandee');
                    break;
            }

            echo "<button type='submit' class='btn btn-submit'>" . ($is_edit ? "Mettre à jour" : "Enregistrer") . "</button>";
            echo "</form>";
        }

        function generate_input($name, $label, $value, $type = 'text') {
            $step = ($type === 'number') ? 'step="0.01"' : '';
            echo "<div class='form-group'>";
            echo "<label for='$name'>" . htmlspecialchars($label) . "</label>";
            echo "<input type='$type' id='$name' name='$name' value='" . htmlspecialchars($value) . "' $step required>";
            echo "</div>";
        }

        function generate_select($name, $label, $options, $selected_value) {
            echo "<div class='form-group'>";
            echo "<label for='$name'>" . htmlspecialchars($label) . "</label>";
            echo "<select id='$name' name='$name' required>";
            $is_assoc = array_keys($options) !== range(0, count($options) - 1);
            foreach ($options as $key => $value) {
                $option_value = $is_assoc ? $key : $value;
                $option_text = $value;
                $selected = ($option_value == $selected_value) ? 'selected' : '';
                echo "<option value='" . htmlspecialchars($option_value) . "' $selected>" . htmlspecialchars($option_text) . "</option>";
            }
            echo "</select>";
            echo "</div>";
        }

        function fetch_related($conn, $table, $key_col, $val_col) {
            $options = [];
            $sql = "SELECT $key_col, $val_col FROM $table ORDER BY $val_col ASC";
            $result = $conn->query($sql);
            if ($result) {
                while ($row = $result->fetch_assoc()) {
                    $options[$row[$key_col]] = $row[$val_col];
                }
            }
            return $options;
        }

        $conn->close();
        ?>
    </div>
</body>
</html>
