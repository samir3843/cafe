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
    <title>Kitchen Display - Café POS</title>
    <link rel="stylesheet" href="index.css">
    <meta http-equiv="refresh" content="30"> <!-- Refresh every 30s as fallback -->
    <style>
        .kitchen-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            padding: 20px;
        }

        .order-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            border-left: 5px solid #ccc;
        }

        .order-card.pending {
            border-left-color: #ff9800;
        }

        .order-card.preparing {
            border-left-color: #2196f3;
        }

        .order-header {
            padding: 15px;
            background: #f9f9f9;
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .order-body {
            padding: 15px;
        }

        .order-footer {
            padding: 15px;
            background: #f9f9f9;
            text-align: right;
        }

        .item-list {
            list-style: none;
            padding: 0;
        }

        .item-list li {
            padding: 5px 0;
            border-bottom: 1px dashed #eee;
            font-size: 1.1em;
        }

        .status-btn {
            padding: 8px 16px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            color: white;
            font-weight: bold;
        }

        .btn-prepare {
            background: #2196f3;
        }

        .btn-finish {
            background: #4caf50;
        }
    </style>
</head>

<body class="dashboard-body" style="display:block;">
    <div style="padding: 20px; background: white; display: flex; justify-content: space-between; align-items: center;">
        <h2>Kitchen Display System</h2>
        <div>
            <span id="welcome-msg" style="margin-right: 20px;"></span>
            <button id="logout-btn" class="btn">Logout</button>
        </div>
    </div>

    <div class="kitchen-grid" id="orders-grid">
        <!-- Orders injected here -->
        <p>Loading orders...</p>
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
            try {
                const res = await fetch('../backend/api/orders.php?action=list_active');
                const data = await res.json();

                if (data.success) {
                    const grid = document.getElementById('orders-grid');
                    if (data.data.length === 0) {
                        grid.innerHTML = '<p style="grid-column: 1/-1; text-align: center; font-size: 1.5em; color: #888; margin-top: 50px;">No pending orders</p>';
                        return;
                    }

                    grid.innerHTML = data.data.map(order => `
                        <div class="order-card ${order.kitchen_status}">
                            <div class="order-header">
                                <strong>#${order.order_code.slice(-4)}</strong>
                                <span>${new Date(order.created_at).toLocaleTimeString()}</span>
                            </div>
                            <div class="order-body">
                                <p><strong>Table: ${order.table_number}</strong></p>
                                <ul class="item-list">
                                    ${order.items_summary.split(', ').map(i => `<li>${i}</li>`).join('')}
                                </ul>
                            </div>
                            <div class="order-footer">
                                ${order.kitchen_status === 'pending' ?
                            `<button class="status-btn btn-prepare" onclick="updateStatus(${order.id}, 'preparing')">Start Preparing</button>` :
                            `<button class="status-btn btn-finish" onclick="updateStatus(${order.id}, 'ready')">Mark Ready</button>`
                        }
                            </div>
                        </div>
                    `).join('');
                }
            } catch (e) {
                console.error(e);
            }
        }

        async function updateStatus(id, status) {
            await fetch('../backend/api/orders.php?action=update_status', {
                method: 'POST',
                body: JSON.stringify({ order_id: id, status_type: 'kitchen', status_value: status })
            });
            loadOrders();
        }

        // Poll every 10 seconds for real-time-ish updates
        setInterval(loadOrders, 10000);
        loadOrders();
    </script>
</body>

</html>