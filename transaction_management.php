<?php 
@include 'config.php';

// Fetch all order items with user information, grouping by order ID
$order_items_query = mysqli_query($conn, "
    SELECT orders.id AS order_id, orders.status, orders.created_at, orders.address, 
           GROUP_CONCAT(products.name SEPARATOR ', ') AS products, 
           SUM(order_items.price * order_items.quantity) AS total_amount,
           users.first_name, users.last_name
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
if (isset($_POST['update_status'])) {
    $order_id = $_POST['order_id'];
    mysqli_query($conn, "UPDATE orders SET status = 'Pending' WHERE id = $order_id");
    header('Location: transaction_management.php'); 
    exit;
}
?>
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
                      
                        <tr>
                            <td><?php echo $order['order_id']; ?></td>
                            <td><?php echo $order['first_name'] . ' ' . $order['last_name']; ?></td>
                            <td><?php echo $order['products']; ?></td>
                            <td>₱<?php echo number_format($order['total_amount'], 2); ?></td>
                            <td><?php echo $order['address']; ?></td>
                            <td><?php echo date("F j, Y, g:i a", strtotime($order['created_at'])); ?></td>
                            <td>
                                <form method="POST">
                                    <input type="hidden" name="order_id" value="<?php echo $order['order_id']; ?>">
                                    <button type="submit" name="update_status" class="btn">to ship</button>
                                </form>
                            </td>
                        </tr>
                     
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="transaction-tab" id="all">
            <h2>All Transactions</h2>
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
                    <tr>
                        <td><?php echo $order['order_id']; ?></td>
                        <td><?php echo $order['first_name'] . ' ' . $order['last_name']; ?></td>
                        <td><?php echo $order['products']; ?></td>
                        <td>₱<?php echo number_format($order['total_amount'], 2); ?></td>
                        <td><?php echo $order['address']; ?></td>
                        <td><?php echo date("F j, Y, g:i a", strtotime($order['created_at'])); ?></td>
                        <td><?php echo $order['status']; ?></td>
                    </tr>
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
                    <?php foreach ($order_items as $order): ?>
                    <?php if ($order['status'] == 'Pending'): ?>
                    <tr>
                        <td><?php echo $order['order_id']; ?></td>
                        <td><?php echo $order['first_name'] . ' ' . $order['last_name']; ?></td>
                        <td><?php echo $order['products']; ?></td>
                        <td>₱<?php echo number_format($order['total_amount'], 2); ?></td>
                        <td><?php echo $order['address']; ?></td>
                        <td><?php echo date("F j, Y, g:i a", strtotime($order['created_at'])); ?></td>
                        <td><?php echo $order['status']; ?></td>
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
                        <td><?php echo $order['order_id']; ?></td>
                        <td><?php echo $order['first_name'] . ' ' . $order['last_name']; ?></td>
                        <td><?php echo $order['products']; ?></td>
                        <td>₱<?php echo number_format($order['total_amount'], 2); ?></td>
                        <td><?php echo $order['address']; ?></td>
                        <td><?php echo date("F j, Y, g:i a", strtotime($order['created_at'])); ?></td>
                        <td><?php echo $order['status']; ?></td>
                    </tr>
                    <?php endif; ?>
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
</script>
<style>
    * {
    font-family: 'Poppins', sans-serif;
    margin: 0; padding: 0;
    box-sizing: border-box;
    outline: none; border: none;
    text-decoration: none;
    text-transform: capitalize;
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

header nav .logo {
    color: #ffffff;
    font-size: 40px;
    font-weight: 800;
    text-transform: uppercase;
}

header nav .nav-links a {
    color: #ffffff;
    font-weight: 600;
    letter-spacing: 1.2px;
    font-size: 18px;
    margin: 0 35px;
    text-transform: capitalize;
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
        text-decoration: none;
        padding: 10px 20px;
        background-color: #a77d54;
        color: #fff;
        border-radius: 5px;
    }

    nav ul li a:hover {
        background-color: #855b3a;
    }

    .transactions {
        padding: 20px;
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