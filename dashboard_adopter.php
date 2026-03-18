<?php
require 'config.php';
include 'header.php';

// Check if user is logged in and is an adopter
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'adopter') {
    header("Location: login.php");
    exit;
}

$adopter_id = $_SESSION['user_id'];

// Fetch the adopter's requests
$stmt = $pdo->prepare("
    SELECT ar.id, ar.status, ar.request_date,
           p.name as pet_name, p.image as pet_image, p.id as pet_id, p.status as pet_status,
           u.name as shelter_name, u.email as shelter_email
    FROM adoption_requests ar
    JOIN pets p ON ar.pet_id = p.id
    JOIN users u ON p.seller_id = u.id
    WHERE ar.adopter_id = ?
    ORDER BY ar.request_date DESC
");
$stmt->execute([$adopter_id]);
$requests = $stmt->fetchAll();
?>

<div style="margin-bottom: 2rem;">
    <h2 style="color: var(--primary-brown);">Welcome, <?= htmlspecialchars($_SESSION['name']) ?>!</h2>
    <p>This is your adopter dashboard. You can track the status of your adoption requests here.</p>
</div>

<div style="margin-bottom: 2rem;">
    <a href="index.php" class="btn">Browse Available Pets</a>
</div>

<?php if (isset($_GET['msg'])): ?>
    <div class="alert alert-success"><?= htmlspecialchars($_GET['msg']) ?></div>
<?php endif; ?>

<h3>Your Adoption Requests</h3>

<?php if (count($requests) > 0): ?>
    <table style="width: 100%; border-collapse: collapse; background: var(--white); box-shadow: 0 2px 5px rgba(0,0,0,0.05); margin-top: 1rem;">
        <thead>
            <tr style="background: var(--primary-brown); color: var(--white);">
                <th style="padding: 1rem; text-align: left;">Pet Image</th>
                <th style="padding: 1rem; text-align: left;">Pet Name</th>
                <th style="padding: 1rem; text-align: left;">Shelter/Seller</th>
                <th style="padding: 1rem; text-align: left;">Request Date</th>
                <th style="padding: 1rem; text-align: left;">Request Status</th>
                <th style="padding: 1rem; text-align: left;">Shelter Contact</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($requests as $req): ?>
                <tr style="border-bottom: 1px solid var(--border-color);">
                    <td style="padding: 1rem;">
                        <?php $imgSrc = !empty($req['pet_image']) ? htmlspecialchars($req['pet_image']) : 'https://via.placeholder.com/80?text=No+Image'; ?>
                        <img src="<?= $imgSrc ?>" alt="<?= htmlspecialchars($req['pet_name']) ?>" style="width: 80px; height: 60px; object-fit: cover; border-radius: 4px;">
                    </td>
                    <td style="padding: 1rem;">
                        <a href="pet_details.php?id=<?= $req['pet_id'] ?>" style="color: var(--primary-brown); font-weight: bold; text-decoration: none;">
                            <?= htmlspecialchars($req['pet_name']) ?>
                        </a>
                    </td>
                    <td style="padding: 1rem;"><?= htmlspecialchars($req['shelter_name']) ?></td>
                    <td style="padding: 1rem;"><?= date('M j, Y', strtotime($req['request_date'])) ?></td>
                    <td style="padding: 1rem;">
                        <span style="font-weight:bold; color: <?= $req['status']=='approved' ? 'green' : ($req['status']=='rejected' ? 'red' : 'orange') ?>">
                            <?= ucfirst($req['status']) ?>
                        </span>
                        <?php if ($req['pet_status'] === 'Adopted' && $req['status'] !== 'approved'): ?>
                            <br><small style="color: gray;">(Pet no longer available)</small>
                        <?php endif; ?>
                    </td>
                    <td style="padding: 1rem;">
                        <?php if ($req['status'] === 'approved'): ?>
                            <a href="mailto:<?= htmlspecialchars($req['shelter_email']) ?>" class="btn" style="padding: 0.3rem 0.6rem; font-size: 0.8rem;">Contact Shelter</a>
                        <?php else: ?>
                            <span style="color: gray; font-size: 0.8rem;">Available after approval</span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php else: ?>
    <div style="background: var(--white); padding: 2rem; border-radius: 8px; border: 1px solid var(--border-color); text-align: center;">
        <p>You haven't submitted any adoption requests yet.</p>
        <p><a href="index.php" style="color: var(--primary-brown); font-weight: bold;">Browse pets</a> and find your new best friend!</p>
    </div>
<?php endif; ?>

<?php include 'footer.php'; ?>
