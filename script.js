document.addEventListener('DOMContentLoaded', () => {
    loadTransactions();
    document.getElementById('addTransactionForm').addEventListener('submit', handleFormSubmit);
});

async function loadTransactions() {
    const response = await fetch('includes/get-transactions.php');
    const data = await response.json();
    
    updateSummary(data);
    renderTransactions(data.transactions);
}

function updateSummary(data) {
    document.getElementById('total-income').textContent = `$${data.totalIncome}`;
    document.getElementById('total-expense').textContent = `$${data.totalExpense}`;
    document.getElementById('net-balance').textContent = `$${data.netBalance}`;
    document.querySelector('.net').style.borderColor = data.netBalance >= 0 ? '#2ecc71' : '#e74c3c';
}

function renderTransactions(transactions) {
    const container = document.getElementById('transactions-container');
    container.innerHTML = transactions.map(transaction => `
        <div class="transaction-item ${transaction.type}-bg">
            <div>
                <h4>${transaction.category}</h4>
                <small>${transaction.description}</small>
            </div>
            <div>
                <span class="${transaction.type}">$${transaction.amount}</span>
                <br>
                <small>${transaction.date}</small>
            </div>
        </div>
    `).join('');
}

async function handleFormSubmit(e) {
    e.preventDefault();
    const formData = new FormData(e.target);
    
    await fetch('includes/add-transaction.php', {
        method: 'POST',
        body: formData
    });
    
    loadTransactions();
    e.target.reset();
    showForm(false);
}

function showForm(show = true) {
    const form = document.getElementById('transaction-form');
    form.classList.toggle('hidden', !show);
}
