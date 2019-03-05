<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Favorite extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->helpers('web_helper');
        $this->member_data = member_login();
        $this->load->model('loop_model');
    }

    /**
     * 商品收藏
     */
    public function goods()
    {
        $pagesize = 20;//分页大小
        $page     = (int)$this->input->get('per_page');
        $page <= 1 ? $page = 1 : $page = $page;//当前页数
        //搜索条件start

        //搜索条件end
        $where_data['select']          = 'g.id as goods_id,g.name,g.image,g.market_price,g.sell_price,f.id';
        $where_data['where']['f.m_id'] = $this->member_data['id'];//过滤用户
        $where_data['join']            = array(
            array('goods as g', 'f.goods_id=g.id')
        );
        //查到数据
        $list_data = $this->loop_model->get_list('goods_favorite as f', $where_data, $pagesize, $pagesize * ($page - 1), 'f.id desc');//列表
        foreach ($list_data as $key) {
            $key['sell_price']   = format_price($key['sell_price']);
            $key['market_price'] = format_price($key['market_price']);
            $list[]              = $key;
        }
        assign('list', $list);
        //开始分页start
        $all_rows = $this->loop_model->get_list_num('goods_favorite as f', $where_data);//所有数量;
        assign('page_count', ceil($all_rows / $pagesize));
        //开始分页end
        display('/member/favorite/goods.html');
    }

    /**
     * 店铺收藏
     */
    public function shop()
    {
        $pagesize = 20;//分页大小
        $page     = (int)$this->input->get('per_page');
        $page <= 1 ? $page = 1 : $page = $page;//当前页数
        //搜索条件start

        //搜索条件end
        $where_data['select'] = 's.m_id as shop_id,s.shop_name,s.logo,s.level,f.id';
        $where_data['where']  = array('f.m_id' => $this->member_data['id']);//过滤用户
        $where_data['join']   = array(
            array('member_shop as s', 'f.shop_id=s.m_id')
        );
        //查到数据
        $list = $this->loop_model->get_list('member_shop_favorite as f', $where_data, $pagesize, $pagesize * ($page - 1), 'f.id desc');//列表
        assign('list', $list);
        //开始分页start
        $all_rows = $this->loop_model->get_list_num('member_shop_favorite as f', $where_data);//所有数量;
        assign('page_count', ceil($all_rows / $pagesize));
        //开始分页end

        assign('list', $list);
        display('/member/favorite/shop.html');
    }

}
