<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Shop extends CI_Controller
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
        //搜索条件start
        $status = $this->input->post_get('status');
        if (!empty($status)) {
            $where_data['where']['s.status'] = $status;
        } else {
            $where_data['where']['s.status!='] = 1;
        }
        //用户名
        $username = $this->input->post_get('username');
        if (!empty($username)) $where_data['where']['username'] = $username;

        //店铺名
        $shop_name = $this->input->post_get('shop_name');
        if (!empty($shop_name)) $where_data['like']['shop_name'] = $shop_name;
        $search_where = array(
            'status' => $status,
            'username' => $username,
            'shop_name'=>$shop_name
        );
        assign('search_where', $search_where);
        //搜索条件end
        $where_data['join'] = array(
            array('member as m', 's.m_id=m.id')
        );
        //查到数据
        $list = $this->loop_model->get_list('member_shop as s', $where_data, $pagesize, $pagesize * ($page - 1), 'm.id desc');//列表
        assign('list', $list);
        //开始分页start
        $all_rows = $this->loop_model->get_list_num('member_shop as s', $where_data);//所有数量
        assign('page_count', ceil($all_rows / $pagesize));
        //开始分页end

        assign('status', array('0' => '正常', 1 => '删除', 2 => '锁定'));//状态
        display('/member/shop/list.html');
    }

    /**
     * 添加
     */
    public function add($m_id)
    {
        $m_id = (int)$m_id;
        if (!empty($m_id)) {
            $this->load->helpers('upload_helper');//加载上传文件插件
            $member_shop         = $this->loop_model->get_where('member_shop', array('m_id' => $m_id));
            $member_shop['m_id'] = $m_id;
            assign('item', $member_shop);
            display('/member/shop/add.html');
        } else {
            error_json('请先注册会员');
        }
    }

    /**
     * 编辑
     */
    public function edit($m_id)
    {
        $m_id = (int)$m_id;
        if (empty($m_id)) msg('id错误');
        $member_shop = $this->loop_model->get_where('member_shop', array('m_id' => $m_id));
        assign('item', $member_shop);
        $this->load->helpers('upload_helper');//加载上传文件插件
        display('/member/shop/add.html');
    }

    /**
     * 保存数据
     */
    public function save()
    {
        if (is_post()) {
            $data_post = $this->input->post(NULL, true);
            $this->load->model('member/shop_model');
            $res = $this->shop_model->update($data_post);
            if (!empty($res)) {
                error_json($res);
            } else {
                error_json('保存失败');
            }
        } else {
            error_json('提交方式错误');
        }

    }

    /**
     * 删除数据到回收站
     */
    public function delete_recycle()
    {
        $id = $this->input->post('id', true);
        if (empty($id)) error_json('id错误');
        if ($id == 1 || in_array(1, $id)) {
            error_json('商城自营不允许删除');
        }
        $res = $this->loop_model->update_where('member_shop', array('status' => 1), array('where_in' => array('m_id' => $id)));
        if (!empty($res)) {
            if (is_array($id)) $id = join(',', $id);
            admin_log_insert('删除店铺到回收站' . $id);
            error_json('y');
        } else {
            error_json('删除失败');
        }
    }

    /**
     * 回收站还原
     */
    public function reduction_recycle()
    {
        $id = $this->input->post('id', true);
        if (empty($id)) error_json('id错误');
        $res = $this->loop_model->update_where('member_shop', array('status' => 0), array('where_in' => array('m_id' => $id)));
        if (!empty($res)) {
            if (is_array($id)) $id = join(',', $id);
            admin_log_insert('还原店铺' . $id);
            error_json('y');
        } else {
            error_json('还原失败');
        }
    }

    /**
     * 彻底删除数据
     */
    public function delete()
    {
        $id = $this->input->post('id', true);
        if (empty($id)) error_json('id错误');
        if ($id == 1 || in_array(1, $id)) {
            error_json('商城自营不允许删除');
        }
        $res = $this->loop_model->delete_where('member_shop', array('where_in' => array('m_id' => $id)));
        if (!empty($res)) {
            if (is_array($id)) $id = join(',', $id);
            admin_log_insert('彻底删除店铺' . $id);
            error_json('y');
        } else {
            error_json('删除失败');
        }
    }

    /**
     * 修改数据状态
     */
    public function update_status()
    {
        $id     = $this->input->post('id', true);
        $status = $this->input->get_post('status', true);
        if (empty($id) || $status == '') error_json('id错误');
        $status                = (int)$status;
        $update_data['status'] = $status;
        $res                   = $this->loop_model->update_where('member_shop', $update_data, array('where_in' => array('m_id' => $id)));
        if (!empty($res)) {
            if (is_array($id)) $id = join(',', $id);
            admin_log_insert('修改店铺status为' . $status . 'id为' . $id);
            error_json('y');
        } else {
            error_json('操作失败');
        }
    }

    /**
     * 查看店铺
     */
    public function view($m_id)
    {
        $m_id = (int)$m_id;
        if (empty($m_id)) msg('id错误');
        $member_shop = $this->loop_model->get_where('member_shop', array('m_id' => $m_id));
        assign('item', $member_shop);
        $this->load->helpers('upload_helper');//加载上传文件插件
        display('/member/shop/view.html');
    }
}
