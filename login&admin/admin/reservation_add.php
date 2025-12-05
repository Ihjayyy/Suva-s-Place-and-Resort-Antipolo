<?php
// admin/reservation_add.php
require_once '../config/database.php';

if (!is_logged_in() || !is_admin()) {
    redirect('../login.php');
}

$errors = [];
$success = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullName = sanitize_input($_POST['full_name'] ?? '');
    $contactNumber = sanitize_input($_POST['contact_number'] ?? '');
    $address = sanitize_input($_POST['address'] ?? '');
    $bookingDate = sanitize_input($_POST['booking_date'] ?? '');
    $numAdults = intval($_POST['num_adults'] ?? 0);
    $numKids = intval($_POST['num_kids'] ?? 0);
    $room = sanitize_input($_POST['room'] ?? '');
    $swimmingType = sanitize_input($_POST['swimming_type'] ?? '');
    $cottage = sanitize_input($_POST['cottage'] ?? '');
    $others = sanitize_input($_POST['others'] ?? '');
    $shift = sanitize_input($_POST['shift'] ?? '');
    $status = sanitize_input($_POST['status'] ?? 'pending');

    // Validation
    if (empty($fullName)) $errors[] = "Full Name is required";
    if (empty($contactNumber)) $errors[] = "Contact Number is required";
    if (empty($address)) $errors[] = "Address is required";
    if (empty($bookingDate)) $errors[] = "Booking Date is required";
    if ($numAdults < 1) $errors[] = "At least 1 adult is required";
    if (empty($shift)) $errors[] = "Shift selection is required";

    // If no errors, insert the booking
    if (empty($errors)) {
        // Generate a unique booking ID
        $bookingId = 'BK-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));

        $stmt = $conn->prepare("INSERT INTO reservations (booking_id, full_name, contact_number, address, booking_date, num_adults, num_kids, room, swimming_type, cottage, shift, others, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        
        $stmt->bind_param("sssssiiссssss", 
            $bookingId, 
            $fullName, 
            $contactNumber, 
            $address, 
            $bookingDate, 
            $numAdults, 
            $numKids, 
            $room, 
            $swimmingType, 
            $cottage, 
            $shift, 
            $others,
            $status
        );

        if ($stmt->execute()) {
            $success = "Reservation created successfully! Booking ID: $bookingId";
            log_activity($_SESSION['user_id'], 'Create Reservation', "Created new reservation $bookingId");
            
            // Clear form
            $_POST = [];
        } else {
            $errors[] = "Failed to create reservation. Error: " . $stmt->error;
        }

        $stmt->close();
    }
}

$page_title = 'Add New Reservation';
include 'includes/header.php';
?>

<div class="content-container">
    <div style="margin-bottom: 30px;">
        <a href="reservations.php" class="btn btn-secondary">← Back to Reservations</a>
    </div>

    <div class="form-container">
        <h1>Add New Reservation</h1>

        <?php if ($success): ?>
            <div class="alert alert-success">
                <?php echo $success; ?>
                <a href="reservations.php" style="margin-left: 15px;">View All Reservations</a>
            </div>
        <?php endif; ?>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-error">
                <h3>Please fix the following errors:</h3>
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo htmlspecialchars($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="POST" action="" class="edit-form">
            <!-- Guest Information -->
            <div class="form-section">
                <h2>Guest Information</h2>
                
                <div class="form-group">
                    <label for="full_name">Full Name <span class="required">*</span></label>
                    <input type="text" id="full_name" name="full_name" 
                           value="<?php echo htmlspecialchars($_POST['full_name'] ?? ''); ?>" required>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="contact_number">Contact Number <span class="required">*</span></label>
                        <input type="tel" id="contact_number" name="contact_number" 
                               value="<?php echo htmlspecialchars($_POST['contact_number'] ?? ''); ?>" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="address">Address <span class="required">*</span></label>
                    <textarea id="address" name="address" rows="3" required><?php echo htmlspecialchars($_POST['address'] ?? ''); ?></textarea>
                </div>
            </div>

            <!-- Booking Details -->
            <div class="form-section">
                <h2>Booking Details</h2>

                <div class="form-row">
                    <div class="form-group">
                        <label for="booking_date">Booking Date <span class="required">*</span></label>
                        <input type="date" id="booking_date" name="booking_date" 
                               value="<?php echo htmlspecialchars($_POST['booking_date'] ?? ''); ?>" 
                               min="<?php echo date('Y-m-d'); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="shift">Shift <span class="required">*</span></label>
                        <select id="shift" name="shift" required>
                            <option value="">Select Shift</option>
                            <option value="day" <?php echo ($_POST['shift'] ?? '') === 'day' ? 'selected' : ''; ?>>Day Swimming (8:00 AM - 4:30 PM)</option>
                            <option value="night" <?php echo ($_POST['shift'] ?? '') === 'night' ? 'selected' : ''; ?>>Night Tour (8:00 PM - 4:30 AM)</option>
                            <option value="whole_day" <?php echo ($_POST['shift'] ?? '') === 'whole_day' ? 'selected' : ''; ?>>Overnight Stay (2:00 PM - 11:00 AM)</option>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="num_adults">No. of Adults <span class="required">*</span></label>
                        <input type="number" id="num_adults" name="num_adults" 
                               value="<?php echo htmlspecialchars($_POST['num_adults'] ?? '1'); ?>" 
                               min="1" max="50" required>
                    </div>

                    <div class="form-group">
                        <label for="num_kids">No. of Kids</label>
                        <input type="number" id="num_kids" name="num_kids" 
                               value="<?php echo htmlspecialchars($_POST['num_kids'] ?? '0'); ?>" 
                               min="0" max="50">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="room">Room</label>
                        <select id="room" name="room">
                            <option value="">No Room</option>
                            <option value="standard" <?php echo ($_POST['room'] ?? '') === 'standard' ? 'selected' : ''; ?>>Standard Room</option>
                            <option value="deluxe" <?php echo ($_POST['room'] ?? '') === 'deluxe' ? 'selected' : ''; ?>>Deluxe Room</option>
                            <option value="family" <?php echo ($_POST['room'] ?? '') === 'family' ? 'selected' : ''; ?>>Family Room</option>
                            <option value="suite" <?php echo ($_POST['room'] ?? '') === 'suite' ? 'selected' : ''; ?>>Suite</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="swimming_type">Swimming Type</label>
                        <select id="swimming_type" name="swimming_type">
                            <option value="">No Swimming</option>
                            <option value="adult_pool" <?php echo ($_POST['swimming_type'] ?? '') === 'adult_pool' ? 'selected' : ''; ?>>Adult Pool</option>
                            <option value="kids_pool" <?php echo ($_POST['swimming_type'] ?? '') === 'kids_pool' ? 'selected' : ''; ?>>Kids Pool</option>
                            <option value="infinity_pool" <?php echo ($_POST['swimming_type'] ?? '') === 'infinity_pool' ? 'selected' : ''; ?>>Infinity Pool</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label for="cottage">Cottage</label>
                    <select id="cottage" name="cottage">
                        <option value="">No Cottage</option>
                        <option value="small" <?php echo ($_POST['cottage'] ?? '') === 'small' ? 'selected' : ''; ?>>Umbrella Cottage (4-5 pax)</option>
                        <option value="medium" <?php echo ($_POST['cottage'] ?? '') === 'medium' ? 'selected' : ''; ?>>Family Cottage (10-15 pax)</option>
                        <option value="large" <?php echo ($_POST['cottage'] ?? '') === 'large' ? 'selected' : ''; ?>>Barkada Cottage (20-30 pax)</option>
                        <option value="pavilion" <?php echo ($_POST['cottage'] ?? '') === 'pavilion' ? 'selected' : ''; ?>>Silong (30-40 pax)</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="status">Status <span class="required">*</span></label>
                    <select id="status" name="status" required>
                        <option value="pending" <?php echo ($_POST['status'] ?? 'pending') === 'pending' ? 'selected' : ''; ?>>Pending</option>
                        <option value="confirmed" <?php echo ($_POST['status'] ?? '') === 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                        <option value="cancelled" <?php echo ($_POST['status'] ?? '') === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                        <option value="completed" <?php echo ($_POST['status'] ?? '') === 'completed' ? 'selected' : ''; ?>>Completed</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="others">Special Requests</label>
                    <textarea id="others" name="others" rows="3"><?php echo htmlspecialchars($_POST['others'] ?? ''); ?></textarea>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Create Reservation</button>
                <button type="reset" class="btn btn-secondary">Clear Form</button>
            </div>
        </form>
    </div>
</div>

<style>
.form-container {
    background: white;
    padding: 30px;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.form-container h1 {
    margin-bottom: 30px;
    color: #111827;
}

.form-section {
    margin-bottom: 30px;
    padding: 20px;
    background: #f9fafb;
    border-radius: 8px;
}

.form-section h2 {
    margin-top: 0;
    margin-bottom: 20px;
    font-size: 18px;
    color: #374151;
    border-bottom: 2px solid #e5e7eb;
    padding-bottom: 10px;
}

.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    margin-bottom: 8px;
    font-weight: 600;
    color: #374151;
}

.form-group input,
.form-group select,
.form-group textarea {
    width: 100%;
    padding: 10px;
    border: 1px solid #d1d5db;
    border-radius: 6px;
    font-size: 14px;
}

.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus {
    outline: none;
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.form-row {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
}

.form-actions {
    display: flex;
    gap: 15px;
    padding-top: 20px;
    border-top: 2px solid #e5e7eb;
}

.required {
    color: #ef4444;
}

.alert {
    padding: 15px;
    margin-bottom: 20px;
    border-radius: 6px;
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

.alert h3 {
    margin-top: 0;
    margin-bottom: 10px;
}

.alert ul {
    margin: 0;
    padding-left: 20px;
}
</style>

<?php include 'includes/footer.php'; ?>