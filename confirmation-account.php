<?php
session_start();
require 'head.php';
?>

<nav>
    <p><a href="index.php">Aller à la page d'accueil</a></p>
    <p><a href="login.php">Se connecter</a></p>
</nav>

<?php
$pdo = new PDO('sqlite:database.sqlite', null, null, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ
]);

$error = null;

try {
    if (isset($_GET['name']) && isset($_GET['key'])) {

        if (!empty($_GET['name']) && !empty($_GET['key'])) {
            $name = htmlspecialchars(urldecode($_GET['name']));
            $key = htmlspecialchars(urldecode($_GET['key']));

            // aller vérifier la clé de confirmation
            $query = $pdo->prepare('SELECT * FROM accounts WHERE name = :name AND confirmationKey = :confirmationKey');
            $query->execute([
                'name' => $name,
                'confirmationKey' => $key
            ]);
            $user = $query->fetch();

            // confirmer le compte si ce n'est pas déjà fait
            if ($user->confirmedAccount == 0) {
                $update = $pdo->prepare('UPDATE accounts SET confirmedAccount = 1 WHERE name = :name AND confirmationKey = :confirmationKey');
                $update->execute([
                    'name' => $name,
                    'confirmationKey' => $key
                ]);
                echo "<p class='success'>Votre compte a bien été confirmé</p>";
            } else {
                echo "<p class='success'>Votre compte a déjà été confirmé</p>";
            }
        }
    } else {
        echo "<h1>Vous ne pouvez pas avoir accès à cette page</h1>";
    }
} catch (PDOException $e) {
    $error = $e->getMessage();
}
?>