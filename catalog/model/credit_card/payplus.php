<?php
/* PayPlus - Payment Gateway (Extension For OpenCart)
 * Ver. 1.0.0
 * Built By: PayPlus LTD - Development Department
 * All rights reserved © PayPlus LTD
 * Website: https://www.payplus.co.il
 * E-mail: service@payplus.co.il
*/
namespace Opencart\Catalog\Model\Extension\Payplus\CreditCard;
class Payplus extends  \Opencart\System\Engine\Model {
    public function addCard($customer_id, $data) {
        $this->db->query("INSERT INTO `" . DB_PREFIX . "payplus_cards` SET customer_id='" . (int)$customer_id . "', card_token='" . $this->db->escape($data['token']) . "', brand_name='" . $this->db->escape($data['brand_name']) . "', last_four='" . $data['four_digits'] . "', exp_year='" . (int)$data['exp_year'] . "', exp_month='" . (int)$data['exp_month'] . "', date_added=NOW()");
		return $this->db->getLastId();
    }

    public function getCard($customer_id,$payplus_card_id) {
        return $this->db->query("SELECT * FROM `" . DB_PREFIX . "payplus_cards` WHERE customer_id='" . (int)$customer_id . "' AND payplus_card_id='" . (int)$payplus_card_id . "'")->row;
    }
    
    public function getCards($customer_id) {
        return $this->db->query("SELECT payplus_card_id,last_four,exp_month,exp_year,brand_name FROM `" . DB_PREFIX . "payplus_cards` WHERE customer_id='" . (int)$customer_id . "'")->rows;
    }

    public function cardExists($customer_id, $token) {
        return $this->db->query("SELECT * FROM `" . DB_PREFIX . "payplus_cards` WHERE customer_id='" . (int)$customer_id . "' AND card_token='" . $token . "'")->num_rows > 0;
    }

    public function verifyCardCustomer($payplus_card_id, $customer_id) {
        return $this->db->query("SELECT * FROM `" . DB_PREFIX . "payplus_cards` WHERE payplus_card_id='" . (int)$payplus_card_id . "' AND customer_id='" . (int)$customer_id . "'")->num_rows > 0;
    }

    public function deleteCard($payplus_card_id) {
        $this->db->query("DELETE FROM `" . DB_PREFIX . "payplus_cards` WHERE payplus_card_id='" . (int)$payplus_card_id . "'");
    }
}