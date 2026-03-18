<?php
require 'config.php';
include 'header.php';

if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$error = '';
$msg = isset($_GET['msg']) ? $_GET['msg'] : '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        $error = "Both email and password are required.";
    } else {
        $stmt = $pdo->prepare("SELECT id, name, password, role FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            // Setup session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['name'] = $user['name'];
            $_SESSION['role'] = $user['role'];
            
            // Redirect based on role
            if ($user['role'] === 'admin') {
                header("Location: dashboard_admin.php");
            } elseif ($user['role'] === 'seller') {
                header("Location: dashboard_seller.php");
            } else {
                header("Location: dashboard_adopter.php");
            }
            exit;
        } else {
            $error = "Invalid email or password.";
        }
    }
}
?>

<div class="form-container">
    <h2 class="text-center" style="color: var(--primary-brown); margin-bottom: 1.5rem;">Login</h2>
    
    <?php if ($msg): ?>
        <div class="alert alert-success"><?= htmlspecialchars($msg) ?></div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" action="">
        <div class="form-group">
            <label for="email">Email Address</label>
            <input type="email" id="email" name="email" required>
        </div>
        
        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" required>
        </div>
        
        <button type="submit" class="btn" style="width: 100%;">Login</button>
        
        <p class="text-center mt-1">
            Don't have an account? <a href="register.php" style="color: var(--primary-brown); font-weight: 500;">Register here</a>
        </p>
    </form>
</div>

<?php include 'footer.php'; ?>
