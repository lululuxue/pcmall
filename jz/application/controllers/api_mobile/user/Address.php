<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Address extends MY_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->model('loop_model');
    }

    /**
     * 获取地址列表
     */
    public function address_list()
    {
        header('Access-Control-Allow-Origin: *');
        // 响应类型
        header('Access-Control-Allow-Methods:POST');
        // 响应头设置
        header('Access-Control-Allow-Headers:x-requested-with,content-type');
        $m_id = $this->input->get('m_id');
        $list = $this->loop_model->get_list('user_address',array('where'=>array('m_id'=>$m_id)),'','');
        error_json($list);
    }

    /**
     * 新增/修改地址
     */
    public function edit()
    {
        header('Access-Control-Allow-Origin: *');
        // 响应类型
        header('Access-Control-Allow-Methods:POST');
        // 响应头设置
        header('Access-Control-Allow-Headers:x-requested-with,content-type');
        $post_data = $this->input->get(NULL);
        $this->load->model('user_address_model');
        $res = $this->user_address_model->update($post_data);
        error_json($res);
    }

    /**
     * 获取地址详情
     */
    public function detail()
    {
        header('Access-Control-Allow-Origin: *');
        // 响应类型
        header('Access-Control-Allow-Methods:POST');
        // 响应头设置
        header('Access-Control-Allow-Headers:x-requested-with,content-type');
        $post_data = $this->input->get(NULL);
        $info = $this->loop_model->get_where('user_address',array('id'=>$post_data['id']));
        error_json($info);
    }

    /**
     * 删除地址
     */
    public function delete()
    {
        header('Access-Control-Allow-Origin: *');
        // 响应类型
        header('Access-Control-Allow-Methods:GET, POST, PUT,DELETE');
        // 响应头设置
        header('Access-Control-Allow-Headers:x-requested-with,content-type');
        $post_data = $this->input->get(NULL);
        $res = $this->loop_model->delete_id('user_address',$post_data['id']);
        if($res > 0){
            error_json('y');
        }else{
            error_json('删除失败');
        }

    }

}


