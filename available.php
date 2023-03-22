<?php
session_start();
require 'head.php';
require 'cookie_handler.php';
?>

<!-- Si l'utilisateurice est connecté-e et le cookie time valide -->
<?php
if ($_SESSION['user']) :

    $pdo = new PDO('sqlite:database.sqlite', null, null, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ
    ]);

    $error = null;

    try {
        // // si le cookie time existe et est valide
        // if (isset($_COOKIE[$cookie_name]) && time() < $_COOKIE[$cookie_name]) {

        // pouvoir rendre le livre indisponible
        if (isset($_POST['non-available'])) {
            if (!empty($_POST['non-available'])) {

                // vérifier que la variable contient trois caractères maximum et la convertir en nombre
                if (strlen($_POST['non-available']) <= 3) {
                    $id_string = trim(htmlspecialchars($_POST['non-available']));
                    $id = intval($id_string);

                    $query = $pdo->prepare('UPDATE books SET available = 0 WHERE id = :id');
                    $query->execute([
                        'id' => $id
                    ]);

                    Header('Location:books-shared.php');
                } else {
                    echo "<p class='error'>Problème avec le livre</p>";
                }
            }

            // pouvoir rendre le livre disponible
        } elseif (isset($_POST['available'])) {
            if (!empty($_POST['available'])) {

                // vérifier que la variable contient trois caractères maximum et la convertir en nombre
                if (strlen($_POST['available']) <= 3) {
                    $id_string = trim(htmlspecialchars($_POST['available']));
                    $id = intval($id_string);

                    $query = $pdo->prepare('UPDATE books SET available = 1 WHERE id = :id');
                    $query->execute([
                        'id' => $id
                    ]);

                    Header('Location:books-shared.php');
                }
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