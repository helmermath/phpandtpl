<?php
//-----------------------------------------
// Author: Qphoria@gmail.com
// Web: http://www.opencartguru.com/
//-----------------------------------------

// Text
$_['text_title'] 							= '---'; // No longer used. Title pulled from the database value set in admin.

// Common Text
$_['text_wait'] 							= 'Please Wait...';
$_['text_testmode']							= 'Attention: The payment gateway is in test mode. Orders will not actually be processed.';
$_['text_legend'] 							= 'Credit Card Details';

// Common Entry
$_['entry_card_name'] 						= 'Name on Card:';
$_['entry_card_type'] 						= 'Card Type:';
$_['entry_card_num'] 						= 'Card Number:';
$_['entry_card_exp'] 						= 'Card Expiry:';
$_['entry_card_cvv'] 						= 'Card CVV:';
$_['entry_card_start_date'] 				= 'Start Date:';
$_['entry_card_issue_num'] 					= 'Issue Num:';
$_['entry_card_postcode'] 					= 'Postcode:';
$_['entry_card_installments'] 				= 'Installments:';

// Common Help
$_['help_card_name'] 						= 'First Last';
$_['help_card_num'] 						= 'xxxx-xxxx-xxxx-xxxx';
$_['help_card_cvv'] 						= '3-4 digit code';
$_['help_card_start_date'] 					= '(optional)';
$_['help_card_issue_num'] 					= '(optional)';

// Common Errors
$_['error_invalid_lastname']		        = 'Please enter both First and Last Name';
$_['error_invalid_card_name']               = 'Invalid Name on card';
$_['error_card_refund_amount']              = 'Please enter valid refund amount';
$_['error_card_details_wrong']              = 'Card security code (CVV) does not match';
$_['error_card_type']						= 'Invalid Card Type';
$_['error_card_num']						= 'Invalid Card Number';
$_['error_card_cvv']						= 'Invalid Card CVV';
$_['error_card_exp']						= 'Invalid Card Expiry';
$_['error_card_name']						= 'Invalid Card Name';
$_['error_no_order_found']					= 'Error: No Order Found!';
$_['error_unknown']                                             = 'Error: Unknown Error!';
$_['error_invalid']                                             = 'Error: The response was either unexpected or the amount was invalid!';
$_['error_declined']						= 'Error: Payment was declined!';
$_['error_duplicate_transaction']                               = 'Transaction is Duplicate!';
$_['error_insufficient_funds']                                  = 'Insufficient funds!';
$_['error_over_limit']                                          = 'Over limit!';
$_['error_transaction_was_declined']                            = 'Transaction was declined!';
$_['error_transaction_not_allowed']                             = 'Transaction not allowed!';
$_['error_incorrect_payment_information']                       = 'Incorrect payment information!';
$_['error_over_limit']                                          = 'No such card issuer!';
$_['error_no_card_number_on_file']                              = 'No card number on file with issuer!';
$_['error_expired_card']                                        = 'Card is Expired!';
$_['error_expiration_date']                                     = 'Invalid expiration date!';
$_['error_card_security_code']                                  = 'Invalid card security code!';
$_['error_invalid_PIN']                                         = 'Invalid PIN!';
$_['error_call_issuer']                                         = 'Invalid card details, please contact your card issuer for further information!';
$_['error_declined_retry']                                      = 'Declined-Retry in a few days!';
$_['error_declined_stop']                                       = 'Declined-Stop all recurring payments!';
$_['error_declined_update']                                     = 'Declined-Update cardholder data available!';
$_['error_invalid_merchant']                                    = 'Invalid merchant configuration!';
$_['error_transaction_rejected']                                = 'Transaction was rejected!';
$_['error_cart_type']                                           = 'Card type is invalid';

?>