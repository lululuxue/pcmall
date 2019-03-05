<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Shop extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->helpers('web_helper');
        //$this->member_data = member_login();
        $this->load->helpers('wechat_helper');
        get_jsapi_ticket();//微信jssdk
        $this->load->model('loop_model');

        //店铺信息
        $this->shop_id                        = $this->input->get_post('shop_id', true);
        $this->member_shop_data               = $this->loop_model->get_where('member_shop', array('m_id' => $this->shop_id));
        $this->member_shop_data['banner_url'] = json_decode($this->member_shop_data['banner_url'], true);
        //是否收藏
        $m_id = get_m_id();
        if (!empty($m_id)) {
            //否是收藏
            $favorite_data = $this->loop_model->get_where('member_shop_favorite', array('shop_id' => $this->shop_id, 'm_id' => $m_id));
            if (!empty($favorite_data)) {
                $this->member_shop_data['favorite'] = 1;
            }
        }
        assign('member_shop_data', $this->member_shop_data);
        assign('action', $this->uri->segment(3));
    }

    /**
     * 店铺首页
     */
    public function index()
    {
        /* $shop_cat = $this->loop_model->get_list('goods_shop_category', array('where' => array('shop_id' => $this->shop_id)));
         if (!empty($shop_cat)) {
             $this->load->model('goods/goods_model');
             foreach ($shop_cat as $key) {
                 //搜索条件
                 $search_where = array(
                     'shop_cat_id' => $key['id'],
                     'shop_id'     => $this->shop_id,
                 );
                 $goods_list   = '';
                 $goods_list   = $this->goods_model->search($search_where, 4);
                 $key['goods'] = $goods_list['goods_list'];
                 $list[]       = $key;
             }
             assign('list', $list);
         }*/
        //搜索条件
        $search_where = array(
            'shop_id'      => $this->shop_id,
            'orderby'      => config_item('goods_list_orderby'),
            'orderby_type' => config_item('goods_list_orderby_type'),
            'limit'        => 4,
        );
        //查询数据
        $this->load->model('goods/goods_model');
        $res_data = $this->goods_model->search($search_where);
        assign('res_data', $res_data);

        display('/shop/index.html');
    }

    /**
     * 全部列表
     */
    public function all_goods()
    {
        $orderby      = $this->input->get_post('orderby', true);
        $orderby_type = $this->input->get_post('orderby_type', true);
        //搜索条件
        $search_where = array(
            'cat_id'       => $this->input->get_post('cat_id', true),
            'shop_cat_id'  => $this->input->get_post('shop_cat_id', true),
            'brand_id'     => $this->input->get_post('brand_id', true),
            'shop_id'      => $this->shop_id,
            'keyword'      => $this->input->get_post('keyword', true),
            'min_price'    => $this->input->get_post('min_price', true),
            'max_price'    => $this->input->get_post('max_price', true),
            'orderby'      => $orderby != '' ? $orderby : config_item('goods_list_orderby'),
            'orderby_type' => $orderby_type != '' ? $orderby_type : config_item('goods_list_orderby_type'),
        );

        //属性条件
        $attr = $this->input->get_post('attr', true);
        if (!empty($attr)) {
            foreach ($attr as $v => $k) {
                $search_where['attr'][$v] = $k;
            }
        }
        assign('search_where', $search_where);
        //查询数据
        $this->load->model('goods/goods_model');
        $res_data = $this->goods_model->search($search_where, '', 1);
        assign('res_data', $res_data);
        assign('per_page', (int)$this->input->get_post('per_page', true));

        $search_where['orderby_type'] == 'desc' ? $new_orderby_type = 'asc' : $new_orderby_type = 'desc';
        assign('new_orderby_type', $new_orderby_type);

        display('/shop/all_goods.html');
    }

    /**
     * 上新列表
     */
    public function new_goods()
    {
        $orderby      = $this->input->get_post('orderby', true);
        $orderby_type = $this->input->get_post('orderby_type', true);
        //搜索条件
        $search_where = array(
            'cat_id'        => $this->input->get_post('cat_id', true),
            'shop_cat_id'   => $this->input->get_post('shop_cat_id', true),
            'brand_id'      => $this->input->get_post('brand_id', true),
            'shop_id'       => $this->shop_id,
            'keyword'       => $this->input->get_post('keyword', true),
            'min_price'     => $this->input->get_post('min_price', true),
            'max_price'     => $this->input->get_post('max_price', true),
            'up_time_start' => strtotime(date('Y-m-d', time()) . ' 00:00:00') - 3600 * 24 * 7,
            'up_time_end'   => strtotime(date('Y-m-d', time()) . ' 23:59:59'),
            'orderby'       => $orderby != '' ? $orderby : config_item('goods_list_orderby'),
            'orderby_type'  => $orderby_type != '' ? $orderby_type : config_item('goods_list_orderby_type'),
        );

        //属性条件
        $attr = $this->input->get_post('attr', true);
        if (!empty($attr)) {
            foreach ($attr as $v => $k) {
                $search_where['attr'][$v] = $k;
            }
        }
        assign('search_where', $search_where);
        //查询数据
        $this->load->model('goods/goods_model');
        $res_data = $this->goods_model->search($search_where, '', 1);
        assign('res_data', $res_data);
        assign('per_page', (int)$this->input->get_post('per_page', true));

        $search_where['orderby_type'] == 'desc' ? $new_orderby_type = 'asc' : $new_orderby_type = 'desc';
        assign('new_orderby_type', $new_orderby_type);

        display('/shop/all_goods.html');
    }

}
