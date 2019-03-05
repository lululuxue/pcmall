<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Check extends CI_Controller
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
        $where_data['where']['a.status >']  = 2; //已经处理过的订单
        $where_data['where']['a.status !=']  = 10; //已经取消的订单

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
        display('/check/all_list.html');
    }

    /**
     * 考勤
     */
    public function check($id)
    {
        //商品分类
        $where_data['select'] = array('a.*,a.completetime as dealtime,b.name,c.job_no,c.full_name as admin_name,d.Id as card_id,d.card_no,d.endtime,d.people_discount,d.care_discount,d.count,d.care_count,f.card_consume,f.care_consume');
        $where_data['join']   = array(
            array('service b', 'a.service_id=b.id', 'left'),
            array('admin c', 'a.people_id=c.id', 'left'),
            array('user_card d', 'a.m_id=d.m_id', 'left'),
            array('user_card_consume f', 'a.pay_id=f.Id', 'left')
        );
        $where_data['where'] = array('a.id'=>$id);
        $list_data_array = $this->loop_model->get_list('order a', $where_data, '', '', 'a.id desc');//列表
        $list_data = $list_data_array[0];
        assign('info', $list_data);
        display('/check/check_add.html');
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
        $where_data['where']['a.status >']  = 2; //已经处理过的订单
        $where_data['where']['a.status !=']  = 10; //已经取消的订单
        $where_data['where']['a.pay_id'] = NULL; //是现金工单
        $where_data['where']['a.payment_no'] = NULL; //是现金工单
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
        // var_dump($all_rows);
        assign('page_count', ceil($all_rows / $pagesize));
        //开始分页end
        assign('list', $list_data);
        display('/check/cash_list.html');
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
        $where_data['where']['a.status >']  = 2; //已经处理过的订单
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
        display('/check/member_list.html');
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

        $where_data['select'] = array('a.*,a.completetime as dealtime,b.name,c.job_no,c.full_name as admin_name,d.card_no');
        $where_data['join']   = array(
            array('normal_user r', 'a.m_id=r.m_id', 'left'),
            array('service b', 'a.service_id=b.id', 'left'),
            array('admin c', 'a.people_id=c.id', 'left'),
            array('user_card d', 'a.m_id=d.m_id', 'left'),
            array('service f', 'b.reid=f.id', 'left')
        );
        $where_data['where']['a.order_status']   = 1;
        $where_data['where']['a.status >']  = 2; //已经处理过的订单
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
        $list_data = $this->loop_model->get_list('order a', $where_data, $pagesize, $pagesize * ($page - 1), 'r.id desc');//列表
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
        display('/check/gu_list.html');
    }

    /**
     * 微信工单
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

        $where_data['where']['a.order_status']   = 1;
        $where_data['where']['a.status >']  = 2; //已经处理过的订单
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
        display('/check/wx_list.html');
    }

    /**
     * 工时记录
     */
    public function work_time()
    {
        $pagesize = 20;//分页大小
        $page     = (int)$this->input->get('per_page');
        $page <= 1 ? $page = 1 : $page = $page;//当前页数
        //关键字
        $shop = $this->input->post('shop');
        $sub_shop = $this->input->post('sub_shop');
        $type = $this->input->post('type');
        $name = $this->input->post('name');
        $level_id= $this->input->post('level_id');
        $start_time = $this->input->post('start_time');
        $end_time = $this->input->post('end_time');

        if (!empty($name) && !empty($type)) {
            $where_data['like'][$type] = $name;
        }
        if (!empty($shop)) $where_data['where']['shop'] = $shop;
        if (!empty($sub_shop)) $where_data['where']['sub_shop'] = $sub_shop;
        if (!empty($level_id)) $where_data['where']['level_id']  = $level_id;
        $where_data['select'] = array('*');

        //查到数据
        $list_data = $this->loop_model->get_list('position', $where_data, $pagesize, $pagesize * ($page - 1), 'id desc');//列表
        //获取员工的具体工作情况
        foreach($list_data as $k=>$v){
            if (!empty($start_time)){
                $start = $start_time;
            } else{
                $start = date('Y-m',time());
            }
            if (!empty($end_time)){
                $end = $end_time;
            }else{
                $end = date('Y-m',time());
            }
            $where['where']['position_id']= $v['id'];
            $where['where']['is_work']= 1 ;       //工作
            $where['select']= array('interval_time,count(id) as day,sum(over_time) as over_time,sum(add_money) as add_money,sum(reduce_money) as reduce_money');
            //计算工时
            $j = 0;
            for($i = strtotime($start) ; $i <= strtotime($end) ; $i = strtotime("+1 month",$i)){
                $where['where']['date'] = date('Y-m',$i);
                $work_day = $this->loop_model->get_group_list('position_check', $where, '', '', 'interval_time');//列表
                //计算该月份工时
                if($work_day){
                    $work_detail[$j]['date']= date('Y-m',$i);
                    $work_detail[$j]['work_time']= 0;
                    $work_detail[$j]['over_time']= 0;
                    $work_detail[$j]['add_money']= 0;
                    $work_detail[$j]['reduce_money']= 0;
                    foreach($work_day as $val){
                        if($val['interval_time'] == '上午'){
                            $work_detail[$j]['work_time'] += $val['day'] * 3 ;   //上午三小时
                            $work_detail[$j]['over_time'] += $val['over_time'];   //加班
                            $work_detail[$j]['add_money'] += $val['add_money'];   //奖励
                            $work_detail[$j]['reduce_money'] += $val['reduce_money'];   //违纪
                        }else{
                            $work_detail[$j]['work_time'] += $val['day'] * 4 ;    //下午四个小时
                            $work_detail[$j]['over_time'] += $val['over_time'];   //加班
                            $work_detail[$j]['add_money'] += $val['add_money'];   //奖励
                            $work_detail[$j]['reduce_money'] += $val['reduce_money'];   //违纪
                        }
                    }
                }else{
                    $work_detail[$j]['date']= date('Y-m',$i);
                    $work_detail[$j]['work_time']= 0;
                    $work_detail[$j]['over_time']= 0;
                    $work_detail[$j]['add_money']= 0;
                    $work_detail[$j]['reduce_money']= 0;
                }
                //获取提成（暂时未完成）
                //获取承包费（暂时未完成）
                $j++;
            }
            $v['work_detail']= $work_detail;
            $list_data[$k]= $v;
        }
        //搜索条件end
        $search_where = array(
            'name'             => $name,
            'type'             => $type,
            'shop'             => $shop,
            'sub_shop'        => $sub_shop,
            'level_id'          => $level_id,
            'start_time'     => $start_time,
            'end_time'       => $end_time,
        );
        assign('search_where',$search_where);
        //开始分页start
        $all_rows = $this->loop_model->get_list_num('position', $where_data);//所有数量
        assign('page_count', ceil($all_rows / $pagesize));
        //开始分页end
        assign('list', $list_data);
        display('/check/work_time.html');
    }


    /**
     * 日常考勤
     */
    public function day_work()
    {
        $pagesize = 20;//分页大小
        $page     = (int)$this->input->get('per_page');
        $page <= 1 ? $page = 1 : $page = $page;//当前页数
        $date_list = $this->loop_model->get_group_list('position_check',array('select'=>'date'),'','','date','date desc');
        $today = array('date'=>date('Y-m',time()));
        if(!in_array($today,$date_list)){
            $date_list[] = $today;
        }
        assign('date_list',$date_list);

        //关键字
        $shop = $this->input->post('shop');
        $sub_shop = $this->input->post('sub_shop');
        $name = $this->input->post('name');
        $date = $this->input->post('date');
        $cat_id = $this->input->post('cat_id');
        $job = $this->input->post('job');
        $department_id = $this->input->post('department_id');
        if (empty($date)) $date  = $date_list[0]['date'];

        if (!empty(name)) $where_data['like']['name'] = $name;
        if (!empty($shop)) $where_data['where']['shop'] = $shop;
        if (!empty($sub_shop)) $where_data['where']['sub_shop'] = $sub_shop;
        if (!empty($cat_id)) $where_data['where']['cat_id']  = $cat_id;
        if (!empty($job)) $where_data['where']['job']  = $job;
        if (!empty($department_id)) $where_data['where']['department_id']  = $department_id;
        $where_data['select'] = array('id,name');
        //搜索条件end
        $search_where = array(
            'name'             => $name,
            'shop'             => $shop,
            'sub_shop'        => $sub_shop,
            'date'             => $date,
            'cat_id'          => $cat_id,
            'job'             => $job,
            'department_id' => $department_id,
        );
        assign('search_where',$search_where);
        //查到数据
        $list_data = $this->loop_model->get_list('position', $where_data, $pagesize, $pagesize * ($page - 1), 'id desc');//列表
        foreach($list_data as $k=>$val){
            $val['list'] = $this->loop_model->get_list('position_check',array('where'=>array('position_id'=>$val['id'],'date'=>$date)),'', '', 'day asc');//列表
            $start = strtotime($date);
            $end = (strtotime('+1 months',strtotime($date)));
            $j = 1;
            for($i = $start ; $i < $end ; $i = $i + 24*3600){
                $morning = ['position_id'=>$val['id'],'date'=>$date,'day'=>$j,'is_work'=>0,'over_time'=>'','add_money'=>'','reduce_money'=>'','addtime'=>'','interval_time'=>'上午'];
                $after = ['position_id'=>$val['id'],'date'=>$date,'day'=>$j,'is_work'=>0,'over_time'=>'','add_money'=>'','reduce_money'=>'','addtime'=>'','interval_time'=>'上午'];
                $list['morning'][$j-1] = $morning;
                $list['after'][$j-1] = $after;
             foreach($val['list'] as $v){
                    if(($v['day'] == $j && $v['interval_time'] == '上午')){
                        $list['morning'][$j-1] = $v;
                    }elseif(($v['day'] == $j && $v['interval_time'] == '下午')){
                        $list['after'][$j-1] = $v;
                    }
                }
                $j++;

            }
        $list_data[$k]['list'] = $list;
        }
        //开始分页start
        $all_rows = $this->loop_model->get_list_num('position', $where_data);//所有数量
        assign('page_count', ceil($all_rows / $pagesize));
        //开始分页end
        assign('list', $list_data);

        //获取职务列表
        $desc_list = $this->loop_model->get_list('job_desc',array('where'=>array('status'=>0)),'','','id desc');
        assign('desc_list',$desc_list);

        //获取部门列表
        $department_list = $this->loop_model->get_list('department',array('where'=>array('status'=>0)),'','','id desc');
        assign('department_list',$department_list);
        display('/check/day_work.html');
    }

    /**
     * 每日考勤
     */
    public function day_check()
    {
        $id = $this->input->get('id');
        $date = $this->input->get('date');
        $info = $this->loop_model->get_where('position',array('id'=>$id) );
        assign('info', $info);
        //获取考勤

        $user_list= $this->loop_model->get_list('position_check',array('where'=>array('position_id'=>$id,'date'=>$date)),'', '', 'day asc');//列表
        $start = strtotime($date);
        $end = (strtotime('+1 months',strtotime($date)));
        $j = 1;
        for($i = $start ; $i < $end ; $i = $i + 24*3600){
            $morning = ['position_id'=>$id,'date'=>$date,'day'=>$j,'is_work'=>0,'over_time'=>'','add_money'=>'','reduce_money'=>'','addtime'=>'','interval_time'=>'上午'];
            $after = ['position_id'=>$id,'date'=>$date,'day'=>$j,'is_work'=>0,'over_time'=>'','add_money'=>'','reduce_money'=>'','addtime'=>'','interval_time'=>'下午'];
            $list[$j-1]['morning'] = $morning;
            $list[$j-1]['after'] = $after;
            foreach($user_list as $v){
                if(($v['day'] == $j && $v['interval_time'] == '上午')){
                    $list[$j-1]['morning'] = $v;
                }elseif(($v['day'] == $j && $v['interval_time'] == '下午')){
                    $list[$j-1]['after'] = $v;
                }
            }
            $j++;
        }
        assign('list', $list);

        display('/check/day_check.html');
    }
    /**
     * 考勤提交
     */
    public function save()
    {
        $post_data = $this->input->post(NULL,true);
        foreach($post_data['date'] as $k=>$v){
            $post_data['addtime'][$k] = time();
        }
        $all_data = [];
        foreach($post_data as $key=>$val){
            foreach($val as $k=>$v){
                $all_data[$k][$key] = $val[$k];
            }
        }
        //判断是否有重复的数据
        $list['date'] = $post_data['date'];
        $list['day'] = $post_data['day'];
        $list['interval_time'] = $post_data['interval_time'];
        $new_data = [];
        foreach($list as $key=>$val){
            foreach($val as $k=>$v){
                $new_data[$k][] = $val[$k];
            }
        }
        $temp = [];
        //降维
        foreach($new_data as $v){
            $list1 = join(",",$v); //降维,也可以用implode,将一维数组转换为用逗号连接的字符串
            $temp[] = $list1;
        }
        if(count($new_data) != count(array_unique($temp)) ){
            error_json('数据重复');
        }
        //判断是否修改
        if($post_data['id'][0]){
            //开启事物
            //$this->db->
            foreach($all_data as $v){
                $res = $this->loop_model->update_where('position_check',$v,array('id'=>$v['id']));
            }
        }else{
            $res = $this->loop_model->insert('position_check',$all_data,true);
        }
        if($res >= 0){
            error_json('y');
        }else{
            error_json('保存失败');
        }

    }

    /**
     * 工资
     */
    public function salary()
    {
        $pagesize = 20;//分页大小
        $page     = (int)$this->input->get('per_page');
        $page <= 1 ? $page = 1 : $page = $page;//当前页数
        //关键字
        $shop = $this->input->post('shop');
        $sub_shop = $this->input->post('sub_shop');
        $department_id = $this->input->post('department_id');
        $job_id = $this->input->post('job_id');
        $job= $this->input->post('job');
        $date = $this->input->post('date');
        $name = $this->input->post('name');

        if (!empty($name)) {
            $where_data['like']['a.name'] = $name;
            $where_data['or_like']['a.position_no'] = $name;
        }
        if (!empty($shop)) $where_data['where']['a.shop'] = $shop;
        if (!empty($sub_shop)) $where_data['where']['a.sub_shop'] = $sub_shop;
        if (!empty($department_id)) $where_data['where']['a.department_id']  = $department_id;
        if (!empty($job_id)) $where_data['where']['a.job_id']  = $job_id;
        if (!empty($job)) $where_data['where']['a.job']  = $job;
        $where_data['select'] = array('a.*,b.job_name,b.base_salary');
        $where_data['join'] = array(
            array('job b','b.id = a.job_id','left')
        );
        //查到数据
        $list_data = $this->loop_model->get_list('position a', $where_data, $pagesize, $pagesize * ($page - 1), 'a.id desc');//列表
        //获取员工的具体工作情况
        foreach($list_data as $k=>$v){

            $where['where']['position_id']= $v['id'];
            $where['where']['is_work']= 1 ;       //工作
            $where['select']= array('interval_time,count(id) as day,sum(over_time) as over_time,sum(add_money) as add_money,sum(reduce_money) as reduce_money');
            //计算工时
            if (empty($date)) $date = date("Y-m",time());
            $where['where']['date'] = $date;
            $work_day = $this->loop_model->get_group_list('position_check', $where, '', '', 'interval_time');//列表
            //计算该月份工时
            if($work_day){
                $work_detail['date']= $date;
                $work_detail['work_time']= 0;
                $work_detail['over_time']= 0;
                $work_detail['add_money']= 0;
                $work_detail['reduce_money']= 0;
                $work_detail['salary']= 0;
                foreach($work_day as $val){
                    if($val['interval_time'] == '上午'){
                        $work_detail['work_time'] += $val['day'] * 4 ;   //上午四小时
                        $work_detail['over_time'] += $val['over_time'];   //加班
                        $work_detail['add_money'] += $val['add_money'];   //奖励
                        $work_detail['reduce_money'] += $val['reduce_money'];   //违纪
                    }else{
                        $work_detail['work_time'] += $val['day'] * 4 ;    //下午四个小时
                        $work_detail['over_time'] += $val['over_time'];   //加班
                        $work_detail['add_money'] += $val['add_money'];   //奖励
                        $work_detail['reduce_money'] += $val['reduce_money'];   //违纪
                    }
                }
                //计算工资
                $day = (strtotime("+1 month",strtotime($date))- strtotime($date))/(24*3600);
                $work_detail['salary']= round($v['base_salary']/$day*$work_detail['work_time']/8,2);//保留两位小数
                $basic = $work_detail['salary'];//基本工资
                $over = $work_detail['over_time']*$v['hour_money'];//加班工资
                $work_detail['total_salary']= $basic + $over + $work_detail['add_money'] ;//应发工资
                $work_detail['real_salary']= $basic + $over + $work_detail['add_money'] - $work_detail['reduce_money'] ;//实发工资
            }else{
                $work_detail['date']= $date;
                $work_detail['work_time']= 0;
                $work_detail['over_time']= 0;
                $work_detail['add_money']= 0;
                $work_detail['reduce_money']= 0;
                $work_detail['salary']= 0;
                $work_detail['total_salary'] = 0;
                $work_detail['real_salary'] = 0;
            }
            //获取提成（暂时未完成）
            //获取承包费（暂时未完成）

            $v['work_detail']= $work_detail;
            $list_data[$k]= $v;
        }
        //搜索条件end
        $search_where = array(
            'name'             => $name,
            'department_id'  => $department_id,
            'shop'             => $shop,
            'sub_shop'        => $sub_shop,
            'job_id'          => $job_id,
            'date'       => $date,
            'job'       => $job
        );
        assign('search_where',$search_where);
        //开始分页start

        $all_rows = $this->loop_model->get_list_num('position a', $where_data);//所有数量
        assign('page_count', ceil($all_rows / $pagesize));
        //开始分页end
        assign('list', $list_data); //error_json($list_data);

        //获取职务列表
        $desc_list = $this->loop_model->get_list('job_desc',array('where'=>array('status'=>0)),'','','id desc');
        assign('desc_list',$desc_list);
        //获取职位列表
        $job_list = $this->loop_model->get_list('job',array('where'=>array('status'=>0)),'','','id desc');
        assign('job_list',$job_list);
        //获取部门列表
        $department_list = $this->loop_model->get_list('department',array('where'=>array('status'=>0)),'','','id desc');
        assign('department_list',$department_list);
        display('/check/salary.html');
    }

    /**
     * 工资审核
     */
    public function salary_produce()
    {
        $pagesize = 20;//分页大小
        $page     = (int)$this->input->get('per_page');
        $page <= 1 ? $page = 1 : $page = $page;//当前页数
        //关键字
        $shop = $this->input->post('shop');
        $sub_shop = $this->input->post('sub_shop');
        $department_id = $this->input->post('department_id');
        $job_id = $this->input->post('job_id');
        $job= $this->input->post('job');
        $date = $this->input->post('date');
        $name = $this->input->post('name');

        //查找数据
        if (empty($date)) $date = date("Y-m",time());
        $data= $this->loop_model->get_where('salary ', array('date'=>$date));//列表
        if($data){
            //搜索条件end
            $search_where = array(
                'name'             => $name,
                'department_id'  => $department_id,
                'shop'             => $shop,
                'sub_shop'        => $sub_shop,
                'job_id'          => $job_id,
                'date'       => $date,
                'job'       => $job
            );
            assign('search_where',$search_where);
            assign('list', ''); //error_json($list_data);
            display('/check/salary_produce.html');
            return ;
        }

        if (!empty($name)) {
            $where_data['like']['a.name'] = $name;
            $where_data['or_like']['a.position_no'] = $name;
        }
        if (!empty($shop)) $where_data['where']['a.shop'] = $shop;
        if (!empty($sub_shop)) $where_data['where']['a.sub_shop'] = $sub_shop;
        if (!empty($department_id)) $where_data['where']['a.department_id']  = $department_id;
        if (!empty($job_id)) $where_data['where']['a.job_id']  = $job_id;
        if (!empty($job)) $where_data['where']['a.job']  = $job;
        $where_data['select'] = array('a.*,b.id as job_id,b.job_name,b.base_salary');
        $where_data['join'] = array(
            array('job b','b.id = a.job_id','left')
        );
        //查到数据
        $list_data = $this->loop_model->get_list('position a', $where_data, $pagesize, $pagesize * ($page - 1), 'a.id desc');//列表
        //获取员工的具体工作情况
        foreach($list_data as $k=>$v){

            $where['where']['position_id']= $v['id'];
            $where['where']['is_work']= 1 ;       //工作
            $where['select']= array('interval_time,count(id) as day,sum(over_time) as over_time,sum(add_money) as add_money,sum(reduce_money) as reduce_money');
            //计算工时
            $where['where']['date'] = $date;
            $work_day = $this->loop_model->get_group_list('position_check', $where, '', '', 'interval_time');//列表
            //计算该月份工时
            if($work_day){
                $work_detail['date']= $date;
                $work_detail['work_time']= 0;
                $work_detail['over_time']= 0;
                $work_detail['add_money']= 0;
                $work_detail['reduce_money']= 0;
                $work_detail['salary']= 0;
                foreach($work_day as $val){
                    if($val['interval_time'] == '上午'){
                        $work_detail['work_time'] += $val['day'] * 4 ;   //上午四小时
                        $work_detail['over_time'] += $val['over_time'];   //加班
                        $work_detail['add_money'] += $val['add_money'];   //奖励
                        $work_detail['reduce_money'] += $val['reduce_money'];   //违纪
                    }else{
                        $work_detail['work_time'] += $val['day'] * 4 ;    //下午四个小时
                        $work_detail['over_time'] += $val['over_time'];   //加班
                        $work_detail['add_money'] += $val['add_money'];   //奖励
                        $work_detail['reduce_money'] += $val['reduce_money'];   //违纪
                    }
                }
                //计算工资
                $day = (strtotime("+1 month",strtotime($date))- strtotime($date))/(24*3600);
                $work_detail['salary']= round($v['base_salary']/$day*$work_detail['work_time']/8,2);//保留两位小数
                $basic = $work_detail['salary'];//基本工资
                $over = $work_detail['over_time']*$v['hour_money'];//加班工资
                $work_detail['total_salary']= $basic + $over + $work_detail['add_money'] ;//应发工资
                $work_detail['real_salary']= $basic + $over + $work_detail['add_money'] - $work_detail['reduce_money'] ;//实发工资
            }else{
                $work_detail['date']= $date;
                $work_detail['work_time']= 0;
                $work_detail['over_time']= 0;
                $work_detail['add_money']= 0;
                $work_detail['reduce_money']= 0;
                $work_detail['salary']= 0;
                $work_detail['total_salary'] = 0;
                $work_detail['real_salary'] = 0;
            }
            //获取提成（暂时未完成）
            //获取承包费（暂时未完成）

            $v['work_detail']= $work_detail;
            $list_data[$k]= $v;
        }
        //搜索条件end
        $search_where = array(
            'name'             => $name,
            'department_id'  => $department_id,
            'shop'             => $shop,
            'sub_shop'        => $sub_shop,
            'job_id'          => $job_id,
            'date'       => $date,
            'job'       => $job
        );
        assign('search_where',$search_where);
        //开始分页start

        $all_rows = $this->loop_model->get_list_num('position a', $where_data);//所有数量
        assign('page_count', ceil($all_rows / $pagesize));
        //开始分页end
        assign('list', $list_data); //error_json($list_data);
        //获取职务列表
        $desc_list = $this->loop_model->get_list('job_desc',array('where'=>array('status'=>0)),'','','id desc');
        assign('desc_list',$desc_list);
        //获取职位列表
        $job_list = $this->loop_model->get_list('job',array('where'=>array('status'=>0)),'','','id desc');
        assign('job_list',$job_list);
        //获取部门列表
        $department_list = $this->loop_model->get_list('department',array('where'=>array('status'=>0)),'','','id desc');
        assign('department_list',$department_list);
        display('/check/salary_produce.html');
    }


    public function salary_check()
    {
        $pagesize = 20;//分页大小
        $page     = (int)$this->input->get('per_page');
        $page <= 1 ? $page = 1 : $page = $page;//当前页数
        //关键字
        $shop = $this->input->post('shop');
        $sub_shop = $this->input->post('sub_shop');
        $department_id = $this->input->post('department_id');
        $job_id = $this->input->post('job_id');
        $job = $this->input->post('job');
        $status= $this->input->post('status');
        $date = $this->input->post('date');
        $name = $this->input->post('name');
        if (empty($date)) $date = date("Y-m",time());

        if (!empty($name)) {
            $where_data['like']['a.name'] = $name;
            $where_data['or_like']['a.position_no'] = $name;
        }
        if (!empty($shop)) $where_data['where']['b.shop'] = $shop;
        if (!empty($sub_shop)) $where_data['where']['b.sub_shop'] = $sub_shop;
        if (!empty($department_id)) $where_data['where']['b.department_id']  = $department_id;
        if (!empty($job_id)) $where_data['where']['b.job_id']  = $job_id;
        if (!empty($job)) $where_data['where']['b.job']  = $job;
        if (!empty($status)) $where_data['where']['a.status']  = $status;
        $where_data['where']['a.date'] = $date;
        $where_data['select'] = array('a.*,b.name');
        $where_data['join'] = array(
            array('position b','a.position_id=b.id','left')
        );
        //查到数据
        $list_data = $this->loop_model->get_list('salary a', $where_data, $pagesize, $pagesize * ($page - 1), 'id desc');//列表

        //搜索条件end
        $search_where = array(
            'name'             => $name,
            'department_id'  => $department_id,
            'shop'             => $shop,
            'sub_shop'        => $sub_shop,
            'job_id'          => $job_id,
            'date'       => $date,
            'job'       =>$job,
            'status'       => $status
        );
        assign('search_where',$search_where);
        //开始分页start

        $all_rows = $this->loop_model->get_list_num('salary a', $where_data);//所有数量
        assign('page_count', ceil($all_rows / $pagesize));
        //开始分页end
        assign('list', $list_data); //error_json($list_data);
        //获取部门列表
        $department_list = $this->loop_model->get_list('department',array('where'=>array('status'=>0)),'','','id desc');
        assign('department_list',$department_list);
        //获取职位列表
        $job_list = $this->loop_model->get_list('job',array('where'=>array('status'=>0)),'','','id desc');
        assign('job_list',$job_list);
        display('/check/salary_check.html');
    }

    public function salary_check_log()
    {
        $pagesize = 20;//分页大小
        $page     = (int)$this->input->get('per_page');
        $page <= 1 ? $page = 1 : $page = $page;//当前页数
        //关键字
        $shop = $this->input->post('shop');
        $sub_shop = $this->input->post('sub_shop');
        $department_id = $this->input->post('department_id');
        $job_id = $this->input->post('job_id');
        $job= $this->input->post('job');
        $status= $this->input->post('status');
        $date = $this->input->post('date');
        $name = $this->input->post('name');
        if (empty($date)) $date = date("Y-m",time());

        if (!empty($name)) {
            $where_data['like']['a.name'] = $name;
            $where_data['or_like']['a.position_no'] = $name;
        }
        if (!empty($shop)) $where_data['where']['b.shop'] = $shop;
        if (!empty($sub_shop)) $where_data['where']['b.sub_shop'] = $sub_shop;
        if (!empty($department_id)) $where_data['where']['b.department_id']  = $department_id;
        if (!empty($job_id)) $where_data['where']['b.job_id']  = $job_id;
        if (!empty($job)) $where_data['where']['b.job']  = $job;
        if (!empty($status)) $where_data['where']['a.status']  = $status;
        $where_data['where']['a.date'] = $date;
        $where_data['select'] = array('a.*,b.name');
        $where_data['join'] = array(
            array('position b','a.position_id=b.id','left')
        );
        //查到数据
        $list_data = $this->loop_model->get_list('salary a', $where_data, $pagesize, $pagesize * ($page - 1), 'id desc');//列表

        //搜索条件end
        $search_where = array(
            'name'             => $name,
            'department_id'  => $department_id,
            'shop'             => $shop,
            'sub_shop'        => $sub_shop,
            'job_id'          => $job_id,
            'date'             => $date,
            'status'        => $status,
            'job'           =>$job
        );
        assign('search_where',$search_where);
        //开始分页start

        $all_rows = $this->loop_model->get_list_num('salary a', $where_data);//所有数量
        assign('page_count', ceil($all_rows / $pagesize));
        //开始分页end
        assign('list', $list_data); //error_json($list_data);
        //获取部门列表
        $department_list = $this->loop_model->get_list('department',array('where'=>array('status'=>0)),'','','id desc');
        assign('department_list',$department_list);
        //获取职位列表
        $job_list = $this->loop_model->get_list('job',array('where'=>array('status'=>0)),'','','id desc');
        assign('job_list',$job_list);
        display('/check/salary_check_log.html');
    }

    //审核
    public function salary_check_verify()
    {
        //获取信息
        $post_data = $this->input->post(NULL, true);
        foreach($post_data['date'] as $k=>$v){
            $post_data['addtime'][$k] = time();
        }
        $all_data = [];
        foreach($post_data as $key=>$val){
            foreach($val as $k=>$v){
                $all_data[$k][$key] = $val[$k];
            }
        }
        //判断是否有重复的数据
        $list['date'] = $post_data['date'];
        $list['position_id'] = $post_data['position_id'];
        $new_data = [];
        foreach($list as $key=>$val){
            foreach($val as $k=>$v){
                $new_data[$k][] = $val[$k];
            }
        }
        $temp = [];
        //降维
        foreach($new_data as $v){
            $list1 = join(",",$v); //降维,也可以用implode,将一维数组转换为用逗号连接的字符串
            $temp[] = $list1;
        }
        if(count($new_data) != count(array_unique($temp)) ){
            error_json('已经审核过了');
        }
        //判断是否修改
        if($post_data['id'][0]){
            //开启事物
            //$this->db->
            foreach($all_data as $v){
                $res = $this->loop_model->update_where('salary',$v,array('id'=>$v['id']));
            }
        }else{
            $res = $this->loop_model->insert('salary',$all_data,true);
        }
        if($res >= 0){
            error_json('y');
        }else{
            error_json('保存失败');
        }

    }

    /**
     * 账号打印
     */
    public function account()
    {
        $pagesize = 20;//分页大小
        $page     = (int)$this->input->get('per_page');
        $page <= 1 ? $page = 1 : $page = $page;//当前页数
        //关键字
        $shop = $this->input->post('shop');
        $sub_shop = $this->input->post('sub_shop');
        $department_id = $this->input->post('department_id');
        $job_id = $this->input->post('job_id');
        $date = $this->input->post('date');
        $name = $this->input->post('name');

        if (!empty($name)) {
            $where_data['like']['a.name'] = $name;
            $where_data['or_like']['a.position_no'] = $name;
        }
        if (!empty($shop)) $where_data['where']['a.shop'] = $shop;
        if (!empty($sub_shop)) $where_data['where']['a.sub_shop'] = $sub_shop;
        if (!empty($department_id)) $where_data['where']['a.department_id']  = $department_id;
        if (!empty($job_id)) $where_data['where']['a.job_id']  = $job_id;
        $where_data['select'] = array('a.*,b.job_name,b.base_salary');
        $where_data['join'] = array(
            array('job b','b.id = a.job_id','left')
        );
        //查到数据
        $list_data = $this->loop_model->get_list('position a', $where_data, $pagesize, $pagesize * ($page - 1), 'a.id desc');//列表
        //获取员工的工资
        foreach($list_data as $k=>$v){

        }
        //搜索条件end
        $search_where = array(
            'name'             => $name,
            'department_id'  => $department_id,
            'shop'             => $shop,
            'sub_shop'        => $sub_shop,
            'job_id'          => $job_id,
            'date'       => $date,
        );
        assign('search_where',$search_where);
        //开始分页start

        $all_rows = $this->loop_model->get_list_num('position a', $where_data);//所有数量
        assign('page_count', ceil($all_rows / $pagesize));
        //开始分页end
        assign('list', $list_data); //error_json($list_data);
        //获取部门列表
        $department_list = $this->loop_model->get_list('department',array('where'=>array('status'=>0)),'','','id desc');
        assign('department_list',$department_list);
        //获取职位列表
        $job_list = $this->loop_model->get_list('job',array('where'=>array('status'=>0)),'','','id desc');
        assign('job_list',$job_list);
        display('/check/account_list.html');
    }

    /**
     * 工资历史记录
     */
    public function salary_user_list()
    {
        $pagesize = 20;//分页大小
        $page     = (int)$this->input->get('per_page');
        $page <= 1 ? $page = 1 : $page = $page;//当前页数
        //关键字
        $shop = $this->input->post('shop');
        $sub_shop = $this->input->post('sub_shop');
        $type = $this->input->post('type');
        $name = $this->input->post('name');
        $level_id= $this->input->post('level_id');
        $start_time = $this->input->post('start_time');
        $end_time = $this->input->post('end_time');
        $department_id = $this->input->post('department_id');
        $job_id = $this->input->post('job_id');
        $department_id = $this->input->post('department_id');
        $job = $this->input->post('job');

        if (!empty($name) && !empty($type)) {
            $where_data['like'][$type] = $name;
        }
        if (!empty($shop)) $where_data['where']['shop'] = $shop;
        if (!empty($sub_shop)) $where_data['where']['sub_shop'] = $sub_shop;
        if (!empty($level_id)) $where_data['where']['level_id']  = $level_id;
        if (!empty($department_id)) $where_data['where']['department_id']  = $department_id;
        if (!empty($job_id)) $where_data['where']['job_id']  = $job_id;
        if (!empty($job)) $where_data['where']['job']  = $job;
        $where_data['select'] = array('*');

        //查到数据
        $list_data = $this->loop_model->get_list('position', $where_data, $pagesize, $pagesize * ($page - 1), 'id desc');//列表
        //获取员工的具体工作情况
        foreach($list_data as $k=>$v){
            if (!empty($start_time)){
                $start = $start_time;
            } else{
                $start = date('Y-m',time());
            }
            if (!empty($end_time)){
                $end = $end_time;
            }else{
                $end = date('Y-m',time());
            }
            $where['where']['position_id']= $v['id'];
            $where['where']['is_work']= 1 ;       //工作
            $where['select']= array('interval_time,count(id) as day,sum(over_time) as over_time,sum(add_money) as add_money,sum(reduce_money) as reduce_money');
            //计算工时
            $j = 0;
            for($i = strtotime($start) ; $i <= strtotime($end) ; $i = strtotime("+1 month",$i)){
                $where['where']['date'] = date('Y-m',$i);
                $work_day = $this->loop_model->get_group_list('position_check', $where, '', '', 'interval_time');//列表
                //计算该月份工时
                if($work_day){
                    $work_detail[$j]['date']= date('Y-m',$i);
                    $work_detail[$j]['work_time']= 0;
                    $work_detail[$j]['over_time']= 0;
                    $work_detail[$j]['add_money']= 0;
                    $work_detail[$j]['reduce_money']= 0;
                    foreach($work_day as $val){
                        if($val['interval_time'] == '上午'){
                            $work_detail[$j]['work_time'] += $val['day'] * 3 ;   //上午三小时
                            $work_detail[$j]['over_time'] += $val['over_time'];   //加班
                            $work_detail[$j]['add_money'] += $val['add_money'];   //奖励
                            $work_detail[$j]['reduce_money'] += $val['reduce_money'];   //违纪
                        }else{
                            $work_detail[$j]['work_time'] += $val['day'] * 4 ;    //下午四个小时
                            $work_detail[$j]['over_time'] += $val['over_time'];   //加班
                            $work_detail[$j]['add_money'] += $val['add_money'];   //奖励
                            $work_detail[$j]['reduce_money'] += $val['reduce_money'];   //违纪
                        }
                    }
                }else{
                    $work_detail[$j]['date']= date('Y-m',$i);
                    $work_detail[$j]['work_time']= 0;
                    $work_detail[$j]['over_time']= 0;
                    $work_detail[$j]['add_money']= 0;
                    $work_detail[$j]['reduce_money']= 0;
                }
                //获取提成（暂时未完成）
                //获取承包费（暂时未完成）
                $j++;
            }
            $v['work_detail']= $work_detail;
            $list_data[$k]= $v;
        }
        //搜索条件end
        $search_where = array(
            'name'             => $name,
            'type'             => $type,
            'shop'             => $shop,
            'sub_shop'        => $sub_shop,
            'level_id'          => $level_id,
            'start_time'     => $start_time,
            'end_time'       => $end_time,
            'department_id' => $department_id,
            'job_id'         => $job_id,
            'job'             => $job,
        );
        assign('search_where',$search_where);
        //开始分页start
        $all_rows = $this->loop_model->get_list_num('position', $where_data);//所有数量
        assign('page_count', ceil($all_rows / $pagesize));
        //开始分页end
        assign('list', $list_data);
        //获取部门列表
        $department_list = $this->loop_model->get_list('department',array('where'=>array('status'=>0)),'','','id desc');
        assign('department_list',$department_list);
        //获取职位列表
        $job_list = $this->loop_model->get_list('job',array('where'=>array('status'=>0)),'','','id desc');
        assign('job_list',$job_list);
        //获取职务列表
        $desc_list = $this->loop_model->get_list('job_desc',array('where'=>array('status'=>0)),'','','id desc');
        assign('desc_list',$desc_list);
        display('/check/salary_user_list.html');
    }

    /**
     * 外派工单
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
        $where_data['select'] = array('a.*,a.completetime as dealtime,b.name,c.job_no,c.full_name as admin_name,d.card_no');
        $where_data['join']   = array(
            array('service b', 'a.service_id=b.id', 'left'),
            array('admin c', 'a.people_id=c.id', 'left'),
            array('user_card d', 'a.m_id=d.m_id', 'left'),
            array('service f', 'b.reid=f.id', 'left')
        );
        $where_data['where']['a.order_status']   = 1;
        $where_data['where']['a.status >']  = 2; //已经处理过的订单
        $where_data['where']['a.status !=']  = 10; //已经取消的订单
        $where_data['where']['a.is_send']  = 1; //已经外派的订单
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
        display('/check/send_list.html');
    }


}
