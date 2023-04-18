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
                    $id_string = trim(htmlspecialchars($_POST['non-available']));
                    $id = intval($id_string);

                    $query = $pdo->prepare('SELECT * FROM books WHERE id = :id');
                    $query->execute([
                        'id' => $id
                    ]);
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

    <form action="" method="POST">
        <div>
            <label for="title">(*) Titre du livre</label>
            <input type="hidden" name="newTitle" value="<?php echo $all_book->id ?>" />
            <input type="text" id="title" name="title" value="" required />
        </div>
        <div>
            <label for="author">(*) Auteur-trice-s</label>
            <input type="text" id="author" name="author" value="" required />
        </div>
        <div>
            <label for="translator">Traducteur-ice-s (s'il y en a)</label>
            <input type="text" id="translator" name="translator" value="" />
        </div>
        <div>
            <label for="collection">Collection</label>
            <input type="text" id="collection" name="collection" value="" />
        </div>
        <div>
            <label for="edition">Edition</label>
            <input type="text" id="edition" name="edition" value="" />
        </div>
        <div>
            <label for="publication">Année de publication</label>
            <input type="date" id="publication" name="publication" value="" />
        </div>
        <div>
            <label for="pages">Nombre de pages</label>
            <input type="number" min="0" id="pages" name="pages" value="" />
        </div>

        <p><em>(*) Ces champs sont obligatoires<em></p>
        <input type="submit" value="Ajouter ce livre" />
    </form>

    <!-- Si l'utilisateurice n'est pas connecté-e -->
<?php else : ?>

    <nav>
        <p><a href="index.php">Page d'accueil</a></p>
    </nav>

    <h1>Vous devez être connecté.e pour avoir accès à cette page</h1>

<?php endif ?>

</body>

</html>