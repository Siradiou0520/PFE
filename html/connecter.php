<?php
session_start();
$error_message = isset($_SESSION['error_message']) ? $_SESSION['error_message'] : "";
unset($_SESSION['error_message']); // Supprimer l'erreur après l'affichage
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prise de RDV - Carte de Séjour</title>
    <link rel="stylesheet" href="../css/style2.css">
</head>
<body>

    <main>
        <section class="login">
            <h2>Connexion</h2>
            <form id="loginForm" action="../php/login.php" method="post">
                <label for="email">Email :</label>
                <input type="email" id="email" name="email" required>
                
                <label for="password">Mot de passe :</label>
                <input type="password" id="password" name="password" required>
                
                <button type="submit">Se connecter</button>
            </form>
            <p>Vous n'avez pas de compte?</p>
            <a href="../html/inscrire.html" target="_blank">Inscrivez-vous</a>

            <!-- Message d'erreur affiché en cas d'échec de connexion -->
            <?php if (!empty($error_message)): ?>
                <p style="color: red;"><?php echo htmlspecialchars($error_message); ?></p>
            <?php endif; ?>

        </section>
    </main>

    <script src="../js/script.js"></script>
</body>
</html>

