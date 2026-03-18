<?php
require 'config.php';
include 'header.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$pet_id = (int)$_GET['id'];

// Fetch pet details
$stmt = $pdo->prepare("
    SELECT p.*, u.name as seller_name, u.email as seller_email 
    FROM pets p
    JOIN users u ON p.seller_id = u.id
    WHERE p.id = ?
");
$stmt->execute([$pet_id]);
$pet = $stmt->fetch();

if (!$pet) {
    echo "<div class='alert alert-danger'>Pet not found.</div>";
    include 'footer.php';
    exit;
}

// Handle Adoption Request Submission
$msg = '';
$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['adopt'])) {
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php?msg=Please+login+to+send+an+adoption+request.");
        exit;
    }
    
    if ($_SESSION['role'] !== 'adopter') {
        $error = "Only buyers/adopters can submit adoption requests.";
    } elseif ($pet['status'] !== 'Available') {
        $error = "Sorry, this pet is no longer available for adoption.";
    } else {
        $adopter_id = $_SESSION['user_id'];
        
        // Check if user has already requested this pet
        $stmt = $pdo->prepare("SELECT id FROM adoption_requests WHERE pet_id = ? AND adopter_id = ?");
        $stmt->execute([$pet_id, $adopter_id]);
        if ($stmt->fetch()) {
            $error = "You have already submitted an adoption request for this pet.";
        } else {
            // Insert request
            $stmt = $pdo->prepare("INSERT INTO adoption_requests (pet_id, adopter_id, status) VALUES (?, ?, 'pending')");
            if ($stmt->execute([$pet_id, $adopter_id])) {
                header("Location: dashboard_adopter.php?msg=Adoption+request+submitted+successfully!");
                exit;
            } else {
                $error = "Database error. Failed to submit request.";
            }
        }
    }
}
?>

<div style="margin-bottom: 2rem;">
    <a href="index.php" class="btn btn-secondary">&larr; Back to Available Pets</a>
</div>

<?php if ($msg): ?>
    <div class="alert alert-success"><?= htmlspecialchars($msg) ?></div>
<?php endif; ?>
<?php if ($error): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<div style="display: flex; gap: 2rem; background: var(--white); padding: 2rem; border-radius: 8px; border: 1px solid var(--border-color); box-shadow: 0 2px 10px rgba(0,0,0,0.05); flex-wrap: wrap;">
    <div style="flex: 1; min-width: 300px;">
        <?php $imgSrc = !empty($pet['image']) ? htmlspecialchars($pet['image']) : 'https://via.placeholder.com/600x400?text=No+Image'; ?>
        <img src="<?= $imgSrc ?>" alt="<?= htmlspecialchars($pet['name']) ?>" style="width: 100%; height: auto; border-radius: 8px; border: 1px solid var(--border-color);">
    </div>
    <div style="flex: 1; min-width: 300px;">
        <h1 style="color: var(--primary-brown); margin-bottom: 0.5rem;"><?= htmlspecialchars($pet['name']) ?></h1>
        <div style="margin-bottom: 1.5rem;">
            <span class="status-badge <?= $pet['status'] === 'Available' ? 'status-available' : 'status-adopted' ?>" style="font-size: 1rem; padding: 0.4rem 1rem;">
                <?= htmlspecialchars($pet['status']) ?>
            </span>
        </div>
        
        <table style="width: 100%; border-collapse: collapse; margin-bottom: 1.5rem;">
            <tr style="border-bottom: 1px solid var(--border-color);">
                <th style="text-align: left; padding: 0.8rem 0; width: 30%;">Breed:</th>
                <td style="padding: 0.8rem 0;"><?= htmlspecialchars($pet['breed']) ?></td>
            </tr>
            <tr style="border-bottom: 1px solid var(--border-color);">
                <th style="text-align: left; padding: 0.8rem 0;">Age:</th>
                <td style="padding: 0.8rem 0;"><?= htmlspecialchars($pet['age']) ?> Years</td>
            </tr>
            <tr style="border-bottom: 1px solid var(--border-color);">
                <th style="text-align: left; padding: 0.8rem 0;">Gender:</th>
                <td style="padding: 0.8rem 0;"><?= htmlspecialchars($pet['gender']) ?></td>
            </tr>
            <tr style="border-bottom: 1px solid var(--border-color);">
                <th style="text-align: left; padding: 0.8rem 0;">Adoption Fee / Price:</th>
                <td style="padding: 0.8rem 0;">$<?= number_format($pet['price'], 2) ?></td>
            </tr>
            <tr style="border-bottom: 1px solid var(--border-color);">
                <th style="text-align: left; padding: 0.8rem 0;">Shelter/Owner:</th>
                <td style="padding: 0.8rem 0;"><?= htmlspecialchars($pet['seller_name']) ?></td>
            </tr>
        </table>
        
        <div style="margin-bottom: 2rem;">
            <h3 style="margin-bottom: 0.5rem; color: var(--text-dark);">About <?= htmlspecialchars($pet['name']) ?></h3>
            <p style="white-space: pre-wrap;"><?= htmlspecialchars($pet['description']) ?></p>
        </div>
        
        <?php if ($pet['status'] === 'Available'): ?>
            <form method="POST" action="">
                <button type="submit" name="adopt" class="btn" style="width: 100%; font-size: 1.2rem; padding: 1rem;">Adopt <?= htmlspecialchars($pet['name']) ?></button>
            </form>
        <?php else: ?>
            <div class="alert alert-success" style="text-align: center; font-weight: bold;">
                This pet has already been adopted!
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include 'footer.php'; ?>
