<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Promotion extends CI_Controller
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
        //查到数据
        $where_data['where']['shop_id']  = $this->shop_id;
        $where_data['where']['status!='] = 2;
        $list_data                       = $this->loop_model->get_list('promotion', $where_data, $pagesize, $pagesize * ($page - 1), 'id desc');//列表
        foreach ($list_data as $key) {
            $key['use_price'] = format_price($key['use_price']);
            $list[]           = $key;
        }
        assign('list', $list);
        //开始分页start
        $all_rows = $this->loop_model->get_list_num('promotion', $where_data);//所有数量
        assign('page_count', ceil($all_rows / $pagesize));
        //开始分页end
        display('/market/promotion/list.html');
    }

    /**
     * 添加编辑
     */
    public function edit($id = '')
    {
        $id = (int)$id;
        $item['user_group'] = array();
        if (!empty($id)) {
            $item               = $this->loop_model->get_where('promotion', array('id' => $id, 'shop_id' => $this->shop_id));
            $item['use_price']  = format_price($item['use_price']);
            $item['user_group'] = explode(',', $item['user_group']);
            $item['start_time'] = date('Y-m-d H:i:s', $item['start_time']);
            $item['end_time']   = date('Y-m-d H:i:s', $item['end_time']);
            if ($item['type'] == 1) $item['type_value'] = format_price($item['type_value']);
        }
        assign('item', $item);

        //会员组
        $group_list = $this->loop_model->get_list('member_user_group');
        assign('group_list', $group_list);

        //优惠券
        $coupons_where = array(
            'select' => 'id,name',
            'where'  => array(
                'status'       => 0,
                'shop_id'      => $this->shop_id,
                'start_time<=' => time(),
                'end_time>='   => time()
            )
        );
        $coupons_list  = $this->loop_model->get_list('coupons', $coupons_where);
        assign('coupons_list', $coupons_list);

        display('/market/promotion/add.html');
    }

    /**
     * 保存数据
     */
    public function save()
    {
        if (is_post()) {
            $data_post   = $this->input->post(NULL, true);
            $update_data = array(
                'name'       => $data_post['name'],
                'use_price'  => price_format($data_post['use_price']),
                'user_group' => join(',', $data_post['user_group']),
                'type'       => (int)$data_post['type'],
                'start_time' => strtotime($data_post['start_time']),
                'end_time'   => strtotime($data_post['end_time']),
                'desc'       => $data_post['desc'],
                'shop_id'    => $this->shop_id,
                'addtime'    => time(),
            );
            if (empty($update_data['name'])) {
                error_json('名称不能为空');
            } elseif (empty($update_data['use_price'])) {
                error_json('订单起用金额不能为空');
            } elseif (empty($data_post['start_time'])) {
                error_json('开始时间不能为空');
            } elseif (empty($data_post['end_time'])) {
                error_json('结束时间不能为空');
            }

            switch ($data_post['type']) {
                //优惠金额
                case 1:
                    if ($data_post['type_value'] > $data_post['use_price']) {
                        error_json('减去金额不能大于购物金额');
                    }
                    $update_data['type_value'] = price_format($data_post['type_value']);
                    break;
                //打折
                case 2:
                    if ($data_post['type_value'] < 1 || $data_post['type_value'] > 100) {
                        error_json('折扣率只能是1-100的整数');
                    }
                    $update_data['type_value'] = (int)$data_post['type_value'];
                    break;
                //赠送积分
                case 3:
                    $update_data['type_value'] = (int)$data_post['type_value'];
                    break;
                //赠送优惠券
                case 5:
                    if ($data_post['type_value'] < 1) {
                        error_json('请选择优惠券');
                    }
                    $update_data['type_value'] = (int)$data_post['type_value'];
                    break;
                default:
                    $update_data['type_value'] = 0;
                    break;
            }

            if (!empty($data_post['id'])) {
                //修改数据
                $res = $this->loop_model->update_where('promotion', $update_data, array('id' => $data_post['id'], 'shop_id' => $this->shop_id));
                shop_admin_log_insert('修改优惠活动' . $data_post['id']);
            } else {
                //增加数据
                $res = $this->loop_model->insert('promotion', $update_data);
                shop_admin_log_insert('增加优惠活动' . $res);
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
     * 修改优惠活动状态
     */
    public function update_status()
    {
        $id     = $this->input->post('id', true);
        $status = $this->input->get_post('status', true);
        if (empty($id) || $status == '') error_json('id错误');
        $update_data['status'] = (int)$status;
        $update_where          = array(
            'where_in' => array('id' => $id),
            'where'    => array('shop_id' => $this->shop_id),
        );
        $res                   = $this->loop_model->update_where('promotion', $update_data, $update_where);
        if (!empty($res)) {
            shop_admin_log_insert('修改优惠活动status为' . $status . 'id为' . $id);
            error_json('y');
        } else {
            error_json('操作失败');
        }
    }

    /**
     * 删除数据
     */
    public function delete()
    {
        $id = $this->input->post('id', true);
        if (empty($id)) error_json('id错误');
        $update_where = array(
            'where_in' => array('id' => $id),
            'where'    => array('shop_id' => $this->shop_id),
        );
        $res          = $this->loop_model->update_where('promotion', array('status' => 2), $update_where);
        if (!empty($res)) {
            shop_admin_log_insert('删除优惠活动' . $id);
            error_json('y');
        } else {
            error_json('删除失败');
        }
    }
}
