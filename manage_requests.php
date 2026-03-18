<?php
require 'config.php';
include 'header.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'seller') {
    header("Location: login.php");
    exit;
}

$seller_id = $_SESSION['user_id'];
$msg = '';
$error = '';

// Handle approval/rejection
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && isset($_POST['request_id'])) {
    $request_id = (int)$_POST['request_id'];
    $action = $_POST['action']; // 'approve' or 'reject'
    
    // Verify this request belongs to one of the seller's pets
    $stmt = $pdo->prepare("
        SELECT ar.id, p.id as pet_id 
        FROM adoption_requests ar
        JOIN pets p ON ar.pet_id = p.id
        WHERE ar.id = ? AND p.seller_id = ?
    ");
    $stmt->execute([$request_id, $seller_id]);
    $req = $stmt->fetch();
    
    if ($req) {
        try {
            $pdo->beginTransaction();
            
            if ($action === 'approve') {
                // Update request status
                $stmt = $pdo->prepare("UPDATE adoption_requests SET status = 'approved' WHERE id = ?");
                $stmt->execute([$request_id]);
                
                // Update pet status to Adopted
                $stmt = $pdo->prepare("UPDATE pets SET status = 'Adopted' WHERE id = ?");
                $stmt->execute([$req['pet_id']]);
                
                // Reject all other pending requests for this pet
                $stmt = $pdo->prepare("UPDATE adoption_requests SET status = 'rejected' WHERE pet_id = ? AND id != ?");
                $stmt->execute([$req['pet_id'], $request_id]);
                
                $msg = "Request approved successfully! Pet is now marked as Adopted.";
            } elseif ($action === 'reject') {
                $stmt = $pdo->prepare("UPDATE adoption_requests SET status = 'rejected' WHERE id = ?");
                $stmt->execute([$request_id]);
                $msg = "Request rejected.";
            }
            
            $pdo->commit();
        } catch (Exception $e) {
            $pdo->rollBack();
            $error = "Error processing request.";
        }
    } else {
        $error = "Invalid request.";
    }
}

// Fetch requests
$stmt = $pdo->prepare("
    SELECT ar.id, ar.status, ar.request_date,
           p.name as pet_name, p.image as pet_image,
           u.name as adopter_name, u.email as adopter_email
    FROM adoption_requests ar
    JOIN pets p ON ar.pet_id = p.id
    JOIN users u ON ar.adopter_id = u.id
    WHERE p.seller_id = ?
    ORDER BY ar.request_date DESC
");
$stmt->execute([$seller_id]);
$requests = $stmt->fetchAll();
?>

<div style="margin-bottom: 2rem;">
    <h2 style="color: var(--primary-brown);">Adoption Requests</h2>
    <p>Manage applications from users wanting to adopt your pets.</p>
</div>

<?php if ($msg): ?>
    <div class="alert alert-success"><?= htmlspecialchars($msg) ?></div>
<?php endif; ?>
<?php if ($error): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<a href="dashboard_seller.php" class="btn btn-secondary mb-2">Back to Dashboard</a>

<table style="width: 100%; border-collapse: collapse; background: var(--white); box-shadow: 0 2px 5px rgba(0,0,0,0.05);">
    <thead>
        <tr style="background: var(--primary-brown); color: var(--white);">
            <th style="padding: 1rem; text-align: left;">Pet</th>
            <th style="padding: 1rem; text-align: left;">Adopter Info</th>
            <th style="padding: 1rem; text-align: left;">Date</th>
            <th style="padding: 1rem; text-align: left;">Status</th>
            <th style="padding: 1rem; text-align: left;">Action</th>
        </tr>
    </thead>
    <tbody>
        <?php if (count($requests) > 0): ?>
            <?php foreach ($requests as $req): ?>
                <tr style="border-bottom: 1px solid var(--border-color);">
                    <td style="padding: 1rem;">
                        <strong><?= htmlspecialchars($req['pet_name']) ?></strong>
                    </td>
                    <td style="padding: 1rem;">
                        <?= htmlspecialchars($req['adopter_name']) ?><br>
                        <small><a href="mailto:<?= htmlspecialchars($req['adopter_email']) ?>"><?= htmlspecialchars($req['adopter_email']) ?></a></small>
                    </td>
                    <td style="padding: 1rem;"><?= date('M j, Y', strtotime($req['request_date'])) ?></td>
                    <td style="padding: 1rem;">
                        <span style="font-weight:bold; color: <?= $req['status']=='approved' ? 'green' : ($req['status']=='rejected' ? 'red' : 'orange') ?>">
                            <?= ucfirst($req['status']) ?>
                        </span>
                    </td>
                    <td style="padding: 1rem;">
                        <?php if ($req['status'] === 'pending'): ?>
                            <form method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to approve this request?');">
                                <input type="hidden" name="request_id" value="<?= $req['id'] ?>">
                                <input type="hidden" name="action" value="approve">
                                <button type="submit" class="btn" style="padding: 0.3rem 0.6rem; font-size: 0.8rem;">Approve</button>
                            </form>
                            <form method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to reject this request?');">
                                <input type="hidden" name="request_id" value="<?= $req['id'] ?>">
                                <input type="hidden" name="action" value="reject">
                                <button type="submit" class="btn btn-secondary" style="padding: 0.3rem 0.6rem; font-size: 0.8rem; color:red !important; border-color:red;">Reject</button>
                            </form>
                        <?php else: ?>
                            <em>No actions available</em>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="5" style="padding: 1rem; text-align: center;">No adoption requests found.</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>

<?php include 'footer.php'; ?>
