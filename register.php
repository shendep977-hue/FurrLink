<?php
require 'config.php';
include 'header.php';

if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $role = $_POST['role'];

    if (empty($name) || empty($email) || empty($password) || empty($role)) {
        $error = "All fields are required.";
    } elseif ($role !== 'seller' && $role !== 'adopter') {
        $error = "Invalid role selected.";
    } else {
        // Check if email already exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $error = "Email is already registered.";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
            if ($stmt->execute([$name, $email, $hashed_password, $role])) {
                header("Location: login.php?msg=Registration+successful.+Please+login.");
                exit;
            } else {
                $error = "Registration failed. Please try again.";
            }
        }
    }
}
?>

<div class="form-container">
    <h2 class="text-center" style="color: var(--primary-brown); margin-bottom: 1.5rem;">Register</h2>
    
    <?php if ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" action="">
        <div class="form-group">
            <label for="name">Full Name or Shelter Name</label>
            <input type="text" id="name" name="name" required>
        </div>
        
        <div class="form-group">
            <label for="email">Email Address</label>
            <input type="email" id="email" name="email" required>
        </div>
        
        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" required>
        </div>
        
        <div class="form-group">
            <label for="role">Register As</label>
            <select id="role" name="role" required>
                <option value="">Select a role...</option>
                <option value="adopter">Buyer / Adopter (Looking for a pet)</option>
                <option value="seller">Seller / Shelter (Putting a pet up for adoption)</option>
            </select>
        </div>
        
        <button type="submit" class="btn" style="width: 100%;">Create Account</button>
        
        <p class="text-center mt-1">
            Already have an account? <a href="login.php" style="color: var(--primary-brown); font-weight: 500;">Login here</a>
        </p>
    </form>
</div>

<?php include 'footer.php'; ?>
