<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class MY_Controller extends CI_Controller {

    private $whiteList = [
        /*
        'api_mobile/order/cart/add',
        'api_mobile/order/cart/update',
        'api_mobile/order/cart/delete',
        'api_mobile/order/cart/clear',
        'api_mobile/order/cart/cart_count',
        'api_mobile/order/cart/cart_list',
        'api_mobile/order/order/order_list',
        'api_mobile/order/order/order_detail',
        'api_mobile/order/order/used_order',
        'api_mobile/member/goods_favorite/favorite_list',
        'api_mobile/member/goods_favorite/add_favorite',
        'api_mobile/member/goods_favorite/delete_favorite',
        'api_mobile/member/shop_favorite/favorite_list',
        'api_mobile/member/shop_favorite/add_favorite',
        'api_mobile/member/shop_favorite/delete_favorite',
        'api_mobile/reset_password/reset_password',
        'api_mobile/member/address/address_add',
        'api_mobile/member/address/address_delete',
        'api_mobile/member/address/address_detail',
        'api_mobile/member/address/address_list',
        'api_mobile/member/brand/brand_list',
        'api_mobile/member/brand/concern_brand',
        'api_mobile/member/brand/del_concern_brand',
        'api_mobile/member/coupons/coupons_list',
        'api_mobile/member/coupons/exchange_save',
        'api_mobile/member/info/headimgurl_save',
        'api_mobile/member/info/info',
        'api_mobile/member/info/info_edit',
        'api_mobile/member/info/upload',
        'api_mobile/member/point/index',
        */
        'api_mobile/user/reg/verify_code',//获取验证码(图片验证码)
        'api_mobile/user/reg/send',//发送验证码（手机验证码）
        'api_mobile/user/reg/user_reg',//用户注册,用户登入
        'api_mobile/goods/index/ad',//广告列表
        'api_mobile/goods/index/index',//首页列表
        'api_mobile/goods/product/goods_list',//商品列表
        'api_mobile/goods/product/product',//商品详情
        'api_mobile/pay/do_pay',//支付
        'api_mobile/pay/callback/2/mobile',

    ];
	
	public function __construct()
	{
		parent::__construct();
        $this->load->model('loop_model');
        $this->validateRequest();
        $brand_id = cache('get','brand_id');
        assign('brand_id',$brand_id);
		
	}

    public function validateRequest()
    {
        header('Access-Control-Allow-Origin: *');
        // 响应类型
        header('Access-Control-Allow-Methods:GET, POST, PUT,DELETE');
        // 响应头设置
        header('Access-Control-Allow-Headers:x-requested-with,content-type');
        $formData = $_REQUEST;
        $url = $formData['url'];
        if (!in_array($url, $this->whiteList)) {
            if (empty($formData['m_id']) || empty($formData['timestamp']) ||
                empty($formData['sign']) || empty($formData['token'])
            ) {
                error_json('参数缺失');
            }

            $user = $this->loop_model->get_where('user',  array('Id' => $formData['m_id'],'is_delete'=>0));//var_dump($user);exit;
            if(!$user) {
                error_json('用户不存在');
            }
            //暂时关闭token验证

            if (!$this->tokenVerify($formData['token'], $formData['m_id'])) {
                error_json('令牌失效');
            }


        }

        // sign verify
        /*暂时不验证签名
        if (!$this->signVerify($formData, $formData['sign'],$formData['m_id'])) {
            error_json('签名错误');
         }
        */
        return true;
    }

    public function tokenVerify($token, $m_id)
    {
        $userToken = cache('get', 'user_token_'.$m_id);
        if (empty($userToken)) {
            //error_json('令牌失效');
            return false;
        }
        if (!empty($userToken) && $token !== $userToken) {
            error_json('token different');
            return false;
        }
        return true;
    }

    public function signVerify($formData, $sign,$m_id)
    {
        $checkSign = self::calculateSignature($formData, cache('get', 'user_token_'.$m_id));
        return ($checkSign === $sign);
    }

    public static function paramsFilter($params)
    {
        $paramsFilter = array();
        while (list ($key, $value) = each ($params)) {
            if ($key == "r" || $key == "sign" || $key == "ui" || $key == "thumb"|| $value == "") {
                continue;
            } else {
                $paramsFilter[$key] = $params[$key];
            }
        }
        return $paramsFilter;
    }

    public static function paramsSort($params)
    {
        ksort($params);
        reset($params);
        return $params;
    }

    public static function createLinkString($params)
    {
        $string = "";
        while (list ($key, $val) = each($params)) {
            $string .= $key . "=" . $val . "&";
        }
        //去掉最后一个&字符
        $string = substr($string, 0, count($string) - 2);

        //如果存在转义字符，那么去掉转义
        if (get_magic_quotes_gpc()) {
            $string = stripslashes($string);
        }

        return $string;
    }

    public static function calculateSignature($formData, $salt)
    {
        $filterParams = self::paramsFilter($formData);
        $sortParams = self::paramsSort($filterParams);
        $prepareParams = self::createLinkString($sortParams);
        return md5($prepareParams . $formData['timestamp'] . $salt);
    }

}