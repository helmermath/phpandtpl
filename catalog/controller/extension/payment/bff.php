<?php
//-----------------------------------------
// Author: Qphoria@gmail.com
// Web: http://www.opencartguru.com/
//-----------------------------------------
class ControllerExtensionPaymentBff extends Controller {

	public function index() {

		# Generic Init
		$extension_type 			= 'extension/payment';
		$classname 					= str_replace('vq2-' . basename(DIR_APPLICATION) . '_' . strtolower(get_parent_class($this)) . '_' . str_replace('/', '_', $extension_type) . '_', '', basename(__FILE__, '.php'));
		$data['classname'] 			= $classname;
		$data 						= array_merge($data, $this->load->language($extension_type . '/' . $classname));

		# Error Check
		$data['error'] = (isset($this->session->data['error'])) ? $this->session->data['error'] : NULL;
		unset($this->session->data['error']);

		# Common fields
		$data['testmode'] 			= $this->config->get($classname . '_test');

		# Form Fields
		$data['action'] 			= 'index.php?route='.$extension_type.'/'.$classname.'/send';
		$data['form_method'] 		= 'post';
		$data['fields']   			= array();

        ### GET CUSTOMER customer_vault_id FROM CUSTOMER DATABASE ###
        $data['customer_vault_id'] = '';
        $data['customer_card_number'] = '';
        $data['show_cvv'] = $data['check_save_card'] = '';
        if ($this->customer->isLogged()) {
            $this->load->model('account/customer');
            $customer_info = $this->model_account_customer->getCustomer($this->customer->getId());
            $this->load->model('setting/setting');
            $setting_save_card = $this->model_setting_setting->getSetting('bff');
            if($setting_save_card['bff_force_save_card'] == 1) {
                $data['fields'][] = array(
                    'entry'			=> '',
                    'type'			=> 'hidden',
                    'placeholder' 	=>         '',
                    'name'			=> 'save_card',
                    'value'			=> '1',
                    'size'			=> '',
                    'param'			=> '',
                    'required'		=> 0,
                );
                $data['check_save_card']=1;
            } else if($setting_save_card['bff_force_save_card'] == 2) {
                $data['fields'][] = array(
                    'entry'			=> '',
                    'type'			=> 'hidden',
                    'placeholder' 	=>         '',
                    'name'			=> 'save_card',
                    'value'			=> '0',
                    'size'			=> '',
                    'param'			=> '',
                    'required'		=> 0,
                );
                $data['check_save_card']=2;
            } else {
                $data['check_save_card']=0;
            }
			
			$card_expired = 1;

			$customer_card_expiry = explode('/', $customer_info['customer_card_expiry']);
			
			if(isset($customer_card_expiry[0]) && isset($customer_card_expiry[1])){
				if($customer_card_expiry[1] > date('y')){ 
					$card_expired = 0; 
				} else if( $customer_card_expiry[0] >= date('m') && $customer_card_expiry[1] >= date('y')){ 
					$card_expired = 0; 
				}
			}

			$data['card_expired'] = $card_expired;

            ### check vault id ###
            if(!isset($setting_save_card['bff_show_cvv'])){
				$data['show_cvv'] = 1;
			}else{
				$data['show_cvv'] = $setting_save_card['bff_show_cvv'];
			}
            $data['customer_vault_id'] = $customer_info['customer_vault_id'];
            $data['customer_card_number'] = $customer_info['customer_card_number'];
        }

		# Compatibility
		if (version_compare(VERSION, '2.2', '>=')) { // v2.2.x Compatibility
			if (version_compare(VERSION, '3.0', '>=')) { // v3.x Compatibility to support twig and tpl files
				$template_file = (DIR_TEMPLATE . str_replace('theme_', '', $this->config->get('config_theme')) . '/template/' . $extension_type . '/' .$classname.'.twig');
				if (is_file($template_file)) {
					return $this->load->view($extension_type . '/' .$classname, $data);
				} else {
					$temp_file = ('/template/' . $extension_type . '/'. $classname . '.tpl');
					if (file_exists(DIR_TEMPLATE . str_replace('theme_', '', $this->config->get('config_theme')) . $temp_file)) {
						$template_file = (DIR_TEMPLATE . str_replace('theme_', '', $this->config->get('config_theme')) . $temp_file);
					} else {
						$template_file = (DIR_TEMPLATE . 'default' . $temp_file);
					}
					extract($data);
					ob_start();
					if (class_exists('VQMod')) { require(VQMod::modCheck(modification($template_file), $template_file)); } else { require(modification($template_file)); }
					return ob_get_clean();
				}
			} else { // v2.2.x Compatibility
				return $this->load->view($extension_type . '/'. $classname, $data);
			}
		} elseif (version_compare(VERSION, '2.0', '>=')) { // v2.0.x Compatibility
			if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/' . $extension_type . '/'. $classname . '.tpl')) {
				return $this->load->view($this->config->get('config_template') . '/template/' . $extension_type . '/'. $classname . '.tpl', $data);
			} else {
				return $this->load->view('default/template/' . $extension_type . '/'. $classname . '.tpl', $data);
			}
		} elseif (version_compare(VERSION, '2.0', '<')) {  // 1.5.x Backwards Compatibility
			$this->data = array_merge($this->data, $data);
			$this->id 	= 'extension/payment';
			if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/payment/' . $classname . '.tpl')) {
				$this->template = $this->config->get('config_template') . '/template/payment/' . $classname . '.tpl';
			} else {
				$this->template = 'default/template/payment/' . $classname . '.tpl';
			}
        	$this->render();
		}
	}

    public function refund($order_info) {
        $log = new Log('bff_debug.txt');
        # Generic Init
        $extension_type = 'extension/payment';
        $classname = str_replace('vq2-' . basename(DIR_APPLICATION) . '_' . strtolower(get_parent_class($this)) . '_' . str_replace('/', '_', $extension_type) . '_', '', basename(__FILE__, '.php'));
        $data['classname'] = $classname;
        $data = array_merge($data, $this->load->language($extension_type . '/' . $classname));
        $refund_amount = $order_info['refund_amount'];
        $refund_type = $order_info['refund_type'];

        # Common URL Values
        $callbackurl 	= $this->url->link($extension_type . '/' . $classname . '/callback', '', 'SSL');
        $cancelurl      = $this->url->link('checkout/checkout', '', 'SSL');
        $successurl 	= $this->url->link('checkout/success');
        $declineurl 	= $this->url->link('checkout/checkout', '', 'SSL');

        $json = array();

        # Check for supported currency, otherwise convert
        $currencies = array(
            'USD' => 'USD',

        );

        if (in_array($order_info['currency_code'], $currencies)) {
            $currency_code = $currencies[$order_info['currency_code']];
        } else {
            $currency_code = 'USD';
        }
        //echo $currency_code;
        $amount = str_replace(array(','), '', $this->currency->format($order_info['total'], $currency_code, FALSE, FALSE));
        if($refund_type == 2) {
            if(!empty($refund_amount) && $refund_amount > 0 && $refund_amount < $amount) {
                $amount = $refund_amount;
            } else {
                $json['error'] = $this->language->get('error_card_refund_amount');
                $this->response->addHeader('Content-Type: application/json');
                $this->response->setOutput(json_encode($json));
                return $json;
            }
        }

        # Prepare Data to send
        if(empty($order_info['transaction_id'])){
            $json['error'] = 'Previous order transaction id not found';
            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_encode($json));
            return $json;
        }
        $params = array(
            'username' 				=> trim($this->config->get($classname . '_mid')),
            'password' 				=> trim($this->config->get($classname . '_key')),
            'amount' 				=> $amount,
            'transactionid' 			=> $order_info['transaction_id'],
            'type' 					=> 'refund',
            'payment'				=> 'creditcard',
        );


        require(DIR_SYSTEM . '../catalog/controller/extension/payment/' . $classname . '.class.php');
        $payclass = New Bff();
        $result = $payclass->sendPayment($params);

        if ($this->config->get($classname . '_debug')) { $log->write("Request: " . print_r($params,1) . "\r\n Response: " . print_r($result,1) . "\r\n"); }

        $res = array();
        $message = '';

        if (!empty($result['error'])) { $json['error'] = $result['error']; }

        if (empty($json['error'])) {
            if (!empty($result['data'])) {
                parse_str($result['data'], $res);
                $message = print_r($result['data'],1);
            }
            if (!isset($res['response'])) { $json['error'] = $this->language->get('error_invalid'); }
        }

        if (empty($json['error'])) {
            if ($res['response'] == '1') {
                // Update the order in the order table. This is done purposely before all the other processing to ensure we do not lose the order if there are other problems.
            } else {
                if (isset($res['responsetext'])) {
                    $json['error'] = ('CODE: ' . $res['response_code'] . ' :: ' . $res['responsetext']);
                    if($res['response_code'] == 200 && $res['cvvresponse'] == 'N') {
                        $json['error'] = $this->language->get('error_card_details_wrong');
                    }
                } else {
                    $json['error'] = $this->language->get('error_declined');
                }
                return $json;
            }
        }

        ### END SPECIFIC DATA ###
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

	public function send() {

        $log = new Log('bff_debug.txt');
		# Generic Init
		$extension_type = 'extension/payment';
		$classname = str_replace('vq2-' . basename(DIR_APPLICATION) . '_' . strtolower(get_parent_class($this)) . '_' . str_replace('/', '_', $extension_type) . '_', '', basename(__FILE__, '.php'));
		$data['classname'] = $classname;
		$data = array_merge($data, $this->load->language($extension_type . '/' . $classname));

		# Order Info
		$this->load->model('checkout/order');
		$order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);

		# Common URL Values
		$callbackurl 	= $this->url->link($extension_type . '/' . $classname . '/callback', '', 'SSL');
		$cancelurl 		= $this->url->link('checkout/checkout', '', 'SSL');
		$successurl 	= $this->url->link('checkout/success');
		$declineurl 	= $this->url->link('checkout/checkout', '', 'SSL');

		$json = array();

		# Order Check
		if (!$order_info) {
			$json['error'] = $this->language->get('error_no_order_found');
			$this->response->addHeader('Content-Type: application/json');
			$this->response->setOutput(json_encode($json));
			return;
		}


		### START SPECIFIC DATA ###

		# Card Check
		$errornumber = '';
		$errortext = '';
        if(isset($_POST['is_card']) && $_POST['is_card'] != 1 ) {
            if(strlen($_POST['card_mon']) == 1) {
                $_POST['card_mon'] = '0'.$_POST['card_mon'];
                $this->request->post['card_mon'] = '0'.$this->request->post['card_mon'];
            }
            if (!$this->checkCreditCard (str_replace(' ', '',$_POST['card_num']), $_POST['card_type'], $_POST['card_cvv'], $_POST['card_mon'], $_POST['card_year'], $errornumber, $errortext)) {
			$json['error'] = $errortext;
			$this->response->addHeader('Content-Type: application/json');
			$this->response->setOutput(json_encode($json));
			return;
		}

		# Check if name is set
		if (empty($_POST['card_name'])) {
			$json['error'] = $this->language->get('error_invalid_card_name');
			$this->response->addHeader('Content-Type: application/json');
			$this->response->setOutput(json_encode($json));
			return;
		}
            if(str_word_count($_POST['card_name']) < 2) {
                $json['error'] = $this->language->get('error_invalid_lastname');
                $this->response->addHeader('Content-Type: application/json');
                $this->response->setOutput(json_encode($json));
                return;
            }
        } else {
            $show_cvv = $this->config->get('bff_show_cvv');;
            if(empty($show_cvv) && $show_cvv == 1) {
                if (empty($_POST['card_cvv']) || !$this->checkCreditCardForOld ($_POST['card_cvv'], $errornumber, $errortext)) {
                    $json['error'] = $errortext;
                    $this->response->addHeader('Content-Type: application/json');
                    $this->response->setOutput(json_encode($json));
                    return;
                }
            }
        }

        # Check for supported currency, otherwise convert
		$currencies = array(
			'USD' => 'USD'
		);
		if (in_array($order_info['currency_code'], $currencies)) {
			$currency_code = $currencies[$order_info['currency_code']];
		} else {
			$currency_code = 'USD';
		}
		$amount = str_replace(array(','), '', $this->currency->format($order_info['total'], $currency_code, FALSE, FALSE));
		if (!empty($this->request->post['card_name'])) {
			$names = explode(" ", trim($this->db->escape($this->request->post['card_name'])));
            // if only firstname then
            if (count($names) == 1) {
                $names[] = 'X';
            }
            // get the last index as last name
            $payment_lastname = array_splice($names, (count($names)-1), 1)[0];
            $payment_firstname = implode(" ", $names);
		} else {
			$payment_firstname = $order_info['payment_firstname'];
			$payment_lastname = $order_info['payment_lastname'];
		}
                if($this->config->get($classname . '_merchant_defined_field_1')) {
                    $merchant_defined_field_website = $this->config->get($classname . '_merchant_defined_field_1');        
                } else {
                    $merchant_defined_field_website = parse_url(HTTP_SERVER, PHP_URL_HOST);
                }

        $merchant_defined_field_country = $order_info['payment_country'];
        $merchant_defined_field_state = $order_info['payment_zone'];

        require(DIR_SYSTEM . '../catalog/controller/extension/payment/' . $classname . '.class.php');
        $payclass = New Bff();
        $customer_vault_id = '';
        $customer_id = '';
        $this->load->model('account/customer');
        if ($this->customer->isLogged()) {
            $customer_info = $this->model_account_customer->getCustomer($this->customer->getId());
            $customer_vault_id = $customer_info['customer_vault_id'];
            $customer_id = $customer_info['customer_id'];
        }
        elseif(!empty($_POST['t'])){
            // Get customer details from token
            $this->load->model("quickrenewal/customer");
            $customer_info = $this->model_quickrenewal_customer->get_customer_from_token($_GET['t']);
            $customer_vault_id = $customer_info['customer_vault_id'];
            $customer_id = $customer_info['customer_id'];
        }
        else{
            //return error
        }
        $save_card = 0;
        if(isset($_POST['is_card']) && $_POST['is_card'] == 1) { //if old card details checked
            if(!empty($customer_vault_id) && $customer_vault_id != 0) {
                $save_card = 3; // if already exits vault id
                $customer_vault = 'update_customer';
            } else {
                $log->write('Customer Vault ID no longer valid.');
                $json['error'] = 'Sorry you cannot checkout with this card at this time, Please checkout with the "Use new card" option instead.';
                $this->response->addHeader('Content-Type: application/json');
                $this->response->setOutput(json_encode($json));
                return;
            }
        }

        if(isset($_POST['is_card']) && $_POST['is_card'] == 2) { //if new card details checked
            if($_POST['save_card'] == 1) { //if checked save card
                // vault id exits
                if(!empty($customer_vault_id) && $customer_vault_id != 0) {
                    #delete previous customer vault id#
                    $params = array(
                        'username' => trim($this->config->get($classname . '_mid')),
                        'password' => trim($this->config->get($classname . '_key')),
                        'customer_vault_id' => $customer_vault_id,
                        'customer_vault' => 'delete_customer',
                    );

                    $result = $payclass->sendPayment($params);

                    if ($this->config->get($classname . '_debug')) { $log->write("Request: " . print_r($params,1) . "\r\n Response: " . print_r($result,1) . "\r\n"); }

                    $res_bff = array();
                    if (!empty($result['data'])) {
                        parse_str($result['data'], $res_bff);
                    }

                    $log->write('Previous customer vault id deleted');
                    $this->model_account_customer->editCustomerVaultIdOnly($customer_id, '');

                    if (isset($res_bff['responsetext']) && $res_bff['response'] == '3') {
                        $json['error'] = ('CODE: ' . $res_bff['response_code'] . ' :: ' . $res_bff['responsetext']);
                        $this->response->addHeader('Content-Type: application/json');
                        $this->response->setOutput(json_encode($json));
                        return;
                    }
                }

                #create new customer vault id#
                $save_card = 1; //for customer record update with new record case
                $customer_vault = 'add_customer';
            } else {
                $save_card = 0;  //customer record not created(remains old card on file)
            }
        }

        if($save_card == 1) { //for add new customer details on card
            $params = array(
                'username' 				=> trim($this->config->get($classname . '_mid')),
                'password' 				=> trim($this->config->get($classname . '_key')),
                'amount' 				=> $amount,
                'orderid' 				=> $order_info['order_id'],
                'ccnumber' 				=> str_replace(' ', '', $this->request->post['card_num']),
                'ccexp' 				=> ($_POST['card_mon'] . $this->request->post['card_year']), //MMYY
                'cvv' 					=> $this->request->post['card_cvv'],
                'type' 					=> !$this->config->get($classname . '_txntype') ? 'auth' : 'sale',
                'payment'				=> 'creditcard',
                'ipaddress' 			=> $order_info['ip'],
                'firstname' 			=> $payment_firstname,
                'lastname' 				=> $payment_lastname,
                'company' 				=> $order_info['payment_company'],
                'address1' 				=> $order_info['payment_address_1'],
                'address2' 				=> $order_info['payment_address_2'],
                'city' 					=> $order_info['payment_city'],
                'state' 				=> $order_info['payment_zone_code'],
                'zip' 					=> $order_info['payment_postcode'],
                'country' 				=> $order_info['payment_iso_code_2'],
                'phone' 				=> $order_info['telephone'],
                'email' 				=> $order_info['email'],
                'customer_vault'                => $customer_vault,
                'initiated_by'                  =>'customer',
                'stored_credential_indicator'   =>'used',
                'merchant_defined_field_1'      => $merchant_defined_field_website,
                'merchant_defined_field_2'      => $merchant_defined_field_country,
                'merchant_defined_field_3'      => $merchant_defined_field_state,
            );
        }
        else if($save_card == 3) { //payment using customer vault id
            $params = array(
                'username'              => trim($this->config->get($classname . '_mid')),
                'password'              => trim($this->config->get($classname . '_key')),
                'amount'                => $amount,
                'orderid'               => $order_info['order_id'],
                'customer_vault_id'             => $customer_vault_id,
                'initiated_by'                  =>'customer',
                'stored_credential_indicator'   =>'used',
                'ipaddress' 			        => $order_info['ip'],
                'merchant_defined_field_1'      => $merchant_defined_field_website,
                'merchant_defined_field_2'      => $merchant_defined_field_country,
                'merchant_defined_field_3'      => $merchant_defined_field_state,
            );
            if(isset($_POST['card_cvv'])) {
                $params['cvv'] = $_POST['card_cvv'];
            }

        } else {
            # Prepare Data to send
            $params = array(
                'username' => trim($this->config->get($classname . '_mid')),
                'password' => trim($this->config->get($classname . '_key')),
                'amount' => $amount,
                'orderid' => $order_info['order_id'],
                'ccnumber' => (isset($_POST['card_num']))? str_replace(' ', '', $this->request->post['card_num']) : '',
                'ccexp' => (isset($_POST['card_mon']) && isset($_POST['card_year']))? ($this->request->post['card_mon'] . $this->request->post['card_year']) : '',
                'cvv' => (isset($_POST['card_cvv']))? $this->request->post['card_cvv'] : '',
                'type' => !$this->config->get($classname . '_txntype') ? 'auth' : 'sale',
                'payment' => 'creditcard',
                'ipaddress' => $order_info['ip'],
                'firstname' => $payment_firstname,
                'lastname' => $payment_lastname,
                'company' => $order_info['payment_company'],
                'address1' => $order_info['payment_address_1'],
                'address2' => $order_info['payment_address_2'],
                'city' => $order_info['payment_city'],
                'state' => $order_info['payment_zone_code'],
                'zip' => $order_info['payment_postcode'],
                'country' => $order_info['payment_iso_code_2'],
                'phone' => $order_info['telephone'],
                'email' => $order_info['email'],
                'merchant_defined_field_1' => $merchant_defined_field_website,
                'merchant_defined_field_2' => $merchant_defined_field_country,
                'merchant_defined_field_3' => $merchant_defined_field_state,
            );
        }

		if ($this->cart->hasShipping()) {
			$params['shipping_firstname'] 	= $order_info['shipping_firstname'];
			$params['shipping_lastname'] 	= $order_info['shipping_lastname'];
			$params['shipping_company']		= $order_info['shipping_company'];
			$params['shipping_address1'] 	= $order_info['shipping_address_1'];
			$params['shipping_address2'] 	= $order_info['shipping_address_2'];
			$params['shipping_city'] 		= $order_info['shipping_city'];
			$params['shipping_state'] 		= $order_info['shipping_zone_code'];
			$params['shipping_zip'] 		= $order_info['shipping_postcode'];
			$params['shpping_country'] 		= $order_info['shipping_iso_code_2'];
		}

 		$result = $payclass->sendPayment($params);

		$params['ccnumber'] = 'xxxxxxxxxxx';
		$params['ccexp'] = 'xxxx';
		$params['cvv'] = 'xxx';
		if ($this->config->get($classname . '_debug')) { $log->write("Request: " . print_r($params,1) . "\r\n Response: " . print_r($result,1) . "\r\n"); }

		$res = array();
		$message = '';

		if (!empty($result['error'])) { $json['error'] = $result['error']; }

		if (empty($json['error'])) {
			if (!empty($result['data'])) {
				parse_str($result['data'], $res);
				$message = print_r($result['data'],1);
			}
			if (!isset($res['response'])) { $json['error'] = $this->language->get('error_invalid'); }
		}

		if (empty($json['error'])) {
			if ($res['response'] == '1') {
				// Update the order in the order table. This is done purposely before all the other processing to ensure we do not lose the order if there are other problems.
				if (version_compare(VERSION, '2.0', '>=')) { // v20x
					$this->model_checkout_order->addOrderHistory($this->session->data['order_id'], $this->config->get($classname . '_order_status_id'), $message, false);
				} else { //v15x
					$this->model_checkout_order->confirm($order_info['order_id'], $this->config->get($classname . '_order_status_id'), $order_info['comment']);
					$this->model_checkout_order->update($order_info['order_id'], $this->config->get($classname . '_order_status_id'), $message, FALSE);
				}
                #store save card file details on db
                if($save_card == 1) {  //if customer use save card on file details
                    if(isset($res['customer_vault_id'])){
                        $customer_card_expiry = $_POST['card_mon'].'/'.$_POST['card_year'];
                        $ccNum = str_replace(' ', '', $this->request->post['card_num']);
                        $customer_card_number = str_replace(range(0,9), "", substr($ccNum, 0, -4)) .  substr($ccNum, -4);
                        $res_customer_vault_id = $res['customer_vault_id'];
                        $this->model_account_customer->editCustomerVaultId($customer_id,$res_customer_vault_id,$customer_card_number,$customer_card_expiry);
                    }
                }
                if ($save_card == 3) {  //if customer update save card on file details
                    if(isset($res['customer_vault_id'])){
                        $res_customer_vault_id = $res['customer_vault_id'];
                        $this->model_account_customer->editCustomerVaultIdOnly($customer_id,$res_customer_vault_id);
                    }
                }
                $transaction_id = '';
                if(isset($res['transactionid'])) {
                    $transaction_id = $res['transactionid'];
                }
                $this->model_account_customer->updateTransactionId($order_info['order_id'],$transaction_id);
				$json['success'] = $this->url->link('checkout/success');
			} else {
				if (isset($res['responsetext'])) {
                                    $json['error'] = $res['responsetext'];                                    
                                    if($res['response_code'] == 200 || $res['response'] == 201) {
                                        $json['error'] = $this->language->get('error_transaction_was_declined');
                                    }
                                    if($res['response_code'] == 202) {
                                        $json['error'] = $this->language->get('error_insufficient_funds');
                                    }
                                    if($res['response_code'] == 203) {
                                        $json['error'] = $this->language->get('error_over_limit');
                                    }
                                     if($res['response_code'] == 204) {
                                        $json['error'] = $this->language->get('error_transaction_not_allowed');
                                    }
                                     if($res['response_code'] == 220) {
                                        $json['error'] = $this->language->get('error_incorrect_payment_information');
                                    }
                                     if($res['response_code'] == 221) {
                                        $json['error'] = $this->language->get('error_over_limit');
                                    }
                                     if($res['response_code'] == 222) {
                                        $json['error'] = $this->language->get('error_no_card_number_on_file');
                                    }
                                     if($res['response_code'] == 223) {
                                        $json['error'] = $this->language->get('error_expired_card');
                                    }
                                     if($res['response_code'] == 224) {
                                        $json['error'] = $this->language->get('error_expiration_date');
                                    }
                                     if($res['response_code'] == 225) {
                                        $json['error'] = $this->language->get('error_card_security_code');
                                    }
                                     if($res['response_code'] == 226) {
                                        $json['error'] = $this->language->get('error_invalid_PIN');
                                    }
                                     if($res['response_code'] == 240 || $res['response_code'] == 250 || $res['response_code'] == 251 || $res['response_code'] == 252 || $res['response_code'] == 253 || $res['response_code'] == 260) {
                                        $json['error'] = $this->language->get('error_call_issuer');
                                    }
                                     if($res['response_code'] == 261 || $res['response_code'] == 262) {
                                        $json['error'] = $this->language->get('error_declined_stop');
                                    }
                                     if($res['response_code'] == 263) {
                                        $json['error'] = $this->language->get('error_declined_update');
                                    }
                                    if($res['response_code'] == 264) {
                                        $json['error'] = $this->language->get('error_declined_retry');
                                    }
                                    if($res['response_code'] == 300 || $res['response_code'] == 400 || $res['response_code'] == 460 || $res['response_code'] == 441 || $res['response_code'] == 440) {
                                        $json['error'] = $this->language->get('error_transaction_rejected');
                                    }
                                    if($res['response_code'] == 300 || $res['response'] == 430) {
                                        $json['error'] = $this->language->get('error_duplicate_transaction');
                                    }
                                    if($res['response_code'] == 410 || $res['response_code'] == 411 || $res['response_code'] == 420 || $res['response_code'] == 421) {
                                        $json['error'] = $this->language->get('error_invalid_merchant');
                                    }
                                    if($res['response_code'] == 461) {
                                        $json['error'] = $this->language->get('error_cart_type');
                                    }
                                    if($res['response_code'] == 200 && $res['cvvresponse'] == 'N') {
                                        $json['error'] = $this->language->get('error_card_details_wrong');
                                    }
				} else {
					$json['error'] = $this->language->get('error_declined');
				}
			}
		}


		### END SPECIFIC DATA ###

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

    private function checkCreditCardForOld ($cvv, &$errornumber, &$errortext) {
        $ccErrorNo = 0;
        $ccErrors[0] = $this->language->get('error_card_cvv');
        if (strlen($cvv) == 3 || strlen($cvv) == 4) {
            return true;
        } else {
            $errornumber = 0;
            $errortext = $ccErrors[$errornumber];
            return false;
        }
        // The credit card is in the required format.

    }

	private function checkCreditCard ($cardnumber, $cardtype, $cvv, $expMon, $expYear, &$errornumber, &$errortext) {

		// Define the cards we support. You may add additional card types.

		//  Name:      As in the selection box of the form - must be same as user's
		//  Length:    List of possible valid lengths of the card number for the card
		//  prefixes:  List of possible prefixes for the card
		//  cvv_length:  Valid cvv code length for the card
		//  luhn Boolean to say whether there is a check digit

		// Don't forget - all but the last array definition needs a comma separator!

		$cards = array(
			array ('name' => 'amex',
				  'length' => '15',
				  'prefixes' => '34,37',
				  'cvv_length' => '4',
				  'luhn' => true
				 ),
			array ('name' => 'diners',
				  'length' => '14,16',
				  'prefixes' => '36,38,54,55',
				  'cvv_length' => '3',
				  'luhn' => true
				 ),
			array ('name' => 'discover',
				  'length' => '16',
				  'prefixes' => '6011,622,64,65',
				  'cvv_length' => '3',
				  'luhn' => true
				 ),
			array ('name' => 'jcb',
				  'length' => '16',
				  'prefixes' => '35',
				  'cvv_length' => '3',
				  'luhn' => true
				 ),
			array ('name' => 'maestro',
				  'length' => '12,13,14,15,16,18,19',
				  'prefixes' => '5018,5020,5038,6304,6759,6761,6762,6763',
				  'cvv_length' => '3',
				  'luhn' => true
				 ),
			array ('name' => 'mastercard',
				  'length' => '16',
				  'prefixes' => '51,52,53,54,55',
				  'cvv_length' => '3',
				  'luhn' => true
				 ),
			array ('name' => 'solo',
				  'length' => '16,18,19',
				  'prefixes' => '6334,6767',
				  'cvv_length' => '3',
				  'luhn' => true
				 ),
			array ('name' => 'switch',
				  'length' => '16,18,19',
				  'prefixes' => '4903,4905,4911,4936,564182,633110,6333,6759',
				  'cvv_length' => '3',
				  'luhn' => true
				 ),
			array ('name' => 'visa',
				  'length' => '16',
				  'prefixes' => '4',
				  'cvv_length' => '3',
				  'luhn' => true
				 ),
			array ('name' => 'visa_electron',
				  'length' => '16',
				  'prefixes' => '417500,4917,4913,4508,4844',
				  'cvv_length' => '3',
				  'luhn' => true
				 ),
			array ('name' => 'laser',
				  'length' => '16,17,18,19',
				  'prefixes' => '6304,6706,6771,6709',
				  'cvv_length' => '3',
				  'luhn' => true
				 )
		);


		$ccErrorNo = 0;
		$ccErrors[0] = $this->language->get('error_card_type');
		$ccErrors[1] = $this->language->get('error_card_num');
		$ccErrors[2] = $this->language->get('error_card_cvv');
		$ccErrors[3] = $this->language->get('error_card_exp');
		
		// Establish card type
		$cardType = -1;
		for ($i=0; $i<sizeof($cards); $i++) {

			// See if it is this card (ignoring the case of the string)
			if (strtolower($cardtype) == strtolower($cards[$i]['name'])) {
				$cardType = $i;
				break;
			}
		}

		// If card type not found, report an error
		if ($cardType == -1) {
			$errornumber = 0;
			$errortext = $ccErrors[$errornumber];
			return false;
		}

		// Ensure that the user has provided a credit card number
		if (strlen($cardnumber) == 0)  {
			$errornumber = 1;
			$errortext = $ccErrors[$errornumber];
			return false;
		}

		// Remove any spaces from the credit card number
		$cardNo = str_replace (array(' ', '-'), '', $cardnumber);

		// Check that the number is numeric and of the right sort of length.
		if (!preg_match("/^[0-9]{13,19}$/", $cardNo))  {
			$errornumber = 1;
			$errortext = $ccErrors[$errornumber];
			return false;
		}

		// Remove any spaces or non-numerics from the expiry date fields
		$expMon = preg_replace('/[^0-9]/', '', $expMon);
		$expYear = preg_replace('/[^0-9]/', '', $expYear);

		// Check expiry length
        if (strlen($expMon) != 2 || strlen($expYear) != 2) {
			$errornumber = 3;
			$errortext = $ccErrors[$errornumber];
			return false;
		}

		// Check the expiry date
		/* Get timestamp of midnight on day after expiration month. */
		$exp_ts = mktime(0, 0, 0, $expMon + 1, 1, $expYear);

		$cur_ts = time();
		/* Don't validate for dates more than 10 years in future. */
		$max_ts = $cur_ts + (10 * 365 * 24 * 60 * 60);

		if ($exp_ts < $cur_ts || $exp_ts > $max_ts) {
			$errornumber = 3;
			$errortext = $ccErrors[$errornumber];
			return false;
		}

		// Now check the modulus 10 check digit - if required
		if ($cards[$cardType]['luhn']) {
			$checksum = 0;                                  // running checksum total
			$mychar = "";                                   // next char to process
			$j = 1;                                         // takes value of 1 or 2

			// Process each digit one by one starting at the right
			for ($i = strlen($cardNo) - 1; $i >= 0; $i--) {

				// Extract the next digit and multiply by 1 or 2 on alternative digits.
				$calc = $cardNo{$i} * $j;

				// If the result is in two digits add 1 to the checksum total
				if ($calc > 9) {
					$checksum = $checksum + 1;
					$calc = $calc - 10;
				}

				// Add the units element to the checksum total
				$checksum = $checksum + $calc;

				// Switch the value of j
				if ($j ==1) {$j = 2;} else {$j = 1;};
			}

			// All done - if checksum is divisible by 10, it is a valid modulus 10.
			// If not, report an error.
			if ($checksum % 10 != 0) {
				$errornumber = 1;
				$errortext = $ccErrors[$errornumber];
				return false;
			}
		}

		// The following are the card-specific checks we undertake.

		// Load an array with the valid prefixes for this card
		$prefix = explode(',', $cards[$cardType]['prefixes']);

		// Now see if any of them match what we have in the card number
		$PrefixValid = false;
		for ($i=0; $i<sizeof($prefix); $i++) {
			$exp = '/^' . $prefix[$i] . '/';
			if (preg_match($exp,$cardNo)) {
				$PrefixValid = true;
				break;
			}
		}

		// If it isn't a valid prefix there's no point at looking at the length
		if (!$PrefixValid) {
			$errornumber = 1;
			$errortext = $ccErrors[$errornumber];
			return false;
		}

		// See if the length is valid for this card
		$LengthValid = false;
		$lengths = explode(',', $cards[$cardType]['length']);
		for ($j=0; $j<sizeof($lengths); $j++) {
			if (strlen($cardNo) == $lengths[$j]) {
				$LengthValid = true;
				break;
			}
		}

		// See if all is OK by seeing if the length was valid.
		if (!$LengthValid) {
			$errornumber = 1;
			$errortext = $ccErrors[$errornumber];
			return false;
		};

		$cvv_length = $cards[$cardType]['cvv_length'];
		if (strlen($cvv) != $cvv_length) {
			$errornumber = 2;
			$errortext = $ccErrors[$errornumber];
			return false;
		}

		// The credit card is in the required format.
		return true;
	}
}
?>