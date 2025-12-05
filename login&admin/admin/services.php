<?php
// admin/services.php
require_once '../config/database.php';

if (!is_logged_in() || !is_admin()) {
    redirect('../login.php');
}

$success = '';
$error = '';

// Handle add/edit service
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_service'])) {
    $service_id = isset($_POST['service_id']) ? (int)$_POST['service_id'] : 0;
    $service_name = sanitize_input($_POST['service_name']);
    $description = sanitize_input($_POST['description']);
    $category = sanitize_input($_POST['category']);
    $price = floatval($_POST['price']);
    $duration = intval($_POST['duration']);
    $availability = sanitize_input($_POST['availability']);
    
    if (empty($service_name)) {
        $error = 'Service name is required';
    } else {
        if ($service_id > 0) {
            // Update existing service
            $stmt = $conn->prepare("UPDATE services SET service_name = ?, description = ?, category = ?, price = ?, duration = ?, availability = ? WHERE id = ?");
            $stmt->bind_param("sssdiis", $service_name, $description, $category, $price, $duration, $availability, $service_id);
            $action = 'Update Service';
        } else {
            // Add new service
            $stmt = $conn->prepare("INSERT INTO services (service_name, description, category, price, duration, availability) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sssdis", $service_name, $description, $category, $price, $duration, $availability);
            $action = 'Add Service';
        }
        
        if ($stmt->execute()) {
            $success = $service_id > 0 ? 'Service updated successfully' : 'Service added successfully';
            log_activity($_SESSION['user_id'], $action, "Service: $service_name");
        } else {
            $error = 'Failed to save service';
        }
    }
}

// Handle delete service
if (isset($_GET['delete'])) {
    $service_id = (int)$_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM services WHERE id = ?");
    $stmt->bind_param("i", $service_id);
    
    if ($stmt->execute()) {
        $success = 'Service deleted successfully';
        log_activity($_SESSION['user_id'], 'Delete Service', "Service ID: $service_id");
    } else {
        $error = 'Failed to delete service';
    }
}

// Get all services
$services = $conn->query("SELECT * FROM services ORDER BY created_at DESC");

// Get service for editing
$edit_service = null;
if (isset($_GET['edit'])) {
    $edit_id = (int)$_GET['edit'];
    $stmt = $conn->prepare("SELECT * FROM services WHERE id = ?");
    $stmt->bind_param("i", $edit_id);
    $stmt->execute();
    $edit_service = $stmt->get_result()->fetch_assoc();
}

$page_title = 'Services Management';
include 'includes/header.php';
?>

<div class="content-container">
    <h1 style="margin-bottom: 30px;">Services Management</h1>
    
    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
    <?php endif; ?>
    
    <?php if ($error): ?>
        <div class="alert alert-error"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <!-- Add/Edit Service Form -->
    <div class="data-table" style="margin-bottom: 30px;">
        <h2><?php echo $edit_service ? 'Edit Service' : 'Add New Service'; ?></h2>
        <form method="POST" action="">
            <?php if ($edit_service): ?>
                <input type="hidden" name="service_id" value="<?php echo $edit_service['id']; ?>">
            <?php endif; ?>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div class="form-group">
                    <label for="service_name">Service Name *</label>
                    <input type="text" id="service_name" name="service_name" required 
                           value="<?php echo $edit_service ? htmlspecialchars($edit_service['service_name']) : ''; ?>">
                </div>
                
                <div class="form-group">
                    <label for="category">Category</label>
                    <input type="text" id="category" name="category" 
                           value="<?php echo $edit_service ? htmlspecialchars($edit_service['category']) : ''; ?>">
                </div>
                
                <div class="form-group">
                    <label for="price">Price ($)</label>
                    <input type="number" id="price" name="price" step="0.01" min="0" 
                           value="<?php echo $edit_service ? $edit_service['price'] : '0'; ?>">
                </div>
                
                <div class="form-group">
                    <label for="duration">Duration (minutes)</label>
                    <input type="number" id="duration" name="duration" min="0" 
                           value="<?php echo $edit_service ? $edit_service['duration'] : '60'; ?>">
                </div>
                
                <div class="form-group">
                    <label for="availability">Availability</label>
                    <select id="availability" name="availability">
                        <option value="available" <?php echo ($edit_service && $edit_service['availability'] === 'available') ? 'selected' : ''; ?>>Available</option>
                        <option value="unavailable" <?php echo ($edit_service && $edit_service['availability'] === 'unavailable') ? 'selected' : ''; ?>>Unavailable</option>
                    </select>
                </div>
            </div>
            
            <div class="form-group">
                <label for="description">Description</label>
                <textarea id="description" name="description" rows="4"><?php echo $edit_service ? htmlspecialchars($edit_service['description']) : ''; ?></textarea>
            </div>
            
            <div style="display: flex; gap: 10px;">
                <button type="submit" name="save_service" class="btn btn-primary">
                    <?php echo $edit_service ? 'Update Service' : 'Add Service'; ?>
                </button>
                <?php if ($edit_service): ?>
                    <a href="services.php" class="btn btn-secondary">Cancel</a>
                <?php endif; ?>
            </div>
        </form>
    </div>
    
    <!-- Services List -->
    <div class="data-table">
        <h2>All Services</h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Service Name</th>
                    <th>Category</th>
                    <th>Price</th>
                    <th>Duration</th>
                    <th>Availability</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($services && $services->num_rows > 0): ?>
                    <?php while ($service = $services->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $service['id']; ?></td>
                            <td><?php echo htmlspecialchars($service['service_name']); ?></td>
                            <td><?php echo htmlspecialchars($service['category']); ?></td>
                            <td>$<?php echo number_format($service['price'], 2); ?></td>
                            <td><?php echo $service['duration']; ?> mins</td>
                            <td>
                                <span class="badge <?php echo $service['availability'] === 'available' ? 'badge-success' : 'badge-danger'; ?>">
                                    <?php echo ucfirst($service['availability']); ?>
                                </span>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <a href="?edit=<?php echo $service['id']; ?>" class="btn btn-sm btn-primary">Edit</a>
                                    <a href="?delete=<?php echo $service['id']; ?>" 
                                       onclick="return confirm('Are you sure you want to delete this service?')" 
                                       class="btn btn-sm btn-danger">Delete</a>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" style="text-align: center;">No services found</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include 'includes/footer.php'; ?>