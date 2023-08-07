<?php
/* PayPlus - Payment Gateway (Extension For OpenCart)
 * Ver. 1.0.0
 * Built By: PayPlus LTD - Development Department
 * All rights reserved © PayPlus LTD
 * Website: https://www.payplus.co.il
 * E-mail: service@payplus.co.il
*/

// Heading
$_['heading_title']              = 'פיי פלוס - פתרונות תשלום';
$_['text_payplus_menu_column']   = 'פיי פלוס - סליקה';

// Text 
$_['text_success']       = 'הפרטים עודכנו: ערכת בהצלחה את פרטי הסליקה שלך בפיי פלוס!';
$_['text_extension']     = 'הרחבות';
$_['text_edit']          = 'עריכת פיי פלוס';
$_['text_payplus']		 = '<img src="view/image/payment/payplus.png" alt="PayPlus - Payment Gateway" title="PayPlus - Payment Gateway" />';

// Entry
$_['entry_title']     	                    = '<a href="https://www.payplus.co.il" target="_blank">פיי פלוס בע"מ</a>';
$_['text_info_ssl']   	                    = '<strong>חשוב:</strong> חובה להפעיל באתרך מצב SSL (https://) כדי שהתוסף של פיי פלוס יעבוד ללא בעיות באתר';
$_['text_edit']   	  	                    = 'הזן את פרטי החשבון שלך בפיי פלוס';
$_['text_view_settings']   	                = 'בחר מצב תצוגת דף הסליקה';
$_['text_general_settings']                 = 'הגדרות כלליות של החנות שלך';
$_['text_opencart_settings']                = 'הגדרות כלליות של OpenCart';
$_['entry_api_key']   	                    = 'API Key';
$_['entry_secret_key']                      = 'Secret Key';
$_['entry_payment_page_uid']                = 'Payment Page UID';
$_['entry_sandbox']                         = 'Sandbox Mode';
$_['entry_display_mode']                    = 'מצב תצוגה';
$_['entry_iframe_height']                   = 'גובה IFrame';
$_['entry_default_payment_page_setting']    = 'הגדרות ברירת מחדל של דף התשלום';
$_['entry_ipn_url']                         = 'כתובת IPN';
$_['entry_charge_method']                   = 'מצב חיוב';
$_['text_status']                           = 'הפעל את פיי פלוס בסל הקניות';
$_['text_order_status']                     = 'מצב הזמנה לאחר חיוב מוצלח';
$_['text_failure_order_status']             = 'מצב הזמנה';
$_['text_sort_order']                       = 'מיקום פיי פלוס';
$_['text_supported_currencies']             = 'מטבעות נתמכים';
$_['entry_store_cards']                     = 'שמירת כרטיסים';
$_['text_entry_logger']                     = 'הפעל לוגים';

// Order
$_['text_payment_info']                     = 'רשימת עסקאות להזמנה';
$_['text_available_actions']                = 'פעולות אפשריות';
$_['text_column_options']		            = 'אפשרויות';
$_['text_column_date_added']		        = 'תאריך';
$_['text_column_type']				        = 'סוג';
$_['text_column_status']			        = 'מצב';
$_['text_column_transaction_number']		= 'מס\' עסקה בפיי פלוס';
$_['text_column_card_info']			        = 'פרטי כרטיס';
$_['text_column_approval_number']			= 'מס\' אישור';
$_['text_column_voucher_number']			= 'מס\' אסמכתא';
$_['text_column_installments']			    = 'תשלומים';
$_['text_column_first_installment']			= 'תשלום ראשון';
$_['text_column_rest_installments']			= 'שאר התשלומים';
$_['text_column_error_code']			    = 'קוד שגיאה';
$_['text_column_error_description']			= 'תיאור השגיאה';
$_['text_column_amount']			        = 'סכום';
$_['text_column_currency']			        = 'מטבע';

/* Order Stats */
$_['stats_order_amount']            = 'סכום ההזמנה';
$_['stats_charged_amount']          = 'סכום חיוב כולל';
$_['stats_refund_amount']           = 'סכום זיכוי כולל';
$_['stats_info']                    = 'מידע נוסף';
$_['stats_text_used_token']         = 'השתמש בכרטיס שמור';
$_['stats_total_operations']         = 'סה"כ עסקאות להזמנה זו';

/* Charge */
$_['text_charge_payment']               = 'חיוב הזמנה';
$_['button_charge']                     = 'הוסף חיוב';
$_['available_charge']                  = 'סכום זמין לחיוב';
$_['text_partial_charge']               = 'חיוב חלקי';
$_['text_full_charge']                  = 'חיוב מלא';
$_['text_charge_type']                  = 'סוג החיוב';
$_['text_charge_error']                 = 'על סכום החיוב להיות גדול מ 0';
$_['text_pp_transaction_successful']    = 'העסקה נוספה בהצלחה';
$_['text_pp_transaction_error']         = 'העסקה נדחתה ולא הושלמה';

/* Refund */
$_['text_refund_payment']           = 'זיכוי עסקה';
$_['button_refund']                 = 'הוספת זיכוי';
$_['available_refund']              = 'סכום זמין לזיכוי';
$_['text_partial_refund']           = 'זיכוי חלקי';
$_['text_full_refund']              = 'זיכוי מלא';
$_['text_refund_type']              = 'סוג זיכוי';
$_['text_refund_error']             = 'על סכום הזיכוי להיות גדול מ 0';


// Error
$_['error_permission']	                         = 'שים לב: אין לך הרשאות לעריכת הפרטים לתוסף של פיי פלוס';
$_['error_text_payplus_api_key_id']	             = 'API Key חובה';
$_['error_text_payplus_secret_key_id']      	 = 'Secret Key חובה';
$_['error_text_payplus_payment_page_uid_id']	 = 'Payment Page UID חובה';

$_['text_extension_credit']	 = 'התוסף נבנה ע"י <a href="https://www.payplus.co.il/opencart">פיי פלוס בע"מ</a> &copy; כל הזכויות שמורות';

?>