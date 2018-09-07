<?php
defined('BASEPATH') OR exit('No direct script access allowed');


/**
* [IMPORTANT]
* 	To allow API access without API Key ("X-API-KEY" from HTTP Header),
* 	remember to add routes from /application/modules/api/config/rest.php like this:
* 		$config['auth_override_class_method']['dummy']['*'] = 'none';
*/
class Key extends API_Controller {

	// Don't User API KEY = FALSE
	protected $mUseIdentityByApiKey = FALSE;

	public function index_get()
	{
		redirect('api');
	}

	private function get_contents($path, $val){
		$config_file = file_get_contents($path);
		$is_val = strpos($config_file, $val);
		if ($is_val === FALSE) {
			return false;
		}else{
			return true;
		}
	}

	private function autogen_apikey($val){
		$this->load->library ('mycrypt');
		$api = $this->mycrypt->generate_api();

		$config_myrestaccounte_path = APPPATH."config/api_master_key.php";
		$config_file = file_get_contents($config_myrestaccounte_path);
		$config_file = str_replace($val, $api, $config_file);
		file_put_contents($config_myrestaccounte_path, $config_file);

		$data = array(
			'api_master_key'=>$api,
		);
		$data_json = [
			'status'=>'success',
			'data'=>array('api_master_key'=>$api,),
			'message'=>'Generate API Master Key Success!'
		];
		$this->response($data_json);
	}

	/**
	* @SWG\Get(
	* 	path="/key/install",
	* 	tags={"key"},
	*  @SWG\Response(
	*   response=200,
	*   description="OK",
	*   @SWG\Schema(
	*     type="object",
	*     @SWG\Property(property="status", type="string", enum={"success", "error"}),
	*     @SWG\Property(property="data", type="object",
	*       @SWG\Property(property="api_master_key", type="string"),
	*     ),
	*     @SWG\Property(property="msg", type="string", description="some message"),
	*   )
	* 	),
	* )
	*/
	public function install_get(){
		// set random enter_encryption_key
		$config_path = APPPATH."config/mycrypt.php";
		if ($this->get_contents($config_path, "{ENTER_PRIVATE_KEY}")) {
			$data_json = [
				'status'=>'error',
				'data'=> null,
				'message'=>"Seems this ". '$config[\'mycrypt_private_key\']'." is not already specify! Please specify to [application/config/mycrypt.php]"
			];
			$this->response($data_json);
		}

		$config_path = APPPATH."config/api_master_key.php";
		if ($this->get_contents($config_path, "{GENERATE_ONLY_BY_API}")) {
			$this->autogen_apikey('{GENERATE_ONLY_BY_API}');
		}else {
			$data_json = [
				'status'=>'error',
				'data'=> null,
				'message'=>"Seems this API Master Key is already generated! You can't regenerated it again."
			];
			$this->response($data_json);
		}
	}

	/**
	* @SWG\Get(
	* 	path="/key/reinstall/{old_key}",
	* 	tags={"key"},
	* 	@SWG\Parameter(
	* 		in="path",
	* 		name="old_key",
	* 		description="Old API Master Key",
	* 		required=true,
	* 		type="string"
	* 	),
	* @SWG\Response(
	*   response=200,
	*   description="OK",
	*   @SWG\Schema(
	*     type="object",
	*     @SWG\Property(property="status", type="string", enum={"success", "error"}),
	*     @SWG\Property(property="data", type="object",
	*       @SWG\Property(property="api_master_key", type="string"),
	*     ),
	*     @SWG\Property(property="msg", type="string", description="some message"),
	*   )
	* 	),
	* 	@SWG\Response(
	* 		response="404",
	* 		description="Invalid Old Key.",
	* 	)
	* )
	*/
	public function reinstall_get($old_key=NULL){
		$this->load->config('api_master_key');
		$api_master_key = $this->config->item('api_master_key');

		if($old_key==NULL || $old_key!=$api_master_key){
			$data_json = [
				'status'=>'error',
				'data'=> null,
				'message'=>"Invalid Old Key."
			];
			$this->response($data_json, 404);
		}

		$config_path = APPPATH."config/api_master_key.php";
		if ($this->get_contents($config_path, $old_key)) {
			$this->autogen_apikey($old_key);
		}
	}
}
