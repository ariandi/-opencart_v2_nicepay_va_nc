<?php

require_once(DIR_SYSTEM . 'library/nicepay/NicepayLib.php');

class ControllerExtensionPaymentNicepayCvs extends Controller {
  private function nicepay(){
      $nicepay = new NicepayLib();
      $nicepay->iMid = $this->config->get('nicepay_cvs_mid');
      $nicepay->merchantKey = $this->config->get('nicepay_cvs_key');
      return $nicepay;
  }

  public function index() {
    $nicepay = $this->nicepay();

    $this->language->load('payment/nicepay_cvs');

    $data['button_confirm'] = $this->language->get('button_confirm');

    $this->load->model('checkout/order');

    $order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);

    $data['action'] = $this->url->link('extension/payment/nicepay_cvs/send');
    $data['url_web'] = $this->url;

    // $data['sid'] = $this->config->get('nicepay_cvs_mid');
    $data['currency_code'] = $order_info['currency_code'];
    $data['amount'] = $this->currency->format($order_info['total'], $order_info['currency_code'], $order_info['currency_value'], false);
    $data['nicepay_cvs_order_id'] = $this->session->data['order_id'];

    return $this->load->view('extension/payment/nicepay_cvs', $data);
  }

  public function send() {
    $this->load->model('checkout/order');
    $data['errors'] = array();

    $order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);

    $transaction_details = array();
    $transaction_details['mallId'] = $this->config->get('nicepay_cvs_mid');
    $transaction_details['invoiceNo'] = 'INV-NO:#' . $this->session->data['order_id'];
    $transaction_details['amount'] = (int)$order_info['total'];
    $transaction_details['currencyCode'] = 360;

    $products = $this->cart->getProducts();
    foreach ($products as $product) {
      if (($this->config->get('config_customer_price') && $this->customer->isLogged()) || ! $this->config->get('config_customer_price')) {
        $product['price'] = $this->tax->calculate(
          $product['price'], $product['tax_class_id'], $this->config->get('config_tax'));
      }

      $orderInfo[] = array(
          'img_url' => HTTPS_SERVER. "image/". $product['image'],
          'goods_name' => $product['name'],
          'goods_detail' => $product['model']." x".$product['quantity']." item",
          'goods_amt' => (int)($product['price'] * $product['quantity'])
      );
    }

    if ($this->cart->hasShipping()) {
      $shipping_info = $this->session->data['shipping_method'];
      if (($this->config->get('config_customer_price') && $this->customer->isLogged()) || ! $this->config->get('config_customer_price')) {
        $shipping_info['cost'] = $this->tax->calculate(
        $shipping_info['cost'],
        $shipping_info['tax_class_id'],
        $this->config->get('config_tax'));
      }

      $orderInfo[] = array(
          'img_url' => HTTPS_SERVER. "image/nicepay/png/delivery.png",
          'goods_name' => "SHIPPING",
          'goods_detail' => 1,
          'goods_amt' => $shipping_info['cost']
      );
    }

    if ($this->config->get('config_currency') != 'IDR') {
      if ($this->currency->has('IDR')) {
        foreach ($orderInfo as &$item) {
          $item['goods_amt'] = intval($this->currency->convert(
            $item['goods_amt'], $this->config->get('config_currency'), 'IDR'));
        }

        unset($item);

        $transaction_details['amount'] = intval($this->currency->convert(
          $transaction_details['amount'],
          $this->config->get('config_currency'),
          'IDR'
        ));
      } else {
        $data['errors'][] = 'Currency IDR tidak terinstall atau Nicepay currency conversion rate tidak valid. Silahkan check option kurs dollar.';die;
      }
    }

    $total_price = 0;
    foreach ($orderInfo as $item) {
      $total_price += $item['goods_amt'];
    }

    if ($total_price != $transaction_details['amount']) {
      $orderInfo[] = array(
          'img_url' => HTTPS_SERVER. "image/nicepay/png/coupon.png",
          'goods_name' => "COUPON",
          'goods_detail' => 1,
          'goods_amt' => $transaction_details['amount'] - $total_price
      );
    }

    $order_total = $order_info['total'];

    $totamt = intval($order_total);

    $cartData = array(
        "count" => count($orderInfo),
        "item" => $orderInfo
    );
    
    $order_id = $this->session->data['order_id'];

    $billingNm = $order_info['payment_firstname']." ".$order_info['payment_lastname'];
    $billingEmail = $order_info['email'];
    $billingPhone = $order_info['telephone'];
    $billingAddr = ($order_info['payment_address_1'] == null) ? "-" : $order_info['payment_address_1'];
    
    $billingCountry = ($order_info['payment_iso_code_2'] == null) ? "-" : $order_info['payment_iso_code_2'];
    $billingState = ($order_info['payment_zone'] == null) ? "-" : $order_info['payment_zone'];
    $billingCity = ($order_info['payment_city'] == null) ? "-" : $order_info['payment_city'];
    $billingPostCd = ($order_info['payment_postcode'] == null) ? "-" : $order_info['payment_postcode'];

    $deliveryNm = ($order_info['shipping_firstname'] == null && $order_info['shipping_lastname'] == null) ? $billingNm : $order_info['shipping_firstname'] ." ". $order_info['shipping_lastname'];
    $deliveryAddr = ($order_info['shipping_address_1'] == null ) ? $billingAddr : $order_info['shipping_address_1'];
    $deliveryCity = ($order_info['shipping_city'] == null) ? $billingCity : $order_info['shipping_city'];
    $deliveryCountry = ($order_info['shipping_country'] == null) ? $billingCountry : $order_info['shipping_country'];
    $deliveryState = ($order_info['shipping_zone'] == null) ? $billingState : $order_info['shipping_zone'];
    $deliveryEmail = ($order_info['email'] == null) ? $billingEmail : $order_info['email'];
    $deliveryPhone = ($order_info['telephone'] == null) ? $billingPhone : $order_info['telephone'];
    $deliveryPostCd = ($order_info['shipping_postcode'] == null) ? $billingPostCd : $order_info['shipping_postcode'];
        
    // Prepare Parameters
    $nicepay = $this->nicepay();

    // Populate Mandatory parameters to send
    $dateNow        = date('Ymd');
    $vaExpiryDate   = date('Ymd', strtotime($dateNow . ' +1 day'));
    $nicepay->set('iMid', $this->config->get('nicepay_cvs_mid'));
    $nicepay->set('merchantKey', $this->config->get('nicepay_cvs_key'));
    $nicepay->set('dbProcessUrl', $this->config->get('nicepay_cvs_db_proccess'));
    $nicepay->set('payMethod', '03');
    $nicepay->set('currency', 'IDR');
    $nicepay->set('cartData', json_encode($cartData));
    $nicepay->set('amt', strval($totamt)); // Total gross amount //
    $nicepay->set('referenceNo', 'RefNo:#'.$order_id);
    $nicepay->set('description', 'Payment of invoice No '.$order_id); // Transaction description
    $nicepay->set('mitraCd', $this->request->post['mitraCd']);

    $nicepay->dbProcessUrl = HTTP_SERVER . 'catalog/controller/payment/nicepay_cvs_response.php';
    $nicepay->set('billingNm', $billingNm); // Customer name
    $nicepay->set('billingPhone', $billingPhone); // Customer phone number
    $nicepay->set('billingEmail', $billingEmail); //
    $nicepay->set('billingAddr', $billingAddr);
    $nicepay->set('billingCity', $billingCity);
    $nicepay->set('billingState', $billingState);
    $nicepay->set('billingPostCd', $billingPostCd);
    $nicepay->set('billingCountry', $billingCountry);

    $nicepay->set('deliveryNm', $deliveryNm); // Delivery name
    $nicepay->set('deliveryPhone', $deliveryPhone);
    $nicepay->set('deliveryEmail', $deliveryEmail);
    $nicepay->set('deliveryAddr', $deliveryAddr);
    $nicepay->set('deliveryCity', $deliveryCity);
    $nicepay->set('deliveryState', $deliveryState);
    $nicepay->set('deliveryPostCd', $deliveryPostCd);
    $nicepay->set('deliveryCountry', $deliveryCountry);

    $nicepay->set('vacctVaildDt', $vaExpiryDate); // Set VA expiry date example: +1 day
    $nicepay->set('vacctVaildTm', date('His')); // Set VA Expiry Time

    // Send Data
    $response = $nicepay->requestCVS();

    // Response from NICEPAY
    if (isset($response->resultCd) && $response->resultCd == "0000") {

      $this->session->data["description"] = "Payment of invoice No ".$response->referenceNo;
      $this->session->data["tXid"] = $response->tXid;
      $this->session->data["bank"] = $this->mitra_info($this->request->post['mitraCd'])["label"];
      $this->session->data["bankContent"] = $this->mitra_info($this->request->post['mitraCd'])["content"];
      $this->session->data["payNo"] = $response->payNo;
      $this->session->data["amount"] = $response->amount;
      $this->session->data["expDate"] = $response->payValidDt.' '.$response->payValidTm;
      $this->session->data["billingEmail"] = $billingEmail;
      $this->session->data["billingNm"] = $billingNm;
    
        $this->model_checkout_order->addOrderHistory($this->session->data['order_id'], $this->config->get('nicepay_cvs_order_status_id'), 'Payment was made using NicePay CVS Payment. Order Invoice ID is '.$order_info['invoice_prefix'].$order_info['order_id'].'. Transaction ID is '.$response->tXid, false);

      $this->response->redirect($this->url->link('extension/payment/nicepay_cvs/success&'.http_build_query($response), 'SSL'));

    } elseif(isset($response->resultCd)) {
        // API data not correct or error happened in bank system, you can redirect back to checkout page or echo error message.
        // In this sample, we echo error message
        // header("Location: "."http://example.com/checkout.php");
        echo "<pre>";
        echo "result code       : ".$response->resultCd."\n";
        echo "result message    : ".$response->resultMsg."\n";
        // echo "requestUrl        : ".$response->data->requestURL."\n";
        echo "</pre>";
    } else {
        // Timeout, you can redirect back to checkout page or echo error message.
        // In this sample, we echo error message
        // header("Location: "."http://example.com/checkout.php");
        echo "<pre>Connection Timeout. Please Try again.</pre>";
    }
  }

  public function success() {
    // $url = "http://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
    // $remove = preg_replace('/[?&]foo=[^&]+$|([?&])route=[^&]+&/', '$1', $url);
    $this->cart->clear();   
    $this->response->redirect($this->url->link('checkout/nicepay_cvs_success', parse_url($remove, PHP_URL_QUERY), 'SSL'));
  }

  public function mitra_info($mitraCd){
      
      $header = '
            <html>
            <body>

            <style>
            input[type="button"]{
              border : 2px solid;
              width : 100%;
            }
            </style>
            ';

            $footer = '
            <script>
            function atm() {
                var div_atm = document.getElementById("div_atm").style.display;
                if(div_atm == "block"){
                  document.getElementById("div_atm").style.display = "none";
                }else{
                  document.getElementById("div_atm").style.display = "block";
                }
            }
            function ib() {
                var div_ib = document.getElementById("div_ib").style.display;
                if(div_ib == "block"){
                  document.getElementById("div_ib").style.display = "none";
                }else{
                  document.getElementById("div_ib").style.display = "block";
                }
            }
            function mb() {
                var div_mb = document.getElementById("div_mb").style.display;
                if(div_mb == "block"){
                  document.getElementById("div_mb").style.display = "none";
                }else{
                  document.getElementById("div_mb").style.display = "block";
                }
            }
            function sms() {
                var div_sms = document.getElementById("div_sms").style.display;
                if(div_sms == "block"){
                  document.getElementById("div_sms").style.display = "none";
                }else{
                  document.getElementById("div_sms").style.display = "block";
                }
            }
            </script>

            </body>
            </html>
            ';

            $data = null;

            switch($mitraCd)
            {
                case "ALMA" :
                $body = '
                <strong id="h4thanks"><input type="button" onclick="atm();" value="Bayar Melalui Alfa Group"></strong>

                <div id="div_atm" style="border: 2px solid rgb(204, 204, 204); padding: 10px 30px 0px; display: block;">
                  <ul style="list-style-type: disc">
                    <li>Pilih pembayaran melalui Alfamart / Alfamidi / Dan+Dan/ Lawson</li>
                    <li>Catat atau print kode pembayaran</li>
                    <li>Bawa kode pembayaran tersebut ke gerai Alfamart / Alfamidi / Dan+Dan / Lawson</li>
                    <li>Informasikan kepada kasir pembayaran menggunakan NICEPay + Nama Merchant</li>
                    <li>Berikan kode pembayaran ke kasir</li>
                    <li>Kasir akan memasukkan kode pembayaran</li>
                    <li>Bayar sesuai nominal</li>
                    <li>Ambil tanda terima pembayaran</li>
                    <li>Selesai</li>
                  </ul>
                </div>

                <br />
                ';

                $data["content"] = "$header$body$footer";
                $data["label"] = "Alfamart";
                break;

                case "INDO" :
                $body = '
                <strong id="h4thanks"><input type="button" onclick="atm();" value="Bayar Melalui Indomaret"></strong>

                <div id="div_atm" style="border: 2px solid rgb(204, 204, 204); padding: 10px 30px 0px; display: block;">
                  <ul style="list-style-type: disc">
                    <li>Pilih pembayaran melalui INDOMARET</li>
                    <li>Catat atau print kode pembayaran</li>
                    <li>Bawa kode pembayaran tersebut ke gerai INDOMARET</li>
                    <li>Informasikan Nama Merchant ke kasir</li>
                    <li>Berikan kode pembayaran ke kasir</li>
                    <li>Kasir akan memasukkan kode pembayaran</li>
                    <li>Bayar sesuai nominal</li>
                    <li>Ambil tanda terima pembayaran</li>
                    <li>Selesai</li>
                  </ul>
                </div>
                <br />
                ';

                $data["content"] = "$header$body$footer";
                $data["label"] = "Indomaret";
                break;

              
            }

            return $data;
  }
}
