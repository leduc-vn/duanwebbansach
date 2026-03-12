<?php
require_once "connect.php";
if(isset($_GET["id"])) {
    $id = isset($_GET["id"]) ? trim($_GET["id"]):"";
    $sql = "DELETE FROM adproduct WHERE ma_sp = '$id'";
    mysqli_query($conn, $sql);
    header('location: sp.php');
     exit();
}
?>
