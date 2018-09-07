<?php defined('BASEPATH') OR exit('No direct script access allowed.');

$config['mycrypt_charset']  		      = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
$config['mycrypt_hash_method']  	   	= 'sha1';
$config['mycrypt_private_key']  	   	= '{ENTER_PRIVATE_KEY}';

$config['mycrypt_api_key_only']  	   	= True;
$config['mycrypt_digit_sum']  		   	= 8;
$config['mycrypt_digit_api_key']  	 	= 56;					// 29 + 3 = 32
$config['mycrypt_digit_username']  	 	= 0;					// 13 + 3 = 16

?>
