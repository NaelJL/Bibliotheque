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
        <p><a href="my-account.php">Retourner sur mon compte</a></p>
        <p><a href="logout.php">Me déconnecter</a></p>
    </nav>

    <h1>Gestion - compte admin</h1>

    <section>
        <p><a href="admin-modif-data.php" class="a-reverse-color">Modifier les informations personnelles de quelqu'un-e</a></p>
        <p><a href="admin-modif-book.php" class="a-reverse-color">Modifier les informations d'un livre</a></p>
        <p><a href="admin-books-borrowed.php" class="a-reverse-color">Voir tous livres empruntés</a></p>
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