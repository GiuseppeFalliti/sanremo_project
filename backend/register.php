<?php
session_start();
include 'config.php';

// Abilita la visualizzazione degli errori
error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["username"], $_POST["email"], $_POST["password"])) {
    $username = trim($_POST["username"]);
    $email = trim($_POST["email"]);
    $password = trim($_POST["password"]);

    if (!empty($username) && !empty($email) && !empty($password)) {
        try {
            // Controlla se l'utente esiste già
            $stmt = $conn->prepare("SELECT id FROM utenti WHERE username = ? OR email = ?");
            $stmt->bind_param("ss",$username, $email);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $error = "Username o email già in uso!";
            } else {
                // Hash della password
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                
                // Inserisci il nuovo utente nel database
                $stmt = $conn->prepare("INSERT INTO utenti (username, email, password) VALUES (?, ?, ?)");
                
                if ($stmt === false) {
                    die("Errore nella preparazione della query: " . $conn->error);
                }

                $stmt->bind_param("sss",$username, $email, $hashed_password);

                if ($stmt->execute()) {
                    
                    $_SESSION['username'] = $username;
                    $_SESSION['email'] = $email;
                    header("Location: ../index.php"); 
                    // Gestione errori completa
                    if (isset($error)) {
                        header("Location: ../register.php?error=" . urlencode($error));
                        exit();
                    }
                    exit();
                } else {
                    $error = "Errore nella registrazione!";
                }
                $stmt->close();
            }
        } catch (PDOException $e) {
            $error = "Errore nel database: " . $e->getMessage();
        }
    } else {
        $error = "Compila tutti i campi!";
    }
    $conn->close();
}

?>

