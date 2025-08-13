<?php
// Set header for JSON response
header('Content-Type: application/json');

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 0);

// CSV file name
$filename = 'donations.csv';

// Razorpay secret key (use the same as in create_order.php)
$keySecret = 'HDZLa4jDy7Qopr1deWWB3OjD';

// Ensure it's a POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get raw POST data if sent as JSON
    $rawData = file_get_contents('php://input');
    $data = json_decode($rawData, true);

    // Fallback if not JSON (standard form submission)
    if (!$data) {
        $data = $_POST;
    }

    // Razorpay payment verification
    $razorpay_payment_id = $data['razorpay_payment_id'] ?? '';
    $razorpay_order_id = $data['razorpay_order_id'] ?? '';
    $razorpay_signature = $data['razorpay_signature'] ?? '';

    if ($razorpay_payment_id && $razorpay_order_id && $razorpay_signature) {
        $generated_signature = hash_hmac('sha256', $razorpay_order_id . '|' . $razorpay_payment_id, $keySecret);
        if ($generated_signature !== $razorpay_signature) {
            echo json_encode(['success' => false, 'message' => 'Payment verification failed.']);
            exit;
        }
        // else: signature is valid, continue to save donation
    }

    // Extract and sanitize fields from frontend
    $donor_name    = htmlspecialchars($data['donor_name'] ?? '');
    $donor_email   = htmlspecialchars($data['donor_email'] ?? '');
    $donor_phone   = htmlspecialchars($data['donor_phone'] ?? '');
    $donor_dob     = htmlspecialchars($data['donor_dob'] ?? '');
    $donor_address = htmlspecialchars($data['donor_address'] ?? '');
    $receive_prasadam = isset($data['receive_prasadam']) ? 'Yes' : 'No';
    $want_80g      = isset($data['want_80g']) ? 'Yes' : 'No';
    $donor_pan     = htmlspecialchars($data['donor_pan'] ?? '');
    $total_amount  = htmlspecialchars($data['total_amount'] ?? '');
    // Razorpay fields already extracted above

    // Seva amounts
    $sevas = [
        'kalash'   => htmlspecialchars($data['kalash_amount'] ?? ''),
        'makhan'   => htmlspecialchars($data['makhan_amount'] ?? ''),
        'pushpanjali' => htmlspecialchars($data['pushpanjali_amount'] ?? ''),
        'annadaan' => htmlspecialchars($data['annadaan_amount'] ?? ''),
        'gau'      => htmlspecialchars($data['gau_amount'] ?? ''),
        'vaishnav' => htmlspecialchars($data['vaishnav_amount'] ?? ''),
        'sadhu'    => htmlspecialchars($data['sadhu_amount'] ?? ''),
        'rajbhog'  => htmlspecialchars($data['rajbhog_amount'] ?? ''),
        'any'      => htmlspecialchars($data['any_amount'] ?? ''),
    ];
    $seva_summary = [];
    foreach ($sevas as $seva => $amt) {
        if ($amt !== '' && $amt > 0) {
            $seva_summary[] = ucfirst($seva) . ": Rs. $amt";
        }
    }
    $seva_summary_str = implode('; ', $seva_summary);

    // Timestamp
    $timestamp = date('Y-m-d H:i:s');

    // Prepare row
    $row = [
        $timestamp,
        $donor_name,
        $donor_email,
        $donor_phone,
        $donor_dob,
        $donor_address,
        $receive_prasadam,
        $want_80g,
        $donor_pan,
        $seva_summary_str,
        $total_amount,
        $razorpay_payment_id,
        $razorpay_order_id,
        $razorpay_signature
    ];

    // Write headers if file doesn't exist
    if (!file_exists($filename)) {
        $header = [
            'Date & Time',
            'Donor Name',
            'Email Address',
            'Mobile Number',
            'Date of Birth',
            'Complete Address',
            'Receive Prasadam',
            '80G Tax Exemption',
            'PAN Number',
            'Selected Sevas',
            'Total Amount (₹)',
            'Razorpay Payment ID',
            'Razorpay Order ID',
            'Payment Signature'
        ];
        $file = fopen($filename, 'w');
        if ($file === false) {
            echo json_encode(['success' => false, 'message' => 'Failed to create CSV file']);
            exit;
        }
        fputcsv($file, $header);
    } else {
        $file = fopen($filename, 'a');
        if ($file === false) {
            echo json_encode(['success' => false, 'message' => 'Failed to open CSV file']);
            exit;
        }
    }

    // Write data row
    if (fputcsv($file, $row) === false) {
        fclose($file);
        echo json_encode(['success' => false, 'message' => 'Failed to write to CSV file']);
        exit;
    }
    fclose($file);

    include 'send_donation_email.php';

    $donorData = [
        'name' => $donor_name,
        'email' => $donor_email,
        'phone' => $donor_phone,
        'amount' => $total_amount,
        'seva' => $seva_summary_str,
        '80g' => $want_80g === 'Yes' ? 'true' : 'false', // true/false
    ];

    sendDonationEmail($donorData);

    echo json_encode(['success' => true, 'message' => 'Donation saved successfully']);
    exit;
}

// If not POST
echo json_encode(['success' => false, 'message' => 'Invalid request']);
exit;
?>
