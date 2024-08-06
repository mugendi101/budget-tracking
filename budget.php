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
$incomeResults = $conn->prepare("SELECT id, label, amount, date FROM income WHERE user_id = ?");
$incomeResults->bind_param("i", $userId);
$incomeResults->execute();
$incomeResults->bind_result($incomeId, $incomeLabel, $incomeAmount, $incomeDate);
$incomeData = [];
while ($incomeResults->fetch()) {
    $incomeData[] = ['id' => $incomeId, 'label' => $incomeLabel, 'amount' => $incomeAmount, 'date' => $incomeDate];
    $totalIncome += $incomeAmount;
}
$incomeResults->close();

// Fetch expenses from database
$expenseResults = $conn->prepare("SELECT id, label, amount, date FROM expenses WHERE user_id = ?");
$expenseResults->bind_param("i", $userId);
$expenseResults->execute();
$expenseResults->bind_result($expenseId, $expenseLabel, $expenseAmount, $expenseDate);
$expenseData = [];
while ($expenseResults->fetch()) {
    $expenseData[] = ['id' => $expenseId, 'label' => $expenseLabel, 'amount' => $expenseAmount, 'date' => $expenseDate];
    $totalExpenses += $expenseAmount;
}
$expenseResults->close();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['income_submit'])) {
        $label = $_POST['income_label'];
        $amount = $_POST['income_amount'];
        $date = $_POST['income_date'];
        
        // Insert income into database
        $stmt = $conn->prepare("INSERT INTO income (user_id, label, amount, date) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("isds", $userId, $label, $amount, $date);
        $stmt->execute();
        $stmt->close();

        header("Location: budget.php");
        exit();
    }

    if (isset($_POST['expense_submit'])) {
        $label = $_POST['expense_label'];
        $amount = $_POST['expense_amount'];
        $date = $_POST['expense_date'];
        
        // Insert expense into database
        $stmt = $conn->prepare("INSERT INTO expenses (user_id, label, amount, date) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("isds", $userId, $label, $amount, $date);
        $stmt->execute();
        $stmt->close();

        header("Location: budget.php");
        exit();
    }     
}

    
    // Handle edit expense submission
    if (isset($_POST['edit_expense'])) {
        $expenseId = $_POST['expense_id'];
        $label = $_POST['expense_label'];
        $amount = $_POST['expense_amount'];
        $date = $_POST['expense_date'];

        // Update expense in database
        $stmt = $conn->prepare("UPDATE expenses SET label = ?, amount = ?, date = ? WHERE id = ?");
        $stmt->bind_param("sdsi", $label, $amount, $date, $expenseId);
        $stmt->execute();
        $stmt->close();

        header("Location: budget.php");
        exit();
    }

    // Handle delete expense submission
    if (isset($_POST['delete_expense'])) {
        $expenseId = $_POST['expense_id'];

        // Delete expense from database
        $stmt = $conn->prepare("DELETE FROM expenses WHERE id = ?");
        $stmt->bind_param("i", $expenseId);
        $stmt->execute();
        $stmt->close();

        header("Location: budget.php");
        exit();
    }

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Budget - DR.MONEY</title>
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
            display: flex;
            justify-content: space-between;
            margin-top: 20px;
        }
        .box, .table-container {
            width: 45%;
            padding: 20px;
            background-color: #f4f4f4;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            text-align: center;
            margin-bottom: 20px;
        }
        .box h2 {
            margin: 0;
        }
        .table-container {
            margin-top: 20px;
            padding: 20px;
            background-color: #f4f4f4;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        table {
            width: 100%;
            border-collapse: collapse;
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
        .add-button {
            display: block;
            margin: 20px 0;
            padding: 10px;
            background-color: green;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        
        .add-button:hover {
            background-color: yellowgreen;
            color: black;
        }

.modal {
    display: none;
    position: fixed;
    z-index: 1;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    overflow: auto;
    background-color: rgba(0,0,0,0.4);
    padding-top: 60px;
}

.modal-content {
    background-color: #fefefe;
    margin: 5% auto;
    padding: 20px;
    border: 1px solid #888;
    width: 80%;
    max-width: 600px; /* Adjust max-width as needed */
    border-radius: 8px;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
}

.modal-content form {
    display: none; /* Hide all forms initially */
}

.modal-content form.active {
    display: block; /* Show the active form */
}

.modal-content label {
    display: block;
    margin-bottom: 10px;
}

.modal-content input[type=text],
.modal-content input[type=date],
.modal-content input[type=number] {
    width: calc(100% - 20px); /* Adjust width minus padding */
    padding: 8px;
    margin-bottom: 10px;
    border: 1px solid #ccc;
    border-radius: 4px;
}

.modal-content button {
    padding: 10px;
    background-color: green;
    color: white;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    margin-right: 10px;
}

.modal-content button.cancel {
    background-color: red;
}

.modal-content button:hover {
    background-color: yellowgreen;
}

.close {
    color: #aaa;
    float: right;
    font-size: 28px;
    font-weight: bold;
    cursor: pointer;
}

.close:hover,
.close:focus {
    color: black;
    text-decoration: none;
}

    </style>
    <script>
    function showModal(formId) {
    var modal = document.getElementById('modal');
    var form = document.getElementById(formId);
    
    // Reset all forms and hide them
    var forms = document.querySelectorAll('.modal-content form');
    forms.forEach(function(form) {
        form.classList.remove('active'); // Hide all forms
    });
    
    // Show the selected form
    form.classList.add('active');
    modal.style.display = 'block';
}

function closeModal() {
    var modal = document.getElementById('modal');
    modal.style.display = 'none';
}


function editExpense(id, label, amount, date) {
    var form = document.getElementById('editExpenseForm');
    form.elements['expense_id'].value = id;
    form.elements['expense_label'].value = label;
    form.elements['expense_amount'].value = amount;
    form.elements['expense_date'].value = date;
    showModal('editExpenseForm');
}

function deleteExpense(id) {
    var form = document.getElementById('deleteExpenseForm');
    form.elements['expense_id'].value = id;
    showModal('deleteExpenseForm');
}

</script>
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
    <div class="content">
        <h2><?php echo $month . " " . $year; ?></h2>
        <div class="container">
            <div class="box">
                <h2>Total Income</h2>
                <p id="totalIncome">KSHS.<?php echo number_format($totalIncome, 2); ?></p>
            </div>
            <div class="box">
                <h2>Total Expenses</h2>
                <p id="totalExpenses">KSHS.<?php echo number_format($totalExpenses, 2); ?></p>
            </div>
        </div>
        <div class="container">
            <div class="table-container">
                <h2>Income</h2>
                <button class="add-button" onclick="showModal('incomeForm')">Add Income</button>
                <table id="incomeTable">
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
                            <td colspan="2">Total Income</td>
                            <td>KSHS.<?php echo number_format($totalIncome, 2); ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="table-container">
                <h2>Expenses</h2>
                <button class="add-button" onclick="showModal('expenseForm')">Add Expense</button>
                <table id="expenseTable">
                    <thead>
                        <tr>
                            <th>Label</th>
                            <th>Date</th>
                            <th>Amount</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($expenseData as $expense): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($expense['label']); ?></td>
                            <td><?php echo htmlspecialchars($expense['date']); ?></td>
                            <td><?php echo number_format($expense['amount'], 2); ?></td>
                            <td>
                                <button onclick="editExpense(<?php echo $expense['id']; ?>, '<?php echo htmlspecialchars($expense['label']); ?>', <?php echo $expense['amount']; ?>, '<?php echo htmlspecialchars($expense['date']); ?>')">Edit</button>
                                <button onclick="deleteExpense(<?php echo $expense['id']; ?>)">Delete</button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <tr>
                            <td colspan="2">Total Expenses</td>
                            <td>KSHS.<?php echo number_format($totalExpenses, 2); ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        
        </div>
        <div id="modal" class="modal">
            <div class="modal-content">
                <span class="close" onclick="closeModal()">&times;</span>
<form id="incomeForm" class="active" method="POST">
    <h2>Add Income</h2>
    <label for="income_label">Label:</label>
    <input type="text" id="income_label" name="income_label" required><br>
    <label for="income_date">Date:</label>
    <input type="date" id="income_date" name="income_date" required><br>
    <label for="income_amount">Amount:</label>
    <input type="number" id="income_amount" name="income_amount" step="0.01" required><br>
    <button type="submit" name="income_submit">Save</button>
    <button type="button" class="cancel" onclick="closeModal()">Cancel</button>
</form>

                <form id="expenseForm" class="active" method="POST">
                    <h2>Add Expense</h2>
                    <label for="expense_label">Label:</label>
                    <input type="text" id="expense_label" name="expense_label" required><br>
                    <label for="expense_date">Date:</label>
                    <input type="date" id="expense_date" name="expense_date" required><br>
                    <label for="expense_amount">Amount:</label>
                    <input type="number" id="expense_amount" name="expense_amount" step="0.01" required><br>
                    <button type="submit" name="expense_submit">Save</button>
                    <button type="button" class="cancel" onclick="closeModal()">Cancel</button>
                </form>
                <form id="editExpenseForm" class="active" method="POST">
                <h2>Edit Expense</h2>
                <input type="hidden" name="expense_id" id="expense_id">
                <label for="expense_label">Label:</label>
                <input type="text" id="expense_label" name="expense_label" required><br>
                <label for="expense_date">Date:</label>
                <input type="date" id="expense_date" name="expense_date" required><br>
                <label for="expense_amount">Amount:</label>
                <input type="number" id="expense_amount" name="expense_amount" step="0.01" required><br>
                <button type="submit" name="edit_expense">Save</button>
                <button type="button" class="cancel" onclick="closeModal()">Cancel</button>
            </form>
            <form id="deleteExpenseForm" class="active" method="POST">
                <h2>Delete Expense</h2>
                <input type="hidden" name="expense_id" id="expense_id">
                <p>Are you sure you want to delete this expense?</p>
                <button type="submit" name="delete_expense">Delete</button>
                <button type="button" class="cancel" onclick="closeModal()">Cancel</button>
            </form>
            </div>
        </div>
    </div>
</body>
</html>