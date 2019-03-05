<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Work extends CI_Controller
{

    private $admin_data;//后台用户登录信息

    public function __construct()
    {
        parent::__construct();
        $this->load->helpers('admin_helper');
        $this->admin_data = admin_login();
        assign('admin_data', $this->admin_data);
        $this->load->model('loop_model');

        $shop_list = $this->loop_model->get_list('shop',array('where'=>array('reid'=>0)));
        assign('shop_list', $shop_list);
    }

    /**
     * 待联系列表
     */
    public function unlink($id = '')
    {
        $pagesize = 20;//分页大小
        $page     = (int)$this->input->get('per_page');
        $page <= 1 ? $page = 1 : $page = $page;//当前页数
        //搜索条件start

        //关键字
        $shop = $this->input->post('shop');
        $sub_shop = $this->input->post('sub_shop');
        $type = $this->input->post('type');
        $name = $this->input->post('name');
        $start_time = $this->input->post('start_time');
        $end_time = $this->input->post('end_time');
        if (!empty($name) && !empty($type)) $where_data['like'][$type] = $name;
        if (!empty($shop)) $where_data['where']['shop'] = $shop;
        if (!empty($sub_shop)) $where_data['where']['sub_shop'] = $sub_shop;
        if (!empty($start_time)) $where_data['where']['a.completetime >='] = strtotime($start_time);
        if (!empty($end_time)) $where_data['where']['a.completetime <=']  = strtotime($end_time);

        $where_data['select'] = array('a.*,a.completetime as dealtime,b.name,c.job_no,c.full_name as admin_name');
        $where_data['join']   = array(
            array('service b', 'a.service_id=b.id', 'left'),
            array('admin c', 'a.people_id=c.id', 'left')
        );
        $where_data['where'] ['order_status'] = 0;
        $where_data['where'] ['a.is_del'] = 0;
        //搜索条件end
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
        //开始分页start
        $all_rows = $this->loop_model->get_list_num('order a', $where_data);//所有数量
        // var_dump($all_rows);
        assign('page_count', ceil($all_rows / $pagesize));
        //开始分页end
        assign('list', $list_data);
        display('/work/unlink.html');
    }

    /**
     * 修改开单
     */
    public function edit($id)
    {
        $id = (int)$id;
        if (empty($id))error_json('订单不存在');
        $item = $this->loop_model->get_id('order',$id);
        $item['addtime'] = date("Y-m-d h:m:s",$item['addtime']);
        if($item['sex']){
            $gender = explode('/',$item['sex']);
            foreach($gender as $val){
                $data = explode('-',$val);
                $item[$data[0]] = $data[1];
            }
        }else{
            $item['gender0'] = '';
            $item['gender1'] = '';
            $item['gender2'] = '';
        }
        assign('item', $item);  //订单详情
        //商品分类
        $this->load->model('service/service_model');
        $cat_list = $this->service_model->get_all();
        assign('cat_list', $cat_list);

        $where_data['where'] = array('a.Id'=>$item['m_id']);
        // $where_data['select'] = array('u.*,a.name,a.username,a.note,FROM_UNIXTIME( u.endtime,"%Y-%m-%d") as endtime,f.prov,f.city,f.area,f.address');
        $where_data['join']   = array(
            array('user as a', 'a.Id=u.m_id', 'left'),
            //  array('user_address as f', 'u.m_id=f.m_id', 'left'),
        );
        //查到数据
        $data = $this->loop_model->get_list('user_new_card as u', $where_data, '', '', 'a.id desc');//列表
        assign('data', $data);  //客户卡消耗
        $where_data['select'] = array('a.*,FROM_UNIXTIME(a.addtime,"%Y-%m-%d %H:%m:%s") as addtime,FROM_UNIXTIME(a.completetime,"%Y-%m-%d %H:%m:%s") as dealtime,b.name,c.job_no,c.full_name as admin_name,d.card_no');
        $where_data['join']   = array(
            array('service b', 'a.service_id=b.id', 'left'),
            array('admin c', 'a.people_id=c.id', 'left'),
            array('user_new_card d', 'a.m_id=d.m_id', 'left'),
        );
        $where_data['where']   = array('a.m_id'=>$item['m_id']);
        //查到数据
        $list_data = $this->loop_model->get_list('order a', $where_data, 10, 0, 'a.addtime desc');//历史订单列表

        assign('list', $list_data);
        display('/work/edit.html');
    }

    /**
     * 删除订单
     */
    public function delete(){
        $id = (int)$this->input->post('id', true);
        if (empty($id)) error_json('id不能为空');

        $res = $this->loop_model->delete_id('order', $id);
        if (!empty($res)) {
            admin_log_insert('删除订单' . $id);
            error_json('y');
        } else {
            error_json('删除失败');
        }

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
            array('user_new_card d', 'a.m_id=d.m_id', 'left'),
            array('service f', 'b.reid=f.id', 'left')
        );
        $where_data['where']['a.order_status']   = 1;
        $where_data['where'] ['a.is_del'] = 0;
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
            array('user_new_card d', 'a.m_id=d.m_id', 'left'),
            array('service f', 'b.reid=f.id', 'left')
        );
        $where_data['where']['a.order_status']   = 1;
        $where_data['where'] ['a.is_del'] = 0;
      //  $where_data['where']['a.status >']  = 2; //已经处理过的订单
        $where_data['where']['a.pay_id']  = 0; //现金
       // $where_data['where']['a.payment_no']  = NULL; //非微信
       // $where_data['where']['a.source_id !=']  = 15; //非微信
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
            array('user_new_card d', 'a.m_id=d.m_id', 'left'),
            array('service f', 'b.reid=f.id', 'left'),
            array('user g', 'a.m_id=g.id', 'left')
        );

        $where_data['where']['a.order_status']   = 1;
     //   $where_data['where']['a.status >']  = 2; //已经处理过的订单
        $where_data['where']['g.is_member']  = 2; //会员
        $where_data['where'] ['a.is_del'] = 0;
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
            array('user_new_card d', 'a.m_id=d.m_id', 'left'),
            array('service f', 'b.reid=f.id', 'left')
        );
        $where_data['where']['a.order_status']   = 1;
      //  $where_data['where']['a.status >']  = 2; //已经处理过的订单
        $where_data['where']['r.id >']  = 0; //是固定订单
        $where_data['where'] ['a.is_del'] = 0;
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
            array('user_new_card d', 'a.m_id=d.m_id', 'left'),
            array('service f', 'b.reid=f.id', 'left')
        );

        $where_data['where']['a.order_status']   = 1;
      //  $where_data['where']['a.status >']  = 2; //已经处理过的订单
        $where_data['where']['a.source_id']  = 15; //是微信工单
        $where_data['where'] ['a.is_del'] = 0;

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
            array('user_new_card d', 'a.m_id=d.m_id', 'left')
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

    /**取消派单
     * work_delete
     */
    public function work_delete(){
        $id = (int)$this->input->post('id', true);
        $res = $this->loop_model->update_where('order',array('status'=>10),array('id'=>$id));
        if (!empty($res)) {
            error_json('y');
        } else {
            error_json('删除失败');
        }
    }
    /**
     * 保存支付
     */
    public function pay_save()
    {
        if (is_post()) {
            $data_post = $this->input->post(NULL, true);
            $this->load->model('service/order_model');
            if($data_post['pay_save']){
                $res = $this->order_model->pay_save($data_post,$this->admin_data);
            }else{
                $res = $this->order_model->new_update_work($data_post,$this->admin_data);
            }

            error_json($res);
        } else {
            error_json('提交方式错误');
        }

    }

    /**
     * 未派工单
     */
    public function unsend_list()
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
            array('user_new_card d', 'a.m_id=d.m_id', 'left'),
            array('service f', 'b.reid=f.id', 'left'),
            array('source g', 'a.source_id=g.id', 'left')
        );
        $where_data['where']['a.order_status']   = 1;
        $where_data['where']['a.status <']  = 2; //未派订单
        $where_data['where'] ['a.is_del'] = 0;

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
        display('/work/unsend.html');
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
            array('user_new_card d', 'a.m_id=d.m_id', 'left'),
            array('service f', 'b.reid=f.id', 'left'),
            array('source g', 'a.source_id=g.id', 'left')
        );
        $where_data['where']['a.order_status']   = 1;
        $where_data['where']['a.status >']  = 2; //已经处理过的订单
        $where_data['where'] ['a.is_del'] = 0;

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
     * 外派工单
     */
    public function out_list()
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
            array('user_new_card d', 'a.m_id=d.m_id', 'left'),
            array('service f', 'b.reid=f.id', 'left')
        );
        $where_data['where']['a.order_status']   = 1;
        $where_data['where']['a.status >']  = 2; //已经处理过的订单
        $where_data['where']['a.status !=']  = 10; //已经取消的订单
        $where_data['where']['a.is_send']  = 1; //已经外派的订单
        $where_data['where'] ['a.is_del'] = 0;
        // $where_data['where']['a.is_send']  = 1; //外派

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
        //开始分页start
        $all_rows = $this->loop_model->get_list_num('order a', $where_data);//所有数量
        assign('page_count', ceil($all_rows / $pagesize));
        //开始分页end
        assign('list', $list_data);
        display('/work/send_list.html');
    }


}
