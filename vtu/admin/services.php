<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/admin-auth.php';

$db = DB::getInstance()->getConnection();

// Handle add/edit service
$success = '';
$error = '';
$editService = null;

if (isset($_GET['edit'])) {
    $id = intval($_GET['edit']);
    $stmt = $db->prepare("SELECT * FROM services WHERE id = ?");
    $stmt->execute([$id]);
    $editService = $stmt->fetch(PDO::FETCH_ASSOC);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $type = trim($_POST['type'] ?? '');
    $icon = trim($_POST['icon'] ?? '');
    $link = trim($_POST['link'] ?? '');
    $status = $_POST['status'] ?? 'active';

    if (!$name || !$type) {
        $error = "Please enter all required fields.";
    } else {
        if (isset($_POST['service_id']) && $_POST['service_id']) {
            // Update
            $stmt = $db->prepare("UPDATE services SET name = ?, type = ?, icon = ?, link = ?, status = ? WHERE id = ?");
            $stmt->execute([$name, $type, $icon, $link, $status, intval($_POST['service_id'])]);
            $success = "Service updated successfully.";
        } else {
            // Add new
            $stmt = $db->prepare("INSERT INTO services (name, type, icon, link, status, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
            $stmt->execute([$name, $type, $icon, $link, $status]);
            $success = "Service added successfully.";
        }
    }
}

// Fetch all services
$stmt = $db->query("SELECT * FROM services ORDER BY created_at DESC");
$services = $stmt->fetchAll(PDO::FETCH_ASSOC);

include '../includes/admin-header.php';
?>
<div class="container" style="max-width:900px;margin:2rem auto;">
    <div class="row">
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><?php echo $editService ? 'Edit Service' : 'Add New Service'; ?></h5>
                </div>
                <div class="card-body">
                    <?php if ($success): ?>
                        <div class="alert alert-success"><?php echo $success; ?></div>
                    <?php endif; ?>
                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>
                    <form method="post">
                        <?php if ($editService): ?>
                            <input type="hidden" name="service_id" value="<?php echo $editService['id']; ?>">
                        <?php endif; ?>
                        <div class="mb-3">
                            <label for="name" class="form-label">Service Name</label>
                            <input type="text" class="form-control" id="name" name="name" required value="<?php echo htmlspecialchars($editService['name'] ?? ''); ?>">
                        </div>
                        <div class="mb-3">
                            <label for="type" class="form-label">Type</label>
                            <select class="form-select" id="type" name="type" required>
                                <option value="">Select Type</option>
                                <option value="airtime" <?php if(($editService['type'] ?? '')=='airtime') echo 'selected'; ?>>Airtime</option>
                                <option value="data" <?php if(($editService['type'] ?? '')=='data') echo 'selected'; ?>>Data</option>
                                <option value="bills" <?php if(($editService['type'] ?? '')=='bills') echo 'selected'; ?>>Bills</option>
                                <option value="cable" <?php if(($editService['type'] ?? '')=='cable') echo 'selected'; ?>>Cable TV</option>
                                <option value="crypto" <?php if(($editService['type'] ?? '')=='crypto') echo 'selected'; ?>>Crypto</option>
                                <option value="other" <?php if(($editService['type'] ?? '')=='other') echo 'selected'; ?>>Other</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="icon" class="form-label">Icon (FontAwesome class)</label>
                            <input type="text" class="form-control" id="icon" name="icon" placeholder="e.g. fas fa-phone-alt" value="<?php echo htmlspecialchars($editService['icon'] ?? ''); ?>">
                            <small class="text-muted">Example: fas fa-phone-alt, fas fa-database, fab fa-bitcoin</small>
                        </div>
                        <div class="mb-3">
                            <label for="link" class="form-label">Link (URL)</label>
                            <input type="text" class="form-control" id="link" name="link" placeholder="e.g. airtime.php" value="<?php echo htmlspecialchars($editService['link'] ?? ''); ?>">
                            <small class="text-muted">Example: airtime.php, data.php, crypto-wallet.php</small>
                        </div>
                        <div class="mb-3">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-select" id="status" name="status">
                                <option value="active" <?php if(($editService['status'] ?? '')=='active') echo 'selected'; ?>>Active</option>
                                <option value="inactive" <?php if(($editService['status'] ?? '')=='inactive') echo 'selected'; ?>>Inactive</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary"><?php echo $editService ? 'Update' : 'Add'; ?> Service</button>
                        <?php if ($editService): ?>
                            <a href="services.php" class="btn btn-secondary">Cancel</a>
                        <?php endif; ?>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">All Services</h5>
                </div>
                <div class="card-body">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Type</th>
                                <th>Status</th>
                                <th>Created</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($services as $svc): ?>
                                <tr>
                                    <td>
                                        <i class="<?php echo htmlspecialchars($svc['icon'] ?? 'fas fa-cogs'); ?>" style="margin-right:6px;"></i>
                                        <?php echo htmlspecialchars($svc['name']); ?>
                                    </td>
                                    <td><?php echo ucfirst($svc['type']); ?></td>
                                    <td>
                                        <span class="badge bg-<?php echo $svc['status']=='active'?'success':'secondary'; ?>">
                                            <?php echo ucfirst($svc['status']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('M j, Y', strtotime($svc['created_at'])); ?></td>
                                    <td>
                                        <a href="services.php?edit=<?php echo $svc['id']; ?>" class="btn btn-sm btn-warning">Edit</a>
                                        <?php if (!empty($svc['link'])): ?>
                                            <a href="../<?php echo htmlspecialchars($svc['link']); ?>" class="btn btn-sm btn-primary" target="_blank">Go</a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            <?php if (empty($services)): ?>
                                <tr>
                                    <td colspan="5" class="text-center text-muted">No services found.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include '../includes/admin-footer.php'; ?>
