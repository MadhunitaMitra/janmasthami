<?php
session_start();

// --- CONFIG ---
$ADMIN_USER = 'admin';
$ADMIN_PASS = 'iskcon2025'; // Change this password after first login!
$CSV_FILE = 'donations.csv';

// --- HANDLE LOGOUT ---
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: admin_donations.php');
    exit;
}

// --- HANDLE LOGIN ---
$login_error = '';
if (isset($_POST['login'])) {
    $user = htmlspecialchars($_POST['username'] ?? '');
    $pass = htmlspecialchars($_POST['password'] ?? '');
    if ($user === $ADMIN_USER && $pass === $ADMIN_PASS) {
        $_SESSION['admin_logged_in'] = true;
        header('Location: admin_donations.php');
        exit;
    } else {
        $login_error = 'Invalid username or password.';
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - ISKCON Donations</title>
    <link rel="icon" type="image/png" href="Logo.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        :root {
            --primary-maroon: #7b1831;
            --dark-maroon: #5a1224;
            --light-maroon: #9a1f3e;
            --gold: #ffd700;
            --light-gold: #fff3cd;
            --cream: #fff8dc;
            --text-dark: #2c1810;
            --text-light: #6c757d;
            --border-color: #e9ecef;
            --success-green: #28a745;
            --warning-orange: #ffc107;
        }

        * {
            font-family: 'Inter', sans-serif;
        }

        body {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            min-height: 100vh;
        }

        /* Login Page Styling */
        .login-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, var(--primary-maroon) 0%, var(--dark-maroon) 100%);
        }

        .login-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(123, 24, 49, 0.15);
            padding: 3rem;
            max-width: 400px;
            width: 90%;
        }

        .login-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .login-header img {
            width: 80px;
            height: 80px;
            margin-bottom: 1rem;
        }

        .login-header h2 {
            color: var(--primary-maroon);
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .login-header p {
            color: var(--text-light);
            font-size: 0.95rem;
        }

        .form-control {
            border: 2px solid var(--border-color);
            border-radius: 12px;
            padding: 12px 16px;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: var(--primary-maroon);
            box-shadow: 0 0 0 0.2rem rgba(123, 24, 49, 0.15);
        }

        .btn-login {
            background: linear-gradient(135deg, var(--primary-maroon) 0%, var(--dark-maroon) 100%);
            border: none;
            border-radius: 12px;
            padding: 12px 24px;
            font-weight: 600;
            color: white;
            transition: all 0.3s ease;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(123, 24, 49, 0.3);
            color: white;
        }

        /* Dashboard Styling */
        .dashboard-header {
            background: linear-gradient(135deg, var(--primary-maroon) 0%, var(--dark-maroon) 100%);
            color: white;
            padding: 2rem 0;
            margin-bottom: 2rem;
            box-shadow: 0 8px 32px rgba(123, 24, 49, 0.15);
        }

        .dashboard-title {
            font-weight: 700;
            font-size: 2.2rem;
            margin-bottom: 0.5rem;
        }

        .dashboard-subtitle {
            opacity: 0.9;
            font-size: 1.1rem;
        }

        .stats-card {
            background: white;
            border-radius: 16px;
            padding: 1.5rem;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.08);
            border: 1px solid var(--border-color);
            margin-bottom: 2rem;
        }

        .stats-icon {
            width: 60px;
            height: 60px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: white;
            margin-bottom: 1rem;
        }

        .stats-icon.total {
            background: linear-gradient(135deg, var(--success-green) 0%, #20c997 100%);
        }

        .stats-icon.count {
            background: linear-gradient(135deg, var(--primary-maroon) 0%, var(--light-maroon) 100%);
        }

        .stats-icon.today {
            background: linear-gradient(135deg, var(--warning-orange) 0%, #fd7e14 100%);
        }

        .stats-number {
            font-size: 2rem;
            font-weight: 700;
            color: var(--text-dark);
            margin-bottom: 0.25rem;
        }

        .stats-label {
            color: var(--text-light);
            font-size: 0.9rem;
            font-weight: 500;
        }

        .main-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.08);
            border: 1px solid var(--border-color);
            overflow: hidden;
        }

        .card-header {
            background: linear-gradient(135deg, var(--primary-maroon) 0%, var(--dark-maroon) 100%);
            color: white;
            padding: 1.5rem 2rem;
            border-bottom: none;
        }

        .card-header h3 {
            margin: 0;
            font-weight: 600;
            font-size: 1.3rem;
        }

        .card-body {
            padding: 2rem;
        }

        .search-container {
            background: var(--cream);
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 2rem;
        }

        .search-box {
            border: 2px solid var(--border-color);
            border-radius: 12px;
            padding: 12px 16px;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .search-box:focus {
            border-color: var(--primary-maroon);
            box-shadow: 0 0 0 0.2rem rgba(123, 24, 49, 0.15);
        }

        .table-container {
            border-radius: 12px;
            overflow: auto;
            border: 1px solid var(--border-color);
            max-height: 600px;
        }

        .table {
            margin: 0;
            min-width: 100%;
        }

        .table-responsive {
            overflow-x: auto;
            overflow-y: auto;
            max-height: 600px;
        }

        .table thead th {
            background: var(--primary-maroon);
            color: white;
            border: none;
            padding: 1rem;
            font-weight: 600;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .table tbody tr {
            transition: all 0.3s ease;
        }

        .table tbody tr:hover {
            background: var(--light-gold);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .table tbody td {
            padding: 1rem;
            border-color: var(--border-color);
            vertical-align: middle;
        }

        .pagination {
            margin: 2rem 0 0 0;
        }

        .page-link {
            color: var(--primary-maroon);
            border: 1px solid var(--border-color);
            padding: 0.75rem 1rem;
            margin: 0 2px;
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        .page-link:hover {
            background: var(--primary-maroon);
            color: white;
            border-color: var(--primary-maroon);
        }

        .page-item.active .page-link {
            background: var(--primary-maroon);
            border-color: var(--primary-maroon);
        }

        .btn-logout {
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: white;
            border-radius: 8px;
            padding: 8px 16px;
            font-size: 0.9rem;
            transition: all 0.3s ease;
        }

        .btn-logout:hover {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            transform: translateY(-1px);
        }

        .footer {
            background: var(--primary-maroon);
            color: white;
            text-align: center;
            padding: 1.5rem 0;
            margin-top: 3rem;
        }

        .amount-cell {
            font-weight: 600;
            color: var(--success-green);
        }

        .date-cell {
            font-weight: 500;
            color: var(--text-dark);
        }

        .status-badge {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        .status-success {
            background: rgba(40, 167, 69, 0.1);
            color: var(--success-green);
        }

        @media (max-width: 768px) {
            .login-card {
                padding: 2rem;
                margin: 1rem;
            }
            
            .dashboard-title {
                font-size: 1.8rem;
            }
            
            .card-body {
                padding: 1rem;
            }
            
            .table-responsive {
                font-size: 0.9rem;
                max-height: 400px;
            }
            
            .table {
                font-size: 0.85rem;
            }
            
            .table thead th,
            .table tbody td {
                padding: 0.5rem;
                white-space: nowrap;
            }
        }

        /* Modal Styling */
        .donation-details-modal .swal2-popup {
            border-radius: 16px;
        }

        .donation-details-modal .swal2-title {
            color: var(--primary-maroon);
            font-weight: 600;
        }

        .donation-details-modal .swal2-confirm {
            background: var(--primary-maroon) !important;
            border-radius: 8px !important;
        }

        .donation-details-modal .swal2-html-container {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 1rem;
            margin: 1rem 0;
        }

        .donation-details-modal .card {
            border: 1px solid var(--border-color);
            border-radius: 8px;
            transition: all 0.3s ease;
            background: white;
            margin-bottom: 1rem;
        }

        .donation-details-modal .card:hover {
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            transform: translateY(-2px);
        }

        .donation-details-modal .card-header {
            background: linear-gradient(135deg, var(--primary-maroon) 0%, var(--dark-maroon) 100%);
            color: white;
            border-bottom: 1px solid var(--border-color);
            font-size: 0.9rem;
            font-weight: 600;
            padding: 0.75rem 1rem;
        }

        .donation-details-modal .card-body {
            padding: 1rem;
            font-size: 0.95rem;
            background: white;
            color: var(--text-dark);
        }

        .donation-details-modal .text-success {
            color: var(--success-green) !important;
        }

        .donation-details-modal .text-primary {
            color: var(--primary-maroon) !important;
        }

        .donation-details-modal .text-muted {
            color: var(--text-light) !important;
        }

        .donation-details-modal .fw-bold {
            font-weight: 700 !important;
        }

        /* Bootstrap Modal Styling */
        #donationDetailsModal .modal-content {
            border-radius: 16px;
            border: none;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
        }

        #donationDetailsModal .modal-header {
            background: linear-gradient(135deg, var(--primary-maroon) 0%, var(--dark-maroon) 100%);
            color: white;
            border-bottom: none;
            border-radius: 16px 16px 0 0;
        }

        #donationDetailsModal .modal-body {
            background: #f8f9fa;
            padding: 2rem;
        }

        #donationDetailsModal .card {
            border: 1px solid var(--border-color);
            border-radius: 8px;
            transition: all 0.3s ease;
            background: white;
            margin-bottom: 1rem;
        }

        #donationDetailsModal .card:hover {
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            transform: translateY(-2px);
        }

        #donationDetailsModal .card-header {
            background: linear-gradient(135deg, var(--primary-maroon) 0%, var(--dark-maroon) 100%);
            color: white;
            border-bottom: 1px solid var(--border-color);
            font-size: 0.9rem;
            font-weight: 600;
            padding: 0.75rem 1rem;
        }

        #donationDetailsModal .card-body {
            padding: 1rem;
            font-size: 0.95rem;
            background: white;
            color: var(--text-dark);
        }
    </style>
</head>
<body>

<?php if (empty($_SESSION['admin_logged_in'])): ?>
    <!-- Login Page -->
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <img src="Logo.png" alt="ISKCON Logo">
                <h2>Admin Login</h2>
                <p>Access the donations dashboard</p>
            </div>
            
            <?php if ($login_error): ?>
                <div class="alert alert-danger" role="alert">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <?php echo htmlspecialchars($login_error); ?>
                </div>
            <?php endif; ?>
            
            <form method="post">
                <div class="mb-3">
                    <label class="form-label">Username</label>
                    <input type="text" name="username" class="form-control" required autofocus>
                </div>
                <div class="mb-4">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <button type="submit" name="login" class="btn btn-login w-100">
                    <i class="fas fa-sign-in-alt me-2"></i>Login
                </button>
            </form>
        </div>
    </div>

<?php else: ?>
    <!-- Dashboard -->
    <div class="dashboard-header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1 class="dashboard-title">Donations Dashboard</h1>
                    <p class="dashboard-subtitle">Monitor and manage all Janmashtami donations</p>
                </div>
                <div class="col-md-4 text-md-end">
                    <a href="?logout=1" class="btn btn-logout">
                        <i class="fas fa-sign-out-alt me-2"></i>Logout
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="container">
        <?php
        // --- READ CSV ---
        $csv_data = [];
        if (file_exists($CSV_FILE) && ($handle = fopen($CSV_FILE, 'r')) !== false) {
            while (($row = fgetcsv($handle)) !== false) {
                if (!empty(array_filter($row))) {
                    $csv_data[] = $row;
                }
            }
            fclose($handle);
        }

        if (count($csv_data) < 2) {
            echo '<div class="alert alert-warning" role="alert">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    No donations found yet.
                  </div>';
        } else {
            $headers = $csv_data[0];
            $rows = array_slice($csv_data, 1);
            
            // Calculate statistics
            $total_donations = count($rows);
            $total_amount = 0;
            $today_donations = 0;
            $today_amount = 0;
            $today = date('Y-m-d');
            
            foreach ($rows as $row) {
                if (isset($row[10])) { // Total Amount column
                    $amount = str_replace(['₹', ','], '', $row[10]);
                    $total_amount += floatval($amount);
                }
                
                if (isset($row[0])) { // Date column
                    $donation_date = date('Y-m-d', strtotime($row[0]));
                    if ($donation_date === $today) {
                        $today_donations++;
                        if (isset($row[10])) {
                            $amount = str_replace(['₹', ','], '', $row[10]);
                            $today_amount += floatval($amount);
                        }
                    }
                }
            }
        ?>
        
        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-md-4 mb-3">
                <div class="stats-card">
                    <div class="stats-icon total">
                        <i class="fas fa-rupee-sign"></i>
                    </div>
                    <div class="stats-number">₹<?php echo number_format($total_amount); ?></div>
                    <div class="stats-label">Total Amount</div>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="stats-card">
                    <div class="stats-icon count">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stats-number"><?php echo $total_donations; ?></div>
                    <div class="stats-label">Total Donations</div>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="stats-card">
                    <div class="stats-icon today">
                        <i class="fas fa-calendar-day"></i>
                    </div>
                    <div class="stats-number"><?php echo $today_donations; ?></div>
                    <div class="stats-label">Today's Donations</div>
                </div>
            </div>
        </div>

        <!-- Main Data Table -->
        <div class="main-card">
            <div class="card-header">
                <h3><i class="fas fa-table me-2"></i>Donations Data</h3>
            </div>
            <div class="card-body">
                <div class="search-container">
                    <div class="row align-items-center">
                        <div class="col-md-6 mb-3 mb-md-0">
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0">
                                    <i class="fas fa-search text-muted"></i>
                                </span>
                                <input type="text" class="form-control search-box border-start-0" id="searchInput" placeholder="Search by name, email, or amount...">
                            </div>
                            <small class="text-muted mt-1" id="searchResults">
                                Showing all donations
                            </small>
                        </div>
                        <div class="col-md-6 text-md-end">
                            <label for="pageSize" class="me-2 text-muted">Rows per page:</label>
                            <select id="pageSize" class="form-select d-inline-block" style="width: auto;">
                                <option>10</option>
                                <option>25</option>
                                <option>50</option>
                                <option>100</option>
                            </select>
                        </div>
                    </div>
                </div>
                
                <div id="tableContainer"></div>
                
                <nav>
                    <ul class="pagination justify-content-center" id="pagination"></ul>
                </nav>
            </div>
        </div>

        <?php
        $csv_json = json_encode($csv_data);
        ?>
        
        <script>
        const csvData = <?php echo $csv_json; ?>;
        const headers = csvData[0];
        const rows = csvData.slice(1);
        let currentPage = 1;
        let pageSize = 10;
        let filteredRows = rows;

        function renderTable() {
            const start = (currentPage - 1) * pageSize;
            const end = start + pageSize;
            
            // Show only essential columns: Date, Name, Email, Amount, Actions
            const essentialHeaders = ['Date & Time', 'Donor Name', 'Email Address', 'Total Amount (₹)', 'Actions'];
            const essentialIndices = [0, 1, 2, 10]; // Date, Name, Email, Amount
            
            let html = "<div class='table-responsive'><table class='table table-hover'><thead><tr>";
            
            essentialHeaders.forEach(h => { 
                html += `<th>${h}</th>`; 
            });
            
            html += "</tr></thead><tbody>";

            if (filteredRows.length === 0) {
                html += `<tr><td colspan="${essentialHeaders.length}" class="text-center text-muted py-4">
                            <i class="fas fa-search me-2"></i>No results found.
                         </td></tr>`;
            } else {
                filteredRows.slice(start, end).forEach((row, rowIndex) => {
                    html += "<tr>";
                    
                    // Add essential columns
                    essentialIndices.forEach((index, colIndex) => {
                        let cellClass = '';
                        let cellContent = row[index] || '';
                        
                        // Style amount column
                        if (index === 10 && cellContent.includes('₹')) {
                            cellClass = 'amount-cell';
                        }
                        // Style date column
                        else if (index === 0) {
                            cellClass = 'date-cell';
                            // Format date for better display
                            if (cellContent) {
                                const date = new Date(cellContent);
                                cellContent = date.toLocaleDateString('en-IN') + ' ' + date.toLocaleTimeString('en-IN', {hour: '2-digit', minute: '2-digit'});
                            }
                        }
                        // Truncate email if too long
                        else if (index === 2 && cellContent.length > 25) {
                            cellContent = cellContent.substring(0, 25) + '...';
                        }
                        
                        html += `<td class="${cellClass}">${cellContent}</td>`;
                    });
                    
                    // Add view details button
                    html += `<td class="text-center">
                                <button class="btn btn-sm btn-outline-primary" onclick="viewDonationDetails(${start + rowIndex})" title="View Details">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </td>`;
                    
                    html += "</tr>";
                });
            }

            html += "</tbody></table></div>";
            document.getElementById('tableContainer').innerHTML = html;
            renderPagination();
        }

        function viewDonationDetails(rowIndex) {
            const row = filteredRows[rowIndex];
            const headers = csvData[0];
            
            let modalContent = '<div class="row">';
            
            headers.forEach((header, index) => {
                let value = row[index] || '';
                let displayClass = '';
                
                // Style different types of data
                if (index === 10 && value.includes('₹')) {
                    displayClass = 'text-success fw-bold';
                } else if (index === 0) {
                    displayClass = 'text-primary';
                } else if (value === 'Yes' || value === 'No') {
                    displayClass = value === 'Yes' ? 'text-success' : 'text-muted';
                }
                
                modalContent += `
                    <div class="col-md-6 mb-3">
                        <div class="card h-100">
                            <div class="card-header bg-light">
                                <strong>${header}</strong>
                            </div>
                            <div class="card-body">
                                <span class="${displayClass}">${value}</span>
                            </div>
                        </div>
                    </div>
                `;
            });
            
            modalContent += '</div>';
            
            // Show modal with donation details
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Donation Details',
                    html: modalContent,
                    width: '90%',
                    maxWidth: '800px',
                    confirmButtonText: 'Close',
                    confirmButtonColor: '#7b1831',
                    showCloseButton: true,
                    customClass: {
                        container: 'donation-details-modal'
                    }
                });
            } else {
                // Fallback to Bootstrap modal
                const modal = new bootstrap.Modal(document.getElementById('donationDetailsModal'));
                document.getElementById('donationDetailsContent').innerHTML = modalContent;
                modal.show();
            }
        }

        function renderPagination() {
            const totalPages = Math.ceil(filteredRows.length / pageSize) || 1;
            let html = "";

            if (totalPages > 1) {
                html += `<li class="page-item ${currentPage === 1 ? 'disabled' : ''}">
                            <a class="page-link" href="#" onclick="gotoPage(1); return false;">
                                <i class="fas fa-angle-double-left"></i>
                            </a>
                         </li>`;
                html += `<li class="page-item ${currentPage === 1 ? 'disabled' : ''}">
                            <a class="page-link" href="#" onclick="gotoPage(${currentPage - 1}); return false;">
                                <i class="fas fa-angle-left"></i>
                            </a>
                         </li>`;

                for (let i = Math.max(1, currentPage - 2); i <= Math.min(totalPages, currentPage + 2); i++) {
                    html += `<li class="page-item ${i === currentPage ? 'active' : ''}">
                                <a class="page-link" href="#" onclick="gotoPage(${i}); return false;">${i}</a>
                             </li>`;
                }

                html += `<li class="page-item ${currentPage === totalPages ? 'disabled' : ''}">
                            <a class="page-link" href="#" onclick="gotoPage(${currentPage + 1}); return false;">
                                <i class="fas fa-angle-right"></i>
                            </a>
                         </li>`;
                html += `<li class="page-item ${currentPage === totalPages ? 'disabled' : ''}">
                            <a class="page-link" href="#" onclick="gotoPage(${totalPages}); return false;">
                                <i class="fas fa-angle-double-right"></i>
                            </a>
                         </li>`;
            }

            document.getElementById('pagination').innerHTML = html;
        }

        function gotoPage(p) {
            currentPage = p;
            renderTable();
        }

        document.getElementById('searchInput').addEventListener('input', function () {
            const q = this.value.trim().toLowerCase();
            const searchResultsElement = document.getElementById('searchResults');
            
            if (q === '') {
                filteredRows = rows; // Show all rows when search is empty
                searchResultsElement.textContent = `Showing all ${rows.length} donations`;
            } else {
                filteredRows = rows.filter(row => {
                    // Search in essential columns: Date, Name, Email, Amount
                    const searchableColumns = [0, 1, 2, 10]; // Date, Name, Email, Amount indices
                    return searchableColumns.some(index => {
                        const value = row[index] || '';
                        return value.toLowerCase().includes(q);
                    });
                });
                searchResultsElement.textContent = `Found ${filteredRows.length} donation(s) matching "${q}"`;
            }
            currentPage = 1;
            renderTable();
        });

        document.getElementById('pageSize').addEventListener('change', function () {
            pageSize = parseInt(this.value);
            currentPage = 1;
            renderTable();
        });

        // Initial render
        renderTable();
        
        // Initialize search results counter
        document.getElementById('searchResults').textContent = `Showing all ${rows.length} donations`;
        </script>

        <?php } ?>
    </div>

    <div class="footer">
        <div class="container">
            <p class="mb-0">
                ISKCON New Town &copy; <?php echo date('Y'); ?> - Admin Dashboard
            </p>
        </div>
    </div>

    <!-- Fallback Bootstrap Modal for Donation Details -->
    <div class="modal fade" id="donationDetailsModal" tabindex="-1" aria-labelledby="donationDetailsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header" style="background: var(--primary-maroon); color: white;">
                    <h5 class="modal-title" id="donationDetailsModalLabel">
                        <i class="fas fa-info-circle me-2"></i>Donation Details
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="donationDetailsContent">
                    <!-- Content will be dynamically loaded here -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

<?php endif; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
