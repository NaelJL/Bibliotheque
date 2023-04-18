<?php
session_start();
require 'head.php';
require 'cookie_handler.php';
?>

<!-- Si l'utilisateurice est connecté-e et le cookie time valide -->
<?php
// if ($_SESSION['user'] && isset($_COOKIE[$cookie_name]) && time() < $_COOKIE[$cookie_name]) :
if ($_SESSION['user']->admin == 1) :


    $pdo = new PDO('sqlite:database.sqlite', null, null, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ
    ]);

    // récupérer les informations du livre dans la variable de session
    $book = unserialize($_SESSION['current-book']);
    $title = $book->title;
    $author = $book->author;

    // fonction générale pour les mises à jour
    function updateField($post, $field)
    {
        global $max_length;
        global $pdo;
        global $title;
        global $author;

        if (isset($_POST[$post]) && !empty($_POST[$post]) && strlen($_POST[$post]) < $max_length) {
            $value = trim(htmlspecialchars($_POST[$post]));

            $query = $pdo->prepare("UPDATE books SET $field = :value WHERE title = :title AND author = :author");
            $query->execute([
                'value' => $value,
                'title' => $title,
                'author' => $author
            ]);

            // supprimer la variable qui stockait les informations du livre
            unset($_SESSION['current-book']);
            // revenir sur la page avec un message de réussite
            Header('location:admin-modif-book.php?message=Validation+successful');
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


    // rendre le livre indisponible
    if (isset($_POST['available'])) {
        $available_string = trim(htmlspecialchars($_POST['available']));
        $available = intval($available_string);
        if (is_int($available)) {
            updateField('available', $available);
        }
    }

    // supprimer définitivement le livre
    if (isset($_POST['delete'])) {
        $delete_string = trim(htmlspecialchars($_POST['delete']));
        $delete = intval($delete_string);
        if (is_int($delete) && $delete === 1) {
            $delete = $pdo->prepare('DELETE FROM books WHERE title = :title AND author = :author');
            $delete->execute([
                'title' => $title,
                'author' => $author
            ]);

            unset($_SESSION['current-book']);
            Header('location:admin-modif-book.php?message=Validation+successful');
        }
    }
?>


    <!-- Si l'utilisateurice n'est pas connecté-e -->
<?php else : ?>

    <nav>
        <p><a href="index.php">Page d'accueil</a></p>
    </nav>

    <h1>Vous devez être administrateur-ice pour avoir accès à cette page</h1>

<?php endif ?>

</body>

</html>