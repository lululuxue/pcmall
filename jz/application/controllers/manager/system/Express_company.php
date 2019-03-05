<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Express_company extends CI_Controller
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
        if (empty($status)) $status = 0;
        if (isset($status)) $where_data['where']['status'] = $status;
        $search_where = array(
            'status' => $status,
        );
        assign('search_where', $search_where);
        //搜索条件end
        //查到数据
        $list = $this->loop_model->get_list('express_company', $where_data, $pagesize, $pagesize * ($page - 1));//列表
        assign('list', $list);
        //开始分页start
        $all_rows = $this->loop_model->get_list_num('express_company', array());//所有数量
        assign('page_count', ceil($all_rows / $pagesize));
        //开始分页end
        display('/system/express_company/list.html');
    }

    /**
     * 添加编辑
     */
    public function edit($id = '')
    {
        $id = (int)$id;
        if (!empty($id)) {
            $item = $this->loop_model->get_id('express_company', $id);
            assign('item', $item);
        }
        display('/system/express_company/add.html');
    }

    /**
     * 保存数据
     */
    public function save()
    {
        if (is_post()) {
            $data_post   = $this->input->post(NULL, true);
            $update_data = array(
                'name'    => $data_post['name'],
                'code'    => $data_post['code'],
                'url'     => $data_post['url'],
                'sortnum' => (int)$data_post['sortnum'],
            );
            if (empty($update_data['name'])) {
                error_json('快递公司名称不能为空');
            }
            if (!empty($data_post['id'])) {
                //修改数据
                $res = $this->loop_model->update_id('express_company', $update_data, $data_post['id']);
                admin_log_insert('修改快递公司' . $data_post['id']);
            } else {
                //增加数据
                $res = $this->loop_model->insert('express_company', $update_data);
                admin_log_insert('增加快递公司' . $res);
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

    /**
     * 删除数据到回收站
     */
    public function delete_recycle()
    {
        $id = $this->input->post('id', true);
        if (empty($id)) error_json('id错误');
        $res = $this->loop_model->update_id('express_company', array('status' => 1), $id);
        if (!empty($res)) {
            if (is_array($id)) $id = join(',', $id);
            admin_log_insert('删除快递到回收站' . $id);
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
        $res = $this->loop_model->update_id('express_company', array('status' => 0), $id);
        if (!empty($res)) {
            if (is_array($id)) $id = join(',', $id);
            admin_log_insert('还原快递' . $id);
            error_json('y');
        } else {
            error_json('还原失败');
        }
    }

    /**
     * 删除数据
     */
    public function delete()
    {
        $id = $this->input->post('id', true);
        if (empty($id)) error_json('id错误');
        $res = $this->loop_model->delete_id('express_company', $id);
        if (!empty($res)) {
            admin_log_insert('删除快递公司' . $id);
            error_json('y');
        } else {
            error_json('删除失败');
        }
    }

    /**
     * 编辑快递模板
     */
    public function tmp_edit($id = '')
    {
        $id = (int)$id;
        if (!empty($id)) {
            $item = $this->loop_model->get_id('express_company', $id);
            assign('item', $item);
        }
        assign('user_type', $this->uri->segment(1, 0));
        display('/system/express_company/tmp_edit.html');
    }

    /**
     * 保存快递模板
     */
    public function tmp_save()
    {
        $id     = (int)$this->input->post('id');
        $config = $this->input->post('config');
        if (!empty($id) && !empty($config)) {
            $res = $this->loop_model->update_id('express_company', array('config' => $config), $id);
            if (!empty($res)) {
                admin_log_insert('修改快递模板' . $id);
                error_json('y');
            } else {
                error_json('修改失败');
            }
        }
    }

}
