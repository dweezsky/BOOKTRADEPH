<?php 
@include 'config.php';
$order_items_query = mysqli_query($conn, "
    SELECT 
        orders.id AS order_id, 
        orders.status, 
        orders.created_at, 
        orders.address, 
        GROUP_CONCAT(products.name SEPARATOR ', ') AS product_names, 
        GROUP_CONCAT(order_items.quantity SEPARATOR ', ') AS quantities,
        GROUP_CONCAT(order_items.price SEPARATOR ', ') AS product_prices,
        SUM(order_items.price * order_items.quantity) AS total_amount,
        users.first_name, 
        users.last_name
    FROM order_items
    INNER JOIN orders ON order_items.order_id = orders.id
    INNER JOIN products ON order_items.product_id = products.id
    INNER JOIN users ON orders.user_id = users.id
    GROUP BY orders.id
    ORDER BY orders.id DESC
");

$order_items = [];
while ($row = mysqli_fetch_assoc($order_items_query)) {
    $order_items[] = $row;
}
// Handle status update (To Ship or Rejected)
if (isset($_POST['update_status'])) {
    $order_id = $_POST['order_id'];
    $new_status = $_POST['update_status'];
    mysqli_query($conn, "UPDATE orders SET status = '$new_status' WHERE id = $order_id");
    header('Location: transaction_management.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transaction Management</title>
    <link rel="stylesheet" href="homepage.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
           .btn {
            background-color: #a77d54;
            color: white;
            border: none;
            padding: 8px 16px;
            cursor: pointer;
            border-radius: 5px;
            margin: 2px;
        }
        .btn:hover {
            background-color: #855b3a;
        }
        .btn-danger {
            background-color: red;
            color: white;
            border: none;
            padding: 8px 16px;
            cursor: pointer;
            border-radius: 5px;
        }
        .btn-danger:hover {
            background-color: darkred;
        }

        header {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            background-color: #a77d54;
            border-bottom: 1px solid #94949450;
            box-shadow: 0px 0px 8px #44444441;
            z-index: 100;
        }

        header nav {
            width: 85%;
            margin: auto;
            height: 100px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        header nav .nav-links a {
            color: #fff;
            font-weight: 600;
            font-size: 18px;
            margin: 0 35px;
        }

        header nav .nav-links a:hover {
            color: #c9c5c2;
        }

        .admin-content {
            padding: 20px;
            margin-top: 120px;
        }

        h1 {
            text-align: center;
            margin-bottom: 40px;
        }

        nav ul {
            display: flex;
            justify-content: center;
            padding: 0;
        }

        nav ul li {
            list-style: none;
            margin: 0 10px;
        }

        nav ul li a {
            padding: 10px 20px;
            background-color: #a77d54;
            color: #fff;
            border-radius: 5px;
        }

        nav ul li a:hover {
            background-color: #855b3a;
        }

        .transaction-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .transaction-table th, .transaction-table td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: center;
        }

        .transaction-table th {
            background-color: #f5f5f5;
        }

        .transaction-tab {
            display: none;
        }

        .transaction-tab.active {
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
            <a href="user_management.php" class="active">USER MANAGEMENT</a>
        </div>
    </nav>
</header>

<div class="admin-content">
    <h1>Transaction Management</h1>

    <nav>
        <ul>
        <li><a href="#" class="tab-link" data-tab="shipping">Shipping</a></li>
            <li><a href="#" class="tab-link" data-tab="pending">Pending Transactions</a></li>
            <li><a href="#" class="tab-link" data-tab="completed">Completed Transactions</a></li>
            <li><a href="#" class="tab-link" data-tab="canceled">Canceled Transactions</a></li>
            <li><a href="#" class="tab-link" data-tab="rejected">Rejected Transactions</a></li>
            <li><a href="#" class="tab-link" data-tab="all">All Transactions</a></li>
        </ul>
    </nav>

    <section class="transactions">
        <div class="transaction-tab active" id="shipping"> 
            <h2>Shipping Transactions</h2>
            <table class="transaction-table">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Customer Name</th>
                        <th>Products</th>
                        <th>Total Amount</th>
                        <th>Address</th>
                        <th>Time of Purchase</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($order_items as $order): ?>
                    <?php if ($order['status'] == 'To Ship'): ?>
                    <tr id="order-<?= $order['order_id']; ?>">
                        <td><?= $order['order_id']; ?></td>
                        <td><?= $order['first_name'] . ' ' . $order['last_name']; ?></td>
                        <td><?= $order['product_names']; ?></td>
                        <td>₱<?= number_format($order['total_amount'], 2); ?></td>
                        <td><?= $order['address']; ?></td>
                        <td><?= date("F j, Y, g:i a", strtotime($order['created_at'])); ?></td>
                        <td>
                            <button class="btn" onclick="updateStatus(<?= $order['order_id']; ?>, 'Pending')">To Ship</button>
                            <button class="btn btn-danger" onclick="updateStatus(<?= $order['order_id']; ?>, 'Rejected')">Reject</button>
                        </td>
                    </tr>
                    <?php endif; ?>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="transaction-tab" id="pending">
            <h2>Pending Transactions</h2>
            <table class="transaction-table">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Customer Name</th>
                        <th>Products</th>
                        <th>Total Amount</th>
                        <th>Address</th>
                        <th>Time of Purchase</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                <tbody id="pending-tbody">
                    <?php foreach ($order_items as $order): ?>
                    <?php if ($order['status'] == 'Pending'): ?>
                    <tr>
                        <td><?= $order['order_id']; ?></td>
                        <td><?= $order['first_name'] . ' ' . $order['last_name']; ?></td>
                        <td><?= $order['product_names']; ?></td>
                        <td>₱<?= number_format($order['total_amount'], 2); ?></td>
                        <td><?= $order['address']; ?></td>
                        <td><?= date("F j, Y, g:i a", strtotime($order['created_at'])); ?></td>
                        <td><?= $order['status']; ?></td>
                    </tr>
                    <?php endif; ?>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
 <!-- Rejected Transactions -->
        <div class="transaction-tab" id="rejected">
            <h2>Rejected Transactions</h2>
            <table class="transaction-table">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Customer Name</th>
                        <th>Products</th>
                        <th>Total Amount</th>
                        <th>Address</th>
                        <th>Time of Purchase</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($order_items as $order): ?>
                    <?php if ($order['status'] == 'Rejected'): ?>
                    <tr>
                        <td><?= $order['order_id']; ?></td>
                        <td><?= $order['first_name'] . ' ' . $order['last_name']; ?></td>
                        <td><?= $order['product_names']; ?></td>
                        <td>₱<?= number_format($order['total_amount'], 2); ?></td>
                        <td><?= $order['address']; ?></td>
                        <td><?= date("F j, Y, g:i a", strtotime($order['created_at'])); ?></td>
                    </tr>
                    <?php endif; ?>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
           <!-- Canceled Transactions Tab -->
           <div class="transaction-tab" id="canceled">
            <h2>Canceled Transactions</h2>
            <table class="transaction-table">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Customer Name</th>
                        <th>Products</th>
                        <th>Total Amount</th>
                        <th>Address</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody id="canceled-tbody">
                <?php foreach ($order_items as $order): ?>
                    <?php if ($order['status'] == 'Canceled'): ?>
                    <tr>
                        <td><?= $order['order_id']; ?></td>
                        <td><?= $order['first_name'] . ' ' . $order['last_name']; ?></td>
                        <td><?= $order['product_names']; ?></td>
                        
                        <td>₱<?= number_format($order['total_amount'], 2); ?></td>
                        <td><?= $order['address']; ?></td>
                        <td><?= $order['status']; ?></td>
                    </tr>
                    <?php endif; ?>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <div class="transaction-tab" id="completed">
            <h2>Completed Transactions</h2>
            <table class="transaction-table">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Customer Name</th>
                        <th>Products</th>
                        <th>Total Amount</th>
                        <th>Address</th>
                        <th>Time of Purchase</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($order_items as $order): ?>
                    <?php if ($order['status'] == 'Completed'): ?>
                    <tr>
                        <td><?= $order['order_id']; ?></td>
                        <td><?= $order['first_name'] . ' ' . $order['last_name']; ?></td>
                        <td><?= $order['product_names']; ?></td>
                        <td>₱<?= number_format($order['total_amount'], 2); ?></td>
                        <td><?= $order['address']; ?></td>
                        <td><?= date("F j, Y, g:i a", strtotime($order['created_at'])); ?></td>
                        <td><?= $order['status']; ?></td>
                    </tr>
                    <?php endif; ?>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <div class="transaction-tab" id="all">
    <h2>All Transactions</h2>
    <table class="transaction-table">
    <a href="export_transactions.php" class="btn" style="float: right; margin-bottom: 10px;">
    <i class="fa fa-download"></i> Export to Excel
</a>
        <thead>
            <tr>
                <th>Order ID</th>
                <th>Customer Name</th>
                <th>Products</th>
                <th>Quantities</th>
                <th>Product Prices</th>
                <th>Total Amount</th>
                <th>Address</th>
                <th>Time of Purchase</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($order_items as $order): ?>
            <tr>
                <td><?php echo $order['order_id']; ?></td>
                <td><?php echo $order['first_name'] . ' ' . $order['last_name']; ?></td>
                <td><?php echo $order['product_names']; ?></td>
                <td><?php echo $order['quantities']; ?></td>
                <td>₱<?php echo $order['product_prices']; ?></td>
                <td>₱<?php echo number_format($order['total_amount'], 2); ?></td>
                <td><?php echo $order['address']; ?></td>
                <td><?php echo date("F j, Y, g:i a", strtotime($order['created_at'])); ?></td>
                <td><?php echo $order['status']; ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
    </section>
</div>

<script>
    const tabs = document.querySelectorAll('.tab-link');
    const tabContents = document.querySelectorAll('.transaction-tab');

    tabs.forEach(tab => {
        tab.addEventListener('click', function (e) {
            e.preventDefault();
            const targetTab = this.getAttribute('data-tab');
            tabContents.forEach(content => content.classList.remove('active'));
            document.getElementById(targetTab).classList.add('active');
        });
    });
    function updateStatus(orderId, status) {
        const xhr = new XMLHttpRequest();
        xhr.open('POST', 'update_status.php', true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.onload = function () {
            if (xhr.status === 200) {
                const row = document.getElementById('order-' + orderId);
                if (row) row.remove();  // Remove the row from the table
            } else {
                alert('Failed to update status.');
            }
        };
        xhr.send('order_id=' + orderId + '&status=' + status);
    }
    function updateStatus(orderId, status) {
        const xhr = new XMLHttpRequest();
        xhr.open('POST', 'update_status.php', true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.onload = function () {
            if (xhr.status === 200) {
                const row = document.getElementById('order-' + orderId);
                if (row) row.remove();  // Remove the row from the Shipping tab

                if (status === 'Pending') {
                    const pendingTbody = document.getElementById('pending-tbody');
                    pendingTbody.innerHTML += xhr.responseText;  // Add the row to Pending tab
                }
            } else {
                alert('Failed to update status.');
            }
        };
        xhr.send('order_id=' + orderId + '&status=' + status);
    }
</script>
</body>
</html>