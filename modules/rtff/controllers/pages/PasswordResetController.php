<?php
namespace rtff\controllers\pages;

use PDO;

class PasswordResetController {
    public function resetPassword() {
        $message = '';
        if (isset($_GET['token'])) {
            $token = $_GET['token'];
            $database = \rtff\database\DatabaseConnexion::getInstance();
            $db = $database->getConnection();

            $query = "SELECT * FROM TOKEN WHERE token_id = :token_id AND date_creation >= NOW() - INTERVAL 30 MINUTE";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':token_id', $token);
            $stmt->execute();

            if ($stmt->rowCount() == 0) {
                $message = "Token invalide ou expiré !";
                $query = "DELETE FROM TOKEN WHERE token_id = :token_id";
                $stmt = $db->prepare($query);
                $stmt->bindParam(':token_id', $token);
                $stmt->execute();
            } else {
                $account = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($_SERVER["REQUEST_METHOD"] == "POST") {
                    $new_password = password_hash($_POST['new_password'], PASSWORD_BCRYPT);

                    $query = "UPDATE ACCOUNT SET password = :new_password WHERE account_id= :account_id";
                    $stmt = $db->prepare($query);
                    $stmt->bindParam(':new_password', $new_password);
                    $stmt->bindParam(':account_id', $account['account_id']);

                    if ($stmt->execute()) {
                        $message = "Mot de passe modifié avec succès !";
                        // Vous pourriez vouloir supprimer le token après l'utilisation
                        $query = "DELETE FROM TOKEN WHERE token_id = :token_id";
                        $stmt = $db->prepare($query);
                        $stmt->bindParam(':token_id', $token);
                        $stmt->execute();
                    } else {
                        $message = "Une erreur est survenue lors de la modification du mot de passe.";
                    }
                }
            }
        } else {
            $message = "Paramètre token manquant !";
        }

        // Chargez la vue pour afficher le résultat
        $view = new \rtff\views\PasswordResetView();
        $view->render($message, $token);
    }
}
?>