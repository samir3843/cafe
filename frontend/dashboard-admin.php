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
        .admin-wrapper {
            display: flex;
            min-height: 100vh;
            background: #fdfdfd;
        }

        .sidebar {
            width: 250px;
            background: var(--main-color);
            color: var(--tertiary-color);
            display: flex;
            flex-direction: column;
            padding: 20px;
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
        }

        .sidebar h3 {
            text-align: center;
            margin-bottom: 40px;
            color: var(--tertiary-color);
            font-size: 1.5rem;
            border-bottom: 2px solid rgba(255,255,255,0.1);
            padding-bottom: 20px;
        }

        .sidebar ul {
            flex-direction: column;
            align-items: stretch;
            gap: 10px;
        }

        .sidebar li a {
            display: block;
            color: rgba(255,255,255,0.8);
            padding: 15px;
            text-decoration: none;
            border-radius: var(--border-radius);
            transition: 0.3s;
            font-weight: normal;
        }

        .sidebar li a:hover, .sidebar li a.active {
            background: var(--accent-color);
            color: white;
            font-weight: bold;
        }

        .main-content {
            flex: 1;
            padding: 30px;
            overflow-y: auto;
        }

        .dashboard-nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            background: white;
            padding: 20px;
            border-radius: var(--border-radius);
            box-shadow: 0 4px 10px rgba(0,0,0,0.05);
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 25px;
        }

        .stat-card {
            background: white;
            padding: 25px;
            border-radius: var(--border-radius);
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
            text-align: center;
            border-bottom: 5px solid var(--secondary-color);
            transition: transform 0.3s;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
        }

        .stat-card h3 {
            font-size: 2.5em;
            color: var(--main-color);
            margin: 10px 0;
        }
        
        .stat-card p {
            color: #666;
            text-transform: uppercase;
            font-size: 0.9rem;
            letter-spacing: 1px;
        }

        .section-card {
            background: white;
            padding: 30px;
            border-radius: var(--border-radius);
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
            margin-top: 30px;
        }
        
        .section-card h3 {
            color: var(--main-color);
            margin-bottom: 20px;
            border-bottom: 2px dashed #eee;
            padding-bottom: 10px;
            display: inline-block;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th {
            background: var(--secondary-color);
            color: white;
            padding: 15px;
            text-align: left;
            border-radius: 5px 5px 0 0;
        }

        td {
            padding: 15px;
            border-bottom: 1px solid #eee;
            color: #333;
        }
        
        tr:last-child td {
            border-bottom: none;
        }

        .btn {
            background-color: var(--main-color);
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: var(--border-radius);
            cursor: pointer;
            font-weight: bold;
            transition: 0.3s;
        }
        
        .btn:hover {
            background-color: var(--accent-color);
        }

        .btn-small {
            padding: 5px 15px;
            font-size: 0.8rem;
            border-radius: 15px;
        }

        .form-row {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
            background: #f9f9f9;
            padding: 20px;
            border-radius: var(--border-radius);
            flex-wrap: wrap;
        }

        input, select {
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 8px;
            flex: 1;
        }
    </style>
</head>

<body class="dashboard-body">
    <div class="admin-wrapper">
        <div class="sidebar">
            <h3>Admin Panel</h3>
            <ul>
                <li><a href="#" onclick="showSection('overview')" id="nav-overview" class="active">Overview</a></li>
                <li><a href="#" onclick="showSection('menu')" id="nav-menu">Manage Menu</a></li>
                <li><a href="#" onclick="showSection('users')" id="nav-users">Manage Users</a></li>
                <li><a href="#" id="logout-btn">Logout</a></li>
            </ul>
        </div>
        
        <div class="main-content">
            <nav class="dashboard-nav">
                <h2 style="color: var(--main-color);">Dashboard</h2>
                <span id="welcome-msg" style="font-weight: bold; color: var(--secondary-color);">Welcome, Admin</span>
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
                    <div class="stat-card">
                        <p>Active Staff</p>
                        <h3 id="active-staff">0</h3>
                    </div>
                </div>
                <div class="section-card">
                    <h3>Staff Performance (Today)</h3>
                    <table id="staff-perf-table">
                        <thead>
                            <tr>
                                <th>Staff Name</th>
                                <th>Orders Processed</th>
                                <th>Revenue Generated</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
    
            <div id="menu-section" style="display:none;">
                <div class="section-card">
                    <h3>Add / Edit Menu Item</h3>
                    <div class="form-row">
                        <input type="text" id="new-item-name" placeholder="Item Name">
                        <input type="number" id="new-item-price" placeholder="Price ($)">
                        <select id="new-item-category">
                            <option>Loading Categories...</option>
                        </select>
                        <input type="file" id="new-item-image" accept="image/*">
                        <button onclick="addMenuItem()" class="btn">Add Item</button>
                    </div>
                </div>
                
                <div class="section-card">
                    <h3>Current Menu</h3>
                    <table id="menu-table">
                        <thead>
                            <tr>
                                <th>Image</th>
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
                    <h3>Create New User</h3>
                    <div class="form-row">
                        <input type="text" id="new-user-name" placeholder="Username" style="text-transform: none;">
                        <input type="password" id="new-user-pass" placeholder="Password">
                        <select id="new-user-role">
                            <option value="staff">Staff</option>
                            <option value="kitchen">Kitchen</option>
                            <option value="admin">Admin</option>
                        </select>
                        <button onclick="addUser()" class="btn">Create User</button>
                    </div>
                </div>

                <div class="section-card">
                    <h3>System Users</h3>
                    <table id="users-table">
                        <thead>
                            <tr>
                                <th>Username</th>
                                <th>Role</th>
                                <th>Created At</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
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
            
            // Update Active Nav
            document.querySelectorAll('.sidebar li a').forEach(a => a.classList.remove('active'));
            document.getElementById('nav-' + id).classList.add('active');
        }

        // Fetch Data
        async function loadStats() {
            const res = await fetch('../backend/api/stats.php?action=dashboard_stats');
            const data = await res.json();
            if (data.success) {
                document.getElementById('today-revenue').innerText = '$' + data.data.today_revenue;
                document.getElementById('today-orders').innerText = data.data.today_orders;
                
                // Assuming backend stats endpoint is updated to return more info or we just use what we have
                // Mocking Active Staff for now or if backend sends it. 
                // Let's assume stats.php returns user count or similar
                // For now, let's just leave it blank or 0 if not provided
                
                const tbody = document.querySelector('#staff-perf-table tbody');
                tbody.innerHTML = data.data.staff_performance.map(s => `
                    <tr>
                        <td><strong>${s.username}</strong></td>
                        <td>${s.order_count}</td>
                        <td>$${s.revenue ? s.revenue : '0.00'}</td>
                    </tr>
                `).join('');
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
                    <td><img src="${m.image_url.startsWith('backend/') ? '../'+m.image_url : m.image_url}" style="width: 50px; height: 50px; object-fit: cover; border-radius: 5px;" onerror="this.src='images/default_food.png'"></td>
                    <td><strong>${m.name}</strong></td>
                    <td>${m.category_name}</td>
                    <td>$${m.price}</td>
                    <td><button class="btn btn-small" style="background:var(--accent-color);" onclick="deleteItem(${m.id})">Delete</button></td>
                </tr>
            `).join('');
        }

        async function addMenuItem() {
            const name = document.getElementById('new-item-name').value;
            const price = document.getElementById('new-item-price').value;
            const category_id = document.getElementById('new-item-category').value;
            const imageInput = document.getElementById('new-item-image');

            if (!name || !price) return alert('Fill all fields');

            const formData = new FormData();
            formData.append('name', name);
            formData.append('price', price);
            formData.append('category_id', category_id);
            if (imageInput.files[0]) {
                formData.append('image', imageInput.files[0]);
            }

            await fetch('../backend/api/menu.php?action=add', {
                method: 'POST',
                body: formData
            });
            loadMenu();
            // Clear inputs
            document.getElementById('new-item-name').value = '';
            document.getElementById('new-item-price').value = '';
            document.getElementById('new-item-image').value = '';
        }

        async function deleteItem(id) {
            if (!confirm('Are you sure you want to delete this item?')) return;
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
            document.getElementById('active-staff').innerText = data.data.length; // Approximate
            tbody.innerHTML = data.data.map(u => `
                <tr>
                    <td>${u.username}</td>
                    <td><span style="background: ${u.role==='admin'?'var(--dropdown-bg)':(u.role==='kitchen'?'orange':'lightblue')}; padding: 2px 8px; border-radius: 4px; color: #333; font-size: 0.8rem;">${u.role}</span></td>
                    <td>${u.created_at}</td>
                    <td><span style="color: green;">Active</span></td>
                     <td><button class="btn btn-small" style="background:var(--accent-color);" onclick="deleteUser(${u.id})">Delete</button></td>
                </tr>
            `).join('');
        }

        async function addUser() {
            const username = document.getElementById('new-user-name').value;
            const password = document.getElementById('new-user-pass').value;
            const role = document.getElementById('new-user-role').value;

            if (!username || !password) return alert('Fill all fields');

            const res = await fetch('../backend/api/users.php?action=create', {
                method: 'POST',
                body: JSON.stringify({ username, password: password, role })
            });
            const data = await res.json();
            if (data.success) {
                alert('User Created');
                document.getElementById('new-user-name').value = '';
                document.getElementById('new-user-pass').value = '';
                loadUsers();
            } else {
                alert('Error: ' + data.message);
            }
        }

        async function deleteUser(id) {
            if (!confirm('Are you sure?')) return;
            const res = await fetch('../backend/api/users.php?action=delete', {
                method: 'POST',
                body: JSON.stringify({ id })
            });
            const data = await res.json();
            if (data.success) {
                loadUsers();
            } else {
                alert(data.message);
            }
        }

        // Initial Load
        loadStats();
        loadMenu();
        loadUsers();
    </script>
</body>

</html>
