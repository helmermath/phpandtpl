<?php
//-----------------------------------------
// Author: Qphoria@gmail.com
// Web: http://www.OpenCartGuru.com/
//-----------------------------------------

// Heading
$_['heading_title']      = 'Npay';

// Specific Entry
$_['entry_mid']          = 'Username:';
$_['entry_key']          = 'Password:';

// Specific Tooltip
$_['tooltip_mid']	  	   = 'Use \'demo\' for test account';
$_['tooltip_key']	   	   = 'Use \'password\' for test account.';

// Specific Errors
$_['error_fields']		   = 'mid,key,merchant_defined_field_1'; // do not change this for any language
$_['error_mid']      	   = 'Field Required!';
$_['error_key']       	   = 'Field Required!';
$_['error_merchant_defined_field_1']       	   = 'Merchant Defined Field Required!';

// Common Text
$_['text_edit']            = 'Edit Payment';
$_['text_payment']         = 'Payment';
$_['text_success']         = 'Success: You have modified the payment settings!';
$_['text_guest']           = 'Guest';

// Common EntryMerchant Defined Field
$_['entry_merchant_defined_field']        	= 'Merchant Defined Field:';
$_['entry_title']        	= 'Title:';
$_['entry_status']         	= 'Status:';
$_['entry_test']       		= 'Testmode:';
$_['entry_sort_order']     	= 'Sort Order:';
$_['entry_order_status']   	= 'Success Order Status:';
$_['entry_geo_zone']       	= 'Geo Zone:';
$_['entry_tax_class']     	= 'Tax Class:';
$_['entry_debug']          	= 'Debug Logging:';
$_['entry_total']   	 	= 'Min Total:';
$_['entry_title']          	= 'Title:';
$_['entry_support']        	= 'Support Info:';
$_['entry_debug_file']      = 'Debug File:';
$_['entry_txntype']         = 'Transaction Type:';

// Tab
$_['tab_support']          	= 'Support';
$_['tab_debug']          	= 'Debug ';

// Common Tooltip
$_['tooltip_title']        = 'The title shown during the checkout payment step';
$_['tooltip_status']       = 'Enable/Disable';
$_['tooltip_total']	       = 'The minimum total the cart must be to show this payment option. Recommend set to 0.01 or higher.';
$_['tooltip_geo_zone']     = 'Allowed Geo Zone';
$_['tooltip_order_status'] = 'The order status that is set upon successful payment';
$_['tooltip_sort_order']   = 'The sort order on the payment checkout step';
$_['tooltip_test']   	   = 'Use the Test server/mode';
$_['tooltip_tax_class']    = 'Which tax should be applied to the payment';
$_['tooltip_debug']		   = 'Logs messages between store and gateway for troubleshooting to the system/logs folder in FTP.';
$_['tooltip_debug_file']   = '';
$_['tooltip_txntype']      = 'Sale - Auto capture. Auth - Manual Capture.';

// Common Help
$_['help_debug']	       = 'Log found at "system/logs/'.basename(__FILE__, '.php').'_debug.txt" (in ftp). <span style="color:red;">Please include this log when contacting the developer for help!</span>';

// Error
$_['error_permission']     = 'Warning: You do not have permission to modify this payment!';
?>