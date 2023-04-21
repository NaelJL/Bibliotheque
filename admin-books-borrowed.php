<?php
session_start();
require 'head.php';
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

    <h1>Tous les emprunts en cours</h1>

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

    // faire une recherche dans les titres de livres empruntés
    if (isset($_POST['search'])) :
        if (!empty($_POST['search'])) :
            if (strlen($_POST['search']) < $max_length) :

                $search_input = trim(htmlspecialchars($_POST['search']));

                // récupérer l'id du livre en fonction de la recherche
                $books = $pdo->prepare('SELECT * FROM books WHERE title LIKE :contain');
                $books->execute([
                    'contain' => '%' . $search_input . '%'
                ]);
                $results_books = $books->fetchAll();

                // si un ou plusieurs livres correspondent à la recherche
                if ($results_books) :
                    // boucle foreach pour parcourir l'array des résultats
                    foreach ($results_books as $result) :
                        $id_book = $result->id;
                        $id_person_lending = $result->id_person;

                        // récupérer les informations du livre emprunté grâce à l'id
                        $borrowed_books = $pdo->prepare('SELECT * FROM borrowed_books WHERE borrowed_books.book_id = :id_book');
                        $borrowed_books->execute([
                            'id_book' => $id_book
                        ]);
                        $results_borrowed_books = $borrowed_books->fetch();

                        $id_person_borrowing = $results_borrowed_books->id_person_borrowing;

                        // récupérer l'email de la personne qui prête grâce à books.id_person
                        $lender_email_search = $pdo->prepare('SELECT email FROM accounts WHERE id = :id');
                        $lender_email_search->execute([
                            'id' => $id_person_lending
                        ]);
                        $result_lender = $lender_email_search->fetch();
                        $lender_email = $result_lender->email;

                        // récupérer l'email de la personne qui emprunte grâce à borrowed_books.id_person_borrowing
                        $borrower_email_search = $pdo->prepare('SELECT email FROM accounts WHERE id = :id');
                        $borrower_email_search->execute([
                            'id' => $id_person_borrowing
                        ]);
                        $result_borrower = $borrower_email_search->fetch();
                        $borrower_email = $result_borrower->email;

    ?>
                        <!-- Afficher les résultats de la recherche avec une carte du livre -->
                        <section>
                            <article class="book-card">

                                <p>Titre : <strong><?php echo $result->title ?></strong></p>
                                <p>Auteur-trice-s : <strong><?php echo $result->author ?></strong></p>
                                <p>Personne qui prête : <strong><?php echo $lender_email ?></strong></p>
                                <p>Personne qui emprunte : <strong><?php echo $borrower_email ?></strong></p>
                                <p>Date d'emprunt : <strong><?php format_date($results_borrowed_books->date_borrowed); ?></strong></p>
                                <p>Date de retour : <strong><?php format_date($results_borrowed_books->date_return); ?></strong></p>

                                <?php if ($results_borrowed_books->extension == 0) : ?>
                                    <p>Non prolongé</p>
                                <?php elseif ($results_borrowed_books->extension == 1) : ?>
                                    <p><strong>Déjà prolongé</strong></p>
                                <?php endif; ?>

                            </article>
                        <?php endforeach; ?>

                        </section>
                        <hr />
                        <hr />
                    <?php endif; ?>

                <?php else : echo "<p class='error'>La recherche est trop longue</p>"; ?>
                <?php endif; ?>
            <?php else : echo "<p class='error'>La recherche ne peut être vide</p>"; ?>
            <?php endif; ?>
        <?php endif; ?>


        <?php
        // afficher sur la page tous les livres empruntés
        $show = $pdo->query('SELECT 
        books.title, books.author, books.id_person as "lender_id", 
        borrowed_books.id, borrowed_books.date_borrowed, 
        borrowed_books.date_return, borrowed_books.extension, 
        borrowed_books.book_id, borrowed_books.id_person_borrowing as "borrower_id"
        FROM books, borrowed_books 
        WHERE books.id = borrowed_books.book_id 
        ORDER BY borrower_id');
        $borrowed_books = $show->fetchAll();
        ?>

        <section>
            <?php if ($borrowed_books) :
                foreach ($borrowed_books as $book) : ?>

                    <article class="book-card">
                        <p>Titre : <strong><?php echo $book->title ?></strong></p>
                        <p>Auteur-ice : <strong><?php echo $book->author ?></strong></p>

                        <?php
                        // aller chercher l'email de la personne qui prête
                        $lender_id = $book->lender_id;
                        $lender = $pdo->prepare('SELECT email FROM accounts WHERE id = :id');
                        $lender->execute([
                            'id' => $lender_id
                        ]);
                        $lender_profile = $lender->fetch();
                        $lender_email = $lender_profile->email;
                        ?>
                        <p>Personne qui prête : <strong><?php echo $lender_email ?></strong></p>

                        <?php
                        // aller chercher l'email de la personne qui emprunte
                        $borrower_id_result = $book->borrower_id;
                        $borrower = $pdo->prepare('SELECT email FROM accounts WHERE id = :id');
                        $borrower->execute([
                            'id' => $borrower_id_result
                        ]);
                        $borrower_pofile = $borrower->fetch();
                        $borrower_email = $borrower_pofile->email;
                        ?>
                        <p>Personne qui emprunte : <strong><?php echo $borrower_email ?></strong></p>
                        <p>Date d'emprunt : <strong><?php format_date($book->date_borrowed); ?></strong></p>
                        <p>Date de retour : <strong><?php format_date($book->date_return); ?></strong></p>

                        <?php if ($book->extension == 0) : ?>
                            <p>Non prolongé</p>
                        <?php elseif ($book->extension == 1) : ?>
                            <p><strong>Déjà prolongé</strong></p>
                        <?php endif; ?>
                    </article>

                <?php endforeach; ?>
            <?php endif; ?>
        </section>

        <!-- Si l'utilisateurice n'est pas connecté-e -->
    <?php else : ?>

        <nav>
            <p><a href="index.php">Page d'accueil</a></p>
        </nav>

        <h1>Vous devez être administrateur-ice pour avoir accès à cette page</h1>

    <?php endif ?>

    </body>

    </html>