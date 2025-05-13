<?php
$host = 'localhost';
$db = 'skincare_db';
$user = 'root'; // or your MySQL username
$pass = '';     // your MySQL password

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $skinType = strtolower(trim($_POST['skin_type']));
        $age = intval($_POST['age']);
        $description = strtolower(trim($_POST['description']));

        $keywords = explode(" ", $description);
        $response = null;

        // Search DB for keyword match in user input
        foreach ($keywords as $word) {
            $stmt = $pdo->prepare("SELECT ceramide, remedy FROM recommendations WHERE keyword = :keyword");
            $stmt->execute(['keyword' => $word]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($result) {
                $response = $result;
                break;
            }
        }

        // If no keyword matched, fallback to skin type
        if (!$response) {
            $stmt = $pdo->prepare("SELECT ceramide, remedy FROM recommendations WHERE keyword = :keyword");
            $stmt->execute(['keyword' => $skinType]);
            $response = $stmt->fetch(PDO::FETCH_ASSOC);
        }

        // Output result
        if ($response) {
            echo "<h2>Skincare Recommendation</h2>";
            echo "<p><strong>Ceramide Product:</strong> " . htmlspecialchars($response['ceramide']) . "</p>";
            echo "<p><strong>Home Remedy:</strong> " . htmlspecialchars($response['remedy']) . "</p>";
        } else {
            echo "<p>No recommendation found.</p>";
        }
    } else {
        echo "Invalid request.";
    }

} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage();
}
?>
