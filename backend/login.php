<?php
session_start();
include 'config.php'; 

// Controllo se esiste già una sessione attiva
if (isset($_SESSION['user_id'])) {
    header("Location: ../index.php"); 
    exit();
} 

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Verifica che i campi siano stati inviati
    if (!isset($_POST['username']) || !isset($_POST['password'])) {
        header("Location: ../login.php?error=" . urlencode("Tutti i campi sono obbligatori"));
        exit();
    }

    $username = $_POST['username'];
    $password = $_POST['password'];

    // Preparazione della query per ottenere l'utente
    $query = "SELECT id, username, email, password FROM utenti WHERE username = ?";
    $stmt = $conn->prepare($query);

    if ($stmt) {
        $stmt->bind_param("s", $username); 
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                // Password corretta: avvia la sessione
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['email'] = $user['email'];
                
                header("Location: ../index.php"); 
                exit();
            } else {
                // Password errata
                header("Location: ../login.php?error=" . urlencode("Password non valida"));
                exit();
            }
        } else {
            // Username non trovato
            header("Location: ../login.php?error=" . urlencode("Username non trovato"));
            exit();
        }
        
        $stmt->close();
    } else {
        header("Location: ../login.php?error=" . urlencode("Errore di sistema. Riprova più tardi."));
        exit();
    }
}

$conn->close();
?>




