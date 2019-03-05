<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Recharge extends CI_Controller
{

    private $admin_data;//后台用户登录信息

    public function __construct()
    {
        parent::__construct();
        $this->load->helpers('manager_helper');
        $this->admin_data = manager_login();
        assign('admin_data', $this->admin_data);
        $this->load->model('loop_model');
    }

    /**
     * 优惠列表
     */
    public function recharge_list()
    {
        $pagesize = 20;//分页大小
        $page     = (int)$this->input->get('per_page');
        $page <= 1 ? $page = 1 : $page = $page;//当前页数
        //搜索条件start

        //关键字
        $name = $this->input->post_get('name');
        if (!empty($name)) $where_data['like']['name'] = $name;
        /*
        $where_data['select'] = array('u.*,a.name,a.username,a.note,FROM_UNIXTIME( u.endtime,"%Y-%m-%d") as endtime,f.prov,f.city,f.area,f.address');
        $where_data['join']   = array(
            array('user as a', 'a.id=u.m_id', 'left'),
            array('user_address as f', 'a.id=f.m_id', 'left'),
        );
        */
        $where_data['select'] = array('a.*,FROM_UNIXTIME(a.addtime,"%Y-%m-%d %H:%m:%s") as addtime,FROM_UNIXTIME(a.completetime,"%Y-%m-%d %H:%m:%s") as dealtime,b.name,c.job_no,c.full_name as admin_name,d.card_no');
        $where_data['join']   = array(
            array('service b', 'a.service_id=b.id', 'left'),
            array('admin c', 'a.people_id=c.id', 'left'),
            array('user_card d', 'a.m_id=d.m_id', 'left')
        );
        $where_data['where']   = array('order_status'=>1);

        //搜索条件end
        $search_where = array(
            'name'             => $name,
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
     * 新增优惠
     */
    public function add_recharge()
    {
        display('/recharge/add.html');
    }

    /**
     * 现金工单
     */
    public function recharge_save()
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

}
