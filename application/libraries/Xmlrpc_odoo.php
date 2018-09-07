<?php (defined('BASEPATH')) OR exit('No direct script access allowed');

/**
* Odoo is an PHP client for Odoo's xmlrpc api that uses the Ripcord library.
* This client should be compatible with version 6 and up of Odoo/OpenERP.
*
* This client is inspired on the OpenERP api from simbigo and the robroypt\Odoo library from
* Jacob Steringa and uses a more or less similar API.
* Instead of the Zend XMLRpc and Xml libraries it has been rewritten to use the the
* Ripcord RPC library used in the Odoo Web API documentation.
*
* @author  Rob Roy <rob@pervasivetelemetry.com.au>
*
* https://github.com/robroypt/odoo-client
*/
class Xmlrpc_odoo {

	/**
	* Host to connect to
	*
	* @var string
	*/
	protected $host;
	/**
	* Unique identifier for current user
	*
	* @var integer
	*/
	protected $uid;
	/**
	* Current users username
	*
	* @var string
	*/
	protected $username;
	/**
	* Current database
	*
	* @var string
	*/
	protected $database;
	/**
	* Password for current user
	*
	* @var string
	*/
	protected $password;
	/**
	* Ripcord Client
	*
	* @var Client
	*/
	protected $client;
	/**
	* XmlRpc endpoint
	*
	* @var string
	*/
	protected $path;

	private $_CI;
	/**
	* Odoo constructor
	*
	* @param string     $host       The url
	* @param string     $database   The database to log into
	* @param string     $user       The username
	* @param string     $password   Password of the user
	*/
	public function __construct()
	{
		$this->_CI =& get_instance();
		$this->_CI->load->config ( 'xmlrpc_odoo' );
		$this->_CI->load->library( 'ripcord/ripcord' );

		$this->host = $this->_CI->config->item ( 'xmlrpc_odoo_url' );
		$this->database = $this->_CI->config->item ( 'xmlrpc_odoo_db' );
		$this->username = $this->_CI->config->item ( 'xmlrpc_odoo_username' );
		$this->password = $this->_CI->config->item ( 'xmlrpc_odoo_password' );
	}
	/**
	* Get version
	*
	* @return array Odoo version
	*/
	public function version()
	{
		$response = $this->getClient('common')->version();
		return $response?$response:FALSE;
	}
	/**
	* Search models
	*
	* @param string  $model    Model
	* @param array   $criteria Array of criteria
	* @param integer $offset   Offset
	* @param integer $limit    Max results
	*
	* @return array Array of model id's
	*/
	public function search($model, $criteria, $offset = 0, $limit = 100, $order = '')
	{
		$response = $this->getClient('object')->execute_kw(
			$this->database,
			$this->uid(),
			$this->password,
			$model,
			'search',
			[$criteria],
			['offset'=>$offset, 'limit'=>$limit, 'order' => $order]
		);
		return $response?$response:FALSE;
	}
	/**
	* Search_count models
	*
	* @param string  $model    Model
	* @param array   $criteria Array of criteria
	*
	* @return array Array of model id's
	*/
	public function search_count($model, $criteria)
	{
		$response = $this->getClient('object')->execute_kw(
			$this->database,
			$this->uid(),
			$this->password,
			$model,
			'search_count',
			[$criteria]
		);
		return $response?$response:FALSE;
	}
	/**
	* Read model(s)
	*
	* @param string $model  Model
	* @param array  $ids    Array of model id's
	* @param array  $fields Index array of fields to fetch, an empty array fetches all fields
	*
	* @return array An array of models
	*/
	public function read($model, $ids, $fields = array())
	{
		$response = $this->getClient('object')->execute_kw(
			$this->database,
			$this->uid(),
			$this->password,
			$model,
			'read',
			[$ids],
			['fields'=>$fields]
		);
		return $response?$response:FALSE;
	}
	/**
	* Search and Read model(s)
	*
	* @param string $model     Model
	* @param array  $criteria  Array of criteria
	* @param array  $fields    Index array of fields to fetch, an empty array fetches all fields
	* @param integer $limit    Max results
	*
	* @return array An array of models
	*/
	public function search_read($model, $criteria, $fields = array(), $limit=100, $order = '')
	{
		$response = $this->getClient('object')->execute_kw(
			$this->database,
			$this->uid(),
			$this->password,
			$model,
			'search_read',
			[$criteria],
			['fields'=>$fields,'limit'=>$limit, 'order' => $order]
		);
		return $response?$response:FALSE;
	}
	/**
	* Create model
	*
	* @param string $model Model
	* @param array  $data  Array of fields with data (format: ['field' => 'value'])
	*
	* @return integer Created model id
	*/
	public function create($model, $data)
	{
		$response = $this->getClient('object')->execute_kw(
			$this->database,
			$this->uid(),
			$this->password,
			$model,
			'create',
			[$data]
		);
		//        print_r($response);
		return $response?$response:FALSE;
	}
	/**
	* Update model(s)
	*
	* @param string $model  Model
	* @param array  $ids     Model ids to update
	* @param array  $fields A associative array (format: ['field' => 'value'])
	*
	* @return array
	*/
	public function write($model, $ids, $fields)
	{
		$response = $this->getClient('object')->execute_kw(
			$this->database,
			$this->uid(),
			$this->password,
			$model,
			'write',
			[
				$ids,
				$fields
			]
		);
		return $response?$response:FALSE;
	}
	/**
	* Unlink model(s)
	*
	* @param string $model Model
	* @param array  $ids   Array of model id's
	*
	* @return boolean True is successful
	*/
	public function unlink($model, $ids)
	{
		$response = $this->getClient('object')->execute_kw(
			$this->database,
			$this->uid(),
			$this->password,
			$model,
			'unlink',
			[$ids]
		);
		return $response?$response:FALSE;
	}
	/**
	* Get XmlRpc Client
	*
	* This method returns an XmlRpc Client for the requested endpoint.
	* If no endpoint is specified or if a client for the requested endpoint is
	* already initialized, the last used client will be returned.
	*
	* @param null|string $path The api endpoint
	*
	* @return Client
	*/
	public function getClient($path = null)
	{
		if ($path === null) {
			return $this->client;
		}
		if ($this->path === $path) {
			return $this->client;
		}
		$this->path = $path;
		$this->client = $this->_CI->ripcord->client($this->host . '/' . $path);
		return $this->client;
	}
	/**
	* Get uid
	*
	* @return int $uid
	*/
	protected function uid()
	{
		if ($this->uid === null) {
			$client = $this->getClient('common');
			$this->uid = $client->authenticate(
				$this->database,
				$this->username,
				$this->password,
				array()
			);
		}
		return $this->uid;
	}


	/**
	*
	* Custom call method in model(s)
	*
	* @param  string $model  Model
	* @param  string $method Method to call
	* @param  array  $ids    Model ids to update
	* @param  array  $datas  A associative array (format: ['key' => 'value'])
	*
	* @return array
	*
	*/
	public function call($model, $method, $ids, $datas)
	{
		$response = $this->getClient('object')->execute_kw(
			$this->database,
			$this->uid(),
			$this->password,
			$model,
			$method,
			[
				$ids,
				$datas
			]
		);
		return $response?$response:FALSE;
	}
}
?>
