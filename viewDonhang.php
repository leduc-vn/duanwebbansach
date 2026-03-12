<?php
  require_once "view/header.php";
require_once "connect.php";
  require_once "view/header1.php";
  $id = $_GET['id'];
  $sql = "SELECT * FROM `order` WHERE `mahd`='$id'";
  $result = mysqli_query($conn, $sql);
  $res = getRes($result)[0];
  $idKh = $res["makh"];

  $sql2 = "SELECT * FROM `customer` where `makh`='$idKh'";
  $result2 = mysqli_query($conn, $sql2);
  $res2 = getRes($result2)[0];

  $sql3 = "SELECT * FROM `orderdetail` WHERE `mahd`='$id'";
  $result3 = mysqli_query($conn, $sql3);
  $res3 = getRes($result3);
  
  $stt = 1;
  $tt = 0;
?>
<div>
  <div>
    <table class="table">
      <thead>
        <th>STT</th>
        <th>Tên</th>
        <th>Đơn giá</th>
        <th>Số lượng</th>
        <th>Thành tiền</th>
      </thead>
      <tbody>
        <?php
        for($i = 0;$i<count($res3);$i++) {
            ?>
        <tr>
          <td><?php echo $stt;$stt++;?></td>
          <td><?php echo $res3[$i]["tensp"]?></td>
          <td><?php echo $res3[$i]["dongia"]?></td>
          <td><?php echo $res3[$i]["soluong"]?></td>
          <td>
            <?php echo ((int)$res3[$i]["soluong"] * (int)$res3[$i]["dongia"]);$tt+=((int)$res3[$i]["soluong"] * (int)$res3[$i]["dongia"])?>
          </td>
        </tr>

        <?php }?>
      </tbody>
    </table>
  </div>
  <br>
  <br>

  <div>
    <table class="table">
      <tr>
        <td>Tên khách hàng</td>
        <td><?php echo $res2["tenkh"]?></td>
      </tr>


      <tr>
        <td>SĐT</td>
        <td><?php echo $res2["phone"]?></td>
      </tr>

      <tr>
        <td>Email</td>
        <td><?php echo $res2["email"]?></td>
      </tr>

      <tr>
        <td>Địa chỉ liên hệ</td>
        <td><?php echo $res2["diachi_lienhe"]?></td>
      </tr>

      <tr>
        <td>Địa chỉ giao hàng</td>
        <td><?php echo $res2["diachi_giaohang"]?></td>
      </tr>
      <tr>
        <td>Trạng thái</td>
        <td>
          <?php if($res["trangthai"]==0) {  echo "chưa hoàn thành";
          } else if($res["trangthai"]== 1) { echo "đang giao hàng";
          } else { echo "hoàn thành";
          }
            // ? "chưa hoàn thành": $res["trangthai"] == 1 ? "": "đang giao hàng" ?>
        </td>
      </tr>
    </table>
  </div>
</div>

<?php require_once "./view/footer.php"; ?>
