<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST");
header("Access-Control-Allow-Headers: Content-Type");
header('Content-Type: application/json');

register_shutdown_function(function() {
    $error = error_get_last();
    if ($error !== null) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => 'Errore interno del server: ' . $error['message']
        ]);
    }
});

session_start();
include 'config.php';
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (isset($_GET['query'])) {
    $searchTerm = trim($_GET['query']);
    $search = '%' . strtolower($searchTerm) . '%';
    $results = array();
    
    try {
        // Query per le canzoni
        $querySongs = "SELECT 
            c.id,
            TRIM(c.titolo) as titolo, 
            a.nome as artista,
            c.anno
        FROM canzoni c
        INNER JOIN artisti a ON c.artista_id = a.id
        WHERE LOWER(TRIM(c.titolo)) LIKE ?
        ORDER BY c.anno DESC
        LIMIT 5";
        
        $stmtSongs = $conn->prepare($querySongs);
        $stmtSongs->bind_param("s", $search);
        $stmtSongs->execute();
        $resultSongs = $stmtSongs->get_result();
        $songs = $resultSongs->fetch_all(MYSQLI_ASSOC);

        // Query per gli artisti
        $queryArtists = "SELECT DISTINCT
            a.id,
            a.nome
        FROM artisti a
        WHERE LOWER(a.nome) LIKE ?
        LIMIT 3";
        
        $stmtArtists = $conn->prepare($queryArtists);
        $stmtArtists->bind_param("s", $search);
        $stmtArtists->execute();
        $resultArtists = $stmtArtists->get_result();
        $artists = $resultArtists->fetch_all(MYSQLI_ASSOC);

        // Processa risultati artisti
        foreach ($artists as $artist) {
            $queryArtistSongs = "SELECT titolo 
                FROM canzoni 
                WHERE artista_id = ? 
                ORDER BY anno DESC 
                LIMIT 2";
            
            $stmtArtistSongs = $conn->prepare($queryArtistSongs);
            $stmtArtistSongs->bind_param("i", $artist['id']);
            $stmtArtistSongs->execute();
            $resultArtistSongs = $stmtArtistSongs->get_result();
            $artistSongs = [];
            while ($row = $resultArtistSongs->fetch_array(MYSQLI_NUM)) {
                $artistSongs[] = $row[0];
            }
            
            $results[] = [
                'type' => 'artist',
                'id' => $artist['id'],
                'name' => $artist['nome'],
                'songs' => $artistSongs
            ];
        }

        foreach ($songs as $song) {
            $results[] = [
                'type' => 'song',
                'id' => $song['id'],
                'title' => $song['titolo'],
                'artist' => $song['artista'],
                'year' => $song['anno']
            ];
        }

        echo json_encode([
            'success' => true, 
            'results' => $results
        ]);

    } catch (Exception $e) {
        echo json_encode([
            'success' => false, 
            'error' => 'Errore database: ' . $e->getMessage()
        ]);
    }
} else {
    echo json_encode([
        'success' => false, 
        'error' => 'Query mancante'
    ]);
}