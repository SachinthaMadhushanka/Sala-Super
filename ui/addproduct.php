<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include_once '../API/connectdb.php';
session_start();


if ($_SESSION['useremail'] == "" or $_SESSION['role'] == "User") {

  header('location:../index.php');

}

function fill_product($pdo)
{

  $output = '';
  $select = $pdo->prepare("select * from Product order by product asc");

  $select->execute();

  $result = $select->fetchAll();

  foreach ($result as $row) {
    $output .= '<option value="' . $row["pid"] . '">' . $row["product"] . '</option>';

  }

  return $output;

}

if ($_SESSION['role'] == "Admin") {
  include_once 'header.php';
} else {

  include_once 'headeruser.php';
}


if (isset($_POST['btnsave'])) {
  try {
    // Start the transaction
    $pdo->beginTransaction();

    $barcode = $_POST['txtbarcode'];
    $product = $_POST['txtproductname'];
    $catid = isset($_POST['txtproductcategoryselect']) ? $_POST['txtproductcategoryselect'] : -1;
    $description = $_POST['txtdescription'];
    $stock = $_POST['txtstock'];
    $purchaseprice = $_POST['txtpurchaseprice'];
    $saleprice = $_POST['txtsaleprice'];

    // When the barcode is not given
    if (empty($barcode)) {
      // Compare product name in the database
      $query = $pdo->prepare("SELECT * FROM Product WHERE LOWER(product) LIKE LOWER(:product)");
      $query->bindParam(':product', $product);
      $query->execute();
      if ($query->rowCount() == 0) { // No matching product found by name
        // Insert the product
        $insert = $pdo->prepare("INSERT INTO Product (product, catid, description) VALUES (:product, :catid, :description)");
        $insert->bindParam(':product', $product);
        $insert->bindParam(':catid', $catid);
        $insert->bindParam(':description', $description);
        $insert->execute();
        $pid = $pdo->lastInsertId();

        // Set default timezone to India Standard Time
        date_default_timezone_set("Asia/Kolkata");
        $newbarcode = $pid . date('his');
        // Update product with new barcode
        $update = $pdo->prepare("UPDATE Product SET barcode = :newbarcode WHERE pid = :pid");
        $update->bindParam(':newbarcode', $newbarcode);
        $update->bindParam(':pid', $pid);
        $update->execute();

        // Insert into Product_Stock
        $insertStock = $pdo->prepare("INSERT INTO Product_Stock (pid, stock, purchaseprice, saleprice) VALUES (:pid, :stock, :purchaseprice, :saleprice)");
        $insertStock->bindParam(':pid', $pid);
        $insertStock->bindParam(':stock', $stock);
        $insertStock->bindParam(':purchaseprice', $purchaseprice);
        $insertStock->bindParam(':saleprice', $saleprice);
        $insertStock->execute();

        // Insert into incoming_stock after updating Product_Stock (Barcode not given)
        $insertIncomingStock = $pdo->prepare("INSERT INTO incoming_stock (pid, stock, purchaseprice, saleprice, date_time) VALUES (:pid, :added_stock, :purchaseprice, :saleprice, NOW())");
        $insertIncomingStock->bindParam(':pid', $pid);
        $insertIncomingStock->bindParam(':added_stock', $stock); // Notice we're inserting the added stock, not the new total
        $insertIncomingStock->bindParam(':purchaseprice', $purchaseprice);
        $insertIncomingStock->bindParam(':saleprice', $saleprice);
        $insertIncomingStock->execute();


        // If we reach this point, all operations were successful
        $pdo->commit();
        $_SESSION['status'] = "Product Inserted Successfully";
        $_SESSION['status_code'] = "success";
      } else {
        // Product name already exists, roll back transaction
        $pdo->rollback();
        $_SESSION['status'] = "Product name already exists in the database.";
        $_SESSION['status_code'] = "error";
      }
    } else {
      // When barcode is given
      // Check if the given barcode already exists in the database
      $checkBarcode = $pdo->prepare("SELECT * FROM Product WHERE barcode = :barcode");
      $checkBarcode->bindParam(':barcode', $barcode);
      $checkBarcode->execute();

      if ($checkBarcode->rowCount() == 0) { // No matching barcode found
        // Check if the product name exists
        $checkProduct = $pdo->prepare("SELECT * FROM Product WHERE LOWER(product) = LOWER(:product)");
        $checkProduct->bindParam(':product', $product);
        $checkProduct->execute();

        if ($checkProduct->rowCount() == 0) { // No matching product found by name
          // Insert the product
          $insert = $pdo->prepare("INSERT INTO Product (barcode, product, catid, description) VALUES (:barcode, :product, :catid, :description)");
          $insert->bindParam(':barcode', $barcode);
          $insert->bindParam(':product', $product);
          $insert->bindParam(':catid', $catid);
          $insert->bindParam(':description', $description);
          $insert->execute();
          $pid = $pdo->lastInsertId();

          // Insert into Product_Stock
          $insertStock = $pdo->prepare("INSERT INTO Product_Stock (pid, stock, purchaseprice, saleprice) VALUES (:pid, :stock, :purchaseprice, :saleprice)");
          $insertStock->bindParam(':pid', $pid);
          $insertStock->bindParam(':stock', $stock);
          $insertStock->bindParam(':purchaseprice', $purchaseprice);
          $insertStock->bindParam(':saleprice', $saleprice);
          $insertStock->execute();

          // Insert into incoming_stock after updating Product_Stock (Barcode given, but it is new barcode)
          $insertIncomingStock = $pdo->prepare("INSERT INTO incoming_stock (pid, stock, purchaseprice, saleprice, date_time) VALUES (:pid, :added_stock, :purchaseprice, :saleprice, NOW())");
          $insertIncomingStock->bindParam(':pid', $pid);
          $insertIncomingStock->bindParam(':added_stock', $stock); // Notice we're inserting the added stock, not the new total
          $insertIncomingStock->bindParam(':purchaseprice', $purchaseprice);
          $insertIncomingStock->bindParam(':saleprice', $saleprice);
          $insertIncomingStock->execute();

          // Commit the transaction
          $pdo->commit();
          $_SESSION['status'] = "Product Inserted Successfully";
          $_SESSION['status_code'] = "success";
        } else {
          // Product name already exists, roll back transaction
          $pdo->rollback();
          $_SESSION['status'] = "Product name already exists in the database.";
          $_SESSION['status_code'] = "error";
        }
      } else {
        // Barcode exists in the database
        $barcodeRow = $checkBarcode->fetch(PDO::FETCH_ASSOC);
        $pid = $barcodeRow['pid'];

        $checkStock = $pdo->prepare("SELECT * FROM Product_Stock WHERE pid = :pid AND purchaseprice = :purchaseprice AND saleprice = :saleprice");
        $checkStock->bindParam(':pid', $pid);
        $checkStock->bindParam(':purchaseprice', $purchaseprice);
        $checkStock->bindParam(':saleprice', $saleprice);
        $checkStock->execute();

        if ($checkStock->rowCount() > 0) {
          // Matching PID with the same purchase price and sale price exists, update the stock
          $row = $checkStock->fetch(PDO::FETCH_ASSOC);
          $newStock = $row['stock'] + $stock;

          $updateStock = $pdo->prepare("UPDATE Product_Stock SET stock = :stock WHERE id = :id");
          $updateStock->bindParam(':stock', $newStock);
          $updateStock->bindParam(':id', $row['id']);
          $updateStock->execute();

          // Insert into incoming_stock after updating Product_Stock (Barcode given, it exists, stock exists with same purchase and sale price)
          $insertIncomingStock = $pdo->prepare("INSERT INTO incoming_stock (pid, stock, purchaseprice, saleprice, date_time) VALUES (:pid, :added_stock, :purchaseprice, :saleprice, NOW())");
          $insertIncomingStock->bindParam(':pid', $pid);
          $insertIncomingStock->bindParam(':added_stock', $stock); // Notice we're inserting the added stock, not the new total
          $insertIncomingStock->bindParam(':purchaseprice', $purchaseprice);
          $insertIncomingStock->bindParam(':saleprice', $saleprice);
          $insertIncomingStock->execute();

          // Commit the transaction
          $pdo->commit();
          $_SESSION['status'] = "Stock Updated Successfully";
          $_SESSION['status_code'] = "success";
        } else {
          // No matching PID with the given purchase and sale price, insert new stock entry
          $insertStock = $pdo->prepare("INSERT INTO Product_Stock (pid, stock, purchaseprice, saleprice) VALUES (:pid, :stock, :purchaseprice, :saleprice)");
          $insertStock->bindParam(':pid', $pid);
          $insertStock->bindParam(':stock', $stock);
          $insertStock->bindParam(':purchaseprice', $purchaseprice);
          $insertStock->bindParam(':saleprice', $saleprice);
          $insertStock->execute();

          // Insert into incoming_stock after updating Product_Stock (Barcode given, it exists, stock does not exist with same purchase and sale price)
          $insertIncomingStock = $pdo->prepare("INSERT INTO incoming_stock (pid, stock, purchaseprice, saleprice, date_time) VALUES (:pid, :added_stock, :purchaseprice, :saleprice, NOW())");
          $insertIncomingStock->bindParam(':pid', $pid);
          $insertIncomingStock->bindParam(':added_stock', $stock); // Notice we're inserting the added stock, not the new total
          $insertIncomingStock->bindParam(':purchaseprice', $purchaseprice);
          $insertIncomingStock->bindParam(':saleprice', $saleprice);
          $insertIncomingStock->execute();

          // Commit the transaction
          $pdo->commit();
          $_SESSION['status'] = "New Stock Inserted Successfully";
          $_SESSION['status_code'] = "success";
        }
      }
    }
  } catch (Exception $e) {
    // An error occurred, roll back the transaction
    $pdo->rollback();
    $_SESSION['status'] = "Transaction Failed: " . $e->getMessage();
    $_SESSION['status_code'] = "error";
  }

  // Redirect after operation
  echo '<script type="text/javascript">window.location.href="addproduct.php";</script>';
  exit;
}


?>

<style type="text/css">
  input::-webkit-outer-spin-button,
  input::-webkit-inner-spin-button {
    -webkit-appearance: none;
    margin: 0;
  }

  /* Firefox */
  input[type="number"] {
    -moz-appearance: textfield;
  }

</style>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
  <!-- Content Header (Page header) -->
  <div class="content-header">
    <div class="container-fluid">
      <div class="row mb-2">
        <div class="col-sm-6">
          <h1 class="m-0">Add Product</h1>
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
      <div class="row">
        <div class="col-lg-12">


          <div class="card card-primary card-outline">
            <div class="card-header">
              <h5 class="m-0">Product</h5>
            </div>


            <form action="" method="post" enctype="multipart/form-data">
              <div class="card-body">
                <div class="row">
                  <div class="col-md-6">

                    <div class="form-group">
                      <label>Barcode</label>
                      <input type="number" class="form-control" placeholder="Enter Barcode" name="txtbarcode"
                             id="txtbarcode_id" autocomplete="off" autofocus>
                    </div>

                    <!--                    <div class="form-group">-->
                    <!--                      <label>Product ID</label>-->
                    <!--                      <input type="text" class="form-control" placeholder="Enter Product ID" name="txtproduct"-->
                    <!--                             id="txtproduct_id" autocomplete="off">-->
                    <!--                    </div>-->

                    <div class="form-group">
                      <label>Search Product (Optional)</label>
                      <select class="form-control select2" data-dropdown-css-class="select2-purple"
                              style="width: 100%;" name="txtproductselect">
                        <option value="-1">New Product</option><?php echo fill_product($pdo); ?>

                      </select>
                    </div>


                    <div class="form-group">
                      <label>Product Name</label>
                      <input type="text" class="form-control" placeholder="Enter Name" name="txtproductname"
                             autocomplete="off" id="txtproductname_id" required>
                    </div>

                    <div class="form-group">
                      <label>Category</label>
                      <select class="form-control" name="txtproductcategoryselect" required>
                        <option value="" disabled selected>Select Category</option>

                        <?php
                        $select = $pdo->prepare("select * from Category order by catid desc");
                        $select->execute();

                        while ($row = $select->fetch(PDO::FETCH_ASSOC)) {
                          extract($row);

                          ?>
                          <option value="<?php echo $row['catid'] ?>"><?php echo $row['category']; ?></option>

                          <?php

                        }

                        ?>


                      </select>
                    </div>


                    <div class="form-group">
                      <label>Description</label>
                      <textarea class="form-control" placeholder="Enter Description" name="txtdescription" rows="4"
                                id="txtdescription_id"></textarea>
                    </div>


                  </div>


                  <div class="col-md-6">


                    <div class="form-group">
                      <label>Stock Quantity</label>
                      <input type="number" min="1" step="any" class="form-control" placeholder="Enter Stock"
                             name="txtstock" autocomplete="off" required>
                    </div>


                    <div class="form-group">
                      <label>Purchase Price</label>
                      <input type="number" min="1" step="any" class="form-control" placeholder="Enter Stock"
                             name="txtpurchaseprice" autocomplete="off" required>
                    </div>

                    <div class="form-group">
                      <label>Sale Price</label>
                      <input type="number" min="1" step="any" class="form-control" placeholder="Enter Stock"
                             name="txtsaleprice" autocomplete="off" required>
                    </div>

                  </div>


                </div>


              </div>

              <div class="card-footer">
                <div class="text-center">
                  <button type="submit" class="btn btn-primary" name="btnsave">Save Product</button>
                </div>
              </div>

            </form>


          </div>


        </div>
        <!-- /.col-md-6 -->
      </div>
      <!-- /.row -->
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
  <script>


    Swal.fire({
      icon: '<?php echo $_SESSION['status_code'];?>',
      title: '<?php echo $_SESSION['status'];?>'
    });

  </script>
  <?php
  unset($_SESSION['status']);
}
?>

<script>
  //Initialize Select2 Elements
  $('.select2').select2()

  //Initialize Select2 Elements
  $('.select2bs4').select2({
    theme: 'bootstrap4'
  })


  function onBarcodeChange() {
    console.log("changed barcode");

    var barcode = $("#txtbarcode_id").val();
    console.log(barcode);

    $.ajax({
      url: "../API/getproduct_by_barcode.php",
      method: "get",
      dataType: "json",
      data: {id: barcode},
      success: function (response) {


        console.log(response);

        $('select[name="txtproductselect"]').off('change', onProductSelectChange);

        // Check the status of the response
        if (response.status === 'success') {
          $('select[name="txtproductselect"]').val(response.data.pid.toString()).trigger('change').trigger('change.select2');
          $('#txtproductname_id').val(response.data.product).prop('readonly', true);
          $('select[name="txtproductcategoryselect"]').val(response.data.catid).prop('disabled', true);
          $('#txtdescription_id').val(response.data.description).prop('readonly', true);


        } else if (response.status === 'error') {
          // Clear the product ID field if the product is not found
          $('select[name="txtproductselect"]').val('-1').trigger('change').trigger('change.select2');
          $('#txtproductname_id').val('').prop('readonly', false);
          $('select[name="txtproductcategoryselect"]').val('').prop('disabled', false);
          $('#txtdescription_id').val('').prop('readonly', false);
        }

        $('select[name="txtproductselect"]').on('change', onProductSelectChange);

      }

    });
  }

  function onProductSelectChange() {
    console.log("changed product select");
    console.log($('select[name="txtproductselect"]').val())

    let pid = $('select[name="txtproductselect"]').val();

    $.ajax({
      url: "../API/getproduct_by_id.php",
      method: "get",
      dataType: "json",
      data: {id: pid},
      success: function (response) {

        console.log(response);

        // Check the status of the response
        if (response.status === 'success') {
          $("#txtbarcode_id").val(response.data.barcode);
          $('#txtproductname_id').val(response.data.product).prop('readonly', true);
          $('select[name="txtproductcategoryselect"]').val(response.data.catid).prop('disabled', true);
          $('#txtdescription_id').val(response.data.description).prop('readonly', true);


        } else if (response.status === 'error') {
          // Clear the product ID field if the product is not found
          $("#txtbarcode_id").val('');
          $('#txtproductname_id').val('').prop('readonly', false);
          $('select[name="txtproductcategoryselect"]').val('').prop('disabled', false);
          $('#txtdescription_id').val('').prop('readonly', false);

        }
      }
    });
  }

  $(function () {
    $('#txtbarcode_id').on('change', onBarcodeChange);
    $('select[name="txtproductselect"]').on('change', onProductSelectChange);

  });


</script>



