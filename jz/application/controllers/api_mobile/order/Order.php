<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Order extends MY_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->model('loop_model');
    }

    /**
     * 我的订单列表
     * status(null-全部订单，1-代付款，2-待发货，3-待收货，4-待评价，10-退款/售后）
     * */
   public function order_list()
   {
       header('Access-Control-Allow-Origin: *');
       // 响应类型
       header('Access-Control-Allow-Methods:GET, POST, PUT,DELETE');
       // 响应头设置
       header('Access-Control-Allow-Headers:x-requested-with,content-type');
       $pagesize = 100;//分页大小
       $page     = (int)$this->input->get('per_page');
       $page <= 1 ? $page = 1 : $page = $page;//当前页数
       $m_id = (int)$this->input->get('m_id');
       //搜索条件start

       $type = $this->input->get('type');//(1-等待服务，2-服务中，3-服务完成)
       if (!empty($type)) {
           switch ($type) {
               case 1:
                   $where_data['where_in']['o.status']      = array(1,2);
                   break;
               case 2:
                   $where_data['where_in']['o.status']  = 3;//已派工
                   break;
               case 3:
                   $where_data['where_in']['o.status'] = array(4,5);//待评价（已结算）
                   break;
               case 4:
                   $where_data['where']['o.status >'] = 5;//（已完结）
                   break;
               default:
                   $where_data['where_in']['o.status'] = array(1,2);
                   break;
           }
       }
       //否则所有订单
       $where_data['select']             = 'o.id,o.order_no,o.status,FROM_UNIXTIME(o.addtime,"%Y-%m-%d   %H:%m:%s") as addtime,FROM_UNIXTIME(o.completetime,"%Y-%m-%d %H:%m:%s") as completetime,o.real_people,o.price_real,o.prov,o.city,o.area,o.address,o.full_name,o.phone,b.image,b.name,b.amount,b.type_name';
       $where_data['where']['o.m_id']    = $m_id;//过滤用户
       $where_data['join']               = array(
           array('service as b', 'o.service_id=b.id')
       );
       //查到数据
       $order_list = $this->loop_model->get_list('order as o', $where_data, $pagesize, $pagesize * ($page - 1), 'o.id desc');//列表
      // assign('list', $list);
       //开始分页start
       $all_rows = $this->loop_model->get_list_num('order as o', $where_data);//所有数量;
      // assign('page_count', ceil($all_rows / $pagesize));
       //开始分页end
       $orderList = [
           'list'=>$order_list,
          'page_count'=> ceil($all_rows / $pagesize)
       ];
       error_json($orderList);


   }

    /**
     * 订单详情
     */
    public function order_detail()
    {
        header('Access-Control-Allow-Origin: *');
        // 响应类型
        header('Access-Control-Allow-Methods:GET, POST, PUT,DELETE');
        // 响应头设置
        header('Access-Control-Allow-Headers:x-requested-with,content-type');
        $post_data = $this->input->get(NULL);
        if (empty($post_data['id'])) error_json('订单id不能为空');
        $order_data = $this->loop_model->get_where('order',array('id'=>$post_data['id']));
        if (!$order_data) error_json('该订单不存在');
        $service_data = $this->loop_model->get_where('service',array('id'=>$order_data['service_id']));
        //获取用户的余额
        $card_data = $this->loop_model->get_where('user_new_card',array('m_id'=>$order_data['m_id']));
        //$order_data['service_name'] = $service_data['name'];
        $data['service_name'] = $service_data['name'];
        $data['amount'] = $service_data['amount'];
        $data['type_name'] = $service_data['type_name'];
        $data['completetime'] = date('Y-m-d  H:i');
        $data['id'] = $order_data['id'];
        $data['real_people'] = $order_data['real_people'];
        $data['price_real'] = $order_data['price_real'];
        $data['prov'] = $order_data['prov'];
        $data['city'] = $order_data['city'];
        $data['area'] = $order_data['area'];
        $data['order_no'] = $order_data['order_no'];
        $data['address'] = $order_data['address'];
        $data['full_name'] = $order_data['full_name'];
        $data['image'] = $service_data['image'];
        $data['phone'] = $order_data['phone'];
        $data['total_count']= $card_data['total_count'];
        //1-等待服务订单,2-服务中订单,3-服务完成订单
        if($order_data['status'] == 1 || $order_data['status'] == 2){
            $data['order_status'] = '等待服务';
        }elseif($order_data['status'] == 3){
            $data['order_status'] = '服务中';
        }elseif($order_data['status'] == 4 || $order_data['status'] == 5){
            $data['order_status'] = '待评价';
        }elseif($order_data['status'] == 6){
            $data['order_status'] = '服务完成';
        }else{
            $data['order_status'] = '已取消订单';
        }
        error_json($data);
    }

    /**
     * 新增订单
     */
    public function create_order()
    {
        header('Access-Control-Allow-Origin: *');
        // 响应类型
        header('Access-Control-Allow-Methods:GET, POST, PUT,DELETE');
        // 响应头设置
        header('Access-Control-Allow-Headers:x-requested-with,content-type');
        $post_data = $this->input->get(NULL);
        $this->load->model('service/order_model');
        $res = $this->order_model->wx_update($post_data);
        error_json($res);
    }

	 /**
     * 充值接口
     */
    public function recharge()
    {
        header('Access-Control-Allow-Origin: *');
        // 响应类型
        header('Access-Control-Allow-Methods:GET, POST, PUT,DELETE');
        // 响应头设置
        header('Access-Control-Allow-Headers:x-requested-with,content-type');
        $post_data = $this->input->get(NULL);
	
        $this->load->model('service/order_model');
        $res = $this->order_model->order_recharge($post_data);
		if($res['status'] == 'y'){
			error_json($res);
		}else{
			error_json('保存失败');
		}
        
    }

    /**
     * 评价接口
     */
    public function comment()
    {
        header('Access-Control-Allow-Origin: *');
        // 响应类型
        header('Access-Control-Allow-Methods:GET, POST, PUT,DELETE');
        // 响应头设置
        header('Access-Control-Allow-Headers:x-requested-with,content-type');
        $post_data = $this->input->get(NULL);
        $res1 = $this->loop_model->get_where('comment',array('order_id'=>$post_data['order_id']));
        if($res1){
            error_json('该订单已评价');
        }
        $insert['order_id'] = $post_data['order_id'];
        $insert['attitude'] = $post_data['attitude'];
        $insert['clear'] = $post_data['clear'];
        $insert['comment'] = $post_data['comment'];
        $insert['addtime'] = time();
        $insert['m_id'] = $post_data['m_id'];
        $this->db->trans_begin();
        $res = $this->loop_model->insert('comment',$insert);
        $res2 = $this->loop_model->update_id('order',array('status'=>6),$post_data['order_id']);

        //插入积分
        $insert_data['order_id'] = $post_data['id'];
        $insert_data['type'] = 0;   //微信支付
        $insert_data['m_id'] = $post_data['m_id'];
        $insert_data['amount'] = 0;
        $insert_data['addtime'] = time();
        $insert_data['note'] = '评价';
        $insert_data['jifen'] = 5;
        $res1 = $this->loop_model->insert('jifen',$insert_data);
        //更新积分
        $jifen = $this->loop_model->get_id('user', $post_data['m_id']);
        $user_date['jifen'] = (int)$jifen['jifen'] + 5;
        $this->loop_model->update_where('user', $user_date,array('id'=>$post_data['m_id']));
        if ($this->db->trans_status() === TRUE) {
            $this->db->trans_commit();
            error_json('y');
        } else {
            $this->db->trans_rollback();
            error_json('保存失败');
            //@todo 异常处理部分
        }
    }

    /**
     * 余额支付
     */
    public function pay()
    {
        header('Access-Control-Allow-Origin: *');
        // 响应类型
        header('Access-Control-Allow-Methods:GET, POST, PUT,DELETE');
        // 响应头设置
        header('Access-Control-Allow-Headers:x-requested-with,content-type');
        //判断是否余额是否足够

        $post_data = $this->input->get(NULL);
        $order_data = $this->loop_model->get_where('order',array('id'=>$post_data['id']));
        $card_data = $this->loop_model->get_where('user_new_card',array('m_id'=>$post_data['m_id']));

        $price_real = round(floatval($order_data['price_real']));
        $total_count = round(floatval($card_data['total_count']));
        $pay_money = round(floatval($order_data['pay_money']));
        //判断用户
        if($order_data['status'] > 4){
            error_json('该订单已经支付');
        }
        if($card_data['total_count'] < ($order_data['price_real'] + $order_data['pay_money'] )){
            //error_json('余额不足');
            $wx_pay_amount = $price_real - $total_count  - $pay_money;
            $price = $card_data['total_count'];
        }else{
            $price = round($price_real - $pay_money);
            $wx_pay_amount = 0;
        }
        $service_data = $this->loop_model->get_where('service',array('id'=>$order_data['service_id']));
        if($wx_pay_amount > 0){
            $update_data = array(
                'pay_money'         => $price,
                'paytime'        => time(),
//                'payment_status' => 1,
            );
        }else{
            $update_data = array(
                'pay_money'         => $price,
                'status'         => 5,
                'paytime'        => time(),
                'payment_status' => 1,
            );
        }

        $this->db->trans_begin();
        $this->loop_model->update_id('order', $update_data, $post_data['id']);
        //插入收款单
        $collection_data = array(
            'order_id'   => $post_data['id'],
            'm_id'       => $post_data['m_id'],
//            'amount'     => '-'.$order_data['price_real'],
//            'consume'     => '-'.$order_data['price_real'],

            'amount'     => '-'.$price,
            'consume'     => '-'.$price,
            'add_money'     => $order_data['add_money'],
            'addtime'    => time(),
            'payment_id' => 2,
            'note'       => $service_data['name'],
            'admin_user' => ''
        );
        $this->loop_model->insert('order_collection_doc', $collection_data);

        //插入积分
        $insert_data['order_id'] = $order_data['id'];
        $insert_data['type'] = 2;   //余额支付
        $insert_data['m_id'] = $order_data['m_id'];
//        $insert_data['amount'] = $order_data['price_real'];
        $insert_data['amount'] = $price;
        $insert_data['addtime'] = time();
        $insert_data['note'] = $service_data['name'];
//        $insert_data['jifen'] = ceil($order_data['price_real']/10);
        $insert_data['jifen'] = ceil($price/10);
        $res1 = $this->loop_model->insert('jifen',$insert_data);
        //更新积分
        $jifen = $this->loop_model->get_id('user', $order_data['m_id']);
//        $user_date['jifen'] = (int)$jifen['jifen'] + (int)(ceil($order_data['price_real']/10));
        $user_date['jifen'] = (int)$jifen['jifen'] + (int)(ceil($price/10));
        $this->loop_model->update_where('user', $user_date,array('id'=>$order_data['m_id']));
        //更余额
        //$card_data = $this->loop_model->get_where('user_new_card',array('m_id'=>$order_data['m_id']));
//        $up['total_count'] = $card_data['total_count'] - $order_data['price_real'];
        $up['total_count'] = $card_data['total_count'] - $price;
        $res2 = $this->loop_model->update_where('user_new_card',$up,array('Id'=>$card_data['Id']));

        if ($this->db->trans_status() === TRUE) {
            $this->db->trans_commit();
            if($wx_pay_amount > 0){
               // header('location:' . site_url('/api/pay/do_pay/?order_no='.$order_data['order_no'].'&price='.$wx_pay_amount));
                error_json('pay');
            }else{
                error_json('y');
            }

        } else {
            $this->db->trans_rollback();
            error_json('信息保存失败');
            //@todo 异常处理部分
        }

    }

    /**
     * 评价
     */
    public function comment_list(){
        header('Access-Control-Allow-Origin: *');
        // 响应类型
        header('Access-Control-Allow-Methods:GET, POST, PUT,DELETE');
        // 响应头设置
        header('Access-Control-Allow-Headers:x-requested-with,content-type');
        //判断是否余额是否足够

        $post_data = $this->input->get(NULL);
        $where['order_id'] = $post_data['id'];
        $info = $this->loop_model->get_where('comment',$where);
        error_json($info);
    }

	



}
