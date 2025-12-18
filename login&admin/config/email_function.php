<?php
// email_functions.php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/../../vendor/autoload.php';

function sendBookingConfirmationEmail($to, $fullName, $bookingId, $items, $totalAmount, $checkIn, $checkOut) {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'emailtestingsuvasresortandplac@gmail.com';
        $mail->Password   = 'rnie iywg mmma pvlv';
        $mail->SMTPSecure = 'tls';
        $mail->Port       = 587;

        $mail->setFrom('noreply@suvasplace.com', "Suva's Place Resort");
        $mail->addAddress($to, $fullName);
        $mail->isHTML(true);
        $mail->Subject = "Booking Confirmation - Suva's Place and Resort";

        // Format items
        $itemsList = '';
        foreach ($items as $item) {
            $itemsList .= "<li>{$item['name']} - {$item['option']}: ₱" . number_format($item['price'], 2) . "</li>";
        }

        $checkInFormatted = date('F d, Y h:i A', strtotime($checkIn));
        $checkOutFormatted = date('F d, Y h:i A', strtotime($checkOut));

        $mail->Body = "
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
                    <p>Thank you for choosing Suva's Place and Resort!</p>
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

        $mail->send();
    } catch (Exception $e) {
        error_log("Booking confirmation email could not be sent. Mailer Error: {$mail->ErrorInfo}");
    }
}

function sendStatusUpdateEmail($to, $fullName, $bookingId, $newStatus) {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'emailtestingsuvasresortandplac@gmail.com';
        $mail->Password   = 'rnie iywg mmma pvlv';
        $mail->SMTPSecure = 'tls';
        $mail->Port       = 587;

        $mail->setFrom('noreply@suvasplace.com', "Suva's Place Resort");
        $mail->addAddress($to, $fullName);
        $mail->isHTML(true);
        $mail->Subject = "Booking Status Update - Suva's Place and Resort";

        $mail->Body = "
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: #2c5f2d; color: white; padding: 20px; text-align: center; }
                .content { background: #f9f9f9; padding: 30px; border-radius: 8px; margin-top: 20px; }
                .status { font-size: 20px; font-weight: bold; color: #2c5f2d; margin-top: 20px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>Booking Status Update</h1>
                    <p>Suva's Place and Resort Antipolo</p>
                </div>
                <div class='content'>
                    <p>Dear <strong>$fullName</strong>,</p>
                    <p>Your booking with ID <strong>$bookingId</strong> has been updated by our admin.</p>
                    <p class='status'>New Status: $newStatus</p>
                    <p>If you have any questions, please contact us.</p>
                </div>
            </div>
        </body>
        </html>
        ";

        $mail->send();
    } catch (Exception $e) {
        error_log("Booking status email could not be sent. Mailer Error: {$mail->ErrorInfo}");
    }
}
