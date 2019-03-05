<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Coupons_model extends CI_Model
{

    public function __construct()
    {
        /**
         * 载入数据库类
         */
        $this->load->database();
    }

    /**
     * 生成优惠券
     * @param $coupons_data array 要生成的优惠券信息
     * @param $generate_num int   生成数量
     * @param $m_id         int   用户id,生成的券需绑定用户的时候需要传入
     */
    public function generate($coupons_data = '', $generate_num = 1, $m_id = '')
    {
        $generate_num = (int)$generate_num;
        if (!empty($coupons_data) && $generate_num > 0) {
            //开始生成
            for ($i = 1; $i <= $generate_num; $i++) {
                $password   = substr(md5(time() . $coupons_data['id'] . get_rand_num('str', 6)), 8, 16);
                $insert_arr = array();
                $insert_arr = array(
                    'cou_id'   => $coupons_data['id'],
                    'password' => strtoupper($password),
                    'shop_id'  => $coupons_data['shop_id'],
                );
                if (!empty($m_id)) {
                    $insert_arr['m_id']    = $m_id;
                    $insert_arr['is_send'] = 1;
                }
                $insert_data[] = $insert_arr;
            }
            //增加数据
            $res = $this->loop_model->insert('coupons_detail', $insert_data, 'insert_batch');
            if (!empty($res)) {
                return 'y';
            } else {
                return '生成失败';
            }
        }
    }

    /**
     * 退还优惠券
     * @param $coupons_id int 优惠券id
     */
    public function back_coupons($coupons_id = '')
    {
        if (!empty($coupons_id)) {
            $this->loop_model->update_where('coupons_detail', array('status' => 0), array('id' => $coupons_id));
        }
    }

    /**
     * 使用优惠券
     * @param $coupons_id int 优惠券id
     */
    public function use_coupons($coupons_id = '')
    {
        if (!empty($coupons_id)) {
            $this->loop_model->update_where('coupons_detail', array('status' => 1), array('id' => $coupons_id));
        }
    }

    /**
     * 查询用户再店铺内满足金额的优惠券
     * @param $m_id           int 用户id
     * @param $shop_id        int 店铺id
     * @param $all_sell_price float 订单金额
     */
    public function user_shop_coupons($m_id = '', $shop_id = '', $all_sell_price = '')
    {
        $coupons_list = '';
        if (!empty($m_id) && !empty($shop_id) && !empty($all_sell_price)) {
            $this->load->model('loop_model');
            $coupons_where     = array(
                'select' => 'd.*,c.name,c.amount,c.use_price,c.shop_id',
                'where'  => array('d.status' => 0, 'is_send' => 1, 'is_close' => 0, 'm_id' => $m_id, 'd.shop_id' => $shop_id, 'start_time<=' => time(), 'end_time>=' => time(), 'use_price<=' => $all_sell_price, 'c.status' => 0),
                'join'   => array(
                    array('coupons as c', 'd.cou_id=c.id'),
                ),
            );
            $coupons_list_data = $this->loop_model->get_list('coupons_detail as d', $coupons_where, '', '', 'amount desc');
            if (!empty($coupons_list_data)) {
                foreach ($coupons_list_data as $key) {
                    $key['amount']    = format_price($key['amount']);
                    $key['use_price'] = format_price($key['use_price']);
                    $coupons_list[]   = $key;
                }
            }
        }
        return $coupons_list;
    }
}
