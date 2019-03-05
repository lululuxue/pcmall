<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Welcome extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->helpers('web_helper');
        $this->member_data = member_login();
        $this->load->model('loop_model');
        $this->load->helpers('order_helper');
    }

    /**
     * 个人中心
     */
    public function index()
    {
        $member_data = $this->loop_model->get_id('member', $this->member_data['id']);
        assign('member_data', $member_data);
        $member_user_data = $this->loop_model->get_where('member_user', array('m_id' => $this->member_data['id']));
        assign('member_user_data', $member_user_data);
        $group_data = $this->loop_model->get_id('member_user_group', $member_user_data['group_id']);
        assign('group_data', $group_data);

        $this->load->helpers('upload_helper');//加载上传文件插件

        //等待付款
        $wait_pay = $this->loop_model->get_list_num('order', array('where' => array('status' => 1, 'payment_id>' => 1, 'm_id' => $this->member_data['id'])));
        //等待发货
        $wait_send = $this->loop_model->get_list_num('order', array('sql' => '((payment_id=1 and status=1) or (status=2))', 'where' => array('m_id' => $this->member_data['id'])));
        //等待收货
        $wait_sign = $this->loop_model->get_list_num('order', array('where' => array('status' => 3, 'm_id' => $this->member_data['id'])));
        //等待评价
        $wait_comment = $this->loop_model->get_list_num('order', array('where' => array('status' => 4, 'm_id' => $this->member_data['id'])));
        //退款
        $wait_refund = $this->loop_model->get_list_num('order', array('where' => array('status' => 10, 'm_id' => $this->member_data['id'])));
        assign('wait', array('wait_pay' => $wait_pay, 'wait_send' => $wait_send, 'wait_sign' => $wait_sign, 'wait_comment'=>$wait_comment, 'wait_refund' => $wait_refund));


        $pagesize = 20;//分页大小
        $page     = (int)$this->input->get('per_page');
        $page <= 1 ? $page = 1 : $page = $page;//当前页数
        //搜索条件start
        $status = $this->input->get_post('status');
        if (!empty($status)) {
            switch ($status) {
                case 1:
                    $where_data['where']['o.status']      = $status;
                    $where_data['where']['o.payment_id>'] = 1;//货到付款的不用支付
                    break;
                case 2:
                    $where_data['sql'] = '((o.payment_id=1 and o.status=1) or (o.status=2))';//包含货到付款的
                    break;
                default:
                    $where_data['where']['o.status'] = $status;
                    break;
            }
        }

        $search_where = array(
            'status' => $status,
        );
        assign('search_where', $search_where);
        //搜索条件end
        $where_data['where']['o.status>'] = 0;
        $where_data['select']             = 'o.*,shop.shop_name,shop.logo';
        $where_data['where']['o.m_id']    = $this->member_data['id'];//过滤用户
        $where_data['join']               = array(
            array('member_shop as shop', 'o.shop_id=shop.m_id')
        );
        //查到数据
        $order_list = $this->loop_model->get_list('order as o', $where_data, $pagesize, $pagesize * ($page - 1), 'o.id desc');//列表
        $this->load->model('order/order_model');
        foreach ($order_list as $key) {
            $order_sku          = '';
            $order_sku          = $this->order_model->get_order_sku($key['id']);
            $key['order_sku']   = $order_sku;
            $key['order_price'] = format_price($key['order_price']);
            $list[]             = $key;
        }
        assign('list', $list);
        display('/member/index.html');
    }

    /**
     * 个人资料
     */
    public function info()
    {
        $member_data = $this->loop_model->get_id('member', $this->member_data['id']);
        assign('member_data', $member_data);
        $member_user_data = $this->loop_model->get_where('member_user', array('m_id' => $this->member_data['id']));
        assign('member_user_data', $member_user_data);
        $this->load->helpers('upload_helper');//加载上传文件插件
        display('/member/info.html');
    }

    /**
     * 个人资料修改
     */
    public function info_act()
    {
        if (is_post()) {
            $data_post = $this->input->post(NULL, true);
            $info_data = array(
                'full_name' => $data_post['full_name'],
                'tel'       => $data_post['tel'],
                'sex'       => (int)$data_post['sex'],
                'email'     => $data_post['email'],
                'prov'      => (int)$data_post['prov'],
                'city'      => (int)$data_post['city'],
                'area'      => (int)$data_post['area'],
                'address'   => $data_post['address'],
                'id'        => $this->member_data['id'],
            );
            $this->load->model('member/user_model');
            $res = $this->user_model->update($info_data);
            error_json($res);
        } else {
            error_json('提交方式错误');
        }
    }

    /**
     * 个人头像修改
     */
    public function headimgurl_save()
    {
        if (is_post()) {
            $data_post = $this->input->post(NULL, true);
            if (!empty($data_post['headimgurl'])) {
                $info_data = array(
                    'headimgurl' => $data_post['headimgurl'],
                    'id'         => $this->member_data['id'],
                );
                $this->load->model('member/user_model');
                $res = $this->user_model->update($info_data);
                error_json($res);
            }
        } else {
            error_json('提交方式错误');
        }
    }

    /**
     * 修改密码
     */
    public function password()
    {
        $member_user_data = $this->loop_model->get_where('member_user', array('m_id' => $this->member_data['id']));
        assign('member_data', $this->member_data);
        assign('member_user_data', $member_user_data);
        display('/member/password.html');
    }

    /**
     * 修改密码
     */
    public function password_act()
    {
        if (is_post()) {
            $data_post = $this->input->post(NULL, true);
            if (empty($data_post['old_password'])) {
                error_json('旧密码不能为空');
            }
            if (empty($data_post['password'])) {
                error_json('新密码不能为空');
            }
            $member_data = $this->loop_model->get_id('member', $this->member_data['id']);
            if (!empty($member_data)) {
                if ($member_data['password'] != md5(md5($data_post['old_password']) . $member_data['salt'])) {
                    error_json('旧密码错误');
                } else {
                    $update_member_data['salt']     = get_rand_num();
                    $update_member_data['password'] = md5(md5($data_post['password']) . $update_member_data['salt']);
                    $res                            = $this->loop_model->update_id('member', $update_member_data, $this->member_data['id']);
                    if (!empty($res)) {
                        error_json('y');
                    } else {
                        error_json('修改失败');
                    }
                }
            }
        } else {
            error_json('提交方式错误');
        }
    }

    /**
     * 分享
     */
    public function share()
    {
        $member_data = $this->loop_model->get_id('member', $this->member_data['id']);
        assign('member_data', $member_data);
        display('/member/share.html');
    }
}
