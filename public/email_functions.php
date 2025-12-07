<?php
// email_functions.php

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
            .detail-row { display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid #eee; }
            .detail-label { font-weight: bold; color: #666; }
            .total { font-size: 24px; color: #2c5f2d; font-weight: bold; text-align: right; margin-top: 20px; }
            .policy { background: #fff3cd; padding: 15px; border-left: 4px solid #ffc107; margin-top: 20px; }
            .footer { text-align: center; margin-top: 30px; color: #666; font-size: 12px; }
            ul { list-style: none; padding: 0; }
            ul li { padding: 8px 0; border-bottom: 1px solid #eee; }
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
                    <div class='detail-row'>
                        <span class='detail-label'>Booking ID:</span>
                        <span><strong>$bookingId</strong></span>
                    </div>
                    <div class='detail-row'>
                        <span class='detail-label'>Check-in:</span>
                        <span>$checkInFormatted</span>
                    </div>
                    <div class='detail-row'>
                        <span class='detail-label'>Check-out:</span>
                        <span>$checkOutFormatted</span>
                    </div>
                    
                    <h3 style='margin-top: 20px;'>Selected Accommodations:</h3>
                    <ul>
                        $itemsList
                    </ul>
                    
                    <div class='total'>
                        Total: ₱" . number_format($totalAmount, 2) . "
                    </div>
                </div>
                
                <div class='policy'>
                    <h3>Booking Policy</h3>
                    <ol>
                        <li>Reschedule must be made 7 days before the date of reservation.</li>
                        <li>In an event of \"No Show\" Initial Deposit is no longer refundable.</li>
                        <li>Initial Deposit is non-refundable unless accidents and god acts occurs.</li>
                        <li>Once it settles, payments are Non-refundable.</li>
                    </ol>
                </div>
                
                <p style='margin-top: 20px;'>Please present this booking ID upon arrival. If you have any questions, feel free to contact us.</p>
                
                <p>We look forward to welcoming you!</p>
            </div>
            
            <div class='footer'>
                <p>Suva's Place and Resort Antipolo<br>
                Contact: [Your Contact Number]<br>
                Email: [Your Email]</p>
            </div>
        </div>
    </body>
    </html>
    ";
    
    // Headers
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= "From: Suva's Place Resort <noreply@suvasplace.com>" . "\r\n";
    
    // Send email
    return mail($to, $subject, $message, $headers);
}

function sendBookingUpdateEmail($to, $fullName, $bookingId, $status, $remarks = '') {
    $subject = "Booking Update - Suva's Place and Resort";
    
    $statusMessages = [
        'confirmed' => 'Your booking has been confirmed!',
        'cancelled' => 'Your booking has been cancelled.',
        'completed' => 'Thank you for staying with us!'
    ];
    
    $message = "
    <!DOCTYPE html>
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: #2c5f2d; color: white; padding: 20px; text-align: center; }
            .content { background: #f9f9f9; padding: 30px; border-radius: 8px; margin-top: 20px; }
            .status-badge { display: inline-block; padding: 10px 20px; border-radius: 5px; font-weight: bold; margin: 20px 0; }
            .status-confirmed { background: #d1fae5; color: #065f46; }
            .status-cancelled { background: #fee2e2; color: #991b1b; }
            .status-completed { background: #dbeafe; color: #1e40af; }
            .footer { text-align: center; margin-top: 30px; color: #666; font-size: 12px; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>Booking Update</h1>
                <p>Suva's Place and Resort Antipolo</p>
            </div>
            
            <div class='content'>
                <p>Dear <strong>$fullName</strong>,</p>
                <p>Your booking <strong>$bookingId</strong> has been updated.</p>
                
                <div class='status-badge status-$status'>
                    Status: " . strtoupper($status) . "
                </div>
                
                <p>" . ($statusMessages[$status] ?? '') . "</p>
                
                " . ($remarks ? "<p><strong>Remarks:</strong> $remarks</p>" : '') . "
                
                <p>If you have any questions, please don't hesitate to contact us.</p>
            </div>
            
            <div class='footer'>
                <p>Suva's Place and Resort Antipolo<br>
                Contact: [Your Contact Number]<br>
                Email: [Your Email]</p>
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