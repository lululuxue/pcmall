<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Login extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->helpers('shop_helper');
    }

    /**
     * 后台登陆
     */
    public function index()
    {
        $shop_id = $this->session->userdata('shop_id');
        if (!empty($shop_id)) {
            header('location:' . site_url('/seller/welcome'));
        } else {
            $redirect_url = trim($this->input->get('redirect_url', true));//返回链接
            if (empty($redirect_url)) $redirect_url = site_url('/seller/welcome');
            assign('redirect_url', $redirect_url);
            display('/login.html');
        }
    }

    /**
     * 后台登陆验证
     */
    public function login_act()
    {
        $username = trim($this->input->post('username', true));
        $password = trim($this->input->post('password', true));
        if (!empty($username) && !empty($password)) {
            $this->load->model('loop_model');
            if (stripos($username, ':') !== false) {
                //子账户
                $shop_shop_data = $this->loop_model->get_where('member_shop_admin', array('username' => $username));
                if (!empty($shop_shop_data)) {
                    //查询店铺信息
                    //查询店铺是否存在
                    $shop_data = $this->loop_model->get_where('member_shop', array('m_id' => $shop_shop_data['shop_id']));
                }
                $shop_data = array(
                    'id'           => $shop_shop_data['id'],
                    'username'     => $shop_shop_data['username'],
                    'password'     => $shop_shop_data['password'],
                    'salt'         => $shop_shop_data['salt'],
                    'admin_status' => $shop_shop_data['status'],
                    'status'       => $shop_data['status'],
                    'type'         => 'member',
                );
            } else {
                //主账号
                //查询会员是否存在
                $member_data = $this->loop_model->get_where('member', array('username' => $username));
                if (!empty($member_data)) {
                    //查询店铺是否存在
                    $shop_data = $this->loop_model->get_where('member_shop', array('m_id' => $member_data['id']));
                    if (!empty($shop_data)) {
                        $shop_data = array(
                            'id'       => $member_data['id'],
                            'username' => $member_data['username'],
                            'password' => $member_data['password'],
                            'salt'     => $member_data['salt'],
                            'status'   => $shop_data['status'],
                            'type'     => 'member_admin',
                        );
                    }
                }
            }

            if ($shop_data['username'] == '') {
                error_json('用户名不存在');
            } elseif ($shop_data['password'] != md5(md5($password) . $shop_data['salt'])) {
                error_json('密码错误');
            } elseif ($shop_data['status'] != 0 || $shop_data['admin_status'] != 0) {
                error_json('帐号被管理员锁定');
            } else {
                $this->session->set_userdata('shop_id', array(
                    'shop_id' => $shop_data['id'],
                    'type' => $shop_data['type'],
                    'username' => $shop_data['username'],
                ));
                shop_admin_log_insert($shop_data['username'] . '登录系统');
                error_json('y');
            }
        } else {
            error_json('账号和密码不能为空');
        }
    }

    /**
     * 后台用户退出登陆
     */
    public function loginout()
    {
        $shop_id = $this->session->userdata('shop_id');
        shop_admin_log_insert($shop_id['username'] . '退出系统');
        $this->session->unset_userdata('shop_id');
        echo "<script>alert('您已经退出登陆。');parent.window.location.href='" . site_url('/seller/login') . "'</script>";
    }

}
