<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Reg extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->model('loop_model');
    }

    /**
     * 开始注册
     */
    public function user_reg()
    {
        header('Access-Control-Allow-Origin: *');
        // 响应类型
        header('Access-Control-Allow-Methods:GET, POST, PUT,DELETE');
        // 响应头设置
        header('Access-Control-Allow-Headers:x-requested-with,content-type');

            $username = trim($this->input->get('username'));
            $code     = (int)trim($this->input->get('code'));
            if (!empty($username) && !empty($code)) {
                //验证码验证

                if (config_item('sms_open') == '1') {
                    $this->load->library('sms_send_tmp');
                    $code_res = $this->sms_send_tmp->validation_code($username, $code);//短信验证
                } else {
                    //不需要短信验证
                    $code_res = 'y';
                }

                $code_res = 'y';
                $this->load->helpers('form_validation_helper');
                if ($code_res != 'y') {
                    error_json($code_res);
                } else if (!is_mobile($username)) {
                    error_json('用户名必须是手机号码');
                } else {
                    $flag_user = decrypt($this->input->cookie('flag_user'));//推荐人id
                    //注册信息
                    $user_data = array(
                        'username'  => $username,
                        'name'  => $username,
                        'flag_user' => $flag_user,
                    );
                    $this->load->model('user_model');
                    $res = $this->user_model->update($user_data);
                    if (!empty($res)) {
                        if ($res == 'y') {
                            $member_data = $this->loop_model->get_where('user', array('username' => $username));
                            $this->loop_model->update_where('user', array('endtime' => time()), array('Id' => $member_data['Id']));
                            $salt = substr(uniqid(), -6);
                            $token = md5($member_data['Id'] . $member_data['password'] . $salt);
                            $tokenData = [
                                'm_id'      => $member_data['Id'],
                                'token'    => $token,
                                'salt'     => $salt,
                                'expire'   => time() + 5 * 24 * 3600,
                            ];
                            cache('save', 'user_token_'.$member_data['Id'] , $token,time() + 5 * 24 * 3600);//保存token

                            cache('del', 'sms_code' . $username);//删除验证码
                            error_json($tokenData);
                        }
                        error_json($res);
                    } else {
                        error_json('注册失败');
                    }
                }
            } else {
                error_json('账号和验证码不能为空');
            }
    }

    //图片验证码
    public function verify_code($width = '80', $height = '30')
    {
        header('Access-Control-Allow-Origin: *');
        // 响应类型
        header('Access-Control-Allow-Methods:GET, POST, PUT,DELETE');
        // 响应头设置
        header('Access-Control-Allow-Headers:x-requested-with,content-type');
        /*
        $mobile = trim($this->input->get('mobile'));
        $this->load->helpers('form_validation_helper');
        if (!is_mobile($mobile)) {
            error_json('手机号码格式错误请不要加0或86');
        }
        */
        $app_path = explode(DIRECTORY_SEPARATOR, APPPATH);
        $this->load->helper('captcha');
        $vals = array(
            'img_path'    => APPPATH . 'cache/captcha/',
            'img_url'     => site_url('/api/pic?url=/' . $app_path[count($app_path) - 2] . '/cache/captcha'),
            'font_path'   => './path/to/fonts/texb.ttf',
            'img_width'   => $width,
            'img_height'  => $height,
            'expiration'  => 90,
            'word_length' => 4,
            'font_size'   => 16,
            'img_id'      => 'Imageid',
            'pool'        => '0123456789abcdefghjklmnpqrstuvwxyzABCDEFGHIJKLMNPQRSTUVWXYZ',

            'colors' => array(
                'background' => array(255, 255, 255),
                'border'     => array(rand(1, 255), rand(1, 255), rand(1, 255)),
                'text'       => array(0, 0, 0),
                'grid'       => array(255, 40, 40)
            )
        );
        $cap  = create_captcha($vals);
        //$this->session->set_userdata('imgcode', $cap['word']);
        cache('save', 'imgcode', $cap['word'], 7*24*3600, 600);//写入缓存
        echo file_get_contents($vals['img_path'].$cap['filename']);
    }

    /**
     * 发送手机验证码
     */
    public function send()
    {
        header('Access-Control-Allow-Origin: *');
        // 响应类型
        header('Access-Control-Allow-Methods:GET, POST, PUT,DELETE');
        // 响应头设置
        header('Access-Control-Allow-Headers:x-requested-with,content-type');
        $mobile = trim($this->input->get('mobile', true));
        $str     = trim($this->input->get('str'));
        if (!$str) {
            error_json('参数缺失');
        }
        $origin = $_SERVER['HTTP_ORIGIN'] ;
        $arr = [
            'http://dev.mjkx.yaokexing.com'
        ];
        if(!in_array($origin,$arr)){
            error_json('请不要恶意刷验证码');
        }
        $this->load->helpers('form_validation_helper');
        if (is_mobile($mobile)) {
//            $this->load->library('sms_send_tmp');
//            $res  = $this->sms_send_tmp->code($mobile);
            //$this->load->library('aliyun/api_demo/SmsDemo');
            $temp = 'SMS_146750110';//身份验证码
            $temp1 = 'SMS_146750109';//登入操作
            $temp2 = 'SMS_146750107';//注册操作
            $member_data = $this->loop_model->get_where('user', array('username' => $mobile));
            if($member_data){
                $tmp = $temp1;
            }else{
                $tmp = $temp2;
            }
            $this->load->library('SmsDemo');
            $res  = SmsDemo::sendSms($mobile,$tmp);
            if ($res->Code == 'OK') {
                error_json('y');
            } else {
                error_json($res->Message);
            }
        } else {
            error_json('手机号码格式错误请不要加0或86');
        }
    }

    /**
     * 注册成为企业用户
     */
    public function enterprise_user()
    {
        header('Access-Control-Allow-Origin: *');
        // 响应类型
        header('Access-Control-Allow-Methods:GET, POST, PUT,DELETE');
        // 响应头设置
        header('Access-Control-Allow-Headers:x-requested-with,content-type');

        $username = trim($this->input->get('username'));
        if (!empty($username) ) {

            $this->load->helpers('form_validation_helper');
            if (!is_mobile($username)) {
                error_json('用户名必须是手机号码');
            } else {
                $flag_user = decrypt($this->input->cookie('flag_user'));//推荐人id
                //注册信息
                $user_data = array(
                    'username'  => $username,
                    'name'  => $username,
                    'flag_user' => $flag_user,
                );
                $this->load->model('user_model');
                $res = $this->user_model->update($user_data);
                if (!empty($res)) {
                    if ($res == 'y') {
                        $member_data = $this->loop_model->get_where('user', array('username' => $username));
                        $this->loop_model->update_where('user', array('endtime' => time()), array('Id' => $member_data['Id']));
                        $salt = substr(uniqid(), -6);
                        $token = md5($member_data['Id'] . $member_data['password'] . $salt);
                        $tokenData = [
                            'm_id'      => $member_data['Id'],
                            'token'    => $token,
                            'salt'     => $salt,
                            'expire'   => time() + 5 * 24 * 3600,
                        ];
                        cache('save', 'user_token_'.$member_data['Id'] , $token,time() + 5 * 24 * 3600);//保存token

                        cache('del', 'sms_code' . $username);//删除验证码
                        error_json($tokenData);
                    }
                    error_json($res);
                } else {
                    error_json('注册失败');
                }
            }
        } else {
            error_json('账号和验证码不能为空');
        }
    }
    /**
     * 注册成为企业用户
     */
    public function per_user()
    {
        header('Access-Control-Allow-Origin: *');
        // 响应类型
        header('Access-Control-Allow-Methods:GET, POST, PUT,DELETE');
        // 响应头设置
        header('Access-Control-Allow-Headers:x-requested-with,content-type');

        $name = trim($this->input->get('full_name'));
        $phone = trim($this->input->get('tel'));
        if (!empty($phone) ) {

            $this->load->helpers('form_validation_helper');
            if (!is_mobile($phone)) {
                error_json('手机号码格式不正确');
            } else {
                $flag_user = decrypt($this->input->cookie('flag_user'));//推荐人id
                //注册信息
                $user_data = array(
                    'username'  => $phone,
                    'name'  => $name,
                    'flag_user' => $flag_user,
                    'addtime' => time(),
                    'is_big' => $this->input->get('is_big'),
                    'address' => $this->input->get('province').','.$this->input->get('city').','.$this->input->get('area').','.$this->input->get('address'),
                );
                $this->load->model('user_model');
                if($this->input->get('company')){
                    $user_data['company'] = $this->input->get('company');
                }

                $res = $this->user_model->update_per($user_data);
                if (!empty($res)) {
                    if ($res == 'y') {
                        error_json('y');
                    }else{
                        error_json($res);
                    }
                } else {
                    error_json('注册失败');
                }
            }
        } else {
            error_json('手机号不能为空');
        }
    }


}
