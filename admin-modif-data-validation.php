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

    $person_email = $_SESSION['old-email-modif'];

    // fonction pour les mises à jour
    function updateField($field, $value)
    {
        global $pdo;
        global $person_email;

        $query = $pdo->prepare("UPDATE accounts SET $field = :value WHERE email = :email");
        $query->execute([
            'value' => $value,
            'email' => $person_email
        ]);

        // supprimer la variable qui stockait l'email
        unset($_SESSION['old-email-modif']);
        // revenir sur la page avec un message de réussite
        Header('location:admin-modif-data.php?message=Validation+successful');
    }

    // modification de l'email
    if (isset($_POST['newEmail'])) {
        if (!empty($_POST['newEmail'])) {
            if (strlen($_POST['newEmail']) < $max_length) {
                if (filter_var($_POST['newEmail'], FILTER_VALIDATE_EMAIL)) {
                    $newEmail = $_POST['newEmail'];
                    updateField('email', $newEmail);
                }
            }
        }
    }

    // modification du prénom
    if (isset($_POST['newName'])) {
        if (!empty($_POST['newName'])) {
            if (strlen($_POST['newName']) < $max_length) {
                $newName = trim(htmlspecialchars($_POST['newName']));
                updateField('name', $newName);
            }
        }
    }

    // modification du nom de famille
    if (isset($_POST['newSurname'])) {
        if (!empty($_POST['newSurname'])) {
            if (strlen($_POST['newSurname']) < $max_length) {
                $newSurname = trim(htmlspecialchars($_POST['newSurname']));
                updateField('surname', $newSurname);
            }
        }
    }

    // activer ou désactiver le compte
    if (isset($_POST['confirmation'])) {
        $confirmationString = htmlspecialchars($_POST['confirmation']);
        $confirmation = intval($confirmationString);
        if ($confirmation === 1) {
            updateField('confirmedAccount', 1);
        } elseif ($confirmation === 0) {
            updateField('confirmedAccount', 0);
        }
    }

    // passer le compte en administrateurice ou simple utilisateurice
    if (isset($_POST['admin'])) {
        $adminString = htmlspecialchars($_POST['admin']);
        $admin = intval($adminString);
        if (is_int($admin)) {
            if ($admin === 1) {
                updateField('admin', 1);
            } elseif ($admin === 0) {
                updateField('admin', 0);
            }
        }
    }

    // supprimer le compte et les livres associés
    if (isset($_POST['delete'])) {
        $deleteString = htmlspecialchars($_POST['delete']);
        $delete = intval($deleteString);

        if (is_int($delete)) {
            if ($delete == 1) {

                global $pdo;
                global $person_email;

                $deleteAccount = $pdo->prepare('DELETE * FROM accounts WHERE email = :email');
                $deleteAccount->execute([
                    'email' => $person_email
                ]);
                $deleteBooks = $pdo->prepare('DELETE * FROM books WHERE email = :email');
                $deleteBooks->execute([
                    'email' => $person_email
                ]);
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