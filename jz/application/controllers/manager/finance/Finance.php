<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Finance extends CI_Controller
{

    private $admin_data;//后台用户登录信息

    public function __construct()
    {
        parent::__construct();
        $this->load->helpers('manager_helper');
        $this->admin_data = manager_login();
        assign('admin_data', $this->admin_data);
        $this->load->model('loop_model');

        $shop_list = $this->loop_model->get_list('shop',array('where'=>array('reid'=>0)));
        assign('shop_list', $shop_list);
    }

    /**
     * 日常开支
     */
    public function finance_list()
    {
        $pagesize = 20;//分页大小
        $page     = (int)$this->input->get('per_page');
        $page <= 1 ? $page = 1 : $page = $page;//当前页数
        //关键字
        $shop = $this->input->post('shop');
        $sub_shop = $this->input->post('sub_shop');
        $type = $this->input->post('type');
        $name = $this->input->post('name');
        $start_time = $this->input->post('start_time');
        $end_time = $this->input->post('end_time');

      //  if (!empty($shop)) $where_data['where']['shop'] = $shop;
      //  if (!empty($sub_shop)) $where_data['where']['sub_shop'] = $sub_shop;
        if (!empty($start_time)) $where_data['where']['time >='] = $start_time;
        if (!empty($end_time)) $where_data['where']['time <='] = $end_time;
        if (!empty($type) &&!empty($name)){
                $where_data['like'][$type]  = $name;
        }
        //搜索条件end
        $search_where = array(
            'name'             => $name,
            'type'             => $type,
            'end_time'         => $end_time,
            'start_time'      => $start_time,
            'sub_shop'        => $sub_shop,
            'shop'             => $shop,
        );
        assign('search_where', $search_where);
        //查到数据
        $list_data = $this->loop_model->get_list('finance_everyday', $where_data, $pagesize, $pagesize * ($page - 1), 'id desc');//列表
        //开始分页start
        $all_rows = $this->loop_model->get_list_num('finance_everyday', $where_data);//所有数量
        assign('page_count', ceil($all_rows / $pagesize));
        //开始分页end
        assign('list', $list_data);
        display('/finance/finance_list.html');
    }

    /**
     * 添加开支信息
     */
    public function add_finance()
    {
        display('/finance/add_finance.html');
    }

    /**
     * 提交开支
     */
    public function add_finance_submit()
    {
        $data_post = $this->input->post(NULL, true);
        $this->load->model('finance_model');
        $res = $this->finance_model->update($data_post,$this->admin_data);
        error_json($res);
    }

    /**
     * 开支信息详情
     */
    public function info($id)
    {
        //查到数据
        $info = $this->loop_model->get_where('finance_everyday', array('id'=>$id));
        assign('info', $info);
        display('/finance/info.html');
    }

    /**
     * 审核
     */
    public function verify($id)
    {
        //查到数据
        $info = $this->loop_model->get_where('finance_everyday', array('id'=>$id));
        assign('info', $info);
        display('/finance/verify.html');
    }

    /**
     * 审核情况提交
     */
    public function verify_submit()
    {
        //查到数据
        $data_post = $this->input->post(NULL, true);
        $this->load->model('finance_model');
        $res = $this->finance_model->verify($data_post);
        error_json($res);
    }



}
