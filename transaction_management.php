<?php
@include 'config.php';


$purchase_history = mysqli_query($conn, "SELECT * FROM purchases");


$payment_processing = mysqli_query($conn, "SELECT * FROM payments");

$transaction_report = mysqli_query($conn, "SELECT product_name, SUM(total_amount) as total_sales FROM purchases GROUP BY product_name");

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <title>Transaction Management</title>
    <style>
  
        .sub-nav-links {
            margin: 20px 0;
        }
        .sub-nav-links a {
            margin-right: 20px;
            text-decoration: none;
            color: #333;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        .sub-nav-links a.active {
            background-color: #4CAF50;
            color: white;
        }
        .section {
            display: none;
        }
        .section.active {
            display: block;
        }
    </style>
</head>
<body>

<header>
    <nav>
        <div class="nav-links">
            <a href="admin_page.php">BOOK MANAGEMENT</a>
            <a href="transaction_management.php" class="active">TRANSACTION MANAGEMENT</a>
            <a href="inventory.php">INVENTORY MANAGEMENT</a>
            <a href="user_management.php">USER MANAGEMENT</a>
        </div>
    </nav>
</header>

<div class="container">
    <h1>Transaction Management</h1>

    <div class="sub-nav-links">
        <a href="#purchase-history" class="sub-nav-item active" data-section="purchase-history">Purchase History</a>
        <a href="#payment-processing" class="sub-nav-item" data-section="payment-processing">Payment Processing</a>
        <a href="#transaction-reporting" class="sub-nav-item" data-section="transaction-reporting">Transaction Reporting & Analytics</a>
    </div>

    <div id="purchase-history" class="section active">
        <h2>Purchase History</h2>
        <p>This section provides a detailed view of all purchases made by users or customers.</p>
        <table class="transaction-table">
            <thead>
                <tr>
                    <th>User/Customer Name</th>
                    <th>Product/Service Purchased</th>
                    <th>Date of Purchase</th>
                    <th>Total Amount</th>
                    <th>Payment Method</th>
                    <th>Transaction Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($purchase = mysqli_fetch_assoc($purchase_history)): ?>
                <tr>
                    <td><?php echo $purchase['customer_name']; ?></td>
                    <td><?php echo $purchase['product_name']; ?></td>
                    <td><?php echo $purchase['purchase_date']; ?></td>
                    <td><?php echo $purchase['total_amount']; ?></td>
                    <td><?php echo $purchase['payment_method']; ?></td>
                    <td><?php echo $purchase['transaction_status']; ?></td>
                    <td>
                        <a href="view_purchase.php?id=<?php echo $purchase['id']; ?>" class="btn">View</a>
                        <a href="process_refund.php?id=<?php echo $purchase['id']; ?>" class="btn">Refund</a>
                        <a href="dispute_transaction.php?id=<?php echo $purchase['id']; ?>" class="btn">Dispute</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>


    <div id="payment-processing" class="section">
        <h2>Payment Processing</h2>
        <p>Manage and track how payments are processed for transactions.</p>
        <table class="transaction-table">
            <thead>
                <tr>
                    <th>Payment Method</th>
                    <th>Status</th>
                    <th>Payment Gateway</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($payment = mysqli_fetch_assoc($payment_processing)): ?>
                <tr>
                    <td><?php echo $payment['payment_method']; ?></td>
                    <td><?php echo $payment['status']; ?></td>
                    <td><?php echo $payment['payment_gateway']; ?></td>
                    <td>
                        <a href="update_payment_status.php?id=<?php echo $payment['id']; ?>" class="btn">Update Status</a>
                        <a href="process_refund.php?id=<?php echo $payment['id']; ?>" class="btn">Refund</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>


    <div id="transaction-reporting" class="section">
        <h2>Transaction Reporting and Analytics</h2>
        <p>Generate reports and insights on transactions to monitor financial health.</p>
        <table class="transaction-table">
            <thead>
                <tr>
                    <th>Product/Service</th>
                    <th>Total Sales</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($report = mysqli_fetch_assoc($transaction_report)): ?>
                <tr>
                    <td><?php echo $report['product_name']; ?></td>
                    <td><?php echo $report['total_sales']; ?></td>
                    <td>
                        <a href="download_report.php?product=<?php echo $report['product_name']; ?>" class="btn">Download Report</a>
                        <a href="view_graphs.php?product=<?php echo $report['product_name']; ?>" class="btn">View Graphs</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

</div>


<script>
    const subNavItems = document.querySelectorAll('.sub-nav-item');
    const sections = document.querySelectorAll('.section');

    subNavItems.forEach(item => {
        item.addEventListener('click', function(event) {
            event.preventDefault();
            
            subNavItems.forEach(nav => nav.classList.remove('active'));

            sections.forEach(section => section.classList.remove('active'));

            item.classList.add('active');
            const targetSection = document.getElementById(item.dataset.section);
            targetSection.classList.add('active');
        });
    });
</script>

</body>
</html>