<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Payment_model extends CI_Model
{

    public function __construct()
    {
        /**
         * 载入数据库类
         */
        $this->load->database();
    }

    /**
     * 根据客户端查询支付方式
     * @param $type 类型,all所有支付列表,online_recharge在线充值,online_pay在线支付
     * @return array
     */
    public function payment_list($type = '')
    {
        $this->db->from('payment');
        $this->db->select('id,name,logo');

        $client = get_client();
        if ($client == 'weixin') {
            //微信端
            $this->db->where_in('client_type', array(2, 3));
        } elseif ($client == 'mobile') {
            //手机端
            $this->db->where_in('client_type', array(2, 3));
            $this->db->where(array('id!=' => 3));
        } elseif ($client == 'web') {
            //pc端
            $this->db->where_in('client_type', array(1, 3));
        }
        $this->db->where(array('status' => 0));
        if ($type == 'online_recharge') {
            $this->db->where(array('id>' => 2));//充值不能使用余额和货到付款
        } elseif ($type == 'online_pay') {
            $this->db->where(array('id>' => 1));//在线支付不包含货到付款
        }
        $this->db->order_by('sortnum asc,id asc');
        $query        = $this->db->get();
        $payment_list = $query->result_array();//echo $this->db->last_query()."<br>";
        return $payment_list;
    }
}
