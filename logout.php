<?php
// Khởi tạo session.
session_start();
// Hủy session hiện tại để đăng xuất.
session_destroy();
// Chuyển về trang đăng nhập.
header('Location: index.php');
exit;
?>