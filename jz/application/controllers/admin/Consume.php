<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Consume extends CI_Controller
{

    private $admin_data;//后台用户登录信息

    public function __construct()
    {
        parent::__construct();
        $this->load->helpers('admin_helper');
        $this->admin_data = admin_login();
        assign('admin_data', $this->admin_data);
        $this->load->model('loop_model');
    }

    /**
     * 积分统计
     */
    public function index()
    {
        $pagesize = 20;//分页大小
        $page     = (int)$this->input->get('per_page');
        $page <= 1 ? $page = 1 : $page = $page;//当前页数
        //查到数据
        $where['select']= array('sum(a.jifen) as total_jifen,a.id,a.m_id,b.name,b.username');
        $where['join'] = array(
            array('user b','a.m_id=b.Id','left')
        );
        $list = $this->loop_model->get_group_list('jifen a',$where,$pagesize, $pagesize * ($page - 1), 'a.m_id','a.id');//列表
        assign('list', $list);//print_r($list);
        $all_rows = $this->loop_model->get_list_num('jifen a', $where);//所有数量
        // var_dump($all_rows);
        assign('page_count', ceil($all_rows / $pagesize));
        display('/consume/jifen/list.html');
    }

    /**
     * 积分详情
     */
    public function jifen_detail($id)
    {
        $pagesize = 20;//分页大小
        $page     = (int)$this->input->get('per_page');
        $page <= 1 ? $page = 1 : $page = $page;//当前页数
        //查到数据
        $where['where']['m_id']= $id;
        $where['select']= array('a.id,a.m_id,a.jifen,b.name,b.username');
        $where['join'] = array(
            array('user b','a.m_id=b.Id','left')
        );
        $list = $this->loop_model->get_list('jifen a',$where,$pagesize, $pagesize * ($page - 1), 'a.id');//列表
        assign('list', $list);//print_r($list);
        $all_rows = $this->loop_model->get_list_num('jifen a', $where);//所有数量
        // var_dump($all_rows);
        assign('page_count', ceil($all_rows / $pagesize));
        display('/consume/jifen/jifen_detail.html');
    }


    /**
     * 账单列表统计
     */
    public function bill_list()
    {
        $pagesize = 20;//分页大小
        $page     = (int)$this->input->get('per_page');
        $page <= 1 ? $page = 1 : $page = $page;//当前页数
        //查到数据
        $where['select']= array('sum(a.consume) as total_consume,a.id,a.m_id,d.name,d.username');
        $where['join'] = array(
//            array('order b','a.order_id=b.id','left'),
//            array('service c','c.id=b.service_id','left'),
            array('user d','d.id=a.m_id','left')
        );
        $list = $this->loop_model->get_group_list('order_collection_doc a',$where,$pagesize, $pagesize * ($page - 1), 'a.m_id','a.id');//列表
        assign('list', $list);//print_r($list);
        $all_rows = $this->loop_model->get_list_num('order_collection_doc a', $where);//所有数量
        // var_dump($all_rows);
        assign('page_count', ceil($all_rows / $pagesize));
        display('/consume/bill/list.html');
    }

    /**
     * 删除账单
     */

    public function delete_bill($id)
    {
        $id = (int)$this->input->post('id', true);
        if (empty($id)) error_json('id不能为空');
        $res = $this->loop_model->delete_where('order_collection_doc',array('id'=>$id));
        if($res > 0){
            error_json('y');
        }else{
            error_json('删除失败');
        }

    }

    /**
     * 账单详情
     */
    public function bill_detail($id)
    {
        $pagesize = 20;//分页大小
        $page     = (int)$this->input->get('per_page');
        $page <= 1 ? $page = 1 : $page = $page;//当前页数
        //查到数据
        $where['select']= array('a.consume,a.add_money,a.amount,a.payment_id,a.id,b.order_no,c.name as service_name ,d.name,d.username');
        $where['where']['a.m_id'] = $id;
        $where['join'] = array(
            array('order b','a.order_id=b.id','left'),
            array('service c','c.id=b.service_id','left'),
            array('user d','d.id=a.m_id','left')
        );

        $list = $this->loop_model->get_list('order_collection_doc a',$where,'', '', 'a.id');//列表
        assign('list', $list);
        $where['select']= array('sum(a.consume) as total');
        $total = $this->loop_model->get_list('order_collection_doc a',$where,'', '', 'a.id');//列表
        assign('total', $total[0]);//print_r($total);

        $where1['select'] = array('sum(consume) as consume');
        $where1['where']['consume <'] = 0;
        $where1['where']['m_id'] = $id;
        $consume = $this->loop_model->get_list('order_collection_doc',$where1,'', '', 'id');//列表
        assign('consume', $consume[0]);//print_r($consume);

        $where2['select'] = array('sum(consume) as recharge');
        $where2['where']['consume >'] = 0;
        $where2['where']['m_id'] = $id;
        $recharge = $this->loop_model->get_list('order_collection_doc',$where2,'', '', 'id');//列表
        assign('recharge', $recharge[0]);//print_r($recharge);

        $where3['select'] = array('sum(add_money) as add_money');
        $where3['where']['m_id'] = $id;
        $add_money = $this->loop_model->get_list('order_collection_doc',$where3,'', '', 'id');//列表
        assign('add_money', $add_money[0]);//print_r($recharge);
        assign('m_id', $id);


        $all_rows = $this->loop_model->get_list_num('order_collection_doc a', $where);//所有数量
        // var_dump($all_rows);
        assign('page_count', ceil($all_rows / $pagesize));
        display('/consume/bill/bill_detail.html');
    }

    public function bill_export(){
        $pagesize = 20;//分页大小
        $page     = (int)$this->input->get('per_page');
        $page <= 1 ? $page = 1 : $page = $page;//当前页数
        //查到数据
        $where['select']= array('sum(a.consume) as total_consume,a.id,a.m_id,d.name,d.username');
        $where['join'] = array(
//            array('order b','a.order_id=b.id','left'),
//            array('service c','c.id=b.service_id','left'),
            array('user d','d.id=a.m_id','left')
        );
        $list = $this->loop_model->get_group_list('order_collection_doc a',$where,$pagesize, $pagesize * ($page - 1), 'a.m_id','a.id');//列表

        $this->load->library('PHPExcel');
        $this->load->library('PHPExcel/IOFactory');
        $resultPHPExcel = new PHPExcel();
        $i              = 1;
        $resultPHPExcel->getActiveSheet()->setCellValue('A' . $i, '编号');
        $resultPHPExcel->getActiveSheet()->setCellValue('B' . $i, '总消费');
        $resultPHPExcel->getActiveSheet()->setCellValue('C' . $i, '用户姓名');
        $resultPHPExcel->getActiveSheet()->setCellValue('D' . $i, '联系方式');
//        $resultPHPExcel->getActiveSheet()->setCellValue('E' . $i, '支付方式');
//        $resultPHPExcel->getActiveSheet()->setCellValue('F' . $i, '配送方式');
//        $resultPHPExcel->getActiveSheet()->setCellValue('G' . $i, '用户名');
//        $resultPHPExcel->getActiveSheet()->setCellValue('H' . $i, '收货地址');
//        $resultPHPExcel->getActiveSheet()->setCellValue('I' . $i, '支付时间');
//        $resultPHPExcel->getActiveSheet()->setCellValue('J' . $i, '支付金额');
//        $resultPHPExcel->getActiveSheet()->setCellValue('K' . $i, '物流费用');
//        $resultPHPExcel->getActiveSheet()->setCellValue('L' . $i, '商品');
        foreach($list as $key=>$val){
            $i++;
            $resultPHPExcel->getActiveSheet()->setCellValue('A' . $i, $val['id']);
            $resultPHPExcel->getActiveSheet()->setCellValue('B' . $i, $val['total_consume']);
            $resultPHPExcel->getActiveSheet()->setCellValue('C' . $i, $val['name']);
            $resultPHPExcel->getActiveSheet()->setCellValue('D' . $i, $val['username']);
        }
        $outputFileName = "订单.xls";
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
