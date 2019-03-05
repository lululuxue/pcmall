<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Payment extends CI_Controller
{

    private $admin_data;//后台用户登录信息

    public function __construct()
    {
        parent::__construct();
        $this->load->helpers('manager_helper');
        $this->admin_data = manager_login();
        assign('admin_data', $this->admin_data);
        $this->load->model('loop_model');
    }

    /**
     * 列表
     */
    public function index()
    {
        $pagesize = 20;//分页大小
        $page     = (int)$this->input->get('per_page');
        $page <= 1 ? $page = 1 : $page = $page;//当前页数
        //查到数据
        $list = $this->loop_model->get_list('payment', array(), $pagesize, $pagesize * ($page - 1), 'id asc');//列表
        assign('list', $list);
        //开始分页start
        $all_rows = $this->loop_model->get_list_num('payment', array());//所有数量
        assign('page_count', ceil($all_rows / $pagesize));
        //开始分页end
        display('/system/payment/list.html');
    }

    /**
     * 编辑
     */
    public function edit($id)
    {
        $id = (int)$id;
        if (empty($id)) msg('id错误');
        $item = $this->loop_model->get_id('payment', $id);
        assign('item', $item);
        display('/system/payment/add.html');
    }

    /**
     * 保存数据
     */
    public function save()
    {
        if (is_post()) {
            $data_post   = $this->input->post(NULL, true);
            $update_data = array(
                'name'        => $data_post['name'],
                'status'      => (int)$data_post['status'],
                'sortnum'     => (int)$data_post['sortnum'],
                'client_type' => (int)$data_post['client_type'],
                'desc'        => $data_post['desc'],
            );
            //处理配置信息
            foreach ($data_post as $val => $key) {
                if (strpos($val, 'config_') !== false) {
                    $config_data[ltrim($val, 'config_')] = $key;
                }
            }
            $update_data['config'] = ch_json_encode($config_data);
            if (empty($update_data['name'])) {
                error_json('支付名称名称不能为空');
            }
            if (!empty($data_post['id'])) {
                //修改数据
                $res = $this->loop_model->update_id('payment', $update_data, $data_post['id']);
                admin_log_insert('修改支付方式' . $data_post['id']);
            } else {
                //增加数据
                $res = $this->loop_model->insert('express_company', $update_data);
                admin_log_insert('增加支付方式' . $res);
            }
            if (!empty($res)) {
                error_json('y');
            } else {
                error_json('保存失败');
            }
        } else {
            error_json('提交方式错误');
        }
    }

}
