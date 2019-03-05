<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Login extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->model('loop_model');
    }

    /**
     * 开始登录
     */
    public function user_login()
    {
        if (is_post()) {
            $job_no = trim($this->input->post('job_no', true));
            $password = trim($this->input->post('password', true));
            $command = trim($this->input->post('command', true));
            if (!empty($job_no) && !empty($password) && !empty($command)) {
                $admin_data = $this->loop_model->get_where('admin', array('job_no' => $job_no));
                if ($admin_data['job_no'] == '') {
                    error_json('工号不存在');
                } elseif ($admin_data['password'] != md5(md5($password) . $admin_data['salt'])) {
                    error_json('密码错误');
                } else {
                    //
                    if ($admin_data['status'] == 0) {
                        //设置登录信息
                        if (config_item('safe_type') == 'cookie') {
                            //$this->input->set_cookie('m_id', encrypt($member_data['id']), config_item('safe_time'));
                            $this->loop_model->update_where('admin', array('lasttime' => time()), array('id' => $admin_data['id']));
                        } else {
                            $this->session->set_userdata('admin_id', $admin_data['id']);
                        }

                        $salt = substr(uniqid(), -6);
                        $token = md5($admin_data['id'] . $admin_data['password'] . $salt);
                        $tokenData = [
                            'm_id'      => $admin_data['id'],
                            'token'    => $token,
                            'salt'     => $salt,
                            'expire'   => time() + 5 * 24 * 3600,
                        ];
                        cache('save', 'user_token_'.$admin_data['id'] , $token,5 * 24 * 3600);//保存token
                        error_json($tokenData,'y');
                    } else {
                        error_json('账户已经被锁定或删除');
                    }

                }
            } else {
                error_json('工号、密码、口令不能为空');
            }
        }
    }

}
