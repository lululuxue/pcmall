<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Order_model extends CI_Model
{

    public function __construct()
    {
        /**
         * 载入数据库类
         */
        $this->load->database();
    }

    /**
     * 更新或者添加订单
     * @param array $data_post 修改数据
     * @param int   $shop_id   店铺id,后台默认为0,店铺后台为店铺id
     * @return array
     */
    public function update($data_post = array(), $data)
    {
        //数据验证
        if (empty($data_post['phone'])) {
            return '联系电话不能为空';
        } elseif (empty($data_post['cat_id'])) {
            return '服务项目错误';
        } elseif (empty($data_post['start_time'])) {
            return '服务时间错误';
        } elseif (empty($data_post['address'])) {
            return '详细地址错误';
        }elseif (empty($data_post['note'])) {
            return '约单备注不能为空';
        } elseif (empty($data_post['name'])) {
            return '客户姓名不能为空';
        }

        $update_data = array(
            'shop'              => $data_post['shop'],
            'sub_shop'         => $data_post['sub_shop'],
            'full_name'         => $data_post['name'],
            'people_id'         => $data['id'],
            'service_id'       => (int)$data_post['cat_id'],
            'address'     => $data_post['address'],
            'prov'     => $data_post['province'],
            'city'     => $data_post['city'],
            'area'     => $data_post['area'],
            'price_real'   => $data_post['price_real'],
            'percentage' => $data_post['percentage'],
            'people'         => $data_post['people'],
            'phone'       => $data_post['phone'],
            'completetime'      => strtotime($data_post['start_time']),
            'addtime'     => time(),
            'tool'      => $data_post['tool'],
            'tel'      => $data_post['tel'],
            'order_status'      => $data_post['order_status'],
             'note'      => $data_post['note'],
            'is_pro'      => $data_post['is_pro'],
            'is_boli'      => $data_post['is_boli'],
           // 'order_no'      => $data_post['order_status'] == 1 ? date('Ymdhms',time()).substr(time(),-6) : '',
            'sex'      => 'gender0-'.$data_post['gender0'].'/gender1-'.$data_post['gender1'].'/gender2-'.$data_post['gender2'],
            'num_people' => $data_post['gender0'] + $data_post['gender1'] + $data_post['gender2']

        );

        //判断该用户是否存在
        $this->load->model('loop_model');
        $user_data = $this->loop_model->get_where('user',array('username'=>$data_post['phone']) );
        if($data_post['m_id']){
            $update_data['m_id'] = $data_post['m_id'];
        }

        if($data_post['m_id']){
            $update_data['m_id'] = $data_post['m_id'];
        }elseif($user_data){
           $update_data['m_id'] = $user_data['Id'];
        }else{
            //添加一个新用户
            $insert_data = array(
                'username'=>$data_post['phone'],
                'name'=>$data_post['name'],
                'addtime'=>time(),
            );
            $res = $this->loop_model->insert('user', $insert_data);
            if($res){
                $update_data['m_id'] = $res;
                //默认用户为默认卡
                $card_data = $this->loop_model->get_where('card', array('is_default'=>1));
/*
                $insert_card = array(
                    'card_no'=>$data_post['phone'],
                    'people_discount'=>$card_data['people_discount'],
                    'care_discount'=>$card_data['care_discount'],
                    'count'=>0,
                    'total_count'=>0,
                    'time'=>$card_data['time'],
                    'addtime'=>time(),
                    'endtime'=>'',
                    'm_id'=>$res,
                    'card_id'=> $card_data['Id']
                );
*/
                $insert_card = array(
                    'card_no'=>$data_post['phone'],
                    'count'=>0,
                    'total_count'=>0,
                    'addtime'=>time(),
                    'm_id'=>$res
                );
                $res = $this->loop_model->insert('user_new_card', $insert_card);
            }

        }
        if(!empty($data_post['id'])){
            //修改
            $where = array('id'=>$data_post['id']);
            $data = $this->loop_model->get_where('order',$where);
            if(!$data['order_no'] && $data_post['order_status'] == 1){
                $update_data['order_no'] = date('Ymdhms',time()).substr(time(),-6);
            }

            $res = $this->loop_model->update_where('order',$update_data,$where);
        }else{
            $update_data['order_no'] = $data_post['order_status'] == 1 ? date('Ymdhms',time()).substr(time(),-6) : '';
            $update_data['ordertime'] = time();
            $res = $this->loop_model->insert('order', $update_data);

        }
        if($res){
            return 'y';
        }else{
            return '信息保存失败';
        }
    }

    /**
     * 派单
     * @param array $data_post 修改数据
     * @param int   $shop_id   店铺id,后台默认为0,店铺后台为店铺id
     * @return array
     */
    public function update_work($data_post = array(),$data)
    {
        //数据验证
        if (empty($data_post['id'])) {
            return '订单错误';
        }
        if(!empty($data_post['fee_people'])) {
            $update_data['fee_people'] = $data_post['fee_people'];
        }
        if(!empty($data_post['people_amount'])) {
            $update_data['people_amount'] = $data_post['people_amount'];
        }
        if(!empty($data_post['people_add'])) {
            $update_data['people_add'] = $data_post['people_add'];
        }
        if(!empty($data_post['help_amount'])) {
            $update_data['help_amount'] = $data_post['help_amount'];
        }
        if(!empty($data_post['work_note'])) {
            $update_data['work_note'] = $data_post['work_note'];
        }
        if(!empty($data_post['real_people'])) {
            $update_data['real_people'] = $data_post['real_people'];
        }
        if(!empty($data_post['safe_people'])) {
            $update_data['safe_people'] = $data_post['safe_people'];
        }
        if(!empty($data_post['safe_note'])) {
            $update_data['safe_note'] = $data_post['safe_note'];
        }
        if(!empty($data_post['fee_note'])) {
            $update_data['fee_note'] = $data_post['fee_note'];
            $update_data['fee_time'] = time();
        }
        if(!empty($data_post['check_people'])) {
            $update_data['check_people'] = $data_post['check_people'];
        }
        if(!empty($data_post['check_note'])) {
            $update_data['check_note'] = $data_post['check_note'];
        }
        if(!empty($data_post['price_real'])) {
            $update_data['price_real'] = $data_post['price_real'];
        }
        if(!empty($data_post['promotion_price'])) {
            $update_data['promotion_price'] = $data_post['promotion_price'];
        }
        if(!empty($data_post['fee_people']) || !empty($data_post['work_note']) || !empty($data_post['real_people'])){
            $update_data['worktime'] = time();
            $update_data['work_admin_id'] = $data['id'];
            $update_data['status'] = $data_post['status'];
        }
        if(!empty($data_post['send_shop'])){
            $update_data['send_shop'] = $data_post['send_shop'];
            $update_data['is_send'] = 1;
            $update_data['status'] = $data_post['status'];
        }
        if(!empty($data_post['safe_people']) || !empty($data_post['safe_note'])){
            $update_data['safetime'] = time();
            $update_data['safe_admin_people'] = $data['full_name'];
            $update_data['status']= 4;
        }
        if(!empty($data_post['check_people']) || !empty($data_post['check_note'])){
            $update_data['checktime'] = time();
            $update_data['status'] = 6;
        }
        if(!empty($data_post['fee_note'])){
            $update_data['status'] = 5;
        }
        if(!empty($data_post['people_phone'])){
            $update_data['people_phone'] = $data_post['people_phone'];
        }


        //判断该用户是否存在
        $this->load->model('loop_model');
        //修改
        $where = array('id'=>$data_post['id']);//var_dump($data_post);var_dump($update_data);exit;
        if(!empty($data_post['card_consume']) || !empty($data_post['care_consume'])){
            //判断是否消费卡
            if(!empty($data_post['card_consume'])) {
                $insert_data['card_consume'] = $data_post['card_consume'];
            }
            if(!empty($data_post['care_consume'])) {
                $insert_data['care_consume'] = $data_post['care_consume'];
            }
            if($data_post['count'] < $data_post['card_consume']){
                return '余额不足';
            }
            $insert_data['free'] = $data_post['count'] - $data_post['card_consume'] - $data_post['care_consume'];
            $insert_data['total_count'] = $data_post['count'];
            $insert_data['consume'] = $data_post['card_consume'] + $data_post['care_consume'];
            $insert_data['user_card_id'] = $data_post['card_id'];
            $insert_data['addtime'] = time();
            $this->db->trans_begin();
            $res1 = $this->loop_model->insert('user_card_consume',$insert_data);

            //更新金额
            $update_data['price_real'] = $data_post['card_consume'] + $data_post['care_consume'];
            $update_data['promotion_price'] = $data_post['card_consume'] + $data_post['care_consume'];
            $update_data['pay_id']= $res1;
            $update_data['status']= 5;
            $res = $this->loop_model->update_where('order',$update_data,$where);

            //更新卡余额

            $up['count'] = $data_post['count'] - $data_post['card_consume'];
            $up['care_count'] = $data_post['care_count'] - $data_post['care_consume'];
            $res2 = $this->loop_model->update_where('user_card',$up,array('Id'=>$data_post['card_id']));

            //更新消费记录
            $user = $this->loop_model->get_id('order', $data_post['id']);
            //插入收款单
            $collection_data = array(
                'order_id'   => $data_post['id'],
                'm_id'       => $user['m_id'],
                'amount'     => '-'.$data_post['card_consume'],
                'addtime'    => time(),
                'payment_id' => 2,
                'note'       => '卡消费',
                'admin_user' => ''
            );
            $this->loop_model->insert('order_collection_doc', $collection_data);

            if ($this->db->trans_status() === TRUE) {
                $this->db->trans_commit();
                return 'y';
            } else {
                $this->db->trans_rollback();
                return '信息保存失败';
                //@todo 异常处理部分
            }
        }else{
            $res = $this->loop_model->update_where('order',$update_data,$where);
        }

        if($res){
            return 'y';
        }else{
            return '信息保存失败';
        }
    }

    /**
     * 订单支付
     * @param array $data_post 修改数据
     * @param int   $shop_id   店铺id,后台默认为0,店铺后台为店铺id
     * @return array
     */
    public function new_update_work($data_post = array(),$data)
    {
        //数据验证
        if (empty($data_post['id'])) {
            return '订单错误';
        }
        if(!empty($data_post['help_amount'])) {
            $update_data['help_amount'] = $data_post['help_amount'];
        }
        if(!empty($data_post['people_add'])) {
            $update_data['people_add'] = $data_post['people_add'];
        }
        if(!empty($data_post['people_amount'])) {
            $update_data['people_amount'] = $data_post['people_amount'];
        }
        if(!empty($data_post['fee_note'])) {
            $update_data['fee_note'] = $data_post['fee_note'];
            $update_data['fee_time'] = time();
            $update_data['status'] = 5;
        }
        if(!empty($data_post['price_real'])) {
            $update_data['price_real'] = $data_post['price_real'];
        }
        if(!empty($data_post['promotion_price'])) {
            $update_data['promotion_price'] = $data_post['promotion_price'];
        }
        //判断该用户是否存在
        $this->load->model('loop_model');
        //修改
        $where = array('id'=>$data_post['id']);//var_dump($data_post);var_dump($update_data);exit;

        $pay_id = $data_post['pay_id'];
        //判断是否余额是否足够
        if($pay_id == 1){
            if($data_post['total_count'] < $data_post['price_real']){
                return '余额不足';
            }
        }


        $user = $this->loop_model->get_id('order', $data_post['id']);
        $price_real = round(floatval($user['price_real']*100));
        $total_count = round(floatval($user['total_count']*100));
        $pay_money = round(floatval($user['pay_money']*100));
        $real_price_real = ($price_real - $total_count  - $pay_money)/100;
        $service = $this->loop_model->get_where('service',array('id'=>$user['service_id']));

        if($user['status'] > 4){
            error_json('该订单已经支付');
        }
            //增加积分
            $insert_data['order_id'] = $data_post['id'];
            if($pay_id == 1){
                $insert_data['type'] = 2;   //余额支付
            }else{
                $insert_data['type'] = 1;   //现金支付
            }
            $insert_data['m_id'] = $user['m_id'];
//            $insert_data['amount'] = $data_post['price_real'];
            $insert_data['amount'] =$real_price_real;
            $insert_data['addtime'] = time();
            $insert_data['note'] = $service['name'];
//            $insert_data['jifen'] = ceil($data_post['price_real']/10);
            $insert_data['jifen'] = ceil($real_price_real/10);
            $this->db->trans_begin();
            $res1 = $this->loop_model->insert('jifen',$insert_data);

            //更新金额
            $update_data['price_real'] = $data_post['price_real'] ;
            $update_data['promotion_price'] = $data_post['promotion_price'] ;
            if($pay_id == 1){
                $update_data['pay_id']= $data_post['card_id'];   //余额支付
            }else{
                $update_data['pay_id'] = 0;   //现金支付
            }
            $update_data['pay_money'] = $data_post['price_real'] ;
            $update_data['payment_status'] = 1;
            $update_data['status']= 5;
            $res = $this->loop_model->update_where('order',$update_data,$where);

            //更余额
            if($pay_id == 1){
                //余额支付
//                $up['total_count'] = $data_post['total_count'] - $data_post['price_real'];
                $up['total_count'] = $data_post['total_count'] - $real_price_real;
                $res2 = $this->loop_model->update_where('user_new_card',$up,array('Id'=>$data_post['card_id']));
            }


            //更新消费记录

            //插入收款单
            $collection_data = array(
                'order_id'   => $data_post['id'],
                'm_id'       => $user['m_id'],
//                'amount'     => '-'.$data_post['price_real'],
                'amount'     => '-'.$real_price_real,
                'addtime'    => time(),
                'payment_id' => $pay_id == 1 ? 2 : 1,
                'note'       => $service['name'],
                'admin_user' => '',
//                'consume'    => '-'.$data_post['price_real'],
                'consume'    => '-'.$real_price_real,
                'add_money' => 0
            );
            $this->loop_model->insert('order_collection_doc', $collection_data);

            //更新积分
            $jifen = $this->loop_model->get_id('user', $user['m_id']);
//            $user_date['jifen'] = (int)$jifen['jifen'] + (int)(ceil($data_post['price_real']/10));
        $user_date['jifen'] = (int)$jifen['jifen'] + (int)(ceil($real_price_real/10));
            $this->loop_model->update_where('user', $user_date,array('id'=>$user['m_id']));

            if ($this->db->trans_status() === TRUE) {
                $this->db->trans_commit();
                return 'y';
            } else {
                $this->db->trans_rollback();
                return '信息保存失败';
                //@todo 异常处理部分
            }

        if($res){
            return 'y';
        }else{
            return '信息保存失败';
        }
    }

    /**
     * 待支付订单修改pay_save
     */
    public function pay_save($data_post = array(),$data){
        //数据验证
        if (empty($data_post['id'])) {
            return '订单错误';
        }
        if(!empty($data_post['help_amount'])) {
            $update_data['help_amount'] = $data_post['help_amount'];
        }
        if(!empty($data_post['people_add'])) {
            $update_data['people_add'] = $data_post['people_add'];
        }
        if(!empty($data_post['people_amount'])) {
            $update_data['people_amount'] = $data_post['people_amount'];
        }
        if(!empty($data_post['fee_note'])) {
            $update_data['fee_note'] = $data_post['fee_note'];
        }
        if(!empty($data_post['price_real'])) {
            $update_data['price_real'] = $data_post['price_real'];
        }
        if(!empty($data_post['promotion_price'])) {
            $update_data['promotion_price'] = $data_post['promotion_price'];
        }
        //判断该用户是否存在
        $this->load->model('loop_model');
        //修改
        $where = array('id'=>$data_post['id']);//var_dump($data_post);var_dump($update_data);exit;

        $res = $this->loop_model->update_where('order',$update_data,$where);

        if($res >= 0){
            return 'y';
        }else{
            return '信息保存失败';
        }
    }

    /**
     * 手机端更新或者添加订单
     * @param array $data_post 修改数据
     * @param int   $shop_id   店铺id,后台默认为0,店铺后台为店铺id
     * @return array
     */
    public function wx_update($data_post = array(), $data)
    {
        //数据验证
        if (empty($data_post['service_id'])) {
            return '项目id不能为空';
        } elseif (empty($data_post['address_id'])) {
            return '请选择服务地址';
        } elseif (empty($data_post['start_time'])) {
            return '请选择预约时间';
        }
        //根据服务地址获取
        $address = $this->loop_model->get_where('user_address',array('id'=>$data_post['address_id']));

        $update_data = array(
            'full_name'         => $address['full_name'],
            'service_id'       => (int)$data_post['service_id'],
            'source_id'     => '15',//公众号
            //'order_status'     => 1,
            'prov'     => $address['province'],
            'city'     => $address['city'],
            'area'     => $address['area'],
            'address'     => $address['address'],
            'phone'       => $address['tel'],
            'completetime'      => strtotime($data_post['start_time']),
            'addtime'     => time(),
            'ordertime'   => time(),
            'note'      => $data_post['note'],
            'm_id'      => $data_post['m_id'],
            'num_people' =>$data_post['num_people']
        );

        $res = $this->loop_model->insert('order', $update_data);

        if($res){
            //根据服务地址获取
            $user = $this->loop_model->get_list('phone_message');
            //发送短信通知
            if($user){
                foreach($user as $val){
                    $mobile = $val['phone'];
                    $mobile1 = $address['tel'];
                    $tmp = 'SMS_147416321';
                    $date = date("Y-m-d H:i",strtotime($data_post['start_time']));
                    $this->load->library('SmsDemo');
                    $res1  = SmsDemo::sendOtherSms($mobile,$tmp,$date,'',$mobile1);
                }
                if ($res1->Code == 'OK') {
                    return 'y';
                } else {
                    return($res1->Message);
                }
            }else{
                return 'y';

            }


//            return 'y';
        }else{
            return '信息保存失败';
        }
    }

	  /**
     * 手机端充值申请生成订单
     * @param array $data_post 修改数据
     * @param int   $shop_id   店铺id,后台默认为0,店铺后台为店铺id
     * @return array
     */
    public function order_recharge($data)
    {
		$user = $this->loop_model->get_where('user',array('Id'=>$data['m_id']));
        $update_data = array(
            'full_name'         => $user['name'],
            'service_id'        => 113,
            'source_id'         => '15',//公众号
            'order_status'      => 1,
            'phone'             => $user['username'],
            'addtime'			 => time(),
            'm_id'				 => $data['m_id'],
            'num_people'		 => 0,
			'card_id'		     => $data['id'],
			'order_no'			=>date('Ymdhms',time()).substr(time(),-6)
        );
        $card= $this->loop_model->get_where('new_card',array('Id'=>$data['id']));
        if($card){
            $update_data['price_real'] = $card['count'];
            $update_data['promotion_price'] = $card['count'];
            $update_data['add_money'] = $card['add_money'];
        }

        $res = $this->loop_model->insert('order', $update_data);
        if($res){
            return array('status'=>'y','order_no'=>$update_data['order_no']);
        }else{
            return array('status'=>'信息保存失败');
        }
    }



}
