<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Area extends CI_Controller
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
     * 小区数据库
     */
    public function area_list()
    {
        $pagesize = 20;//分页大小
        $page     = (int)$this->input->get('per_page');
        $page <= 1 ? $page = 1 : $page = $page;//当前页数
        //关键字
        $shop = $this->input->post('shop');
        $name = $this->input->post('name');
        if (!empty($name) ){
            $where_data['like']['area_name'] = $name;
        }
        if (!empty($shop)) $where_data['where']['shop'] = $shop;
        $search_where = array(
            'name'             => $name,
            'shop'             => $shop
        );
        assign('search_where',$search_where);
        //查到数据
        $where_data['where']['is_delete'] = 1;//未被删除
        $list_data = $this->loop_model->get_list('areas_data', $where_data, $pagesize, $pagesize * ($page - 1), 'id desc');//列表
        //开始分页start
        $all_rows = $this->loop_model->get_list_num('areas_data', $where_data);//所有数量
        assign('page_count', ceil($all_rows / $pagesize));
        //开始分页end
        assign('list', $list_data);
        display('/area/area_list.html');
    }

    /**
     * 增加/修改小区数据
     */
    public function add_area_data($id){
        if($id){
            $info = $this->loop_model->get_where('areas_data', array('id'=>$id));//所有数量
            assign('info',$info);
            display('/area/edit_area.html');
        }else{
            display('/area/add_area.html');
        }
    }

    /**
     * 增加/修改小区数据保存
     */
    public function add_area_data_submit(){
        if (is_post()) {
            $data_post = $this->input->post(NULL, true);
            $this->load->model('areas_model');
            $res = $this->areas_model->update($data_post, $this->admin_data);
            error_json($res);
        } else {
            error_json('提交方式错误');
        }

    }

    /**
     * 删除小区数据
     */
    public function alert_delete($id){
        $info = $this->loop_model->get_where('areas_data', array('id'=>$id,'is_delete'=>1));//所有数量
        assign('info',$info);
        display('/area/alert_delete.html');
    }

    /**
     * 删除小区数据保存
     */
    public function delete(){
        $data_post = $this->input->post(NULL, true);
        $this->load->model('areas_model');
        $res = $this->areas_model->delete($data_post);
        error_json($res);

    }

    /**\
     * 派工
     */
    public function work($id)
    {
        $pagesize = 10;//分页大小
        $page     = (int)$this->input->get('per_page');
        $page <= 1 ? $page = 1 : $page = $page;//当前页数
        $where_data['where']   = array('areas_data_id'=>$id,'is_del'=>0);
        $list = $this->loop_model->get_list('broadcast_order',$where_data ,$pagesize, $pagesize * ($page - 1), 'id desc');
        $all_rows = $this->loop_model->get_list_num('broadcast_order', $where_data);
        assign('page_count', ceil($all_rows / $pagesize));
        assign('list', $list);
        $info = $this->loop_model->get_where('areas_data', array('id'=>$id,'is_delete'=>1));//所有数量
        assign('info',$info);
        //获取员工列表
        $position_list = $this->loop_model->get_list('position',array('select'=>array('id,position_no,name')));
        assign('position_list',$position_list);
        display('/area/work_send.html');
    }

    /**
     * 提交派工
     */
    public function submit_work()
    {
        $data_post = $this->input->post(NULL, true);
        $this->load->model('areas_model');
        if($data_post['broadcast_order_id']){
            $res = $this->areas_model->update_work($data_post,$this->admin_data);
        }else{
            $res = $this->areas_model->insert($data_post);
        }
        error_json($res);
    }

    /**
     * 添加派工内容
     */
    public function add_content($id)
    {
        $where_data['where']   = array('a.is_del'=>0,'a.id'=>$id);
        $where_data['select']   = array('a.*,d.area_name');
        $where_data['join']   = array(
            array('areas_data d', 'd.id=a.areas_data_id','left')
        );
        //查到数据
        $list_data = $this->loop_model->get_list('broadcast_order a', $where_data, '', '', 'a.id desc');//列表
        $info = $list_data[0];
        $info['array_people'] = explode('、',$info['work_people']);
        assign('info',$info);
        display('/area/add_content.html');
    }

    /**
     * 考勤
     */
    public function check_work($id){
        $where_data['where']   = array('a.is_del'=>0,'a.id'=>$id);
        $where_data['select']   = array('a.*,d.area_name');
        $where_data['join']   = array(
            array('areas_data d', 'd.id=a.areas_data_id','left')
        );
        //查到数据
        $list_data = $this->loop_model->get_list('broadcast_order a', $where_data, '', '', 'a.id desc');//列表
        $info = $list_data[0];
        $peoples = explode('||',$info['people_content']);
        foreach($peoples as $key=>$val){
            $res = explode(':',$val);
            $people[$key]['name'] = $res[0];
            $res1 = explode('|',$res[1]);
            foreach($res1 as $k=>$v){
                $res2 = explode('-',$v);
                $people[$key]['value'][$k] = $res2;
            }
        }
        $info['peoples'] = $people;
        $people_salary = explode(' ',$info['people_salary']);

        foreach($people_salary as $key=>$val){
            $data = explode(':',$val);
            $peoples_salary[$key]['name'] = $data[0];
            $peoples_salary[$key]['value'] = $data[1];
        }
        $info['people_salary'] = $peoples_salary;
        assign('info',$info);
        display('/area/check_work.html');
    }

    /**
     * 考勤提交
     */
    public function check_work_submit(){
        $data_post = $this->input->post(NULL, true);
        $this->load->model('areas_model');
        $res = $this->areas_model->check($data_post,$this->admin_data);
        error_json($res);
    }

    /**
     * 查看
     */
    public function detail($id){
        $where_data['where']   = array('a.is_del'=>0,'a.id'=>$id);
        $where_data['select']   = array('a.*,d.area_name');
        $where_data['join']   = array(
            array('areas_data d', 'd.id=a.areas_data_id','left')
        );
        //查到数据
        $list_data = $this->loop_model->get_list('broadcast_order a', $where_data, '', '', 'a.id desc');//列表
        $info = $list_data[0];
        $peoples = explode('||',$info['people_content']);
        foreach($peoples as $key=>$val){
            $res = explode(':',$val);
            $people[$key]['name'] = $res[0];
            $res1 = explode('|',$res[1]);
            foreach($res1 as $k=>$v){
                $res2 = explode('-',$v);
                $people[$key]['value'][$k] = $res2;
            }
        }
        $info['peoples'] = $people;
        $people_salary = explode(' ',$info['people_salary']);

        foreach($people_salary as $key=>$val){
            $data = explode(':',$val);
            $peoples_salary[$key]['name'] = $data[0];
            $peoples_salary[$key]['value'] = $data[1];
        }
        $info['people_salary'] = $peoples_salary;
        assign('info',$info);
        display('/area/detail.html');
    }

    /**
     * 查询
     */
    public function search_list()
    {
        $pagesize = 20;//分页大小
        $page     = (int)$this->input->get('per_page');
        $page <= 1 ? $page = 1 : $page = $page;//当前页数
        //关键字
        $shop = $this->input->post('shop');
        $type = $this->input->post('type');
        $name = $this->input->post('name');
        $start_time = $this->input->post('start_time');
        $end_time = $this->input->post('end_time');
        if (!empty($name) && !empty($type)){
            $where_data['like'][$type] = $name;
        }
        if (!empty($shop)) $where_data['where']['a.shop'] = $shop;
        if (!empty($start_time)) $where_data['where']['a.worktime >='] = strtotime($start_time);
        if (!empty($end_time)) $where_data['where']['a.worktime <=']  = strtotime($end_time);
        $where_data['where']['a.is_del']  = 0;
        $where_data['select']   = array('a.*,d.area_name');
        $where_data['join']   = array(
            array('areas_data d', 'd.id=a.areas_data_id','left')
        );

        $search_where = array(
            'name'             => $name,
            'type'             => $type,
            'shop'             => $shop,
            'start_time'      => $start_time,
            'end_time'        => $end_time
        );
        assign('search_where',$search_where);
        //查到数据
        $list_data = $this->loop_model->get_list('broadcast_order a', $where_data, $pagesize, $pagesize * ($page - 1), 'a.id desc');//列表
        //开始分页start
        $all_rows = $this->loop_model->get_list_num('broadcast_order a', $where_data);//所有数量
        assign('page_count', ceil($all_rows / $pagesize));
        //开始分页end
        assign('list', $list_data);
        display('/area/search_list.html');
    }

    /**
     * 查看考勤
     */
    public function check_detail($id)
    {
        display('/area/area_list.html');
    }

}
