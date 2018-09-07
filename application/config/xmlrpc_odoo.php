<?php defined('BASEPATH') OR exit('No direct script access allowed.');

$config['xmlrpc_odoo_server_url'] = FALSE;
$config['xmlrpc_odoo_xmlrpc_url'] = '/xmlrpc/2';

$config['xmlrpc_odoo_db'] = FALSE; #odoo DB
$config['xmlrpc_odoo_username'] = FALSE; #odoo username
$config['xmlrpc_odoo_password'] = FALSE; #odoo password

$config['xmlrpc_odoo_url'] = $config['xmlrpc_odoo_server_url'].$config['xmlrpc_odoo_xmlrpc_url']; #odoo url
?>
