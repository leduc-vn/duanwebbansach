<?php
require_once "connect.php";
if(isset($_GET["id"])) {
    $id = isset($_GET["id"]) ? trim($_GET["id"]):"";
    $sql = "DELETE FROM adproducttype WHERE Ma_loaisp = '$id'";
    mysqli_query($conn, $sql);
    
    header('location: dongsp.php');
    exit();
}
?>
