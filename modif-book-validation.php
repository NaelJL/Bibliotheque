<?php
session_start();
require 'head.php';
?>

<!-- Si l'utilisateurice est connecté-e et le cookie time valide -->
<?php
// if ($_SESSION['user'] && isset($_COOKIE[$cookie_name]) && time() < $_COOKIE[$cookie_name]) :
if ($_SESSION['user']->admin == 1) :


    $pdo = new PDO('sqlite:database.sqlite', null, null, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ
    ]);

    // récupérer l'id du livre dans la variable de session
    $id_book = $_SESSION['id-modif-book'];

    // fonction générale pour les mises à jour
    function updateField($post, $field)
    {
        global $max_length;
        global $pdo;
        global $id_book;

        if (isset($_POST[$post]) && !empty($_POST[$post]) && strlen($_POST[$post]) < $max_length) {
            $value = trim(htmlspecialchars($_POST[$post]));

            $query = $pdo->prepare("UPDATE books SET $field = :value WHERE id = :id_book");
            $query->execute([
                'value' => $value,
                'id_book' => $id_book,
            ]);

            // supprimer la variable qui stockait les informations du livre
            unset($_SESSION['id-modif-book']);
            // revenir sur la page des livres prêtés
            Header('location:books-shared.php');
        }
    }

    // modification des éléments
    updateField('newTitle', 'title');
    updateField('newAuthor', 'author');
    updateField('newTranslator', 'translator');
    updateField('newCollection', 'collection');
    updateField('newEdition', 'edition');
    updateField('newPublication', 'publication');
    updateField('newPages', 'pages');

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