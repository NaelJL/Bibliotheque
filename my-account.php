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
        <?php if ($_SESSION['user']->admin == 1) : ?>
            <p><a href="admin.php">Compte admin</a></p>
        <?php endif; ?>
        <p><a href="logout.php">Me déconnecter</a></p>
    </nav>

    <h1>
        <?php
        $user = $_SESSION['user'];
        echo "Bonjour $user->name, que souhaites-tu faire ?";
        ?>
    </h1>

    <section>
        <p><a href="books-shared.php" class="a-reverse-color">Livres prêtés</a></p>
        <p><a href="books-borrowed.php" class="a-reverse-color">Livres empruntés</a></p>
        <p><a href="list-books.php" class="a-reverse-color">Trouver un livre</a></p>
        <p><a href="share-a-book.php" class="a-reverse-color">Proposer un livre au prêt</a></p>
        <p><a href="modif-data.php" class="a-reverse-color">Modifier mes informations personnelles</a></p>
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