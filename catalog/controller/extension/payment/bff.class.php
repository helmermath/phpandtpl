<?php
/**
 * @author Qphoria@gmail.com
 * @web http://www.opencartguru.com/
 *
 * @usage
 *		$params = array(
 *			'xxx' => 'value1',
 *			'yyy' => 'value2',
 *			'zzz' => 'value3',
 *		);
 *
 *		$payclass = New PayClass();
 *		$payclass->sendPayment($params);
 */
 
 /* Notes
 To cause a declined message, pass an amount less than 1.00.
 To trigger a fatal error message, pass an invalid card number.
 To simulate an AVS Match, pass 888 in the address1 field, 77777 for zip.
 To simulate a CVV Match, pass 999 in the cvv field. 
 */

class Bff {

	private $_url = 'https://secure.nmi.com/api/transact.php';
	private $_log;

	public function __construct($logpath = '') {
		if ($logpath && is_dir($logpath) && is_writable($logpath)) { $this->_log = $logpath .  basename(__FILE__, '.php') . '.log'; }
	}

	public function sendPayment($params, $url = '') {
		if (!$url) { $url = $this->_url; }
		$data = '';
		foreach ($params as $key => $value) {
			$data .= "&$key=$value";
		}
		$data = trim($data,"&");
		
		return $this->curl_post($url, $data);
	}

	private function writeLog($msg) {
		if ($this->_log) {
			$msg = (str_repeat('-', 70) . "\r\n" . $msg . "\r\n" . str_repeat('-', 70) . "\r\n");
			file_put_contents($this->_log, $msg, FILE_APPEND);
		}
	}

	private function curl_get ($url, $data) {
		$ch = curl_init($url . $data);
		curl_setopt($ch, CURLOPT_PORT, 443);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FORBID_REUSE, 1);
        curl_setopt($ch, CURLOPT_FRESH_CONNECT, 1);

		$response = array();

		if (curl_error($ch)) {
			$response['error'] = curl_error($ch) . '(' . curl_errno($ch) . ')';
		} else {
			$response['data'] = curl_exec($ch);
		}

		$this->writeLog(__FUNCTION__ . "\r\n" . print_r($data,1) . "\r\n---------------\r\n" . print_r($response,1) . "\r\n");
		
		curl_close($ch);

		return $response;
	}
	
	private function curl_post($url, $data) {
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_PORT, 443);
		//curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		$response = array();

		if (curl_error($ch)) {
			$response['error'] = curl_error($ch) . '(' . curl_errno($ch) . ')';
		} else {
			$response['data'] = curl_exec($ch);
		}
		
		$this->writeLog(__FUNCTION__ . "\r\n" . print_r($data,1) . "\r\n---------------\r\n" . print_r($response,1) . "\r\n");
		
		curl_close($ch);
				
		return $response;
	}
}
?>