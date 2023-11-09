<?php

session_start();


if ($_SESSION['useremail'] == "" or $_SESSION['role'] == "User") {

  header('location:../index.php');

}


if ($_SESSION['role'] == "Admin") {
  include_once "header.php";
} else {
  include_once "headeruser.php";
}

if (isset($_POST['exportDbBtn'])) {
  $saleprice_txt = $_POST['txtsaleprice'];
  if ($purchaseprice_db > $saleprice_txt) {
    // Set session variable to indicate error and prevent DB update
    $_SESSION['status'] = "Selling Price Cannot be Less Than Purchase Price";
    $_SESSION['status_code'] = "error";
  } else {
    try {
      // Begin the transaction
      $pdo->beginTransaction();

      // Prepare the update statement
      $updateStock = $pdo->prepare("UPDATE Product_Stock SET saleprice=:sprice WHERE id=:stock_id");

      // Bind the parameters
      $updateStock->bindParam(':sprice', $saleprice_txt);
      $updateStock->bindParam(':stock_id', $stock_id);

      // Execute the update
      $updateStock->execute();

      // If we reach this point, it means that no exception was thrown
      // and the update was successful, so we can commit the transaction
      $pdo->commit();

      $_SESSION['status'] = "Product Stock Updated Successfully";
      $_SESSION['status_code'] = "success";

      echo '<script type="text/javascript">window.location.href="editstock.php?stock_id=' . $stock_id . '";</script>';
      exit;

    } catch (Exception $e) {
      // An exception has been thrown
      // Rollback the transaction to ensure data integrity
      $pdo->rollBack();

      $_SESSION['status'] = "Product Stock Update Failed";
      $_SESSION['status_code'] = "error";
      echo '<script type="text/javascript">window.location.href="editstock.php?stock_id=' . $stock_id . '";</script>';
      exit;
    }
  }
}

?>


<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
  <!-- Content Header (Page header) -->
  <div class="content-header">
    <div class="container-fluid">
      <div class="row mb-2">
        <div class="col-sm-6">
          <h1 class="m-0">Sync Database</h1>
        </div><!-- /.col -->
        <div class="col-sm-6">
          <ol class="breadcrumb float-sm-right">
            <!-- <li class="breadcrumb-item"><a href="#">Home</a></li>
            <li class="breadcrumb-item active">Starter Page</li> -->
          </ol>
        </div><!-- /.col -->
      </div><!-- /.row -->
    </div><!-- /.container-fluid -->
  </div>
  <!-- /.content-header -->

  <!-- Main content -->
  <div class="content">
    <div class="container-fluid">


      <div class="card card-primary card-outline">
        <div class="card-header">
          <h5 class="m-0">Sync Database</h5>
        </div>
        <div class="card-body">

          <div class="row">

            <button id="exportDbBtn" class="btn btn-primary" name="exportDbBtn" onclick="exportDB()">
              <span id="buttonText">Sync</span>
              <i id="loadingIcon" class="fa fa-spinner fa-spin" style="display: none;"></i>
            </button>
          </div>


        </div>
      </div>


    </div><!-- /.container-fluid -->
  </div>
  <!-- /.content -->
</div>
<!-- /.content-wrapper -->


<?php

include_once "footer.php";


?>

<?php
if (isset($_SESSION['status']) && $_SESSION['status'] != '') {

  ?>
  <script
    Swal.fire({
    icon: '<?php echo $_SESSION['status_code']; ?>',
  title: '<?php echo $_SESSION['status']; ?>'
  });
  </script>
  <?php
  unset($_SESSION['status']);
}
?>

<script>

  function exportDB() {
    // Send data using AJAX
    $.get('../API/exportDB.php', function (response) {
      let JSON_response = JSON.parse(response);
      console.log("Server Response:", JSON_response);

      if (JSON_response.success) {
        Swal.fire({
          icon: "success",
          title: "Order Processed Successfully"
        }).then((result) => {


          // window.location.reload();
          // Redirect to the printbill.php page with the invoice ID
          // window.location.href = `printbill.php?id=${JSON_response.invoice_id}`;

        });
      } else {
        Swal.fire({
          icon: "error",
          title: JSON_response.message
        });
      }
    });

    // // Show loading icon
    // $('#buttonText').hide();
    // $('#loadingIcon').show();
    //
    // // Perform AJAX requ
  }

</script>
