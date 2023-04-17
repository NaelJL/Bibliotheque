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

    <h1>Tous les emprunts en cours</h1>

    <?php
    $pdo = new PDO('sqlite:database.sqlite', null, null, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ
    ]);
    ?>

    <?php
    // afficher sur la page tous les livres empruntés
    $show = $pdo->query('SELECT 
        books.title, books.author, books.email as "lender_email", 
        borrowed_books.id, borrowed_books.date_borrowed, 
        borrowed_books.date_return, borrowed_books.extension, 
        borrowed_books.book_id, borrowed_books.email as "borrower_email" 
        FROM books, borrowed_books 
        WHERE books.id = borrowed_books.book_id 
        ORDER BY borrower_email');
    $borrowed_books = $show->fetchAll();
    ?>

    <section>
        <?php
        if ($borrowed_books) :
            foreach ($borrowed_books as $book) :

                // formater les dates pour qu'elles soient plus lisibles
                $date_borrowed = stripslashes($book->date_borrowed);
                if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $date_borrowed)) {
                    $date_borrowed_date = new DateTime($date_borrowed);
                    $date_borrowed_ok = $date_borrowed_date->format('d/m/Y');
                }
                $date_return = stripslashes($book->date_return);
                if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $date_return)) {
                    $date_return_date = new DateTime($date_return);
                    $date_return_ok = $date_return_date->format('d/m/Y');
                }
        ?>

                <article class="book-card">
                    <p>Titre : <strong><?php echo $book->title ?></strong></p>
                    <p>Auteur-ice : <strong><?php echo $book->author ?></strong></p>
                    <p>Personne qui prête : <strong><?php echo $book->lender_email ?></strong></p>
                    <p>Personne qui emprunte : <strong><?php echo $book->borrower_email ?></strong></p>
                    <p>Date d'emprunt : <strong><?php echo $date_borrowed_ok; ?></strong></p>
                    <p>Date de retour : <strong><?php echo $date_return_ok; ?></strong></p>

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