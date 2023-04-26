<?php
session_start();
require 'head.php';
?>

<!-- Si l'utilisateurice est connecté-e et le cookie time valide -->
<?php
// if ($_SESSION['user'] && isset($_COOKIE[$cookie_name]) && time() < $_COOKIE[$cookie_name]) :
if ($_SESSION['user']) :

    $pdo = new PDO('sqlite:database.sqlite', null, null, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ
    ]);

    // confirmer la suppression définitive d'un livre
    if (isset($_POST['delete'])) {
        if (!empty($_POST['delete'])) {

            // vérifier que la variable contient 3 caractères maximum et la convertir en nombre
            if (strlen($_POST['delete']) <= 3) {
                $id_string = trim(htmlspecialchars($_POST['delete']));
                $id = intval($id_string);

                $email = $_SESSION['user']->email;

                $person = $pdo->prepare('SELECT id FROM accounts WHERE email = :email');
                $person->execute([
                    'email' => $email
                ]);
                $result_person = $person->fetch();
                $result_id_person = $result_person->id;

                $delete = $pdo->prepare('DELETE FROM books WHERE id = :id AND id_person = :id_person');
                $delete->execute([
                    'id' => $id,
                    'id_person' => $result_id_person
                ]);

                // retourner sur la page des livres prêtés
                Header('Location:books-shared.php');
            }
        }
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