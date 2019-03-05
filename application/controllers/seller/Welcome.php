<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Welcome extends CI_Controller
{

    private $shop_data;//后台用户登录信息

    public function __construct()
    {
        parent::__construct();
        $this->load->helpers('shop_helper');
        $this->shop_data = shop_login();
        assign('shop_data', $this->shop_data);
    }

    /**
     * 后台首页
     */
    public function index()
    {
        display('/index/index.html');
    }

    /**
     * 后台头部
     */
    public function header()
    {
        assign('shop_data', $this->shop_data);
        display('/index/header.html');
    }

    /**
     * 后台菜单
     */
    public function menu($type = 'system')
    {
        //菜单查询
        $this->load->model('shop/menu_model');
        $menu_list = array();
        $menu_list = $this->menu_model->get_menu($type);//菜单列表
        assign('menu_list', $menu_list);

        assign('shop_data', $this->shop_data);
        display('/index/menu.html');
    }

    /**
     * 后台右侧首页
     */
    public function main()
    {
        assign('shop_data', $this->shop_data);

        //基础统计
        $this->load->model('loop_model');

        $shop_id = $this->shop_data['id'];
        //商品数量
        $count['goods'] = $this->loop_model->get_list_num('goods', array('where' => array('shop_id' => $shop_id, 'status!=' => 1)), 1);
        //待审核商品
        $count['examine_goods'] = $this->loop_model->get_list_num('goods', array('where' => array('shop_id' => $shop_id, 'status' => 3)), 1);
        //库存预警
        $count['goods_store_nums'] = $this->loop_model->get_list_num('goods', array('where' => array('shop_id' => $shop_id, 'store_nums<=' => config_item('goods_store_nums'))), 1);

        //订单数量
        $count['order'] = $this->loop_model->get_list_num('order', array('where' => array('shop_id' => $shop_id, 'is_del' => 0)), 1);
        //待发货订单数量
        $count['send_order'] = $this->loop_model->get_list_num('order', array('sql' => 'shop_id=' . $shop_id . ' and ((payment_id=1 and status=1) or (status=2))'), 1);
        //退款申请
        $count['order_refund_doc'] = $this->loop_model->get_list_num('order_refund_doc', array('where' => array('shop_id' => $shop_id, 'status' => 0)), 1);
        assign('count', $count);

        //最新订单
        //*********************************************************************
        $this->load->helpers('order_helper');
        $where_data['where']['is_del'] = 0;
        //搜索条件end
        $where_data['where']['shop_id'] = $shop_id;
        $where_data['select']           = 'o.*,m.username';
        $where_data['join']             = array(
            array('member as m', 'o.m_id=m.id')
        );
        //查到数据
        $list = $this->loop_model->get_list('order as o', $where_data, 10, 0, 'o.id desc');//列表
        assign('list', $list);

        //支付方式列表
        $payment_data = $this->loop_model->get_list('payment', array(), '', '', 'id asc');
        foreach ($payment_data as $key) {
            $payment_list[$key['id']] = $key;
        }
        assign('payment_list', $payment_list);

        //配送方式列表
        $delivery_data = $this->loop_model->get_list('delivery', array(), '', '', 'id asc');
        foreach ($delivery_data as $key) {
            $delivery_list[$key['id']] = $key;
        }
        assign('delivery_list', $delivery_list);

        display('/index/main.html');
    }

}
