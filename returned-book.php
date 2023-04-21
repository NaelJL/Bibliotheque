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

    <h1>Confirmer le retour</h1>

    <?php
    $pdo = new PDO('sqlite:database.sqlite', null, null, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ
    ]);

    $error = null;

    try {

        if (isset($_POST['returned'])) {
            if (!empty($_POST['returned'])) {

                // vérifier que la variable contient 3 caractères maximum et la convertir en nombre
                if (strlen($_POST['returned']) <= 3) {
                    $id_string = trim(htmlspecialchars($_POST['returned']));
                    $id = intval($id_string);

                    // Récupérer le book_id dans la table borrowed_books pour faire le lien avec la table books
                    $find = $pdo->prepare('SELECT * FROM borrowed_books WHERE id = :id');
                    $find->execute([
                        'id' => $id
                    ]);
                    $book_id_obj = $find->fetch();
                } else {
                    echo "<p class='error'>Problème avec le livre</p>";
                }
            }
        } else {
            echo "<p class='error'>Problème avec le retour</p>";
        }
    } catch (PDOException $e) {
        $error = $e->getMessage();
    }

    if ($book_id_obj) :
    ?>

        <section>
            <article class="book-card">
                <p>Le livre a bien été rendu</p>
                <!-- Indiquer le livre comme étant rendu -->
                <form action="returned-book-confirmed.php" method="POST">
                    <input type="hidden" name="returned" value="<?php echo $book_id_obj->id ?>" />
                    <input type="submit" value="Confirmer" />
                </form>
            </article>
        </section>

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