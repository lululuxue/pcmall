<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class User extends CI_Controller
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
     * 客户档案
     */
    public function index()
    {
        $pagesize = 20;//分页大小
        $page     = (int)$this->input->get('per_page');
        $page <= 1 ? $page = 1 : $page = $page;//当前页数
        //搜索条件start

        //关键字
        $shop       =   $this->input->post('shop');
        $sub_shop   =   $this->input->post('sub_shop');
        $type       =   $this->input->post('type');
        $name       =   $this->input->post('name');
        if (!empty($name) && !empty($type))     $where_data['like'][$type] = $name;
        if (!empty($shop))                       $where_data['where']['a.shop'] = $shop;
        if (!empty($sub_shop))                   $where_data['where']['a.sub_shop'] = $sub_shop;
        $where_data['where']['a.is_delete'] = 0;

       // $where_data['select'] = array('u.*,a.name,a.username,a.note,FROM_UNIXTIME( u.endtime,"%Y-%m-%d") as endtime,a.address');
        $where_data['join']   =  array(
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
        $list_data = $this->loop_model->get_list('user_new_card as u', $where_data, $pagesize, $pagesize * ($page - 1), 'a.addtime desc');//列表
/*
        foreach($list_data as $key=>$val){
            $provice = $this->loop_model->get_where('user_address',array('m_id'=>$val['Id'],'is_default'=>1));//列表
            if($provice){
                $val['address'] = $provice['prov'].$provice['city'].$provice['area'].$provice['address'];
            }else{
                $val['address'] = '';
            }

            $list_data[$key] = $val;
        }
*/
       // var_dump($list_data);
        //开始分页start
        $all_rows = $this->loop_model->get_list_num('user_new_card as u', $where_data);//所有数量
       // var_dump($all_rows);
        assign('page_count', ceil($all_rows / $pagesize));
        //开始分页end
        assign('list', $list_data);
        display('/user/list.html');
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
            $data = $this->loop_model->get_list('user_new_card as u', $where_data, '', '', 'a.id desc');//列表
            assign('data', $data);  //客户卡消耗
            $where_data['select'] = array('a.*,e.username,FROM_UNIXTIME(a.addtime,"%Y-%m-%d %H:%m:%s") as addtime,FROM_UNIXTIME(a.completetime,"%Y-%m-%d %H:%m:%s") as dealtime,b.name,c.job_no,c.full_name as admin_name,d.card_no');
            $where_data['join']   = array(
                array('service b', 'a.service_id=b.id', 'left'),
                array('admin c', 'a.people_id=c.id', 'left'),
                array('user_new_card d', 'a.m_id=d.m_id', 'left'),
                array('user e', 'a.m_id=e.Id', 'left')
            );
            $where_data['where']   = array('a.m_id'=>$id);
            //查到数据
            $list_data = $this->loop_model->get_list('order a', $where_data, 10, 0, 'a.addtime desc');//历史订单列表
           ;

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

        display('/user/add.html');
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
        display('/user/gu_add.html');
    }

    /**
     * 用户信息修改、增加
     */
    public function user_edit($id){
        if($id){
            $user_data = $this->loop_model->get_id('user',$id);
            $user_info = $this->loop_model->get_where('user_new_card',array('m_id'=>$id));
            assign('user_data', $user_data);
            assign('user_info', $user_info);
        }else{

        }
        display('/user/user_edit.html');
    }
    /***
     * 用户信息保存
     */
    public function user_save(){
        if (is_post()) {
            $data_post = $this->input->post(NULL, true);
            $update['name']          = $data_post['name'];
            $update['note']          = $data_post['note'];
            $update['shop']          = $data_post['shop'];
            $update['sub_shop']     = $data_post['sub_shop'];
           // $update['jifen']         = $data_post['jifen'];
            $update['address']      = $data_post['address'];

           // $card_update['count']       = $data_post['count'];
           // $card_update['add_money']   = $data_post['add_money'];
           // $card_update['total_count'] = $data_post['total_count'];

            $m_id = $data_post['m_id'];
            //判断是否有$m_id
            if($m_id){//修改用户
                //开启事物
                $this->db->trans_begin();
                $res = $this->loop_model->update_where('user',$update,array('Id'=>$m_id));
                $res1 = $this->loop_model->update_where('user_new_card',$card_update,array('m_id'=>$m_id));
                //$res1 =
                if ($this->db->trans_status() === TRUE) {
                    $this->db->trans_commit();
                    error_json( 'y');
                } else {
                    $this->db->trans_rollback();
                    error_json( '信息保存失败');
                    //@todo 异常处理部分
                }

                // error_json($res);
            }else{
                //添加用户
                //判断该用户是否存在
                $user_data = $this->loop_model->get_where('user',array('username'=>$data_post['username']) );
                if(!$user_data){
                    //添加一个新用户
                    $insert_data = $update;
                    $insert_data['username'] = $data_post['username'];
                    $insert_data['addtime'] = time();
                    $this->db->trans_begin();
                    $res = $this->loop_model->insert('user', $insert_data);
                    //开一个新卡
                    $insert_card = $card_update;
                    $insert_card['card_no'] = $data_post['username'];
                    $insert_card['addtime'] = time();
                    $insert_card['m_id'] = $res;
                    $res1 = $this->loop_model->insert('user_new_card', $insert_card);
                    if ($this->db->trans_status() === TRUE) {
                        $this->db->trans_commit();
                        error_json( 'y');
                    } else {
                        $this->db->trans_rollback();
                        error_json( '信息保存失败');
                        //@todo 异常处理部分
                    }
                }else{
                    error_json( '该手机号用户已存在');
                }
            }

        } else {
            error_json('提交方式错误');
        }
    }

    /**
     * 删除用户
     */
    public function delete_user(){
        $id = (int)$this->input->post('id', true);
        if (empty($id)) error_json('id不能为空');
        //删除
        $this->db->trans_begin();
        //删除用户
        $this->loop_model->delete_where('user',array('Id'=>$id));
        //删除账户
        $this->loop_model->delete_where('user_new_card',array('m_id'=>$id));
        //删除订单
        $data1 = $this->loop_model->get_where('order',array('m_id'=>$id));
        if($data1){
            $this->loop_model->delete_where('order',array('m_id'=>$id));
        }
        //删除账单
        $data2 = $this->loop_model->get_where('order_collection_doc',array('m_id'=>$id));
        if($data2){
            $this->loop_model->delete_where('order_collection_doc',array('m_id'=>$id));
        }
        //删除积分
        $data3 = $this->loop_model->get_where('jifen',array('m_id'=>$id));
        if($data3) {
            $this->loop_model->delete_where('jifen', array('m_id' => $id));
        }
        //删除固定用户
        $data4 = $this->loop_model->get_where('normal_user',array('m_id'=>$id));
        if($data4) {
            $this->loop_model->delete_where('normal_user
            
            
            ', array('m_id' => $id));
        }
        if ($this->db->trans_status() === TRUE) {
            $this->db->trans_commit();
            error_json('y') ;
        } else {
            $this->db->trans_rollback();
            error_json('信息保存失败') ;
            //@todo 异常处理部分
        }
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
     * 客户详情
     */
    public function user_detail($id)
    {
        $pagesize = 10;//分页大小
        $page     = (int)$this->input->get('per_page');
        $page <= 1 ? $page = 1 : $page = $page;//当前页数
        if($id){
            $item = $this->loop_model->get_id('user',$id);
            $where_data['where'] = array('a.Id'=>$id);
            // $where_data['select'] = array('u.*,a.name,a.username,a.note,FROM_UNIXTIME( u.endtime,"%Y-%m-%d") as endtime,f.prov,f.city,f.area,f.address');
            $where_data['join']   = array(
                array('user as a', 'a.Id=u.m_id', 'left'),
                // array('user_address as f', 'u.m_id=f.m_id', 'left'),
            );
            //查到数据
            $data = $this->loop_model->get_list('user_new_card as u', $where_data, '', '', 'a.id desc');//列表
            assign('data', $data);  //客户卡消耗
            $where_data['select'] = array('a.*,FROM_UNIXTIME(a.addtime,"%Y-%m-%d %H:%m:%s") as addtime,FROM_UNIXTIME(a.completetime,"%Y-%m-%d %H:%m:%s") as dealtime,b.name,c.job_no,c.full_name as admin_name,d.card_no');
            $where_data['join']   = array(
                array('service b', 'a.service_id=b.id', 'left'),
                array('admin c', 'a.people_id=c.id', 'left'),
                array('user_new_card d', 'a.m_id=d.m_id', 'left')
            );
            $where_data['where']   = array('a.m_id'=>$id);
            //查到数据
            $list_data = $this->loop_model->get_list('order a', $where_data, $pagesize, $pagesize * ($page - 1), 'a.addtime desc');//历史订单列表
            $all_rows = $this->loop_model->get_list_num('order a', $where_data);//所有数量
            assign('page_count', ceil($all_rows / $pagesize));
            assign('list', $list_data);
            assign('id', $id);
        }else{
            assign('list', []);
            assign('id', []);
        }

        display('/user/user_detail.html');
    }

    /**
     * 客户详情
     */
    public function comsume_list($id)
    {
        $pagesize = 10;//分页大小
        $page     = (int)$this->input->get('per_page');
        $page <= 1 ? $page = 1 : $page = $page;//当前页数
        if($id){
            $item = $this->loop_model->get_id('user',$id);
            $where_data['where'] = array('a.Id'=>$id);
            // $where_data['select'] = array('u.*,a.name,a.username,a.note,FROM_UNIXTIME( u.endtime,"%Y-%m-%d") as endtime,f.prov,f.city,f.area,f.address');
            $where_data['join']   = array(
                array('user as a', 'a.Id=u.m_id', 'left'),
                // array('user_address as f', 'u.m_id=f.m_id', 'left'),
            );
            //查到数据
            $data = $this->loop_model->get_list('user_new_card as u', $where_data, '', '', 'a.id desc');//列表
            assign('data', $data);  //客户卡消耗
            $where_data['select'] = array('a.*,FROM_UNIXTIME(a.addtime,"%Y-%m-%d %H:%m:%s") as addtime,FROM_UNIXTIME(a.completetime,"%Y-%m-%d %H:%m:%s") as dealtime,b.name,c.job_no,c.full_name as admin_name,d.card_no');
            $where_data['join']   = array(
                array('service b', 'a.service_id=b.id', 'left'),
                array('admin c', 'a.people_id=c.id', 'left'),
                array('user_new_card d', 'a.m_id=d.m_id', 'left')
            );
            $where_data['where']   = array('a.m_id'=>$id);
            //查到数据
            $list_data = $this->loop_model->get_list('order a', $where_data, $pagesize, $pagesize * ($page - 1), 'a.addtime desc');//历史订单列表
            $all_rows = $this->loop_model->get_list_num('order a', $where_data);//所有数量
            assign('page_count', ceil($all_rows / $pagesize));
            assign('list', $list_data);
            assign('id', $id);
        }else{
            assign('list', []);
            assign('id', []);
        }

        display('/user/comsume_list.html');
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
        if (!empty($name))      $where_data['like']['b.name'] = $name;
        if (!empty($shop))      $where_data['where']['b.shop'] = $shop;
        if (!empty($sub_shop))  $where_data['where']['b.sub_shop'] = $sub_shop;

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
        display('/user/gu_list.html');

    }

    /**
     * 会员列表
     */
    public function member_list()
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
        if (!empty($name) && !empty($type))     $where_data['like'][$type] = $name;
        if (!empty($shop))                       $where_data['where']['a.shop'] = $shop;
        if (!empty($sub_shop))                   $where_data['where']['a.sub_shop'] = $sub_shop;
        $where_data['where']['a.is_delete'] = 0;

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
        $list_data = $this->loop_model->get_list('user_new_card as u', $where_data, $pagesize, $pagesize * ($page - 1), 'a.addtime desc');//列表
/*
        foreach($list_data as $key=>$val){
            $provice = $this->loop_model->get_where('user_address',array('m_id'=>$val['Id'],'is_default'=>1));//列表
            if($provice){
                $val['address'] = $provice['prov'].$provice['city'].$provice['area'].$provice['address'];
            }else{
                $val['address'] = '';
            }

            $list_data[$key] = $val;
        }
*/
        // var_dump($list_data);
        //开始分页start
        $all_rows = $this->loop_model->get_list_num('user_new_card as u', $where_data);//所有数量
        // var_dump($all_rows);
        assign('page_count', ceil($all_rows / $pagesize));
        //开始分页end
        assign('list', $list_data);
        display('/user/member_list.html');

    }

    /**
     * 充值
     */
    public function recharge($id){
        if($id){
            $user_data = $this->loop_model->get_id('user',$id);
            $user_info = $this->loop_model->get_where('user_new_card',array('m_id'=>$id));
            assign('user_data', $user_data);
            assign('user_info', $user_info);
        }
        display('/user/recharge.html');
    }

    /**
     * 充值保存
     */
    public function recharge_save(){
        if (is_post()) {
            $data_post = $this->input->post(NULL, true);

            $count          = $data_post['count'];
            $add_money      = $data_post['add_money'];
            $total_count    = $data_post['total_count'];

            $m_id = $data_post['m_id'];
            $user = $this->loop_model->get_id('user',$m_id);

            //开启事物
          $this->db->trans_begin();
            //插入订单
            $update_data = array(
                'full_name'             => $user['name'],
                'service_id'            => 113,
                //'source_id'               => '15',//公众号
                'order_status'          => 1,
                'status'                 => 5,
                //'prov'                    => $address['province'],
                //'city'                    => $address['city'],
               // 'area'                    => $address['area'],
                //'address'                 => $address['address'],
                'price_real'            => $count,
                'promotion_price'      => $count,
                'add_money'             => $add_money,
                'phone'                  => $user['username'],
                'completetime'          => strtotime($data_post['start_time']),
                'addtime'                => time(),
                'ordertime'              => time(),
                'note'                    => '线下充值',
                'm_id'                    => $m_id,
                'num_people'             =>0,
                'order_no'               =>date('Ymdhms',time()).substr(time(),-6)
            );
            $res = $this->loop_model->insert('order',$update_data);

            //插入收款单
            $collection_data = array(
               'order_id'     => $res,
                'm_id'         => $m_id,
                'amount'      => $total_count,
                'consume'     => $count,
                'add_money'   => $add_money,
                'addtime'     => time(),
                'payment_id'  => 1,
                'note'         => '线下交易',
                'admin_user'  => ''
            );
            $this->loop_model->insert('order_collection_doc', $collection_data);
            //
            //插入积分
            $insert_data['order_id']    = $res;
            $insert_data['type']        = 1;   //现金支付
            $insert_data['m_id']        = $m_id;
            $insert_data['amount']      = $count;
            $insert_data['addtime']     = time();
            $insert_data['note']         = '线下交易';
            $insert_data['jifen']        = ceil($count/10);
            $this->loop_model->insert('jifen',$insert_data);

            //更新积分
            $user_date['jifen']         = (int)$user['jifen'] + (int)ceil($count/10);;
            $this->loop_model->update_where('user', $user_date,array('Id'=>$m_id));

            //更新账户余额
            $user = $this->loop_model->get_where('user_new_card',array('m_id'=>$m_id));
            $account_data['count']				 = $user['count']+$count;
            $account_data['add_money']         = $user['add_money']+$add_money;
            $account_data['total_count']       = $user['total_count']+$total_count;

            $res = $this->loop_model->update_where('user_new_card',$account_data,array('m_id'=>$m_id));
            if ($this->db->trans_status() === TRUE) {
                $this->db->trans_commit();
                $mobile = $user['card_no'];
                $tmp = 'SMS_147436077';
                $this->load->library('SmsDemo');
                $res  = SmsDemo::sendOtherSms($mobile,$tmp,'',$count,'');
                if ($res->Code == 'OK') {
                    error_json( 'y');
                } else {
                    error_json($res->Message);
                }

            } else {
                $this->db->trans_rollback();
                error_json( '信息保存失败');
                //@todo 异常处理部分
            }

        } else {
            error_json('提交方式错误');
        }
    }

    /*
    * 地址列表
    */
    public function address_list($id){
        assign('m_id', $id);
        //获取地址列表
        $where['where']['m_id'] = $id;
        $data_list = $this->loop_model->get_list('user_address',$where);

        $str = '';
        foreach($data_list as $val){
            $id = $val['id'];
            $host = 'http://'.$_SERVER['SERVER_NAME'];
            $str .= '<div class="row cl">';
            $str .= '<label class="form-label col-1"><input type="radio" name="is_chose" value="'.$val['id'].'"></label>';
				$str .= '<div class="formControls col-9">';
					$str .= '<p>';
						$str .= '<span>'.$val['full_name'].'&nbsp;</span>';
						$str .= '<span>'.$val['tel'].'</span>';
            $str .= '</p>';
					$str .= '<p class="address-detail">';
                        if ($val['is_default'] == 1){
                            $str .= '<span style="color:#ff0000">默认</span>';
                        }
						$str .= '<span>'.$val['province'].'&nbsp;'. $val['city'].'&nbsp;'. $val['area']. '&nbsp;'.$val['address'].'</span> <a style="float:right;text-decoration:none;color:#2691FD" class="ml-5" onClick="open_iframe(\'\',\''.$host.'/admin/user/edit_address/'.$id.'\')"  title="修改">修改</a>';
            $str .= '</p>';
				$str .= '</div>';
            $str .= '</div>';
        }
        //assign('str', $str);
        error_json($str);
       // display('/user/address_list.html');
    }

    /*
     * 添加地址
     */
    public function add_address($id){
        assign('m_id', $id);
        display('/user/address_add.html');
    }

    /*
   * 修改地址
   */
    public function edit_address($id){
        $info = $this->loop_model->get_where('user_address',array('id'=>$id));
        assign('info', $info);
        display('/user/address_edit.html');
    }
    /*
 * 修改地址
 */
    public function address_info($id){
        $info = $this->loop_model->get_where('user_address',array('id'=>$id));
        error_json($info);
    }

    /**
     * 保存地址
     */
    public function save_address(){
        $post_data = $this->input->post(NULL);
        $this->load->model('user_address_model');
        $res = $this->user_address_model->update($post_data);
        error_json($res);
    }

}
