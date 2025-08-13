<?php
header('Content-Type: application/json');

$keyId = 'rzp_live_gEUiOyc32j0gf5';
$keySecret = 'bTInoL9nyanBGz7vphPkOBUr';

$data = json_decode(file_get_contents('php://input'), true);
$amount = isset($data['amount']) ? intval($data['amount']) * 100 : 0;

if ($amount < 10100) { // ₹101 minimum
    echo json_encode(['error' => 'Minimum amount is ₹101']);
    exit;
}

$postData = [
    'amount' => $amount,
    'currency' => 'INR',
    'receipt' => 'donation_rcptid_' . time(),
    'payment_capture' => 1
];

$ch = curl_init('https://api.razorpay.com/v1/orders');
curl_setopt($ch, CURLOPT_USERPWD, $keyId . ':' . $keySecret);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

$response = curl_exec($ch);
$http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);

if ($response === false) {
    echo json_encode(['error' => 'Curl error: ' . curl_error($ch)]);
    curl_close($ch);
    exit;
}

curl_close($ch);

if ($http_status !== 200) {
    echo json_encode(['error' => 'Razorpay API error: ' . $response]);
    exit;
}

$order = json_decode($response, true);
echo json_encode([
    'id' => $order['id'],
    'amount' => $order['amount']
]);
?> 