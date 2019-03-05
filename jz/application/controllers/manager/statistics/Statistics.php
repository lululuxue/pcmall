<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Statistics extends CI_Controller
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
     * 现金统计
     */
    public function cash_total()
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
        if (!empty($name) && !empty($type))$where_data['like'][$type] = $name;
        if (!empty($shop)) $where_data['where']['shop'] = $shop;
        if (!empty($sub_shop)) $where_data['where']['sub_shop'] = $sub_shop;
        if (!empty($start_time)) $where_data['where']['addtime >='] = strtotime($start_time);
        if (!empty($end_time)) $where_data['where']['addtime <=']  = strtotime($end_time);
        $all_data = [];
        //派单数(所有消费)
        $where_data['select']= array('count(id),sum(percentage*price_real/100) as percentage,sum(price_real) as total_money');
        $where_data['where_in']['status'] = array(3,5,6);//派单工单
        $total_work_order = $this->loop_model->get_list('order', $where_data);
        $all_data['total_work_order'] = $total_work_order[0];
        //派单数（现金消费）
        $where_data['where']['pay_id'] = NULL;//非会员卡消费（现金消费）
        $cash_work_order = $this->loop_model->get_list('order', $where_data);
        $all_data['cash_work_order'] = $cash_work_order[0];

        assign('all_data', $all_data);

        $search_where = array(
            'name'             => $name,
            'type'             => $type,
            'shop'             => $shop,
            'sub_shop'        => $sub_shop,
            'start_time'      => $start_time,
            'end_time'        => $end_time
        );
        assign('search_where', $search_where);
        display('/statistics/cash_total.html');
    }

    /**
     * 折卡消费统计
     */
    public function consume_total()
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
        if (!empty($name) && !empty($type))$where_data['like'][$type] = $name;
        if (!empty($shop)) $where_data['where']['shop'] = $shop;
        if (!empty($sub_shop)) $where_data['where']['sub_shop'] = $sub_shop;
        //if (!empty($start_time)) $where_data['where']['addtime >='] = strtotime($start_time);
       // if (!empty($end_time)) $where_data['where']['addtime <=']  = strtotime($end_time);
        $where_data['where']['pay_id !='] = NULL;//会员卡消费
        $all_data = [];
        $today_data = [];
        //--------------今日
        $start = strtotime(date('Y-m-d',time()));
        $end = strtotime(date('Y-m-d',strtotime('+1 day')));
        //录入单数
        $where1 = $where_data;
        $where1['where']['addtime >='] = $start;
        $where1['where']['addtime <='] = $end;
        $where1['where']['order_status'] = 1;//录入工单
        $where1['select']= array('count(id) as order_count,sum(price_real) as money,sum(num_people) as num');
        $today_all_order = $this->loop_model->get_list('order', $where1);
        $today_data['today_all_order'] = $today_all_order[0];

        //派单数
        $where2 = $where_data;
        $where2['where']['worktime >='] = $start;
        $where2['where']['worktime <='] = $end;
        $where2['select']= array('count(id) as order_count,sum(price_real) as money,sum(num_people) as num');
        $where2['where_in']['status'] = array(3,5,6);//派单工单
        $today_work_order = $this->loop_model->get_list('order', $where2);
        $today_data['today_work_order'] = $today_work_order[0];
        //结算单款
        $where3 = $where_data;
        $where3['where']['fee_time >='] = $start;
        $where3['where']['fee_time <='] = $end;
        $where3['select']= array('count(id) as order_count,sum(price_real) as money,sum(num_people) as num');
        $where3['where_in']['status'] = array(5,6);//结算工单
        $today_pay_order = $this->loop_model->get_list('order', $where3);
        $today_data['today_pay_order'] = $today_pay_order[0] ;
        //-------------该段时间段
        if (!empty($start_time)) $where_data['where']['addtime >='] = strtotime($start_time);
        if (!empty($end_time)) $where_data['where']['addtime <=']  = strtotime($end_time);
        //录入单数
        $where_data['where']['order_status'] = 1;//录入工单
        $where_data['select']= array('count(id) as order_count,sum(price_real) as money,sum(num_people) as num');
        $today_all_order = $this->loop_model->get_list('order', $where_data);
        $all_data['short_all_order'] = $today_all_order[0];

        //派单数
        $where_data['select']= array('count(id) as order_count,sum(price_real) as money,sum(num_people) as num');
        $where_data['where_in']['status'] = array(3,5,6);//派单工单
        $today_work_order = $this->loop_model->get_list('order', $where_data);
        $all_data['short_work_order'] = $today_work_order[0];
        //结算单款
        $where_data['select']= array('count(id) as order_count,sum(price_real) as money,sum(num_people) as num');
        $where_data['where_in']['status'] = array(5,6);//结算工单
        $today_pay_order = $this->loop_model->get_list('order', $where_data);
        $all_data['short_pay_order'] = $today_pay_order[0] ;

        assign('all_data', $all_data);
        assign('today_data', $today_data);
        $search_where = array(
            'name'             => $name,
            'type'             => $type,
            'shop'             => $shop,
            'sub_shop'        => $sub_shop,
            'start_time'      => $start_time,
            'end_time'        => $end_time
        );
        assign('search_where', $search_where);

        $list_data = $this->loop_model->get_list('order', $where_data, $pagesize, $pagesize * ($page - 1), 'id desc');//列表
        //开始分页start
        $all_rows = $this->loop_model->get_list_num('order', $where_data);//所有数量
        assign('page_count', ceil($all_rows / $pagesize));
        //开始分页end
        assign('list', $list_data);
        display('/statistics/consume_total.html');
    }

    /**
     * 业务来源统计
     */
    public function source_total()
    {
        $pagesize = 20;//分页大小
        $page     = (int)$this->input->get('per_page');
        $page <= 1 ? $page = 1 : $page = $page;//当前页数
        //关键字
        $shop = $this->input->post('shop');
        $sub_shop = $this->input->post('sub_shop');
        $start_time = $this->input->post('start_time');
        $end_time = $this->input->post('end_time');
        $name = $this->input->post('name');
        $type = $this->input->post('type');
        if (!empty($name) && !empty($type))$where_data['like'][$type] = $name;
        if (!empty($shop)) $where_data['where']['shop'] = $shop;
        if (!empty($sub_shop)) $where_data['where']['sub_shop'] = $sub_shop;
        if (!empty($start_time)) $where_data['where']['order.addtime >='] = strtotime($start_time);
        if (!empty($end_time)) $where_data['where']['order.addtime <=']  = strtotime($end_time);
        $all_data = [];
        //派单数(所有消费)
        $where_data['select']= array('count(id) as orders,sum(percentage*price_real/100) as percentage,sum(price_real) as total_money');
        $where_data['where_in']['status'] = array(3,5,6);//派单工单
        $total_work_order = $this->loop_model->get_list('order', $where_data);
        $all_data['total_work_order'] = $total_work_order[0];
        //派单数（来源消费）
        $where_data['join']= array(
            array('source','source.id = order.source_id','left')
        );
        $where_data['select']= array('my_source.id,my_source.source_name,count(my_order.id) as orders,sum(my_order.percentage*my_order.price_real/100) as percentage,sum(my_order.price_real) as total_money');
        $all_data['cash_work_order'] = $this->loop_model->get_group_list('order', $where_data,'','','source_id');

        $all_rows = $this->loop_model->get_list_num('order', $where_data);//所有数量
        assign('page_count', ceil($all_rows / $pagesize));
        assign('list', $all_data);
        $search_where = array(
            'name'             => $name,
            'type'             => $type,
            'shop'             => $shop,
            'sub_shop'        => $sub_shop,
            'start_time'      => $start_time,
            'end_time'        => $end_time
        );
        assign('search_where', $search_where);
        display('/statistics/source_total.html');
    }

    /**
     * 服务项目统计
     */
    public function service_total()
    {
        $pagesize = 20;//分页大小
        $page     = (int)$this->input->get('per_page');
        $page <= 1 ? $page = 1 : $page = $page;//当前页数
        //关键字
        $shop = $this->input->post('shop');
        $sub_shop = $this->input->post('sub_shop');
        $start_time = $this->input->post('start_time');
        $end_time = $this->input->post('end_time');
        $name = $this->input->post('name');
        $type = $this->input->post('type');
        if (!empty($name) && !empty($type))$where_data['like'][$type] = $name;
        if (!empty($shop)) $where_data['where']['shop'] = $shop;
        if (!empty($sub_shop)) $where_data['where']['sub_shop'] = $sub_shop;
        if (!empty($start_time)) $where_data['where']['addtime >='] = strtotime($start_time);
        if (!empty($end_time)) $where_data['where']['addtime <=']  = strtotime($end_time);
        $all_data = [];
        //派单数(所有消费，订单)
        $where_data['select']= array('count(id) as orders,sum(percentage*price_real/100) as percentage,sum(price_real) as total_money');
        $where_data['where_in']['status'] = array(3,5,6);//派单工单
        $total_work_order = $this->loop_model->get_list('order', $where_data);
        $all_data['total_work_order'] = $total_work_order[0];
        //派单数（现金消费）
        $where_data['where']['pay_id'] = NULL;//现金消费
        $where_data['join']= array(
            array('service','service.id = order.service_id','left')

        );
        $where_data['select']= array('my_service.id,my_service.name,count(my_order.id) as orders,COALESCE(sum(my_order.price_real),0) as total_money');
        $all_data['work_order'] = $this->loop_model->get_group_list('order', $where_data,'','','service_id');
        foreach($all_data['work_order'] as $k=>$v){
            unset($where_data['where']['pay_id']);
            $where_data['where']['pay_id !='] = NULL;//卡消费
            $where_data['where']['service_id'] = $v['id'];//卡消费
            $cash = $this->loop_model->get_list('order', $where_data,'','','service_id');
            if($cash){
                $v['cash_money']= $cash[0]['total_money'];
                $v['cash_orders'] = $cash[0]['orders'];
                $v['cash_orders'] = $cash[0]['orders'];
            }else{
                $v['cash_money']= 0;
                $v['cash_orders'] = 0;
                $v['cash_orders'] = 0;
            }

            $all_data['work_order'][$k] = $v;
        }
        $all_rows = $this->loop_model->get_list_num('order', $where_data);//所有数量
        assign('page_count', ceil($all_rows / $pagesize));
        assign('list', $all_data);
        $search_where = array(
            'name'             => $name,
            'type'             => $type,
            'shop'             => $shop,
            'sub_shop'        => $sub_shop,
            'start_time'      => $start_time,
            'end_time'        => $end_time
        );
        assign('search_where', $search_where);
        display('/statistics/service_total.html');
    }

    /**
     * 业务查询
     */
    public function business_query()
    {
        $pagesize = 20;//分页大小
        $page     = (int)$this->input->get('per_page');
        $page <= 1 ? $page = 1 : $page = $page;//当前页数
        //关键字
        $shop = $this->input->post('shop');
        $sub_shop = $this->input->post('sub_shop');
        $source_id = $this->input->post('source_id');
        $start_time = $this->input->post('start_time');
        $end_time = $this->input->post('end_time');

        if (!empty($source_id)) $where_data['where']['source_id'] = $source_id;
        if (!empty($shop)) $where_data['where']['a.shop'] = $shop;
        if (!empty($sub_shop)) $where_data['where']['a.sub_shop'] = $sub_shop;
        if (!empty($start_time)) $where_data['where']['a.completetime >='] = strtotime($start_time);
        if (!empty($end_time)) $where_data['where']['a.completetime <=']  = strtotime($end_time);
        $where_data['select'] = array('a.*,a.completetime as dealtime,b.name,c.job_no,c.full_name as admin_name,d.card_no,g.source_name');
        $where_data['join']   = array(
            array('service b', 'a.service_id=b.id', 'left'),
            array('admin c', 'a.people_id=c.id', 'left'),
            array('user_card d', 'a.m_id=d.m_id', 'left'),
            array('source g', 'g.id=a.source_id', 'left')
        );
        $where_data['where']['a.order_status']   = 1;
        $where_data['where']['a.status >']  = 2; //已经处理过的订单
        $where_data['where']['a.status !=']  = 10; //已经取消的订单
        $where_data['where']['source_id > ']  = 0;

        $search_where = array(
            'source_id'       => $source_id,
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
        $source_list = $this->loop_model->get_list('source');
        assign('source_list', $source_list);
        display('/statistics/business_query.html');
    }
    /**
     * 排名统计
     */
    public function rank_total($id)
    {
        //关键字
        $shop = $this->input->post('shop');
        $sub_shop = $this->input->post('sub_shop');
        $start_time = $this->input->post('start_time');
        $end_time = $this->input->post('end_time');
        $service_id = $this->input->post('service_id');
        $department_id = $this->input->post('department_id');

        if (!empty($shop)) $where_data['where']['shop'] = $shop;
        if (!empty($sub_shop)) $where_data['where']['sub_shop'] = $sub_shop;
        if (!empty($start_time)) $where_data['where']['addtime >='] = strtotime($start_time);
        if (!empty($end_time)) $where_data['where']['addtime <=']  = strtotime($end_time);
        if (!empty($service_id)) $where_data['where']['service_id'] = $service_id;


        if (!empty($department_id)) $where_data['where']['department_id']  = $department_id;
        $where1 = $where_data;
        $where1['select'] = array("id,name,position_no");
        $all_data = [];
        //获取员工列表
        $position_list = $this->loop_model->get_list('position',$where1);
        foreach($position_list as $k=>$v){
            $where_data['where']['real_people'] = $v['name'];
            $where_data['select']= array('count(id) as orders,COALESCE(sum(price_real*percentage/100),0) as percentage,COALESCE(sum(price_real),0) as total_money');
            $where_data['where_in']['status'] = array(3,5,6);//派单工单
            $all_data = $this->loop_model->get_list('order', $where_data);
            $v['orders'] = $all_data[0]['orders'];
            $v['percentage'] = $all_data[0]['percentage'];
            $v['total_money'] = $all_data[0]['total_money'];
            $position_list[$k] = $v;
        }
        //根据销售额排队
        $arrSort = array();
        foreach ($position_list as $uniqid => $row) {
            foreach ($row as $key => $value) {
                $arrSort[$key][$uniqid] = $value;
            }
        }
        array_multisort($arrSort['total_money'], constant('SORT_DESC'), $position_list);
        assign('list', $position_list);
        $search_where = array(
            'shop'             => $shop,
            'sub_shop'        => $sub_shop,
            'start_time'      => $start_time,
            'end_time'        => $end_time,
            'service_id'      => $service_id,
            'department_id'  => $department_id
        );
        assign('search_where', $search_where);
        //获取所有部门
        $department_list = $this->loop_model->get_list('department');
        assign('department_list', $department_list);
        //获取所有项目
        $service_list = $this->loop_model->get_list('service',array('where'=>array('sortnum >'=>0)));
        assign('service_list', $service_list);
        display('/statistics/rank_total.html');
    }

    /**
     * 会员卡统计
     */
    public function card_total($id)
    {
        $pagesize = 20;//分页大小
        $page     = (int)$this->input->get('per_page');
        $page <= 1 ? $page = 1 : $page = $page;//当前页数
        //关键字
        $shop = $this->input->post('shop');
        $sub_shop = $this->input->post('sub_shop');
        $start_time = $this->input->post('start_time');
        $end_time = $this->input->post('end_time');
        $service_id = $this->input->post('service_id');
        if (!empty($service_id)){
            $where_data['where']['service_id'] = $service_id;
        } else{
            $where_data['where']['service_id'] = 114;
        }
        if (!empty($shop)) $where_data['where']['shop'] = $shop;
        if (!empty($sub_shop)) $where_data['where']['sub_shop'] = $sub_shop;
        if (!empty($start_time)) $where_data['where']['addtime >='] = strtotime($start_time);
        if (!empty($end_time)) $where_data['where']['addtime <=']  = strtotime($end_time);
        $all_data = [];
        //派单数(所有消费，订单)
        $where_data['select']= array('count(id) as orders,COALESCE(sum(price_real),0) as total_money');
        $where_data['where_in']['status'] = array(3,5,6);//派单工单
        $all_data = $this->loop_model->get_group_list('order', $where_data,'','','service_id');
        $all_rows = $this->loop_model->get_list_num('order', $where_data);//所有数量
        assign('page_count', ceil($all_rows / $pagesize));
        assign('list', $all_data);
        $search_where = array(
            'service_id'      => $service_id,
            'shop'             => $shop,
            'sub_shop'        => $sub_shop,
            'start_time'      => $start_time,
            'end_time'        => $end_time
        );
        assign('search_where', $search_where);
        display('/statistics/card_total.html');
    }
    /**
     * 临时排名
     */
    public function short_rank($id)
    {
        $pagesize = 20;//分页大小
        $page     = (int)$this->input->get('per_page');
        $page <= 1 ? $page = 1 : $page = $page;//当前页数
        //关键字
        $shop = $this->input->post('shop');
        $sub_shop = $this->input->post('sub_shop');
        $start_time = $this->input->post('start_time');
        $end_time = $this->input->post('end_time');

        if (!empty($shop)) $where_data['where']['shop'] = $shop;
        if (!empty($sub_shop)) $where_data['where']['sub_shop'] = $sub_shop;
        if (!empty($start_time)) $where_data['where']['addtime >='] = strtotime($start_time);
        if (!empty($end_time)) $where_data['where']['addtime <=']  = strtotime($end_time);
        $all_data = [];
        //派单数(所有消费，订单)
        $where_data['select']= array('count(id) as orders,company,COALESCE(sum(price_real),0) as total_money');
        $where_data['where_in']['status'] = array(3,5,6);//派单工单
        $all_data = $this->loop_model->get_group_list('order', $where_data,'','','company','total_money desc');
        $all_rows = $this->loop_model->get_list_num('order', $where_data);//所有数量
        assign('page_count', ceil($all_rows / $pagesize));
        assign('list', $all_data);
        $search_where = array(
            'shop'             => $shop,
            'sub_shop'        => $sub_shop,
            'start_time'      => $start_time,
            'end_time'        => $end_time
        );
        assign('search_where', $search_where);
        display('/statistics/short_rank.html');
    }

    /**
     * 帮扶业务统计
     */
    public function help_total()
    {
        //关键字
        $shop = $this->input->post('shop');
        $sub_shop = $this->input->post('sub_shop');
        $start_time = $this->input->post('start_time');
        $end_time = $this->input->post('end_time');
        $service_id = $this->input->post('service_id');
        $department_id = $this->input->post('department_id');

        if (!empty($shop)) $where_data['where']['shop'] = $shop;
        if (!empty($sub_shop)) $where_data['where']['sub_shop'] = $sub_shop;
        if (!empty($start_time)) $where_data['where']['addtime >='] = strtotime($start_time);
        if (!empty($end_time)) $where_data['where']['addtime <=']  = strtotime($end_time);

        $where1 = $where_data;
        if (!empty($department_id)) $where1['where']['department_id']  = $department_id;
        $where1['select'] = array("id,name,position_no");

        $where2 = $where_data;
        if (!empty($service_id)) $where2['where']['service_id'] = $service_id;

        $all_data = [];
        //获取员工列表
        $position_list = $this->loop_model->get_list('position',$where1);
        foreach($position_list as $k=>$v){
            $where2['where']['help_people'] = $v['name'];
            $where2['select']= array('count(id) as orders,COALESCE(sum(help_amount),0) as total_money');
            $where2['where_in']['status'] = array(3,5,6);//派单工单
            $all_data = $this->loop_model->get_list('order', $where2);
            $v['orders'] = $all_data[0]['orders'];
            $v['total_money'] = $all_data[0]['total_money'];
            $position_list[$k] = $v;
        }
        //根据销售额排队
        $arrSort = array();
        foreach ($position_list as $uniqid => $row) {
            foreach ($row as $key => $value) {
                $arrSort[$key][$uniqid] = $value;
            }
        }
        array_multisort($arrSort['total_money'], constant('SORT_DESC'), $position_list);
        assign('list', $position_list);
        $search_where = array(
            'shop'             => $shop,
            'sub_shop'        => $sub_shop,
            'start_time'      => $start_time,
            'end_time'        => $end_time,
            'service_id'      => $service_id,
            'department_id'  => $department_id,
        );
        assign('search_where', $search_where);
        //获取所有部门
        $department_list = $this->loop_model->get_list('department');
        assign('department_list', $department_list);
        //获取所有项目
        $service_list = $this->loop_model->get_list('service',array('where'=>array('sortnum >'=>0)));
        assign('service_list', $service_list);
        assign('search_where', $search_where);
        display('/statistics/help_total.html');
    }



}
