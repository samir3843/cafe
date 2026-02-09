document.addEventListener('DOMContentLoaded', function() {
    const loginForm = document.querySelector('.login-form');
    const signupForm = document.querySelector('.signup-form');

    if (loginForm) {
        loginForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const email = document.querySelector('#login-email').value; // Using email field for username as per form, but backend expects username. 
            // NOTE: The form asks for Email, but the backend uses username. 
            // For simplicity in this demo, we'll assume the user enters their username in the email field 
            // OR we should update the backend/database to allow login by email.
            // Let's stick to username for backend consistency, so we'll treat the input as username.
            const password = document.querySelector('#login-password').value;

            fetch('../backend/api/login.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ username: email, password: password })
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    window.location.href = 'dashboard.html';
                } else {
                    alert('Login failed: ' + data.message);
                }
            })
            .catch(error => console.error('Error:', error));
        });
    }

    if (signupForm) {
        signupForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const fullname = document.querySelector('#signup-name').value;
            const email = document.querySelector('#signup-email').value;
            const password = document.querySelector('#signup-password').value;
            const confirmPassword = document.querySelector('#signup-confirm').value;

            if (password !== confirmPassword) {
                alert("Passwords do not match!");
                return;
            }

            // Using email as username for simplicity in this specific "Cafe" context if desired,
            // but backend expects 'username'. We will send email as username or ask user for username.
            // The HTML form doesn't have a specific username field, just Name and Email. 
            // We'll use the part of email before @ as username or just use email as username.
            // Let's use Email as the username for the backend to keep it simple.

            fetch('../backend/api/signup.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ 
                    username: email, 
                    password: password,
                    fullname: fullname,
                    email: email
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    alert('Signup successful! Redirecting...');
                    window.location.href = 'dashboard.html';
                } else {
                    alert('Signup failed: ' + data.message);
                }
            })
            .catch(error => console.error('Error:', error));
        });
    }
});
