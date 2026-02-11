<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login-form.html?error=unauthorized");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Café POS</title>
    <link rel="stylesheet" href="index.css">
    <style>
        .admin-container {
            padding: 20px;
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
        }

        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        .stat-card h3 {
            font-size: 2em;
            color: var(--main-color);
            margin: 10px 0;
        }

        .section-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th,
        td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        .btn-small {
            padding: 5px 10px;
            font-size: 0.8em;
        }

        input,
        select {
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            margin-right: 10px;
        }
    </style>
</head>

<body class="dashboard-body">
    <div class="sidebar">
        <h3>Admin Panel</h3>
        <ul>
            <li><a href="#" onclick="showSection('overview')">Overview</a></li>
            <li><a href="#" onclick="showSection('menu')">Manage Menu</a></li>
            <li><a href="#" onclick="showSection('users')">Manage Users</a></li>
            <li><a href="#" id="logout-btn">Logout</a></li>
        </ul>
    </div>
    <div class="main-content">
        <nav class="dashboard-nav">
            <h2>Admin Dashboard</h2>
            <span id="welcome-msg">Welcome, Admin</span>
        </nav>

        <div id="overview-section">
            <div class="stats-grid">
                <div class="stat-card">
                    <p>Today's Revenue</p>
                    <h3 id="today-revenue">$0.00</h3>
                </div>
                <div class="stat-card">
                    <p>Orders Today</p>
                    <h3 id="today-orders">0</h3>
                </div>
            </div>
            <div class="section-card" style="margin-top: 20px;">
                <h3>Staff Performance (Today)</h3>
                <table id="staff-perf-table">
                    <thead>
                        <tr>
                            <th>Staff Name</th>
                            <th>Orders Processed</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>

        <div id="menu-section" style="display:none;">
            <div class="section-card">
                <h3>Manage Menu</h3>
                <div style="margin-bottom: 10px;">
                    <input type="text" id="new-item-name" placeholder="Item Name">
                    <input type="number" id="new-item-price" placeholder="Price">
                    <select id="new-item-category">
                        <option>Loading...</option>
                    </select>
                    <button onclick="addMenuItem()" class="btn btn-small">Add Item</button>
                </div>
                <table id="menu-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Category</th>
                            <th>Price</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>

        <div id="users-section" style="display:none;">
            <div class="section-card">
                <h3>Manage Users</h3>
                <table id="users-table">
                    <thead>
                        <tr>
                            <th>Username</th>
                            <th>Role</th>
                            <th>Created At</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
    <script>
        // Auth Check handled by PHP at the top
        const user = {
            username: "<?php echo $_SESSION['username']; ?>",
            role: "<?php echo $_SESSION['role']; ?>"
        };
        
        document.getElementById('welcome-msg').innerText = 'Welcome, ' + user.username;
        document.getElementById('logout-btn').addEventListener('click', () => {
            window.location.href = '../backend/api/auth.php?action=logout';
        });

        // Navigation
        function showSection(id) {
            document.getElementById('overview-section').style.display = 'none';
            document.getElementById('menu-section').style.display = 'none';
            document.getElementById('users-section').style.display = 'none';
            document.getElementById(id + '-section').style.display = 'block';
        }

        // Fetch Data
        async function loadStats() {
            const res = await fetch('../backend/api/stats.php?action=dashboard_stats');
            const data = await res.json();
            if (data.success) {
                document.getElementById('today-revenue').innerText = '$' + data.data.today_revenue;
                document.getElementById('today-orders').innerText = data.data.today_orders;
                const tbody = document.querySelector('#staff-perf-table tbody');
                tbody.innerHTML = data.data.staff_performance.map(s => `<tr><td>${s.username}</td><td>${s.order_count}</td></tr>`).join('');
            }
        }

        async function loadMenu() {
            const resCat = await fetch('../backend/api/menu.php?action=categories');
            const cats = await resCat.json();
            const catSelect = document.getElementById('new-item-category');
            catSelect.innerHTML = cats.data.map(c => `<option value="${c.id}">${c.name}</option>`).join('');

            const res = await fetch('../backend/api/menu.php?action=list');
            const data = await res.json();
            const tbody = document.querySelector('#menu-table tbody');
            tbody.innerHTML = data.data.map(m => `
                <tr>
                    <td>${m.name}</td>
                    <td>${m.category_name}</td>
                    <td>$${m.price}</td>
                    <td><button class="btn-small" style="background:red; color:white;" onclick="deleteItem(${m.id})">Delete</button></td>
                </tr>
            `).join('');
        }

        async function addMenuItem() {
            const name = document.getElementById('new-item-name').value;
            const price = document.getElementById('new-item-price').value;
            const category_id = document.getElementById('new-item-category').value;

            if (!name || !price) return alert('Fill all fields');

            await fetch('../backend/api/menu.php?action=add', {
                method: 'POST',
                body: JSON.stringify({ name, price, category_id })
            });
            loadMenu();
            document.getElementById('new-item-name').value = '';
            document.getElementById('new-item-price').value = '';
        }

        async function deleteItem(id) {
            if (!confirm('Are you sure?')) return;
            await fetch('../backend/api/menu.php?action=delete', {
                method: 'POST',
                body: JSON.stringify({ id })
            });
            loadMenu();
        }

        async function loadUsers() {
            const res = await fetch('../backend/api/stats.php?action=users');
            const data = await res.json();
            const tbody = document.querySelector('#users-table tbody');
            tbody.innerHTML = data.data.map(u => `<tr><td>${u.username}</td><td>${u.role}</td><td>${u.created_at}</td></tr>`).join('');
        }

        // Initial Load
        loadStats();
        loadMenu();
        loadUsers();
    </script>
</body>

</html>