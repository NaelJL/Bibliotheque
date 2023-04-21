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

    $person_email = $_SESSION['old-email-modif'];

    // fonction générale pour les mises à jour des éléments
    function updateField($type, $post, $field)
    {
        global $max_length;
        global $pdo;
        global $person_email;

        if (isset($_POST[$post]) && strlen($_POST[$post]) < $max_length) {
            $value = trim(htmlspecialchars($_POST[$post]));

            $query = $pdo->prepare("UPDATE accounts SET $field = :value WHERE email = :email");

            if ($type == "number") {
                $numeric_value = filter_var($value, FILTER_VALIDATE_INT);
                if ($numeric_value === false) {
                    echo "<p class='error'>Erreur</p>";
                    return;
                } else {
                    $value = $numeric_value;
                }
            } elseif ($type == "email") {
                if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    echo "<p class='error'>Erreur</p>";
                    return;
                }
            }

            $query->execute([
                'value' => $value,
                'email' => $person_email
            ]);

            // supprimer la variable qui stockait l'email
            unset($_SESSION['old-email-modif']);
            // revenir sur la page avec un message de réussite
            Header('location:admin-modif-data.php?message=Validation+successful');
        }
    }

    // modification des éléments
    updateField('email', 'newEmail', 'email');
    updateField('text', 'newName', 'name');
    updateField('text', 'newSurname', 'surname');
    updateField('number', 'confirmation', 'confirmedAccount');
    updateField('number', 'admin', 'admin');

    // supprimer le compte et les livres associés
    if (isset($_POST['delete'])) {
        $deleteString = htmlspecialchars($_POST['delete']);
        $delete = intval($deleteString);

        if (is_int($delete)) {
            if ($delete === 1) {

                global $pdo;
                global $person_email;

                $deleteAccount = $pdo->prepare('DELETE FROM accounts WHERE email = :email');
                $deleteAccount->execute([
                    'email' => $person_email
                ]);
                $deleteBooks = $pdo->prepare('DELETE FROM books WHERE email = :email');
                $deleteBooks->execute([
                    'email' => $person_email
                ]);

                unset($_SESSION['old-email-modif']);
                Header('location:admin-modif-data.php?message=Validation+successful');
            }
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