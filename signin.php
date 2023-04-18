<?php
session_start();
require 'head.php';
require 'cookie_handler.php';

require './vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;
?>

<nav>
    <p><a href="index.php">Revenir à la page d'accueil</a></p>
</nav>

<h1>Créer un compte</h1>

<form action="" method="POST">
    <div>
        <label for="name">Votre prénom</label>
        <input type="text" name="name" id="name" value="" maxlenght="20" required />
    </div>
    <div>
        <label for="surname">Votre nom</label>
        <input type="text" name="surname" id="surname" value="" maxlenght="20" required />
    </div>
    <div>
        <label for="email">Votre adresse email</label>
        <input type="email" name="email" id="email" value="" required />
    </div>
    <div>
        <label for="password">Un mot de passe</label>
        <input type="password" name="password" id="password" value="" required />
    </div>
    <div>
        <label for="password2">Confirmez le mot de passe</label>
        <input type="password" name="password2" id="password2" value="" required />
    </div>
    <div>
        <img src="captcha.php" alt="captcha" style="margin-right: 10px" class="captcha" />
        <input type="text" name="captcha" placeholder="Entrez la captcha" required />
    </div>
    <input type="submit" value="Valider mon inscription" />
</form>

<?php
$pdo = new PDO('sqlite:database.sqlite', null, null, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ
]);

$error = null;

try {
    // si toutes les variables existent
    if (isset($_POST['name']) && isset($_POST['surname']) && isset($_POST['email']) && isset($_POST['password']) && isset($_POST['password2']) && isset($_POST['captcha'])) {

        // si elles ne sont pas vides 
        if (!empty($_POST['name']) && !empty($_POST['surname']) && !empty($_POST['email']) && !empty($_POST['password']) && !empty($_POST['password2']) && !empty($_POST['captcha'])) {

            // si les strings ne sont pas trop longues
            if (strlen($_POST['name']) < $max_length && strlen($_POST['surname']) < $max_length && strlen($_POST['email']) < $max_length && strlen($_POST['password']) < $max_length && strlen($_POST['captcha']) < $max_length) {

                // si la captcha est correcte
                if ($_POST['captcha'] == $_SESSION['captcha']) {

                    // protéger des injections
                    $name = trim(htmlspecialchars($_POST['name']));
                    $surname = trim(htmlspecialchars($_POST['surname']));
                    if (filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
                        $email = $_POST['email'];
                    } else {
                        echo "<p class='error'>Adresse email invalide</p>";
                        exit;
                    }

                    // si les mots de passe sont identiques
                    if ($_POST['password'] === $_POST['password2']) {

                        // hacher le mot de passe
                        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

                        // s'il n'y a pas de doublon d'email ou de personne
                        $query = $pdo->prepare('SELECT * FROM accounts WHERE name = :name AND surname = :surname OR email = :email');
                        $query->execute([
                            'name' => $name,
                            'surname' => $surname,
                            'email' => $email
                        ]);
                        $result = $query->fetch();

                        if ($result === false) {

                            // insérer les données récupérées dans la table avec une clé de confirmation par email
                            $confirmationKey = mt_rand(100000000, 999999999);
                            $confirmedAccount = 0;

                            $query = $pdo->prepare('INSERT INTO accounts (name, surname, email, password, confirmationKey, confirmedAccount, admin) VALUES (:name, :surname, :email, :password, :confirmationKey, :confirmedAccount, :admin)');
                            $query->execute([
                                'name' => $name,
                                'surname' => $surname,
                                'email' => $email,
                                'password' => $password,
                                'confirmationKey' => $confirmationKey,
                                'confirmedAccount' => $confirmedAccount,
                                'admin' => 0
                            ]);
                            echo '<p class="success"> Votre compte a bien été enregistré<br />Vous devez l\'activer par mail pour pouvoir vous connecter</p>';

                            // envoyer l'email de confirmation de compte
                            $mail = new PHPMailer(true);

                            try {
                                // configuration du serveur
                                $mail->SMTPDebug = SMTP::DEBUG_SERVER;
                                $mail->isSMTP();
                                $mail->Host       = 'localhost';
                                $mail->SMTPAuth   = false;
                                // si true // $mail->Username   = '@.com'; // $mail->Password   = '';
                                // $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
                                // $mail->Port       = 587;

                                // configuration envoyeur envoyé
                                $mail->setFrom('blibliothequeparticipative@gmail.com', 'NJL');
                                $mail->addAddress($email);

                                // configuration du message
                                $mail->isHTML(true);
                                $mail->Subject = 'Confirmation de compte';
                                $mail->Body    = "
                            <html>
                                <body>
                                    <h2>Merci de votre inscription à la bibliothèque participative, . $name . !</h2>
                                    <p>Vous devez simplement suivre 
                                        <a href='http://localhost:8888/Bibliotheque/confirmation-account.php?name= . urlencode($name) . &key= . urlencode($confirmationKey)'>
                                        ce lien
                                        </a>
                                    pour pouvoir proposer et emprunter des livres!
                                    </p>
                                </body>
                            </html>
                            ";

                                $mail->send();
                            } catch (Exception $e) {
                                echo $mail->ErrorInfo;
                            }
                        } else {
                            echo "<p class='error'>Ce nom ou cet email sont déjà utilisés</p>";
                        }
                    } else {
                        echo "<p class='error'>Les mots de passe ne sont pas identiques</p>";
                    }
                } else {
                    echo "<p class='error'>Captcha invalide</p>";
                }
            } else {
                echo "<p class='error'>Au moins l'un des éléments contient trop de caractères</p>";
            }
        } else {
            echo "<p class='error'>Vous devez remplir tous les champs</p>";
        }
    }
} catch (PDOException $e) {
    $error = $e->getMessage();
}
?>
</body>

</html>