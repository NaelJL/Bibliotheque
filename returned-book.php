<?php
session_start();
require 'head.php';
require 'cookie_handler.php';
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

        if (isset($_POST['returned'])) {
            if (!empty($_POST['returned'])) {

                // vérifier que la variable contient 3 caractères maximum et la convertir en nombre
                if (strlen($_POST['returned']) <= 3) {
                    $id_string = trim(htmlspecialchars($_POST['returned']));
                    $id = intval($id_string);

                    // Récupérer le book_id dans la table borrowed_books pour faire le lien avec la table books
                    $find = $pdo->prepare('SELECT * FROM borrowed_books WHERE id = :id');
                    $find->execute([
                        'id' => $id
                    ]);
                    $book_id_obj = $find->fetch();
                    $book_id = intval($book_id_obj->book_id);

                    if (!empty($book_id)) {

                        // Supprimer le livre de la table borrowed_books comme il n'est plus emprunté
                        $delete = $pdo->prepare('DELETE FROM borrowed_books WHERE id = :id');
                        $delete->execute([
                            'id' => $id
                        ]);

                        // Mettre à jour la table books pour afficher le livre de nouveau comme disponible
                        $update = $pdo->prepare('UPDATE books SET available = 1 WHERE id = :id');
                        $update->execute([
                            'id' => $book_id
                        ]);

                        // Revenir sur la page des livres prêtés
                        header('Location: books-shared.php');
                    } else {
                        echo "<p class='error'>Problème avec le livre</p>";
                    }
                }
            } else {
                echo "<p class='error'>Problème avec le retour</p>";
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