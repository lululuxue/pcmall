<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Info extends MY_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->model('loop_model');
    }

    /**
     * 用户信息
     */
    public function user_info()
    {
        header('Access-Control-Allow-Origin: *');
        // 响应类型
        header('Access-Control-Allow-Methods:GET, POST, PUT,DELETE');
        // 响应头设置
        header('Access-Control-Allow-Headers:x-requested-with,content-type');
        $formData = $_REQUEST;
        $info = $this->loop_model->get_where('user_new_card',array('m_id'=>$formData['m_id']),'card_no,total_count,addtime');
        $data = $this->loop_model->get_where('user',array('Id'=>$formData['m_id']),'username,name');
        $info['username'] = $data['username'];
        $info['name'] = $data['name'];
        error_json($info);
    }

    /**
     * 验证是否登入
     */
    public function is_reg()
    {
        error_json('y');
    }
}
