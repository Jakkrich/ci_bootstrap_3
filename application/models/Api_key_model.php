<?php

class Api_key_model extends MY_Model {
  public function __construct()
  {
    parent::__construct();
    $this->table = 'api_keys';
    $this->primary_key = 'id';
    $this->timestamps = FALSE;
    
  }
}
