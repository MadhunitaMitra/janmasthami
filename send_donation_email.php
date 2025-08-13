<?php
// Usage: include this file and call sendDonationEmail($donorData);

function sendDonationEmail($donorData) {
    $to = "thebishalghosh@gmail.com";
    $subject = "New Donation Received";
    $message = "A new donation has been received:\n\n";
    $message .= "Name: " . $donorData['name'] . "\n";
    $message .= "Email: " . $donorData['email'] . "\n";
    $message .= "Phone: " . $donorData['phone'] . "\n";
    $message .= "Amount: ₹" . $donorData['amount'] . "\n";
    $message .= "Seva: " . $donorData['seva'] . "\n";
    $message .= "80G Certificate: " . ($donorData['80g'] ? "Requested" : "Not Requested") . "\n";
    $headers = "From: donations@iskconnewtown.org\r\n";

    mail($to, $subject, $message, $headers);
}
?>