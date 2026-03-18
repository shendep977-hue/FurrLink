<?php
require 'config.php';
include 'header.php';

// Check if user is logged in and is a seller
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'seller') {
    header("Location: login.php");
    exit;
}

$seller_id = $_SESSION['user_id'];

// Get seller's pets
$stmt = $pdo->prepare("SELECT * FROM pets WHERE seller_id = ? ORDER BY created_at DESC");
$stmt->execute([$seller_id]);
$my_pets = $stmt->fetchAll();

// Get count of pending requests for seller's pets
$stmt = $pdo->prepare("
    SELECT COUNT(*) 
    FROM adoption_requests ar 
    JOIN pets p ON ar.pet_id = p.id 
    WHERE p.seller_id = ? AND ar.status = 'pending'
");
$stmt->execute([$seller_id]);
$pending_requests_count = $stmt->fetchColumn();
?>

<div style="margin-bottom: 2rem;">
    <h2 style="color: var(--primary-brown);">Welcome, <?= htmlspecialchars($_SESSION['name']) ?>!</h2>
    <p>This is your shelter/seller dashboard. Here you can manage your pet listings.</p>
</div>

<div style="display: flex; gap: 1rem; margin-bottom: 2rem;">
    <a href="add_pet.php" class="btn">Add New Pet</a>
    <a href="manage_requests.php" class="btn btn-secondary">
        View Adoption Requests
        <?php if ($pending_requests_count > 0): ?>
            <span style="background: red; color: white; padding: 2px 6px; border-radius: 50%; font-size: 0.8rem; margin-left: 5px;">
                <?= $pending_requests_count ?>
            </span>
        <?php endif; ?>
    </a>
</div>

<?php if (isset($_GET['msg'])): ?>
    <div class="alert alert-success"><?= htmlspecialchars($_GET['msg']) ?></div>
<?php endif; ?>

<h3>Your Pet Listings</h3>
<div class="pet-grid">
    <?php if (count($my_pets) > 0): ?>
        <?php foreach ($my_pets as $pet): ?>
            <div class="pet-card">
                <?php $imgSrc = !empty($pet['image']) ? htmlspecialchars($pet['image']) : 'https://via.placeholder.com/300x200?text=No+Image'; ?>
                <img src="<?= $imgSrc ?>" alt="<?= htmlspecialchars($pet['name']) ?>" class="pet-img">
                <div class="pet-info">
                    <h3 class="pet-name"><?= htmlspecialchars($pet['name']) ?></h3>
                    <div class="pet-meta">
                        Status: <span class="status-badge <?= $pet['status'] === 'Available' ? 'status-available' : 'status-adopted' ?>">
                            <?= htmlspecialchars($pet['status']) ?>
                        </span>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p style="grid-column: 1 / -1;">You haven't listed any pets yet. Click "Add New Pet" to get started.</p>
    <?php endif; ?>
</div>

<?php include 'footer.php'; ?>
