<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Delivery extends CI_Controller
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
        if (empty($status)) $where_data['where']['status!='] = 1;
        if (isset($status)) $where_data['where']['status'] = $status;
        $where_data['where']['shop_id'] = $this->shop_id;
        $search_where                   = array(
            'status' => $status,
        );
        assign('search_where', $search_where);
        //搜索条件end
        //查到数据
        $list = $this->loop_model->get_list('delivery', $where_data, $pagesize, $pagesize * ($page - 1));//列表
        assign('list', $list);
        //开始分页start
        $all_rows = $this->loop_model->get_list_num('delivery', array());//所有数量
        assign('page_count', ceil($all_rows / $pagesize));
        //开始分页end
        display('/system/delivery/list.html');
    }


    /**
     * 添加编辑
     */
    public function edit($id = '')
    {
        $id = (int)$id;
        if (!empty($id)) {
            $this->load->model('system/delivery_model');
            $item = $this->delivery_model->get_where(array('id' => $id, 'shop_id' => $this->shop_id));
            assign('item', $item);
        }

        //省份列表
        $this->load->model('areas_model');
        $area_list = $this->areas_model->get_list();
        assign('area_list', $area_list);
        display('/system/delivery/add.html');
    }

    /**
     * 保存数据
     */
    public function save()
    {
        if (is_post()) {
            $data_post = $this->input->post(NULL, true);
            if (is_array($data_post['firstprice'])) {
                foreach ($data_post['firstprice'] as $key) {
                    $firstprice[] = price_format($key);
                }
            }
            if (is_array($data_post['secondprice'])) {
                foreach ($data_post['secondprice'] as $key) {
                    $secondprice[] = price_format($key);
                }
            }
            $update_data = array(
                'name'          => $data_post['name'],
                'type'          => (int)$data_post['type'],
                'first_weight'  => (int)$data_post['first_weight'],
                'second_weight' => (int)$data_post['second_weight'],
                'first_price'   => price_format((int)$data_post['first_price']),
                'second_price'  => price_format((int)$data_post['second_price']),
                'price_type'    => (int)$data_post['price_type'],
                'open_default'  => (int)$data_post['open_default'],
                'area_groupid'  => json_encode($data_post['area_groupid']),
                'firstprice'    => json_encode($firstprice),
                'secondprice'   => json_encode($secondprice),
                'sortnum'       => (int)$data_post['sortnum'],
                'status'        => (int)$data_post['status'],
                'desc'          => $data_post['desc'],
            );
            if (empty($update_data['name'])) {
                error_json('配送方式名称不能为空');
            }
            if (!empty($data_post['id'])) {
                //修改数据
                $res = $this->loop_model->update_where('delivery', $update_data, array('id' => $data_post['id'], 'shop_id' => $this->shop_id));
                shop_admin_log_insert('修改配送方式' . $data_post['id']);
            } else {
                //增加数据
                $update_data['shop_id'] = $this->shop_id;
                $res                    = $this->loop_model->insert('delivery', $update_data);
                shop_admin_log_insert('增加配送方式' . $res);
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
        $res          = $this->loop_model->update_where('delivery', array('status' => 1), $update_where);
        if (!empty($res)) {
            if (is_array($id)) $id = join(',', $id);
            shop_admin_log_insert('删除配送方式到回收站' . $id);
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
        $res          = $this->loop_model->update_where('delivery', array('status' => 0), $update_where);
        if (!empty($res)) {
            if (is_array($id)) $id = join(',', $id);
            shop_admin_log_insert('还原配送方式' . $id);
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
        $res          = $this->loop_model->delete_where('delivery', $delete_where);
        if (!empty($res)) {
            shop_admin_log_insert('删除配送方式' . $id);
            error_json('y');
        } else {
            error_json('删除失败');
        }
    }

}
