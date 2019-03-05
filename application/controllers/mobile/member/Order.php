<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Order extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->helpers('web_helper');
        $this->member_data = member_login();
        $this->load->model('loop_model');
        $this->load->helpers('order_helper');
    }

    /**
     * 订单列表
     */
    public function index()
    {
        $pagesize = 20;//分页大小
        $page     = (int)$this->input->get('per_page');
        $page <= 1 ? $page = 1 : $page = $page;//当前页数
        //搜索条件start
        $status = $this->input->get_post('status');
        if (!empty($status)) {
            switch ($status) {
                case 1:
                    $where_data['where']['o.status']      = $status;
                    $where_data['where']['o.payment_id>'] = 1;//货到付款的不用支付
                    break;
                case 2:
                    $where_data['sql'] = '((o.payment_id=1 and o.status=1) or (o.status=2))';//包含货到付款的
                    break;
                default:
                    $where_data['where']['o.status'] = $status;
                    break;
            }
        }

        $search_where = array(
            'status' => $status,
        );
        assign('search_where', $search_where);
        //搜索条件end
        $where_data['where']['o.status>'] = 0;
        $where_data['select']             = 'o.*,shop.shop_name,shop.logo';
        $where_data['where']['o.m_id']    = $this->member_data['id'];//过滤用户
        $where_data['join']               = array(
            array('member_shop as shop', 'o.shop_id=shop.m_id')
        );
        //查到数据
        $order_list = $this->loop_model->get_list('order as o', $where_data, $pagesize, $pagesize * ($page - 1), 'o.id desc');//列表
        $this->load->model('order/order_model');
        foreach ($order_list as $key) {
            $order_sku          = '';
            $order_sku          = $this->order_model->get_order_sku($key['id']);
            $key['order_sku']   = $order_sku;
            $key['order_price'] = format_price($key['order_price']);
            $list[]             = $key;
        }
        assign('list', $list);
        //开始分页start
        $all_rows = $this->loop_model->get_list_num('order as o', $where_data);//所有数量;
        assign('page_count', ceil($all_rows / $pagesize));
        //开始分页end

        display('/member/order/index.html');
    }

    /**
     * 订单详情
     */
    public function view($id)
    {
        $this->load->model('order/order_model');
        $order_data = $this->order_model->get_order($id);
        if ($order_data['m_id'] != $this->member_data['id']) {
            msg('权限错误');
        }
        $order_data['coupons_price']   = format_price($order_data['coupons_price']);
        $order_data['promotion_price'] = format_price($order_data['promotion_price']);
        $order_sku                     = $this->order_model->get_order_sku($order_data['id']);//商品列表
        $this->load->model('areas_model');
        //支付方式
        if ($order_data['payment_id'] > 1) {
            $order_data['payment'] = $this->loop_model->get_where('payment', array('id' => $order_data['payment_id']));
        }
        //配送方式
        if ($order_data['delivery_id'] > 0) {
            $order_data['delivery'] = $this->loop_model->get_where('delivery', array('id' => $order_data['delivery_id']));
        }

        //店铺信息
        if ($order_data['shop_id'] > 0) {
            $order_data['shop_data'] = $this->loop_model->get_where('member_shop', array('m_id' => $order_data['shop_id']));
        }
        assign('order_data', $order_data);
        assign('order_sku', $order_sku);

        display('/member/order/view.html');
    }

    /**
     * 订单物流查询
     */
    public function delivery_status($id)
    {
        assign('order_id', $id);
        display('/member/order/delivery_status.html');
    }

    /**
     * 订单评论
     */
    public function comment($id)
    {
        if (empty($id)) error_json('订单号错误');
        $order_data = $this->loop_model->get_where('order', array('id' => $id));
        if (is_comment($order_data)) {
            $this->load->model('order/order_model');
            $order_sku = $this->order_model->get_order_sku($order_data['id']);//商品列表
            assign('order_data', $order_data);
            assign('order_sku', $order_sku);
            display('/member/order/comment.html');
        } elseif ($order_data['status'] == 5) {
            msg('订单已经评价');
        } else {
            msg('订单还不能评价');
        }
    }

    /**
     * 订单退款
     */
    public function refund($id)
    {
        if (empty($id)) error_json('订单商品ID错误');
        $order_sku_data                        = $this->loop_model->get_where('order_sku', array('id' => $id));
        $order_sku_data['sku_sell_price_real'] = format_price($order_sku_data['sku_sell_price_real']);
        $order_sku_data['sku_value']           = json_decode($order_sku_data['sku_value'], true);
        //订单信息
        $order_data = $this->loop_model->get_where('order', array('id' => $order_sku_data['order_id']));
        assign('order_sku_data', $order_sku_data);
        assign('order_data', $order_data);
        if ($order_sku_data['is_refund'] == 1 || $order_sku_data['is_refund'] == 2) {
            //已经提交了退款申请
            $select_where    = array(
                'm_id'     => $this->member_data['id'],
                'order_id' => $order_sku_data['order_id'],
                'sku_id'   => $id,
            );
            $refund_doc_data = $this->loop_model->get_where('order_refund_doc', $select_where);
            assign('refund_doc_data', $refund_doc_data);
            //退款日志
            $doc_log_list = $this->loop_model->get_list('order_refund_doc_log', array('where' => array('doc_id' => $refund_doc_data['id'])));
            assign('doc_log_list', $doc_log_list);
            //展示退款详情
            display('/member/order/refund_view.html');
        } else {
            if (is_refund($order_data)) {
                //添加退款申请
                display('/member/order/refund_add.html');
            } else {
                msg('已经确认和未支付的不能退款');
            }

        }
    }

}
