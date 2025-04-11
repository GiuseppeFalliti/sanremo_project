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

    // Controllo dei campi vuoti
    if (empty($username) || empty($email) || empty($password)) {
        header("Location: ../register.php?error=" . urlencode("Compila tutti i campi!"));
        exit();
    }

    try {
        // Controlla se l'utente esiste già
        $stmt = $conn->prepare("SELECT id FROM utenti WHERE username = ? OR email = ?");
        $stmt->bind_param("ss", $username, $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            header("Location: ../register.php?error=" . urlencode("Username o email già in uso!"));
            exit();
        }

        // Hash della password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        // Inserisci il nuovo utente nel database
        $stmt = $conn->prepare("INSERT INTO utenti (username, email, password) VALUES (?, ?, ?)");
        
        if ($stmt === false) {
            header("Location: ../register.php?error=" . urlencode("Errore di sistema: " . $conn->error));
            exit();
        }

        $stmt->bind_param("sss", $username, $email, $hashed_password);

        if ($stmt->execute()) {
            // Ottieni l'ID del nuovo utente
            $user_id = $conn->insert_id;
            
            // Imposta le variabili di sessione
            $_SESSION['user_id'] = $user_id;
            $_SESSION['username'] = $username;
            $_SESSION['email'] = $email;
            
            // Reindirizza alla home
            header("Location: ../index.php");
            exit();
        } else {
            header("Location: ../register.php?error=" . urlencode("Errore nella registrazione: " . $stmt->error));
            exit();
        }
        
    } catch (Exception $e) {
        header("Location: ../register.php?error=" . urlencode("Errore nel database: " . $e->getMessage()));
        exit();
    }
}

// Se si accede direttamente a questa pagina senza POST, reindirizza alla pagina di registrazione
header("Location: ../register.php");
exit();
?>

