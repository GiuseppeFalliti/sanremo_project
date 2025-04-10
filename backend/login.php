<?php
session_start();
include 'config.php'; 
// Controllo se esiste già una sessione attiva
if (isset($_SESSION['username'])) {
    $username = $_SESSION['username'];
    $email = $_SESSION['email'];
    header("Location: ../index.php"); 
    exit();
} 

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Preparazione della query per ottenere l'utente
    $query = "SELECT id, username, password FROM utenti WHERE username = ?;";
    $stmt = $conn->prepare($query);

    if($stmt){
    $stmt->bind_param("s", $username); 
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        if (password_verify($password, $user['password'])) {
            // Password corretta: avvia la sessione
            $_SESSION['username'] = $row['username'];
            $_SESSION['email']= $row['email'];
            header("Location: ../index.php"); 
            exit;
        } else {
            // Credenziali errate
            echo "Nome utente o password non validi.";
        }

    }
    else {
        echo "Nome utente non trovato.";
    }
    
    } else {
        echo "Errore nella preparazione della query: " . $conn->error;
    }
    $stmt->close();
}
$conn->close();  

?>




