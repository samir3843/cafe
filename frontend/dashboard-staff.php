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
            background: #fdfdfd;
        }

        .menu-area {
            flex: 2;
            padding: 20px;
            overflow-y: auto;
            background: #fafafa;
        }

        .order-sidebar {
            flex: 1;
            background: white;
            padding: 20px;
            display: flex;
            flex-direction: column;
            box-shadow: -2px 0 10px rgba(0, 0, 0, 0.1);
            border-left: 2px solid var(--secondary-color);
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
            border: 2px solid var(--main-color);
            background: white;
            color: var(--main-color);
            border-radius: 20px;
            cursor: pointer;
            white-space: nowrap;
            font-weight: bold;
            font-family: var(--font-family);
            transition: 0.3s;
        }

        .category-btn:hover,
        .category-btn.active {
            background: var(--main-color);
            color: white;
        }

        .menu-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
            gap: 20px;
        }
        
        /* Updated Menu Item Card Style matching product card logic */
        .menu-item {
            background: white;
            padding: 15px;
            border-radius: var(--border-radius);
            text-align: center;
            cursor: pointer;
            transition: transform 0.3s, box-shadow 0.3s;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            position: relative;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: space-between;
            min-height: 250px;
        }

        .menu-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.15);
        }

        .menu-item img {
            width: 120px;
            height: 120px;
            object-fit: contain;
            /* Allow image to overlap top like in index.html designs if desired, or contain properly */
            margin-bottom: 10px;
            transition: 0.3s;
        }
        
        .menu-item:hover img {
            transform: scale(1.1) rotate(5deg);
        }

        .menu-item h4 {
            color: var(--main-color);
            margin: 10px 0;
            font-size: 1.1rem;
        }
        
        .menu-item p {
            color: var(--accent-color);
            font-weight: bold;
        }

        .order-items {
            flex: 1;
            overflow-y: auto;
            margin: 20px 0;
            padding-right: 5px;
        }

        .order-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px;
            border-bottom: 1px solid #eee;
            background: #fff;
            margin-bottom: 5px;
            border-radius: 5px;
        }

        .qty-btn {
            padding: 4px 10px;
            cursor: pointer;
            background: var(--secondary-color);
            color: white;
            border-radius: 5px;
            margin: 0 5px;
            font-weight: bold;
        }
        
        .qty-btn:hover {
            background: var(--main-color);
        }

        .tabs {
            display: flex;
            margin-bottom: 20px;
            background: #fff;
            border-radius: var(--border-radius);
            overflow: hidden;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        .tab-btn {
            flex: 1;
            padding: 15px;
            text-align: center;
            cursor: pointer;
            /* background: #f9f9f9; */
            background: white;
            font-weight: bold;
            color: var(--main-color);
            transition: 0.3s;
        }

        .tab-btn.active {
            background: var(--main-color);
            color: white;
        }

        /* Order Card Grid for "Today's Orders" */
        .order-card-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 20px;
        }

        .order-card-item {
            background: white;
            border-radius: var(--border-radius);
            padding: 20px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            position: relative;
            border-left: 5px solid var(--secondary-color);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            gap: 10px;
        }
        
        .order-card-item h4 {
            color: var(--main-color);
            margin-bottom: 5px;
        }
        
        .order-card-item .meta {
            font-size: 0.9rem;
            color: #666;
            margin-bottom: 10px;
        }
        
        .status-badge {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 0.8rem;
            font-weight: bold;
            text-transform: uppercase;
        }
        
        .status-paid { background: #d4edda; color: #155724; }
        .status-unpaid { background: #f8d7da; color: #721c24; }
        
        .kitchen-badge {
            background: #e2e3e5; 
            color: #383d41;
            padding: 2px 8px; 
            border-radius: 4px;
            font-size: 0.8rem;
        }

        .btn {
            background-color: var(--main-color);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: var(--border-radius);
            cursor: pointer;
            font-weight: bold;
            transition: 0.3s;
        }
        
        .btn:hover {
            background-color: var(--accent-color);
        }
        
        /* Modal Styles */
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
            z-index: 1000;
        }

        .bill-content {
            background: white;
            padding: 30px;
            width: 400px;
            max-height: 80vh;
            overflow-y: auto;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
        }
        
        /* Scrollbar styling */
        ::-webkit-scrollbar { width: 8px; }
        ::-webkit-scrollbar-track { background: #f1f1f1; }
        ::-webkit-scrollbar-thumb { background: var(--secondary-color); border-radius: 4px; }
        ::-webkit-scrollbar-thumb:hover { background: var(--main-color); }
    </style>
</head>

<body class="dashboard-body">
    <div class="staff-container">
        <!-- Center Area -->
        <div class="menu-area">
            <nav class="dashboard-nav" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h2 style="color: var(--main-color);">Staff Dashboard</h2>
                <div>
                    <span id="welcome-msg" style="font-weight: bold; color: var(--secondary-color);">Welcome, Staff</span>
                    <button id="logout-btn" class="btn" style="padding: 5px 15px; margin-left: 10px; background: var(--accent-color);">Logout</button>
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
                <h3 style="color: var(--main-color); margin-bottom: 15px;">Today's Orders</h3>
                <div id="orders-list" class="order-card-grid">
                    <!-- Orders injected here -->
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="order-sidebar" id="sidebar">
            <h3 style="color: var(--main-color); text-align: center; margin-bottom: 20px;">Current Order</h3>
            <div class="form-group">
                <input type="text" id="customer-name" placeholder="Customer Name" class="form-control"
                    style="width:100%; margin-bottom:10px; padding: 10px; border: 1px solid #ccc; border-radius: 5px;">
                <input type="number" id="table-number" placeholder="Table No." class="form-control" style="width:100%; padding: 10px; border: 1px solid #ccc; border-radius: 5px;">
            </div>
            
            <div class="order-items" id="cart-items">
                <div style="text-align:center; color:#888; margin-top: 50px;">
                    <img src="images/coffeeeee.png" style="width: 50px; opacity: 0.5; margin-bottom: 10px;"><br>
                    Cart is empty
                </div>
            </div>
            
            <div style="border-top: 2px dashed var(--secondary-color); padding-top: 20px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                    <h4 style="color: #666;">Total:</h4>
                    <h2 style="color: var(--main-color);">$<span id="total-price">0.00</span></h2>
                </div>
                <button class="btn" style="width:100%; padding: 15px;" onclick="placeOrder()">Place Order</button>
            </div>
        </div>
    </div>

    <!-- Bill Modal -->
    <div class="bill-modal" id="bill-modal">
        <div class="bill-content" id="bill-print-area">
            <div style="text-align: center; margin-bottom: 20px;">
                <img src="images/coffe_logo-removebg-preview.png" style="width: 60px;">
                <h2 style="color: var(--main-color); margin: 5px 0;">Café Invoice</h2>
                <p style="color: #666; font-size: 0.9rem;">Thank you for dining with us!</p>
            </div>
            
            <p style="display: flex; justify-content: space-between;"><strong>Date:</strong> <span id="bill-date"></span></p>
            <p style="display: flex; justify-content: space-between;"><strong>Order ID:</strong> <span id="bill-id"></span></p>
            <p style="display: flex; justify-content: space-between;"><strong>Table:</strong> <span id="bill-table"></span></p>
            <hr style="border-top: 1px dashed #ccc; margin: 15px 0;">
            
            <div id="bill-items" style="margin-bottom: 15px;"></div>
            
            <hr style="border-top: 1px dashed #ccc; margin: 15px 0;">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <h3>Total</h3>
                <h3 style="color: var(--accent-color);">$<span id="bill-total"></span></h3>
            </div>
            
            <div style="margin-top:20px; text-align:center; gap: 10px; display: flex; justify-content: center;" class="no-print">
                <button onclick="window.print()" class="btn">Print Bill</button>
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

        document.getElementById('welcome-msg').innerText = 'Hi, ' + user.username;
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
                    <button class="btn" style="margin-top: 5px; padding: 5px 15px; font-size: 0.8rem;">Add</button>
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
                container.innerHTML = '<div style="text-align:center; color:#888; margin-top: 50px;"><img src="images/coffeeeee.png" style="width: 50px; opacity: 0.5; margin-bottom: 10px;"><br>Cart is empty</div>';
                document.getElementById('total-price').innerText = '0.00';
                return;
            }

            let total = 0;
            container.innerHTML = cart.map((item, index) => {
                total += item.price * item.qty;
                return `
                <div class="order-item">
                    <div style="display:flex; align-items:center; gap: 10px;">
                        <img src="${item.image_url}" style="width: 40px; height: 40px; object-fit: cover; border-radius: 5px;" onerror="this.src='images/default_food.png'">
                        <div>
                            <strong>${item.name}</strong><br>
                            <span style="font-size: 0.8rem; color: #666;">$${item.price}</span>
                        </div>
                    </div>
                    <div style="display: flex; align-items: center;">
                        <span class="qty-btn" onclick="updateQty(${index}, -1)">-</span>
                        <span style="font-weight: bold;">${item.qty}</span>
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
                document.querySelector('.menu-area').style.flex = '2'; 
            } else {
                document.getElementById('pos-view').style.display = 'none';
                document.getElementById('orders-view').style.display = 'block';
                document.getElementById('sidebar').style.display = 'none';
                document.querySelector('.menu-area').style.flex = '1'; // Full width when no sidebar
                loadOrders();
            }
        }

        async function loadOrders() {
            const res = await fetch('../backend/api/orders.php?action=list_today');
            const data = await res.json();
            if (data.success) {
                const list = document.getElementById('orders-list');
                list.innerHTML = data.data.map(o => `
                    <div class="order-card-item">
                        <div>
                            <h4>Order #${o.order_code}</h4>
                            <div class="meta">
                                <strong>${o.customer_name}</strong> (Table ${o.table_number})<br>
                                <span style="font-size: 0.8rem;">${o.created_at}</span>
                            </div>
                            <div style="display: flex; gap: 10px; margin-top: 10px;">
                                <span class="status-badge ${o.payment_status === 'paid' ? 'status-paid' : 'status-unpaid'}">
                                    ${o.payment_status}
                                </span>
                                <span class="kitchen-badge">Kitchen: ${o.kitchen_status}</span>
                            </div>
                        </div>
                        
                        <div style="border-top: 1px dashed #eee; padding-top: 10px; display: flex; flex-direction: column; gap: 10px;">
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <span>Total:</span>
                                <strong style="color: var(--main-color); font-size: 1.2rem;">$${o.total_amount}</strong>
                            </div>
                            
                            <div style="display: flex; gap: 10px;">
                                <button class="btn" style="flex: 1; padding: 8px; font-size: 0.9rem; background: ${o.payment_status === 'paid' ? '#f8d7da' : '#d4edda'}; color: ${o.payment_status === 'paid' ? '#721c24' : '#155724'}" 
                                    onclick="togglePayment(${o.id}, '${o.payment_status === 'paid' ? 'unpaid' : 'paid'}')">
                                    ${o.payment_status === 'paid' ? 'Mark Unpaid' : 'Mark Paid'}
                                </button>
                                <button class="btn" style="flex: 1; padding: 8px; font-size: 0.9rem; background: var(--secondary-color);" onclick="viewBill(${o.id})">Bill</button>
                            </div>
                        </div>
                    </div>
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
                document.getElementById('bill-table').innerText = o.table_number || 'N/A';
                document.getElementById('bill-total').innerText = o.total_amount;
                document.getElementById('bill-items').innerHTML = data.data.items.map(i => `
                    <div style="display:flex; justify-content:space-between; margin-bottom: 5px; font-size: 0.9rem;">
                        <span>${i.menu_name} <small style="color:#888;">x${i.quantity}</small></span>
                        <span>$${(i.price * i.quantity).toFixed(2)}</span>
                    </div>
                `).join('');
                document.getElementById('bill-modal').style.display = 'flex';
            }
        }

        init();
    </script>
</body>

</html>