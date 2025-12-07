<?php
// admin/services.php - Updated with proper service management
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
    $subcategory = sanitize_input($_POST['subcategory']);
    $price = floatval($_POST['price']);
    $capacity = intval($_POST['capacity']);
    $availability = sanitize_input($_POST['availability']);
    $amenities = sanitize_input($_POST['amenities']);
    
    if (empty($service_name)) {
        $error = 'Service name is required';
    } else {
        if ($service_id > 0) {
            // Update existing service
            $stmt = $conn->prepare("UPDATE services SET service_name = ?, description = ?, category = ?, subcategory = ?, price = ?, capacity = ?, availability = ?, amenities = ? WHERE id = ?");
            $stmt->bind_param("ssssdissi", $service_name, $description, $category, $subcategory, $price, $capacity, $availability, $amenities, $service_id);
            $action = 'Update Service';
        } else {
            // Add new service
            $stmt = $conn->prepare("INSERT INTO services (service_name, description, category, subcategory, price, capacity, availability, amenities) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssdiSs", $service_name, $description, $category, $subcategory, $price, $capacity, $availability, $amenities);
            $action = 'Add Service';
        }
        
        if ($stmt->execute()) {
            $success = $service_id > 0 ? 'Service updated successfully' : 'Service added successfully';
            log_activity($_SESSION['user_id'], $action, "Service: $service_name");
        } else {
            $error = 'Failed to save service: ' . $stmt->error;
        }
    }
}

// Handle delete service
if (isset($_GET['delete'])) {
    $service_id = (int)$_GET['delete'];
    
    // Check if service is used in any reservations
    $checkStmt = $conn->prepare("SELECT COUNT(*) as count FROM reservation_items WHERE service_id = ?");
    $checkStmt->bind_param("i", $service_id);
    $checkStmt->execute();
    $result = $checkStmt->get_result();
    $row = $result->fetch_assoc();
    
    if ($row['count'] > 0) {
        $error = 'Cannot delete service that has existing reservations. Please set availability to unavailable instead.';
    } else {
        $stmt = $conn->prepare("DELETE FROM services WHERE id = ?");
        $stmt->bind_param("i", $service_id);
        
        if ($stmt->execute()) {
            $success = 'Service deleted successfully';
            log_activity($_SESSION['user_id'], 'Delete Service', "Service ID: $service_id");
        } else {
            $error = 'Failed to delete service';
        }
    }
}

// Get filter parameters
$category_filter = isset($_GET['category']) ? sanitize_input($_GET['category']) : 'all';
$availability_filter = isset($_GET['availability']) ? sanitize_input($_GET['availability']) : 'all';

// Build query
$query = "SELECT * FROM services WHERE 1=1";

if ($category_filter !== 'all') {
    $query .= " AND category = '" . $conn->real_escape_string($category_filter) . "'";
}

if ($availability_filter !== 'all') {
    $query .= " AND availability = '" . $conn->real_escape_string($availability_filter) . "'";
}

$query .= " ORDER BY category, service_name";

$services = $conn->query($query);

// Get service for editing
$edit_service = null;
if (isset($_GET['edit'])) {
    $edit_id = (int)$_GET['edit'];
    $stmt = $conn->prepare("SELECT * FROM services WHERE id = ?");
    $stmt->bind_param("i", $edit_id);
    $stmt->execute();
    $edit_service = $stmt->get_result()->fetch_assoc();
}

// Get statistics
$stats_query = "SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN category = 'cottage' THEN 1 ELSE 0 END) as cottages,
    SUM(CASE WHEN category = 'room' THEN 1 ELSE 0 END) as rooms,
    SUM(CASE WHEN category = 'cuarto' THEN 1 ELSE 0 END) as cuartos,
    SUM(CASE WHEN availability = 'available' THEN 1 ELSE 0 END) as available
    FROM services";
$stats = $conn->query($stats_query)->fetch_assoc();

$page_title = 'Services Management';
include 'includes/header.php';
?>

<div class="content-container">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
        <h1>Services Management</h1>
        <?php if (!$edit_service): ?>
        <button onclick="showAddForm()" class="btn btn-primary">
            <i class="fas fa-plus"></i> Add New Service
        </button>
        <?php endif; ?>
    </div>
    
    <?php if ($success): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i> <?php echo $success; ?>
        </div>
    <?php endif; ?>
    
    <?php if ($error): ?>
        <div class="alert alert-error">
            <i class="fas fa-exclamation-triangle"></i> <?php echo $error; ?>
        </div>
    <?php endif; ?>
    
    <!-- Statistics Cards -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon" style="background: #3b82f6;">
                <i class="fas fa-concierge-bell"></i>
            </div>
            <div class="stat-info">
                <h3><?php echo $stats['total']; ?></h3>
                <p>Total Services</p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon" style="background: #10b981;">
                <i class="fas fa-home"></i>
            </div>
            <div class="stat-info">
                <h3><?php echo $stats['cottages']; ?></h3>
                <p>Cottages</p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon" style="background: #8b5cf6;">
                <i class="fas fa-bed"></i>
            </div>
            <div class="stat-info">
                <h3><?php echo $stats['rooms']; ?></h3>
                <p>Rooms</p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon" style="background: #f59e0b;">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="stat-info">
                <h3><?php echo $stats['available']; ?></h3>
                <p>Available</p>
            </div>
        </div>
    </div>
    
    <!-- Add/Edit Service Form -->
    <div class="data-table" id="serviceForm" style="<?php echo $edit_service ? '' : 'display: none;'; ?> margin-bottom: 30px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h2><?php echo $edit_service ? 'Edit Service' : 'Add New Service'; ?></h2>
            <?php if ($edit_service): ?>
                <a href="services.php" class="btn btn-secondary">Cancel Edit</a>
            <?php else: ?>
                <button onclick="hideAddForm()" class="btn btn-secondary">Cancel</button>
            <?php endif; ?>
        </div>
        
        <form method="POST" action="">
            <?php if ($edit_service): ?>
                <input type="hidden" name="service_id" value="<?php echo $edit_service['id']; ?>">
            <?php endif; ?>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div class="form-group">
                    <label for="service_name">Service Name *</label>
                    <input type="text" id="service_name" name="service_name" required 
                           placeholder="e.g., Casa Ernesto - Day Tour"
                           value="<?php echo $edit_service ? htmlspecialchars($edit_service['service_name']) : ''; ?>">
                </div>
                
                <div class="form-group">
                    <label for="category">Category *</label>
                    <select id="category" name="category" required>
                        <option value="">Select Category</option>
                        <option value="cottage" <?php echo ($edit_service && $edit_service['category'] === 'cottage') ? 'selected' : ''; ?>>Cottage</option>
                        <option value="room" <?php echo ($edit_service && $edit_service['category'] === 'room') ? 'selected' : ''; ?>>Room</option>
                        <option value="cuarto" <?php echo ($edit_service && $edit_service['category'] === 'cuarto') ? 'selected' : ''; ?>>Cuarto</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="subcategory">Subcategory/Option *</label>
                    <input type="text" id="subcategory" name="subcategory" required 
                           placeholder="e.g., day-tour, public, semi-private"
                           value="<?php echo $edit_service ? htmlspecialchars($edit_service['subcategory']) : ''; ?>">
                    <small>Examples: public, semi-private, day-tour, night-tour, overnight, 6-hours, 12-hours, 24-hours</small>
                </div>
                
                <div class="form-group">
                    <label for="price">Price (₱) *</label>
                    <input type="number" id="price" name="price" step="0.01" min="0" required
                           placeholder="0.00"
                           value="<?php echo $edit_service ? $edit_service['price'] : ''; ?>">
                </div>
                
                <div class="form-group">
                    <label for="capacity">Capacity (Pax) *</label>
                    <input type="number" id="capacity" name="capacity" min="1" required
                           placeholder="Number of guests"
                           value="<?php echo $edit_service ? $edit_service['capacity'] : ''; ?>">
                </div>
                
                <div class="form-group">
                    <label for="availability">Availability *</label>
                    <select id="availability" name="availability" required>
                        <option value="available" <?php echo ($edit_service && $edit_service['availability'] === 'available') ? 'selected' : ''; ?>>Available</option>
                        <option value="unavailable" <?php echo ($edit_service && $edit_service['availability'] === 'unavailable') ? 'selected' : ''; ?>>Unavailable</option>
                    </select>
                </div>
            </div>
            
            <div class="form-group">
                <label for="description">Description</label>
                <textarea id="description" name="description" rows="2" 
                          placeholder="Brief description of the service"><?php echo $edit_service ? htmlspecialchars($edit_service['description']) : ''; ?></textarea>
            </div>
            
            <div class="form-group">
                <label for="amenities">Amenities</label>
                <textarea id="amenities" name="amenities" rows="3" 
                          placeholder="Comma-separated amenities (e.g., Airconditioned Room, Double Deck Beds, Dining Area)"><?php echo $edit_service ? htmlspecialchars($edit_service['amenities']) : ''; ?></textarea>
                <small>Separate amenities with commas</small>
            </div>
            
            <div style="display: flex; gap: 10px; margin-top: 20px;">
                <button type="submit" name="save_service" class="btn btn-primary">
                    <i class="fas fa-save"></i> <?php echo $edit_service ? 'Update Service' : 'Add Service'; ?>
                </button>
            </div>
        </form>
    </div>
    
    <!-- Filters -->
    <div class="filter-card">
        <form method="GET" action="">
            <div class="filter-grid">
                <div class="form-group">
                    <label for="category">
                        <i class="fas fa-filter"></i> Category
                    </label>
                    <select id="category" name="category">
                        <option value="all" <?php echo $category_filter === 'all' ? 'selected' : ''; ?>>All Categories</option>
                        <option value="cottage" <?php echo $category_filter === 'cottage' ? 'selected' : ''; ?>>Cottages</option>
                        <option value="room" <?php echo $category_filter === 'room' ? 'selected' : ''; ?>>Rooms</option>
                        <option value="cuarto" <?php echo $category_filter === 'cuarto' ? 'selected' : ''; ?>>Cuartos</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="availability">
                        <i class="fas fa-toggle-on"></i> Availability
                    </label>
                    <select id="availability" name="availability">
                        <option value="all" <?php echo $availability_filter === 'all' ? 'selected' : ''; ?>>All</option>
                        <option value="available" <?php echo $availability_filter === 'available' ? 'selected' : ''; ?>>Available</option>
                        <option value="unavailable" <?php echo $availability_filter === 'unavailable' ? 'selected' : ''; ?>>Unavailable</option>
                    </select>
                </div>
                
                <div class="form-group" style="display: flex; align-items: flex-end; gap: 10px;">
                    <button type="submit" class="btn btn-primary" style="flex: 1;">
                        Apply Filters
                    </button>
                    <a href="services.php" class="btn btn-secondary">
                        <i class="fas fa-redo"></i>
                    </a>
                </div>
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
                    <th>Option</th>
                    <th>Price</th>
                    <th>Capacity</th>
                    <th>Availability</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($services && $services->num_rows > 0): ?>
                    <?php while ($service = $services->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $service['id']; ?></td>
                            <td>
                                <strong><?php echo htmlspecialchars($service['service_name']); ?></strong>
                                <?php if (!empty($service['description'])): ?>
                                    <br><small style="color: #666;"><?php echo htmlspecialchars($service['description']); ?></small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge" style="background: <?php 
                                    echo $service['category'] === 'cottage' ? '#10b981' : 
                                        ($service['category'] === 'room' ? '#8b5cf6' : '#f59e0b'); 
                                ?>; color: white;">
                                    <?php echo ucfirst($service['category']); ?>
                                </span>
                            </td>
                            <td><?php echo htmlspecialchars($service['subcategory']); ?></td>
                            <td><strong style="color: #2c5f2d;">₱<?php echo number_format($service['price'], 2); ?></strong></td>
                            <td><i class="fas fa-users"></i> <?php echo $service['capacity']; ?> pax</td>
                            <td>
                                <span class="badge <?php echo $service['availability'] === 'available' ? 'badge-success' : 'badge-danger'; ?>">
                                    <?php echo ucfirst($service['availability']); ?>
                                </span>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <a href="?edit=<?php echo $service['id']; ?>" class="btn btn-sm btn-primary" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="?delete=<?php echo $service['id']; ?>" 
                                       onclick="return confirm('Are you sure you want to delete this service?')" 
                                       class="btn btn-sm btn-danger" title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8" style="text-align: center; padding: 40px; color: #999;">
                            <i class="fas fa-inbox" style="font-size: 3rem; margin-bottom: 15px; display: block;"></i>
                            No services found
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
function showAddForm() {
    document.getElementById('serviceForm').style.display = 'block';
    document.getElementById('serviceForm').scrollIntoView({ behavior: 'smooth' });
}

function hideAddForm() {
    document.getElementById('serviceForm').style.display = 'none';
}
</script>

<style>
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.stat-card {
    background: white;
    padding: 25px;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    display: flex;
    align-items: center;
    gap: 20px;
}

.stat-icon {
    width: 60px;
    height: 60px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    color: white;
}

.stat-info h3 {
    margin: 0;
    font-size: 2rem;
    color: #111827;
}

.stat-info p {
    margin: 5px 0 0;
    color: #6b7280;
}

.filter-card {
    background: white;
    padding: 25px;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    margin-bottom: 30px;
}

.filter-grid {
    display: grid;
    grid-template-columns: 1fr 1fr auto;
    gap: 20px;
    align-items: end;
}

.action-buttons {
    display: flex;
    gap: 8px;
    justify-content: center;
}

.badge {
    padding: 6px 14px;
    border-radius: 20px;
    font-size: 0.85rem;
    font-weight: 600;
}

.badge-success { background: #10b981; color: white; }
.badge-danger { background: #ef4444; color: white; }

.alert {
    padding: 15px 20px;
    margin-bottom: 25px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.alert-success {
    background: #d1fae5;
    color: #065f46;
    border: 1px solid #10b981;
}

.alert-error {
    background: #fee2e2;
    color: #991b1b;
    border: 1px solid #ef4444;
}

@media (max-width: 768px) {
    .filter-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<?php include 'includes/footer.php'; ?>