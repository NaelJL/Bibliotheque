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
        <p><a href="list-books.php">Retourner à la liste</a></p>
        <p><a href="logout.php">Me déconnecter</a></p>
    </nav>

    <h1>Emprunter un livre</h1>

    <section>
        <?php

        $pdo = new PDO('sqlite:database.sqlite', null, null, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ
        ]);

        $error = null;

        try {
            // nettoyer la variable hidden et la formater en integer
            if (isset($_POST['borrow'])) {
                if (!empty($_POST['borrow'])) {
                    $id_string = trim(htmlspecialchars($_POST['borrow']));
                    $id = intval($id_string);

                    // la stocker dans une variable de session
                    $_SESSION['id-book-borrow'] = $id;

                    $query = $pdo->prepare('SELECT * FROM books WHERE id = :id');
                    $query->execute([
                        'id' => $id
                    ]);
                    $book = $query->fetch();
                } else {
                    echo "<p class='error'>Problème avec l'emprunt</p>";
                }
            }
        } catch (PDOException $e) {
            $error = $e->getMessage();
        }


        if ($book) :
        ?>
            <p class='success'>Le livre est disponible</p>
            <article class="book-card">
                <p>Titre : <strong><?php echo $book->title; ?></strong></p>
                <p>Auteur-trice-s : <strong><?php echo $book->author; ?></strong></p>
                <form action="borrow-confirmed.php" method="POST">
                    <input type="submit" value="Confirmer l'emprunt" />
                </form>
            </article>

        <?php else : echo "<p class='error'>le livre n'est pas disponible</p>"; ?>
        <?php endif ?>

        <!-- Si l'utilisateurice n'est pas connecté-e -->
    <?php else : ?>

        <nav>
            <p><a href="index.php">Page d'accueil</a></p>
        </nav>

        <h1>Vous devez être connecté.e pour avoir accès à cette page</h1>

    <?php endif ?>
    </body>

    </html>