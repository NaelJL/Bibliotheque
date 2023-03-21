<?php
session_start();
require 'head.php';
require 'cookie_handler.php';
?>

<?php
$pdo = new PDO('sqlite:database.sqlite', null, null, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ
]);

$error = null;

try {

    if (isset($_GET['email'])) :
?>

        <nav>
            <p><a href="login.php">Aller à la page de connexion</a></p>
            <p><a href="index.php">Aller à la page d'accueil</a></p>
        </nav>

        <h1>Changement de mot de passe 1 / 2</h1>

        <form action="" method="POST">
            <div>
                <label for="code">Entrez le code de vérification reçu par email</label>
                <input type="text" name="code" id="code" value="" required />
            </div>
            <input type="submit" value="Envoyer" />
        </form>

        <?php
        // récupérer l'email et le stocker dans une session
        if (!empty($_GET['email'])) {
            $email = urldecode(htmlspecialchars($_GET['email']));
            $_SESSION['email'] = $email;

            if (isset($_POST['code'])) {
                if (!empty($_POST['code'])) {
                    $code = htmlspecialchars($_POST['code']);

                    // récupérer les données dans la table recupCode
                    $query = $pdo->prepare('SELECT * FROM recupCode WHERE email = :email AND code = :code');
                    $query->execute([
                        'email' => $email,
                        'code' => $code
                    ]);
                    $user = $query->fetch();

                    // vérifier que le code donné et celui de la table correspondent
                    if ($user && $user->code === $code) {

                        // supprimer la ligne de la table recupCode et rediriger vers le changement de password 
                        $delete = $pdo->prepare('DELETE FROM recupCode WHERE code = :code');
                        $delete->execute([
                            'code' => $code
                        ]);
                        header('Location:change-forgotten-password.php');
                    } else {
                        echo "<p class='error'>Code invalide</p>";
                    }
                } else {
                    echo "<p class='error'>Vous devez rentrer un code à 6 chiffres</p>";
                }
            }
        }
        ?>
    <?php else : ?>

        <nav>
            <p><a href="index.php">Page d'accueil</a></p>
        </nav>

        <h1>Vous ne pouvez pas avoir accès à cette page</h1>

<?php endif;
} catch (PDOException $e) {
    $error = $e->getMessage();
}
?>