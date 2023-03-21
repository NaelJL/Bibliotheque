<?php session_start();

// générer une captcha par session
$_SESSION['captcha'] = mt_rand(1000, 9999);

$img = imagecreate(70, 30);
$font = './assets/28DaysLater.ttf';
$background = imagecolorallocate($img, 255, 255, 255);
$textColor = imagecolorallocate($img, 0, 0, 0);

// convertir du texte en image 
imagettftext($img, 23, 0, 3, 30, $textColor, $font, $_SESSION['captcha']);

// supprimer l'image à chaque fois
header('Content-type:image/jpeg');
imagejpeg($img);
imagedestroy($img);
