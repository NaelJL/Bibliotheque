<?php
session_start();
require 'head.php';

require './vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;
?>

<!-- Si l'utilisateurice est connecté-e et le cookie time valide -->
<?php
// if ($_SESSION['user'] && isset($_COOKIE[$cookie_name]) && time() < $_COOKIE[$cookie_name]) :
if ($_SESSION['user']) :

    $pdo = new PDO('sqlite:database.sqlite', null, null, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ
    ]);

    $error = null;

    try {
        $id_book = $_SESSION['id-book-borrow'];
        $email = $_SESSION['user']->email;

        if ($id_book && $email) {

            // indiquer que le livre n'est plus disponible dans la table books
            $query = $pdo->prepare('UPDATE books SET available = 0 WHERE id = :id');
            $query->execute([
                'id' => $id_book
            ]);

            // récupérer l'id de la personne qui emprunte grâce à son email
            $person_borrowing = $pdo->prepare('SELECT * FROM accounts WHERE email = :email');
            $person_borrowing->execute([
                'email' => $email
            ]);
            $person_borrowing_profile = $person_borrowing->fetch();

            if ($person_borrowing_profile) {
                $id_person_borrowing = $person_borrowing_profile->id;

                // rentrer le livre dans la table borrowed_books
                $date_borrowed = date('Y-m-d');
                $date_return = date('Y-m-d', strtotime('+3 weeks', strtotime($date_borrowed)));
                $extension = 0;

                $record = $pdo->prepare('INSERT INTO borrowed_books (date_borrowed, date_return, extension, book_id, id_person_borrowing) VALUES (:date_borrowed, :date_return, :extension, :book_id, :id_person_borrowing)');
                $record->execute([
                    'date_borrowed' => $date_borrowed,
                    'date_return' => $date_return,
                    'extension' => $extension,
                    'book_id' => $id_book,
                    'id_person_borrowing' => $id_person_borrowing
                ]);
            }

            // récupérer l'email de la personne qui prête grâce à l'id
            // $email = $pdo->prepare('SELECT email FROM books WHERE id = :id');
            // $email->execute([
            //     'id' => $id_book
            // ]);
            // $email_ok = $email->fetch();

            // envoyer un email à la personne qui prête le livre
            // $mail = new PHPMailer(true);

            // try {
            //     // configuration du serveur
            //     $mail->SMTPDebug = SMTP::DEBUG_SERVER;
            //     $mail->isSMTP();
            //     $mail->Host       = 'localhost';
            //     $mail->SMTPAuth   = false;
            //     // si true // $mail->Username   = '@.com'; // $mail->Password   = '';
            //     // $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            //     // $mail->Port       = 587;

            //     // configuration envoyeur envoyé
            //     $mail->setFrom('blibliothequeparticipative@gmail.com', 'NJL');
            //     $mail->addAddress($email_ok);

            //     // configuration du message
            //     $mail->isHTML(true);
            //     $mail->Subject = 'Un de vos livres a été emprunté';
            //     $mail->Body    = "
            //             <html>
            //                 <body>
            //                     <h2>Que faire maintenant ?</h2>
            //                     <p>
            //                         Attendez que la personne qui vous a emprunté le livre vous contacte par email.
            //                     </p>
            //                 </body>
            //             </html>
            //             ";

            //     $mail->send();
            // } catch (Exception $e) {
            //     echo $mail->ErrorInfo;
            // }

            // supprimer la session id-book pour ne pas multiplier l'emprunt
            unset($_SESSION['id-book']);

            // retourner sur la page des livres empruntés
            header('Location:books-borrowed.php');
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