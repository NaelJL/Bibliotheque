<?php
session_start();
require 'head.php';
?>

<!-- Si l'utilisateurice est connecté-e et le cookie time valide -->
<?php
// if ($_SESSION['user'] && isset($_COOKIE[$cookie_name]) && time() < $_COOKIE[$cookie_name]) :
if ($_SESSION['user']) :
?>

    <?php
    $pdo = new PDO('sqlite:database.sqlite', null, null, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ
    ]);

    if (isset($_POST['search']) && isset($_POST['page'])) :
        if (!empty($_POST['search']) && !empty($_POST['page'])) :
            if (strlen($_POST['search']) < $max_length) :

                // savoir si la recherche se fait depuis la page user ou admin
                $page = trim(htmlspecialchars($_POST['page']));

                // stocker la recherche dans une variable
                $search_input = trim(htmlspecialchars($_POST['search']));

                $search = $pdo->prepare('SELECT * FROM books WHERE title LIKE :contain');
                $search->execute([
                    'contain' => '%' . $search_input . '%'
                ]);
                $results = $search->fetchAll();

                // si un ou plusieurs livres correspondent à la recherche
                if ($results) :

                    // AFFICHER LES RESULTATS POUR LA PAGE UTILISATEURICE
                    if ($page === "user-page") :
    ?>
                        <section>
                            <!-- boucle foreach pour parcourir l'array des résultats -->
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

                                    $search = $pdo->prepare('SELECT * FROM borrowed_books WHERE id_person_borrowing = :id');
                                    $search->execute([
                                        'id' => $id_person
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

                        <!-- AFFICHER LES RESULTATS POUR LA PAGE ADMINISTRATEURICE-->
                    <?php elseif ($_SESSION['user']->admin == 1 && $page === "admin-page") : ?>

                        <?php // boucle foreach pour parcourir l'array des résultats
                        foreach ($results as $result) :
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
                            </section>
                            <hr />
                            <hr />
                        <?php endforeach; ?>
                    <?php endif; ?>
                <?php else :
                    echo "<p class='error'>Aucun résultat</p>?"; ?>
                <?php endif; ?>
            <?php else :
                echo "<p class='error'>La recherche est trop longue</p>"; ?>
            <?php endif; ?>
        <?php else :
            echo "<p class='error'>La recherche ne peut être vide</p>"; ?>
        <?php endif; ?>
    <?php endif; ?>


    <!-- Si l'utilisateurice n'est pas connecté-e -->
<?php else : ?>

    <nav>
        <p><a href="index.php">Page d'accueil</a></p>
    </nav>

    <h1>Vous devez être connecté.e pour avoir accès à cette page</h1>

<?php endif ?>
</body>

</html>