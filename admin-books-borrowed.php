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

    <!-- La recherche est gérée par search.js et search.php (méthode AJAX) -->
    <section>
        <form method="POST" class="search-form">
            <input type="text" name="search" class="search-input" placeholder="Rechercher un livre" />
            <input type="hidden" name="page" class="admin-page" value="admin-page" />
        </form>
    </section>

    <section>
        <div class="search-results" aria-label="Vos résultats de recherche apparaissent ici"></div>
    </section>

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