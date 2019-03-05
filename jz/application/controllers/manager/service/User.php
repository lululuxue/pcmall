<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class User extends CI_Controller
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
     * 获取分店
     */
    public function sub_shop()
    {
        $name = $this->input->post('name');
        $where_data['where']['a.name'] = $name;
        $where_data['select']= array('b.*');
        $where_data['join']= array(
            array('shop a','a.id=b.reid','left')
        );
        $shop_list = $this->loop_model->get_list('shop b',$where_data);
        error_json($shop_list);
    }

    /**
     * 客户档案
     */
    public function index()
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
        if (!empty($name) && !empty($type)) $where_data['like'][$type] = $name;
        if (!empty($shop)) $where_data['where']['a.shop'] = $shop;
        if (!empty($sub_shop)) $where_data['where']['a.sub_shop'] = $sub_shop;

      //  $where_data['select'] = array('u.*,a.name,a.username,a.note,FROM_UNIXTIME( u.endtime,"%Y-%m-%d") as endtime,f.prov,f.city,f.area,f.address');
        $where_data['join']   = array(
            array('user as a', 'a.Id=u.m_id', 'left'),
           // array('user_address as f', 'u.m_id=f.m_id', 'left'),
        );
        //搜索条件end
        $search_where = array(
            'name'             => $name,
            'type'             => $type,
            'shop'             => $shop,
            'sub_shop'        => $sub_shop
        );
        assign('search_where',$search_where);
        //查到数据
        $list_data = $this->loop_model->get_list('user_card as u', $where_data, $pagesize, $pagesize * ($page - 1), 'a.addtime desc');//列表

        foreach($list_data as $key=>$val){
            $provice = $this->loop_model->get_where('user_address',array('m_id'=>$val['Id'],'is_default'=>1));//列表
            if($provice){
                $val['address'] = $provice['prov'].$provice['city'].$provice['area'].$provice['address'];
            }else{
                $val['address'] = '';
            }

            $list_data[$key] = $val;
        }
       // var_dump($list_data);
        //开始分页start
        $all_rows = $this->loop_model->get_list_num('user_card as u', $where_data);//所有数量
       // var_dump($all_rows);
        assign('page_count', ceil($all_rows / $pagesize));
        //开始分页end
        assign('list', $list_data);
        display('/service/user/list.html');
    }


    /**
     * 添加开单
     */
    public function add($id)
    {
        if($id){
            $item = $this->loop_model->get_id('user',$id);
            $where_data['where'] = array('a.Id'=>$id);
            // $where_data['select'] = array('u.*,a.name,a.username,a.note,FROM_UNIXTIME( u.endtime,"%Y-%m-%d") as endtime,f.prov,f.city,f.area,f.address');
            $where_data['join']   = array(
                array('user as a', 'a.Id=u.m_id', 'left'),
               // array('user_address as f', 'u.m_id=f.m_id', 'left'),
            );
            //查到数据
            $data = $this->loop_model->get_list('user_card as u', $where_data, '', '', 'a.id desc');//列表
            assign('data', $data);  //客户卡消耗
            $where_data['select'] = array('a.*,FROM_UNIXTIME(a.addtime,"%Y-%m-%d %H:%m:%s") as addtime,FROM_UNIXTIME(a.completetime,"%Y-%m-%d %H:%m:%s") as dealtime,b.name,c.job_no,c.full_name as admin_name,d.card_no');
            $where_data['join']   = array(
                array('service b', 'a.service_id=b.id', 'left'),
                array('admin c', 'a.people_id=c.id', 'left'),
                array('user_card d', 'a.m_id=d.m_id', 'left')
            );
            $where_data['where']   = array('a.m_id'=>$id);
            //查到数据
            $list_data = $this->loop_model->get_list('order a', $where_data, 10, 0, 'a.addtime desc');//历史订单列表

            assign('list', $list_data);
            assign('id', $id);
        }
        //商品分类
        $this->load->model('service/service_model');
        $cat_list = $this->service_model->get_all();
        assign('cat_list', $cat_list);
        //员工列表
        $position_list = $this->loop_model->get_list('position', array('select'=>array('id,position_no,name')));//历史订单列表
        assign('position_list', $position_list);

        display('/service/user/add.html');
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
        $data = $this->loop_model->get_list('user_card as u', $where_data, '', '', 'a.id desc');//列表
        assign('data', $data);  //客户卡消耗
        $where_data['select'] = array('a.*,FROM_UNIXTIME(a.addtime,"%Y-%m-%d %H:%m:%s") as addtime,FROM_UNIXTIME(a.completetime,"%Y-%m-%d %H:%m:%s") as dealtime,b.name,c.job_no,c.full_name as admin_name,d.card_no');
        $where_data['join']   = array(
            array('service b', 'a.service_id=b.id', 'left'),
            array('admin c', 'a.people_id=c.id', 'left'),
            array('user_card d', 'a.m_id=d.m_id', 'left')
        );
        $where_data['where']   = array('a.m_id'=>$item['m_id']);
        //查到数据
        $list_data = $this->loop_model->get_list('order a', $where_data, 10, 0, 'a.addtime desc');//历史订单列表

        assign('list', $list_data);
        display('/service/user/edit.html');
    }

    /**
     * 保存开单
     */
    public function save()
    {
        if (is_post()) {
            $data_post = $this->input->post(NULL, true);
            $this->load->model('service/order_model');
            $res = $this->order_model->update($data_post, $this->admin_data);
            error_json($res);
        } else {
            error_json('提交方式错误');
        }

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
        display('/service/user/unlink.html');
    }

    /**
     * 工单记录
     */
    public function order_log()
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
        $where_data['select'] = array('a.*,a.completetime as dealtime,b.name,c.job_no,c.full_name as admin_name,d.card_no,f.name as key_name');
        $where_data['join']   = array(
            array('service b', 'a.service_id=b.id', 'left'),
            array('admin c', 'a.people_id=c.id', 'left'),
            array('user_card d', 'a.m_id=d.m_id', 'left'),
            array('service f', 'b.reid=f.id', 'left')
        );
        $where_data['where_in']['order_status'] = array(1,2,3);
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
        display('/service/user/order_log.html');
    }

    /**
     * 固定
     */
    public function gu_add($id){
        //商品分类
        $this->load->model('service/service_model');
        $cat_list = $this->service_model->get_all();
        //获取用户资料
        $user_data = $this->loop_model->get_id('user',$id);
        $item = $this->loop_model->get_where('normal_user',array('m_id'=>$id));
        if($item){
            if($item['dealtime']){
                $item['dealtime'] = date('Y-m-d',$item['dealtime']);
            }
            $gender = explode('/',$item['gender']);
            foreach($gender as $val){
                $data = explode('-',$val);
                $item[$data[0]] = $data[1];
            }
            assign('item',$item);
        }
        assign('cat_list', $cat_list);
        assign('user_data', $user_data);
        display('/service/user/gu_add.html');
    }

    /**
     * 保存固定
     */
    public function gu_save()
    {
        if (is_post()) {
            $data_post = $this->input->post(NULL, true);
            $this->load->model('service/normal_user_model');
            $res = $this->normal_user_model->update($data_post);
            error_json($res);
        } else {
            error_json('提交方式错误');
        }

    }

    /**
     * 固定列表
     */
    public function gu_list()
    {
        $pagesize = 20;//分页大小
        $page     = (int)$this->input->get('per_page');
        $page <= 1 ? $page = 1 : $page = $page;//当前页数
        //搜索条件start

        //关键字
        $name = $this->input->post_get('name');
        $shop = $this->input->post('shop');
        $sub_shop = $this->input->post('sub_shop');
        if (!empty($name)) $where_data['like']['b.name'] = $name;
        if (!empty($shop)) $where_data['where']['b.shop'] = $shop;
        if (!empty($sub_shop)) $where_data['where']['b.sub_shop'] = $sub_shop;

        $where_data['select'] = array('a.*,b.username,b.name,c.name as service_name,d.address,d.is_default');
        $where_data['join']   = array(
            array('user b', 'a.m_id=b.Id', 'left'),
            array('service c', 'a.service_id=c.id', 'left'),
            array('user_address d', 'd.m_id=b.id', 'left'),
        );
        $search_where = array(
            'name'             => $name,
            'shop'             => $shop,
            'sub_shop'        => $sub_shop,
        );
        assign('search_where',$search_where);
        //$where_data['where'] = array('d.is_default'=>1);  //默认地址
        $list_data = $this->loop_model->get_list('normal_user a', $where_data, $pagesize, $pagesize * ($page - 1), 'a.addtime,d.is_default desc');//列表
        //开始分页start
        $all_rows = $this->loop_model->get_list_num('normal_user a', $where_data);//所有数量
        assign('page_count', ceil($all_rows / $pagesize));
        //开始分页end
        assign('list', $list_data);
        display('/service/user/gu_list.html');

    }

    /***
     * 员工信息列表
     */
    public function position_list()
    {
        $pagesize = 20;//分页大小
        $page     = (int)$this->input->get('per_page');
        $page <= 1 ? $page = 1 : $page = $page;//当前页数
        //搜索条件start

        //关键字
        $type = $this->input->post_get('type');
        $name = $this->input->post_get('name');
        $shop = $this->input->post('shop');
        $sub_shop = $this->input->post('sub_shop');
        if (!empty($type) && !empty($name)) $where_data['like'][$type] = $name;
        if (!empty($shop)) $where_data['where']['shop'] = $shop;
        if (!empty($sub_shop)) $where_data['where']['sub_shop'] = $sub_shop;

        $where_data['select'] = array('*');

        $search_where = array(
            'name'             => $name,
            'shop'             => $shop,
            'sub_shop'        => $sub_shop,
        );
        assign('search_where',$search_where);
        $list_data = $this->loop_model->get_list('position', $where_data, $pagesize, $pagesize * ($page - 1), 'id asc');//列表
        //开始分页start
        $all_rows = $this->loop_model->get_list_num('position', $where_data);//所有数量
        assign('page_count', ceil($all_rows / $pagesize));
        //开始分页end
        assign('list', $list_data);
        //$date = date('w',time());
        $date = date('w',strtotime('2018-6-3'));
        assign('date', $date);
        display('/service/user/position_list.html');
    }

    /**
     * 顾客固定查询
     */
    public function order_gu_list()
    {
        $pagesize = 20;//分页大小
        $page     = (int)$this->input->get('per_page');
        $page <= 1 ? $page = 1 : $page = $page;//当前页数
        //搜索条件start

        //关键字
        $nornal_id = $this->input->post_get('normal_id');
        $type = $this->input->post_get('type');
        $name = $this->input->post_get('name');
        $shop = $this->input->post('shop');
        $sub_shop = $this->input->post('sub_shop');

        $sql = 'select a.*,a.completetime as dealtime';
        $sql .= ',b.name,c.job_no,c.full_name as admin_name,d.card_no,e.normal_id,e.value,e.point_people,e.note as normal_note';
        $sql .= ' from my_normal_user e left join my_order a on a.m_id=e.m_id';
        $sql .= ' left join my_admin c on c.id=a.people_id';
        $sql .= ' left join my_user_card d on a.m_id=d.m_id';
        $sql .= ' left join my_service b on a.service_id=b.id';
        $sql .= ' where a.order_status = 1 and a.id in (select max(id) from my_order group by m_id )';
        if (!empty($name) && !empty($type)) {
            if($type == 'phone'){
                $sql .= " and a.$type like '%$name%'";
            }else{
                $sql .= " and b.$type like '%$name%'";
            }
        }
        if (!empty($nornal_id)) {
            $sql .= " and e.normal_id = $nornal_id";
        }
        if (!empty($shop)) {
            $sql .= " and a.shop = '$shop'";
        }
        if (!empty($sub_shop)) {
            $sql .= " and a.sub_shop = '$sub_shop'";
        }
        $sql .= ' order by a.addtime desc';

        //搜索条件end
        $search_where = array(
            'name'             => $name,
            'type'             => $type,
            'normal_id'       => $nornal_id,
            'shop'            => $shop,
            'sub_shop'       => $sub_shop
        );
        assign('search_where',$search_where);
        //查到数据
        $query = $this->db->query($sql);
        $list_data = $query->result_array($sql);
        //开始分页start
        //$all_rows = $this->loop_model->get_list_num('order a', $where_data);//所有数量

        //assign('page_count', ceil($all_rows / $pagesize));
        assign('page_count',ceil(count($list_data )/ $pagesize));
        //开始分页end
        assign('list', $list_data);
        display('/service/user/order_gu_list.html');
    }

    /**
     * 会员卡消费记录
     */
    public function user_card_consume()
    {
        $pagesize = 20;//分页大小
        $page     = (int)$this->input->get('per_page');
        $page <= 1 ? $page = 1 : $page = $page;//当前页数
        //搜索条件start

        //关键字
        $type = $this->input->post_get('type');
        $name = $this->input->post_get('name');
        if (!empty($name) && !empty($type)) {
            if($type == 'card_no'){
                $where_data['like']['d.card_no'] = $name;
            }else{
                $where_data['like']['a.sub_shop'] = $name;
            }
        }

        $where_data['select'] = array('a.*,a.completetime as dealtime,b.name,c.job_no,c.full_name as admin_name,d.card_no,d.people_discount,d.care_discount,d.count,d.endtime');
        $where_data['join']   = array(
            array('service b', 'a.service_id=b.id', 'left'),
            array('admin c', 'a.people_id=c.id', 'left'),
            array('user_card d', 'a.m_id=d.m_id', 'left')
        );
        $where_data['where']['order_status']  = 1;
        $where_data['where']['pay_id !='] = NULL;

        //搜索条件end
        $search_where = array(
            'name'             => $name,
            'type'             => $type,
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
        display('/service/user/user_card_consume.html');
    }

    /**
     * 积分商品
     */
    public function point_goods()
    {
        /*
        $pagesize = 20;//分页大小
        $page     = (int)$this->input->get('per_page');
        $page <= 1 ? $page = 1 : $page = $page;//当前页数
        //搜索条件start

        //关键字
        $name = $this->input->post_get('name');
        if (!empty($name)) $where_data['like']['goods'] = $name;

        $where_data['select'] = array('a.*,FROM_UNIXTIME(a.addtime,"%Y-%m-%d %H:%m:%s") as dealtime,b.card_no,b.total_count,c.name');
        $where_data['join']   = array(
            array('user_card b', 'a.user_card_id=b.id', 'left'),
            array('user c', 'b.m_id=c.id', 'left'),

        );
        $search_where = array(
            'name'             => $name,
        );
        assign('search_where',$search_where);
        $list_data = $this->loop_model->get_list('user_card_consume a', $where_data, $pagesize, $pagesize * ($page - 1), 'a.id desc');//列表
        //开始分页start
        $all_rows = $this->loop_model->get_list_num('user_card_consume a', $where_data);//所有数量

        assign('page_count', ceil($all_rows / $pagesize));
        //开始分页end
        assign('list', $list_data);
        */
        display('/service/user/user_card_consume.html');
    }

    /**
     * 电话回访
     */
    public function call_back_list()
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
        if (!empty($name) && !empty($type))
            if($type == 'full_name'){
                $where_data['like']['a.full_name'] = $name;
            }elseif($type == 'card_no'){
                $where_data['like']['d.card_no'] = $name;
            }else{
                $where_data['like'][$type] = $name;
            }
        if (!empty($shop)) $where_data['where']['a.shop'] = $shop;
        if (!empty($sub_shop)) $where_data['where']['a.sub_shop'] = $sub_shop;
        if (!empty($start_time)) $where_data['where']['a.worktime >='] = strtotime($start_time);
        if (!empty($end_time)) $where_data['where']['a.worktime <=']  = strtotime($end_time);

        $where_data['select'] = array('a.*,a.worktime as dealtime,b.name as service_name,c.job_no,c.full_name as admin_name,d.card_no,d.people_discount,d.care_discount,d.count,d.endtime,e.id as note_id,e.note as call_note,e.addtime as call_time');
        $where_data['join']   = array(
            array('service b', 'a.service_id=b.id', 'left'),
            array('admin c', 'a.people_id=c.id', 'left'),
            array('user_card d', 'a.m_id=d.m_id', 'left'),
            array('order_callback e', 'e.order_id=a.id', 'left')
        );
        $where_data['where']['order_status'] = 1;
        $where_data['where']['a.status'] = 3;//已派单

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
        assign('page_count', ceil($all_rows / $pagesize));
        //开始分页end
        assign('list', $list_data);
        display('/service/user/call_back.html');
    }

    /***
     * 回访
     */

    public function call_back($id)
    {
        $sql = 'select a.*,a.worktime as dealtime,';
        $sql .= 'b.name as service_name,c.job_no,c.full_name as admin_name,d.card_no,d.people_discount,d.care_discount,d.count,d.endtime,e.id as note_id,e.note as call_note,e.addtime as call_time';
        $sql .= ' from my_order a';
        $sql .= ' left join my_service b on a.service_id=b.id';
        $sql .= ' left join my_admin c on c.id=a.people_id';
        $sql .= ' left join my_user_card d on a.m_id=d.m_id';
        $sql .= ' left join my_order_callback e on e.order_id=a.id';
        $sql .= ' where a.order_status = 1 and a.id = '.$id.' order by a.addtime';

        //查到数据
        $query = $this->db->query($sql);
        $user_data = $query->result_array($sql);
        $list = $user_data[0];
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
        display('/service/user/phone_call.html');
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
     * 投诉列表
     */
    public function complain_list()
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
        if (!empty($name) && !empty($type))
           if($type == 'card_no'){
                $where_data['like']['d.card_no'] = $name;
            }else{
                $where_data['like'][$type] = $name;
            }
        if (!empty($shop)) $where_data['where']['b.shop'] = $shop;
        if (!empty($sub_shop)) $where_data['where']['b.sub_shop'] = $sub_shop;
        if (!empty($start_time)) $where_data['where']['a.addtime >='] = strtotime($start_time);
        if (!empty($end_time)) $where_data['where']['a.addtime <=']  = strtotime($end_time);
        $where_data['select'] = array('a.*,b.phone,b.order_no,b.real_people,c.note as complain_note,d.card_no,e.name as service_name');
        $where_data['join']   = array(
            array('order b', 'a.order_id=b.id', 'left'),
            array('order_callback c', 'b.id=c.order_id', 'left'),
            array('user_card d', 'b.m_id=d.m_id', 'left'),
            array('service e', 'b.service_id=e.id', 'left'),
        );
       // $where_data['where']   = array('order_status'=>1);

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
        $list_data = $this->loop_model->get_list('order_deal a', $where_data, $pagesize, $pagesize * ($page - 1), 'a.id desc');//列表
        //开始分页start
        $all_rows = $this->loop_model->get_list_num('order_deal a', $where_data);//所有数量
        assign('page_count', ceil($all_rows / $pagesize));
        //开始分页end
        assign('list', $list_data);
        display('/service/user/complain_list.html');
    }

    /**
     * 投诉处理
     */
    public function complain_info($id)
    {
        $where_data['select'] = array('a.*,b.phone,b.order_no,b.real_people,c.note as complain_note,d.card_no,e.name as service_name');
        $where_data['join']   = array(
            array('order b', 'a.order_id=b.id', 'left'),
            array('order_callback c', 'b.id=c.order_id', 'left'),
            array('user_card d', 'b.m_id=d.m_id', 'left'),
            array('service e', 'b.service_id=e.id', 'left'),
        );
        $where_data['where']   = array('a.id'=>$id);

        //查到数据
        $info = $this->loop_model->get_list('order_deal a', $where_data, '', '', 'a.id desc');//列表
        //开始分页end
        assign('info', $info[0]);
        display('/service/user/complain_info.html');
    }

    /**
     * 投诉处理提交
     */
    public function complain_submit()
    {
        if (is_post()) {
            $data_post = $this->input->post(NULL, true);
            $this->load->model('service/complain_model');
            $res = $this->complain_model->update($data_post, $this->admin_data);
            error_json($res);
        } else {
            error_json('提交方式错误');
        }

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

}
