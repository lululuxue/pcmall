<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Online_recharge extends CI_Controller
{

    private $admin_data;//后台用户登录信息

    public function __construct()
    {
        parent::__construct();
        $this->load->helpers('manager_helper');
        $this->admin_data = manager_login();
        assign('admin_data', $this->admin_data);
        $this->load->model('loop_model');
    }

    /**
     * 列表
     */
    public function index()
    {
        $export   = $this->input->get('export');
        $pagesize = 20;//分页大小
        $page     = (int)$this->input->get('per_page');
        $page <= 1 ? $page = 1 : $page = $page;//当前页数
        //搜索条件start
        //用户名
        $username = $this->input->post_get('username');
        if (!empty($username)) $where_data['where']['m.username'] = $username;
        //开始时间
        $start_time = $this->input->post_get('start_time');
        if (!empty($start_time)) $where_data['where']['mo.addtime>='] = strtotime($start_time . ' 00:00:00');
        //结束时间
        $end_time = $this->input->post_get('end_time');
        if (!empty($end_time)) $where_data['where']['mo.addtime<='] = strtotime($start_time . ' 23:59:59');
        //关键字
        $search_where = array(
            'start_time' => $start_time,
            'end_time'   => $end_time,
            'username'   => $username,
        );
        assign('search_where', $search_where);
        //搜索条件end
        $where_data['where']['mo.status'] = 1;
        $where_data['join']               = array(
            array('member as m', 'mo.m_id=m.id')
        );
        $where_data['select']             = 'mo.*,m.username';
        //查到数据
        if ($export != 1) {
            $list_data = $this->loop_model->get_list('member_user_online_recharge as mo', $where_data, $pagesize, $pagesize * ($page - 1), 'mo.id desc');//列表
            foreach ($list_data as $key) {
                $key['amount'] = format_price($key['amount']);
                $list[]        = $key;
            }
            assign('list', $list);
            //开始分页start
            $all_rows = $this->loop_model->get_list_num('member_user_online_recharge as mo', $where_data);//所有数量
            assign('page_count', ceil($all_rows / $pagesize));
            //开始分页end

            display('/report/online_recharge/list.html');
        } else {
            //需要导出
            $list = $this->loop_model->get_list('member_user_online_recharge as mo', $where_data, '', '', 'mo.id desc');//列表
            $this->load->library('PHPExcel');
            $this->load->library('PHPExcel/IOFactory');
            $resultPHPExcel = new PHPExcel();
            $i = 1;
            $resultPHPExcel->getActiveSheet()->setCellValue('A'.$i, '会员名');
            $resultPHPExcel->getActiveSheet()->setCellValue('B'.$i, '金额');
            $resultPHPExcel->getActiveSheet()->setCellValue('C'.$i, '支付方式');
            $resultPHPExcel->getActiveSheet()->setCellValue('D'.$i, '时间');
            $i++;
            foreach ($list as $key) {
                $resultPHPExcel->getActiveSheet()->setCellValue('A'.$i, $key['username']);
                $resultPHPExcel->getActiveSheet()->setCellValue('B'.$i, format_price($key['amount']));
                $resultPHPExcel->getActiveSheet()->setCellValue('C'.$i, $key['payment_name']);
                $resultPHPExcel->getActiveSheet()->setCellValue('D'.$i, date('Y-m-d H:i:s',$key['addtime']));
                $i++;
            }
            $outputFileName = "充值记录.xls";
            $xlsWriter      = new PHPExcel_Writer_Excel5($resultPHPExcel);
            header("Content-Type: application/force-download");
            header("Content-Type: application/octet-stream");
            header("Content-Type: application/download");
            header('Content-Disposition:inline;filename="' . $outputFileName . '"');
            header("Content-Transfer-Encoding: binary");
            header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
            header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
            header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
            header("Pragma: no-cache");
            $xlsWriter->save("php://output");
        }
    }

    /**
     * 彻底删除数据
     */
    public function delete()
    {
        $id = $this->input->post('id', true);
        if (empty($id)) error_json('id错误');
        $res = $this->loop_model->delete_id('member_user_online_recharge', $id);
        if (!empty($res)) {
            if (is_array($id)) $id = join(',', $id);
            admin_log_insert('删除充值单' . $id);
            error_json('y');
        } else {
            error_json('删除失败');
        }
    }
}
