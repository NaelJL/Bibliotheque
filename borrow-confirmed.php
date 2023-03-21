<?php
session_start();
require 'head.php';
require 'cookie_handler.php';
?>

<!-- Si l'utilisateurice est connecté-e et le cookie time valide -->
<?php
if ($_SESSION['user'] && isset($_COOKIE[$cookie_name]) && time() < $_COOKIE[$cookie_name]) :

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

            // rentrer le livre dans la table borrowed_books
            $date_borrowed = date('Y-m-d');
            $date_return = date('Y-m-d', strtotime('+3 weeks', strtotime($date_borrowed)));
            $extension = 0;

            $record = $pdo->prepare('INSERT INTO borrowed_books (date_borrowed, date_return, extension, book_id, email) VALUES (:date_borrowed, :date_return, :extension, :book_id, :email)');
            $record->execute([
                'date_borrowed' => $date_borrowed,
                'date_return' => $date_return,
                'extension' => $extension,
                'book_id' => $id_book,
                'email' => $email
            ]);

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