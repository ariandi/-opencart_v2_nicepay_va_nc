<?php echo $header; ?><?php echo $column_left; ?>
<div id="content" style="width: 60%; margin: auto">
    <div class="page-header">
        <div class="container-fluid">
            <div class="pull-right">
                <a href="<?php echo $continue; ?>" data-toggle="tooltip" title="Continue to home" class="btn btn-default"><i class="fa fa-reply"></i></a>
            </div>
            <h1><?php echo $heading_title; ?></h1>
            <ul class="breadcrumb">
                <?php foreach ($breadcrumbs as $breadcrumb) { ?>
                <li><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a></li>
                <?php } ?>
            </ul>
            <div class="panel-body">

  <table cellpadding="5" border="0" style="border-collapse:collapse;" width="100%" id="bayar">
  <tr>
    <td width="200"><b>Description</b></td><td><?php echo $description; ?></td>
  </tr>
  <tr>
    <td><b>Nama Mitra</b></td><td><?php echo $bank_name; ?></td>
  </tr>
  <tr>
    <td><b>Nama Penerima</b></td><td><?php echo $billing_name; ?></td>
  </tr>
  <tr>
    <td><b>Transaction ID</b></td><td><?php echo $transid; ?></td>
  </tr>
  <tr>
    <td><b>Pay No</b></td><td><?php echo $pay_no; ?></td>
  </tr>
  <tr>
    <td><b>Total Ammount</b></td><td><b><i><?php echo "Rp. ".number_format($transamount,2) ; ?></i></b></td>
  </tr>
  <tr>
    <td><b>Masa Berlaku</b></td><td><?php echo date('d-m-Y', strtotime($expired_date)); ?></td>
  </tr>
  </table>
  <br/>
  Pembayaran melalui Bank Transfer <?php echo $bank_name; ?> dapat dilakukan dengan mengikuti petunjuk berikut :
  <br/>
  <br/>
  <?php echo $bank_content; ?>
  <br/>
  <?php echo $text_message; ?>
  </div>
        </div>
    </div>

<style>
  
#bayar tr td{ padding-left: 1%; border: 1px inset #cccccc }
</style>


</div>
<?php echo $footer; ?>
