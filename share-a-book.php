<?php
session_start();
require 'head.php';
require 'cookie_handler.php';
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

    <h1>Ajouter un livre à la base de données et le proposer au prêt</h1>

    <form action="" method="POST">
        <div>
            <label for="title">(*) Titre du livre</label>
            <input type="text" id="title" name="title" value="" required />
            <p>Cet espace est limité à 30 caractères ( ' et : )</p>
        </div>
        <div>
            <label for="author">(*) Auteur-trice-s</label>
            <input type="text" id="author" name="author" value="" required />
            <p>Cet espace est limité à 25 caractères ( ' et : )</p>
        </div>
        <div>
            <label for="translator">Traducteur-ice-s (s'il y en a)</label>
            <input type="text" id="translator" name="translator" value="" />
            <p>Cet espace est limité à 25 caractères ( ' et : )</p>
        </div>
        <div>
            <label for="collection">Collection</label>
            <input type="text" id="collection" name="collection" value="" />
            <p>Cet espace est limité à 25 caractères ( ' et : )</p>
        </div>
        <div>
            <label for="edition">Edition</label>
            <input type="text" id="edition" name="edition" value="" />
            <p>Cet espace est limité à 25 caractères ( ' et : )</p>
        </div>
        <div>
            <label for="publication">Année de publication</label>
            <input type="date" id="publication" name="publication" value="" />
        </div>
        <div>
            <label for="pages">Nombre de pages</label>
            <input type="number" min="0" id="pages" name="pages" value="" />
        </div>
        <div>
            <p>(*) Disponible immédiatement :</p>
            <label for="yes">Oui</label>
            <input type="radio" id="yes" name="available" value="1" checked />
            <label for="no">Non</label>
            <input type="radio" id="no" name="available" value="0" />
        </div>

        <p><em>(*) Ces champs sont obligatoires<em></p>
        <input type="submit" value="Ajouter ce livre" />
    </form>

    <?php

    $pdo = new PDO('sqlite:database.sqlite', null, null, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ
    ]);

    $error = null;

    try {

        if (isset($_POST['title']) && isset($_POST['author']) && isset($_POST['available'])) {
            if (!empty($_POST['title']) && !empty($_POST['author']) && $_POST['available'] !== '') {

                // vérifier que les variables ne sont pas trop longues
                if (strlen($_POST['title']) < $max_length && strlen($_POST['author']) < $max_length && strlen($_POST['available']) < $max_length) {

                    // nettoyer les données texte 
                    $title = trim(htmlspecialchars($_POST['title']));
                    $author = trim(htmlspecialchars($_POST['author']));
                    $translator = trim(htmlspecialchars($_POST['translator']));
                    $collection = trim(htmlspecialchars($_POST['collection']));
                    $edition = trim(htmlspecialchars($_POST['edition']));

                    // nettoyer la date, vérifier le format ISO 8601 (AAAA-MM-JJ), convetir en objet DateTime et formater
                    $publication = trim(stripslashes(htmlspecialchars($_POST['publication'])));
                    if (!empty($publication)) {
                        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $publication)) {
                            $publication_date = new DateTime($publication);
                            $publication_ok = $publication_date->format('d/m/Y');
                        } else {
                            $publication_date = null;
                            echo "<p class='error'>Le format de la date n'est pas valide</p>";
                            exit;
                        }
                    }

                    // nettoyer l'input pages
                    $pages = trim(stripslashes(htmlspecialchars($_POST['pages'])));
                    if (!empty($pages)) {
                        if (is_numeric($pages)) {
                            $pages_ok = $pages;
                        } else {
                            $pages = null;
                            echo "<p class='error'>Le format du nombre de page n'est pas valide</p>";
                            exit;
                        }
                    }

                    // nettoyer l'input available
                    $available = trim(stripslashes(htmlspecialchars($_POST['available'])));
                    if ($available == 1 || $available == 0) {
                        $available_ok = $available;

                        // récupérer l'email de session
                        $email = $_SESSION['user']->email;
                        if ($email) {

                            // aller chercher l'id de la personne grâce à l'email de session
                            $search = $pdo->prepare('SELECT * FROM accounts WHERE email = :email');
                            $search->execute([
                                'email' => $email
                            ]);

                            $result = $search->fetch();

                            if ($result) {
                                $id_person = $result->id;

                                // création d'une ligne dans la table books
                                $query = $pdo->prepare('INSERT INTO books (title, author, translator, collection, edition, publication, pages, available, id_person) VALUES (:title, :author, :translator, :collection, :edition, :publication, :pages, :available, :id_person)');
                                $query->execute([
                                    'title' => $title,
                                    'author' => $author,
                                    'translator' => $translator,
                                    'collection' => $collection,
                                    'edition' => $edition,
                                    'publication' => $publication_ok,
                                    'pages' => $pages_ok,
                                    'available' => $available_ok,
                                    'id_person' => $id_person
                                ]);
                                echo "<p class='success'>Le livre a bien été enregistré</p>";
                            } else {
                                echo "<p class='error'>Problème avec l'email du compte</p>";
                                exit;
                            }
                        } else {
                            echo "<p class='error'>Problème avec l'email du compte</p>";
                            exit;
                        }
                    } else {
                        echo "<p class='error'>La disponibilité n'est pas valide</p>";
                        exit;
                    }
                } else {
                    echo "<p class='error'>Au moins l'un des éléments contient trop de caractères</p>";
                }
            } else {
                echo "<p class='error'>Les champs obligatoires doivent être remplis</p>";
                exit;
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