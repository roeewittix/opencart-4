<?php
/* PayPlus - Payment Gateway (Extension For OpenCart)
 * Ver. 1.0.0
 * Built By: PayPlus LTD - Development Department
 * All rights reserved © PayPlus LTD
 * Website: https://www.payplus.co.il
 * E-mail: service@payplus.co.il
*/
namespace Opencart\Admin\Model\Extension\Payplus\Payment;

class Payplus extends  \Opencart\System\Engine\Model {

    public function install() {
    $this->db->query("
        CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "payplus_order` (
            `payplus_order_id` INT(11) NOT NULL AUTO_INCREMENT,
            `customer_id` INT(10) NOT NULL,
            `date_added` DATETIME NOT NULL,
            `modified` DATETIME NOT NULL,
            `order_id` int(11) NOT NULL,
            `payplus_transaction_uid` varchar(255) NOT NULL,
            `currency_code` CHAR(3) NOT NULL,
            `token_id` INT(10) DEFAULT NULL,
            `save_token` INT(1) DEFAULT NULL,
            `total` DECIMAL( 10, 2 ) NOT NULL,
            KEY `payplus_transaction_uid` (`payplus_transaction_uid`),
            PRIMARY KEY `payplus_order_id` (`payplus_order_id`)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
    ");

    $this->db->query("
        CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "payplus_order_transaction` (
          `payplus_transaction_id` INT(11) NOT NULL AUTO_INCREMENT,
          `customer_id` INT(10) NOT NULL,
          `order_id` INT(11) NOT NULL,
          `payplus_transaction_uid` varchar(255) NOT NULL,
          `date_added` DATETIME NOT NULL,
          `type` ENUM('charge', 'approval', 'refund') DEFAULT NULL,
          `status` ENUM('Confirmed', 'Failed') DEFAULT NULL,
          `error_code` VARCHAR(10) DEFAULT NULL,
          `error_description` VARCHAR(254) DEFAULT NULL,
          `amount` DECIMAL( 10, 2 ) NOT NULL,
          `is_full_refunded` INT(1) NOT NULL,
          `refund_original` varchar(255) NOT NULL,
          `response_log` TEXT NOT NULL,
          PRIMARY KEY `payplus_transaction_id` (`payplus_transaction_id`)
        ) ENGINE=MyISAM DEFAULT COLLATE=utf8_general_ci;
        ");
    
    $this->db->query("
        CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "payplus_cards` (
          `payplus_card_id` INT(11) NOT NULL AUTO_INCREMENT,
          `date_added` DATETIME NOT NULL,
          `modified` DATETIME NOT NULL,
          `customer_id` INT(11) NOT NULL,
          `last_four` CHAR(4) NOT NULL,
          `exp_year` CHAR(2) NOT NULL,
          `exp_month` CHAR(2) NOT NULL,
          `brand_name` VARCHAR(20) DEFAULT NULL,
          `card_token` VARCHAR(254) NOT NULL,
          PRIMARY KEY `payplus_card_id` (`payplus_card_id`)
          ) ENGINE=MyISAM DEFAULT COLLATE=utf8_general_ci;
        ");

    }

    // Model Admin Functions
    
    public function getOrder($order_id) {
		$qq = $this->db->query("SELECT * FROM `" . DB_PREFIX . "payplus_order` WHERE `order_id` = '".(int)$order_id."' LIMIT 1");
		$charges=$this->db->query("SELECT SUM(amount) AS amount FROM `" . DB_PREFIX . "payplus_order_transaction` WHERE order_id='" . (int)$order_id . "' AND type='charge' AND status='Confirmed'")->row;
		$refund=$this->db->query("SELECT SUM(amount) AS amount FROM `" . DB_PREFIX . "payplus_order_transaction` WHERE order_id='" . (int)$order_id . "' AND type='refund' AND status='Confirmed'")->row;
		if ($qq->num_rows) {
			$order = $qq->row;
			$order['transactions'] = $this->getTransactions($order['order_id']);
			$order['stats']['total_charges']=($charges['amount'] ? $charges['amount'] : '0');
			$order['stats']['total_refunds']=($refund['amount'] ? $refund['amount'] : '0');
			return $order;
			} else {
			return false;
			}
		}

    private function getTransactions($order_id) {
		  $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "payplus_order_transaction` WHERE `order_id` = '".(int)$order_id."' ORDER BY `date_added` DESC");

      $transactions = array();
      if ($query->num_rows) {
        foreach ($query->rows as $row) {
          $row['info']=$this->string_to_array($row['response_log']);
          unset($row['response_log']);
          $transactions[] = $row;
        }
        return $transactions;
      } else {
        return false;
      }
    }

    public function getByPayPlusTransaction($uid) {
		  return $this->db->query("SELECT * FROM `" . DB_PREFIX . "payplus_order_transaction` WHERE `payplus_transaction_uid` = '".$uid."'")->row;
    }
    
    public function updatePayPlusTransaction($uid) {
      $original=$this->db->query("SELECT * FROM `" . DB_PREFIX . "payplus_order_transaction` WHERE `payplus_transaction_uid` = '".$uid."' AND type='charge' AND status='Confirmed'")->row;
      $qq=$this->db->query("SELECT SUM(amount) AS amount FROM `" . DB_PREFIX . "payplus_order_transaction` WHERE `refund_original` = '".$uid."' AND type='refund' AND status='Confirmed'")->row;
      if ($qq['amount'] >= $original['amount']) return $this->db->query("UPDATE `" . DB_PREFIX . "payplus_order_transaction` SET is_full_refunded='1' WHERE `payplus_transaction_id` = '".$original['payplus_transaction_id']."'")->row;
    }

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
    
    public function maxToCharge($order_id) {
      $approvals=$this->db->query("SELECT SUM(amount) AS amount FROM `" . DB_PREFIX . "payplus_order_transaction` WHERE order_id='" . (int)$order_id . "' AND type='approval' AND status='Confirmed'")->row;
      $charges=$this->db->query("SELECT SUM(amount) AS amount FROM `" . DB_PREFIX . "payplus_order_transaction` WHERE order_id='" . (int)$order_id . "' AND type='charge' AND status='Confirmed'")->row;
      $return=(double)($approvals['amount']-$charges['amount']);
      return $return > 0 ? $return : '0';
      }

    public function maxToRefund($order_id) {
      $charges=$this->db->query("SELECT SUM(amount) AS amount FROM `" . DB_PREFIX . "payplus_order_transaction` WHERE order_id='" . (int)$order_id . "' AND type='charge' AND status='Confirmed'")->row;
      $refunds=$this->db->query("SELECT SUM(amount) AS amount FROM `" . DB_PREFIX . "payplus_order_transaction` WHERE order_id='" . (int)$order_id . "' AND type='refund' AND status='Confirmed'")->row;
      $return = (double)($charges['amount']-$refunds['amount']);
      return $return > 0 ? $return : '0';
      }

    public function orderRefunded($order_id) {
      return $this->db->query("SELECT SUM(amount) AS amount FROM `" . DB_PREFIX . "payplus_order_transaction` WHERE order_id='" . (int)$order_id . "' AND type='refund' AND status='Confirmed'")->rows;
      }

    private function string_to_array($string) {
      if (!$string) return false;
      if (is_object($string)) $string = (array) json_decode($string);
      $exp=explode("\n",trim(str_replace(Array("Array","(",")","stdClass Object"),"",$string)));
      for ($i=0; $i<count($exp); $i++) {
        $line_exp=explode("=>",$exp[$i]);
        $key = trim(str_replace(array("[","]"),"",$line_exp[0]));
        if ($key && $line_exp[1]) $new_array[$key]=trim($line_exp[1]);
      }
      return $new_array;
    }

}