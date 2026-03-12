<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
copy($_POST['img'], './public/images/'.$_POST['name']);
echo json_encode(array('status'=> 'success','message'=> 'ok'));
?>
