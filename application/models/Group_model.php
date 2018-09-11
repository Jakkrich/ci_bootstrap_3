<?php

class Group_model extends MY_Model {
  public function __construct()
  {
    parent::__construct();
    $this->table = 'groups';
    $this->primary_key = 'id';
    $this->timestamps = FALSE;
  }
}
