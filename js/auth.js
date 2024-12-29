// Authentication related functions
const auth = {
    async register(formData) {
        try {
            const response = await fetch('/ai-fashion/api/auth/register.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(formData)
            });
            
            const data = await response.json();
            if (data.success) {
                // Show success message and redirect to login
                showMessage('Registration successful! Please login.', 'success');
                showLoginForm();
            } else {
                showMessage(data.message || 'Registration failed', 'error');
            }
            return data;
        } catch (error) {
            showMessage('An error occurred during registration', 'error');
            console.error('Registration error:', error);
        }
    },

    async login(formData) {
        try {
            const response = await fetch('/ai-fashion/api/auth/login.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(formData)
            });
            
            const data = await response.json();
            if (data.success) {
                // Store user data and redirect
                localStorage.setItem('user', JSON.stringify(data.user));
                showMessage('Login successful!', 'success');
                window.location.href = '/ai-fashion/dashboard.php';
            } else {
                showMessage(data.message || 'Login failed', 'error');
            }
            return data;
        } catch (error) {
            showMessage('An error occurred during login', 'error');
            console.error('Login error:', error);
        }
    },

    async logout() {
        try {
            const response = await fetch('/ai-fashion/api/auth/logout.php', {
                method: 'POST'
            });
            
            const data = await response.json();
            if (data.success) {
                // Clear user data and redirect
                localStorage.removeItem('user');
                window.location.href = '/ai-fashion/index.php';
            }
            return data;
        } catch (error) {
            console.error('Logout error:', error);
        }
    },

    isLoggedIn() {
        return !!localStorage.getItem('user');
    },

    getCurrentUser() {
        const userData = localStorage.getItem('user');
        return userData ? JSON.parse(userData) : null;
    }
};

// UI Helper Functions
function showMessage(message, type = 'info') {
    const messageDiv = document.createElement('div');
    messageDiv.className = `message ${type}`;
    messageDiv.textContent = message;
    
    document.body.appendChild(messageDiv);
    
    setTimeout(() => {
        messageDiv.remove();
    }, 3000);
}

function showLoginForm() {
    const authForms = document.querySelectorAll('.auth-form');
    authForms.forEach(form => form.style.display = 'none');
    document.querySelector('.login-form').style.display = 'block';
}

function showRegisterForm() {
    const authForms = document.querySelectorAll('.auth-form');
    authForms.forEach(form => form.style.display = 'none');
    document.querySelector('.register-form').style.display = 'block';
}

// Event Listeners
document.addEventListener('DOMContentLoaded', () => {
    // Login Form Handler
    const loginForm = document.querySelector('.login-form');
    if (loginForm) {
        loginForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = {
                username: loginForm.querySelector('[name="username"]').value,
                password: loginForm.querySelector('[name="password"]').value
            };
            await auth.login(formData);
        });
    }

    // Register Form Handler
    const registerForm = document.querySelector('.register-form');
    if (registerForm) {
        registerForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = {
                username: registerForm.querySelector('[name="username"]').value,
                email: registerForm.querySelector('[name="email"]').value,
                password: registerForm.querySelector('[name="password"]').value,
                first_name: registerForm.querySelector('[name="first_name"]').value,
                last_name: registerForm.querySelector('[name="last_name"]').value
            };
            await auth.register(formData);
        });
    }

    // Logout Button Handler
    const logoutBtn = document.querySelector('.logout-btn');
    if (logoutBtn) {
        logoutBtn.addEventListener('click', (e) => {
            e.preventDefault();
            auth.logout();
        });
    }

    // Form Toggle Handlers
    const loginLink = document.querySelector('.login-link');
    const registerLink = document.querySelector('.register-link');
    
    if (loginLink) {
        loginLink.addEventListener('click', (e) => {
            e.preventDefault();
            showLoginForm();
        });
    }
    
    if (registerLink) {
        registerLink.addEventListener('click', (e) => {
            e.preventDefault();
            showRegisterForm();
        });
    }
});
