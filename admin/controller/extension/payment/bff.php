<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

class ControllerExtensionPaymentBff extends Controller {
	private $error = array();

	public function index() {

		$errors = array(
			'warning'
		);

		$extension_type = 'extension/payment';
		$classname = str_replace('vq2-' . basename(DIR_APPLICATION) . '_' . strtolower(get_parent_class($this)) . '_' . str_replace('/', '_', $extension_type) . '_', '', basename(__FILE__, '.php'));

		if (!isset($this->session->data['token'])) { $this->session->data['token'] = 0; }

		$data['classname'] = $classname;
		if (isset($this->session->data['user_token'])) {
			$data['token'] = $this->session->data['user_token'];
			$token_key = 'user_token';
			$extension_path = 'marketplace/extension';
		} else {
			$data['token'] = $this->session->data['token'];
			$token_key = 'token';
			$extension_path = 'extension/extension';
		}
		$data = array_merge($data, $this->load->language($extension_type . '/' . $classname));

		if (isset($data['error_fields'])) {
			$errors = array_merge(explode(",", $data['error_fields']), $errors);
		}

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('setting/setting');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && ($this->validate($errors))) {
			foreach ($this->request->post as $key => $value) {
				if (is_array($value)) { $this->request->post[$key] = implode(',', $value); }
				$this->request->post[$classname.'_'.$key] = $this->request->post[$key];
				unset($this->request->post[$key]);
			}

			$this->model_setting_setting->editSetting($classname, $this->request->post);
			//3.0 compatibility - must insert status and sort order with extension type prefix to satisfy some validation
			if (version_compare(VERSION, '3.0', '>=')) {
				foreach ($this->request->post as $key => $value) {
					if ($key == $classname . '_status' || $key == $classname . '_sort_order') {
						$this->db->query("INSERT INTO " . DB_PREFIX . "setting SET store_id = '0', `code` = '" . $this->db->escape($classname) . "', `key` = '" . $this->db->escape(str_replace("extension/", "", $extension_type).'_'.$key) . "', `value` = '" . $this->db->escape($value) . "'");
					}
				}
			}
			//

			$this->session->data['success'] = $this->language->get('text_success');

			$this->response->redirect(str_replace("&amp;", "&", $this->url->link($extension_path, $token_key.'=' . $data['token'] . '&type=' . basename($extension_type), 'SSL')));
		}

		$data['breadcrumbs'] = array();

   		$data['breadcrumbs'][] = array(
       		'href'      => $this->url->link('common/home', $token_key.'=' . $data['token'], 'SSL'),
       		'text'      => $this->language->get('text_home'),
      		'separator' => FALSE
   		);

   		$data['breadcrumbs'][] = array(
       		'href'      => $this->url->link($extension_path, $token_key.'=' . $data['token'] . '&type=' . basename($extension_type), 'SSL'),
       		'text'      => $this->language->get('text_' . basename($extension_type)),
      		'separator' => ' :: '
   		);

   		$data['breadcrumbs'][] = array(
       		'href'      => $this->url->link($extension_type . '/' . $classname, $token_key.'=' . $data['token'], 'SSL'),
       		'text'      => $this->language->get('heading_title'),
      		'separator' => ' :: '
   		);

		$data['action'] = $this->url->link($extension_type . '/' . $classname, $token_key.'=' . $data['token'], 'SSL');

		$data['cancel'] = $this->url->link($extension_path, 'type=payment&'.$token_key.'=' . $data['token'], 'SSL');

        foreach ($errors as $error) {
			if (isset($this->error[$error])) {
				$data['error_' . $error] = $this->error[$error];
			} else {
				$data['error_' . $error] = '';
			}
		}

		# Extension Type Icon
		switch($extension_type) {
			case 'payment':
				$data['icon_class'] = 'credit-card';
				break;
			case 'shipping':
				$data['icon_class'] = 'truck';
				break;
			default:
				$data['icon_class'] = 'puzzle-piece';
		}

		$data['extension_class'] = $extension_type;
		$data['tab_class'] = 'htabs'; //vtabs or htabs

		# Geozones
		$geo_zones = array();
		$this->load->model('localisation/geo_zone');
		$geo_zones[0] = $this->language->get('text_all_zones');
		foreach ($this->model_localisation_geo_zone->getGeoZones() as $geozone) {
			$geo_zones[$geozone['geo_zone_id']] = $geozone['name'];
		}

		# Tax Classes
		$tax_classes = array();
		$this->load->model('localisation/tax_class');
		$tax_classes[0] = $this->language->get('text_none');
		foreach ($this->model_localisation_tax_class->getTaxClasses() as $tax_class) {
			$tax_classes[$tax_class['tax_class_id']] = $tax_class['title'];
		}

		# Order Statuses
		$order_statuses = array();
		$this->load->model('localisation/order_status');
		foreach ($this->model_localisation_order_status->getOrderStatuses() as $order_status) {
			$order_statuses[$order_status['order_status_id']] = $order_status['name'];
		}

		# Customer Groups
		$customer_groups = array();
		if (file_exists(DIR_APPLICATION . 'model/customer/customer_group.php')) { $this->load->model('customer/customer_group'); $cgmodel = 'customer'; } else { $this->load->model('sale/customer_group'); $cgmodel = 'sale'; }
		$customer_groups[0] = $this->language->get('text_guest');
		foreach ($this->{'model_' . $cgmodel . '_customer_group'}->getCustomerGroups() as $customer_group) {
			$customer_groups[$customer_group['customer_group_id']] = $customer_group['name'];
		}

        $data['force_save_card'] = $this->config->get('bff_force_save_card');
        $data['show_cvv'] = $this->config->get('bff_show_cvv');
        if(!isset($data['show_cvv'])){
            $data['show_cvv'] = 1;
        }

		# Languages
		$languages = array();
		$this->load->model('localisation/language');
		foreach ($this->model_localisation_language->getLanguages() as $language) {
			$languages[$language['language_id']] = $language['name'];
		}

		# Tabs
		$data['tabs'] = array();

		# Fields
		$data['fields'] = array();

        $data['tabs'][] = array(
            'id'		=> 'tab_general',
            'title'		=> $this->language->get('tab_general')
        );

        $data['tabs'][] = array(
            'id'		=> 'tab_debug',
            'title'		=> $this->language->get('tab_debug')
        );

        $data['fields'][] = array(
            'tab'			=> 'tab_general',
            'entry' 		=> $this->language->get('entry_status'),
            'type'			=> 'select',
            'name' 			=> 'status',
            'value' 		=> (isset($this->request->post['status'])) ? $this->request->post['status'] : $this->config->get($classname . '_status'),
            'required' 		=> false,
            'options'		=> array(
                '0' => $this->language->get('text_disabled'),
                '1' => $this->language->get('text_enabled')
            ),
            'help'			=> ($this->language->get('help_status') != 'help_status' ? $this->language->get('help_status') : ''),
            'tooltip'		=> ($this->language->get('tooltip_status') != 'tooltip_status' ? $this->language->get('tooltip_status') : '')
        );

        foreach ($languages as $language_id => $language_name) {
            $data['fields'][] = array(
                'entry' 		=> '[ ' . $language_name . ' ] ' . $this->language->get('entry_title'),
                'type'			=> 'text',
                'size'			=> '20',
                'name' 			=> 'title_' . $language_id,
                'value' 		=> ((isset($this->request->post['title_' . $language_id])) ? $this->request->post['title_' . $language_id] : $this->config->get($classname . '_title_' . $language_id) ? $this->config->get($classname . '_title_' . $language_id) : ucwords(str_replace(array('-','_','.'), " ", $classname))),
                'required' 		=> false,
                'help'			=> ($this->language->get('help_title') != 'help_title' ? $this->language->get('help_title') : ''),
                'tooltip'		=> ($this->language->get('tooltip_title') != 'tooltip_title' ? $this->language->get('tooltip_title') : '')
            );
        }

        $data['fields'][] = array(
            'tab'			=> 'tab_general',
            'entry' 		=> $this->language->get('entry_mid'),
            'type'			=> 'text',
            'name' 			=> 'mid',
            'value' 		=> (isset($this->request->post['mid'])) ? $this->request->post['mid'] : $this->config->get($classname . '_mid'),
            'required' 		=> true,
            'help'			=> ($this->language->get('help_mid') != 'help_mid' ? $this->language->get('help_mid') : ''),
            'tooltip'		=> ($this->language->get('tooltip_mid') != 'tooltip_mid' ? $this->language->get('tooltip_mid') : '')
        );

        $data['fields'][] = array(
            'tab'			=> 'tab_general',
            'entry' 		=> $this->language->get('entry_key'),
            'type'			=> 'text',
            'name' 			=> 'key',
            'value' 		=> (isset($this->request->post['key'])) ? $this->request->post['key'] : $this->config->get($classname . '_key'),
            'required' 		=> true,
            'help'			=> ($this->language->get('help_key') != 'help_key' ? $this->language->get('help_key') : ''),
            'tooltip'		=> ($this->language->get('tooltip_key') != 'tooltip_key' ? $this->language->get('tooltip_key') : '')
        );

        $data['fields'][] = array(
            'tab'			=> 'tab_general',
            'entry' 		=> $this->language->get('entry_txntype'),
            'type'			=> 'select',
            'name' 			=> 'txntype',
            'value' 		=> (isset($this->request->post['txntype'])) ? $this->request->post['txntype'] : $this->config->get($classname . '_txntype'),
            'required' 		=> false,
            'options'		=> array(
                '0' => 'Auth',
                '1' => 'Sale'
            ),
            'help'			=> ($this->language->get('help_txntype') != 'help_txntype' ? $this->language->get('help_txntype') : ''),
            'tooltip'		=> ($this->language->get('tooltip_txntype') != 'tooltip_txntype' ? $this->language->get('tooltip_txntype') : '')
        );

        $data['fields'][] = array(
            'tab'			=> 'tab_general',
            'entry' 		=> $this->language->get('entry_total'),
            'type'			=> 'text',
            'name' 			=> 'total',
            'value' 		=> (isset($this->request->post['total'])) ? $this->request->post['total'] : $this->config->get($classname . '_total'),
            'required' 		=> false,
            'help'			=> ($this->language->get('help_total') != 'help_total' ? $this->language->get('help_total') : ''),
            'tooltip'		=> ($this->language->get('tooltip_total') != 'tooltip_total' ? $this->language->get('tooltip_total') : '')
        );

        $data['fields'][] = array(
            'tab'			=> 'tab_general',
            'entry' 		=> $this->language->get('entry_order_status'),
            'type'			=> 'select',
            'name' 			=> 'order_status_id',
            'value' 		=> (isset($this->request->post['order_status_id'])) ? $this->request->post['order_status_id'] : $this->config->get($classname . '_order_status_id'),
            'required' 		=> false,
            'options'		=> $order_statuses,
            'help'			=> ($this->language->get('help_order_status') != 'help_order_status' ? $this->language->get('help_order_status') : ''),
            'tooltip'		=> ($this->language->get('tooltip_order_status') != 'tooltip_order_status' ? $this->language->get('tooltip_order_status') : '')
        );

        $data['fields'][] = array(
            'tab'			=> 'tab_general',
            'entry' 		=> $this->language->get('entry_tax_class'),
            'type'			=> 'select',
            'name' 			=> 'tax_class_id',
            'value' 		=> (isset($this->request->post['tax_class_id'])) ? $this->request->post['tax_class_id'] : $this->config->get($classname . '_tax_class_id'),
            'required' 		=> false,
            'options'		=> $tax_classes,
            'help'			=> ($this->language->get('help_tax_class') != 'help_tax_class' ? $this->language->get('help_tax_class') : ''),
            'tooltip'		=> ($this->language->get('tooltip_tax_class') != 'tooltip_tax_class' ? $this->language->get('tooltip_tax_class') : '')
        );

        $data['fields'][] = array(
            'tab'			=> 'tab_general',
            'entry' 		=> $this->language->get('entry_geo_zone'),
            'type'			=> 'select',
            'name' 			=> 'geo_zone_id',
            'value' 		=> (isset($this->request->post['geo_zone_id'])) ? $this->request->post['geo_zone_id'] : $this->config->get($classname . '_geo_zone_id'),
            'required' 		=> false,
            'options'		=> $geo_zones,
            'help'			=> ($this->language->get('help_geo_zone') != 'help_geo_zone' ? $this->language->get('help_geo_zone') : ''),
            'tooltip'		=> ($this->language->get('tooltip_geo_zone') != 'tooltip_geo_zone' ? $this->language->get('tooltip_geo_zone') : '')
        );

        $data['fields'][] = array(
            'tab'			=> 'tab_general',
            'entry'			=> $this->language->get('entry_sort_order'),
            'type'			=> 'text',
            'name'			=> 'sort_order',
            'value'			=> (isset($this->request->post['sort_order'])) ? $this->request->post['sort_order'] : $this->config->get($classname . '_sort_order'),
            'required'		=> false,
            'help'			=> ($this->language->get('help_sort_order') != 'help_sort_order' ? $this->language->get('help_sort_order') : ''),
            'tooltip'		=> ($this->language->get('tooltip_sort_order') != 'tooltip_sort_order' ? $this->language->get('tooltip_sort_order') : '')
        );

        $data['fields'][] = array(
            'tab'			=> 'tab_debug',
            'entry' 		=> $this->language->get('entry_debug'),
            'type'			=> 'select',
            'name' 			=> 'debug',
            'value' 		=> (isset($this->request->post['debug'])) ? $this->request->post['debug'] : $this->config->get($classname . '_debug'),
            'required' 		=> false,
            'options'		=> array(
                '0' => $this->language->get('text_disabled'),
                '1' => $this->language->get('text_enabled')
            ),
            'help'			=> ($this->language->get('help_debug') != 'help_debug' ? $this->language->get('help_debug') : ''),
            'tooltip'		=> ($this->language->get('tooltip_debug') != 'tooltip_debug' ? $this->language->get('tooltip_debug') : '')
        );

        $data['fields'][] = array(
            'tab'			=> 'tab_debug',
            'entry'			=> $this->language->get('entry_debug_file'),
            'type'			=> 'label',
            'name'			=> '',
            'value'			=> DIR_LOGS . $classname . '_debug.txt',
            'help'			=> ($this->language->get('help_debug_file') != 'help_debug_file' ? $this->language->get('help_debug_file') : ''),
            'tooltip'		=> ($this->language->get('tooltip_debug_file') != 'tooltip_debug_file' ? $this->language->get('tooltip_debug_file') : '')
        );

        $data['fields'][] = array(
            'tab'			=> 'tab_debug',
            'entry'			=> '',
            'type'			=> 'textarea',
            'cols'			=> '160',
            'rows'			=> '100',
            'name'			=> '',
            'value'			=> (file_exists(DIR_LOGS . $classname . '_debug.txt')) ? file_get_contents(DIR_LOGS . $classname . '_debug.txt') : 'empty'
        );

        $data['fields'][] = array(
            'tab'			=> 'tab_support',
            'entry'			=> 'Troubleshooting Info: ',
            'type'			=> 'label',
            'name'			=> 'troubleshooting',
            'value'			=> '',
            'help'			=> '',
            'tooltip'		=> ''
        );

        $data['fields'][] = array(
            'tab'			=> 'tab_support',
            'entry'			=> 'Support Info:',
            'type'			=> 'label',
            'name'			=> 'support',
            'value'			=> 'For support questions, contact me at qphoria@gmail.com or on skype: taqmobile',
            'help'			=> '',
            'tooltip'		=> ''
        );

        $data['fields'][] = array(
            'tab'			=> 'tab_general',
            'entry'			=> 'Version:',
            'type'			=> 'text',
            'name'			=> 'version',
            'value'			=> '2.2.0',
            'required'		=> false,
            'params'        => 'readonly="readonly"'
        );
         $data['fields'][] = array(
            'tab'			=> 'tab_general',
            'entry' 		=> $this->language->get('entry_merchant_defined_field'),
            'type'			=> 'text',
            'name' 			=> 'merchant_defined_field_1',
            'value' 		=> (isset($this->request->post['merchant_defined_field_1'])) ? $this->request->post['merchant_defined_field_1'] : (!empty($this->config->get($classname . '_merchant_defined_field_1')) ? $this->config->get($classname . '_merchant_defined_field_1') : parse_url(HTTP_SERVER, PHP_URL_HOST)),
            'required' 		=> true,
            'help'			=> '',
            'tooltip'		=> ''
        );

        # Get Mod list
		$domain = rawurlencode(str_ireplace('www.', '', parse_url(HTTP_SERVER, PHP_URL_HOST)));
		$url = "https://opencartguru.com/index.php?route=feed/modlist&classname=$classname&domain=$domain";
		$ch = @curl_init();
		@curl_setopt ($ch, CURLOPT_URL, $url);
		@curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		@curl_setopt ($ch, CURLOPT_TIMEOUT, 10);
		@curl_setopt ($ch, CURLOPT_FOLLOWLOCATION, 1);
		@curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
		$result = @curl_exec ($ch);
		@curl_close($ch);

		$dom = new DOMDocument();
		libxml_use_internal_errors(true);
		@$dom->loadXML($result);
		$products = @$dom->getElementsByTagName('product');
		$data['mods'] = array();
		foreach ($products as $i => $product) {
			$data['mods'][$i]['extension_id'] = $product->getElementsByTagName('extension_id')->item(0)->nodeValue;
			$data['mods'][$i]['title'] = $product->getElementsByTagName('title')->item(0)->nodeValue;
			$data['mods'][$i]['price'] = $product->getElementsByTagName('price')->item(0)->nodeValue;
			$data['mods'][$i]['date_added'] = date('Y-m-d', strtotime($product->getElementsByTagName('date_added')->item(0)->nodeValue));
			$data['mods'][$i]['img'] = $product->getElementsByTagName('img')->item(0)->nodeValue;
			$data['mods'][$i]['features'] = explode(';', $product->getElementsByTagName('features')->item(0)->nodeValue);
			$data['mods'][$i]['opencart_version'] = explode(',', $product->getElementsByTagName('opencart_version')->item(0)->nodeValue);
			$data['mods'][$i]['latest_version'] = $product->getElementsByTagName('latest_version')->item(0)->nodeValue;
			$data['mods'][$i]['ocg_link'] = $product->getElementsByTagName('ocg_link')->item(0)->nodeValue;
			if (is_numeric($data['mods'][$i]['extension_id'])) {
				$data['mods'][$i]['oc_link'] = $product->getElementsByTagName('oc_link')->item(0)->nodeValue;
			} else {
				$data['mods'][$i]['oc_link'] = $product->getElementsByTagName('oc_search_link')->item(0)->nodeValue;
			}
		}

		# Compatibility
	
		if (version_compare(VERSION, '2.0', '>=')) { // v2.0.x Compatibility
			$data['header'] = $this->load->controller('common/header');
			$data['menu'] = $this->load->controller('common/menu');
			$data['column_left'] = $this->load->controller('common/column_left');
			$data['footer'] = $this->load->controller('common/footer');
			if (version_compare(VERSION, '3.0', '>=')) { // v3.x Compatibility to support twig and tpl files
				$file = (DIR_TEMPLATE . $extension_type . '/' .$classname.'.twig');
				if (is_file($file)) {
					$this->response->setOutput($this->load->view($extension_type . '/' .$classname, $data));
				} else {
					$file = (DIR_TEMPLATE . $extension_type . '/' .$classname.'.tpl');
					extract($data);
					ob_start();
					if (class_exists('VQMod')) { require(\VQMod::modCheck(modification($file), $file));	} else { require(modification($file)); }
					$this->response->setOutput(ob_get_clean());
				}
			} elseif (version_compare(VERSION, '2.2', '>=')) { // v2.2.x Compatibility
				$this->response->setOutput($this->load->view($extension_type . '/'. $classname, $data));
			} else { // 2.x
				$this->response->setOutput($this->load->view($extension_type . '/'.$classname.'.tpl', $data));
			}
		} elseif (version_compare(VERSION, '2.0', '<')) {  // 1.5.x Backwards Compatibility
			$this->data = array_merge($this->data, $data);
			$this->id       = 'content';
			$this->template = $extension_type . '/' . $classname . '.tpl';

			$this->children = array(
	            'common/header',
	            'common/footer'
        	);
        	$this->response->setOutput($this->render(TRUE));
		}

	}



	private function validate($errors = array()) {
		$extension_type = 'extension/payment';
		$classname = str_replace('vq2-' . basename(DIR_APPLICATION) . '_' . strtolower(get_parent_class($this)) . '_' . str_replace('/', '_', $extension_type) . '_', '', basename(__FILE__, '.php'));

		if (!$this->user->hasPermission('modify', $extension_type. '/' . $classname)) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		foreach ($errors as $error) {
			if (isset($this->request->post[$error]) && !$this->request->post[$error]) {
				$this->error[$error] = $this->language->get('error_' . $error);
			}
		}

		if (!$this->error) {
			return TRUE;
		} else {
			return FALSE;
		}
	}
}
?>