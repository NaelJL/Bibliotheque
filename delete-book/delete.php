<?php
session_start();
require 'head.php';
?>

<!-- Si l'utilisateurice est connecté-e et le cookie time valide -->
<?php
// if ($_SESSION['user'] && isset($_COOKIE[$cookie_name]) && time() < $_COOKIE[$cookie_name]) :
if ($_SESSION['user']) :
?>

    <nav>
        <p><a href="my-account.php">Retourner sur mon compte</a></p>
        <p><a href="logout.php">Me déconnecter</a></p>
    </nav>

    <?php

    $pdo = new PDO('sqlite:database.sqlite', null, null, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ
    ]);
    ?>

    <h1>Supprimer un livre</h1>

    <section>
        <?php

        // confirmer la suppression définitive d'un livre
        if (isset($_POST['delete'])) {
            if (!empty($_POST['delete'])) {

                // vérifier que la variable contient 3 caractères maximum et la convertir en nombre
                if (strlen($_POST['delete']) <= 3) {
                    $id_string = trim(htmlspecialchars($_POST['delete']));
                    $id = intval($id_string);

                    $email = $_SESSION['user']->email;

                    $show = $pdo->prepare('SELECT * FROM books WHERE id = :id');
                    $show->execute([
                        'id' => $id
                    ]);
                    $books = $show->fetch();
                }
            }
        }

        if ($books) :
        ?>
            <article class="book-card">
                <p>Titre : <strong><?php echo $books->title ?></strong></p>
                <p>Auteur-trice : <strong><?php echo $books->author ?></strong></p>

                <!-- Supprimer le livre -->
                <form action="delete-book-confirmed.php" method="POST">
                    <input type="hidden" name="delete" value="<?php echo $books->id ?>" />
                    <input type="submit" value="Supprimer définitivement" />
                </form>
            </article>

        <?php endif; ?>
    </section>


    <!-- Si l'utilisateurice n'est pas connecté-e -->
<?php else : ?>

    <nav>
        <p><a href="index.php">Page d'accueil</a></p>
    </nav>

    <h1>Vous devez être connecté.e pour avoir accès à cette page</h1>

<?php endif ?>

</body>

</html>