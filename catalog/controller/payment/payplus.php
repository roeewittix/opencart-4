<?php
/* PayPlus - Payment Gateway (Extension For OpenCart)
 * Ver. 1.0.0
 * Built By: PayPlus LTD - Development Department
 * All rights reserved © PayPlus LTD
 * Website: https://www.payplus.co.il
 * E-mail: service@payplus.co.il
*/
namespace Opencart\Catalog\Controller\Extension\Payplus\Payment;
define('PAYPLUS_PAYMNET_URL_PRODUCTION','https://restapi.payplus.co.il/api/v1.0/');
define('PAYPLUS_PAYMNET_URL_DEV','https://restapidev.payplus.co.il/api/v1.0/');
class Payplus extends \Opencart\System\Engine\Controller {

	public function index() {
        $this->session->data['language'] = $this->request->get['language'];

	    $this->language->load('extension/payment/payplus');   
		$data['sandbox_mode'] = $this->config->get('payment_payplus_sandbox');
		$data['text_sandbox'] = $this->language->get('text_sandbox');
		$data['store_cards'] = (bool)$this->config->get('payment_payplus_store_cards');
		
		$data['cards'] = array();
		
		if ($this->customer->isLogged()) { // If User Is Logged In
            $data['is_logged'] = true;
            $this->load->model('extension/payplus/credit_card/payplus');
            $data['cards'] = $this->model_extension_payplus_credit_card_payplus->getCards($this->customer->getId());
        }
		$data['action'] = $this->url->link('extension/payplus/payment/payplus.create_link', '', true);

		
		$data['display_mode'] = $this->config->get('payment_payplus_display_mode');

		$data['iframe_height'] = $this->config->get('payment_payplus_iframe_height');
		
		return  $this->load->view('extension/payplus/payment/payplus', $data);
		}
    public  function getTotalAll(){
        $taxes = $this->cart->getTaxes();


        $total_data = array(
            'totals' => &$totals,
            'taxes'  => &$taxes,
            'total'  => &$total
        );
        $this->load->model('setting/extension');

        $sort_order = array();

        $results = $this->model_setting_extension->getExtensions('total');


        foreach ($results as $key => $value) {
            $sort_order[$key] = $this->config->get('total_' . $value['code'] . '_sort_order');
        }

        array_multisort($sort_order, SORT_ASC, $results);

        foreach ($results as $result) {
            if ($this->config->get('total_' . $result['code'] . '_status')) {
                $this->load->model('extension/total/' . $result['code']);

                // We have to put the totals in an array so that they pass by reference.
                $this->{'model_extension_total_' . $result['code']}->getTotal($total_data);
            }
        }

        return $totals;
    }
    public  function  setArrangement($order_info ){
        $order_info['payment_firstname']=($order_info['payment_firstname'])?$order_info['payment_firstname'] :$order_info['shipping_firstname'];
        $order_info['payment_firstname']=($order_info['payment_lastname'])?$order_info['payment_lastname'] :$order_info['shipping_lastname'];
        $order_info['payment_address_1']=($order_info['payment_address_1'])?$order_info['payment_address_1'] :$order_info['shipping_address_1'];
        $order_info['payment_address_2']=($order_info['payment_address_2'])?$order_info['payment_address_2'] :$order_info['shipping_address_2'];
        $order_info['payment_city']=($order_info['payment_city'])?$order_info['payment_city'] :$order_info['shipping_city'];
        $order_info['payment_postcode']=($order_info['payment_postcode'])?$order_info['payment_postcode'] :$order_info['shipping_postcode'];
        return $order_info;

    }
    public  function get_url_api(){
        $apiURL =($this->config->get('payment_payplus_sandbox'))?PAYPLUS_PAYMNET_URL_DEV:PAYPLUS_PAYMNET_URL_PRODUCTION;
        return $apiURL;
    }
 	public function create_link($token=false) {

	    $this->language->load('extension/payment/payplus');   
		$this->load->model('checkout/order');

		$order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);
        $order_info =$this->setArrangement($order_info);

        $apiURL =$this->get_url_api().'PaymentPages/generateLink';
		$refURL_success = $this->url->link('extension/payplus/payment/payplus.callback');

		// Payment Using Token
		if ($this->request->post['selected_card'] != "save" AND is_numeric($this->request->post['selected_card']) AND $this->customer->isLogged()) {
            $this->load->model('extension/payplus/credit_card/payplus');
			$getToken=$this->model_extension_credit_card_payplus->getCard($this->customer->getId(),$this->request->post['selected_card']);
			$cardToken = $getToken['card_token'];
			}
		
		$data['products'] = array();
		$totalCartAmount=0;
		foreach ($this->cart->getProducts() as $product) {
			$price = $this->currency->format($product['price'], $order_info['currency_code'], false, false);
			$productsItems[] = '{	
					"name" : "'.htmlspecialchars($product['name']).'",
					"barcode" : "'.htmlspecialchars($product['model']).'",
					"quantity" : '.$product['quantity'].',
					"price" : '.$price.'
					}';
			$totalCartAmount += $price*$product['quantity'];
			}
		
		$total = $this->currency->format($order_info['total'] - $this->cart->getSubTotal(), $order_info['currency_code'], false, false);

		if ($total > 0) {
			$productsItems[] = '{	
					"name" : "'.$this->language->get('text_total').'",
					"quantity" : 1,
					"price" : '.$total.'
					}';
			$totalCartAmount += $total;
		}
        if(!empty($this->session->data['coupon'])){

            $total =$this->getTotalAll();
            $coupons =array_filter($total,function ($value){
                return $value['code']==="coupon";
            });

            if($coupons){

                foreach ($coupons as $key =>$coupon){
                    $productsItems[] = '{
					"name" : "'.$this->language->get('text_discount').'",
					"quantity" : 1,
					"price" : '.$coupon['value'].'
					}';
                    $totalCartAmount +=$coupon['value'];
                }

            }
        }

	$langCode = explode("-",$this->session->data['language']);


	
	$payload = '{
    "payment_page_uid": "'.$this->config->get('payment_payplus_payment_page_uid').'",
	'.($this->config->get('payment_payplus_charge_method') > 0 ? '"charge_method": "'.$this->config->get('payment_payplus_charge_method').'",' : '').'
	"language_code": "'.trim(strtolower($langCode[0])).'",
    "expiry_datetime": "30",
    "refURL_success": "'.$refURL_success.'",
    "refURL_failure": "'.$refURL_success.'",
    "customer": {
        "customer_name":"'.html_entity_decode(($order_info['payment_company'] ? $order_info['payment_company'] : $order_info['payment_firstname'].' '.$order_info['payment_lastname']), ENT_QUOTES, 'UTF-8').'",
        "email":"'.($order_info['email'] ? $order_info['email'] : '').'",
		"address": '.html_entity_decode(($order_info['payment_address_1'] || $order_info['payment_address_2'] ? '"'.$order_info['payment_address_1'].' '.$order_info['payment_address_2'].'"' : 'null'), ENT_QUOTES, 'UTF-8').',
		"city": '.html_entity_decode(($order_info['payment_city'] ? '"'.$order_info['payment_city'].'"' : 'null'), ENT_QUOTES, 'UTF-8').',
		"postal_code": '.html_entity_decode(($order_info['payment_postcode'] ? '"'.$order_info['payment_postcode'].'"' : 'null'), ENT_QUOTES, 'UTF-8').',
		"country_iso": '.($order_info['payment_iso_code_2'] ? '"'.$order_info['payment_iso_code_2'].'"' : 'null').'
    },
    "items": [
        '.@implode(",",$productsItems).'
    ],
	'.(isset($cardToken) ? '"token" : "'.$cardToken.'",' : '').'
    "amount": '.round($totalCartAmount,2).',
    "currency_code": "'.$order_info['currency_code'].'",
    '.($this->request->post['selected_card'] == "save" ? '"create_token": true,' : '').'
	"more_info": "'.$order_info['order_id'].'"
}';
		if ($this->request->post['selected_card'] == "save") $this->session->data['save_card'] = true;
		
		$paymentPageURL=$this->pp_post($apiURL,$payload);

		
		if ($this->request->post['selected_card'] == "save" || $this->request->post['selected_card'] == "skip") {

			if ($paymentPageURL->data->payment_page_link) {
				if ($this->config->get('payment_payplus_logger')) $this->log->write('PAYPLUS :: PAYMENT REQUEST: ' . print_r($paymentPageURL,true));
				header("Location: ".$paymentPageURL->data->payment_page_link);
				} else {
				if ($this->config->get('payment_payplus_logger')) $this->log->write('PAYPLUS :: PAYMENT ERROR: ' . print_r($paymentPageURL,true));
				header('Location: '.$this->response->redirect($this->url->link('checkout/failure', '', true)));
				}

			} else if (is_numeric($this->request->post['selected_card']) AND $cardToken) {
			$responseParam = Array();
			$output = Array();
			if (isset($paymentPageURL->results->status) && $paymentPageURL->results->status == "error") {
				$responseParam=json_decode(json_encode($paymentPageURL->results ,true),true);
				} else {
				$responseParam=json_decode(json_encode($paymentPageURL->data ,true),true);
				foreach ($responseParam AS $k => $v) $output[$k] = $v;
				$output['used_token'] = $this->request->post['selected_card'];
				}
			if ($responseParam['transaction_uid'] && $responseParam['status'] == "approved" && $responseParam['status_code'] == "000") $this->response->redirect($this->url->link('extension/payment/payplus/callback', $output, true)); //header('Location: '.$refURL_success.'?'.implode("&",$output));
				else header('Location: '.$this->response->redirect($this->url->link('checkout/failure', '', true)));
			
			}
	}

    private function pp_post($url,$payload) {
		$ch = curl_init($url);
		$headers = array('Content-Type: application/json','Authorization:{"api_key":"'.$this->config->get('payment_payplus_api_key').'","secret_key":"'.$this->config->get('payment_payplus_secret_key').'"}');
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_USERAGENT, 'OpenCart '.$_SERVER['HTTP_USER_AGENT']);
		curl_setopt($ch, CURLOPT_REFERER, $this->url->link('checkout/checkout', '', true));
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
		
	public function ipn($transaction_uid='') {
		if (empty($transaction_uid)) return false;
        $ipnURL =$this->get_url_api().'PaymentPages/ipn';
		$payload = '{
			"transaction_uid": "'.$transaction_uid.'"
			}';
		$res=$this->pp_post($ipnURL,$payload);

		if ($this->config->get('payment_payplus_logger')) $this->log->write('PAYPLUS :: IPN Response: ' . print_r($res,true));

		if ($res->results->status == "error") {
			$this->log->write('PAYPLUS :: IPN Failure: ' . print_r($res,true));
			return false;
			} else {
			if ( $res->data->status == "approved" && $res->data->status_code == "000" && $res->results->status == "success" ) {
				$this->log->write('PAYPLUS :: IPN Successful: ' . print_r($res,true));
				return true;
				}
			}
		return false;
		}
		
	public function callback(){
		$this->load->model('checkout/order');

        if (isset($this->request->get['more_info'])){
            $request = $this->request->get;
        }else{
            $request = $this->request->post;
        }

		$order_id = $request['more_info'];
		if (!$order_id OR !$request) {
			$this->response->redirect($this->url->link('checkout/checkout', '', true));
			}
		$transaction_uid = $request['transaction_uid'];

		$order_info = $this->model_checkout_order->getOrder($order_id);


		if (isset($request['token_uid']) && $this->customer->isLogged() && $this->session->data['save_card'] && $request['method'] && $request['status'] == "approved" && $request['status_code'] == "000" && $request['token_uid']) { // Save Token if choosed

            $this->load->model('extension/payplus/credit_card/payplus');
			$card_data = Array();
			$card_data['token'] = $request['token_uid'];
			$card_data['brand_name'] = $request['brand_name'];
			$card_data['four_digits'] = $request['four_digits'];
			$card_data['exp_year'] = $request['expiry_year'];
			$card_data['exp_month'] = $request['expiry_month'];
			$getExistingCard = $this->model_extension_credit_card_payplus->cardExists($this->customer->getId(),$card_data['token']);
			if (!$getExistingCard) $saved_card = $this->model_extension_credit_card_payplus->addCard($this->customer->getId(),$card_data);
			unset($this->session->data['save_card']);
			}
        $this->load->model('extension/payplus/payment/payplus');
		if (!$this->model_extension_payplus_payment_payplus->checkOrder($order_id)) {
			$order_data = Array();
			if (isset($request['used_token'])) $saved_card=$request['used_token'];
			$order_data['order_id']=$order_id;
			$order_data['payplus_transaction_uid']=$request['transaction_uid'];
			$order_data['currency_code']=$order_info['currency_code'];
			if (isset($saved_card)) $order_data['token_id']=$saved_card;
			$order_data['save_token']=(isset($saved_card) ? '1' : '0');
			$order_data['total']=$order_info['total'];
			$save_order = $this->model_extension_payplus_payment_payplus->addOrder($this->customer->getId(),$order_data);
			if ($this->config->get('payment_payplus_logger')) $this->log->write('PAYPLUS :: Card Saved: ' . print_r($save_order,true));
			}

		if (!$this->model_extension_payplus_payment_payplus->checkTransaction($this->customer->getId(),$order_id)) {
			$transaction_data = Array();
			$transaction_data['order_id'] = $order_id;
			$transaction_data['payplus_transaction_uid'] = $request['transaction_uid'];
			$transaction_data['type'] = strtolower($request['type']);
			$transaction_data['status'] = ($request['status'] == "approved" ? 'Confirmed' : 'Failed');
			$transaction_data['error_code'] = $request['status_code'];
			$transaction_data['error_description'] = $request['status_description'];
			$transaction_data['amount'] = $order_info['total'];
			$transaction_data['response_log'] = print_r($request,true);
			$transaction_id=$this->model_extension_payplus_payment_payplus->addTransaction($this->customer->getId(),$transaction_data);
			if ($this->config->get('payment_payplus_logger')) $this->log->write('PAYPLUS :: Transaction Saved: ' . print_r($transaction_data,true));
			}
		if ($this->config->get('payment_payplus_logger')) $this->log->write('PAYPLUS :: Payment Callback Response: ' . print_r($request,true));
		
		$IPNResponse = $this->ipn($transaction_uid);

		if ($order_info && !$order_info['order_status_id']) {
			$order_status_id = $this->config->get('config_order_status_id');
			if (!empty($this->config->get('payment_payplus_order_status_id'))) $order_status_id = $this->config->get('payment_payplus_order_status_id');
			$order_failure_status_id = $this->config->get('payment_payplus_failure_order_status_id');

			if ($IPNResponse) {
                $this->load->model('checkout/order');
                $this->model_checkout_order->addHistory($order_id, $order_status_id);
                $this->response->redirect($this->url->link('checkout/success', 'language=' . $this->config->get('config_language'), true));
				} else {
				$this->model_checkout_order->addHistory($order_id, $order_failure_status_id);
                $this->response->redirect($this->url->link('checkout/failure', 'language=' . $this->config->get('config_language'), true));

				}
			}
		$this->response->redirect($this->url->link('checkout/checkout', 'language=' . $this->config->get('config_language'),true));
		}
	}
