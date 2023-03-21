<?php
session_start();
require 'head.php';
require 'cookie_handler.php';
?>

<!-- Si l'utilisateurice est connecté-e et le cookie time valide -->
<?php
if ($_SESSION['user'] && isset($_COOKIE[$cookie_name]) && time() < $_COOKIE[$cookie_name]) :
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
                    $search = $pdo->prepare('SELECT date_return FROM borrowed_books WHERE id = :id');
                    $search->execute([
                        'id' => $id
                    ]);
                    $date_return = $search->fetch();

                    $date_return_format = $date_return->date_return;
                    $date_return_update = date('Y-m-d', strtotime('+3 weeks', strtotime($date_return_format)));

                    // mettre à jour la table borrowed_books : plus de prolongement possible, 3 semaines supplémentaires ajoutées
                    $query = $pdo->prepare('UPDATE borrowed_books SET extension = 1, date_return = :date_return WHERE id = :id');
                    $query->execute([
                        'id' => $id,
                        'date_return' => $date_return_update
                    ]);
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