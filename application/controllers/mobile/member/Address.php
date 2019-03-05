<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Address extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->helpers('web_helper');
        //$this->member_data = member_login();
        $this->load->model('loop_model');
    }

    /**
     * 地址列表
     */
    public function index()
    {
        $redirect_url = trim($this->input->get('redirect_url', true));//返回链接
        $redirect_url = preg_replace('/&address_id=(\d+)/', '', $redirect_url);
        assign('redirect_url', $redirect_url);
        display('/member/address/index.html');
    }
}
