<?php
session_start();
require 'head.php';
require 'cookie_handler.php';
?>

<!-- Si l'utilisateurice est connecté-e et le cookie time valide -->
<?php
if ($_SESSION['user'] && isset($_COOKIE[$cookie_name]) && time() < $_COOKIE[$cookie_name]) :
?>

    <nav>
        <p><a href="my-account.php">Retourner sur mon compte</a></p>
        <p><a href="logout.php">Me déconnecter</a></p>
    </nav>

    <h1>Modifier mes informations personnelles</h1>

    <form action="" method="POST">
        <p>Adresse email actuelle :
            <?php
            $user = $_SESSION['user'];
            echo $user->email;
            ?>
        </p>
        <input type="email" name="email" value="" placeholder="Nouvelle adresse email" required />
        <input type="submit" value="Valider" />
    </form>

    <form action="" method="POST">
        <input type="password" name="password" value="" placeholder="Nouveau mot de passe" required />
        <input type="password" name="password1" value="" placeholder="Confirmer le mot de passe" required />
        <input type="submit" value="Valider" />
    </form>

    <form action="" method="POST">
        <input type="text" name="name" value="" placeholder="Nouveau prénom" required />
        <input type="submit" value="Valider" />
    </form>

    <form action="" method="POST">
        <input type="text" name="surname" value="" placeholder="Nouveau nom de famille" required />
        <input type="submit" value="Valider" />
    </form>

    <?php
    $pdo = new PDO('sqlite:database.sqlite', null, null, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ
    ]);

    $error = null;

    try {
        // fonction pour mettre à jour plus rapidement les champs individuellement
        function updateField($field, $value)
        {
            // récupérer l'id de l'utilisateurice dans la variable de session
            $user = $_SESSION['user'];
            $id = $user->id;
            global $pdo;

            $query = $pdo->prepare("UPDATE accounts SET $field = :value WHERE id = :id");
            $query->execute([
                'value' => $value,
                'id' => $id
            ]);

            echo "<p class='success'>Compte mis à jour avec succès.</p>";
        }

        // mettre à jour l'email de l'utilisateurice
        if (isset($_POST['email'])) {
            if (!empty($_POST['email'])) {
                if (filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
                    $newEmail = $_POST['email'];

                    // dans la table accounts
                    updateField('email', $newEmail);

                    // récupérer l'ancien email pour faire le lien avec la table books et la mettre à jour aussi
                    $user = $_SESSION['user'];
                    $oldEmail = $user->email;

                    $query = $pdo->prepare("UPDATE books SET email = :newEmail WHERE email = :oldEmail");
                    $query->execute([
                        'newEmail' => $newEmail,
                        'oldEmail' => $oldEmail
                    ]);

                    // mettre à jour dans la session en cours
                    $_SESSION['user']->email = $newEmail;
                } else {
                    echo "<p class='error'>Adresse email invalide</p>";
                }
            } else {
                echo "<p class='error'>Vous devez entrer un email</p>";
            }
        };

        // mettre à jour le mot de passe dans la table accounts et la session en cours
        if (isset($_POST['password']) && isset($_POST['password1'])) {
            if (!empty($_POST['password']) && !empty($_POST['password1'])) {
                if (strlen($_POST['password']) < $max_length && strlen($_POST['password1']) < $max_length) {
                    if ($_POST['password'] === $_POST['password1']) {
                        $newPassword = password_hash($_POST['password'], PASSWORD_DEFAULT);
                        updateField('password', $newPassword);
                        $_SESSION['user']->password = $newPassword;
                    } else {
                        echo "<p class='error'>Les deux mots de passe ne sont pas identiques</p>";
                    }
                } else {
                    echo "<p class='error'>Le mot de passe est trop long</p>";
                }
            } else {
                echo "<p class='error'>Vous devez entrer un mot de passe</p>";
            }
        }

        // mettre à jour le prénom dans la table accounts et la session en cours
        if (isset($_POST['name'])) {
            if (!empty($_POST['name'])) {
                if (strlen($_POST['name']) < $max_length) {
                    $newName = trim(htmlspecialchars($_POST['name']));
                    updateField('name', $newName);
                    $_SESSION['user']->name = $newName;
                } else {
                    echo "<p class='error'>Le prénom est trop long</p>";
                }
            } else {
                echo "<p class='error'>Vous devez entrer un élément</p>";
            }
        }

        // mettre à jour le nom de famille dans la table accounts et la session en cours
        if (isset($_POST['surname'])) {
            if (!empty($_POST['surname'])) {
                if (strlen($_POST['surname']) < $max_length) {
                    $newSurname = trim(htmlspecialchars($_POST['surname']));
                    updateField('surname', $newSurname);
                    $_SESSION['user']->surname = $newSurname;
                } else {
                    echo "<p class='error'>Le nom est trop long</p>";
                }
            } else {
                echo "<p class='error'>Vous devez entrer un élément</p>";
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