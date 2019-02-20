<?php echo $header; ?><?php echo $column_left; ?><?php echo $column_right; ?>
<div id="content"><?php echo $content_top; ?>
  <div class="breadcrumb">
    <?php foreach ($breadcrumbs as $breadcrumb) { ?>
    <?php echo $breadcrumb['separator']; ?><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a>
    <?php } ?>
  </div>
  <h1><?php echo $heading_title; ?></h1>

  <table cellpadding="5" border="1" style="border-collapse:collapse;border-color:#cccccc;border:1px solid #cccccc;" width="100%">
	<tr>
		<td width="200"><b>Description</b></td><td><?php echo $description; ?></td>
	</tr>
	<tr>
		<td><b>Bank</b></td><td><?php echo $bank_name; ?></td>
	</tr>
	<tr>
		<td><b>Virtual Account</b></td><td><?php echo $virtual_account; ?></td>
	</tr>
	<tr>
		<td><b>Expired Date</b></td><td><?php echo $expired_date; ?></td>
	</tr>
  </table>
  <br/>
  Pembayaran melalui gerai <?php echo $bank_name; ?> dapat dilakukan dengan mengikuti petunjuk berikut :
  <br/>
  <br/>
  <?php echo $bank_content; ?>
  <br/>
  <br/>
  <br/>
  <br/>
  <?php echo $text_message; ?>
  <div class="buttons">
    <div class="right"><a href="<?php echo $continue; ?>" class="button"><?php echo $button_continue; ?></a></div>
  </div>
  <?php echo $content_bottom; ?></div>
<?php echo $footer; ?>
