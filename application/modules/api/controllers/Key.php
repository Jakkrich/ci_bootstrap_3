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

	public function __construct()
	{
		parent::__construct();
		$this->load->library('mycrypt');
	}

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

	private function autogen_apikey($create=NULL){
		$api = $this->mycrypt->generate_api();

		$ok = $this->api_keys->update(array(
			'key'=>$api,
			'user_id'=>1,
		), 1);
		if($ok){
			$data = array(
				'api_master_key'=>$api,
			);
			$data_json = [
				'status'=>'success',
				'data'=>array('api_master_key'=>$api,),
				'message'=>'Generate API Master Key Success!'
			];
			$this->response($data_json);
		}else{
			return False;
		}
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
		$config_path = APPPATH."config/config.php";
		if ($this->get_contents($config_path, "{ENTER_ENCRYPTION_KEY}")) {
			$data_json = [
				'status'=>'error',
				'data'=> null,
				'message'=>"Seems this ". '$config[\'encryption_key\']'." is not already specify! Please specify to [application/config/config.php]"
			];
			$this->response($data_json);
		}

		$row = $this->api_keys->where('id', 1)->get();
		if(!empty($row)){
			$data_json = [
				'status'=>'error',
				'data'=> null,
				'message'=>"Seems this API Master Key is already generated! You can't regenerated it again."
			];
			$this->response($data_json);
		}else{
			$this->autogen_apikey();
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
		$data_json = [
			'status'=>'error',
			'data'=> [],
			'message'=>"Invalid Old Key."
		];
		$pass = $this->mycrypt->ValidateAPI($old_key);
		if($pass !== TRUE){
			$this->response($data_json, 404);
		}

		$row = $this->api_keys->where('id', 1)->get();
		if(!empty($row)){
			if($row['key'] == $old_key){
				$this->autogen_apikey();
			}else{
				$this->response($data_json, 404);
			}
		}else{
			$this->response($data_json, 404);
		}
	}
}
