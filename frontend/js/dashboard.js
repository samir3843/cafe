// Check Authentication on Page Load
document.addEventListener('DOMContentLoaded', () => {
    fetch('../backend/api/check_auth.php')
        .then(response => {
            if (response.status === 401) {
                window.location.href = 'login-form.html';
            }
            return response.json();
        })
        .then(data => {
            if (data.status === 'authenticated') {
                document.getElementById('welcome-msg').textContent = 'Welcome, ' + data.user.username;
                window.currentUser = data.user;
            }
        })
        .catch(err => {
            console.error(err);
            window.location.href = 'login-form.html';
        });

    document.getElementById('logout-btn').addEventListener('click', () => {
        fetch('../backend/api/logout.php', { method: 'POST' })
            .then(() => window.location.href = 'index.html');
    });
});

// Order Logic
let currentOrder = [];

function addToOrder(productName, price) {
    // Check if item exists
    const existingItem = currentOrder.find(item => item.name === productName);
    if (existingItem) {
        existingItem.quantity++;
    } else {
        currentOrder.push({ name: productName, price: price, quantity: 1 });
    }
    renderOrder();
}

function removeFromOrder(productName) {
    currentOrder = currentOrder.filter(item => item.name !== productName);
    renderOrder();
}

function updateQuantity(productName, change) {
    const item = currentOrder.find(i => i.name === productName);
    if (item) {
        item.quantity += change;
        if (item.quantity <= 0) {
            removeFromOrder(productName);
        } else {
            renderOrder();
        }
    }
}

function renderOrder() {
    const list = document.getElementById('order-list');
    const emptyMsg = document.getElementById('empty-msg');
    const totalEl = document.getElementById('total-price');

    list.innerHTML = '';
    let total = 0;

    if (currentOrder.length === 0) {
        emptyMsg.style.display = 'block';
    } else {
        emptyMsg.style.display = 'none';
        currentOrder.forEach(item => {
            total += item.price * item.quantity;

            const div = document.createElement('div');
            div.className = 'order-item';
            div.innerHTML = `
                <div>${item.name} ($${item.price}) x ${item.quantity}</div>
                <div>
                    <button onclick="updateQuantity('${item.name}', -1)">-</button>
                    <button onclick="updateQuantity('${item.name}', 1)">+</button>
                    <span class="remove-btn" onclick="removeFromOrder('${item.name}')">&times;</span>
                </div>
            `;
            list.appendChild(div);
        });
    }

    totalEl.textContent = total;
}

function submitOrder() {
    const customerName = document.getElementById('customer-name').value;
    const tableNumber = document.getElementById('table-number').value;

    if (!customerName || !tableNumber || currentOrder.length === 0) {
        alert('Please fill in customer details and add items to order.');
        return;
    }

    const orderDetails = currentOrder.map(item => `${item.name} x${item.quantity}`).join(', ');

    // Total could be stored too, but schema currently only has text details.
    // We'll append total to details for now.
    const total = document.getElementById('total-price').textContent;
    const fullDetails = `${orderDetails} | Total: $${total}`;

    fetch('../backend/api/create_order.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            customer_name: customerName,
            table_number: tableNumber,
            order_details: fullDetails,
            user_id: window.currentUser.id
        })
    })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                alert('Order placed successfully!');
                currentOrder = [];
                document.getElementById('customer-name').value = '';
                document.getElementById('table-number').value = '';
                renderOrder();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(err => console.error(err));
}
