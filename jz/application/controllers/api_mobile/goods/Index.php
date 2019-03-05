<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Index extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->model('loop_model');
    }

    /**
     * 首页
     */
    public function index()
    {
        header('Access-Control-Allow-Origin: *');
        // 响应类型
        header('Access-Control-Allow-Methods:POST');
        // 响应头设置
        header('Access-Control-Allow-Headers:x-requested-with,content-type');
        //父类
        $where['where_in']['reid'] = [0,1];
        $where['where']['is_del'] = 0;
        $where['where']['id >'] = 1;
        $where['select'] = array('id,name,image,sub_desc,reid,is_del,show_image');
        $parent_list = $this->loop_model->get_list('service', $where, '', '0', 'sortnum asc,id asc');
        //子类1
        $sub_list1 = $this->loop_model->get_list('service', array('where_not_in'=>['reid'=>0],'where'=>['flag'=>1],'select'=>'id,name,image,show_image,sub_desc'), '4', '0', 'sortnum asc,id asc');
        foreach($sub_list1 as $key=>$val){
            $sub_list1[$key]['title_list'] = explode(' ',$val['sub_desc']);
            if(count($sub_list1[$key]['title_list']) < 2){
                $sub_list1[$key]['title_list'][1] = '';
            }
        }
        //子类2
//        $sub_list2 = $this->loop_model->get_list('service', array('where_not_in'=>['reid'=>0],'where'=>['flag'=>1,'sortnum'=>98],'select'=>'id,name,image,show_image,sub_desc'), '4', '0', 'sortnum asc,id desc');
        $list = array(
            'parent_list'=>$parent_list,
            'sub_list1'=>$sub_list1,
//            'sub_list2'=>$sub_list2
        );
        error_json($list);
    }


    /**
     * 轮播图
     * $position_id（1-手机版首页banner，2-pc版首页banner，3-pc版首页banner下）
     */
    public function ad()
    {
        header('Access-Control-Allow-Origin: *');
        // 响应类型
        header('Access-Control-Allow-Methods:POST');
        // 响应头设置
        header('Access-Control-Allow-Headers:x-requested-with,content-type');
        $position_id= (int)$this->input->get('position_id');
        if (empty($position_id)) {
            $position_id = 1;   //默认手机版首页banner
        }
        $this->load->model('loop_model');
        $position_data = $this->loop_model->get_id('adv_position', $position_id);
        $adv_html      = '';
        if ($position_data['status'] == '0') {
            //单个的
            if ($position_data['play_type'] == 1) {
                $adv_data = $this->loop_model->get_where('adv', array('position_id' => $position_id, 'start_time<=' => time(), 'end_time>=' => time()));
            } elseif ($position_data['play_type'] == 2) {
                //列表
                $adv_data = $this->loop_model->get_list('adv', array('where' => array('position_id' => $position_id, 'start_time<=' => time(), 'end_time>=' => time())), '', '', 'sortnum asc,id desc');
            } elseif ($position_data['play_type'] == 3) {
                //随机
                $adv_data = $this->loop_model->get_where('adv', array('position_id' => $position_id, 'start_time<=' => time(), 'end_time>=' => time()), '*', 'rand()');
            }
            error_json($adv_data);
            //return $adv_data;
        }

    }

}
