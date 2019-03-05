<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Setting extends CI_Controller
{

    private $admin_data;//后台用户登录信息

    public function __construct()
    {
        parent::__construct();
        $this->load->helpers('shop_helper');
        $this->shop_data = shop_login();
        assign('shop_data', $this->shop_data);
        $this->load->model('loop_model');
        $this->shop_id = $this->shop_data['id'];
    }

    /**
     * 编辑
     */
    public function index()
    {
        $member_shop               = $this->loop_model->get_where('member_shop', array('m_id' => $this->shop_id));
        $member_shop['banner_url'] = json_decode($member_shop['banner_url'], true);
        assign('item', $member_shop);
        $this->load->helpers('upload_helper');//加载上传文件插件
        display('/system/setting/index.html');
    }

    /**
     * 保存数据
     */
    public function save()
    {
        if (is_post()) {
            $data_post        = $this->input->post(NULL, true);
            $member_shop_data = $this->loop_model->get_where('member_shop', array('m_id' => $this->shop_id));
            //banner
            if (!empty($data_post['banner_name'])) {
                foreach ($data_post['banner_name'] as $v => $k) {
                    $banner_url[] = array(
                        'name' => $k,
                        'link' => $data_post['banner_link'][$v],
                        'url'  => $data_post['banner_url'][$v],
                    );
                }
            }

            $update_data = array(
                'shop_name'    => $data_post['shop_name'],
                'logo'         => $data_post['logo'],
                'tel'          => $data_post['tel'],
                'email'        => $data_post['email'],
                'customer_url' => $data_post['customer_url'],
                'prov'         => $data_post['prov'],
                'city'         => $data_post['city'],
                'area'         => $data_post['area'],
                'address'      => $data_post['address'],
                'desc'         => $data_post['desc'],
                'banner_url'   => json_encode($banner_url),
            );

            if (!empty($data_post['business_license']) && $member_shop_data['status'] == 2) $update_data['business_license'] = $data_post['business_license'];

            $res = $this->loop_model->update_where('member_shop', $update_data, array('m_id' => $this->shop_id));
            if (!empty($res)) {
                error_json('y');
            } else {
                error_json('保存失败');
            }
        } else {
            error_json('提交方式错误');
        }

    }
}
