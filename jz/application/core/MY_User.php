<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class MY_User extends CI_Controller {
	
	public function __construct()
	{
		parent::__construct();
        $this->load->model('loop_model');

	}

    public function curl($url, $params = false, $isPost = 0)
    {
        $httpInfo = array();
        $ch = curl_init();

        //curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 60);
        //curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        //curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        if ($isPost) {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
            curl_setopt($ch, CURLOPT_URL, $url);
        }
        else {
            if($params) {
                curl_setopt($ch, CURLOPT_URL, $url.'?'.$params);
            } else {
                curl_setopt($ch, CURLOPT_URL, $url);
            }
        }
        $response = curl_exec($ch);
        if ($response === FALSE) {
            return false;
        }
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $httpInfo = array_merge($httpInfo, curl_getinfo($ch));
        curl_close($ch);
        return $response;
    }

    /**
     * @param $url
     * @param array|null $data
     * @param int $isPost
     * @param boolean $throw
     * @return array|mixed
     */
    public function apiRequest($url, array $data = null, $isPost = 0, $throw = false)
    {
        $url = $this->config->item("website_mobile").$url;
        $isPost = (!is_null($data) && is_array($data));
        $data['uid'] = $order_no = cache('get', 'm_id');//取的订单缓存;
        $data['timestamp'] = time();
        $data['token'] = session('index.token') ? session('index.token') : session('index.token');
        $data['sign'] = ApiRequest::calculateSignature($data, '');
        $data['os'] = 'pc';

        //$response = $isPost ? YKX::curlPost($url, $data) : YKX::curlGet($url);
        $response = YKX::curl($url, $data, $isPost);//return $response;
        if(is_string($response)){
            $list = json_decode($response);
            if(!is_object($list)){
                header('Location: /404');
            }
        }

        $response = (is_string($response)) ? json_decode($response) : $response;
        if ($throw && $response->code != 200) {
            $response->msg = $response->message;

        }

        return $response;
    }
}