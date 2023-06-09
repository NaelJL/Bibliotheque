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
        $user_id = $_SESSION['user']->id;

        $show = $pdo->prepare('SELECT books.title, borrowed_books.id, borrowed_books.date_borrowed, borrowed_books.date_return, borrowed_books.extension, borrowed_books.id_person_borrowing FROM books, borrowed_books WHERE books.id = borrowed_books.book_id AND books.id_person = :user_id');
        $show->execute([
            'user_id' => $user_id
        ]);
        $books = $show->fetchAll();

        if ($books) :
            foreach ($books as $book) : ?>

                <article class="book-card">
                    <p>Titre : <strong><?php echo $book->title ?></strong></p>
                    <p>Date d'emprunt : <strong><?php format_date($book->date_borrowed); ?></strong></p>
                    <p>Date de retour : <strong><?php format_date($book->date_return); ?></strong></p>

                    <?php
                    // Indiquer l'email de la personne qui emprunte
                    $id_person_borrowing = $book->id_person_borrowing;
                    $person_borrowing = $pdo->prepare('SELECT email FROM accounts WHERE id = :id_person_borrowing');
                    $person_borrowing->execute([
                        'id_person_borrowing' => $id_person_borrowing
                    ]);
                    $person_borrowing_profile = $person_borrowing->fetch();

                    if ($person_borrowing_profile) :
                        $email = $person_borrowing_profile->email;
                    ?>
                        <p>Personne qui emprunte : <strong><?php echo $email; ?></strong></p>
                    <?php endif; ?>

                    <!-- Indiquer le livre comme étant rendu (avec bouton de confirmation) -->
                    <button class="button">Indiquer le livre comme rendu</button>
                    <div class="form">
                        <form action="returned-book.php" method="POST">
                            <input type="hidden" name="returned" value="<?php echo $book->id ?>" />
                            <input type="submit" value="Confirmez le retour" style="margin: 5px auto" />
                        </form>
                    </div>
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
        $all = $pdo->prepare('SELECT * FROM books WHERE id_person = :user_id');
        $all->execute([
            'user_id' => $user_id
        ]);
        $all_books = $all->fetchAll();

        if ($all_books) :
            foreach ($all_books as $all_book) : ?>

                <article class="book-card">
                    <p>Titre : <strong><?php echo $all_book->title ?></strong></p>
                    <p>Author : <strong><?php echo $all_book->author ?></strong></p>

                    <!-- Un livre peut être available == 0 quand il est emprunté ou indisponible -->
                    <!-- Vérifier si le livre est emprunté : -->
                    <?php
                    $book_id = $all_book->id;
                    $borrow = $pdo->prepare('SELECT * FROM borrowed_books WHERE book_id = :book_id');
                    $borrow->execute([
                        'book_id' => $book_id
                    ]);
                    $is_borrowed = $borrow->fetch();

                    if ($is_borrowed) :
                    ?>
                        <!-- S'il est bien emprunté, ne rien pouvoir faire -->
                        <p><strong><em>Livre emprunté</em></strong></p>

                    <?php
                    // S'il n'est pas emprunté mais simplement indisponible
                    elseif (!$is_borrowed && $all_book->available == 0) :
                    ?>

                        <!-- Pouvoir le rendre disponible -->
                        <p><strong><em>Livre non disponible</em></strong></p>
                        <form action="available.php" method="POST">
                            <input type="hidden" name="available" value="<?php echo $all_book->id ?>" />
                            <input type="submit" value="Indiquer comme disponible" style="margin: 5px auto" />
                        </form>

                    <?php
                    // S'il n'est pas emprunté et disponible
                    elseif (!$is_borrowed && $all_book->available == 1) :
                    ?>
                        <!-- Pouvoir le rendre indiponible -->
                        <form action="available.php" method="POST">
                            <input type="hidden" name="non-available" value="<?php echo $all_book->id ?>" />
                            <input type="submit" value="Indiquer comme indisponible" style="margin: 5px auto" />
                        </form>

                        <!-- Pouvoir le modifier -->
                        <form action="modif-book.php" method="POST">
                            <input type="hidden" name="modification" value="<?php echo $all_book->id ?>" />
                            <input type="submit" value="Modifier le livre" style="margin: 5px auto" />
                        </form>

                        <!-- Pouvoir le supprimer (avec bouton de confirmation) -->
                        <button class="button">Supprimer le livre</button>
                        <div class="form">
                            <form action="delete-book.php" method="POST">
                                <input type="hidden" name="delete" value="<?php echo $all_book->id ?>" />
                                <input type="submit" value="Confirmez la suppression" style="margin: 5px auto" />
                            </form>
                        </div>
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