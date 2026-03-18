<?php
require 'config.php';
include 'header.php';

// Check if user is logged in and is a seller
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'seller') {
    header("Location: login.php");
    exit;
}

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $seller_id = $_SESSION['user_id'];
    $name = trim($_POST['name']);
    $breed = trim($_POST['breed']);
    $age = (int)$_POST['age'];
    $gender = $_POST['gender'];
    $description = trim($_POST['description']);
    $price = !empty($_POST['price']) ? (float)$_POST['price'] : 0.00;
    
    // Image upload handling
    $imagePath = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $filename = $_FILES['image']['name'];
        $filetype = pathinfo($filename, PATHINFO_EXTENSION);
        
        if (in_array(strtolower($filetype), $allowed)) {
            $newName = uniqid() . '.' . $filetype;
            $uploadDir = 'uploads/';
            
            if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir . $newName)) {
                $imagePath = $uploadDir . $newName;
            } else {
                $error = "Failed to upload image.";
            }
        } else {
            $error = "Only JPG, JPEG, PNG, and GIF files are allowed.";
        }
    }

    if (empty($error)) {
        if (empty($name) || empty($breed) || empty($gender)) {
            $error = "Please fill in all required fields.";
        } else {
            $stmt = $pdo->prepare("INSERT INTO pets (seller_id, name, breed, age, gender, description, price, image) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            if ($stmt->execute([$seller_id, $name, $breed, $age, $gender, $description, $price, $imagePath])) {
                header("Location: dashboard_seller.php?msg=Pet+listed+successfully!");
                exit;
            } else {
                $error = "Database error. Failed to add pet.";
            }
        }
    }
}
?>

<div class="form-container" style="max-width: 600px;">
    <h2 class="text-center" style="color: var(--primary-brown); margin-bottom: 1.5rem;">Add a Pet for Adoption</h2>
    
    <?php if ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" action="" enctype="multipart/form-data">
        <div class="form-group">
            <label for="name">Pet Name *</label>
            <input type="text" id="name" name="name" required>
        </div>
        
        <div class="form-group">
            <label for="breed">Breed *</label>
            <input type="text" id="breed" name="breed" required>
        </div>
        
        <div style="display: flex; gap: 1rem;">
            <div class="form-group" style="flex: 1;">
                <label for="age">Age (Years) *</label>
                <input type="number" id="age" name="age" min="0" required>
            </div>
            <div class="form-group" style="flex: 1;">
                <label for="gender">Gender *</label>
                <select id="gender" name="gender" required>
                    <option value="">Select...</option>
                    <option value="Male">Male</option>
                    <option value="Female">Female</option>
                    <option value="Unknown">Unknown</option>
                </select>
            </div>
        </div>
        
        <div class="form-group">
            <label for="price">Price/Adoption Fee ($) (Optional)</label>
            <input type="number" id="price" name="price" min="0" step="0.01" value="0.00">
        </div>
        
        <div class="form-group">
            <label for="image">Pet Photo (Optional)</label>
            <input type="file" id="image" name="image" accept="image/*">
        </div>
        
        <div class="form-group">
            <label for="description">Description</label>
            <textarea id="description" name="description" rows="4"></textarea>
        </div>
        
        <button type="submit" class="btn" style="width: 100%;">List Pet</button>
        <a href="dashboard_seller.php" class="btn btn-secondary mt-1" style="width: 100%; text-align: center;">Cancel</a>
    </form>
</div>

<?php include 'footer.php'; ?>
