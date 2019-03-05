<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Coupons extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->helpers('web_helper');
        $this->member_data = member_login();
        $this->load->model('loop_model');
    }

    /**
     * 我的优惠券
     */
    public function index()
    {
        $pagesize = 20;//分页大小
        $page     = (int)$this->input->get('per_page');
        $page <= 1 ? $page = 1 : $page = $page;//当前页数
        //搜索条件start
        $overdue = $this->input->get_post('overdue');//是否过期
        if (!empty($overdue)) {
            $where_data['where']['start_time>='] = time();
            $where_data['where']['end_time<=']   = time();
        }
        $status = $this->input->get_post('status');//是否使用
        if (isset($overdue)) $where_data['where']['d.status'] = $status;

        $search_where = array(
            'overdue' => $overdue,
            'status'  => $status
        );
        assign('search_where', $search_where);
        //搜索条件end
        $where_data['where']['d.m_id']     = $this->member_data['id'];//过滤用户
        $where_data['where']['c.status'] = 0;//是否删除
        $where_data['select']            = 'c.name,c.amount,c.use_price,c.start_time,c.end_time,d.status,d.is_send,d.is_close,s.shop_name';
        $where_data['join']              = array(
            array('coupons as c', 'd.cou_id = c.id'),
            array('member_shop as s', 's.m_id = d.shop_id')
        );
        //查到数据
        $list_data = $this->loop_model->get_list('coupons_detail as d', $where_data, $pagesize, $pagesize * ($page - 1), 'd.id desc');//列表
        foreach ($list_data as $key) {
            $key['amount']    = format_price($key['amount']);
            $key['use_price'] = format_price($key['use_price']);
            $list[]           = $key;
        }
        assign('list', $list);
        //开始分页start
        $all_rows = $this->loop_model->get_list_num('coupons_detail as d', $where_data);//所有数量;
        assign('page_count', ceil($all_rows / $pagesize));
        //开始分页end

        display('/member/coupons/index.html');
    }


    /**
     * 兑换优惠券
     */
    public function exchange()
    {
        display('/member/coupons/exchange.html');
    }
}
