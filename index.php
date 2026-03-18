<?php
require 'config.php';
include 'header.php';

// Fetch available pets
$stmt = $pdo->query("SELECT * FROM pets WHERE status = 'Available' ORDER BY created_at DESC");
$pets = $stmt->fetchAll();
?>

<div style="text-align: center; margin-bottom: 2rem;">
    <h1 style="color: var(--primary-brown);">Welcome to FURLINK</h1>
    <p>Find your new best friend today. Browse our available pets below.</p>
</div>

<?php if (isset($_GET['msg'])): ?>
    <div class="alert alert-success"><?= htmlspecialchars($_GET['msg']) ?></div>
<?php endif; ?>

<div class="pet-grid">
    <?php if (count($pets) > 0): ?>
        <?php foreach ($pets as $pet): ?>
            <div class="pet-card">
                <?php $imgSrc = !empty($pet['image']) ? htmlspecialchars($pet['image']) : 'https://via.placeholder.com/300x200?text=No+Image'; ?>
                <img src="<?= $imgSrc ?>" alt="<?= htmlspecialchars($pet['name']) ?>" class="pet-img">
                <div class="pet-info">
                    <h3 class="pet-name"><?= htmlspecialchars($pet['name']) ?></h3>
                    <div class="pet-meta">
                        <strong>Breed:</strong> <?= htmlspecialchars($pet['breed']) ?><br>
                        <strong>Age:</strong> <?= htmlspecialchars($pet['age']) ?> years<br>
                        <strong>Gender:</strong> <?= htmlspecialchars($pet['gender']) ?>
                    </div>
                    <?php if (strlen($pet['description']) > 100): ?>
                        <p><?= htmlspecialchars(substr($pet['description'], 0, 100)) ?>...</p>
                    <?php else: ?>
                        <p><?= htmlspecialchars($pet['description']) ?></p>
                    <?php endif; ?>
                    <br>
                    <a href="pet_details.php?id=<?= $pet['id'] ?>" class="btn">View Details / Adopt</a>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p style="text-align: center; grid-column: 1 / -1;">No pets currently available for adoption. Please check back later.</p>
    <?php endif; ?>
</div>

<?php include 'footer.php'; ?>
