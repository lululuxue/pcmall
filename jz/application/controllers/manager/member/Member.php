<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Member extends CI_Controller
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
     * 会员卡查询
     */
    public function member_list()
    {
        $pagesize = 20;//分页大小
        $page     = (int)$this->input->get('per_page');
        $page <= 1 ? $page = 1 : $page = $page;//当前页数
        //关键字
        $shop = $this->input->post('shop');
        $card_id = $this->input->post('card_id');
        $start_time = $this->input->post('start_time');
        $end_time = $this->input->post('end_time');

        if (!empty($shop)) $where_data['where']['shop'] = $shop;
        if (!empty($card_id)) $where_data['where']['d.card_id'] = $card_id;
        if (!empty($start_time)) $where_data['where']['d.addtime >='] = strtotime($start_time);
        if (!empty($end_time)) $where_data['where']['d.addtime <=']  = strtotime($end_time);

        //统计查询
        $where_data['select'] = array('count(f.id) as total,g.name as card_name,f.shop');
        $where_data['join']   = array(
            array('user_card d', 'd.m_id=f.Id', 'left'),
            array('card g', 'g.id=d.card_id', 'left')
        );
        $where_data['where']['g.is_card']   = 1;  //是否符合会员卡资格
       // $where_data['where']['f.is_member']   = 2;  //是否申请了会员卡
        $total = $this->loop_model->get_group_list('user f', $where_data, $pagesize, $pagesize * ($page - 1), 'f.shop');//列表
        assign('total',$total);

        //查询卡消费
        $card_no = $this->input->post('card_no'); //卡号
        if (!empty($card_no)) $where['like']['d.card_no'] = $card_no;
        if (!empty($card_id)) $where['where']['d.card_id'] = $card_id;
        $where['select'] = array('f.username,f.name,g.name as card_name,a.*,d.card_no');
        $where['join']   = array(
            array('user_card d', 'a.user_card_id=d.id', 'left'),
            array('user f', 'd.m_id=f.id', 'left'),
            array('card g', 'g.id=d.card_id', 'left')
        );
        $where['where']['g.is_card']   = 1;  //是否符合会员卡资格
        $where['where']['f.is_member']   = 2;  //是否申请了会员卡

        //搜索条件end
        $search_where = array(
            'shop'             => $shop,
            'card_id'         => $card_id,
            'start_time'     => $start_time,
            'end_time'       => $end_time,
            'card_no'         =>$card_no
        );
        assign('search_where',$search_where);
        //查到数据
        $list_data = $this->loop_model->get_list('user_card_consume a', $where, $pagesize, $pagesize * ($page - 1), 'a.id desc');//列表
        //开始分页start
        $all_rows = $this->loop_model->get_list_num('user_card_consume a', $where);//所有数量
        assign('page_count', ceil($all_rows / $pagesize));
        //开始分页end
        assign('list', $list_data);
        //查找卡类型
        $card_list = $this->loop_model->get_list('card', array('where'=>array('is_card'=>1)), '', '', 'id desc');//列表
        assign('card_list', $card_list);
        display('/member/member_list.html');
    }

    /**
     * 开卡申请
     */
    public function open_card()
    {
        $pagesize = 20;//分页大小
        $page     = (int)$this->input->get('per_page');
        $page <= 1 ? $page = 1 : $page = $page;//当前页数
        //关键字
        $shop = $this->input->post('shop');
        $start_time = $this->input->post('start_time');
        $end_time = $this->input->post('end_time');
        $type = $this->input->post('type');
        $name = $this->input->post('name');

        if (!empty($shop)) $where_data['where']['shop'] = $shop;
        if (!empty($type) && !empty($name)) $where_data['like'][$type] = $name;
        if (!empty($start_time)) $where_data['where']['a.addtime >='] = strtotime($start_time);
        if (!empty($end_time)) $where_data['where']['a.addtime <=']  = strtotime($end_time);

        $where_data['select'] = array('a.*,d.card_no');
        $where_data['join']   = array(
            array('user_card d', 'a.id=d.m_id', 'left'),
        );
        $where_data['where']['is_member'] =  1; //查找不是会员的信息
        //搜索条件end
        $search_where = array(
            'name'             => $name,
            'type'             => $type,
            'shop'             => $shop,
            'start_time'      =>$start_time,
            'end_time'        => $end_time
        );
        assign('search_where',$search_where);
        //查到数据
        $list_data = $this->loop_model->get_list('user a', $where_data, $pagesize, $pagesize * ($page - 1), 'a.id desc');//列表
        //开始分页start
        $all_rows = $this->loop_model->get_list_num('user a', $where_data);//所有数量
        assign('page_count', ceil($all_rows / $pagesize));
        //开始分页end
        assign('list', $list_data);
        display('/member/open_card.html');
    }

    /**
     * 开卡申请
     */
    public function member_add($id)
    {
        $info = $this->loop_model->get_where('user', array('Id'=>$id));//列表
        assign('info',$info);
        display('/member/member_add.html');
    }

    /**
     * 开卡申请提交
     */
    public function card_add()
    {
        if (is_post()) {
            $data_post = $this->input->post(NULL, true);
            $this->load->model('member/card_model');
            $res = $this->card_model->update_card($data_post);
            //var_dump($res);
            error_json($res);
        } else {
            error_json('提交方式错误');
        }

        display('/memner/member_add.html');
    }


    /**
     * 充值申请
     */
    public function recharge_list()
    {
        $pagesize = 20;//分页大小
        $page     = (int)$this->input->get('per_page');
        $page <= 1 ? $page = 1 : $page = $page;//当前页数
        //关键字
        $shop = $this->input->post('shop');
        $start_time = $this->input->post('start_time');
        $end_time = $this->input->post('end_time');
        $type = $this->input->post('type');
        $name = $this->input->post('name');

        if (!empty($shop)) $where_data['where']['shop'] = $shop;
        if (!empty($type) && !empty($name)) $where_data['like'][$type] = $name;
        if (!empty($start_time)) $where_data['where']['a.addtime >='] = strtotime($start_time);
        if (!empty($end_time)) $where_data['where']['a.addtime <=']  = strtotime($end_time);

        $where_data['select'] = array('a.*,d.card_no');
        $where_data['join']   = array(
            array('user_card d', 'a.id=d.m_id', 'left'),
        );
        $where_data['where']['is_member']  = 2; //查找是会员的信息
       // $where_data['where']['g.is_card']   = 1;  //是否符合会员卡资格

        //搜索条件end
        $search_where = array(
            'name'             => $name,
            'type'             => $type,
            'shop'             => $shop,
            'start_time'      =>$start_time,
            'end_time'        => $end_time
        );
        assign('search_where',$search_where);
        //查到数据
        $list_data = $this->loop_model->get_list('user a', $where_data, $pagesize, $pagesize * ($page - 1), 'a.id desc');//列表
        //开始分页start
        $all_rows = $this->loop_model->get_list_num('user a', $where_data);//所有数量
        assign('page_count', ceil($all_rows / $pagesize));
        //开始分页end
        assign('list', $list_data);
        display('/member/recharge_list.html');
    }

    /**
     * 充值
     */
    public function recharge($id){
        $info = $this->loop_model->get_where('user', array('Id'=>$id));//列表
        assign('info',$info);
        //获取充值id
        $service = $this->loop_model->get_where('service',array('name'=>'充值'));
        assign('service',$service);
        display('/member/recharge.html');
    }


    /**
     * 变更申请
     */
    public function change_card()
    {
        //获取所有的充值变更申请
        $list_data = $this->loop_model->get_list('user_card_up',array('where'=>array('cat'=>1,'status'=>0)),'','','id desc');
        $this->load->helpers('upload_helper');//加载上传文件插件
        assign('list',$list_data);
        display('/member/change_card.html');
    }

    /**
     * 卡升级审核
     */
    public function card_up_verify($id)
    {
        $res = $this->loop_model->get_where('user_card_up',array('Id'=>$id));
        $info = $this->loop_model->get_where('user_card',array('Id'=>$res['user_card_id']));
        //查找卡类型
        $card_list = $this->loop_model->get_list('card', array('where'=>array('is_card'=>1)), '', '', 'id desc');//列表
        assign('card_list', $card_list);
        assign('info',$info);
        assign('res',$res);
        display('/member/card_up.html');
    }

    /**
     * 审核保存
     */
    public function member_card_verify_submit()
    {
        $post_data = $this->input->post(NULL,true);
        $this->load->model('member/card_model');
        $res = $this->card_model->user_card_verify($post_data);
        error_json($res);
    }

    /**
     * 获取卡信息
     */
    public function card_info(){
        if (is_post()) {
            $data_post = $this->input->post(NULL, true);
            $res = $this->loop_model->get_where('user_card',array('card_no'=>$data_post['card_no']));
            if($res){
                $res['endtime'] = date('Y-m-d',$res['endtime']);
                error_json($res);
            }else{
                error_json('卡号错误');
            }

        } else {
            error_json('提交方式错误');
        }
    }

    /***
     * 变更申请提交
     */
    public function change_card_save(){
        if (is_post()) {
            $data_post = $this->input->post(NULL, true);
            $this->load->model('member/card_model');
            $res = $this->card_model->user_card_up($data_post);
            if($res['status'] == 'y'){
                error_json($res);
            }else{
                error_json($res);
            }

        } else {
            error_json('提交方式错误');
        }
    }

    /**
     * 挂失申请
     */
    public function lose_card()
    {
        $this->load->helpers('upload_helper');//加载上传文件插件
        display('/member/lose_card.html');
    }

    /**
     * 申请挂失
     */
    public function very_lose_card(){
        if (is_post()) {
            $data_post = $this->input->post(NULL, true);
            $this->load->model('member/card_model');
            $res = $this->card_model->change_card($data_post);
            error_json($res);
        } else {
            error_json('提交方式错误');
        }
    }

    /**
     * 退卡申请
     */
    public function card_back(){
        $list_data = $this->loop_model->get_list('user_card_up',array('where'=>array('cat'=>2,'status'=>0)),'','','id desc');
        assign('list',$list_data);
        $this->load->helpers('upload_helper');//加载上传文件插件
        display('/member/card_back.html');
    }

    /**
     * 退卡审核
     */
    public function card_reback_verify($id)
    {
        $res = $this->loop_model->get_where('user_card_up',array('Id'=>$id));
        $info = $this->loop_model->get_where('user_card',array('Id'=>$res['user_card_id']));
        assign('info',$info);
        assign('res',$res);
        display('/member/card_back_verify.html');
    }

    /**
     * 退卡申请
     */
    public function card_back_submit(){
        if (is_post()) {
            $data_post = $this->input->post(NULL, true);
            $this->load->model('member/card_model');
            $res = $this->card_model->change_card($data_post);
            error_json($res);
        } else {
            error_json('提交方式错误');
        }
    }

}
