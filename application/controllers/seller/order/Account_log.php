<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Account_log extends CI_Controller
{

    private $shop_data;//后台用户登录信息

    public function __construct()
    {
        parent::__construct();
        $this->load->helpers('shop_helper');
        $this->shop_data = shop_login();
        assign('shop_data', $this->shop_data);
        $this->load->model('loop_model');
        $this->shop_id = $this->shop_data['id'];
    }

    /**
     * 列表
     */
    public function index()
    {
        $pagesize = 20;//分页大小
        $page     = (int)$this->input->get('per_page');
        $page <= 1 ? $page = 1 : $page = $page;//当前页数
        //搜索条件start
        //开始时间
        $start_time = $this->input->post_get('start_time');
        if (!empty($start_time)) $where_data['where']['addtime>='] = strtotime($start_time . ' 00:00:00');
        //结束时间
        $end_time = $this->input->post_get('end_time');
        if (!empty($end_time)) $where_data['where']['addtime<='] = strtotime($start_time . ' 23:59:59');
        $where_data['where']['event'] = 100;
        //关键字
        $search_where = array(
            'start_time' => $start_time,
            'end_time'   => $end_time,
        );
        assign('search_where', $search_where);
        $where_data['where']['m_id'] = $this->shop_id;
        //搜索条件end
        //查到数据
        $list_data = $this->loop_model->get_list('member_user_account_log', $where_data, $pagesize, $pagesize * ($page - 1), 'id desc');//列表
        foreach ($list_data as $key) {
            $key['amount'] = format_price($key['amount']);
            $list[]        = $key;
        }
        assign('list', $list);
        //开始分页start
        $all_rows = $this->loop_model->get_list_num('member_user_account_log', $where_data);//所有数量
        assign('page_count', ceil($all_rows / $pagesize));
        //开始分页end

        display('/order/account_log/list.html');
    }

}
