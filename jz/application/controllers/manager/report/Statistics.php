<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Statistics extends CI_Controller
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
     * 会员注册统计
     */
    public function user_reg()
    {
        $export = $this->input->get('export');
        //开始时间
        $start_time = $this->input->post_get('start_time');
        if (empty($start_time)) $start_time = date('Y-m-d', time() - (86400 * 7));
        if (!empty($start_time)) $where_data['where']['addtime>='] = strtotime($start_time . ' 00:00:00');
        //结束时间
        $end_time = $this->input->post_get('end_time');
        if (empty($end_time)) $end_time = date('Y-m-d', time());
        if (!empty($end_time)) $where_data['where']['addtime<='] = strtotime($end_time . ' 23:59:59');

        $search_where = array(
            'start_time' => $start_time,
            'end_time'   => $end_time,
        );
        assign('search_where', $search_where);
        $time_format = self::parse_condition($start_time, $end_time);

        $where_data['select']   = 'count(*) as all_nums,DATE_FORMAT(FROM_UNIXTIME(`addtime`),"' . $time_format . '") as a_time';
        $where_data['group_by'] = 'DATE_FORMAT(FROM_UNIXTIME(`addtime`),"' . $time_format . '")';
        //查到数据
        $list_data = $this->loop_model->get_list('member_user', $where_data, '', '', '');//列表

        if ($export == 1) {
            //需要导出
            $excel_title = array('人数', '时间');
            self::export('会员注册统计', $list_data, $excel_title);
        } else {
            foreach ($list_data as $key) {
                $categories[] = $key['a_time'];
                $value[]      = $key['all_nums'];
            }
            assign('list', array('categories' => $categories, 'value' => $value));
            display('/report/statistics/user_reg.html');
        }
    }

    /**
     * 平均消费统计
     */
    public function consumption_avg()
    {
        $export = $this->input->get('export');
        //开始时间
        $start_time = $this->input->post_get('start_time');
        if (empty($start_time)) $start_time = date('Y-m-d', time() - (86400 * 7));
        if (!empty($start_time)) $where_data['where']['addtime>='] = strtotime($start_time . ' 00:00:00');
        //结束时间
        $end_time = $this->input->post_get('end_time');
        if (empty($end_time)) $end_time = date('Y-m-d', time());
        if (!empty($end_time)) $where_data['where']['addtime<='] = strtotime($end_time . ' 23:59:59');

        $search_where = array(
            'start_time' => $start_time,
            'end_time'   => $end_time,
        );
        assign('search_where', $search_where);
        $time_format = self::parse_condition($start_time, $end_time);

        $where_data['select']   = 'sum(amount)/count(*) as all_nums,DATE_FORMAT(FROM_UNIXTIME(`addtime`),"' . $time_format . '") as a_time';
        $where_data['group_by'] = 'DATE_FORMAT(FROM_UNIXTIME(`addtime`),"' . $time_format . '")';
        //查到数据
        $list_data = $this->loop_model->get_list('order_collection_doc', $where_data, '', '', '');//列表

        if ($export == 1) {
            //需要导出
            foreach ($list_data as $key) {
                $key['all_nums'] = format_price($key['all_nums']);
                $list[] = $key;
            }
            $excel_title = array('平均消费', '时间');
            self::export('平均消费统计', $list, $excel_title);
        } else {
            foreach ($list_data as $key) {
                $categories[] = $key['a_time'];
                $value[]      = format_price($key['all_nums']);
            }
            assign('list', array('categories' => $categories, 'value' => $value));
            display('/report/statistics/consumption_avg.html');
        }
    }

    /**
     * 每日销售额统计
     */
    public function consumption_sum()
    {
        $export = $this->input->get('export');
        //开始时间
        $start_time = $this->input->post_get('start_time');
        if (empty($start_time)) $start_time = date('Y-m-d', time() - (86400 * 7));
        if (!empty($start_time)) $where_data['where']['addtime>='] = strtotime($start_time . ' 00:00:00');
        //结束时间
        $end_time = $this->input->post_get('end_time');
        if (empty($end_time)) $end_time = date('Y-m-d', time());
        if (!empty($end_time)) $where_data['where']['addtime<='] = strtotime($end_time . ' 23:59:59');

        $search_where = array(
            'start_time' => $start_time,
            'end_time'   => $end_time,
        );
        assign('search_where', $search_where);
        $time_format = self::parse_condition($start_time, $end_time);

        $where_data['select']   = 'sum(amount) as all_nums,DATE_FORMAT(FROM_UNIXTIME(`addtime`),"' . $time_format . '") as a_time';
        $where_data['group_by'] = 'DATE_FORMAT(FROM_UNIXTIME(`addtime`),"' . $time_format . '")';
        //查到数据
        $list_data = $this->loop_model->get_list('order_collection_doc', $where_data, '', '', '');//列表

        if ($export == 1) {
            //需要导出
            foreach ($list_data as $key) {
                $key['all_nums'] = format_price($key['all_nums']);
                $list[] = $key;
            }
            $excel_title = array('销售额', '时间');
            self::export('每日销售额统计', $list, $excel_title);
        } else {
            foreach ($list_data as $key) {
                $categories[] = $key['a_time'];
                $value[]      = format_price($key['all_nums']);
            }
            assign('list', array('categories' => $categories, 'value' => $value));
            display('/report/statistics/consumption_sum.html');
        }
    }

    /**
     * 订单数量统计
     */
    public function order_count()
    {
        $export = $this->input->get('export');
        //开始时间
        $start_time = $this->input->post_get('start_time');
        if (empty($start_time)) $start_time = date('Y-m-d', time() - (86400 * 7));
        if (!empty($start_time)) $where_data['where']['addtime>='] = strtotime($start_time . ' 00:00:00');
        //结束时间
        $end_time = $this->input->post_get('end_time');
        if (empty($end_time)) $end_time = date('Y-m-d', time());
        if (!empty($end_time)) $where_data['where']['addtime<='] = strtotime($end_time . ' 23:59:59');

        $search_where = array(
            'start_time' => $start_time,
            'end_time'   => $end_time,
        );
        assign('search_where', $search_where);
        $where_data['where_in'] = array('status'=>array(2,3,4,5,7,10));
        $time_format = self::parse_condition($start_time, $end_time);

        $where_data['select']   = 'count(*) as all_nums,DATE_FORMAT(FROM_UNIXTIME(`addtime`),"' . $time_format . '") as a_time';
        $where_data['group_by'] = 'DATE_FORMAT(FROM_UNIXTIME(`addtime`),"' . $time_format . '")';
        //查到数据
        $list_data = $this->loop_model->get_list('order', $where_data, '', '', '');//列表

        if ($export == 1) {
            //需要导出
            $excel_title = array('数量', '时间');
            self::export('订单数量统计', $list_data, $excel_title);
        } else {
            foreach ($list_data as $key) {
                $categories[] = $key['a_time'];
                $value[]      = $key['all_nums'];
            }
            assign('list', array('categories' => $categories, 'value' => $value));
            display('/report/statistics/order_count.html');
        }
    }

    /**
     * 导出数据
     * @param str   $doc_title  文档的名称
     * @param array $data       导出数据
     * @param array $list_title 对应excel标题
     */
    private function export($doc_title, $data, $list_title)
    {
        $this->load->library('PHPExcel');
        $this->load->library('PHPExcel/IOFactory');
        $resultPHPExcel = new PHPExcel();
        $letter = array('A','B','C','D','E','F','G','H','I','J','K','L','M','N');
        $i      = 1;
        foreach ($list_title as $vt=>$t) {
            $resultPHPExcel->getActiveSheet()->setCellValue($letter[$vt].$i, $t);
        }
        $i++;
        foreach ($data as $key) {
            foreach (array_values($key) as $v=>$k) {
                $resultPHPExcel->getActiveSheet()->setCellValue($letter[$v]. $i, $k);
            }
            $i++;
        }
        $outputFileName = $doc_title.".xls";
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

    /**
     * 计算时间分段
     * @param str $start_time 开始日期 Y-m-d
     * @param str $end_time   结束日期 Y-m-d
     */
    private function parse_condition($start_time = '', $end_time = '')
    {
        $start_time = strtotime($start_time);
        $end_time   = strtotime($end_time);
        if ($end_time < $start_time) {
            msg('结束时间不能小于开始时间');
        }

        $diff = $end_time - $start_time;
        //按天分组，小于30个天
        if ($diff <= 86400 * 30) {
            $time_format = '%Y-%m-%d';
        } //按月分组，小于24个月
        else if ($diff <= 86400 * 30 * 24) {
            $time_format = '%Y-%m';
        } //按年分组
        else {
            $time_format = '%Y';
        }
        return $time_format;
    }

}
