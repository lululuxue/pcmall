<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Point extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->helpers('web_helper');
        $this->member_data = member_login();
        $this->load->model('loop_model');
    }

    /**
     * 积分流水
     */
    public function index()
    {
        $pagesize = 20;//分页大小
        $page     = (int)$this->input->get('per_page');
        $page <= 1 ? $page = 1 : $page = $page;//当前页数
        //搜索条件start
        $event = $this->input->get_post('event');
        if (!empty($event)) $where_data['where']['event'] = $event;

        $search_where = array(
            'event' => $event,
        );
        assign('search_where', $search_where);
        //搜索条件end
        $where_data['where']['m_id'] = $this->member_data['id'];//过滤用户
        //查到数据
        $list = $this->loop_model->get_list('member_user_point_log', $where_data, $pagesize, $pagesize * ($page - 1), 'id desc');//列表
        assign('list', $list);
        //开始分页start
        $all_rows = $this->loop_model->get_list_num('member_user_point_log', $where_data);//所有数量;
        assign('page_count', ceil($all_rows / $pagesize));
        //开始分页end

        //自己类型
        $this->load->model('member/user_point_log_model');
        assign('event_name', $this->user_point_log_model->get_type_name());

        $member_user_data            = $this->loop_model->get_where('member_user', array('m_id' => $this->member_data['id']));
        assign('member_user_data', $member_user_data);
        display('/member/point/index.html');
    }
}
