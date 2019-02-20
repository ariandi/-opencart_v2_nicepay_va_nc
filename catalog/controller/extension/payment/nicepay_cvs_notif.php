<?php

require_once(DIR_SYSTEM . 'library/nicepay/NicepayLib.php');

class ControllerExtensionPaymentNicepayCvsNotif extends Controller {
  private function nicepay(){
      $nicepay = new NicepayLib();
      $nicepay->iMid = $this->config->get('nicepay_cvs_mid');
      $nicepay->merchantKey = $this->config->get('nicepay_cvs_key');
      return $nicepay;
  }

  public function index() {
    $nicepay = $this->nicepay();

    $this->language->load('payment/nicepay_cvs');
    $this->load->model('checkout/order');

    $order_id_arr = explode("#", $_POST['referenceNo']);
    
    $mToken = hash('sha256',   $this->config->get('nicepay_cvs_mid').
                                $_POST['tXid'].
                                $_POST['amt'].
                                $this->config->get('nicepay_cvs_key')
        );

    if( $_POST['merchantToken'] == $mToken ){
      $this->model_checkout_order->addOrderHistory(
                                                    end($order_id_arr), $this->config->get('nicepay_cvs_order_success_status'), 
                                                    $_POST['referenceNo'].' Telah Sukses di Bayarkan', false
                                                  );
    }
  }
}
