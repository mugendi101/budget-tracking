<?php
include 'db.php';
session_start();

if (!isset($_SESSION['userId'])) {
    header('Location: login.php');
    exit();
}

$userId = $_SESSION['userId'];

// Fetch total savings amount from the database
$stmt = $conn->prepare("SELECT SUM(savings_amount) AS total_savings FROM savings WHERE user_id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$totalSavings = $row['total_savings'] ?? 0;
$stmt->close();

// Handle form submission for saving savings amount
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['save'])) {
    $savingsAmount = $_POST['savings_amount'];

    // Insert savings amount into database
    $stmt = $conn->prepare("INSERT INTO savings (user_id, savings_amount) VALUES (?, ?)");
    $stmt->bind_param("id", $userId, $savingsAmount);
    $stmt->execute();
    $stmt->close();

    // Redirect to prevent form resubmission
    header('Location: savings.php');
    exit();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Savings - DR.MONEY</title>
    <style>
        body {
            font-family: Arial, sans-serif;
        }
        .navbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px;
            background-color: #f4f4f4;
        }
        .navbar h1 {
            margin: 0;
        }
        .navbar a {
            padding: 10px;
            background-color: green;
            color: white;
            border: none;
            border-radius: 4px;
            text-decoration: none;
            margin-left: 10px;
        }
        
        .navbar a:hover {
            background-color: yellowgreen;
            color: black;
        }
        .container {
            margin-top: 20px;
            padding: 20px;
            background-color: #f4f4f4;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            text-align: center;
        }
        .savings {
            font-size: 24px;
            margin-bottom: 10px;
        }
        form {
            margin-top: 20px;
            text-align: center;
        }
        label {
            display: block;
            margin-bottom: 10px;
        }
        input[type="number"] {
            width: 20%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            margin-bottom: 10px;
            box-sizing: border-box;
        }
        .btn-save {
            background-color: green;
            color: white;
            border: none;
            border-radius: 4px;
            padding: 10px 20px;
            cursor: pointer;
            margin-right: 10px;
        }
        .btn-save:hover {
            background-color: greenyellow;
        }
    </style>
</head>
<body>
    <div class="navbar">
        <h1>DR.MONEY</h1>
        <div>
            <a href="budget.php">Finances</a>
            <a href="calculator.php">Calculator</a>
            <a href="savings.php">Savings</a>
            <a href="report.php">Report</a>
            <a href="logout.php">Logout</a>
        </div>
    </div>
    <div class="container">
        <h2>Savings</h2>
        <div class="savings">
            Total Savings: KSHS. <?php echo formatCurrency($totalSavings); ?>
        </div>

        <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
            <label for="savings_amount">Enter Your Savings Amount:</label>
            <input type="number" id="savings_amount" name="savings_amount" step="0.01" required>
            <button type="submit" name="save" class="btn-save">Save</button>
        </form>
    </div>
</body>
</html>

<?php
// Function to format numbers to two decimal places for display
function formatCurrency($amount) {
    return number_format($amount, 2);
}
?>
