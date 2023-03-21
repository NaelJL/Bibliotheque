<?php
session_start();
require 'head.php';
require 'cookie_handler.php';
?>

<?php
if ($_SESSION['email']) :
?>

    <nav>
        <p><a href="login.php">Aller à la page de connexion</a></p>
        <p><a href="index.php">Aller à la page d'accueil</a></p>
    </nav>

    <h1>Changement de mot de passe 2 / 2</h1>

    <form action="" method="POST">
        <div>
            <label for="password">Entrez votre nouveau mot de passe</label>
            <input type="password" name="password" id="password" value="" required />
        </div>
        <input type="submit" value="Envoyer" />
    </form>

    <?php
    $pdo = new PDO('sqlite:database.sqlite', null, null, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ
    ]);

    $error = null;

    try {

        if (isset($_POST['password'])) {
            if (!empty($_POST['password'])) {
                $newPassword = htmlspecialchars($_POST['password']);
                $email = $_SESSION['email'];

                // mettre à jour le mot de passe dans la table accounts
                $query = $pdo->prepare('UPDATE accounts SET password = :password WHERE email = :email');
                $query->execute([
                    'password' => $newPassword,
                    'email' => $email
                ]);

                // rediriger vers la page log in
                header('Location:login.php');
            } else {
                echo "<p class='error'>Veuillez remplir le champ.</p>";
            }
        }
    } catch (PDOException $e) {
        $error = $e->getMessage();
    }
    ?>

<?php else : ?>

    <nav>
        <p><a href="index.php">Page d'accueil</a></p>
    </nav>

    <h1>Vous ne pouvez pas avoir accès à cette page</h1>

<?php endif ?>

</body>

</html>