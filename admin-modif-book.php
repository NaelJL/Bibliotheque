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

    <h1>Modifier un livre</h1>

    <section>
        <form action="" method="POST">
            <label>Livre concerné :</label>
            <input type="text" name="title" value="" placeholder="Titre" required />
            <input type="text" name="author" value="" placeholder="Auteur-ice" required />
            <input type="submit" value="Valider" />
        </form>
    </section>

    <?php
    $pdo = new PDO('sqlite:database.sqlite', null, null, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ
    ]);

    ?>


    <!-- Si l'utilisateurice n'est pas connecté-e -->
<?php else : ?>

    <nav>
        <p><a href="index.php">Page d'accueil</a></p>
    </nav>

    <h1>Vous devez être administrateur-ice pour avoir accès à cette page</h1>

<?php endif ?>

</body>

</html>