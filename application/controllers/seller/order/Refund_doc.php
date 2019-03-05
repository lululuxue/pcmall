<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Refund_doc extends CI_Controller
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
     * 退款申请
     */
    public function pending()
    {
        $pagesize = 20;//分页大小
        $page     = (int)$this->input->get('per_page');
        $page <= 1 ? $page = 1 : $page = $page;//当前页数
        //搜索条件start
        $where_data['where'] = array('doc.status' => 0);
        //搜索条件end
        $where_data['where']['doc.shop_id'] = $this->shop_id;
        $where_data['select']               = 'doc.*,o.order_no,o.id as order_id,m.username';
        $where_data['join']                 = array(
            array('member as m', 'doc.m_id=m.id'),
            array('order as o', 'doc.order_id=o.id')
        );
        //查到数据
        $list_data = $this->loop_model->get_list('order_refund_doc as doc', $where_data, $pagesize, $pagesize * ($page - 1), 'doc.id desc');//列表
        foreach ($list_data as $key) {
            $key['amount'] = format_price($key['amount']);
            $list[]        = $key;
        }
        assign('list', $list);
        //开始分页start
        $all_rows = $this->loop_model->get_list_num('order_refund_doc as doc', $where_data);//所有数量
        assign('page_count', ceil($all_rows / $pagesize));
        //开始分页end

        display('/order/refund_doc/pending.html');
    }

    /**
     * 订单退款
     */
    public function refund_doc_view($id)
    {
        $id = (int)$id;
        if (!empty($id)) {
            $order_refund_doc_data = $this->loop_model->get_id('order_refund_doc', $id);
            if ($order_refund_doc_data['status'] == 0) {
                $order_refund_doc_data['amount'] = format_price($order_refund_doc_data['amount']);
                assign('order_refund_doc_data', $order_refund_doc_data);
                $order_data                    = $this->loop_model->get_id('order', $order_refund_doc_data['order_id']);
                $order_data['coupons_price']   = format_price($order_data['coupons_price']);
                $order_data['promotion_price'] = format_price($order_data['promotion_price']);
                assign('order_data', $order_data);
                $order_sku_data                        = $this->loop_model->get_id('order_sku', $order_refund_doc_data['sku_id']);
                $order_sku_data['sku_sell_price_real'] = format_price($order_sku_data['sku_sell_price_real']);
                assign('order_sku_data', $order_sku_data);
                display('/order/refund_doc/refund_doc_view.html');
            } else {
                echo "退款申请已经处理";
            }
        }
    }

    /**
     * 订单退款保存
     */
    public function refund_doc_save()
    {
        $id     = (int)$this->input->post('id', true);
        $amount = $this->input->post('amount', true);
        $desc   = $this->input->post('desc', true);
        $status = (int)$this->input->post('status', true);
        if (!empty($id) && $amount >= 0) {
            $this->load->model('order/order_model');
            $res = $this->order_model->refund_edit($id, $amount, $this->shop_data['username'], 1, $desc, $status);
            error_json($res);
        }
    }

    /**
     * 订单退款流程
     */
    public function refund_doc_log($id)
    {
        $id = (int)$id;
        if (!empty($id)) {
            $refund_doc_log_data = $this->loop_model->get_list('order_refund_doc_log', array('where' => array('doc_id' => $id)));
            assign('refund_doc_log_data', $refund_doc_log_data);
            display('/order/refund_doc/refund_doc_log.html');
        }
    }
}
