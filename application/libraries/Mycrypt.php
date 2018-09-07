<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');

class Mycrypt {

	private $_CI;
	private $hash_method = 'sha1';
	private $private_key = '';
	private $digit_sum = 8;
	private $digit_api_key = 56;
	private $charset = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
	private $api_key_only = true;

	public function __construct()
  {
		$this->_CI =& get_instance();
		$this->_CI->config->load('mycrypt');

		$this->hash_method = $this->_CI->config->item('mycrypt_hash_method');
		$this->private_key = $this->_CI->config->item('mycrypt_private_key');
		$this->digit_sum = $this->_CI->config->item('mycrypt_digit_sum');
		$this->digit_api_key = $this->_CI->config->item('mycrypt_digit_api_key');
		$this->charset = $this->_CI->config->item('mycrypt_charset');
		$this->api_key_only = $this->_CI->config->item('mycrypt_api_key_only');
	}

	protected function str_first($str, $length) {
		return substr($str, 0, $length);
	}

	protected function str_last($str, $length) {
		return substr($str, -$length);
	}

	protected function generate_hash($s1, $s2) {
		return strtoupper(hash($this->hash_method, $s1 . $s2));
	}

	protected function generate_check_sum($key){
		$h = $this->generate_hash($key, "", $this->hash_method);
		return $this->str_last($h, $this->digit_sum);
	}

	protected function generate_random_string($len = 8) {
		$randString = "";
		for ($i = 0; $i < $len; $i++) {
			$randString .= $this->charset[mt_rand(0, strlen($this->charset) - 1)];
		}
		return $randString;
	}

	public function generate_api(){
		$key = $this->generate_random_string($this->digit_api_key);
		$key_sum = $this->generate_check_sum($key.$this->private_key);
		$data = $key.$key_sum;
		return $data;
	}

	public function generate_ref($len=4){
		$str = "";
		$characters = array_merge(range('A','Z'));
		$max = count($characters) - 1;
		for ($i = 0; $i < $len; $i++) {
			$rand = mt_rand(0, $max);
			$str .= $characters[$rand];
		}
		return $str;
	}

	public function generate_pin($api, $emp_id, $ref, $pin_length) {
		$words = $this->generate_hash($api.$this->private_key.$emp_id.$ref, $ref.$emp_id.$this->private_key.$api);
		$sum = preg_replace('/[A-Z]+/', '', $words);
		$pin = $this->str_last($sum, $pin_length);
		return $pin;
	}

	public function generate_expire($time_alive){
		return time() + $time_alive;
	}

	public function ValidateOTP($api, $emp_id, $ref, $pin, $pin_length){
		$words = $this->generate_hash($api.$this->private_key.$emp_id.$ref, $ref.$emp_id.$this->private_key.$api);
		$sum = preg_replace('/[A-Z]+/', '', $words);
		$decrypt_pin = $this->str_last($sum, $pin_length);
		return $decrypt_pin === $pin;
	}

	protected function _sub_validate($k){
		$sum = $this->str_last($k, $this->digit_sum);
		$ori = $this->str_first($k, strlen($k)-strlen($sum));
		$pass = ($sum === $this->generate_check_sum($ori.$this->private_key));
		return $pass;
	}

	public function ValidateAPI($api) {
		//echo "========= Check Api Key ==========================<br/>";
		$api_pass = $this->_sub_validate($api, $this->private_key, $this->digit_sum);
		return $api_pass;
	}

}

?>
