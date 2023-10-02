<?php

use rtff\database\DatabaseConnexion;

require_once './DatabaseConnexion.php';

$account_id = $_POST['account_id'];

$database = new DatabaseConnexion();
$db = $database->getConnection();


$query = "SELECT account_id FROM ACCOUNT WHERE account_id = :account_id";
$stmt = $db->prepare($query);
$stmt->bindParam(':account_id', $account_id);

if ($stmt->execute()) {
if ($stmt->rowCount() > 0) {

    $bndary = md5(uniqid(mt_rand()));
    $query = "INSERT INTO TOKEN (token_id, account_id, date_creation) VALUES (:bndary, :account_id , NOW())";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':account_id', $account_id);
    $stmt->bindParam(':bndary', $bndary);
    $stmt->execute();

$link = "http://rtff.alwaysdata.net/modifyPasswordUser.php?token=" . $bndary;

$to = $account_id;
$subject = 'Test mail';

$headers = 'Content-type: multipart/alternative; boundary="' . $bndary . '"';
// Message texte brut
$message_text = 'Cliquez sur le bouton ci-dessous pour changer de mots de passe : rtff.alwaysdata.net/modifyPasswordUser.php?token=bndary';

// Message HTML
$message_html = '
<html>
<head>
    <title>Changement de mot de passe</title>
</head>
<body style="background-color: #2c2a2e; color: white; text-align: center; padding: 20px;">
<p>Cliquez sur le bouton ci-dessous pour changer de mots de passe :</p>
<a href='.$link.' style="display: inline-block; padding: 10px 20px; background-color: #b0baff; color: #000; text-decoration: none; border-radius: 5px;">Cliquez ici</a>
</body>
</html>
';

$message = '--' . $bndary . "\n";
$message .= $message_text . "\n\n";
$message .= 'Content-Type: text/plain; charset=utf-8' . "\n\n";
$message .= '--' . $bndary . "\n";
$message .= 'Content-Type: text/html; charset=utf-8' . "\n\n";
$message .= $message_html . "\n\n";

// Envoyer l'e-mail
if (mail($to, $subject, $message, $headers))
echo "Mail envoyé avec succès.";
else
echo "Un problème est survenu.";
exit;
} else {
// Aucun utilisateur trouvé avec cet email
echo " Aucun utilisateur trouvé avec cet email ";
}
}