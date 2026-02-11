document.addEventListener('DOMContentLoaded', function () {
    // Check if running via file:// protocol
    if (window.location.protocol === 'file:') {
        alert("CRITICAL ERROR: You are opening this file directly in the browser.\n\nPlease access this site via your local server (XAMPP) at address like:\nhttp://localhost/web-cafee/frontend/login-form.html\n\nLogin and Registration WILL NOT WORK otherwise due to security policies.");
    }

    const loginForm = document.querySelector('.login-form');
    const signupForm = document.querySelector('.signup-form');

    if (loginForm) {
        loginForm.addEventListener('submit', function (e) {
            e.preventDefault();
            const email = document.querySelector('#login-email').value; // Using email field as username
            const password = document.querySelector('#login-password').value;

            if (!email || !password) {
                alert("Please fill in all fields.");
                return;
            }

            fetch('../backend/api/auth.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'login', email: email, password: password })
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        localStorage.setItem('user', JSON.stringify(data.user));
                        window.location.href = data.redirect;
                    } else {
                        alert('Login failed: ' + (data.message || 'Unknown error'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert("Login Error: " + error.message);
                });
        });
    }

    if (signupForm) {
        signupForm.addEventListener('submit', function (e) {
            e.preventDefault();
            const fullname = document.querySelector('#signup-name').value;
            const email = document.querySelector('#signup-email').value;
            const password = document.querySelector('#signup-password').value;
            const confirmPassword = document.querySelector('#signup-confirm').value;

            if (password !== confirmPassword) {
                alert("Passwords do not match!");
                return;
            }

            fetch('../backend/api/auth.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    action: 'register',
                    username: email,
                    password: password,
                    fullname: fullname
                })
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Signup successful! Please login.');
                        window.location.reload();
                    } else {
                        alert('Signup failed: ' + (data.message || 'Unknown error'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert("Signup Error: " + error.message);
                });
        });
    }
});
