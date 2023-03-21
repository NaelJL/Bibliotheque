<?php
session_start();
require 'head.php';
require 'cookie_handler.php';
?>

<!-- Si l'utilisateurice est connecté-e et le cookie time valide -->
<?php
if ($_SESSION['user'] && isset($_COOKIE[$cookie_name]) && time() < $_COOKIE[$cookie_name]) :
?>

    <nav>
        <p><a href="my-account.php">Retourner sur mon compte</a></p>
        <p><a href="logout.php">Me déconnecter</a></p>
    </nav>

    <?php

    $pdo = new PDO('sqlite:database.sqlite', null, null, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ
    ]);
    ?>

    <h1>Mes prêts en cours</h1>

    <section>
        <?php

        // afficher sur la page les livres prêtés en cours
        $email = $_SESSION['user']->email;

        $show = $pdo->prepare('SELECT books.title, borrowed_books.id, borrowed_books.date_borrowed, borrowed_books.date_return, borrowed_books.extension, borrowed_books.email FROM books, borrowed_books WHERE books.id = borrowed_books.book_id AND books.email = :email');
        $show->execute([
            'email' => $email
        ]);
        $books = $show->fetchAll();

        if ($books) :
            foreach ($books as $book) :

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
                    <p>Date d'emprunt : <strong><?php echo $date_borrowed_ok; ?></strong></p>
                    <p>Date de retour : <strong><?php echo $date_return_ok; ?></strong></p>
                    <p>Personne qui emprunte : <strong><?php echo $book->email; ?></strong></p>

                    <!-- Indiquer le livre comme étant rendu -->
                    <form action="returned-book.php" method="POST">
                        <input type="hidden" name="returned" value="<?php echo $book->id ?>" />
                        <input type="submit" value="Indiquer comme rendu" />
                    </form>
                </article>

            <?php endforeach; ?>

        <?php else : ?>

            <p style="text-align: center"><em>Aucun livre prêté pour l'instant</em></p>

        <?php endif; ?>
    </section>


    <h1>Mes livres proposés au prêt</h1>

    <section>
        <?php
        // afficher sur la page les livres proposés au prêt
        $all = $pdo->prepare('SELECT * FROM books WHERE email = :email');
        $all->execute([
            'email' => $email
        ]);
        $all_books = $all->fetchAll();

        if ($all_books) :
            foreach ($all_books as $all_book) : ?>

                <article class="book-card">
                    <p>Titre : <strong><?php echo $all_book->title ?></strong></p>
                    <p>Author : <strong><?php echo $all_book->author ?></strong></p>

                    <!-- Rendre le livre indisponible -->
                    <?php if ($all_book->available == 1) : ?>
                        <form action="available.php" method="POST">
                            <input type="hidden" name="non-available" value="<?php echo $all_book->id ?>" />
                            <input type="submit" value="Indiquer comme indisponible" />
                        </form>

                        <!-- Rendre le livre disponible -->
                    <?php elseif ($all_book->available == 0) : ?>

                        <!-- Vérifier que le livre n'est pas indisponible parce qu'il est emprunté -->
                        <?php
                        $book_id = $all_book->id;
                        $borrow = $pdo->prepare('SELECT * FROM borrowed_books WHERE book_id = :book_id');
                        $borrow->execute([
                            'book_id' => $book_id
                        ]);
                        $is_borrowed = $borrow->fetch();

                        if ($is_borrowed) :
                        ?>

                            <p><strong><em>Livre emprunté</em></strong></p>

                        <?php else : ?>
                            <p><strong><em>Livre non disponible</em></strong></p>
                            <form action="available.php" method="POST">
                                <input type="hidden" name="available" value="<?php echo $all_book->id ?>" />
                                <input type="submit" value="Indiquer comme disponible" />
                            </form>
                        <?php endif; ?>

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

    <h1>Vous devez être connecté.e pour avoir accès à cette page</h1>

<?php endif ?>

</body>

</html>