<?php
session_start();
require 'head.php';
require 'cookie_handler.php';
?>

<!-- Si l'utilisateurice est connecté-e et le cookie time valide -->
<?php
// if ($_SESSION['user'] && isset($_COOKIE[$cookie_name]) && time() < $_COOKIE[$cookie_name]) :
if ($_SESSION['user']->admin == 1) :
?>
    <nav>
        <p><a href="admin.php">Retourner sur mon compte admin</a></p>
        <p><a href="logout.php">Me déconnecter</a></p>
    </nav>

    <h1>Modifier une information personnelle</h1>

    <section>
        <form action="" method="POST">
            <label>Personne concernée :</label>
            <input type="email" name="email" value="" placeholder="Adresse email" required />
            <input type="text" name="name" value="" placeholder="Nom ou pseudo" required />
            <input type="submit" value="Valider" />
        </form>
    </section>

    <?php
    // afficher un message de validation si les données ont bien pu être modifiées
    if (isset($_GET['message'])) :
        if (!empty($_GET['message'])) :
            echo "<p class='success'>Modification réussie</p>";
    ?>
            <script>
                // rediriger vers l'url classique après 3 secondes
                function redirectWithDelay() {
                    setTimeout(function() {
                        window.location.href = 'admin-modif-data.php';
                    }, 3000);
                }

                // appeler la fonction une fois que la page est chargée
                window.onload = function() {
                    redirectWithDelay();
                };
            </script>
        <?php endif; ?>
    <?php endif; ?>

    <?php
    $pdo = new PDO('sqlite:database.sqlite', null, null, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ
    ]);

    // rechercher la personne concernée dans la table Accounts
    if (isset($_POST['email']) && isset($_POST['name'])) {
        if (!empty($_POST['email']) && !empty($_POST['name'])) {
            if (strlen($_POST['email']) < $max_length && strlen($_POST['name']) < $max_length) {
                if (filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {

                    $email = $_POST['email'];
                    // stocker l'email dans une variable de session pour s'en resservir pour modifier les données
                    $_SESSION['old-email-modif'] = $email;

                    $name = trim(htmlspecialchars($_POST['name']));

                    $person = $pdo->prepare('SELECT * FROM accounts WHERE email = :email AND name = :name');
                    $person->execute([
                        'email' => $email,
                        'name' => $name
                    ]);
                    $person_result = $person->fetch();
                } else {
                    echo "<p class='error'>Adresse email invalide</p>";
                }
            } else {
                echo "<p class='error'>L'un des deux champs est trop long</p>";
            }
        } else {
            echo "<p class='error'>Les deux champs doivent être remplis</p>";
        }
    }

    // si la personne existe, pouvoir modifier ses informations
    if ($person_result) :
    ?>
        <section>
            <p><strong>Vous allez modifier le compte de
                    <?php echo $person_result->name; ?>
                    <?php echo $person_result->surname; ?>
                </strong></p>
        </section>

        <section style="flex-direction: column;">
            <form action="admin-modif-data-validation.php" method="POST">
                <input type="email" name="newEmail" value="" placeholder="Nouvelle adresse email" required />
                <input type="submit" value="Valider" />
            </form>

            <form action="admin-modif-data-validation.php" method="POST">
                <input type="text" name="newName" value="" placeholder="Nouveau prénom" required />
                <input type="submit" value="Valider" />
            </form>

            <form action="admin-modif-data-validation.php" method="POST">
                <input type="text" name="newSurname" value="" placeholder="Nouveau nom de famille" required />
                <input type="submit" value="Valider" />
            </form>

            <form action="admin-modif-data-validation.php" method="POST">
                <p>Confirmer le compte :</p>
                <label for="yes">Oui</label>
                <input type="radio" id="yes" name="confirmation" value="1" checked />
                <label for="no">Non</label>
                <input type="radio" id="no" name="confirmation" value="0" />
                <input type="submit" value="Valider" />
            </form>

            <form action="admin-modif-data-validation.php" method="POST">
                <p>Transformer en compte admin :</p>
                <label for="yes">Oui</label>
                <input type="radio" id="yes" name="admin" value="1" checked />
                <label for="no">Non</label>
                <input type="radio" id="no" name="admin" value="0" />
                <input type="submit" value="Valider" />
            </form>

            <form action="admin-modif-data-validation.php" method="POST">
                <p>Supprimer définitivement le compte ainsi que tous les livres de la personne :</p>
                <input type="hidden" name="delete" value="1" />
                <input type="submit" value="Supprimer définitivement" />
            </form>
        </section>

    <?php endif; ?>


    <!-- Si l'utilisateurice n'est pas connecté-e -->
<?php else : ?>

    <nav>
        <p><a href="index.php">Page d'accueil</a></p>
    </nav>

    <h1>Vous devez être administrateur-ice pour avoir accès à cette page</h1>

<?php endif ?>

</body>

</html>