<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Check extends CI_Controller
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
     * 待付款工单
     */
    public function unpay_list()
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
        $where_data['where_in']['a.status']  = array(3,4); //待付款订单
        $where_data['where']['a.status !=']  = 10; //已经取消的订单
        //$where_data['where'] ['a.is_del'] = 0;

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
        display('/check/unpay_list.html');
    }

    /**
     * 已付款工单
     */
    public function pay_list()
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
        $where_data['select'] = array('a.*,a.completetime as dealtime,b.name,c.job_no,c.full_name as admin_name,d.card_no,g.order_id');
        $where_data['join']   = array(
            array('service b', 'a.service_id=b.id', 'left'),
            array('admin c', 'a.people_id=c.id', 'left'),
            array('user_new_card d', 'a.m_id=d.m_id', 'left'),
            array('service f', 'b.reid=f.id', 'left'),
            array('order_callback g', 'a.id=g.order_id', 'left')
        );
        $where_data['where']['a.order_status']   = 1;
        $where_data['where']['a.status >']  = 4; //已经付款订单
        $where_data['where']['a.status !=']  = 10; //已经取消的订单
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
        $list_data = $this->loop_model->get_list('order a', $where_data, $pagesize, $pagesize * ($page - 1), 'a.fee_time desc');//列表
        //开始分页start
        $all_rows = $this->loop_model->get_list_num('order a', $where_data);//所有数量
        // var_dump($all_rows);
        assign('page_count', ceil($all_rows / $pagesize));
        //开始分页end
        assign('list', $list_data);
        display('/check/pay_list.html');
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
        $where_data['where']['a.status']  = 4; //已经安检的订单
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
        // var_dump($all_rows);
        assign('page_count', ceil($all_rows / $pagesize));
        //开始分页end
        assign('list', $list_data);
        display('/check/safe_list.html');
    }

    /**
     * 安检工作
     */
    public function safe_add($id)
    {
        $list_data = $this->loop_model->get_where('order', array('id'=>$id));
        assign('info', $list_data);
        display('/check/safe_add.html');
    }

    /**
     * 回访工单
     */
    public function phone_list()
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
            array('order a', 'a.id=g.order_id', 'left'),
            array('service b', 'a.service_id=b.id', 'left'),
            array('admin c', 'a.people_id=c.id', 'left'),
            array('user_new_card d', 'a.m_id=d.m_id', 'left'),
            array('service f', 'b.reid=f.id', 'left'),

        );

        $where_data['where']['a.order_status']   = 1;
        $where_data['where']['a.status']  = 5; //已经安检的订单
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
        $list_data = $this->loop_model->get_list('order_callback g', $where_data, $pagesize, $pagesize * ($page - 1), 'a.id desc');//列表
        foreach($list_data as $key=>$item){
            $gender = explode('/',$item['sex']);
            foreach($gender as $val){
                $data = explode('-',$val);
                $item[$data[0]] = $data[1];
            }
            $list_data[$key] = $item;
        }

        //开始分页start
        $all_rows = $this->loop_model->get_list_num('order_callback g', $where_data);//所有数量
        // var_dump($all_rows);
        assign('page_count', ceil($all_rows / $pagesize));
        //开始分页end
        assign('list', $list_data);
        display('/check/phone_list.html');
    }

    /***
     * 回访
     */

    public function call_back($id)
    {
        $where_data['select'] = array('a.*,a.worktime as dealtime,b.name as service_name,c.job_no,c.full_name as admin_name,d.card_no,d.total_count,e.id as note_id,e.note as call_note,e.addtime as call_time');
        $where_data['join']   = array(
           // array('order a', 'a.id=g.order_id', 'left'),
            array('service b', 'a.service_id=b.id', 'left'),
            array('admin c', 'a.people_id=c.id', 'left'),
            array('user_new_card d', 'a.m_id=d.m_id', 'left'),
            array('order_callback e', 'e.order_id=a.id', 'left'),

        );

        $where_data['where']['a.order_status']   = 1;
        $where_data['where']['a.id']  = $id; //已经安检的订单
        //查到数据
        $list_data = $this->loop_model->get_list('order a', $where_data, '', '', 'a.addtime desc');//列表

        $list = $list_data[0];
        $gender = explode('/',$list['sex']);
        foreach($gender as $val){
            $data = explode('-',$val);
            if($data[0] == 'gender0'){
                $sex0 = $data[1];
            }elseif($data[0] == 'gender1'){
                $sex1 = $data[1];
            }elseif($data[0] == 'gender2'){
                $sex2 = $data[1];
            }
        }
        $list['sex'] = $sex1 .' 男 '. $sex2 . ' 女 ' . $sex0 . '男女不限';
        //开始分页end
        assign('user_data', $list);
        display('/check/phone_call.html');
    }

    /**
     * 回访信息提交
     */
    public function call_submit()
    {
        if (is_post()) {
            $data_post = $this->input->post(NULL, true);
            $this->load->model('service/call_back_model');
            $res = $this->call_back_model->add($data_post, $this->admin_data);
            error_json($res);
        } else {
            error_json('提交方式错误');
        }

    }

    /**
     * 收费
     */
    public function fee_add($id)
    {
        $where_data['select'] = array('a.*,FROM_UNIXTIME(a.addtime,"%Y-%m-%d %H:%m:%s") as addtime,FROM_UNIXTIME(a.worktime,"%Y-%m-%d %H:%m:%s") as worktime,b.name,c.job_no,c.full_name as admin_name,d.total_count,d.count,d.id as card_id,d.card_no');
        $where_data['join']   = array(
            array('service b', 'a.service_id=b.id', 'left'),
            array('admin c', 'a.work_admin_id=c.id', 'left'),
            array('user_new_card d', 'a.m_id=d.m_id', 'left')
        );
        $where_data['where'] = array('a.id'=>$id);

        //查到数据
        $list_data = $this->loop_model->get_list('order a', $where_data, '', '', 'a.addtime desc');//列表
        assign('info', $list_data[0]);
        display('/check/fee_add.html');
    }

    /**
     * 修改收费信息
     */
    public function fee_edit($id)
    {
        $where_data['select'] = array('a.*,FROM_UNIXTIME(a.addtime,"%Y-%m-%d %H:%m:%s") as addtime,FROM_UNIXTIME(a.worktime,"%Y-%m-%d %H:%m:%s") as worktime,b.name,c.job_no,c.full_name as admin_name,d.total_count,d.count,d.id as card_id,d.card_no');
        $where_data['join']   = array(
            array('service b', 'a.service_id=b.id', 'left'),
            array('admin c', 'a.work_admin_id=c.id', 'left'),
            array('user_new_card d', 'a.m_id=d.m_id', 'left')
        );
        $where_data['where'] = array('a.id'=>$id);

        //查到数据
        $list_data = $this->loop_model->get_list('order a', $where_data, '', '', 'a.addtime desc');//列表
        assign('info', $list_data[0]);
        display('/check/fee_edit.html');
    }


}
