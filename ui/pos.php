<?php
ob_start();
include_once '../API/connectdb.php';
session_start();

if ($_SESSION['useremail'] == "" or $_SESSION['role'] == "") {

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


ob_end_flush();


//$select = $pdo->prepare("select * from tbl_taxdis where taxdis_id =1");
//$select->execute();
//$row = $select->fetch(PDO::FETCH_OBJ);


?>


<style type="text/css">

  .tableFixHead {
    overflow: scroll;
    height: 520px;
  }

  .tableFixHead thead th {
    position: sticky;
    top: 0;
    z-index: 1;
  }

  table {
    border-collapse: collapse;
    width: 100px;
  }

  th, td {
    padding: 8px 16px;
  }

  th {
    background: #eee;
  }


</style>


<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
  <!-- Content Header (Page header) -->
  <div class="content-header">
    <div class="container-fluid">
      <div class="row mb-2">
        <div class="col-sm-6">
          <!-- <h1 class="m-0">Point Of Sale</h1> -->
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
              <h5 class="m-0" style="font-weight: bold">POS</h5>
            </div>


            <div class="card-body">

              <div class="row">


                <div class="col-md-8">

                  <div class="input-group mb-3">
                    <div class="input-group-prepend">
                      <span class="input-group-text"><i class="fa fa-barcode"></i></span>
                    </div>
                    <input type="text" class="form-control" placeholder="Scan Barcode" autocomplete="off"
                           name="txtbarcode" id="txtbarcode_id" autofocus>
                  </div>


                  <select class="form-control select2" data-dropdown-css-class="select2-purple" style="width: 100%;"
                          id="productsearch_id">
                    <option>Select OR Search</option><?php echo fill_product($pdo); ?>

                  </select>
                  </br>
                  <div class="tableFixHead">


                    <table id="producttable" class="table table-bordered table-hover">
                      <thead>
                      <tr>
                        <th>Product</th>
                        <th>Stock</th>
                        <th>price</th>
                        <th>QTY</th>
                        <th>Total</th>
                        <th>Del</th>
                      </tr>

                      </thead>


                      <tbody class="details" id="itemtable">
                      <tr data-widget="expandable-table" aria-expanded="false">

                      </tr>
                      </tbody>
                    </table>


                  </div>


                </div>


                <div class="col-md-4">
                  <div class="input-group mb-3">
                    <div class="input-group-prepend">
                      <span class="input-group-text">SUBTOTAL(Rs) </span>
                    </div>
                    <input type="text" class="form-control" name="txtsubtotal" id="txtsubtotal" readonly>
                    <div class="input-group-append">
                      <span class="input-group-text">Rs</span>
                    </div>
                  </div>


                  <div class="input-group mb-3">
                    <div class="input-group-prepend">
                      <span class="input-group-text">DISCOUNT(%)</span>
                    </div>
                    <input value="0.0" type="number" class="form-control" name="txtdiscount_p" id="txtdiscount_p">
                    <div class="input-group-append">
                      <span class="input-group-text">%</span>
                    </div>
                  </div>


                  <div class="input-group mb-3">
                    <div class="input-group-prepend">
                      <span class="input-group-text">DISCOUNT(Rs)</span>
                    </div>
                    <input type="text" class="form-control" id="txtdiscount" readonly>
                    <div class="input-group-append">
                      <span class="input-group-text">Rs</span>
                    </div>
                  </div>


                  <div class="input-group mb-4">
                    <div class="input-group-prepend">
                      <span class="input-group-text">TOTAL(Rs)</span>
                    </div>
                    <input type="text" class="form-control form-control total" name="txttotal" id="txttotal"
                           readonly>
                    <div class="input-group-append">
                      <span class="input-group-text">Rs</span>
                    </div>
                  </div>

                  <hr style="height:2px; border-width:0; color:black; background-color:black;">

                  <div class="icheck-success d-inline">
                    <input type="radio" name="rb" value="Cash" checked id="radioSuccess1">
                    <label for="radioSuccess1">
                      CASH
                    </label>
                  </div>
                  <div class="icheck-primary d-inline">
                    <input type="radio" name="rb" value="Card" id="radioSuccess2">
                    <label for="radioSuccess2">
                      CARD
                    </label>
                  </div>
                  <div class="icheck-danger d-inline">
                    <input type="radio" name="rb" value="Check" id="radioSuccess3">
                    <label for="radioSuccess3">
                      CHECK
                    </label>
                  </div>
                  <hr style="height:2px; border-width:0; color:black; background-color:black;">


                  <div class="input-group mb-3 mt-1">
                    <div class="input-group-prepend">
                      <span class="input-group-text">DUE(Rs)</span>
                    </div>
                    <input type="text" class="form-control" name="txtdue" id="txtdue" readonly>
                    <div class="input-group-append">
                      <span class="input-group-text">Rs</span>
                    </div>
                  </div>

                  <div class="input-group">
                    <div class="input-group-prepend">
                      <span class="input-group-text">PAID(Rs)</span>
                    </div>
                    <input type="number" class="form-control" name="txtpaid" id="txtpaid" required>
                    <div class="input-group-append">
                      <span class="input-group-text">Rs</span>
                    </div>
                  </div>
                  <hr style="height:2px; border-width:0; color:black; background-color:black;">

                  <div class="card-footer">


                    <div class="text-center">
                      <button type="submit" class="btn btn-success" name="btnsaveorder" id="placeOrderBtn"
                              onclick="placeOrder()">
                        Place Order
                      </button>
                    </div>
                  </div>


                </div>

              </div>

            </div>


          </div>


        </div>

        </form>

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
  function handleEnter(event, stock_id) {
    if (event.key === "Enter") {
      event.preventDefault();

      let qtyInput = document.getElementById("qty_id" + stock_id);
      let qtyValue = parseFloat(qtyInput.value); // Parse the input value as a float

      // Check if the entered value is a float or less than 1
      if (!Number.isInteger(qtyValue) || qtyValue < 1) {
        qtyInput.value = 1; // Set it to 1 if it's a float or less than 1
      }

      // Logic to determine the next element's ID
      let nextElementId = 'txtbarcode_id'; // Replace this with actual logic to find the next input ID
      document.getElementById(nextElementId).focus();
    }
  }

  function handleBlur(event, stock_id) {
    let qtyInput = document.getElementById("qty_id" + stock_id);
    let qtyValue = parseFloat(qtyInput.value); // Parse the input value as a float

    // Check if the entered value is a float or less than 1
    if (!Number.isInteger(qtyValue) || qtyValue < 1) {
      qtyInput.value = 1; // Set it to 1 if it's a float or less than 1
    }
  }




  let productCount = 0;

  //Initialize Select2 Elements
  $('.select2').select2();

  //Initialize Select2 Elements with Bootstrap 4 theme
  $('.select2bs4').select2({
    theme: 'bootstrap4'
  });

  let productarr = [];

  $(function () {
    productarr = []; // Keep track of products (by stock_id) added to the table

    $('#txtbarcode_id').on('change', function () {
      let barcode = $("#txtbarcode_id").val();
      console.log(productarr);

      $.ajax({
        url: "../API/posGetProductsByBarcode.php",
        method: "get",
        dataType: "json",
        data: {barcode: barcode},
        success: function (response) {
          console.log(response);

          $.each(response.data, function (index, item) {
            let stock_id = item.stock_id;

            if (jQuery.inArray(stock_id, productarr) !== -1) {
              let stock_quantity = parseInt($('#stock_qty' + stock_id).val());
              console.log(stock_quantity);

              if (stock_quantity > parseInt($('#qty_id' + stock_id).val())) {
                let actualqty = parseInt($('#qty_id' + stock_id).val()) + 1;
                $('#qty_id' + stock_id).val(actualqty);
                let saleprice = parseInt(actualqty) * item.saleprice;

                $('#total_raw_price_id' + stock_id).html(saleprice);
                $('#saleprice_idd' + stock_id).val(saleprice);

                calculate();
                updateOrderButtonState();
              }

            } else {
              addrow(item.pid, item.product, item.saleprice, item.stock, item.barcode, stock_id);
              productarr.push(stock_id);
            }
            console.log("qty_id" + stock_id);

            autoFocusOnElementById("qty_id" + stock_id);
          });

          $("#txtbarcode_id").val("");
          $("#qty_id + stock_id")

        }
      });
    });


    $('#productsearch_id').on('change', function () {
        let selectedID = $(this).val();
        if (selectedID) {
          $.ajax({
            url: "../API/posGetProductById.php",
            method: "get",
            dataType: "json",
            data: {pid: selectedID},
            success: function (response) {
              console.log(response);

              $.each(response.data, function (index, item) {
                  let stock_id = item.stock_id;

                  if (jQuery.inArray(stock_id, productarr) !== -1) {
                    let stock_quantity = parseInt($('#stock_qty' + stock_id).val());
                    console.log(stock_quantity);

                    if (stock_quantity > parseInt($('#qty_id' + stock_id).val())) {
                      let actualqty = parseInt($('#qty_id' + stock_id).val()) + 1;
                      $('#qty_id' + stock_id).val(actualqty);
                      let saleprice = parseInt(actualqty) * item.saleprice;

                      $('#total_raw_price_id' + stock_id).html(saleprice);
                      $('#saleprice_idd' + stock_id).val(saleprice);

                      calculate();
                      updateOrderButtonState();
                    }

                  } else {
                    addrow(item.pid, item.product, item.saleprice, item.stock, item.barcode, stock_id);
                    productarr.push(stock_id);
                  }
                  console.log("qty_id" + stock_id);

                  autoFocusOnElementById("qty_id" + stock_id);

                }
              )
              ;
              $("#productsearch_id").val('').trigger('change');


            }
          });
        }
      }
    )
    ;

    function autoFocusOnElementById(elementId) {
      document.getElementById(elementId).focus();
    }

    function addrow(pid, product, saleprice, stock, barcode, stock_id) {
      let tr =
        '<tr>' +
        '<input type="hidden" class="form-control barcode" name="barcode_arr[]" id="barcode_id' + stock_id + '" value="' + barcode + '" >' +
        '<td style="text-align:left; vertical-align:middle; font-size:17px;"><span class="badge badge-dark">' + product + '</span><input type="hidden" class="form-control pid" name="pid_arr[]" value="' + pid + '" ><input type="hidden" class="form-control product" name="product_arr[]" value="' + product + '" >  </td>' +
        '<td style="text-align:left; vertical-align:middle; font-size:17px;"><span class="badge badge-primary stocklbl" name="stock_arr[]" id="stock_id' + stock_id + '">' + stock + '</span><input type="hidden" class="form-control stock_id" name="stock_id_arr[]" id="stock_id' + stock_id + '" value="' + stock_id + '"><input type="hidden" class="form-control stock_qty" name="stock_qty_arr[]" id="stock_qty' + stock_id + '" value="' + stock + '"></td>' +
        '<td style="text-align:left; vertical-align:middle; font-size:17px;"><span class="badge badge-warning price" name="price_arr[]" id="price_id' + stock_id + '">' + saleprice + '</span></td>' +
        '<td><input style="width: 80px" type="number" class="form-control qty" name="quantity_arr[]" id="qty_id' + stock_id + '" size="1" min="1" onkeydown="handleEnter(event, ' + stock_id + ')" onblur="handleBlur(event, ' + stock_id + ')"></td>'+
        '<td style="text-align:left; vertical-align:middle; font-size:17px;"><span class="badge badge-success totalamt" name="netamt_arr[]" id="total_raw_price_id' + stock_id + '">' + saleprice + '</span><input type="hidden" class="form-control saleprice" name="saleprice_arr[]" id="saleprice_idd' + stock_id + '" value="' + saleprice + '"></td>' +
        '<td><center><button type="button" name="remove" class="btn btn-danger btn-sm btnremove" data-id="' + stock_id + '"><span class="fas fa-trash"></span></button></center></td>' +
        '</tr>';

      $('.details').append(tr);
      calculate();
      productCount++;  // Increment product count
      updateOrderButtonState();
    }

    updateOrderButtonState();

  });


  $("#itemtable").on("keyup change", ".qty", "keyup change", function () {

    let quantity = $(this);
    console.log("q" + quantity.val());
    let tr = $(this).parent().parent();

    if ((quantity.val() - 0) > (tr.find(".stock_qty").val() - 0)) {

      Swal.fire("WARNING!", "SORRY! This Much Of Quantity Is Not Available", "warning");
      quantity.val(1);

      tr.find(".totalamt").text(quantity.val() * tr.find(".price").text());

      tr.find(".saleprice").val(quantity.val() * tr.find(".price").text());
      calculate();
      updateOrderButtonState();

    } else {
      tr.find(".totalamt").text(quantity.val() * tr.find(".price").text());

      tr.find(".saleprice").val(quantity.val() * tr.find(".price").text());
      calculate();
      updateOrderButtonState();
    }
  });


  function calculate() {
    console.log("dic")

    let subtotal = 0;
    let total;
    let paid_amt = $("#txtpaid").val();
    let due = 0;

    $(".saleprice").each(function () {

      subtotal = subtotal + ($(this).val() * 1);
    });

    $("#txtsubtotal").val(subtotal.toFixed(2));

    let discount = parseFloat($("#txtdiscount_p").val());

    discount = discount / 100;
    discount = discount * subtotal;

    $("#txtdiscount").val(discount.toFixed(2));


    total = subtotal - discount;
    due = total - paid_amt;


    $("#txttotal").val(total.toFixed(2));

    $("#txtdue").val(due.toFixed(2));

  }  //end calculate function


  $("#txtdiscount_p").on('input', function () {
    calculate();
    updateOrderButtonState();
  });

  $("#txtpaid").on('input', function () {
    calculate();
    updateOrderButtonState();

  });


  $(document).on('click', '.btnremove', function () {
    let stock_id = $(this).attr("data-id");
    console.log(stock_id);

// Remove the stock_id from the productarr array
    productarr = jQuery.grep(productarr, function (value) {
      return value != stock_id;
    });

    console.log("remove");
    console.log(productarr);
    // Remove the row from the table
    $(this).closest('tr').remove();

    // Recalculate values if needed
    calculate();
    productCount--;  // Decrement product count

    updateOrderButtonState();

  });


  function updateOrderButtonState() {
    // Check if there are any products added
    let hasProducts = productCount > 0;
    // Check if the paid input is empty
    let validPaid = $("#txtpaid").val() && $("#txtdue").val() <= 0;


    // If no products or paid is empty, disable the button. Otherwise, enable it.
    $("#placeOrderBtn").prop("disabled", !hasProducts || !validPaid);
  }

  function placeOrder() {
    // Collect data

    let stock_ids = $('.stock_id').map(function () {
      return $(this).val();
    }).get();

    let quantities = $('.qty').map(function () {
      return $(this).val();
    }).get();
    //
    // let stock_quantities = $('.stock_qty').map(function () {
    //   return $(this).val();
    // }).get();


    // Convert datetime to GMT +5::30
    function addMinutes(date, minutes) {
      return new Date(date.getTime() + minutes * 60000);
    }

    let now = new Date();
    let gmtPlus530 = addMinutes(now, (5 * 60) + 30);
    let currentDateTime = gmtPlus530.toISOString().slice(0, 19).replace('T', ' ');


    let postData = {
      order_datetime: currentDateTime,
      sub_total: $('#txtsubtotal').val(),
      discount: $('#txtdiscount').val(),
      total: $('#txttotal').val(),
      payment_method: $('input[name=rb]:checked').val(),
      balance: $('#txtdue').val(),
      paid: $('#txtpaid').val(),
      stock_ids: stock_ids,
      quantities: quantities
      // stock_quantities: stock_quantities
    };

    console.log(postData);


    // Send data using AJAX
    $.post('../API/processOrder.php', postData, function (response) {
      let JSON_response = JSON.parse(response)
      console.log("Server Response:", JSON_response);

      if (JSON_response.success) {
        Swal.fire({
          icon: "success",
          title: "Order Processed Successfully"
        });
      }
        // Reload the current page
      // window.location.reload();
      else {
        Swal.fire({
          icon: "error",
          title: JSON_response.message
        });
      }
    });
  }


</script>