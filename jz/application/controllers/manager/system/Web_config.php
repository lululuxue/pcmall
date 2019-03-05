<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Web_config extends CI_Controller
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
     * 列表
     */
    public function index()
    {
        $this->load->helpers('upload_helper');//加载上传文件插件
        display('/system/web_config/index.html');
    }


    /**
     * 添加编辑
     */
    public function save()
    {
        error_json('y');
        
        $file_path = APPPATH . "/config/web_config.php";
        if (!is_writeable($file_path)) {
            error_json('文件写入权限不足');
        }
        $str = "<?php\r\n";
        $str .= "defined('BASEPATH') OR exit('No direct script access allowed');\r\n";
        $data_post = $this->input->post(NULL, true);
        if (!empty($data_post)) {
            foreach ($data_post as $val => $key) {
                $str .= "$" . "config['$val'] = '" . str_replace("'", '"', $key) . "';\r\n";
            }
        }
        file_put_contents($file_path, $str);
        admin_log_insert('修改了站点设置');
        error_json('y');
    }

}
