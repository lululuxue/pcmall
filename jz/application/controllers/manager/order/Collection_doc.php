<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Collection_doc extends CI_Controller
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
        $export   = $this->input->get('export');//是否导出
        $pagesize = 20;//分页大小
        $page     = (int)$this->input->get('per_page');
        $page <= 1 ? $page = 1 : $page = $page;//当前页数
        //搜索条件start
        $username = $this->input->post_get('username');
        if (!empty($username)) $where_data['where']['m.username'] = $username;
        $search_where = array(
            'username' => $username,
        );
        assign('search_where', $search_where);
        //搜索条件end
        $where_data['select'] = 'doc.*,o.order_no,m.username';
        $where_data['join']   = array(
            array('member as m', 'doc.m_id=m.id'),
            array('order as o', 'doc.order_id=o.id')
        );
        //查到数据
        if (empty($export)) {
            $list_data = $this->loop_model->get_list('order_collection_doc as doc', $where_data, $pagesize, $pagesize * ($page - 1), 'doc.id desc');//列表
            foreach ($list_data as $key) {
                $key['amount'] = format_price($key['amount']);
                $list[]        = $key;
            }
            assign('list', $list);
            //开始分页start
            $all_rows = $this->loop_model->get_list_num('order_collection_doc as doc', $where_data);//所有数量
            assign('page_count', ceil($all_rows / $pagesize));
            //开始分页end
            display('/order/collection_doc/list.html');
        } else {
            $list = $this->loop_model->get_list('order_collection_doc as doc', $where_data, '', '', 'doc.id desc');//列表
            $this->load->library('PHPExcel');
            $this->load->library('PHPExcel/IOFactory');
            $resultPHPExcel = new PHPExcel();
            $i              = 1;
            $resultPHPExcel->getActiveSheet()->setCellValue('A' . $i, '订单号');
            $resultPHPExcel->getActiveSheet()->setCellValue('B' . $i, '用户名');
            $resultPHPExcel->getActiveSheet()->setCellValue('C' . $i, '金额');
            $resultPHPExcel->getActiveSheet()->setCellValue('D' . $i, '时间');
            foreach ($list as $key) {
                $i++;
                $resultPHPExcel->getActiveSheet()->setCellValue('A' . $i, $key['order_no']);
                $resultPHPExcel->getActiveSheet()->setCellValue('B' . $i, $key['username']);
                $resultPHPExcel->getActiveSheet()->setCellValue('C' . $i, '￥'.format_price($key['amount']));
                $resultPHPExcel->getActiveSheet()->setCellValue('D' . $i, date('Y-m-d H:i:s',$key['addtime']));
            }
            $outputFileName = "收款单.xls";
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

}
