<?php
session_start();
require 'head.php';
require 'cookie_handler.php';
?>

<nav>
    <p><a href="login.php">Revenir à la page de connexion</a></p>
    <p><a href="index.php">Revenir à la page d'accueil</a></p>
</nav>

<h1>Mot de passe oublié</h1>

<form action="" method="POST">
    <div>
        <label for="email">Entrez votre adresse email</label>
        <input type="email" name="email" id="email" value="" required />
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

    if (isset($_POST['email'])) {
        if (!empty($_POST['email'])) {
            if (filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
                $recupEmail = $_POST['email'];

                // vérifier que l'utilisateurice existe
                $query = $pdo->prepare('SELECT id FROM accounts WHERE email = :email');
                $query->execute([
                    'email' => $recupEmail
                ]);
                $recupUser = $query->fetch();

                // s'iel existe, enregistrer le code et l'email dans la session
                if ($recupUser) {
                    $_SESSION['recupEmail'] = $recupEmail;
                    $recupCode = random_int(100000, 999999);
                    $_SESSION['recupCode'] = $recupCode;

                    // préparer la table recupCode
                    $mailRecupExist = $pdo->prepare('SELECT id FROM recupCode WHERE email = :email');
                    $mailRecupExist->execute([
                        'email' => $recupEmail
                    ]);
                    $mailExist = $mailRecupExist->fetch();

                    // si l'email est déjà dans la table recupCode, alors le code est mis à jour
                    if ($mailExist) {
                        $recup = $pdo->prepare('UPDATE recupCode SET code = :code WHERE email = :email');
                        $recup->execute([
                            'code' => $recupCode,
                            'email' => $recupEmail
                        ]);
                        // sinon l'email et le code sont entrés dans la table
                    } else {
                        $recup = $pdo->prepare('INSERT INTO recupCode (email, code) VALUES (:email, :code)');
                        $recup->execute([
                            'code' => $recupCode,
                            'email' => $recupEmail
                        ]);
                    }

                    // envoyer l'email de modification de mot de passe
                    // $header = 'MIME-Version: 1.0\r\n';
                    // $header .= 'From:"Bibliotheque"<support-bibliotheque@protonmail.com>' . "\n";
                    // $header .= 'Content-Type:text/html; charset="uft-8"' . "\n";
                    // $header .= 'Content-Transfer-Encoding: 8bit';

                    // $message = "
                    // <html>
                    //     <body>
                    //         <h2>Pour vous connecter!</h2>
                    //          <p>Votre code de récupération : $recupCode
                    //         <p>Vous devez simplement suivre 
                    //             <a href='http://localhost:8888/Bibliotheque/change-forgotten-password.php?email= . urlencode($recupEmail)'>
                    //             ce lien
                    //             </a>
                    //         pour pouvoir modifier votre mot de passe.
                    //         </p>
                    //     </body>
                    // </html>
                    // ";

                    // mail($recupEmail, 'Mot de passe oublié', $message, $header);
                }
                echo "<p class='success'>Si l'adresse email est enregistrée, vous allez recevoir un message.</p>";

                // Une fois le mail envoyé, la page est basculée sur la modification du mot de passe
                header('Location:code-forgotten-password.php');
            } else {
                echo "<p class='error'>Adresse email invalide</p>";
            }
        } else {
            echo "<p class='error'>Veuillez entrer votre adresse email.</p>";
        }
    }
} catch (PDOException $e) {
    $error = $e->getMessage();
}
?>