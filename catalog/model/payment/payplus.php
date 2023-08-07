<?php
/* PayPlus - Payment Gateway (Extension For OpenCart)
 * Ver. 1.0.0
 * Built By: PayPlus LTD - Development Department
 * All rights reserved © PayPlus LTD
 * Website: https://www.payplus.co.il
 * E-mail: service@payplus.co.il
*/
namespace Opencart\Catalog\Model\Extension\Payplus\Payment;

class Payplus extends \Opencart\System\Engine\Model {
  	public function getMethods($address) {

        $this->load->language('extension/payplus/payment/payplus');

		if ($this->config->get('payment_payplus_status')) {
      		$status = true;	
      	} else {
			$status = false;
		}
		
		$currencies = explode(",",$this->config->get('payment_payplus_supported_currencies'));


		if (!in_array($this->session->data['currency'], $currencies)) {
			$status = false;
		}

		$method_data = array();
		if ($status) {
            $option_data['payplus'] = [
                'code' => 'payplus.payplus',
                'name' => $this->language->get('text_title')
            ];

      		$method_data = array( 
        		'code'       => 'payplus',
        		'name'      => $this->language->get('text_title'),
				'terms'      => '',
				'sort_order' => $this->config->get('payplus_sort_order'),
                'option'=>$option_data
      		);
    	}
   
    	return $method_data;
  	}
	
	public function addOrder($customer_id, $data) {
		if (!is_array($data)) return false;
		$implodeData = '';
		foreach ($data AS $k => $v) $implodeData .= ",".$k."='".$this->db->escape($v)."'";
        $this->db->query("INSERT INTO `" . DB_PREFIX . "payplus_order` SET customer_id='" . (int)$customer_id . "', date_added=NOW()".$implodeData);
    }
	
	public function checkOrder($order_id) {
		return $this->db->query("SELECT * FROM `" . DB_PREFIX . "payplus_order` WHERE order_id='" . (int)$order_id . "'")->num_rows > 0;
    }
	
	// Transactions Model
	
	public function addTransaction($customer_id, $data) {
		if (!is_array($data)) return false;
		$implodeData = '';
		foreach ($data AS $k => $v) $implodeData .= ",".$k."='".$this->db->escape($v)."'";
        $this->db->query("INSERT INTO `" . DB_PREFIX . "payplus_order_transaction` SET customer_id='" . (int)$customer_id . "', date_added=NOW()".$implodeData);
		return $this->db->getLastId();
		}
		
	public function checkTransaction($customer_id, $order_id) {
        return $this->db->query("SELECT * FROM `" . DB_PREFIX . "payplus_order_transaction` WHERE customer_id='" . (int)$customer_id . "' AND order_id='" . $order_id . "'")->num_rows > 0;
		}
}
?>