<form action="<?php echo $action; ?>" method="post">  
  <input type="hidden" name="aksi" value="bayar" />
  <input type="hidden" name="orderid" value="<?php echo $nicepay_cvs_order_id; ?>" />
  <div class="content">
	Choose Store Name :
	<select name="mitraCd" required >
		<option value=""> - </option>
		<option value="ALMA"> Alfamart </option>
		<option value="INDO"> Indomaret </option>
	</select>
  </div>
  <div class="buttons">
    <div class="right">
      <input type="submit" value="<?php echo $button_confirm; ?>" class="button" />
    </div>
  </div>
</form>
