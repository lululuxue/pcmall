<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Admin_log extends CI_Controller
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
        //用户名
        $admin_user = $this->input->post_get('admin_user');
        if (!empty($admin_user)) $where_data['where']['admin_user'] = $admin_user;
        //开始时间
        $start_time = $this->input->post_get('start_time');
        if (!empty($start_time)) $where_data['where']['addtime>='] = strtotime($start_time . ' 00:00:00');
        //结束时间
        $end_time = $this->input->post_get('end_time');
        if (!empty($end_time)) $where_data['where']['addtime<='] = strtotime($start_time . ' 23:59:59');
        //关键字
        $search_where = array(
            'start_time' => $start_time,
            'end_time'   => $end_time,
            'admin_user' => $admin_user,
        );
        assign('search_where', $search_where);
        $where_data['where']['shop_id'] = $this->shop_id;
        //搜索条件end
        //查到数据
        $list = $this->loop_model->get_list('member_shop_admin_log', $where_data, $pagesize, $pagesize * ($page - 1), 'id desc');//列表
        assign('list', $list);
        //开始分页start
        $all_rows = $this->loop_model->get_list_num('member_shop_admin_log', $where_data);//所有数量
        assign('page_count', ceil($all_rows / $pagesize));
        //开始分页end

        display('/report/admin_log/list.html');
    }

    /**
     * 彻底删除数据
     */
    public function delete()
    {
        $id = $this->input->post('id', true);
        if (empty($id)) error_json('id错误');
        $res = $this->loop_model->delete_id('member_shop_admin_log', $id);
        if (!empty($res)) {
            if (is_array($id)) $id = join(',', $id);
            shop_admin_log_insert('删除管理员操作记录' . $id);
            error_json('y');
        } else {
            error_json('删除失败');
        }
    }
}
