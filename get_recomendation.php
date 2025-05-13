<?php
$host = 'localhost';
$db = 'skincare_db';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $skinType = strtolower(trim($_POST['skin_type']));
        $age = intval($_POST['age']);
        $description = strtolower(trim($_POST['description']));

        $keywords = explode(" ", $description);
        $response = null;

        // Try keyword match
        foreach ($keywords as $word) {
            $stmt = $pdo->prepare("SELECT ceramide, remedy FROM recommendations WHERE keyword = :keyword");
            $stmt->execute(['keyword' => $word]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($result) {
                $response = $result;
                break;
            }
        }

        // Fallback to skin type
        if (!$response) {
            $stmt = $pdo->prepare("SELECT ceramide, remedy FROM recommendations WHERE keyword = :keyword");
            $stmt->execute(['keyword' => $skinType]);
            $response = $stmt->fetch(PDO::FETCH_ASSOC);
        }

        if ($response) {
            // Load the template
            $template = file_get_contents("recommendation.html");

            // Replace placeholders
            $filled = str_replace(
                ['{{ceramide}}', '{{remedy}}'],
                [htmlspecialchars($response['ceramide']), htmlspecialchars($response['remedy'])],
                $template
            );

            echo $filled;
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
