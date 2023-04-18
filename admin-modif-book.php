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

    <h1>Modifier un livre</h1>

    <section>
        <form action="" method="POST">
            <label>Livre concerné :</label>
            <input type="text" name="title" value="" placeholder="Titre" required />
            <input type="text" name="author" value="" placeholder="Auteur-ice" required />
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
                        window.location.href = 'admin-modif-book.php';
                    }, 2000);
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

    // rechercher le livre concerné dans la table Books
    if (isset($_POST['title']) && isset($_POST['author'])) :
        if (!empty($_POST['title']) && !empty($_POST['author'])) :
            if (strlen($_POST['title']) < $max_length && strlen($_POST['author']) < $max_length) :

                // nettoyer et mettre en minuscule les variables
                $title = trim(htmlspecialchars(strtolower($_POST['title'])));
                $author = trim(htmlspecialchars(strtolower($_POST['author'])));

                $book = $pdo->prepare('SELECT * FROM books WHERE LOWER(title) = :title AND LOWER(author) = :author');
                $book->execute([
                    'title' => $title,
                    'author' => $author
                ]);

                $book_result = $book->fetch();

                // stocker le resultat dans une variable de session
                $_SESSION['current-book'] = serialize($book_result);

                // si le livre existe, pouvoir modifier ses informations
                if ($book_result) :
    ?>
                    <section>
                        <p><strong>Vous allez modifier
                                <?php echo $book_result->title; ?>
                                de
                                <?php echo $book_result->author; ?>
                            </strong></p>
                    </section>

                    <section style="flex-direction: column;">
                        <form action="admin-modif-book-validation.php" method="POST">
                            <input type="text" name="newTitle" value="" placeholder="Changement de titre" required />
                            <input type="submit" value="Valider" />
                        </form>

                        <form action="admin-modif-book-validation.php" method="POST">
                            <input type="text" name="newAuthor" value="" placeholder="Changement d'auteur-trice" required />
                            <input type="submit" value="Valider" />
                        </form>

                        <form action="admin-modif-book-validation.php" method="POST">
                            <input type="text" name="newTranslator" value="" placeholder="Changement de traducteur-trice" required />
                            <input type="submit" value="Valider" />
                        </form>

                        <form action="admin-modif-book-validation.php" method="POST">
                            <input type="text" name="newCollection" value="" placeholder="Changement de collection" required />
                            <input type="submit" value="Valider" />
                        </form>

                        <form action="admin-modif-book-validation.php" method="POST">
                            <input type="text" name="newEdition" value="" placeholder="Changement d'édition" required />
                            <input type="submit" value="Valider" />
                        </form>

                        <form action="admin-modif-book-validation.php" method="POST">
                            <input type="text" name="newPublication" value="" placeholder="Changement d'année de publication" required />
                            <input type="submit" value="Valider" />
                        </form>

                        <form action="admin-modif-book-validation.php" method="POST">
                            <input type="text" name="newPages" value="" placeholder="Changement du nombre de pages" required />
                            <input type="submit" value="Valider" />
                        </form>

                        <form action="admin-modif-book-validation.php" method="POST">
                            <label for="yes">Livre disponible</label>
                            <input type="radio" id="yes" name="available" value="1" checked />
                            <label for="no">Livre indisponible</label>
                            <input type="radio" id="no" name="available" value="0" />
                            <input type="submit" value="Valider" />
                        </form>

                        <?php
                        // supprimer définitivement le livre s'il n'est pas en cours d'emprunt
                        if ($book_result->available == 1) :
                        ?>
                            <form action="admin-modif-book-validation.php" method="POST">
                                <p>Supprimer définitivement le livre :</p>
                                <input type="hidden" name="delete" value="1" />
                                <input type="submit" value="Supprimer définitivement" />
                            </form>
                        <?php elseif ($book_result->available == 0) : ?>
                            <p>Vous pourrez supprimer définitivement le livre quand il ne sera plus emprunté</p>
                        <?php endif; ?>

                    </section>

                <?php else :
                    echo "<p class='error'>Le livre n'est pas présent dans la bibliothèque</p>";
                ?>
                <?php endif; ?>
            <?php else :
                echo "<p class='error'>L'un des deux champs est trop long</p>";
            ?>
            <?php endif; ?>
        <?php else :
            echo "<p class='error'>Les deux champs doivent être remplis</p>";
        ?>
        <?php endif; ?>
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