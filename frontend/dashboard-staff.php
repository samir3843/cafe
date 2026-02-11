<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'staff') {
    header("Location: login-form.html?error=unauthorized");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Dashboard - Café POS</title>
    <link rel="stylesheet" href="index.css">
    <style>
        .staff-container {
            display: flex;
            height: 100vh;
        }

        .menu-area {
            flex: 2;
            padding: 20px;
            overflow-y: auto;
            background: #f4f4f4;
        }

        .order-sidebar {
            flex: 1;
            background: white;
            padding: 20px;
            display: flex;
            flex-direction: column;
            box-shadow: -2px 0 5px rgba(0, 0, 0, 0.1);
        }

        .category-nav {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            overflow-x: auto;
            padding-bottom: 10px;
        }

        .category-btn {
            padding: 10px 20px;
            border: none;
            background: white;
            border-radius: 20px;
            cursor: pointer;
            white-space: nowrap;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .category-btn.active {
            background: var(--main-color);
            color: white;
        }

        .menu-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            gap: 15px;
        }

        .menu-item {
            background: white;
            padding: 10px;
            border-radius: 10px;
            text-align: center;
            cursor: pointer;
            transition: transform 0.2s;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
        }

        .menu-item:hover {
            transform: translateY(-3px);
        }

        .menu-item img {
            width: 100%;
            height: 100px;
            object-fit: cover;
            border-radius: 8px;
        }

        .order-items {
            flex: 1;
            overflow-y: auto;
            margin: 20px 0;
        }

        .order-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px;
            border-bottom: 1px solid #eee;
        }

        .qty-btn {
            padding: 2px 8px;
            cursor: pointer;
            background: #eee;
            border-radius: 4px;
            margin: 0 5px;
        }

        .tabs {
            display: flex;
            margin-bottom: 20px;
            border-bottom: 1px solid #ddd;
        }

        .tab-btn {
            flex: 1;
            padding: 10px;
            text-align: center;
            cursor: pointer;
            background: #f9f9f9;
        }

        .tab-btn.active {
            background: white;
            border-bottom: 2px solid var(--main-color);
            font-weight: bold;
        }

        .bill-modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            display: none;
            justify-content: center;
            align-items: center;
        }

        .bill-content {
            background: white;
            padding: 30px;
            width: 400px;
            max-height: 80vh;
            overflow-y: auto;
            border-radius: 10px;
        }
    </style>
</head>

<body class="dashboard-body">
    <div class="staff-container">
        <!-- Center Area -->
        <div class="menu-area">
            <nav class="dashboard-nav">
                <h3>Staff Dashboard</h3>
                <div>
                    <span id="welcome-msg">Welcome, Staff</span>
                    <button id="logout-btn" class="btn" style="padding: 5px 15px; margin-left: 10px;">Logout</button>
                </div>
            </nav>

            <div class="tabs">
                <div class="tab-btn active" onclick="switchTab('pos')">New Order</div>
                <div class="tab-btn" onclick="switchTab('orders')">Today's Orders / Bills</div>
            </div>

            <div id="pos-view">
                <div class="category-nav" id="category-nav">
                    <button class="category-btn active" onclick="filterMenu('all')">All</button>
                    <!-- Categories injected here -->
                </div>
                <div class="menu-grid" id="menu-grid">
                    <!-- Menu items injected here -->
                </div>
            </div>

            <div id="orders-view" style="display: none;">
                <h3>Today's Orders</h3>
                <table style="width:100%; text-align:left;">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Customer</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Payment</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="orders-list"></tbody>
                </table>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="order-sidebar" id="sidebar">
            <h3>Current Order</h3>
            <div class="form-group">
                <input type="text" id="customer-name" placeholder="Customer Name" class="form-control"
                    style="width:100%; margin-bottom:10px;">
                <input type="number" id="table-number" placeholder="Table No." class="form-control" style="width:100%;">
            </div>
            <div class="order-items" id="cart-items">
                <p style="text-align:center; color:#888;">Cart is empty</p>
            </div>
            <div style="border-top: 2px solid #ddd; padding-top: 10px;">
                <h4>Total: $<span id="total-price">0.00</span></h4>
                <button class="btn" style="width:100%; margin-top:10px;" onclick="placeOrder()">Place Order</button>
            </div>
        </div>
    </div>

    <!-- Bill Modal -->
    <div class="bill-modal" id="bill-modal">
        <div class="bill-content" id="bill-print-area">
            <h2 style="text-align:center;">User Cafe</h2>
            <p style="text-align:center;">Date: <span id="bill-date"></span></p>
            <p>Order: <span id="bill-id"></span></p>
            <hr>
            <div id="bill-items"></div>
            <hr>
            <h3 style="text-align:right;">Total: $<span id="bill-total"></span></h3>
            <div style="margin-top:20px; text-align:center;" class="no-print">
                <button onclick="window.print()" class="btn">Print</button>
                <button onclick="document.getElementById('bill-modal').style.display='none'" class="btn"
                    style="background:#888;">Close</button>
            </div>
        </div>
    </div>

    <script>
        // Auth Check handled by PHP
        const user = {
            id: <?php echo $_SESSION['user_id']; ?>,
            username: "<?php echo $_SESSION['username']; ?>",
            role: "<?php echo $_SESSION['role']; ?>"
        };

        document.getElementById('welcome-msg').innerText = 'Welcome, ' + user.username;
        document.getElementById('logout-btn').addEventListener('click', () => {
            window.location.href = '../backend/api/auth.php?action=logout';
        });

        let menuData = [];
        let cart = [];

        async function init() {
            // Load Categories
            const resCat = await fetch('../backend/api/menu.php?action=categories');
            const cats = await resCat.json();
            const catNav = document.getElementById('category-nav');
            if (cats.success) {
                cats.data.forEach(c => {
                    catNav.innerHTML += `<button class="category-btn" onclick="filterMenu(${c.id})">${c.name}</button>`;
                });
            }

            // Load Menu
            const resMenu = await fetch('../backend/api/menu.php?action=list');
            const data = await resMenu.json();
            if (data.success) {
                menuData = data.data;
                renderMenu(menuData);
            }
        }

        function renderMenu(items) {
            const grid = document.getElementById('menu-grid');
            grid.innerHTML = items.map(item => `
                <div class="menu-item" onclick="addToCart(${item.id})">
                    <img src="${item.image_url}" alt="${item.name}" onerror="this.src='images/default_food.png'">
                    <h4>${item.name}</h4>
                    <p>$${item.price}</p>
                </div>
            `).join('');
        }

        function filterMenu(catId) {
            document.querySelectorAll('.category-btn').forEach(b => b.classList.remove('active'));
            event.target.classList.add('active');

            if (catId === 'all') renderMenu(menuData);
            else renderMenu(menuData.filter(i => i.category_id == catId));
        }

        function addToCart(id) {
            const item = menuData.find(i => i.id === id);
            const existing = cart.find(i => i.id === id);
            if (existing) existing.qty++;
            else cart.push({ ...item, qty: 1 });
            renderCart();
        }

        function renderCart() {
            const container = document.getElementById('cart-items');
            if (cart.length === 0) {
                container.innerHTML = '<p style="text-align:center; color:#888;">Cart is empty</p>';
                document.getElementById('total-price').innerText = '0.00';
                return;
            }

            let total = 0;
            container.innerHTML = cart.map((item, index) => {
                total += item.price * item.qty;
                return `
                <div class="order-item">
                    <div>
                        <strong>${item.name}</strong><br>
                        $${item.price} x ${item.qty}
                    </div>
                    <div>
                        <span class="qty-btn" onclick="updateQty(${index}, -1)">-</span>
                        <span class="qty-btn" onclick="updateQty(${index}, 1)">+</span>
                    </div>
                </div>`;
            }).join('');
            document.getElementById('total-price').innerText = total.toFixed(2);
        }

        function updateQty(index, change) {
            cart[index].qty += change;
            if (cart[index].qty <= 0) cart.splice(index, 1);
            renderCart();
        }

        async function placeOrder() {
            if (cart.length === 0) return alert('Cart is empty');
            const customerName = document.getElementById('customer-name').value;
            const tableNumber = document.getElementById('table-number').value;
            if (!customerName || !tableNumber) return alert('Enter customer details');

            const orderData = {
                action: 'create',
                user_id: user.id,
                table_number: tableNumber,
                customer_name: customerName,
                items: cart.map(i => ({ menu_id: i.id, quantity: i.qty, price: i.price }))
            };

            const res = await fetch('../backend/api/orders.php?action=create', {
                method: 'POST',
                body: JSON.stringify(orderData)
            });
            const data = await res.json();
            if (data.success) {
                alert('Order Placed! ID: ' + data.order_code);
                cart = [];
                renderCart();
                document.getElementById('customer-name').value = '';
                document.getElementById('table-number').value = '';
                loadOrders(); // Valid if on orders tab, but harmless
            } else {
                alert('Error: ' + data.message);
            }
        }

        function switchTab(tab) {
            document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
            event.target.classList.add('active');
            if (tab === 'pos') {
                document.getElementById('pos-view').style.display = 'block';
                document.getElementById('orders-view').style.display = 'none';
                document.getElementById('sidebar').style.display = 'flex';
            } else {
                document.getElementById('pos-view').style.display = 'none';
                document.getElementById('orders-view').style.display = 'block';
                document.getElementById('sidebar').style.display = 'none';
                loadOrders();
            }
        }

        async function loadOrders() {
            const res = await fetch('../backend/api/orders.php?action=list_today');
            const data = await res.json();
            if (data.success) {
                const tbody = document.getElementById('orders-list');
                tbody.innerHTML = data.data.map(o => `
                    <tr>
                        <td>${o.order_code}</td>
                        <td>${o.customer_name} (T${o.table_number})</td>
                        <td>$${o.total_amount}</td>
                        <td>${o.kitchen_status}</td>
                        <td>
                             <button class="btn-small" onclick="togglePayment(${o.id}, '${o.payment_status === 'paid' ? 'unpaid' : 'paid'}')">
                                ${o.payment_status}
                             </button>
                        </td>
                        <td><button onclick="viewBill(${o.id})">Bill</button></td>
                    </tr>
                `).join('');
            }
        }

        async function togglePayment(id, status) {
            await fetch('../backend/api/orders.php?action=update_status', {
                method: 'POST',
                body: JSON.stringify({ order_id: id, status_type: 'payment', status_value: status })
            });
            loadOrders();
        }

        async function viewBill(id) {
            const res = await fetch('../backend/api/orders.php?action=details&id=' + id);
            const data = await res.json();
            if (data.success) {
                const o = data.data.order;
                document.getElementById('bill-date').innerText = o.created_at;
                document.getElementById('bill-id').innerText = o.order_code;
                document.getElementById('bill-total').innerText = o.total_amount;
                document.getElementById('bill-items').innerHTML = data.data.items.map(i => `
                    <div style="display:flex; justify-content:space-between;">
                        <span>${i.menu_name} x ${i.quantity}</span>
                        <span>$${i.price * i.quantity}</span>
                    </div>
                `).join('');
                document.getElementById('bill-modal').style.display = 'flex';
            }
        }

        init();
    </script>
</body>

</html>