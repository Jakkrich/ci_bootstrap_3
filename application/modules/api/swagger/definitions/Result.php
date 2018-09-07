<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
|--------------------------------------------------------------------------
| Swagger Definitions
|--------------------------------------------------------------------------
| Example: https://github.com/zircote/swagger-php/tree/master/Examples/petstore.swagger.io/models
*/

// To avoid class naming conflicts when defining Swagger Definitions
namespace MySwaggerDefinitions;

/**
* @SWG\Definition()
*/
class ResultItem {

	/**
	* @var string
	* @SWG\Property(enum={"success", "error"})
	*/
	public $status;

	/**
	* @var list
	* @SWG\Property()
	*/
	public $data;

	/**
	* @var string
	* @SWG\Property()
	*/
	public $message;
}
