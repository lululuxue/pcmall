<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class MY_Controller extends CI_Controller {
	
	public function __construct()
	{
		parent::__construct();

		/*$this->timestamp = time();

        $directory = str_replace('/','',trim($this->router->fetch_directory(), '/'));
        $this->controller = $this->router->fetch_class();
        $this->method = $this->router->fetch_method();

        //判断用户是否登录，未登陆则跳转到登录页面
        $user_sess = $this->session->userdata('user_sess');
        $this->login_user_id = isset($user_sess['uid'])?$user_sess['uid']:0;
        $this->login_user_name = isset($user_sess['user_name'])?$user_sess['user_name']:'';
        if (!$this->login_user_id && !($this->router->fetch_class()=='user' && $this->router->fetch_method()=='login')) {
		
			if($this->controller!='nursery' &&  $this->method!='purchase_order'){
				$redirect = $this->uri->uri_string();
				if ( $_SERVER['QUERY_STRING']) {
					$redirect .= '?' . $_SERVER['QUERY_STRING'];
				}
				
				redirect($this->config->item('base_url') . '/user/user/login?redirect=' . base64_encode($redirect));
			}
        }*/
		
	}
}