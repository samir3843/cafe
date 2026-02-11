<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'kitchen') {
    header("Location: login-form.html?error=unauthorized");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kitchen Dashboard - Café POS</title>
    <link rel="stylesheet" href="index.css">
    <style>
        .kitchen-container {
            padding: 20px;
            background: #fdfdfd;
            min-height: 100vh;
            color: #333;
        }

        .dashboard-nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            background: white;
            padding: 15px 30px;
            border-radius: var(--border-radius);
            box-shadow: 0 4px 10px rgba(0,0,0,0.05);
        }

        .order-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 25px;
        }

        .kitchen-card {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: 0 4px 15px rgba(0,0,0,0.08); /* Softer shadow */
            padding: 20px;
            position: relative;
            border-top: 5px solid var(--secondary-color);
            transition: transform 0.3s;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .kitchen-card:hover {
            transform: translateY(-5px);
        }

        .kitchen-card.preparing {
            border-top: 5px solid var(--accent-color); /* Highlight preparing orders */
            background: #fff5f5; /* Light reddish tint */
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            border-bottom: 2px dashed #eee;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }

        .card-header h3 {
            margin: 0;
            color: var(--main-color);
        }

        .card-items {
            flex: 1;
            margin-bottom: 20px;
        }

        .kitchen-item {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 8px 0;
            border-bottom: 1px solid #f0f0f0;
        }
        
        .kitchen-item:last-child {
            border-bottom: none;
        }

        .kitchen-item img {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .item-details {
            flex: 1;
        }
        
        .item-qty {
            background: var(--main-color);
            color: white;
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 0.8rem;
            font-weight: bold;
        }

        .status-btn {
            width: 100%;
            padding: 12px;
            border: none;
            border-radius: var(--border-radius);
            font-weight: bold;
            cursor: pointer;
            transition: 0.3s;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .btn-prepare {
            background: var(--secondary-color);
            color: white;
        }
        
        .btn-prepare:hover {
            background: var(--main-color);
        }

        .btn-finish {
            background: var(--accent-color);
            color: white;
            animation: pulse 2s infinite; 
        }
        
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.02); }
            100% { transform: scale(1); }
        }

        .meta-info {
            font-size: 0.85rem;
            color: #777;
            margin-top: 5px;
        }

        /* Empty state styling */
        .empty-state {
            grid-column: 1 / -1;
            text-align: center;
            padding: 50px;
            color: #999;
        }
        .empty-state img {
            width: 150px;
            opacity: 0.3;
            margin-bottom: 20px;
        }
    </style>
</head>

<body class="dashboard-body">
    <div class="kitchen-container">
        <nav class="dashboard-nav">
            <h2 style="color: var(--main-color);">Kitchen Display System (KDS)</h2>
            <div style="display: flex; align-items: center; gap: 15px;">
                <span id="welcome-msg" style="font-weight: bold; color: var(--secondary-color);">Chef</span>
                <button id="logout-btn" class="status-btn" style="width: auto; padding: 8px 20px; background: #666; font-size: 0.9rem;">Logout</button>
            </div>
        </nav>

        <div id="order-grid" class="order-grid">
            <!-- Orders injected here -->
            <div class="empty-state">
                <img src="images/coffe_logo-removebg-preview.png" alt="No Orders">
                <h2>No Active Orders</h2>
                <p>Relax! The kitchen is quiet.</p>
            </div>
        </div>
    </div>

    <script>
        // Auth Check handled by PHP
        const user = {
            username: "<?php echo $_SESSION['username']; ?>",
            role: "<?php echo $_SESSION['role']; ?>"
        };

        document.getElementById('welcome-msg').innerText = 'Chef: ' + user.username;
        document.getElementById('logout-btn').addEventListener('click', () => {
             window.location.href = '../backend/api/auth.php?action=logout';
        });

        async function loadOrders() {
            const res = await fetch('../backend/api/orders.php?action=list_active');
            const data = await res.json();
            const grid = document.getElementById('order-grid');
            
            if (data.success && data.data.length > 0) {
                grid.innerHTML = data.data.map(order => {
                    // Parse items details string: name::qty::img||name::qty::img
                    // Note: items_details might be null if manually tested incorrectly, handle that
                    const rawItems = order.items_details || '';
                    const items = rawItems.split('||').map(itemStr => {
                        const parts = itemStr.split('::');
                        return {
                            name: parts[0] || 'Unknown',
                            qty: parts[1] || '1',
                            img: parts[2] || 'images/default_food.png'
                        };
                    });

                    const itemsHtml = items.map(i => `
                        <div class="kitchen-item">
                            <img src="${i.img}" onerror="this.src='images/default_food.png'">
                            <div class="item-details">
                                <div style="display: flex; justify-content: space-between; align-items: center;">
                                    <strong>${i.name}</strong>
                                    <span class="item-qty">x${i.qty}</span>
                                </div>
                            </div>
                        </div>
                    `).join('');

                    return `
                    <div class="kitchen-card ${order.kitchen_status}">
                        <div class="card-header">
                            <div>
                                <h3>#${order.order_code}</h3>
                                <div class="meta-info">Table ${order.table_number} • ${order.customer_name}</div>
                            </div>
                            <div style="text-align: right;">
                                <span style="font-weight: bold; color: var(--accent-color);">${order.created_at.split(' ')[1]}</span><br>
                                <small>${Math.floor((new Date() - new Date(order.created_at)) / 60000)} min ago</small>
                            </div>
                        </div>
                        <div class="card-items">
                            ${itemsHtml}
                        </div>
                        
                        ${order.kitchen_status === 'pending' ? 
                            `<button class="status-btn btn-prepare" onclick="updateStatus(${order.id}, 'preparing')">Start Preparing</button>` : 
                            `<button class="status-btn btn-finish" onclick="updateStatus(${order.id}, 'ready')">Mark Ready</button>`
                        }
                    </div>
                    `;
                }).join('');
            } else {
                 grid.innerHTML = `
                <div class="empty-state">
                    <img src="images/coffe_logo-removebg-preview.png" alt="No Orders">
                    <h2>No Active Orders</h2>
                    <p>Relax! The kitchen is quiet for now.</p>
                </div>`;
            }
        }

        async function updateStatus(id, status) {
            await fetch('../backend/api/orders.php?action=update_status', {
                method: 'POST',
                body: JSON.stringify({ order_id: id, status_type: 'kitchen', status_value: status })
            });
            loadOrders(); // Refresh list immediately
        }

        loadOrders();
        setInterval(loadOrders, 10000); // Poll every 10 seconds
    </script>
</body>

</html>