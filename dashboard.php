<?php
// Include the configuration file for database connection
include 'config.php';

// Start the session to access user info
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Fetch user information
$user_stmt = $pdo->prepare("SELECT username FROM users WHERE id = :user_id");
$user_stmt->execute(['user_id' => $_SESSION['user_id']]);
$user_info = $user_stmt->fetch();

// Fetch all transactions for the user (IN NRS ONLY)
$stmt = $pdo->prepare("SELECT * FROM transactions WHERE user_id = :user_id ORDER BY transaction_date DESC");
$stmt->execute(['user_id' => $_SESSION['user_id']]);
$transactions = $stmt->fetchAll();

// Calculate total income, expenses, and net balance (IN NRS)
$income_stmt = $pdo->prepare("SELECT SUM(amount) FROM transactions WHERE user_id = :user_id AND type = 'income'");
$income_stmt->execute(['user_id' => $_SESSION['user_id']]);
$total_income = $income_stmt->fetchColumn() ?: 0;

$expense_stmt = $pdo->prepare("SELECT SUM(amount) FROM transactions WHERE user_id = :user_id AND type = 'expense'");
$expense_stmt->execute(['user_id' => $_SESSION['user_id']]);
$total_expenses = $expense_stmt->fetchColumn() ?: 0;

$net_balance = $total_income - $total_expenses;

// Handle adding new transaction
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_transaction'])) {
    $amount = $_POST['amount']; // Amount is already in NRS
    $type = $_POST['type'];
    $category = $_POST['category'];
    $description = $_POST['description'];

    $add_stmt = $pdo->prepare("INSERT INTO transactions (user_id, amount, type, category, description, transaction_date) VALUES (:user_id, :amount, :type, :category, :description, NOW())");
    $add_stmt->execute([
        'user_id' => $_SESSION['user_id'],
        'amount' => $amount,
        'type' => $type,
        'category' => $category,
        'description' => $description
    ]);

    header("Location: dashboard.php");
    exit();
}

// Handle deleting transaction
if (isset($_GET['delete'])) {
    $transaction_id = $_GET['delete'];
    $delete_stmt = $pdo->prepare("DELETE FROM transactions WHERE id = :id AND user_id = :user_id");
    $delete_stmt->execute(['id' => $transaction_id, 'user_id' => $_SESSION['user_id']]);

    header("Location: dashboard.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <div class="container">
        <!-- User Info -->
        <div class="user-info">
            <span>Welcome, <?php echo htmlspecialchars($user_info['username']); ?> (ID: <?php echo $_SESSION['user_id']; ?>)</span>
            <a href="logout.php" class="logout-btn">Logout</a>
        </div>

        <h2>Your Dashboard</h2>

        <!-- Financial Overview (Auto Updates) -->
        <div class="financial-overview">
            <p><strong>Total Income: </strong>NRS <span id="total_income"><?php echo number_format($total_income, 2); ?></span></p>
            <p><strong>Total Expenses: </strong>NRS <span id="total_expenses"><?php echo number_format($total_expenses, 2); ?></span></p>
            <p><strong>Net Balance: </strong>
                <span id="net_balance" style="color: <?php echo ($net_balance < 0) ? 'red' : 'green'; ?>">
                    <?php echo ($net_balance < 0 ? '-NRS ' : 'NRS ') . number_format(abs($net_balance), 2); ?>
                </span>
            </p>
        </div>

        <!-- Add Transaction Form -->
        <h3>Add Transaction</h3>
        <form method="POST">
            <label for="amount">Amount (NRS):</label>
            <input type="number" step="0.01" name="amount" required>

            <label for="type">Type:</label>
            <select name="type" required>
                <option value="income">Income</option>
                <option value="expense">Expense</option>
            </select>

            <label for="category">Category:</label>
            <input type="text" name="category" required>

            <label for="description">Description:</label>
            <input type="text" name="description" required>

            <button type="submit" name="add_transaction">Add Transaction</button>
        </form>

        <!-- Transactions Table -->
        <h3>All Transactions</h3>
        <table>
            <thead>
                <tr>
                    <th>Amount (NRS)</th>
                    <th>Type</th>
                    <th>Category</th>
                    <th>Date</th>
                    <th>Description</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($transactions as $transaction): ?>
                    <tr>
                        <td class="clickable-amount" data-type="<?php echo $transaction['type']; ?>">
                            NRS <span><?php echo number_format($transaction['amount'], 2); ?></span>
                        </td>
                        <td><?php echo ucfirst($transaction['type']); ?></td>
                        <td><?php echo htmlspecialchars($transaction['category']); ?></td>
                        <td><?php echo date("m/d/Y", strtotime($transaction['transaction_date'])); ?></td>
                        <td><?php echo htmlspecialchars($transaction['description']); ?></td>
                        <td>
                            <a href="dashboard.php?delete=<?php echo $transaction['id']; ?>" onclick="return confirm('Are you sure you want to delete this transaction?')">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- JavaScript for Clickable Amounts & Auto Update -->
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            document.querySelectorAll(".clickable-amount").forEach(item => {
                item.addEventListener("click", function () {
                    let amountSpan = this.querySelector("span");
                    let currentAmount = parseFloat(amountSpan.textContent.replace("NRS ", "").replace(",", ""));
                    let newAmount = currentAmount + 5;
                    amountSpan.textContent = newAmount.toFixed(2);

                    // Auto update totals
                    let totalIncome = parseFloat(document.getElementById("total_income").textContent.replace(",", ""));
                    let totalExpenses = parseFloat(document.getElementById("total_expenses").textContent.replace(",", ""));

                    if (this.getAttribute("data-type") === "income") {
                        totalIncome += 5;
                    } else {
                        totalExpenses += 5;
                    }

                    let netBalance = totalIncome - totalExpenses;

                    document.getElementById("total_income").textContent = totalIncome.toFixed(2);
                    document.getElementById("total_expenses").textContent = totalExpenses.toFixed(2);

                    let netBalanceElement = document.getElementById("net_balance");
                    netBalanceElement.textContent = (netBalance < 0 ? "-NRS " : "NRS ") + Math.abs(netBalance).toFixed(2);
                    netBalanceElement.style.color = netBalance < 0 ? "red" : "green";
                });
            });
        });
    </script>

</body>
</html>
