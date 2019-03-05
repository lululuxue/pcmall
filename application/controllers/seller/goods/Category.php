<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Category extends CI_Controller
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
        //查到数据
        $this->load->model('goods/shop_category_model');
        $list = $this->shop_category_model->get_all($this->shop_id);//列表
        assign('list', $list);//print_r($list);
        display('/goods/category/list.html');
    }

    /**
     * 添加
     */
    public function add($reid = 0)
    {
        $reid = (int)$reid;
        assign('reid', $reid);
        $this->load->helpers('upload_helper');//加载上传文件插件
        display('/goods/category/add.html');
    }

    /**
     * 编辑
     */
    public function edit($id)
    {
        $id = (int)$id;
        if (!empty($id)) {
            $item = $this->loop_model->get_where('goods_shop_category', array('id' => $id, 'shop_id' => $this->shop_id));
            assign('item', $item);
        }

        $this->load->helpers('upload_helper');//加载上传文件插件
        display('/goods/category/add.html');
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
                'sortnum' => (int)$data_post['sortnum'],
                'reid'    => $data_post['reid'] != '' ? $data_post['reid'] : 0,
                'image'   => $data_post['image'],
            );

            if (empty($update_data['name'])) {
                error_json('分类名称不能为空');
            }
            if (!empty($data_post['id'])) {
                //修改数据
                $res = $this->loop_model->update_where('goods_shop_category', $update_data, array('id' => $data_post['id'], 'shop_id' => $this->shop_id));
                shop_admin_log_insert('修改分类' . $data_post['id']);
            } else {
                //增加数据
                $update_data['shop_id'] = $this->shop_id;
                $res                    = $this->loop_model->insert('goods_shop_category', $update_data);
                shop_admin_log_insert('增加分类' . $res);
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
        $id = (int)$this->input->post('id', true);
        if (empty($id)) error_json('id不能为空');
        $re_item = $this->loop_model->get_where('goods_shop_category', array('reid' => $id, 'shop_id' => $this->shop_id));
        if (!empty($re_item)) {
            error_json('下级栏目不为空不能删除');
        } else {
            $res = $this->loop_model->delete_where('goods_shop_category', array('id' => $id, 'shop_id' => $this->shop_id));
            if (!empty($res)) {
                shop_admin_log_insert('删除分类' . $id);
                error_json('y');
            } else {
                error_json('删除失败');
            }
        }
    }

}
