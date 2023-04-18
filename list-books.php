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

    <h1>Les livres à emprunter</h1>

    <?php

    $pdo = new PDO('sqlite:database.sqlite', null, null, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ
    ]);
    ?>

    <section>
        <form action="" method="POST">
            <input type="text" name="search" />
            <input type="submit" value="Rechercher un livre" />
        </form>
    </section>

    <?php
    $results = "";

    // faire une recherche dans les titres de livres
    if (isset($_POST['search'])) {
        if (!empty($_POST['search'])) {
            if (strlen($_POST['search']) < $max_length) {

                $search_input = trim(htmlspecialchars($_POST['search']));

                $search = $pdo->prepare('SELECT * FROM books WHERE title LIKE :contain');
                $search->execute([
                    'contain' => '%' . $search_input . '%'
                ]);
                $results = $search->fetchAll();
            } else {
                echo "<p class='error'>La recherche est trop longue</p>";
            }
        } else {
            echo "<p class='error'>La recherche ne peut être vide</p>";
        }
    }

    // si un ou plusieurs livres correspondent à la recherche
    if ($results) :
    ?>
        <section>
            <?php foreach ($results as $result) : ?>

                <article class="book-card">
                    <p>Titre : <strong><?php echo $result->title ?></strong></p>
                    <p>Auteur-trice-s : <strong><?php echo $result->author ?></strong></p>
                    <p>Traduction :
                        <?php $translator = $result->translator ? "<strong>$result->translator</strong>" : "-";
                        echo  $translator; ?>
                    </p>
                    <p>Collection :
                        <?php $collection = $result->collection ? "<strong>$result->collection</strong>" : "-";
                        echo  $collection; ?>
                    </p>
                    <p>Edition :
                        <?php $edition = $result->edition ? "<strong>$result->edition</strong>" : "-";
                        echo  $edition; ?>
                    </p>
                    <p>Année de publication :
                        <?php $publication = $result->publication ? "<strong>$result->publication</strong>" : "-";
                        echo  $publication; ?>
                    </p>
                    <p>Nombre de pages :
                        <?php $pages = $result->pages ? "<strong>$result->pages</strong>" : "-";
                        echo  $pages; ?>
                    </p>

                    <!-- si l'utilisateurice a moins de 3 ouvrages en cours d'emprunt -->
                    <?php
                    $email = $_SESSION['user']->email;

                    $person = $pdo->prepare('SELECT * FROM accounts WHERE email = :email');
                    $person->execute([
                        'email' => $email
                    ]);
                    $person_profile = $person->fetch();
                    if ($person_profile) {
                        $id_person = $person_profile->id;
                        $email_person = $person_profile->email;
                    }

                    $search = $pdo->prepare('SELECT * FROM borrowed_books WHERE id_person = :id');
                    $search->execute([
                        'id_person' => $id
                    ]);
                    $search_result = $search->fetchAll();

                    $count = count($search_result);
                    if ($count < 3) :

                        // si l'utilisateurice n'est pas la personne qui propose le livre au prêt
                        if ($id_person !== $result->id_person) :
                    ?>
                            <!-- si le livre est disponible, pouvoir l'emprunter -->
                            <form action="borrow.php" method="POST">
                                <input type="hidden" name="borrow" value="<?php echo $result->id ?>" />
                                <input type="submit" value="Emprunter" />
                            </form>
                        <?php else : ?>
                            <p><strong>Livre vous appartenant</strong></p>
                        <?php endif; ?>
                    <?php else : ?>
                        <p><strong>Vous avez déjà 3 ouvrages en cours d'emprunt</strong></p>
                    <?php endif; ?>

                </article>
            <?php endforeach; ?>

        </section>
        <hr />
        <hr />
    <?php endif; ?>

    <section>

        <!-- Lister les livres que l'on peut emprunter depuis la table books -->
        <?php
        $search = $pdo->prepare('SELECT * FROM books');
        $search->execute();
        $all_books = $search->fetchAll();
        ?>

        <?php foreach ($all_books as $all_book) :
            if ($all_book->available == 1) : ?>

                <!-- afficher les livres disponibles dans des cards -->
                <article class="book-card">
                    <p>Titre : <strong><?php echo $all_book->title ?></strong></p>
                    <p>Auteur-trice-s : <strong><?php echo $all_book->author ?></strong></p>
                    <p>Traduction :
                        <?php $translator = $all_book->translator ? "<strong>$all_book->translator</strong>" : "-";
                        echo  $translator; ?>
                    </p>
                    <p>Collection :
                        <?php $collection = $all_book->collection ? "<strong>$all_book->collection</strong>" : "-";
                        echo  $collection; ?>
                    </p>
                    <p>Edition :
                        <?php $edition = $all_book->edition ? "<strong>$all_book->edition</strong>" : "-";
                        echo  $edition; ?>
                    </p>
                    <p>Année de publication :
                        <?php $publication = $all_book->publication ? "<strong>$all_book->publication</strong>" : "-";
                        echo  $publication; ?>
                    </p>
                    <p>Nombre de pages :
                        <?php $pages = $all_book->pages ? "<strong>$all_book->pages</strong>" : "-";
                        echo  $pages; ?>
                    </p>

                    <!-- si l'utilisateurice a moins de 3 ouvrages en cours d'emprunt -->
                    <?php
                    $email = $_SESSION['user']->email;

                    $person = $pdo->prepare('SELECT * FROM accounts WHERE email = :email');
                    $person->execute([
                        'email' => $email
                    ]);
                    $person_profile = $person->fetch();
                    if ($person_profile) {
                        $id_person = $person_profile->id;
                        $email_person = $person_profile->email;
                    }

                    $search = $pdo->prepare('SELECT * FROM borrowed_books WHERE id_person_borrowing = :id');
                    $search->execute([
                        'id' => $id_person
                    ]);
                    $results = $search->fetchAll();

                    $count = count($results);
                    if ($count < 3) :

                        // si l'utilisateurice n'est pas la personne qui propose le livre au prêt
                        if ($id_person !== $all_book->id_person) :
                    ?>
                            <!-- si le livre est disponible, pouvoir l'emprunter -->
                            <form action="borrow.php" method="POST">
                                <input type="hidden" name="borrow" value="<?php echo $all_book->id ?>" />
                                <input type="submit" value="Emprunter" />
                            </form>
                        <?php else : ?>
                            <p><strong>Ce livre vous appartient</strong></p>
                        <?php endif; ?>
                    <?php else : ?>
                        <p>Vous avez déjà 3 ouvrages en cours d'emprunt</p>
                    <?php endif; ?>

                </article>

            <?php endif; ?>
        <?php endforeach; ?>

        <!-- Lister les livres indisponibles (déjà empruntés) -->
        <?php
        $query = $pdo->prepare('SELECT books.id, books.title, books.author, books.translator, books.collection, books.edition, books.publication, books.pages, books.available, books.id_person, borrowed_books.date_return FROM books, borrowed_books WHERE books.id = borrowed_books.book_id');
        $query->execute();
        $books = $query->fetchAll();

        foreach ($books as $book) :
            if ($book->available == 0) : ?>

                <!-- afficher les livres dans des cards -->
                <article class="book-card">
                    <p>Titre : <strong><?php echo $book->title ?></strong></p>
                    <p>Auteur-trice-s : <strong><?php echo $book->author ?></strong></p>
                    <p>Traduction :
                        <?php $translator = $book->translator ? "<strong>$book->translator</strong>" : "-";
                        echo  $translator; ?>
                    </p>
                    <p>Collection :
                        <?php $collection = $book->collection ? "<strong>$book->collection</strong>" : "-";
                        echo  $collection; ?>
                    </p>
                    <p>Edition :
                        <?php $edition = $book->edition ? "<strong>$book->edition</strong>" : "-";
                        echo  $edition; ?>
                    </p>
                    <p>Année de publication :
                        <?php $publication = $book->publication ? "<strong>$book->publication</strong>" : "-";
                        echo  $publication; ?>
                    </p>
                    <p>Nombre de pages :
                        <?php $pages = $book->pages ? "<strong>$book->pages</strong>" : "-";
                        echo  $pages; ?>
                    </p>

                    <?php // formater les dates pour qu'elles soient plus lisibles
                    $date_return = stripslashes($book->date_return);
                    if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $date_return)) {
                        $date_return_date = new DateTime($date_return);
                        $date_return_ok = $date_return_date->format('d/m/Y');
                    }
                    ?>

                    <p>Ce livre est déjà emprunté jusqu'au : <strong><?php echo $date_return_ok ?></strong></p>

                </article>

            <?php endif ?>
        <?php endforeach; ?>
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