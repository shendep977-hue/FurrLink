<?php
require 'config.php';
include 'header.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

$msg = '';
$error = '';

// Handle deletions
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['delete_user'])) {
        $user_id = (int)$_POST['user_id'];
        if ($user_id !== $_SESSION['user_id']) { // Check so admin doesn't delete themselves
            $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
            if ($stmt->execute([$user_id])) {
                $msg = "User deleted successfully.";
            } else {
                $error = "Failed to delete user.";
            }
        } else {
            $error = "You cannot delete your own admin account.";
        }
    } elseif (isset($_POST['delete_pet'])) {
        $pet_id = (int)$_POST['pet_id'];
        $stmt = $pdo->prepare("DELETE FROM pets WHERE id = ?");
        if ($stmt->execute([$pet_id])) {
            $msg = "Pet listing removed.";
        } else {
            $error = "Failed to remove pet listing.";
        }
    }
}

// Fetch all users
$stmt = $pdo->query("SELECT id, name, email, role, created_at FROM users ORDER BY created_at DESC");
$all_users = $stmt->fetchAll();

// Fetch all pets
$stmt = $pdo->query("SELECT p.id, p.name, p.status, p.created_at, u.name as seller_name FROM pets p JOIN users u ON p.seller_id = u.id ORDER BY p.created_at DESC");
$all_pets = $stmt->fetchAll();
?>

<div style="margin-bottom: 2rem;">
    <h2 style="color: var(--primary-brown);">Admin Panel</h2>
    <p>Manage all users and pet listings across the FURLINK platform.</p>
</div>

<?php if ($msg): ?>
    <div class="alert alert-success"><?= htmlspecialchars($msg) ?></div>
<?php endif; ?>
<?php if ($error): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<div style="display: flex; gap: 2rem; flex-wrap: wrap;">
    
    <!-- Users Management -->
    <div style="flex: 1; min-width: 300px;">
        <h3>Manage Users</h3>
        <table style="width: 100%; border-collapse: collapse; background: var(--white); box-shadow: 0 2px 5px rgba(0,0,0,0.05); margin-top: 1rem;">
            <thead>
                <tr style="background: var(--text-dark); color: var(--white);">
                    <th style="padding: 0.5rem; text-align: left;">Name</th>
                    <th style="padding: 0.5rem; text-align: left;">Role</th>
                    <th style="padding: 0.5rem; text-align: left;">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($all_users as $u): ?>
                    <tr style="border-bottom: 1px solid var(--border-color);">
                        <td style="padding: 0.5rem;">
                            <?= htmlspecialchars($u['name']) ?><br>
                            <small><?= htmlspecialchars($u['email']) ?></small>
                        </td>
                        <td style="padding: 0.5rem;"><?= ucfirst($u['role']) ?></td>
                        <td style="padding: 0.5rem;">
                            <?php if ($u['id'] !== $_SESSION['user_id']): ?>
                                <form method="POST" style="display:inline;" onsubmit="return confirm('Delete this user? This removes their pets and requests too.');">
                                    <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                                    <button type="submit" name="delete_user" class="btn btn-secondary" style="padding: 0.2rem 0.5rem; font-size: 0.8rem; color:red !important; border-color:red;">Delete</button>
                                </form>
                            <?php else: ?>
                                <small>Current User</small>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    
    <!-- Pets Management -->
    <div style="flex: 1; min-width: 300px;">
        <h3>Manage Pets</h3>
        <table style="width: 100%; border-collapse: collapse; background: var(--white); box-shadow: 0 2px 5px rgba(0,0,0,0.05); margin-top: 1rem;">
            <thead>
                <tr style="background: var(--primary-brown); color: var(--white);">
                    <th style="padding: 0.5rem; text-align: left;">Pet</th>
                    <th style="padding: 0.5rem; text-align: left;">Status</th>
                    <th style="padding: 0.5rem; text-align: left;">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($all_pets as $p): ?>
                    <tr style="border-bottom: 1px solid var(--border-color);">
                        <td style="padding: 0.5rem;">
                            <a href="pet_details.php?id=<?= $p['id'] ?>" style="color: var(--primary-brown); font-weight: bold;"><?= htmlspecialchars($p['name']) ?></a><br>
                            <small>By: <?= htmlspecialchars($p['seller_name']) ?></small>
                        </td>
                        <td style="padding: 0.5rem;"><?= htmlspecialchars($p['status']) ?></td>
                        <td style="padding: 0.5rem;">
                            <form method="POST" style="display:inline;" onsubmit="return confirm('Remove this pet listing?');">
                                <input type="hidden" name="pet_id" value="<?= $p['id'] ?>">
                                <button type="submit" name="delete_pet" class="btn btn-secondary" style="padding: 0.2rem 0.5rem; font-size: 0.8rem; color:red !important; border-color:red;">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

</div>

<?php include 'footer.php'; ?>
