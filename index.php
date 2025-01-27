<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/db.php';

// If already logged in, redirect to dashboard
if (isset($_SESSION['user_id'])) {
    header('Location: pages/dashboard.php');
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $error = 'Please enter both username and password';
    } else {
        try {
            $conn = getDBConnection();
            $stmt = $conn->prepare("SELECT id, username, password FROM users WHERE username = ?");
            $stmt->execute([$username]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                header('Location: pages/dashboard.php');
                exit();
            } else {
                $error = 'Invalid username or password';
            }
        } catch (PDOException $e) {
            $error = 'Database error: ' . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hotel Dashboard - Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            height: 100vh;
            margin: 0;
            background-color: #f8f9fa;
        }

        .split-screen-container {
            height: 100vh;
            overflow: hidden;
        }

        .login-container {
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100%;
            background-color: white;
            padding: 2rem;
        }

        .login-form {
            width: 100%;
            max-width: 400px;
        }

        .login-title {
            font-size: 2rem;
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 2rem;
        }

        .form-control {
            border-radius: 8px;
            padding: 0.8rem 1.2rem;
            border: 1px solid #e0e0e0;
            margin-bottom: 1rem;
        }

        .btn-login {
            width: 100%;
            padding: 0.8rem;
            border-radius: 8px;
            background-color: #2c3e50;
            border: none;
            color: white;
            font-weight: 500;
            margin-top: 1rem;
        }

        .btn-login:hover {
            background-color: #34495e;
        }

        .image-container {
            height: 100%;
            background-image: url('img/bg.jpg');
            background-size: cover;
            background-position: center;
            position: relative;
        }

        .image-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.3);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            color: white;
            text-align: center;
            padding: 2rem;
        }

        .overlay-title {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
        }

        .overlay-text {
            font-size: 1.1rem;
            max-width: 500px;
        }

        .error-message {
            color: #dc3545;
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            padding: 0.75rem 1.25rem;
            margin-bottom: 1rem;
            border-radius: 8px;
            display: none;
        }

        .brand-logo {
            width: 150px;
            margin-bottom: 2rem;
        }

        @media (max-width: 768px) {
            .image-container {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row split-screen-container">
            <!-- Login Section -->
            <div class="col-12 col-md-6 login-container">
                <div class="login-form">
                    <!-- Add your logo here -->
                    <!-- <img src="assets/images/logo.png" alt="Logo" class="brand-logo"> -->
                    <img src="favicon.png" alt="Logo" class="brand-logo" style="width: 60px;">
                    <h1 class="login-title">Welcome Back</h1>
                    <div id="error-message" class="error-message"></div>
                    
                    <form id="loginForm" onsubmit="return handleLogin(event)">
                        <div class="mb-3">
                            <label for="username" class="form-label">Username</label>
                            <input type="text" class="form-control" id="username" name="username" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        
                        <button type="submit" class="btn btn-login">Sign In</button>
                    </form>
                </div>
            </div>
            
            <!-- Image Section -->
            <div class="col-md-6 image-container">
                <div class="image-overlay">
                    <h2 class="overlay-title">MetaHotel Analytics Engine</h2>
                    <p class="overlay-text">
                        Access your comprehensive hotel management system to track bookings, 
                        monitor traffic, and analyze performance metrics in real-time.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <script>
        function handleLogin(event) {
            event.preventDefault();
            
            const username = document.getElementById('username').value;
            const password = document.getElementById('password').value;
            const errorMessage = document.getElementById('error-message');
            const loginButton = document.querySelector('.btn-login');
            
            // Disable button and show loading state
            loginButton.disabled = true;
            loginButton.innerHTML = 'Signing in...';
            errorMessage.style.display = 'none';
            
            fetch('api/login.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    username: username,
                    password: password
                })
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    window.location.href = 'pages/dashboard.php';
                } else {
                    errorMessage.style.display = 'block';
                    errorMessage.textContent = data.message || 'Invalid username or password';
                    loginButton.disabled = false;
                    loginButton.innerHTML = 'Sign In';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                errorMessage.style.display = 'block';
                errorMessage.textContent = 'An error occurred. Please try again.';
                loginButton.disabled = false;
                loginButton.innerHTML = 'Sign In';
            });

            return false;
        }
    </script>
</body>
</html> 