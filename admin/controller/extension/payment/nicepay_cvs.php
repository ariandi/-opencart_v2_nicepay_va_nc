<?php
class ControllerExtensionPaymentNicepayCvs extends Controller {
  private $error = array();

  public function index() {
    $this->load->language('extension/payment/nicepay_cvs');

    $this->document->setTitle($this->language->get('heading_title'));

    $this->load->model('setting/setting');

    if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {

      $this->model_setting_setting->editSetting('nicepay_cvs', $this->request->post);

      $this->session->data['success'] = $this->language->get('text_success');

      $this->response->redirect($this->url->link('extension/extension', 'token=' . $this->session->data['token'] . '&type=payment', true));
    }

    $data['heading_title'] = $this->language->get('heading_title');

    $data['text_edit'] = $this->language->get('text_edit');
    $data['text_enabled'] = $this->language->get('text_enabled');
    $data['text_disabled'] = $this->language->get('text_disabled');
    $data['text_all_zones'] = $this->language->get('text_all_zones');
    $data['text_yes'] = $this->language->get('text_yes');
    $data['text_no'] = $this->language->get('text_no');

    $data['entry_merchant'] = $this->language->get('entry_merchant');
    $data['entry_security'] = $this->language->get('entry_security');
    $data['entry_dbProccessUrl'] = $this->language->get('entry_dbProccessUrl');
    $data['entry_order_success_status'] = $this->language->get('entry_order_success_status');

    $data['entry_display'] = $this->language->get('entry_display');
    $data['entry_test'] = $this->language->get('entry_test');
    // $data['entry_total'] = $this->language->get('entry_total');
    $data['entry_order_status'] = $this->language->get('entry_order_status');
    $data['entry_geo_zone'] = $this->language->get('entry_geo_zone');
    $data['entry_status'] = $this->language->get('entry_status');
    $data['entry_sort_order'] = $this->language->get('entry_sort_order');

    $data['help_secret'] = $this->language->get('help_secret');
    // $data['help_total'] = $this->language->get('help_total');

    $data['button_save'] = $this->language->get('button_save');
    $data['button_cancel'] = $this->language->get('button_cancel');

    if (isset($this->error['warning'])) {
      $data['error_warning'] = $this->error['warning'];
    } else {
      $data['error_warning'] = '';
    }

    if (isset($this->error['account'])) {
      $data['error_account'] = $this->error['account'];
    } else {
      $data['error_account'] = '';
    }

    if (isset($this->error['secret'])) {
      $data['error_secret'] = $this->error['secret'];
    } else {
      $data['error_secret'] = '';
    }

    $data['breadcrumbs'] = array();

    $data['breadcrumbs'][] = array(
      'text' => $this->language->get('text_home'),
      'href' => $this->url->link('common/dashboard', 'token=' . $this->session->data['token'], true)
    );

    $data['breadcrumbs'][] = array(
      'text' => $this->language->get('text_extension'),
      'href' => $this->url->link('extension/extension', 'token=' . $this->session->data['token'] . '&type=payment', true)
    );

    $data['breadcrumbs'][] = array(
      'text' => $this->language->get('heading_title'),
      'href' => $this->url->link('extension/payment/nicepay_cvs', 'token=' . $this->session->data['token'], true)
    );

    $data['action'] = $this->url->link('extension/payment/nicepay_cvs', 'token=' . $this->session->data['token'], true);

    $data['cancel'] = $this->url->link('extension/extension', 'token=' . $this->session->data['token'] . '&type=payment', true);

    if (isset($this->request->post['nicepay_cvs_mid'])) {
      $data['nicepay_cvs_mid'] = $this->request->post['nicepay_cvs_mid'];
    } else {
      $data['nicepay_cvs_mid'] = $this->config->get('nicepay_cvs_mid');
    }

    if (isset($this->request->post['nicepay_cvs_key'])) {
      $data['nicepay_cvs_key'] = $this->request->post['nicepay_cvs_key'];
    } else {
      $data['nicepay_cvs_key'] = $this->config->get('nicepay_cvs_key');
    }

    if (isset($this->request->post['nicepay_cvs_db_proccess'])) {
      $data['nicepay_cvs_db_proccess'] = $this->request->post['nicepay_cvs_db_proccess'];
    } else {
      $data['nicepay_cvs_db_proccess'] = $this->config->get('nicepay_cvs_db_proccess');
    }

    if (isset($this->request->post['nicepay_cvs_order_status_id'])) {
      $data['nicepay_cvs_order_status_id'] = $this->request->post['nicepay_cvs_order_status_id'];
    } else {
      $data['nicepay_cvs_order_status_id'] = $this->config->get('nicepay_cvs_order_status_id');
    }

    if (isset($this->request->post['nicepay_cvs_order_success_status'])) {
      $data['nicepay_cvs_order_success_status'] = $this->request->post['nicepay_cvs_order_success_status'];
    } else {
      $data['nicepay_cvs_order_success_status'] = $this->config->get('nicepay_cvs_order_success_status');
    }

    $this->load->model('localisation/order_status');

    $data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();

    if (isset($this->request->post['nicepay_cvs_geo_zone_id'])) {
      $data['nicepay_cvs_geo_zone_id'] = $this->request->post['nicepay_cvs_geo_zone_id'];
    } else {
      $data['nicepay_cvs_geo_zone_id'] = $this->config->get('nicepay_cvs_geo_zone_id');
    }

    $this->load->model('localisation/geo_zone');

    $data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();

    if (isset($this->request->post['nicepay_cvs_status'])) {
      $data['nicepay_cvs_status'] = $this->request->post['nicepay_cvs_status'];
    } else {
      $data['nicepay_cvs_status'] = $this->config->get('nicepay_cvs_status');
    }

    if (isset($this->request->post['nicepay_cvs_sort_order'])) {
      $data['nicepay_cvs_sort_order'] = $this->request->post['nicepay_cvs_sort_order'];
    } else {
      $data['nicepay_cvs_sort_order'] = $this->config->get('nicepay_cvs_sort_order');
    }

    $data['header'] = $this->load->controller('common/header');
    $data['column_left'] = $this->load->controller('common/column_left');
    $data['footer'] = $this->load->controller('common/footer');

    $this->response->setOutput($this->load->view('extension/payment/nicepay_cvs', $data));
  }

  protected function validate() {
    if (!$this->user->hasPermission('modify', 'extension/payment/nicepay_cvs')) {
      $this->error['warning'] = $this->language->get('error_permission');
    }

    if (!$this->request->post['nicepay_cvs_mid']) {
      $this->error['account'] = $this->language->get('error_nicepay_account');
    }

    if (!$this->request->post['nicepay_cvs_key']) {
      $this->error['secret'] = $this->language->get('error_nicepay_secret');
    }

    return !$this->error;
  }
}
