<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Welcome extends CI_Controller
{

    private $admin_data;//后台用户登录信息

    public function __construct()
    {
        parent::__construct();
        $this->load->helpers('manager_helper');
        $this->admin_data = manager_login();
        assign('admin_data', $this->admin_data);
    }

    /**
     * 后台首页
     */
    public function index()
    {
        display('/index/index.html');
    }

    /**
     * 后台头部
     */
    public function header()
    {
        //assign('admin_data', $this->admin_data);
        display('/index/header.html');
    }

    /**
     * 后台菜单
     */
    public function menu($type = 'service')
    {
        //菜单查询
        $this->load->model('/manager/menu_model');
        $menu_list = array();
        if($this->admin_data['role_id'] != 0){
/*
            if(stripos($this->admin_data['role'], 'service/')!== false){
                $type = 'service';
            }elseif(stripos($this->admin_data['role'], 'work/')!== false){
                $type = 'work';
            }elseif(stripos($this->admin_data['role'], 'check/')!== false){
                $type = 'check';
            }elseif(stripos($this->admin_data['role'], 'area/')!== false){
                $type = 'area';
            }elseif(stripos($this->admin_data['role'], 'member/')!== false){
                $type = 'member';
            }elseif(stripos($this->admin_data['role'], 'personnel/')!== false){
                $type = 'personnel';
            }elseif(stripos($this->admin_data['role'], 'finance/')!== false){
                $type = 'finance';
            }elseif(stripos($this->admin_data['role'], 'statistics/')!== false){
                $type = 'statistics';
            }elseif(stripos($this->admin_data['role'], 'cat/')!== false){
                $type = 'cat';
            }elseif(stripos($this->admin_data['role'], 'role/')!== false){
                $type = 'role';
            }
*/
            if(stripos($this->admin_data['role'], $type.'/')!== false){
                $type = $type;
            }
            $menu_list = $this->menu_model->get_menu($type);//菜单列表
        }else{
            $menu_list = $this->menu_model->get_menu($type);//菜单列表
        }
        assign('menu_list', $menu_list);

        //assign('admin_data', $this->admin_data);
        display('/index/menu.html');
    }

    /**
     * 后台右侧首页
     */
    public function main()
    {
        display('/list.html');
    }

    /**
     * 获取分店
     */
    public function sub_shop()
    {
        $name = $this->input->post('name');
        $where_data['where']['a.name'] = $name;
        $where_data['select']= array('b.*');
        $where_data['join']= array(
            array('shop a','a.id=b.reid','left')
        );
        $shop_list = $this->loop_model->get_list('shop b',$where_data);
        error_json($shop_list);
    }


}
