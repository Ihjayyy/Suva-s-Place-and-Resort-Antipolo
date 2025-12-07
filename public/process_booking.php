<?php
// process_booking.php
require_once '../login&admin/config/database.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 0); // Don't display errors in output
ini_set('log_errors', 1);

header('Content-Type: application/json');

// Check if user is logged in
if (!is_logged_in()) {
    echo json_encode(['success' => false, 'message' => 'Please login to make a booking']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$errors = [];

try {
    $conn->begin_transaction();

    // Sanitize inputs
    $fullName = sanitize_input($_POST['fullName'] ?? '');
    $email = sanitize_input($_POST['email'] ?? '');
    $contactNumber = sanitize_input($_POST['contactNumber'] ?? '');
    $address = sanitize_input($_POST['address'] ?? '');
    $checkIn = sanitize_input($_POST['checkIn'] ?? '');
    $checkOut = sanitize_input($_POST['checkOut'] ?? '');
    $guestCount = intval($_POST['guestCount'] ?? 0);
    $numKids = intval($_POST['numKids'] ?? 0);
    $specialRequests = sanitize_input($_POST['additionalRequests'] ?? '');
    $notes = sanitize_input($_POST['notes'] ?? '');
    $paymentMethod = sanitize_input($_POST['paymentMethod'] ?? 'onsite');
    $selectedItems = json_decode($_POST['selectedItems'] ?? '[]', true);
    $totalAmount = floatval($_POST['totalAmount'] ?? 0);
    
    // Validation
    if (empty($fullName)) $errors[] = "Full name is required";
    if (empty($email)) $errors[] = "Email is required";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Invalid email format";
    if (empty($contactNumber)) $errors[] = "Contact number is required";
    if (empty($address)) $errors[] = "Address is required";
    if (empty($checkIn)) $errors[] = "Check-in date is required";
    if (empty($checkOut)) $errors[] = "Check-out date is required";
    if ($guestCount < 1) $errors[] = "At least 1 guest is required";
    if (empty($selectedItems)) $errors[] = "Please select at least one accommodation";
    
    // Validate dates
    $checkInDate = new DateTime($checkIn);
    $checkOutDate = new DateTime($checkOut);
    $today = new DateTime();
    $today->setTime(0, 0, 0);
    
    if ($checkInDate < $today) {
        $errors[] = "Check-in date cannot be in the past";
    }
    
    if ($checkOutDate <= $checkInDate) {
        $errors[] = "Check-out date must be after check-in date";
    }
    
    if (!empty($errors)) {
        throw new Exception(implode(', ', $errors));
    }
    
    // Generate unique booking ID
    $bookingId = 'BK-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
    
    // Determine shift based on selected items
    $shift = 'day'; // default
    foreach ($selectedItems as $item) {
        if (isset($item['option'])) {
            $optionLower = strtolower($item['option']);
            if (strpos($optionLower, 'night') !== false) {
                $shift = 'night';
            } elseif (strpos($optionLower, 'overnight') !== false) {
                $shift = 'overnight';
            }
        }
    }
    
    // Handle file upload for proof of payment
    $proofOfPayment = null;
    if ($paymentMethod === 'online' && isset($_FILES['proofOfPayment']) && $_FILES['proofOfPayment']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = '../uploads/proof_of_payment/';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        
        $fileExtension = pathinfo($_FILES['proofOfPayment']['name'], PATHINFO_EXTENSION);
        $fileName = $bookingId . '_' . time() . '.' . $fileExtension;
        $filePath = $uploadDir . $fileName;
        
        if (move_uploaded_file($_FILES['proofOfPayment']['tmp_name'], $filePath)) {
            $proofOfPayment = 'uploads/proof_of_payment/' . $fileName;
        }
    }
    
    // Combine special requests and notes
    $combinedNotes = '';
    if (!empty($specialRequests)) {
        $combinedNotes .= "Special Request: " . $specialRequests . "\n";
    }
    if (!empty($notes)) {
        $combinedNotes .= $notes;
    }
    
    // Insert reservation
    $stmt = $conn->prepare("INSERT INTO reservations (
        booking_id, user_id, full_name, email, phone, address, 
        booking_date, check_in, check_out, num_adults, num_kids, 
        shift, total_amount, payment_method, payment_status, 
        proof_of_payment, notes, status
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    $bookingDate = $checkInDate->format('Y-m-d');
    $paymentStatus = ($paymentMethod === 'online') ? 'pending' : 'pending';
    $status = 'pending';
    $userId = $_SESSION['user_id'];
    
    $stmt->bind_param("sisssssssiisdsssss",
        $bookingId,
        $userId,
        $fullName,
        $email,
        $contactNumber,
        $address,
        $bookingDate,
        $checkIn,
        $checkOut,
        $guestCount,
        $numKids,
        $shift,
        $totalAmount,
        $paymentMethod,
        $paymentStatus,
        $proofOfPayment,
        $combinedNotes,
        $status
    );
    
    if (!$stmt->execute()) {
        throw new Exception("Failed to create reservation: " . $stmt->error);
    }
    
    $reservationId = $conn->insert_id;
    
    // Insert reservation items
    $itemStmt = $conn->prepare("INSERT INTO reservation_items (
        reservation_id, service_id, service_name, service_option, quantity, price, subtotal
    ) VALUES (?, ?, ?, ?, ?, ?, ?)");
    
    foreach ($selectedItems as $item) {
        $serviceId = intval($item['serviceId'] ?? 0);
        $serviceName = $item['name'] ?? '';
        $serviceOption = $item['option'] ?? '';
        $quantity = 1;
        $price = floatval($item['price'] ?? 0);
        $subtotal = $price * $quantity;
        
        $itemStmt->bind_param("iissidd",
            $reservationId,
            $serviceId,
            $serviceName,
            $serviceOption,
            $quantity,
            $price,
            $subtotal
        );
        
        if (!$itemStmt->execute()) {
            throw new Exception("Failed to add reservation item: " . $itemStmt->error);
        }
        
        // Update service availability
        updateServiceAvailability($conn, $serviceId, $bookingDate, 1);
    }
    
    // Commit transaction
    $conn->commit();
    
    // Send confirmation email (optional - comment out if email not configured)
    // sendBookingConfirmationEmail($email, $fullName, $bookingId, $selectedItems, $totalAmount, $checkIn, $checkOut);
    
    // Log activity
    if (function_exists('log_activity')) {
        log_activity($_SESSION['user_id'], 'Create Booking', "Created booking $bookingId");
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Booking confirmed successfully!',
        'booking_id' => $bookingId,
        'redirect' => 'booking_confirmation.php?id=' . $bookingId
    ]);
    
} catch (Exception $e) {
    if (isset($conn)) {
        $conn->rollback();
    }
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

function updateServiceAvailability($conn, $serviceId, $bookingDate, $quantity) {
    // Check if availability record exists
    $stmt = $conn->prepare("SELECT id, booked_quantity FROM service_availability WHERE service_id = ? AND booking_date = ?");
    $stmt->bind_param("is", $serviceId, $bookingDate);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        // Update existing record
        $row = $result->fetch_assoc();
        $newBooked = $row['booked_quantity'] + $quantity;
        $updateStmt = $conn->prepare("UPDATE service_availability SET booked_quantity = ? WHERE id = ?");
        $updateStmt->bind_param("ii", $newBooked, $row['id']);
        $updateStmt->execute();
    } else {
        // Insert new record
        $insertStmt = $conn->prepare("INSERT INTO service_availability (service_id, booking_date, available_quantity, booked_quantity) VALUES (?, ?, 1, ?)");
        $insertStmt->bind_param("isi", $serviceId, $bookingDate, $quantity);
        $insertStmt->execute();
    }
}

function sendBookingConfirmationEmail($to, $fullName, $bookingId, $items, $totalAmount, $checkIn, $checkOut) {
    $subject = "Booking Confirmation - Suva's Place and Resort";
    
    // Format items list
    $itemsList = '';
    foreach ($items as $item) {
        $itemsList .= "<li>{$item['name']} - {$item['option']}: ₱" . number_format($item['price'], 2) . "</li>";
    }
    
    $checkInFormatted = date('F d, Y h:i A', strtotime($checkIn));
    $checkOutFormatted = date('F d, Y h:i A', strtotime($checkOut));
    
    $message = "
    <!DOCTYPE html>
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: #2c5f2d; color: white; padding: 20px; text-align: center; }
            .content { background: #f9f9f9; padding: 30px; border-radius: 8px; margin-top: 20px; }
            .booking-details { background: white; padding: 20px; border-radius: 5px; margin: 20px 0; }
            .total { font-size: 24px; color: #2c5f2d; font-weight: bold; text-align: right; margin-top: 20px; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>Booking Confirmation</h1>
                <p>Suva's Place and Resort Antipolo</p>
            </div>
            
            <div class='content'>
                <p>Dear <strong>$fullName</strong>,</p>
                <p>Thank you for choosing Suva's Place and Resort! Your booking has been confirmed.</p>
                
                <div class='booking-details'>
                    <h2>Booking Details</h2>
                    <p><strong>Booking ID:</strong> $bookingId</p>
                    <p><strong>Check-in:</strong> $checkInFormatted</p>
                    <p><strong>Check-out:</strong> $checkOutFormatted</p>
                    
                    <h3>Selected Accommodations:</h3>
                    <ul>$itemsList</ul>
                    
                    <div class='total'>Total: ₱" . number_format($totalAmount, 2) . "</div>
                </div>
            </div>
        </div>
    </body>
    </html>
    ";
    
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= "From: Suva's Place Resort <noreply@suvasplace.com>" . "\r\n";
    
    return mail($to, $subject, $message, $headers);
}
?>