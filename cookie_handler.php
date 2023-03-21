<?php
// créer un cookie qui se détruit au bout de 20 minutes pour stocker l'heure de la dernière activité 
$cookie_name = "cookie_time";
setcookie($cookie_name, time(), time() + 1200);

// si le cookie est présent - pas encore 20 minutes écoulé : cookie remis à zéro
if (isset($_COOKIE[$cookie_name])) {
    setcookie($cookie_name, time(), time() + 1200);

    // si le cookie n'est pas présent - 20 minutes écoulées : utilisateurice déconnecté, cookie supprimé
} else {
    setcookie($cookie_name, '', time() - 3600);
    session_destroy();
    header('Location: login.php');
    exit();
}
