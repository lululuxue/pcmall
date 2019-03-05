<?php

defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * 获取全局token
 */

/**
 * 获取微信所有用户信息
 */
if( ! function_exists('get_userinfo')) {
    function get_userinfo()
    {
        $appid  = config_item('QQ_APPID');//appid
        $secret = config_item('QQ_APPKEY');//secret

        $CI = &get_instance();

        $user_data = json_decode(decrypt($CI->input->cookie('qq_userinfo')), true);

        if(empty($user_data)){
            $code = $CI->input->get('code',true);
            if(empty($code))
            {
                $states = md5(uniqid(rand(), TRUE));
                cache('save', 'state', $states, 5*60);
                $redirect_uri = urlencode(get_now_url());//授权后的跳转连接
                $url = "https://graph.qq.com/oauth2.0/authorize?response_type=code&client_id=". $appid . "&redirect_uri=" . $redirect_uri . "&state=".$states;
                //$url = "https://graph.qq.com/oauth2.0/token?grant_type=authorization_code&client_id=".$appid."&client_secret=".$appid."&code=".$code."&redirect_uri=".$redirect_uri;
                header("location:$url");exit;
            }
            if($_REQUEST['state'] ==  cache('get', 'state')){
                $redirect_uri = urlencode(get_now_url());//授权后的跳转连接
                $url = "https://graph.qq.com/oauth2.0/token?grant_type=authorization_code&client_id=".$appid."&client_secret=".$secret."&code=".$code."&redirect_uri=".$redirect_uri;
                $response = file_get_contents($url);
                //如果返回callback
                if(strpos($response,"callback") !== false) {
                    $lpos = strpos($response, "(");
                    $rpos = strrpos($response, ")");
                    $response  = substr($response, $lpos + 1, $rpos - $lpos -1);
                    $msg = json_decode($response);
                }

                //成功返回access_token，expires_in，refresh_token，使用access_token获取用户的openid
                $params = array();
                parse_str($response, $params);                             //将字符串解析到数组中
                $graph_url = "https://graph.qq.com/oauth2.0/me?access_token=".$params['access_token'];
                $str  = file_get_contents($graph_url);
                //判断是否返回callback
                if (strpos($str, "callback") !== false)
                {
                    $lpos = strpos($str, "(");
                    $rpos = strrpos($str, ")");
                    $str  = substr($str, $lpos + 1, $rpos - $lpos -1);
                }
                $user = json_decode($str);

                if(isset($user->error)) {
                    return false;
                }
                $user_data_url = "https://graph.qq.com/user/get_user_info?access_token={$params['access_token']}&oauth_consumer_key={$appid}&openid={$user->openid}&format=json";

                $user_data = file_get_contents($user_data_url);//此为获取到的user信息
                $user_data = json_decode($user_data);
                if($user->openid ==''){
                    msg('用户信息获取失败', 'stop');
                }else{
                    $CI->input->set_cookie('qq_userinfo', encrypt(json_encode($user_data)), 3600*24);
                }
            }
        }
        //查询用户信息,没有用户信息就注册新用户
        $qq_member = reg_qq_member($user_data,$user->openid );
        return $qq_member;//启用微信登录
        //return $user_data;//不启用微信登录

    }
}

/**
 * 注册微信用户信息
 */
if (!function_exists('reg_qq_member')) {
    function reg_qq_member($userinfo,$openid )
    {
        $CI = &get_instance();
        if(!empty($userinfo)) {
            $member_data = array();
            //获取登录信息
            if(config_item('safe_type')=='cookie') {
                $m_id = (int)decrypt($CI->input->cookie('m_id', true));
            } else {
                $m_id = (int)$CI->session->userdata('m_id');
            }
            if (empty($m_id)) {
                $CI->load->model('loop_model');
                //查新第三方用户是否存在
                $member_oauth = $CI->loop_model->get_where('member_oauth', array('oauth_type'=>'wechat','oauth_id'=>$openid));
                if (!empty($member_oauth)) {
                    //存在用户信息
                    $m_id = $member_oauth['m_id'];
                } else {
                    $flag_user = decrypt($CI->input->cookie('flag_user'));//推荐人id
                    if($userinfo->gender){
                        $sex = $userinfo->gender == '女' ? 2 : 1;
                    }else{
                        $sex = 0;
                    }
                    //不存在用户开始注册
                    $member_data = array(
                        'username'   => 'qq_'.date('mdHis', time()).get_rand_num('str', 5),
                        'password'   => get_rand_num('str', 15),
                        'headimgurl' => $userinfo->figureurl_2,
                        'flag_user'  => $flag_user,
                        'full_name'  => $userinfo->nickname,
                        'sex'        => $sex,
                    );
                    $CI->load->model('member/user_model');
                    $res = $CI->user_model->update($member_data);
                    if (!empty($res)) {
                        //查询用户id
                        $new_member_data = $CI->loop_model->get_where('member', array('username'=>$member_data['username']));
                        if (!empty($new_member_data)) {
                            //绑定第三方用户
                            $member_oauth = array(
                                'oauth_type' => 'qq',
                                'oauth_id'   => $openid,
                                'm_id'       => $new_member_data['id'],
                                'addtime'    => time(),
                            );
                            $oauth_id = $CI->loop_model->insert('member_oauth', $member_oauth);
                            if (empty($oauth_id)) {
                                msg('用户绑定失败');
                            } else {
                                $m_id = $new_member_data['id'];
                            }
                        } else {
                            msg('用户信息查询失败');
                        }
                    } else {
                        msg('用户注册失败');
                    }
                }
                //设置登录信息
                if (!empty($m_id)) {
                    //设置登录信息
                    if (config_item('safe_type') == 'cookie') {
                        $CI->input->set_cookie('m_id', encrypt($m_id), config_item('safe_time'));
                    } else {
                        $CI->session->set_userdata('m_id', $m_id);
                    }
                } else {
                    msg('用户信息不存在');
                }
            }
            return $m_id;
        } else {
            msg('用户信息获取失败');
        }
    }
}
