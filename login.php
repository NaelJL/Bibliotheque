<?php
session_start();
require 'head.php';
?>

<nav>
    <p><a href="index.php">Revenir à la page d'accueil</a></p>
</nav>

<h1>Me connecter</h1>
<form action="" method="POST">
    <div>
        <label for="email">Entrez votre adresse email</label>
        <input type="email" name="email" id="email" value="" required />
    </div>
    <div>
        <label for="password">Entrez votre mot de passe</label>
        <input type="password" name="password" id="password" value="" required />
        <a href="forgotten-password.php" class="forgotten-password-img"><img src="./assets/information.png" alt="mot de passe oublié" style="width: 20px; height: 20px" /></a>
    </div>
    <div>
        <img src="captcha.php" alt="captcha" style="margin-right: 10px" class="captcha" />
        <input type="text" name="captcha" placeholder="Entrez la captcha" required />
    </div>
    <input type="submit" value="Connexion" />
</form>

<?php
$pdo = new PDO('sqlite:database.sqlite', null, null, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ
]);

$error = null;

try {
    // si les variables sont valides
    if (isset($_POST['email']) && isset($_POST['password']) && isset($_POST['captcha'])) {

        // si elles ne sont pas vides
        if (!empty($_POST['email']) && !empty($_POST['password']) && !empty($_POST['captcha'])) {

            // si elles ne sont pas trop longues
            if (strlen($_POST['email']) < $max_length && strlen($_POST['password']) < $max_length && strlen($_POST['captcha']) < $max_length) {

                // si la captcha est correcte
                if ($_POST['captcha'] == $_SESSION['captcha']) {

                    // nettoyer les variables
                    if (filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
                        $email = $_POST['email'];
                    } else {
                        echo "<p class='error'>Adresse email invalide</p>";
                        exit;
                    }
                    $password = htmlspecialchars($_POST['password']);

                    // Recherche de l'utilisateurice correspondant à l'email
                    $query = $pdo->prepare('SELECT * FROM accounts WHERE email = :email');
                    $query->execute([
                        'email' => $email,
                    ]);
                    $user = $query->fetch();

                    if ($user) {

                        // comparer les mots de passe 
                        if (password_verify($password, $user->password)) {

                            // Si l'utilisateur existe on vérifie que son compte est confirmé
                            if ($user->confirmedAccount == 1) {

                                // On démarre la session et on stocke ses informations
                                session_start();
                                $_SESSION['user'] = $user;

                                // Rediriger l'utilisateur vers la page de compte
                                header('Location: my-account.php');
                                exit;
                            } else {
                                echo "<p class='error'>Vous devez activer votre compte par email</p>";
                            }
                        } else {
                            echo "<p class='error'>Identifiants incorrects</p>";
                        }
                    } else {
                        echo "<p class='error'>Identifiants incorrects</p>";
                    }
                } else {
                    echo "<p class='error'>Captcha invalide</p>";
                }
            } else {
                echo "<p class='error'>Au moins l'un des éléments contient trop de caractères</p>";
            }
        } else {
            echo "<p class='error'>Veuillez remplir tous les champs</p>";
        }
    }
} catch (PDOException $e) {
    $error = $e->getMessage();
}
?>

</body>

</html>