<?php
session_start();
session_unset(); 
session_destroy();

echo"<script>
    localStorage.removeItem('cart');
    localStorage.removeItem('liked');
    window.location.href = 'login_page.php'; 
</script>";
?>                     