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

    <h1>Mes emprunts en cours</h1>

    <?php

    $pdo = new PDO('sqlite:database.sqlite', null, null, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ
    ]);

    $email = $_SESSION['user']->email;

    // aller chercher l'id de la personne grâce à son email
    $person = $pdo->prepare('SELECT * FROM accounts WHERE email = :email');
    $person->execute([
        'email' => $email
    ]);
    $person_profile = $person->fetch();

    if ($person_profile) {
        $id_person_borrowing = $person_profile->id;

        // afficher sur la page les livres empruntés
        $show = $pdo->prepare('SELECT books.title, books.id_person, borrowed_books.id, borrowed_books.date_borrowed, borrowed_books.date_return, borrowed_books.extension, borrowed_books.book_id FROM books, borrowed_books WHERE books.id = borrowed_books.book_id AND borrowed_books.id_person_borrowing = :id_person_borrowing ORDER BY books.id_person');
        $show->execute([
            'id_person_borrowing' => $id_person_borrowing
        ]);
        $borrowed_books = $show->fetchAll();
    }
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

                    <?php
                    // récupérer l'email de la personne à contacter
                    $id = $book->id_person;
                    $person = $pdo->prepare('SELECT email FROM accounts WHERE id = :id');
                    $person->execute([
                        'id' => $id
                    ]);
                    $person_profile = $person->fetch();
                    $person_email = $person_profile->email;
                    ?>
                    <p>Email de la personne à contacter : <strong><?php echo $person_email ?></strong></p>

                    <p>Date d'emprunt : <strong><?php echo $date_borrowed_ok; ?></strong></p>
                    <p>Date de retour : <strong><?php echo $date_return_ok; ?></strong></p>

                    <?php if ($book->extension == 0) : ?>
                        <form action="extension.php" method="POST">
                            <input type="hidden" name="extension" value="<?php echo $book->book_id ?>" />
                            <input type="submit" value="Prolonger de 3 semaines" />
                        </form>
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

    <h1>Vous devez être connecté.e pour avoir accès à cette page</h1>

<?php endif ?>

</body>

</html>