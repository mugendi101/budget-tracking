<?php
include 'db.php';
session_start();

if (!isset($_SESSION['userId'])) {
    header('Location: login.php');
    exit();
}

$userId = $_SESSION['userId'];

// Fetch total income from the database
$stmt = $conn->prepare("SELECT SUM(amount) AS total_income FROM income WHERE user_id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$totalIncome = $row['total_income'];
$stmt->close();

// Calculate 50/30/20 breakdown
$needs = $totalIncome * 0.5;
$wants = $totalIncome * 0.3;
$savingsDebt = $totalIncome * 0.2;

// Save the totalIncome in session for reference
$_SESSION['savedIncome'] = $totalIncome;

// Function to format numbers to two decimal places for display
function formatCurrency($amount) {
    return number_format($amount, 2);
}

// Handle form submission for saving income
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['save'])) {
    // Example code to save to database or session for reference
    // $_SESSION['savedIncome'] = $totalIncome; // Save in session for example

    // Redirect to prevent form resubmission
    header('Location: calculator.php');
    exit();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Income Calculator - DR.MONEY</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
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
            background-color: greenyellow;
            color: black;
        }
        .container {
            max-width: 800px;
            margin: 20px auto;
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        h1 {
            text-align: center;
            margin-bottom: 20px;
        }
        form {
            text-align: center;
        }
        label {
            display: block;
            margin-bottom: 10px;
        }
        input[type="number"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            margin-bottom: 10px;
            box-sizing: border-box;
        }
        .budget-breakdown {
            margin-top: 20px;
            text-align: center;
        }
        .budget-breakdown h2 {
            margin-bottom: 10px;
        }
        .budget-item {
            margin-bottom: 10px;
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
        <h1>Income Calculator</h1>

        <p><strong>Budgeting Rule Explanation:</strong> The 50/30/20 budgeting rule suggests dividing your after-tax income into three categories:</p>
        <ul>
            <li><strong>50% for Needs:</strong> Essential expenses like rent/mortgage, utilities, groceries.</li>
            <li><strong>30% for Wants:</strong> Non-essential expenses like dining out, entertainment, hobbies.</li>
            <li><strong>20% for Savings and Debt Repayment:</strong> Savings, investments, and paying down debt.</li>
        </ul>

        <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
            <label for="income">Your Total Income:</label>
            <input type="text" id="income" name="income" value="KSHS. <?php echo formatCurrency($totalIncome); ?>" readonly>
        </form>

        <div class="budget-breakdown">
            <h2>Budget Breakdown</h2>
            <div class="budget-item">
                <strong>50% Needs:</strong> KSHS. <?php echo formatCurrency($needs); ?>
            </div>
            <div class="budget-item">
                <strong>30% Wants:</strong> KSHS. <?php echo formatCurrency($wants); ?>
            </div>
            <div class="budget-item">
                <strong>20% Savings and Debt Repayment:</strong> KSHS. <?php echo formatCurrency($savingsDebt); ?>
            </div>
        </div>
    </div>
</body>
</html>
