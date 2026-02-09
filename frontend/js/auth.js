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
            const username = document.querySelector('#login-email').value;
            const password = document.querySelector('#login-password').value;

            if (!username || !password) {
                alert("Please fill in all fields.");
                return;
            }

            fetch('../backend/api/login.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ username: username, password: password })
            })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Server returned ' + response.status);
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.status === 'success') {
                        window.location.href = 'dashboard.html';
                    } else {
                        alert('Login failed: ' + (data.message || 'Unknown error'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert("Login Error: " + error.message + "\n\nMake sure XAMPP is running and you are accessing via http://localhost/");
                });
        });
    }

    if (signupForm) {
        signupForm.addEventListener('submit', function (e) {
            e.preventDefault();
            const fullname = document.querySelector('#signup-name').value;
            // const username = document.querySelector('#signup-username').value; // User removed this field
            const email = document.querySelector('#signup-email').value;
            const password = document.querySelector('#signup-password').value;
            const confirmPassword = document.querySelector('#signup-confirm').value;

            if (password !== confirmPassword) {
                alert("Passwords do not match!");
                return;
            }

            if (!fullname || !email || !password) {
                alert("Please fill in all fields.");
                return;
            }

            fetch('../backend/api/signup.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    username: email, // Use email as username
                    password: password,
                    fullname: fullname,
                    email: email
                })
            })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Server returned ' + response.status);
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.status === 'success') {
                        alert('Signup successful! Please login.');
                        // Switch to login tab or reload logic could go here, for now redirect or reload
                        window.location.reload();
                    } else {
                        alert('Signup failed: ' + (data.message || 'Unknown error'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert("Signup Error: " + error.message + "\n\nMake sure XAMPP is running and you are accessing via http://localhost/");
                });
        });
    }
});
