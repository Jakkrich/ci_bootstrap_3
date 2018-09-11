<?php

class User_model extends MY_Model {
  public function __construct()
  {
    parent::__construct();
    $this->table = 'users';
    $this->primary_key = 'id';
    $this->timestamps = FALSE;
  }
}
