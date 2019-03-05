<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Brand extends CI_Controller
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
        $list = $this->loop_model->get_list('goods_brand', array(), $pagesize, $pagesize * ($page - 1), 'sortnum asc,id desc');//列表
        assign('list', $list);
        //开始分页start
        $all_rows = $this->loop_model->get_list_num('goods_brand', array());//所有数量
        assign('page_count', ceil($all_rows / $pagesize));
        //开始分页end
        display('/goods/brand/list.html');
    }

    /**
     * 添加编辑
     */
    public function edit($id = '')
    {
        $id = (int)$id;
        if (!empty($id)) {
            $item = $this->loop_model->get_id('goods_brand', $id);
            $item['cat_id'] = explode(',', $item['cat_id']);
            assign('item', $item);
        }
        $this->load->helpers('upload_helper');//加载上传文件插件

        //商品分类
        $this->load->model('goods/category_model');
        $cat_list = $this->category_model->get_all();
        assign('cat_list', $cat_list);
        display('/goods/brand/add.html');
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
                'logo'    => $data_post['logo'],
                'sortnum' => (int)$data_post['sortnum'],
                'cat_id'  => join(',', $data_post['cat_id']),
            );
            if (empty($update_data['name'])) {
                error_json('品牌名称不能为空');
            }
            if (!empty($data_post['id'])) {
                //修改数据
                $res = $this->loop_model->update_id('goods_brand', $update_data, $data_post['id']);
                admin_log_insert('修改品牌' . $data_post['id']);
            } else {
                //增加数据
                $res = $this->loop_model->insert('goods_brand', $update_data);
                admin_log_insert('增加品牌' . $res);
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
     * 删除数据
     */
    public function delete()
    {
        $id = $this->input->post('id', true);
        if (empty($id)) error_json('id错误');
        $res = $this->loop_model->delete_id('goods_brand', $id);
        if (!empty($res)) {
            admin_log_insert('删除品牌' . $id);
            error_json('y');
        } else {
            error_json('删除失败');
        }
    }

}
