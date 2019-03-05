<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Promotion_model extends CI_Model
{

    public function __construct()
    {
        /**
         * 载入数据库类
         */
        $this->load->database();
    }

    /**
     * 现金活动
     * @param  $m_id    int 用户id
     * @param  $shop_id int 店铺id
     * @param  $price   int 消费金额(单位分)
     */
    public function cash_list($m_id, $shop_id, $price)
    {
        $promotion_data  = array();
        $promotion_price = 0;//优惠活动金额
        if (!empty($m_id) && !empty($shop_id) && !empty($price)) {
            $promotion_list = self::prop_list($m_id, $shop_id, $price, array(1, 2));
            if (!empty($promotion_list)) {
                foreach ($promotion_list as $k) {
                    switch ($k['type']) {
                        case 1:
                            $prop_price = $k['type_value'];//优惠金额
                            break;
                        case 2:
                            $prop_price = $price * ($k['type_value'] / 100);//优惠金额
                            break;
                    }
                    if ($prop_price > $promotion_price) {
                        $promotion_price = $prop_price;//只保留优惠最多的
                        unset($k['type']);
                        unset($k['type_value']);
                        $promotion_data['promotion_data'] = $k;
                    }
                }
                $promotion_data['promotion_price'] = format_price($promotion_price);
            }
        }
        return $promotion_data;
    }

    /**
     * 包邮活动
     * @param  $m_id    int 用户id
     * @param  $shop_id int 店铺id
     * @param  $price   int 消费金额(单位分)
     */
    public function free_delivery_list($m_id, $shop_id, $price)
    {
        $free_delivery = false;
        if (!empty($m_id) && !empty($shop_id) && !empty($price)) {
            $promotion_list = self::prop_list($m_id, $shop_id, $price, array(4));
            if (!empty($promotion_list)) {
                $free_delivery = true;
            }
        }
        return $free_delivery;
    }

    /**
     * 赠送活动
     * @param  $order_data array 订单信息
     */
    public function give_list($order_data)
    {
        $prop_point = 0;
        $prop_data  = array();
        $coupons_id = array();
        if (!empty($order_data)) {
            $order_price    = $order_data['order_price'] - $order_data['delivery_price_real'];//不能算上邮费
            $promotion_list = self::prop_list($order_data['m_id'], $order_data['shop_id'], $order_price, array(3, 5));
            if (!empty($promotion_list)) {
                foreach ($promotion_list as $k) {
                    switch ($k['type']) {
                        case 3:
                            //赠送积分
                            if ($k['type_value'] > $prop_point) {
                                $prop_point = $k['type_value'];//只保留积分赠送最多的
                                $prop_data  = $k;
                            }
                            break;
                        case 5:
                            //赠送优惠券
                            $coupons_id[] = $k['type_value'];
                            break;
                    }
                }
                //开始处理赠送
                //发放赠送积分
                if ($prop_point > 0) {
                    $this->load->model('member/user_point_log_model');
                    $data = array(
                        'm_id'   => $order_data['m_id'],
                        'amount' => $prop_point,
                        'event'  => 2,
                        'note'   => '订单【' . $order_data['order_no'] . '】满足活动【' . $prop_data['name'] . '】赠送',
                    );
                    $this->user_point_log_model->insert($data);
                }
                //发放赠送优惠券
                if (!empty($coupons_id)) {
                    $this->db->from('coupons');
                    $this->db->select('id,name,amount,shop_id');
                    $this->db->where_in('id', $coupons_id);
                    $this->db->where(
                        array(
                            'start_time<=' => time(),
                            'end_time>='   => time(),
                            'shop_id'      => $order_data['shop_id'],
                            'status'       => 0,
                        )
                    );
                    $this->db->order_by('amount', 'desc');
                    $coupons      = $this->db->get();
                    $coupons_list = $coupons->result_array();
                    if (!empty($coupons_list[0])) {
                        $this->load->model('market/coupons_model');
                        $this->coupons_model->generate($coupons_list[0], 1, $order_data['m_id']);
                    }
                }
            }
        }
    }

    /**
     * 可以参与的优惠活动
     * @param  $m_id    int 用户id
     * @param  $shop_id int 店铺id
     * @param  $price   int 消费金额(单位分)
     * @param  $type    array 活动规则
     */
    public function prop_list($m_id, $shop_id, $price, $type)
    {
        $promotion_list = array();
        if (!empty($m_id) && !empty($shop_id) && !empty($price) && !empty($type)) {
            //查询用户组信息
            $member_user_query = $this->db->get_where('member_user', array('m_id' => $m_id));
            $member_user_data  = $member_user_query->row_array();
            //可以参与的优惠活动
            $this->db->from('promotion');
            $this->db->select('id,name,type,type_value');
            $this->db->where("find_in_set(0, user_group)");
            if ($member_user_data['group_id'] > 0) $this->db->where("find_in_set(" . $member_user_data['group_id'] . ", user_group)");
            $this->db->where_in('type', $type);
            $this->db->where(
                array(
                    'start_time<=' => time(),
                    'end_time>='   => time(),
                    'shop_id'      => $shop_id,
                    'status'       => 0,
                    'use_price<='  => $price,
                )
            );
            $promotion      = $this->db->get();
            $promotion_list = $promotion->result_array();
        }
        return $promotion_list;
    }
}
