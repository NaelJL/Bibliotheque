<?php
session_start();
require 'head.php';
require 'cookie_handler.php';

require './vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;
?>

<!-- Si l'utilisateurice est connecté-e et le cookie time valide -->
<?php
// if ($_SESSION['user'] && isset($_COOKIE[$cookie_name]) && time() < $_COOKIE[$cookie_name]) :
if ($_SESSION['user']) :
?>

    <?php

    $pdo = new PDO('sqlite:database.sqlite', null, null, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ
    ]);

    $error = null;

    try {

        if (isset($_POST['extension'])) {
            if (!empty($_POST['extension'])) {

                // vérifier que la variable contient un caractère et la convertir en nombre
                if (strlen($_POST['extension']) == 1) {
                    $id = trim(htmlspecialchars($_POST['extension']));

                    // aller chercher la date de retour initiale
                    $search = $pdo->prepare('SELECT date_return FROM borrowed_books WHERE book_id = :book_id');
                    $search->execute([
                        'book_id' => $book_id
                    ]);
                    $date_return = $search->fetch();

                    $date_return_format = $date_return->date_return;
                    $date_return_update = date('Y-m-d', strtotime('+3 weeks', strtotime($date_return_format)));

                    // mettre à jour la table borrowed_books : plus de prolongement possible, 3 semaines supplémentaires ajoutées
                    $query = $pdo->prepare('UPDATE borrowed_books SET extension = 1, date_return = :date_return WHERE book_id = :book_id');
                    $query->execute([
                        'book_id' => $book_id,
                        'date_return' => $date_return_update
                    ]);

                    // récupérer l'email de la personne qui prête grâce à l'id
                    $email = $pdo->prepare('SELECT email FROM books WHERE id = :id');
                    $email->execute([
                        'id' => $book_id
                    ]);
                    $email_ok = $email->fetch();

                    // envoyer un email à la personne qui prête le livre
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
                        $mail->addAddress($email_ok);

                        // configuration du message
                        $mail->isHTML(true);
                        $mail->Subject = 'Un de vos livres a été prolongé';
                        $mail->Body    = "
                                <html>
                                    <body>
                                        <h2>Que faire maintenant ?</h2>
                                        <p>
                                            Vous pouvez vous connecter pour retrouver toutes les informations sur vos livres prêtés.
                                        </p>
                                    </body>
                                </html>
                                ";

                        $mail->send();
                    } catch (Exception $e) {
                        echo $mail->ErrorInfo;
                    }

                    // retourner sur la page des livres en cours
                    Header('Location: my-current-books.php');
                }
            } else {
                echo "<p class='error'>Problème avec le prolongement</p>";
            }
        }
    } catch (PDOException $e) {
        $error = $e->getMessage();
    }
    ?>

    <!-- Si l'utilisateurice n'est pas connecté-e -->
<?php else : ?>

    <nav>
        <p><a href="index.php">Page d'accueil</a></p>
    </nav>

    <h1>Vous devez être connecté.e pour avoir accès à cette page</h1>

<?php endif ?>

</body>

</html>