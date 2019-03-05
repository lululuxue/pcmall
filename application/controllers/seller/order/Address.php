<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Address extends CI_Controller
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
        $status = $this->input->post_get('status');
        if (empty($status)) $status = 0;
        if (isset($status)) $where_data['where']['status'] = $status;
        $search_where = array(
            'status' => $status,
        );
        assign('search_where', $search_where);
        $where_data['where']['shop_id'] = $this->shop_id;
        //搜索条件end
        //查到数据
        $list = $this->loop_model->get_list('member_shop_address', $where_data, $pagesize, $pagesize * ($page - 1), 'id desc');//列表
        assign('list', $list);
        //开始分页start
        $all_rows = $this->loop_model->get_list_num('member_shop_address', $where_data);//所有数量
        assign('page_count', ceil($all_rows / $pagesize));
        //开始分页end
        display('/order/address/list.html');
    }

    /**
     * 添加编辑
     */
    public function edit($id = '')
    {
        $id = (int)$id;
        if (!empty($id)) {
            $item = $this->loop_model->get_where('member_shop_address', array('id' => $id, 'shop_id' => $this->shop_id));
        }
        assign('item', $item);

        display('/order/address/add.html');
    }

    /**
     * 保存数据
     */
    public function save()
    {
        if (is_post()) {
            $data_post = $this->input->post(NULL, true);
            if (empty($data_post['full_name'])) {
                error_json('发货联系人不能为空');
            }
            if (empty($data_post['tel'])) {
                error_json('联系电话不能为空');
            }
            if (empty($data_post['prov']) || empty($data_post['city']) || empty($data_post['area'])) {
                error_json('请选择省市区不能为空');
            }
            if (empty($data_post['address'])) {
                error_json('详细地址不能为空');
            }

            $update_data = array(
                'full_name'  => $data_post['full_name'],
                'tel'        => $data_post['tel'],
                'prov'       => $data_post['prov'],
                'city'       => $data_post['city'],
                'area'       => $data_post['area'],
                'address'    => $data_post['address'],
                'is_default' => $data_post['is_default'],
            );

            //设为默认地址的时候先设置其他的地址为非默认
            if ($update_data['is_default'] == 1) {
                $this->loop_model->update_where('member_shop_address', array('is_default' => 0), array('shop_id' => $this->shop_id));
            }

            if (!empty($data_post['id'])) {
                //修改数据
                $update_where = array(
                    'where_in' => array('id' => $data_post['id']),
                    'where'    => array('shop_id' => $this->shop_id),
                );
                $res          = $this->loop_model->update_where('member_shop_address', $update_data, $update_where);
                shop_admin_log_insert('修改发货地址' . $data_post['id']);
            } else {
                //增加数据
                $update_data['shop_id'] = $this->shop_id;
                $res                    = $this->loop_model->insert('member_shop_address', $update_data);
                shop_admin_log_insert('增加发货地址' . $res);
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
        $update_where = array(
            'where_in' => array('id' => $id),
            'where'    => array('shop_id' => $this->shop_id),
        );
        $res          = $this->loop_model->update_where('member_shop_address', array('status' => 1), $update_where);
        if (!empty($res)) {
            if (is_array($id)) $id = join(',', $id);
            shop_admin_log_insert('删除发货地址到回收站' . $id);
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
        $update_where = array(
            'where_in' => array('id' => $id),
            'where'    => array('shop_id' => $this->shop_id),
        );
        $res          = $this->loop_model->update_where('member_shop_address', array('status' => 0), $update_where);
        if (!empty($res)) {
            if (is_array($id)) $id = join(',', $id);
            shop_admin_log_insert('还原发货地址' . $id);
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
        $delete_where = array(
            'where_in' => array('id' => $id),
            'where'    => array('shop_id' => $this->shop_id),
        );
        $res          = $this->loop_model->delete_where('member_shop_address', $delete_where);
        if (!empty($res)) {
            shop_admin_log_insert('删除发货地址' . $id);
            error_json('y');
        } else {
            error_json('删除失败');
        }
    }

}
