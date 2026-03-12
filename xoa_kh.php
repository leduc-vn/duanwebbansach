<?php
require_once("connect.php");
if(isset($_GET["id"]))
{
	$id = isset($_GET["id"])?trim($_GET["id"]):"";
	$sql = "DELETE FROM customer WHERE makh = '$id'";
	//$sql1 = "DELETE FROM dathang WHERE mahd = '$id'";
	//$sql2 = "DELETE FROM orderdetail WHERE mahd = '$mahd'";
	mysqli_query($conn,$sql);
	header('Location: kh.php');
	//header('Location: dathang.php');
	//header('Location: orderdetail.php');
}
?>