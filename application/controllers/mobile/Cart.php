<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Cart extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->helpers('web_helper');
        $this->member_data = member_login();
        $this->load->model('loop_model');
    }

    /**
     * 购物车列表
     */
    public function index()
    {
        display('/cart/index.html');
    }

    /**
     * 订单确认
     */
    public function confirm()
    {
        $sku_id   = $this->input->get_post('sku_id');//选择的商品,购物车是数组,直接购买是skuid
        $buy_type = $this->input->get_post('buy_type');//购买类型cart购物车,sku单个商品直接购买
        $buy_num  = $this->input->get_post('buy_num');//购买数量,购物车时不需要
        if (empty($buy_type)) $buy_type = 'cart';
        if (!empty($sku_id) && !empty($buy_type)) {
            //获取用户的默认收货地址,没有默认的获取第一个,没有的话跳转到地址添加页面
            $address_id            = $this->input->get_post('address_id');//地址id
            $address_where['m_id'] = $this->member_data['id'];
            if (!empty($address_id)) {
                $address_where['id'] = $address_id;
            }
            $this->load->model('member/user_address_model');
            $address_data = $this->user_address_model->get_address($address_where);

            $address_url = site_url(get_web_type() . '/member/address?redirect_url=' . urlencode(get_now_url()));
            if (empty($address_data)) {
                //去添加地址
                header('location:' . $address_url);
            } else {
                //地址信息
                $address_data['select_url'] = $address_url;
                assign('address_data', $address_data);
            }

            //结算信息
            //购物车结算
            $this->load->model('goods/goods_sum_model');
            if ($buy_type == 'cart') {
                $cart_goods = $this->goods_sum_model->cart_select_count($sku_id, $this->member_data['id']);
            } elseif ($buy_type == 'sku') {
                //直接购买
                if (empty($buy_num)) $buy_num = 1;
                $cart_goods = array($sku_id => $buy_num);
            }
            $shop_list = $this->goods_sum_model->order_count($cart_goods, $address_data['prov']);
            if (is_array($shop_list)) {
                if (is_array($sku_id)) $sku_id = join(',', $sku_id);
                assign('sku_id', $sku_id);
                assign('buy_type', $buy_type);
                assign('buy_num', $buy_num);
                assign('shop_list', $shop_list);
                display('/cart/confirm.html');
            } else {
                msg($shop_list);
            }
        } else {
            msg('请选择商品', site_url(get_web_type() . '/cart'));
        }
    }

    /**
     * 订单提交
     */
    public function commit()
    {
        $sku_id   = $this->input->get_post('sku_id');//选择的商品,购物车需要转化成数组,直接购买是skuid
        $buy_type = $this->input->get_post('buy_type');//购买类型cart购物车,sku单个商品直接购买
        $buy_num  = $this->input->get_post('buy_num');//购买数量,购物车时不需要
        if (empty($buy_type)) {
            msg('购买来源出错');
        }
        if (empty($sku_id)) {
            msg('还没有选择商品');
        }
        $payment_id    = $this->input->get_post('payment_id');//支付方式
        $delivery_id   = $this->input->get_post('delivery_id');//配送方式
        $delivery_time = $this->input->get_post('delivery_time');//配送时间
        $address_id    = $this->input->get_post('address_id');//收货地址id
        $coupons_id    = $this->input->get_post('coupons_id');//优惠券id
        $m_desc        = $this->input->get_post('m_desc');//用户备注

        //支付方式
        if (empty($payment_id)) {
            msg('请选择支付方式');
        }
        //收货地址
        $address_data = $this->loop_model->get_id('member_user_address', $address_id);
        if (empty($address_data)) {
            msg('收货地址不存在');
        }
        //结算商品信息
        //购物车结算
        $this->load->model('goods/goods_sum_model');
        if ($buy_type == 'cart') {
            $sku_id     = explode(',', $sku_id);
            $cart_goods = $this->goods_sum_model->cart_select_count($sku_id, $this->member_data['id']);
        } elseif ($buy_type == 'sku') {
            //直接购买
            if (empty($buy_num)) $buy_num = 1;
            $cart_goods = array($sku_id => $buy_num);
        }
        $shop_list = $this->goods_sum_model->order_count($cart_goods, $address_data['prov']);
        if (empty($shop_list['result']['list'])) {
            msg('购买商品不存在');
        }

        //按店铺拆分订单
        $all_order_price = 0;
        foreach ($shop_list['result']['list'] as $shop_id => $key) {
            //配送方式是否存在
            $delivery_data = array();
            if (!empty($key['delivery_list'])) {
                foreach ($key['delivery_list'] as $d) {
                    if ($d['id'] == $delivery_id[$shop_id]) {
                        $delivery_data = $d;
                    }
                }
            }
            if (empty($delivery_data)) {
                msg('店铺“' . $key['shop_data']['shop_name'] . '”在该收货地址没有可送达的快递');
            }

            if ($delivery_data['type'] == 1) $payment_id = 1;//货到付款,付款方式为1
            //组合订单数据
            $order_data = array(
                'm_id'                => $this->member_data['id'],
                'order_no'            => date('YmdHis') . get_rand_num('int', 6),
                'payment_id'          => $payment_id,
                'delivery_id'         => $delivery_id[$shop_id],
                'delivery_time'       => $delivery_time[$shop_id],
                'shop_id'             => $shop_id,
                'status'              => 1,
                'full_name'           => $address_data['full_name'],
                'tel'                 => $address_data['tel'],
                'prov'                => $address_data['prov'],
                'city'                => $address_data['city'],
                'area'                => $address_data['area'],
                'address'             => $address_data['address'],
                'sku_price'           => price_format($key['all_market_price']),
                'sku_price_real'      => price_format($key['all_sell_price']),
                'delivery_price'      => price_format($delivery_data['old_price']),
                'delivery_price_real' => price_format($delivery_data['price']),
                'm_desc'              => $m_desc[$shop_id],
                'addtime'             => time(),
            );
            $admin_desc = '';//店铺备注
            //是否有优惠活动
            if (!empty($key['promotion_price']) && !empty($key['promotion_data'])) {
                $order_data['promotion_price'] = price_format($key['promotion_price']);//优惠活动金额
                $admin_desc .= $key['promotion_data']['name'] . '活动优惠【￥' . $key['promotion_price'] . '】 ';//优惠活动备注
            }
            //验证优惠券
            $coupons_data = array();
            if (!empty($key['coupons_list'])) {
                foreach ($key['coupons_list'] as $c) {
                    if ($c['id'] == $coupons_id[$shop_id]) {
                        $coupons_data = $c;
                    }
                }
                if (!empty($coupons_data)) {
                    $order_data['coupons_id']    = $coupons_data['id'];//优惠券id
                    $order_data['coupons_price'] = price_format($coupons_data['amount']);//优惠券金额
                    $admin_desc .= $coupons_data['name'] . ' 满￥' . $coupons_data['use_price'] . '优惠【￥' . $coupons_data['amount'] . '】 ';//优惠备注
                    //锁定优惠券
                    $this->load->model('market/coupons_model');
                    $this->coupons_model->use_coupons($coupons_data['id']);
                }
            }
            $order_data['admin_desc'] = $admin_desc;//优惠备注
            //订单总价
            $order_data['order_price'] = price_format($key['all_sell_price'] + $delivery_data['price'] - $coupons_data['amount'] - $key['promotion_price']);
            if ($order_data['order_price'] <= 0) $order_data['order_price'] = 0;//订单少于0元的时候直接等于0元

            $this->load->model('order/order_model');
            //添加订单商品
            $this->order_model->add($order_data, $key['sku_list']);
            //订单金额为0时，订单自动完成
            if ($order_data['order_price'] == 0) {
                $this->order_model->update_pay_status($order_data['order_no']);
            }
            $all_order_price = $all_order_price + $order_data['order_price'];
            $order_no[]      = $order_data['order_no'];
        }

        //删除购物车已经下单的商品
        if ($buy_type == 'cart') {
            $this->load->model('goods/cart_model');
            $this->cart_model->delete($sku_id, $this->member_data['id']);
        }

        //订单金额为0时，订单自动完成
        if ($all_order_price <= 0) {
            header('location:' . site_url(get_web_type() . '/member/order'));
        } else {
            //直接去支付
            header('location:' . site_url('/api/pay/do_pay?client=' . get_web_type() . '&order_no=' . join(',', $order_no)));
        }
    }

}
