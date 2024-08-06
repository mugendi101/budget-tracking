<?php
include 'db.php';
session_start();

if (!isset($_SESSION['userId'])) {
    header('Location: login.php');
    exit();
}

$userId = $_SESSION['userId'];

// Initialize total values
$totalIncome = 0;
$totalExpenses = 0;

// Fetch month and year
$month = date('F');
$year = date('Y');

// Fetch income from database
$incomeResults = $conn->prepare("SELECT label, amount, date FROM income WHERE user_id = ?");
$incomeResults->bind_param("i", $userId);
$incomeResults->execute();
$incomeResults->bind_result($incomeLabel, $incomeAmount, $incomeDate);
$incomeData = [];
while ($incomeResults->fetch()) {
    $incomeData[] = ['label' => $incomeLabel, 'amount' => $incomeAmount, 'date' => $incomeDate];
    $totalIncome += $incomeAmount;
}
$incomeResults->close();

// Fetch expenses from database
$expenseResults = $conn->prepare("SELECT label, amount, date FROM expenses WHERE user_id = ?");
$expenseResults->bind_param("i", $userId);
$expenseResults->execute();
$expenseResults->bind_result($expenseLabel, $expenseAmount, $expenseDate);
$expenseData = [];
while ($expenseResults->fetch()) {
    $expenseData[] = ['label' => $expenseLabel, 'amount' => $expenseAmount, 'date' => $expenseDate];
    $totalExpenses += $expenseAmount;
}
$expenseResults->close();


// Fetch savings from database
$savingsResults = $conn->prepare("SELECT SUM(savings_amount) AS total_savings FROM savings WHERE user_id = ?");
$savingsResults->bind_param("i", $userId);
$savingsResults->execute();
$savingsResults->bind_result($totalSavings);
$savingsResults->fetch();
$savingsResults->close();

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Monthly Report - DR.MONEY</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
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
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table, th, td {
            border: 1px solid #ddd;
        }
        th, td {
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        .total {
            margin-top: 20px;
            text-align: right;
        }
        .total label {
            font-weight: bold;
        }
        .button-container {
            text-align: center;
            margin-top: 20px;
        }
        .button-container button {
            padding: 10px 20px;
            background-color: green;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
    </style>
</head>
<body>
<div class="navbar">
        <h1>DR.MONEY</h1>
        <div>
            <a href="budget.php">Budget</a>
            <a href="calculator.php">Calculator</a>
            <a href="savings.php">Savings</a>
            <a href="report.php">Report</a>
            <a href="logout.php">Logout</a>
        </div>
        </div>
    <div class="container">
        <h1><?php echo $month . " " . $year; ?> Report</h1>
        
        <h2>Income</h2>
        <table>
            <thead>
                <tr>
                    <th>Label</th>
                    <th>Date</th>
                    <th>Amount</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($incomeData as $income): ?>
                <tr>
                    <td><?php echo htmlspecialchars($income['label']); ?></td>
                    <td><?php echo htmlspecialchars($income['date']); ?></td>
                    <td><?php echo number_format($income['amount'], 2); ?></td>
                </tr>
                <?php endforeach; ?>
                <tr>
                    <td colspan="2"><label>Total Income</label></td>
                    <td>KSHS.<?php echo number_format($totalIncome, 2); ?></td>
                </tr>
            </tbody>
        </table>

        <h2>Expenses</h2>
        <table>
            <thead>
                <tr>
                    <th>Label</th>
                    <th>Date</th>
                    <th>Amount</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($expenseData as $expense): ?>
                <tr>
                    <td><?php echo htmlspecialchars($expense['label']); ?></td>
                    <td><?php echo htmlspecialchars($expense['date']); ?></td>
                    <td><?php echo number_format($expense['amount'], 2); ?></td>
                </tr>
                <?php endforeach; ?>
                <tr>
                    <td colspan="2"><label>Total Expenses</label></td>
                    <td>KSHS.<?php echo number_format($totalExpenses, 2); ?></td>
                </tr>
            </tbody>
        </table>


        <div class="total">
            <label>Total Income: KSHS.<?php echo number_format($totalIncome, 2); ?></label><br>
            <label>Total Expenses: KSHS.<?php echo number_format($totalExpenses, 2); ?></label><br>
            <label>Total Savings: KSHS.<?php echo number_format($totalSavings, 2); ?></label><br>
            </div>

        <div class="button-container">
            <button onclick="window.print()">Print Report</button>
            
        </div>
    </div>
</body>
</html>
