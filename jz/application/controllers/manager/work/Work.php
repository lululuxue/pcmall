<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Work extends CI_Controller
{

    private $admin_data;//后台用户登录信息

    public function __construct()
    {
        parent::__construct();
        $this->load->helpers('manager_helper');
        $this->admin_data = manager_login();
        assign('admin_data', $this->admin_data);
        $this->load->model('loop_model');

        $shop_list = $this->loop_model->get_list('shop',array('where'=>array('reid'=>0)));
        assign('shop_list', $shop_list);
    }

    /**
     * 微信订单
     */
    public function wx_list()
    {
        $pagesize = 20;//分页大小
        $page     = (int)$this->input->get('per_page');
        $page <= 1 ? $page = 1 : $page = $page;//当前页数
        //关键字
        $shop = $this->input->post('shop');
        $sub_shop = $this->input->post('sub_shop');
        $type = $this->input->post('type');
        $name = $this->input->post('name');
        $start_time = $this->input->post('start_time');
        $end_time = $this->input->post('end_time');
        if (!empty($name) && !empty($type)){
            if($type == 'reid'){
                $where_data['like']['f.name'] = $name;
            }elseif($type == 'service_name'){
                $where_data['like']['b.name'] = $name;
            }else{
                $where_data['like'][$type] = $name;
            }
        }
        if (!empty($shop)) $where_data['where']['a.shop'] = $shop;
        if (!empty($sub_shop)) $where_data['where']['a.sub_shop'] = $sub_shop;
        if (!empty($start_time)) $where_data['where']['a.completetime >='] = strtotime($start_time);
        if (!empty($end_time)) $where_data['where']['a.completetime <=']  = strtotime($end_time);

        $where_data['select'] = array('a.*,a.completetime as dealtime,b.name,c.job_no,c.full_name as admin_name,d.card_no');
        $where_data['join']   = array(
            array('service b', 'a.service_id=b.id', 'left'),
            array('admin c', 'a.people_id=c.id', 'left'),
            array('user_card d', 'a.m_id=d.m_id', 'left'),
            array('service f', 'b.reid=f.id', 'left')
        );
        $where_data['where']['order_status'] = 0;//未派单
        $where_data['where']['source_id'] = 15;//微信订单
        $search_where = array(
            'name'             => $name,
            'type'             => $type,
            'shop'             => $shop,
            'sub_shop'        => $sub_shop,
            'start_time'      => $start_time,
            'end_time'        => $end_time
        );
        assign('search_where',$search_where);
        //查到数据
        $list_data = $this->loop_model->get_list('order a', $where_data, $pagesize, $pagesize * ($page - 1), 'a.id desc');//列表
        foreach($list_data as $key=>$item){
            $gender = explode('/',$item['sex']);
            foreach($gender as $val){
                $data = explode('-',$val);
                $item[$data[0]] = $data[1];
            }
            $list_data[$key] = $item;
        }

        //开始分页start
        $all_rows = $this->loop_model->get_list_num('order a', $where_data);//所有数量
        assign('page_count', ceil($all_rows / $pagesize));
        //开始分页end
        assign('list', $list_data);
        display('/work/wx_list.html');
    }

    /**
     * 全部工单
     */
    public function all_list()
    {
        $pagesize = 20;//分页大小
        $page     = (int)$this->input->get('per_page');
        $page <= 1 ? $page = 1 : $page = $page;//当前页数
        //关键字
        $shop = $this->input->post('shop');
        $sub_shop = $this->input->post('sub_shop');
        $type = $this->input->post('type');
        $name = $this->input->post('name');
        $start_time = $this->input->post('start_time');
        $end_time = $this->input->post('end_time');
        if (!empty($name) && !empty($type)){
            if($type == 'reid'){
                $where_data['like']['f.name'] = $name;
            }elseif($type == 'service_name'){
                $where_data['like']['b.name'] = $name;
            }else{
                $where_data['like'][$type] = $name;
            }
        }
        if (!empty($shop)) $where_data['where']['a.shop'] = $shop;
        if (!empty($sub_shop)) $where_data['where']['a.sub_shop'] = $sub_shop;
        if (!empty($start_time)) $where_data['where']['a.completetime >='] = strtotime($start_time);
        if (!empty($end_time)) $where_data['where']['a.completetime <=']  = strtotime($end_time);

        $where_data['select'] = array('a.*,a.completetime as dealtime,b.name,c.job_no,c.full_name as admin_name,d.card_no');
        $where_data['join']   = array(
            array('service b', 'a.service_id=b.id', 'left'),
            array('admin c', 'a.people_id=c.id', 'left'),
            array('user_card d', 'a.m_id=d.m_id', 'left'),
            array('service f', 'b.reid=f.id', 'left')
        );
        $where_data['where']['a.order_status']   = 1;
        //$where_data['where']['a.status >']  = 2; //已经处理过的订单
        $search_where = array(
            'name'             => $name,
            'type'             => $type,
            'shop'             => $shop,
            'sub_shop'        => $sub_shop,
            'start_time'      => $start_time,
            'end_time'        => $end_time
        );
        assign('search_where',$search_where);
        //查到数据
        $list_data = $this->loop_model->get_list('order a', $where_data, $pagesize, $pagesize * ($page - 1), 'a.addtime desc');//列表
        foreach($list_data as $key=>$item){
            $gender = explode('/',$item['sex']);
            foreach($gender as $val){
                $data = explode('-',$val);
                $item[$data[0]] = $data[1];
            }
            $list_data[$key] = $item;
        }

        //开始分页start
        $all_rows = $this->loop_model->get_list_num('order a', $where_data);//所有数量
        // var_dump($all_rows);
        assign('page_count', ceil($all_rows / $pagesize));
        //开始分页end
        assign('list', $list_data);
        display('/work/all_list.html');
    }

    /**
     * 现金工单
     */
    public function cash_list()
    {
        $pagesize = 20;//分页大小
        $page     = (int)$this->input->get('per_page');
        $page <= 1 ? $page = 1 : $page = $page;//当前页数
        //关键字
        $shop = $this->input->post('shop');
        $sub_shop = $this->input->post('sub_shop');
        $type = $this->input->post('type');
        $name = $this->input->post('name');
        $start_time = $this->input->post('start_time');
        $end_time = $this->input->post('end_time');
        if (!empty($name) && !empty($type)){
            if($type == 'reid'){
                $where_data['like']['f.name'] = $name;
            }elseif($type == 'service_name'){
                $where_data['like']['b.name'] = $name;
            }else{
                $where_data['like'][$type] = $name;
            }
        }
        if (!empty($shop)) $where_data['where']['a.shop'] = $shop;
        if (!empty($sub_shop)) $where_data['where']['a.sub_shop'] = $sub_shop;
        if (!empty($start_time)) $where_data['where']['a.completetime >='] = strtotime($start_time);
        if (!empty($end_time)) $where_data['where']['a.completetime <=']  = strtotime($end_time);

        $where_data['select'] = array('a.*,a.completetime as dealtime,b.name,c.job_no,c.full_name as admin_name,d.card_no');
        $where_data['join']   = array(
            array('service b', 'a.service_id=b.id', 'left'),
            array('admin c', 'a.people_id=c.id', 'left'),
            array('user_card d', 'a.m_id=d.m_id', 'left'),
            array('service f', 'b.reid=f.id', 'left')
        );
        $where_data['where']['a.order_status']   = 1;
      //  $where_data['where']['a.status >']  = 2; //已经处理过的订单
        $where_data['where']['a.pay_id ']  = NULL; //现金
        $where_data['where']['a.payment_no']  = NULL; //非微信
        $where_data['where']['a.source_id !=']  = 15; //非微信
        $search_where = array(
            'name'             => $name,
            'type'             => $type,
            'shop'             => $shop,
            'sub_shop'        => $sub_shop,
            'start_time'      => $start_time,
            'end_time'        => $end_time
        );
        assign('search_where',$search_where);
        //查到数据
        $list_data = $this->loop_model->get_list('order a', $where_data, $pagesize, $pagesize * ($page - 1), 'a.addtime desc');//列表
        foreach($list_data as $key=>$item){
            $gender = explode('/',$item['sex']);
            foreach($gender as $val){
                $data = explode('-',$val);
                $item[$data[0]] = $data[1];
            }
            $list_data[$key] = $item;
        }

        //开始分页start
        $all_rows = $this->loop_model->get_list_num('order a', $where_data);//所有数量
        // var_dump($all_rows);
        assign('page_count', ceil($all_rows / $pagesize));
        //开始分页end
        assign('list', $list_data);
        display('/work/cash_list.html');
    }

    /**
     * 会员工单
     */
    public function member_list()
    {
        $pagesize = 20;//分页大小
        $page     = (int)$this->input->get('per_page');
        $page <= 1 ? $page = 1 : $page = $page;//当前页数
        //关键字
        $shop = $this->input->post('shop');
        $sub_shop = $this->input->post('sub_shop');
        $type = $this->input->post('type');
        $name = $this->input->post('name');
        $start_time = $this->input->post('start_time');
        $end_time = $this->input->post('end_time');
        if (!empty($name) && !empty($type)){
            if($type == 'reid'){
                $where_data['like']['f.name'] = $name;
            }elseif($type == 'service_name'){
                $where_data['like']['b.name'] = $name;
            }else{
                $where_data['like'][$type] = $name;
            }
        }
        if (!empty($shop)) $where_data['where']['a.shop'] = $shop;
        if (!empty($sub_shop)) $where_data['where']['a.sub_shop'] = $sub_shop;
        if (!empty($start_time)) $where_data['where']['a.completetime >='] = strtotime($start_time);
        if (!empty($end_time)) $where_data['where']['a.completetime <=']  = strtotime($end_time);

        $where_data['select'] = array('a.*,a.completetime as dealtime,b.name,c.job_no,c.full_name as admin_name,d.card_no');
        $where_data['join']   = array(
            array('service b', 'a.service_id=b.id', 'left'),
            array('admin c', 'a.people_id=c.id', 'left'),
            array('user_card d', 'a.m_id=d.m_id', 'left'),
            array('service f', 'b.reid=f.id', 'left'),
            array('user g', 'a.m_id=g.id', 'left')
        );

        $where_data['where']['a.order_status']   = 1;
     //   $where_data['where']['a.status >']  = 2; //已经处理过的订单
        $where_data['where']['g.is_member']  = 2; //会员
        $search_where = array(
            'name'             => $name,
            'type'             => $type,
            'shop'             => $shop,
            'sub_shop'        => $sub_shop,
            'start_time'      => $start_time,
            'end_time'        => $end_time
        );
        assign('search_where',$search_where);
        //查到数据
        $list_data = $this->loop_model->get_list('order a', $where_data, $pagesize, $pagesize * ($page - 1), 'a.addtime desc');//列表
        foreach($list_data as $key=>$item){
            $gender = explode('/',$item['sex']);
            foreach($gender as $val){
                $data = explode('-',$val);
                $item[$data[0]] = $data[1];
            }
            $list_data[$key] = $item;
        }

        //开始分页start
        $all_rows = $this->loop_model->get_list_num('order a', $where_data);//所有数量
        // var_dump($all_rows);
        assign('page_count', ceil($all_rows / $pagesize));
        //开始分页end
        assign('list', $list_data);
        display('/work/member_list.html');
    }

    /**
     * 固定工单
     */
    public function gu_list()
    {
        $pagesize = 20;//分页大小
        $page     = (int)$this->input->get('per_page');
        $page <= 1 ? $page = 1 : $page = $page;//当前页数
        //关键字
        $shop = $this->input->post('shop');
        $sub_shop = $this->input->post('sub_shop');
        $type = $this->input->post('type');
        $name = $this->input->post('name');
        $start_time = $this->input->post('start_time');
        $end_time = $this->input->post('end_time');
        if (!empty($name) && !empty($type)){
            if($type == 'reid'){
                $where_data['like']['f.name'] = $name;
            }elseif($type == 'service_name'){
                $where_data['like']['b.name'] = $name;
            }else{
                $where_data['like'][$type] = $name;
            }
        }
        if (!empty($shop)) $where_data['where']['a.shop'] = $shop;
        if (!empty($sub_shop)) $where_data['where']['a.sub_shop'] = $sub_shop;
        if (!empty($start_time)) $where_data['where']['a.completetime >='] = strtotime($start_time);
        if (!empty($end_time)) $where_data['where']['a.completetime <=']  = strtotime($end_time);

        $where_data['select'] = array('a.*,a.completetime as dealtime,b.name,c.job_no,c.full_name as admin_name,d.card_no,r.id as normal_id,r.m_id');
        $where_data['join']   = array(
            array('normal_user r', 'a.m_id=r.m_id', 'left'),
            array('service b', 'a.service_id=b.id', 'left'),
            array('admin c', 'a.people_id=c.id', 'left'),
            array('user_card d', 'a.m_id=d.m_id', 'left'),
            array('service f', 'b.reid=f.id', 'left')
        );
        $where_data['where']['a.order_status']   = 1;
      //  $where_data['where']['a.status >']  = 2; //已经处理过的订单
        $where_data['where']['r.id >']  = 0; //是固定订单
        $search_where = array(
            'name'             => $name,
            'type'             => $type,
            'shop'             => $shop,
            'sub_shop'        => $sub_shop,
            'start_time'      => $start_time,
            'end_time'        => $end_time
        );
        assign('search_where',$search_where);
        //查到数据
        $list_data = $this->loop_model->get_list('order a', $where_data, $pagesize, $pagesize * ($page - 1), 'r.addtime desc');//列表
        foreach($list_data as $key=>$item){
            $gender = explode('/',$item['sex']);
            foreach($gender as $val){
                $data = explode('-',$val);
                $item[$data[0]] = $data[1];
            }
            $list_data[$key] = $item;
        }
        //开始分页start
        $all_rows = $this->loop_model->get_list_num('order a', $where_data);//所有数量

        assign('page_count', ceil($all_rows / $pagesize));
        //开始分页end
        assign('list', $list_data);
        display('/work/gu_list.html');
    }

    /**
     * 微信工单
     */
    public function wx1_list()
    {
        $pagesize = 20;//分页大小
        $page     = (int)$this->input->get('per_page');
        $page <= 1 ? $page = 1 : $page = $page;//当前页数
        //关键字
        $shop = $this->input->post('shop');
        $sub_shop = $this->input->post('sub_shop');
        $type = $this->input->post('type');
        $name = $this->input->post('name');
        $start_time = $this->input->post('start_time');
        $end_time = $this->input->post('end_time');
        if (!empty($name) && !empty($type)){
            if($type == 'reid'){
                $where_data['like']['f.name'] = $name;
            }elseif($type == 'service_name'){
                $where_data['like']['b.name'] = $name;
            }else{
                $where_data['like'][$type] = $name;
            }
        }
        if (!empty($shop)) $where_data['where']['a.shop'] = $shop;
        if (!empty($sub_shop)) $where_data['where']['a.sub_shop'] = $sub_shop;
        if (!empty($start_time)) $where_data['where']['a.completetime >='] = strtotime($start_time);
        if (!empty($end_time)) $where_data['where']['a.completetime <=']  = strtotime($end_time);

        $where_data['select'] = array('a.*,a.completetime as dealtime,b.name,c.job_no,c.full_name as admin_name,d.card_no');
        $where_data['join']   = array(
            array('service b', 'a.service_id=b.id', 'left'),
            array('admin c', 'a.people_id=c.id', 'left'),
            array('user_card d', 'a.m_id=d.m_id', 'left'),
            array('service f', 'b.reid=f.id', 'left')
        );

        $where_data['where']['a.order_status']   = 1;
      //  $where_data['where']['a.status >']  = 2; //已经处理过的订单
        $where_data['where']['a.source_id']  = 15; //是微信工单

        $search_where = array(
            'name'             => $name,
            'type'             => $type,
            'shop'             => $shop,
            'sub_shop'        => $sub_shop,
            'start_time'      => $start_time,
            'end_time'        => $end_time
        );
        assign('search_where',$search_where);
        //查到数据
        $list_data = $this->loop_model->get_list('order a', $where_data, $pagesize, $pagesize * ($page - 1), 'a.addtime desc');//列表
        foreach($list_data as $key=>$item){
            $gender = explode('/',$item['sex']);
            foreach($gender as $val){
                $data = explode('-',$val);
                $item[$data[0]] = $data[1];
            }
            $list_data[$key] = $item;
        }
        //开始分页start
        $all_rows = $this->loop_model->get_list_num('order a', $where_data);//所有数量
        assign('page_count', ceil($all_rows / $pagesize));
        //开始分页end
        assign('list', $list_data);
        display('/work/wx_list1.html');
    }

    /**
     * 派单
     */
    public function work_send($id)
    {
        //商品分类
        $where_data['select'] = array('a.*,a.completetime as dealtime,b.name,c.job_no,c.full_name as admin_name,d.card_no');
        $where_data['join']   = array(
            array('service b', 'a.service_id=b.id', 'left'),
            array('admin c', 'a.people_id=c.id', 'left'),
            array('user_card d', 'a.m_id=d.m_id', 'left')
        );
        $where_data['where'] = array('a.id'=>$id);
        $list_data_array = $this->loop_model->get_list('order a', $where_data, '', '', 'a.addtime desc');//列表
        $list_data = $list_data_array[0];

          $gender = explode('/',$list_data['sex']);
          foreach($gender as $val){
              $data = explode('-',$val);
              $list_data[$data[0]] = $data[1];
          }
        assign('info', $list_data);
        //员工列表
        $position_list = $this->loop_model->get_list('position');//列表
        assign('position_list', $position_list);
        display('/work/work_send.html');
    }


    /**
     * 保存派单
     */
    public function save()
    {
        if (is_post()) {
            $data_post = $this->input->post(NULL, true);
            $this->load->model('service/order_model');
            $res = $this->order_model->update_work($data_post,$this->admin_data);
            error_json($res);
        } else {
            error_json('提交方式错误');
        }

    }

    /**
     * 已派工单
     */
    public function send_list()
    {
        $pagesize = 20;//分页大小
        $page     = (int)$this->input->get('per_page');
        $page <= 1 ? $page = 1 : $page = $page;//当前页数
        //关键字
        $shop = $this->input->post('shop');
        $sub_shop = $this->input->post('sub_shop');
        $type = $this->input->post('type');
        $name = $this->input->post('name');
        $start_time = $this->input->post('start_time');
        $end_time = $this->input->post('end_time');
        if (!empty($name) && !empty($type)){
            if($type == 'reid'){
                $where_data['like']['f.name'] = $name;
            }elseif($type == 'service_name'){
                $where_data['like']['b.name'] = $name;
            }else{
                $where_data['like'][$type] = $name;
            }
        }
        if (!empty($shop)) $where_data['where']['a.shop'] = $shop;
        if (!empty($sub_shop)) $where_data['where']['a.sub_shop'] = $sub_shop;
        if (!empty($start_time)) $where_data['where']['a.completetime >='] = strtotime($start_time);
        if (!empty($end_time)) $where_data['where']['a.completetime <=']  = strtotime($end_time);
        $where_data['select'] = array('a.*,a.completetime as dealtime,b.name,c.job_no,c.full_name as admin_name,d.id as card_id,d.card_no,g.source_name');
        $where_data['join']   = array(
            array('service b', 'a.service_id=b.id', 'left'),
            array('admin c', 'a.people_id=c.id', 'left'),
            array('user_card d', 'a.m_id=d.m_id', 'left'),
            array('service f', 'b.reid=f.id', 'left'),
            array('source g', 'a.source_id=g.id', 'left')
        );
        $where_data['where']['a.order_status']   = 1;
        $where_data['where']['a.status >']  = 2; //已经处理过的订单

        $search_where = array(
            'name'             => $name,
            'type'             => $type,
            'shop'             => $shop,
            'sub_shop'        => $sub_shop,
            'start_time'      => $start_time,
            'end_time'        => $end_time
        );
        assign('search_where',$search_where);
        //查到数据
        $list_data = $this->loop_model->get_list('order a', $where_data, $pagesize, $pagesize * ($page - 1), 'a.addtime desc');//列表
        foreach($list_data as $key=>$item){
            if($item['sex']){
                $gender = explode('/',$item['sex']);
                foreach($gender as $val){
                    $data = explode('-',$val);
                    if($data[1]){
                        $item[$data[0]] = $data[1];
                    }else{
                        $item[$data[0]] = 0;
                    }
                }
                $list_data[$key] = $item;
            }

        }
        //开始分页start
        $all_rows = $this->loop_model->get_list_num('order a', $where_data);//所有数量
        assign('page_count', ceil($all_rows / $pagesize));
        //开始分页end
        assign('list', $list_data);
        display('/work/work_finish.html');
    }

  /**
   * 安检工单
   */

    public function safe_list()
    {
        $pagesize = 20;//分页大小
        $page     = (int)$this->input->get('per_page');
        $page <= 1 ? $page = 1 : $page = $page;//当前页数
        //关键字
        $shop = $this->input->post('shop');
        $sub_shop = $this->input->post('sub_shop');
        $type = $this->input->post('type');
        $name = $this->input->post('name');
        $start_time = $this->input->post('start_time');
        $end_time = $this->input->post('end_time');
        $safe_id = $this->input->post('safe_id');
        if (!empty($name) && !empty($type)){
            if($type == 'reid'){
                $where_data['like']['f.name'] = $name;
            }elseif($type == 'service_name'){
                $where_data['like']['b.name'] = $name;
            }else{
                $where_data['like'][$type] = $name;
            }
        }
        if (!empty($shop)) $where_data['where']['a.shop'] = $shop;
        if (!empty($sub_shop)) $where_data['where']['a.sub_shop'] = $sub_shop;
        if (!empty($start_time)) $where_data['where']['a.completetime >='] = strtotime($start_time);
        if (!empty($end_time)) $where_data['where']['a.completetime <=']  = strtotime($end_time);
        if (!empty($safe_id)){
            if($safe_id == 1){
                $where_data['where']['safe_people !='] = NULL;
            }else{
                $where_data['where']['safe_people'] = NULL;
            }
        }
        $where_data['select'] = array('a.*,a.completetime as dealtime,b.name,c.job_no,c.full_name as admin_name,d.card_no');
        $where_data['join']   = array(
            array('service b', 'a.service_id=b.id', 'left'),
            array('admin c', 'a.people_id=c.id', 'left'),
            array('user_card d', 'a.m_id=d.m_id', 'left'),
            array('service f', 'b.reid=f.id', 'left')
        );
        $where_data['where']['a.order_status']   = 1;
    //    $where_data['where']['a.status >']  = 2; //已经处理过的订单
        $where_data['where']['a.status >=']  = 3;
        $search_where = array(
            'name'             => $name,
            'type'             => $type,
            'shop'             => $shop,
            'sub_shop'        => $sub_shop,
            'safe_id'        => $safe_id,
            'start_time'      => $start_time,
            'end_time'        => $end_time
        );
        assign('search_where',$search_where);
        //查到数据
        $list_data = $this->loop_model->get_list('order a', $where_data, $pagesize, $pagesize * ($page - 1), 'a.addtime desc');//列表
        foreach($list_data as $key=>$item){
            $gender = explode('/',$item['sex']);
            foreach($gender as $val){
                $data = explode('-',$val);
                $item[$data[0]] = $data[1];
            }
            $list_data[$key] = $item;
        }

        //开始分页start
        $all_rows = $this->loop_model->get_list_num('order a', $where_data);//所有数量
        assign('page_count', ceil($all_rows / $pagesize));
        //开始分页end
        assign('list', $list_data);
        display('/work/safe_list.html');
    }

    /**
     * 安检工作
     */
    public function safe_add($id)
    {
        $list_data = $this->loop_model->get_where('order', array('id'=>$id));
        assign('info', $list_data);
        display('/work/safe_add.html');
    }

    /**
     * 收费列表
     */

    public function fee_list()
    {
        $pagesize = 20;//分页大小
        $page     = (int)$this->input->get('per_page');
        $page <= 1 ? $page = 1 : $page = $page;//当前页数
        //关键字
        $shop = $this->input->post('shop');
        $sub_shop = $this->input->post('sub_shop');
        $type = $this->input->post('type');
        $name = $this->input->post('name');
        $start_time = $this->input->post('start_time');
        $end_time = $this->input->post('end_time');
        if (!empty($name) && !empty($type)){
            if($type == 'reid'){
                $where_data['like']['f.name'] = $name;
            }elseif($type == 'service_name'){
                $where_data['like']['b.name'] = $name;
            }else{
                $where_data['like'][$type] = $name;
            }
        }
        if (!empty($shop)) $where_data['where']['a.shop'] = $shop;
        if (!empty($sub_shop)) $where_data['where']['a.sub_shop'] = $sub_shop;
        if (!empty($start_time)) $where_data['where']['a.completetime >='] = strtotime($start_time);
        if (!empty($end_time)) $where_data['where']['a.completetime <=']  = strtotime($end_time);
        $where_data['select'] = array('a.*,FROM_UNIXTIME(a.addtime,"%Y-%m-%d %H:%m:%s") as addtime,FROM_UNIXTIME(a.worktime,"%Y-%m-%d %H:%m:%s") as worktime,b.name,c.job_no,c.full_name as admin_name,d.card_no');
        $where_data['join']   = array(
            array('service b', 'a.service_id=b.id', 'left'),
            array('admin c', 'a.people_id=c.id', 'left'),
            array('user_card d', 'a.m_id=d.m_id', 'left'),
            array('service f', 'b.reid=f.id', 'left')
        );
        $where_data['where']['a.order_status']   = 1;
        $where_data['where']['a.status >']  = 3; //已经派单

        $search_where = array(
            'name'             => $name,
            'type'             => $type,
            'shop'             => $shop,
            'sub_shop'        => $sub_shop,
            'start_time'      => $start_time,
            'end_time'        => $end_time
        );
        assign('search_where',$search_where);
        //查到数据
        $list_data = $this->loop_model->get_list('order a', $where_data, $pagesize, $pagesize * ($page - 1), 'a.addtime desc');//列表
        foreach($list_data as $key=>$item){
            $gender = explode('/',$item['sex']);
            foreach($gender as $val){
                $data = explode('-',$val);
                $item[$data[0]] = $data[1];
            }
            $list_data[$key] = $item;
        }

        //开始分页start
        $all_rows = $this->loop_model->get_list_num('order a', $where_data);//所有数量
        assign('page_count', ceil($all_rows / $pagesize));
        //开始分页end
        assign('list', $list_data);
        display('/work/fee_list.html');
    }

    /**
     * 收费
     */
    public function fee_add($id)
    {
        $where_data['select'] = array('a.*,FROM_UNIXTIME(a.addtime,"%Y-%m-%d %H:%m:%s") as addtime,FROM_UNIXTIME(a.worktime,"%Y-%m-%d %H:%m:%s") as worktime,b.name,c.job_no,c.full_name as admin_name,d.people_discount,d.care_discount,d.card_no,FROM_UNIXTIME(d.endtime,"%Y-%m-%d %H:%m:%s") as endtime');
        $where_data['join']   = array(
            array('service b', 'a.service_id=b.id', 'left'),
            array('admin c', 'a.work_admin_id=c.id', 'left'),
            array('user_card d', 'a.m_id=d.m_id', 'left')
        );
        $where_data['where'] = array('a.id'=>$id);

        //查到数据
        $list_data = $this->loop_model->get_list('order a', $where_data, '', '', 'a.addtime desc');//列表
        assign('info', $list_data[0]);
        display('/work/fee_add.html');
    }

}
