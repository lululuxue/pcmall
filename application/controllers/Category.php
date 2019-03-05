<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Category extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->helpers('web_helper');
        //$this->member_data = member_login();
        $this->load->helpers('wechat_helper');
        get_jsapi_ticket();//微信jssdk
        $this->load->model('loop_model');
    }

    /**
     * 分类首页
     */
    public function index()
    {
        $cat_id   = (int)$this->input->get('cat_id', true);
        $category = $this->loop_model->get_id('goods_category', $cat_id);
        assign('category', $category);
        display('/category/index.html');
    }
}
