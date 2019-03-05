<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Goods extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->helpers('web_helper');
        if (get_client() == 'weixin') {
            $this->member_data = member_login();
        }
        $this->load->helpers('wechat_helper');
        get_jsapi_ticket();//微信jssdk
        $this->load->model('loop_model');
    }

    /**
     * 列表
     */
    public function goods_list()
    {
        $cat_id  = $this->input->get_post('cat_id', true);
        $keyword = $this->input->get_post('keyword', true);
        if (empty($cat_id) && empty($keyword)) {
            msg('分类或关键字有一项必填');
        }
        $orderby      = $this->input->get_post('orderby', true);
        $orderby_type = $this->input->get_post('orderby_type', true);
        //搜索条件
        $search_where = array(
            'cat_id'       => $this->input->get_post('cat_id', true),
            'brand_id'     => $this->input->get_post('brand_id', true),
            'shop_id'      => $this->input->get_post('shop_id', true),
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
        assign('per_page', (int)$this->input->get_post('per_page', true));//当前页数

        //页面显示默认排序
        $search_where['orderby_type'] == 'desc' ? $new_orderby_type = 'asc' : $new_orderby_type = 'desc';
        assign('new_orderby_type', $new_orderby_type);
        if (!empty($this->input->get_post('keyword', true))){
            $keyword =  $this->input->get_post('keyword', true);
            assign('keyword', $keyword);
        }

        if (!empty($cat_id)) {
            //当前分类信息
            $goods_category_data = $this->loop_model->get_id('goods_category', $cat_id);
            assign('goods_category_data', $goods_category_data);
        }

        display('/goods/goods_list.html');
    }

    /**
     * 商品详情
     */
    public function product($id)
    {
        $id = (int)$id;
        if (!empty($id)) {
            $this->load->model('goods/goods_model');
            $goods_data = $this->goods_model->get_id($id);
            //图片预加载替换地址
            $goods_data['desc'] = str_replace('src="/upload', 'src="/public/images/loading.gif" width="100%" data-echo="/upload', $goods_data['desc']);

            //增加浏览量
            $visit       = $this->input->cookie('goods_visit', true);
            $goods_visit = "#" . $id . "#";
            if ($visit && strpos($visit, $goods_visit) !== false) {
            } else {
                $this->loop_model->update_id('goods', array('set' => array(array('visit', 'visit+1'))), $id);
                $visit = $visit === null ? $goods_visit : $visit . $goods_visit;
                $this->input->set_cookie('goods_visit', $visit, 3600 * 24 * 30);
            }

            //商品模型
            if (!empty($goods_data['model_id'])) {
                //扩展属性
                $goods_attr_data          = array();
                $attr_where               = array(
                    'select' => 'ma.name,ga.attr_value',
                    'where'  => array('ga.goods_id' => $id, 'ga.model_id' => $goods_data['model_id']),
                    'join'   => array(
                        array('goods_model_attr as ma', 'ga.attr_id=ma.id')
                    )
                );
                $goods_data['goods_attr'] = $this->loop_model->get_list('goods_attr as ga', $attr_where, '', '', 'ga.id asc');
            }

            $m_id = get_m_id();
            if (!empty($m_id)) {
                //否是收藏
                $favorite_data = $this->loop_model->get_where('goods_favorite', array('goods_id' => $id, 'm_id' => $m_id));
                if (!empty($favorite_data)) {
                    $goods_data['favorite'] = 1;
                }
            }
        }


        //店铺信息
        $shop_data = $this->loop_model->get_where('member_shop', array('m_id' => $goods_data['shop_id']));
        //店铺全部商品
        /*$shop_data['all_goods'] = $this->loop_model->get_list_num('goods', array('where' => array('shop_id' => $goods_data['shop_id'], 'status' => 0)));

        //店铺新品
        $shop_data['new_goods'] = $this->loop_model->get_list_num('goods', array('where' => array('shop_id' => $goods_data['shop_id'], 'status' => 0, 'up_time>' => time() - 3600 * 96)));

        //店铺收藏
        $shop_data['favorite']   = $this->loop_model->get_list_num('member_shop_favorite', array('where' => array('shop_id' => $goods_data['shop_id'])));*/
        $goods_data['shop_data'] = $shop_data;

        assign('goods_data', $goods_data);
        display('/goods/product.html');
    }

    /**
     * 商品评论
     */
    public function comment($id)
    {
        $id         = (int)$id;
        $goods_data = $this->loop_model->get_id('goods', $id);
        assign('goods_data', $goods_data);
        display('/goods/comment.html');
    }

    /**
     * 商品列表页加入购物车
     */
    public function join_cart_product($id)
    {
        $id = (int)$id;
        if (!empty($id)) {
            $this->load->model('goods/goods_model');
            $goods_data = $this->goods_model->get_id($id);

            //商品扩展属性
            if (!empty($id)) {
                $attr_where               = array(
                    'where'  => array('ga.goods_id' => $id, 'ga.model_id' => $goods_data['model_id']),
                    'join'   => array(
                        array('goods_model_attr as ma', 'ga.attr_id=ma.id')
                    ),
                    'select' => 'ma.name,ga.attr_value',
                );
                $goods_data['model_attr'] = $this->loop_model->get_list('goods_attr as ga', $attr_where, '', '', 'ga.id asc');
            }

            $m_id = get_m_id();
            if (!empty($m_id)) {
                //否是收藏
                $favorite_data = $this->loop_model->get_where('goods_favorite', array('goods_id' => $id, 'm_id' => $m_id));
                if (!empty($favorite_data)) {
                    $goods_data['favorite'] = 1;
                }
            }
        }
        assign('url', $this->input->get('url'));
        assign('id', $id);
        assign('goods_data', $goods_data);
        display('/goods/join_cart_product.html');
    }
}
