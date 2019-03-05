<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Source_model extends CI_Model
{

    public function __construct()
    {
        /**
         * 载入数据库类
         */
        $this->load->database();
    }

}
