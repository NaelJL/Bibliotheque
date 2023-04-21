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

        $id = "";

        // pouvoir modifier le livre
        if (isset($_POST['modification'])) {
            if (!empty($_POST['modification'])) {

                // vérifier que la variable contient trois caractères maximum et la convertir en nombre
                if (strlen($_POST['modification']) <= 3) {
                    $id_string = trim(htmlspecialchars($_POST['modification']));
                    $id = intval($id_string);

                    $query = $pdo->prepare('SELECT * FROM books WHERE id = :id');
                    $query->execute([
                        'id' => $id
                    ]);

                    $book = $query->fetch();

                    // stocker l'id du livre dans une variable de session
                    $_SESSION['id-modif-book'] = $id;
                }
            }
        }
    } catch (PDOException $e) {
        $error = $e->getMessage();
    }
?>
    <nav>
        <p><a href="my-account.php">Retourner sur mon compte</a></p>
        <p><a href="logout.php">Me déconnecter</a></p>
    </nav>

    <h1>Modifier un de mes livres</h1>

    <?php
    if ($book->available == 1) :
    ?>
        <section>
            <p><strong>Vous allez modifier
                    <?php echo $book->title; ?>
                    de
                    <?php echo $book->author; ?>
                </strong></p>
        </section>

        <section style="flex-direction: column;">
            <form action="modif-book-validation.php" method="POST">
                <input type="text" name="newTitle" value="" placeholder="Changement de titre" required />
                <input type="submit" value="Valider" />
            </form>

            <form action="modif-book-validation.php" method="POST">
                <input type="text" name="newAuthor" value="" placeholder="Changement d'auteur-trice" required />
                <input type="submit" value="Valider" />
            </form>

            <form action="modif-book-validation.php" method="POST">
                <input type="text" name="newTranslator" value="" placeholder="Changement de traducteur-trice" required />
                <input type="submit" value="Valider" />
            </form>

            <form action="modif-book-validation.php" method="POST">
                <input type="text" name="newCollection" value="" placeholder="Changement de collection" required />
                <input type="submit" value="Valider" />
            </form>

            <form action="modif-book-validation.php" method="POST">
                <input type="text" name="newEdition" value="" placeholder="Changement d'édition" required />
                <input type="submit" value="Valider" />
            </form>

            <form action="modif-book-validation.php" method="POST">
                <input type="text" name="newPublication" value="" placeholder="Changement d'année de publication" required />
                <input type="submit" value="Valider" />
            </form>

            <form action="modif-book-validation.php" method="POST">
                <input type="text" name="newPages" value="" placeholder="Changement du nombre de pages" required />
                <input type="submit" value="Valider" />
            </form>

        <?php else : ?>
            <p>Le livre est emprunté. Il pourra être modifié à son retour.</p>
        <?php endif; ?>

        <!-- Si l'utilisateurice n'est pas connecté-e -->
    <?php else : ?>

        <nav>
            <p><a href="index.php">Page d'accueil</a></p>
        </nav>

        <h1>Vous devez être connecté.e pour avoir accès à cette page</h1>

    <?php endif ?>

    </body>

    </html>