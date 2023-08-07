<?php
/* PayPlus - Payment Gateway (Extension For OpenCart)
 * Ver. 1.0.0
 * Built By: PayPlus LTD - Development Department
 * All rights reserved © PayPlus LTD
 * Website: https://www.payplus.co.il
 * E-mail: service@payplus.co.il
*/
namespace Opencart\Admin\Controller\Extension\Payplus\Payment;
define('PAYPLUS_PAYMNET_URL_PRODUCTION','https://restapi.payplus.co.il/api/v1.0/');
define('PAYPLUS_PAYMNET_URL_DEV','https://restapidev.payplus.co.il/api/v1.0/');
class Payplus extends \Opencart\System\Engine\Controller  {
	private $version = '1.0.0';
	private $error = array();
    public  function get_url_api(){
        $apiURL =($this->config->get('payment_payplus_sandbox'))?PAYPLUS_PAYMNET_URL_DEV:PAYPLUS_PAYMNET_URL_PRODUCTION;
        return $apiURL;
    }
	public function index() {
		$this->load->language('extension/payment/payplus');
        $this->install();
		$this->document->setTitle($this->language->get('heading_title')); 
		
		$this->load->model('setting/setting');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && ($this->validate())) {

			$this->model_setting_setting->editSetting('payment_payplus', $this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');
		//	$this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment', true));
            $this->response->redirect($this->url->link('extension/payplus/payment/payplus'
                , 'user_token=' . $this->session->data['user_token'] , true));
		}

		
 		if (isset($this->error['warning'])) $data['error_warning'] = $this->error['warning'];
			else $data['error_warning'] = '';
		
		// [START] Check Validation

		if (isset($this->error['error_payplus_api_key_id'])) $data['error_payplus_api_key_id'] = $this->error['error_payplus_api_key_id'];
			else $data['error_payplus_api_key_id'] = '';

		if (isset($this->error['error_payplus_secret_key_id'])) $data['error_payplus_secret_key_id'] = $this->error['error_payplus_secret_key_id'];
			else $data['error_payplus_secret_key_id'] = '';

		if (isset($this->error['error_payplus_payment_page_uid_id'])) $data['error_payplus_payment_page_uid_id'] = $this->error['error_payplus_payment_page_uid_id'];
			else $data['error_payplus_payment_page_uid_id'] = '';

		// [END] Check Validation
		$data['has_ssl'] = !empty($this->request->server['HTTPS']);

  		$this->document->breadcrumbs = array();

		  $data['breadcrumbs'] = array();

		  $data['breadcrumbs'][] = array(
			  'text' => $this->language->get('text_home'),
			  'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
		  );
  
		  $data['breadcrumbs'][] = array(
			  'text' => $this->language->get('text_extension'),
			  'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment', true)
		  );
  
		  $data['breadcrumbs'][] = array(
			  'text' => $this->language->get('heading_title'),
			  'href' => $this->url->link('extension/payment/payplus', 'user_token=' . $this->session->data['user_token'], true)
		  );
				
		$data['action'] = $this->url->link('extension/payplus/payment/payplus', 'user_token=' . $this->session->data['user_token'], true);
		$data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment', true);
		
		// Settings Form

		if (isset($this->request->post['payment_payplus_api_key'])) $data['payment_payplus_api_key'] = $this->request->post['payment_payplus_api_key'];
			else $data['payment_payplus_api_key'] = $this->config->get('payment_payplus_api_key');

		if (isset($this->request->post['payment_payplus_secret_key'])) $data['payment_payplus_secret_key'] = $this->request->post['payment_payplus_secret_key'];
			else $data['payment_payplus_secret_key'] = $this->config->get('payment_payplus_secret_key');

		if (isset($this->request->post['payment_payplus_payment_page_uid'])) $data['payment_payplus_payment_page_uid'] = $this->request->post['payment_payplus_payment_page_uid'];
			else $data['payment_payplus_payment_page_uid'] = $this->config->get('payment_payplus_payment_page_uid');

		if (isset($this->request->post['payment_payplus_sandbox'])) $data['payment_payplus_sandbox'] = $this->request->post['payment_payplus_sandbox'];
			else $data['payment_payplus_sandbox'] = ($this->config->get('payment_payplus_sandbox') ? 'checked' : '');

		if (isset($this->request->post['payment_payplus_status'])) $data['payment_payplus_status'] = $this->request->post['payment_payplus_status'];
			else $data['payment_payplus_status'] = $this->config->get('payment_payplus_status');

		// -------------------------------------------------------------------------------------------------------------------------------------
		
		if (isset($this->request->post['payment_payplus_charge_method'])) $data['payment_payplus_charge_method'] = $this->request->post['payment_payplus_charge_method'];
			else {
				if ($this->config->get('payment_payplus_charge_method') == 1) $data['payment_payplus_charge_selected'] = 'selected';
					else if ($this->config->get('payment_payplus_charge_method') == 2) $data['payment_payplus_authorization_selected'] = 'selected';
			}

		if (isset($this->request->post['payment_payplus_display_mode'])) $data['payment_payplus_display_mode'] = $this->request->post['payment_payplus_display_mode'];
			else {
				if ($this->config->get('payment_payplus_display_mode') == "redirect") $data['payment_payplus_redirect_selected'] = 'selected';
					else $data['payment_payplus_iframe_selected'] = 'selected';
			}

		if (isset($this->request->post['payment_payplus_iframe_height'])) $data['payment_payplus_iframe_height'] = $this->request->post['payment_payplus_iframe_height'];
			else $data['payment_payplus_iframe_height'] = ($this->config->get('payment_payplus_iframe_height') ? $this->config->get('payment_payplus_iframe_height') : '700');

		$data['payment_payplus_ipn_url'] = HTTPS_CATALOG . 'index.php?route=extension/payment/payplus/ipn';
		
		// -------------------------------------------------------------------------------------------------------------------------------------
		
		if (isset($this->request->post['payment_payplus_order_status_id'])) $data['payment_payplus_order_status_id'] = $this->request->post['payment_payplus_order_status_id'];
			else $data['payment_payplus_order_status_id'] = $this->config->get('payment_payplus_order_status_id');

		if (isset($this->request->post['payment_payplus_failure_order_status_id'])) $data['payment_payplus_failure_order_status_id'] = $this->request->post['payment_payplus_failure_order_status_id'];
			else $data['payment_payplus_failure_order_status_id'] = $this->config->get('payment_payplus_failure_order_status_id');

		if (isset($this->request->post['payment_payplus_sort_order'])) $data['payment_payplus_sort_order'] = $this->request->post['payment_payplus_sort_order'];
			else $data['payment_payplus_sort_order'] = ($this->config->get('payment_payplus_sort_order') ? $this->config->get('payment_payplus_sort_order') : '1');
			
		if (isset($this->request->post['payment_payplus_store_cards'])) $data['payment_payplus_store_cards'] = $this->request->post['payment_payplus_store_cards'];
			else $data['payment_payplus_store_cards'] = $this->config->get('payment_payplus_store_cards');
			
		if (isset($this->request->post['payment_payplus_supported_currencies'])) $data['payment_payplus_supported_currencies'] = $this->request->post['payment_payplus_supported_currencies'];
			else $data['payment_payplus_supported_currencies'] = ($this->config->get('payment_payplus_supported_currencies') ? $this->config->get('payment_payplus_supported_currencies') : 'ILS,USD');
			
		if (isset($this->request->post['payment_payplus_logger'])) $data['payment_payplus_logger'] = $this->request->post['payment_payplus_logger'];
			else $data['payment_payplus_logger'] = ($this->config->get('payment_payplus_logger') ? 'checked' : '');
		
			// -------------------------------------------------------------------------------------------------------------------------------------

		$this->load->model('localisation/order_status');

		$data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/payment/payplus', $data));
	}
	
	public function install() {

		if ($this->user->hasPermission('modify','extension/payplus/payment/payplus')) {
			$this->load->model('extension/payplus/payment/payplus');
			$this->model_extension_payplus_payment_payplus->install();
		}
	}

	
	public function order() {
        
		$this->load->model('sale/order');
		$this->load->language('extension/payment/payplus');
		$this->load->model('extension/payplus/payment/payplus');
		
		$order_id=$this->request->get['order_id'];

		$data['original_order_info'] = $this->model_sale_order->getOrder($order_id);
		$data['payplus_order_info'] = $this->model_extension_payplus_payment_payplus->getOrder($order_id);

		
		$maxToCharge=$this->model_extension_payplus_payment_payplus->maxToCharge($order_id);
		$data['max_to_charge'] = ($maxToCharge > $data['original_order_info']['total'] ? (double)$data['original_order_info']['total'] : (double)$maxToCharge);
		$data['max_to_refund'] = (double)$this->model_extension_payplus_payment_payplus->maxToRefund($order_id);
		
		$data['charge_url'] = $this->url->link('extension/payment/payplus/charge', 'user_token=' . $this->session->data['user_token'].'&order_id='.$order_id, true);
		$data['refund_url'] = $this->url->link('extension/payplus/payment/payplus.refund', 'user_token=' . $this->session->data['user_token'].'&order_id='.$order_id, true);

		if (isset($this->request->get['pp'])) $data['tab_selected'] = trim($this->request->get['pp']);

		return $this->load->view('extension/payment/payplus_order', $data);
		}

	public function charge() {
		$this->load->model('extension/payplus/payment/payplus');
		$order_info = $this->model_extension_payplus_payment_payplus->getOrder($this->request->get['order_id']);
        $apiURL=$this->get_url_api().'Transactions/ChargeByTransactionUID';

        $payload = '{
			"transaction_uid": "'.$order_info['payplus_transaction_uid'].'",
			"amount": '.(double)$this->request->post['amount'].'
			}';

		$req=$this->pp_post($apiURL,$payload);
		$transaction_data = Array();
		$transaction_data['order_id'] = $order_info['order_id'];
		$transaction_data['type'] = 'charge';
		$transaction_data['error_code'] = $req->data->transaction->status_code;
		$transaction_data['error_description'] = $req->results->description;
		$transaction_data['amount'] = (double)$this->request->post['amount'];
		$useIPN = $this->ipn($req->data->transaction->uid);
		$transaction_data['response_log'] = print_r($useIPN,true);

		if ($req->data->transaction->status_code == "000" && $req->results->status == "success") {
			$transaction_data['payplus_transaction_uid'] = $req->data->transaction->uid;
			$transaction_data['status'] = 'Confirmed';
			$transaction_id=$this->model_extension_payplus_payment_payplus->addTransaction($order_info['customer_id'],$transaction_data);
			if ($this->config->get('payment_payplus_logger')) $this->log->write('PAYPLUS :: Transaction Charge Success: ' . print_r($req,true));
		} else {
			$transaction_data['status'] = 'Failed';
			$transaction_id=$this->model_extension_payplus_payment_payplus->addTransaction($order_info['customer_id'],$transaction_data);
			if ($this->config->get('payment_payplus_logger')) $this->log->write('PAYPLUS :: Transaction Charge Failed: ' . print_r($req,true));
		}
		$this->response->redirect($this->url->link('sale/order/info', 'user_token=' . $this->session->data['user_token'] . '&order_id='.$order_info['order_id'].'&pp='.$transaction_data['status'], true));
	}
	public function refund() {

        if(empty($this->request->get['order_id'])){
            $this->response->redirect($this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'] , true));
        }else{

            $this->load->model('extension/payplus/payment/payplus');
            $order_info = $this->model_extension_payplus_payment_payplus->getOrder($this->request->get['order_id']);
            $amount=(double)$this->request->post['amount'];
            $payload = '{
			"transaction_uid": "'.$this->request->post['transaction_uid'].'",
			"amount": '.$amount.'
			}';
            $apiURL=$this->get_url_api().'Transactions/RefundByTransactionUID';


            $tranz_by_payplus_uid=$this->model_extension_payplus_payment_payplus->getByPayPlusTransaction($this->request->post['transaction_uid']);

            $this->model_extension_payplus_payment_payplus->updatePayPlusTransaction($this->request->post['transaction_uid']);

            $req=$this->pp_post($apiURL,$payload);

            if($req->results->status=="success") {

                $transaction_data = array();
                $transaction_data['order_id'] = $order_info['order_id'];
                $transaction_data['type'] = 'refund';
                $transaction_data['error_code'] = $req->data->transaction->status_code;
                $transaction_data['error_description'] = $req->results->description;
                $transaction_data['amount'] = $amount;
                $transaction_data['refund_original'] = $this->request->post['transaction_uid'];
                $useIPN = $this->ipn($this->request->post['transaction_uid']);
                $transaction_data['response_log'] = print_r($req,true);


                if ($req->data->transaction->status_code == "000" && $req->results->status == "success") {
                    $transaction_data['payplus_transaction_uid'] = $req->data->transaction->uid;
                    $transaction_data['status'] = 'Confirmed';
                    if ($this->config->get('payment_payplus_logger')) $this->log->write('PAYPLUS :: Transaction Refund Success: ' . print_r($req, true));
                } else {
                    $transaction_data['status'] = 'Failed';
                    if ($this->config->get('payment_payplus_logger')) $this->log->write('PAYPLUS :: Transaction Refund Failed: ' . print_r($req, true));
                }
                $transaction_id = $this->model_extension_payplus_payment_payplus->addTransaction($order_info['customer_id'], $transaction_data);
                $this->response->redirect($this->url->link('sale/order.info', 'user_token=' . $this->session->data['user_token'] . '&order_id=' . $order_info['order_id'] . '&pp=' . $transaction_data['status'], true));
            }else{
                $this->response->redirect($this->url->link('sale/order.info', 'user_token=' . $this->session->data['user_token'] . '&order_id=' . $order_info['order_id'] . '&pp=error' , true));
            }
        }

		
	}

	private function ipn($transaction_uid='') {
		if (empty($transaction_uid)) return false;
		
		if ($this->config->get('payment_payplus_sandbox')) $ipnURL = 'https://restapidev.payplus.co.il/api/v1.0/PaymentPages/ipn';
			else $ipnURL = 'https://restapi.payplus.co.il/api/v1.0/PaymentPages/ipn';
	
		$payload = '{
			"transaction_uid": "'.$transaction_uid.'"
			}';
		$res=$this->pp_post($ipnURL,$payload);


		if ( $res->data->status == "approved" && $res->data->status_code == "000" && $res->results->status == "success" ) {
			return $res->data;
			}
		return false;
		}

	private function pp_post($url,$payload) {
		$ch = curl_init($url);
		$headers = array('Content-Type: application/json','Authorization:{"api_key":"'.$this->config->get('payment_payplus_api_key').'","secret_key":"'.$this->config->get('payment_payplus_secret_key').'"}');
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_USERAGENT, 'OpenCart');
		curl_setopt($ch, CURLOPT_HEADER, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT, 30);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		$return = curl_exec($ch);
		
		$header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
		$header = substr($return, 0, $header_size);
		$body = json_decode(substr($return, $header_size));
		curl_close($ch);
		
		return $body;
		
		}

	private function validate() {
		if (!$this->user->hasPermission('modify', 'extension/payplus/payment/payplus')) $this->error['warning'] = $this->language->get('error_permission');
		
		if (!$this->request->post['payment_payplus_api_key']) $this->error['error_payplus_api_key_id'] = $this->language->get('error_text_payplus_api_key_id');
		if (!$this->request->post['payment_payplus_secret_key']) $this->error['error_payplus_secret_key_id'] = $this->language->get('error_text_payplus_secret_key_id');
		if (!$this->request->post['payment_payplus_payment_page_uid']) $this->error['error_payplus_payment_page_uid_id'] = $this->language->get('error_text_payplus_payment_page_uid_id');

		return !$this->error;
	}

}
