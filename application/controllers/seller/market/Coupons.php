<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Coupons extends CI_Controller
{

    private $shop_data;//后台用户登录信息

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
     * 列表
     */
    public function index()
    {
        $pagesize = 20;//分页大小
        $page     = (int)$this->input->get('per_page');
        $page <= 1 ? $page = 1 : $page = $page;//当前页数
        //查到数据
        $where_data['where']['shop_id'] = $this->shop_id;
        $where_data['where']['status']  = 0;
        $list_data                      = $this->loop_model->get_list('coupons', $where_data, $pagesize, $pagesize * ($page - 1), 'id desc');//列表
        foreach ($list_data as $key) {
            $key['amount']    = format_price($key['amount']);
            $key['use_price'] = format_price($key['use_price']);
            $list[]           = $key;
        }
        assign('list', $list);
        //开始分页start
        $all_rows = $this->loop_model->get_list_num('coupons', $where_data);//所有数量
        assign('page_count', ceil($all_rows / $pagesize));
        //开始分页end
        display('/market/coupons/list.html');
    }

    /**
     * 添加编辑
     */
    public function edit($id = '')
    {
        $id = (int)$id;
        if (!empty($id)) {
            $item               = $this->loop_model->get_where('coupons', array('id' => $id, 'shop_id' => $this->shop_id));
            $item['amount']     = format_price($item['amount']);
            $item['use_price']  = format_price($item['use_price']);
            $item['start_time'] = date('Y-m-d H:i:s', $item['start_time']);
            $item['end_time']   = date('Y-m-d H:i:s', $item['end_time']);
            assign('item', $item);
        }

        display('/market/coupons/add.html');
    }

    /**
     * 保存数据
     */
    public function save()
    {
        if (is_post()) {
            $data_post   = $this->input->post(NULL, true);
            $update_data = array(
                'name'       => $data_post['name'],
                'amount'     => price_format($data_post['amount']),
                'use_price'  => price_format($data_post['use_price']),
                'start_time' => strtotime($data_post['start_time']),
                'end_time'   => strtotime($data_post['end_time']),
                'shop_id'    => $this->shop_id,
                'addtime'    => time(),
            );
            if (empty($update_data['name'])) {
                error_json('名称不能为空');
            } elseif (empty($update_data['amount'])) {
                error_json('优惠金额不能为空');
            } elseif (empty($update_data['use_price'])) {
                error_json('订单起用金额不能为空');
            } elseif (empty($data_post['start_time'])) {
                error_json('开始时间不能为空');
            } elseif (empty($data_post['end_time'])) {
                error_json('结束时间不能为空');
            }

            if (!empty($data_post['id'])) {
                unset($update_data['amount']);//优惠券面额不能修改
                //修改数据
                $res = $this->loop_model->update_where('coupons', $update_data, array('id' => $data_post['id'], 'shop_id' => $this->shop_id));
                shop_admin_log_insert('修改优惠券活动' . $data_post['id']);
            } else {
                //增加数据
                $res = $this->loop_model->insert('coupons', $update_data);
                shop_admin_log_insert('增加优惠券活动' . $res);
            }
            if (!empty($res)) {
                error_json('y');
            } else {
                error_json('保存失败');
            }
        } else {
            error_json('提交方式错误');
        }

    }

    /**
     * 删除数据
     */
    public function delete()
    {
        $id = $this->input->post('id', true);
        if (empty($id)) error_json('id错误');
        $update_where = array(
            'where_in' => array('id' => $id),
            'where'    => array('shop_id' => $this->shop_id),
        );
        $res          = $this->loop_model->update_where('coupons', array('status' => 1), $update_where);
        if (!empty($res)) {
            shop_admin_log_insert('删除优惠券活动' . $id);
            error_json('y');
        } else {
            error_json('删除失败');
        }
    }

    /**
     * 生成券
     */
    public function generate($id)
    {
        $id = (int)$id;
        if (empty($id)) error_json('id错误');
        $generate_num = (int)$this->input->post('generate_num', true);
        if (empty($generate_num)) error_json('生成数量必须大于0');

        $coupons_data = $this->loop_model->get_where('coupons', array('id' => $id, 'shop_id' => $this->shop_id));
        if (!empty($coupons_data)) {
            //开始生成
            $this->load->model('market/coupons_model');
            $res = $this->coupons_model->generate($coupons_data, $generate_num);
            error_json($res);
        } else {
            error_json('活动不存在');
        }
    }

    /**
     * 生成券列表
     */
    public function detail()
    {
        $export   = $this->input->get('export');
        $pagesize = 20;//分页大小
        $page     = (int)$this->input->get('per_page');
        $page <= 1 ? $page = 1 : $page = $page;//当前页数
        //搜索条件start
        $status   = $this->input->post_get('status');//状态
        $is_send  = $this->input->post_get('is_send');//是否发放
        $is_close = $this->input->post_get('is_close');//是否禁用
        $m_id     = $this->input->post_get('m_id');//用户id
        $cou_id   = $this->input->post_get('cou_id');//活动id
        if (isset($status)) $where_data['where']['d.status'] = $status;
        if (isset($is_send)) $where_data['where']['is_send'] = $is_send;
        if (isset($is_close)) $where_data['where']['is_close'] = $is_close;
        if (!empty($m_id)) $where_data['where']['m_id'] = $m_id;
        if (!empty($cou_id)) $where_data['where']['cou_id'] = $cou_id;
        $where_data['where']['d.shop_id'] = $this->shop_id;//过滤店铺
        $search_where                     = array(
            'status'   => $status,
            'is_send'  => $is_send,
            'is_close' => $is_close,
            'm_id'     => $m_id,
        );
        assign('search_where', $search_where);

        $where_data['select'] = array('d.*,c.name,c.amount');
        $where_data['join']   = array(
            array('coupons as c', 'd.cou_id=c.id', 'left')
        );
        //搜索条件end
        //查到数据
        if (empty($export)) {
            $list_data = $this->loop_model->get_list('coupons_detail as d', $where_data, $pagesize, $pagesize * ($page - 1), 'd.id desc');//列表
            foreach ($list_data as $key) {
                $key['amount'] = format_price($key['amount']);
                $list[]        = $key;
            }
            assign('list', $list);
            //开始分页start
            $all_rows = $this->loop_model->get_list_num('coupons_detail as d', $where_data);//所有数量
            assign('page_count', ceil($all_rows / $pagesize));
            //开始分页end
            display('/market/coupons/detail.html');
        } else {
            $list = $this->loop_model->get_list('coupons_detail as d', $where_data, '', '', 'd.id desc');//列表
            $this->load->library('PHPExcel');
            $this->load->library('PHPExcel/IOFactory');
            $resultPHPExcel = new PHPExcel();
            $i              = 1;
            $resultPHPExcel->getActiveSheet()->setCellValue('A' . $i, '活动名称');
            $resultPHPExcel->getActiveSheet()->setCellValue('B' . $i, '密码');
            $resultPHPExcel->getActiveSheet()->setCellValue('C' . $i, '券额');
            $resultPHPExcel->getActiveSheet()->setCellValue('D' . $i, '是否使用');
            $resultPHPExcel->getActiveSheet()->setCellValue('E' . $i, '是否发放');
            $resultPHPExcel->getActiveSheet()->setCellValue('F' . $i, '是否禁用');
            foreach ($list as $key) {
                $i++;
                if ($key['status'] == 0) {
                    $status = '未使用';
                } elseif ($key['status'] == 1) {
                    $status = '已使用';
                }
                if ($key['is_send'] == 0) {
                    $is_send = '未发放';
                } elseif ($key['is_send'] == 1) {
                    $is_send = '已发放';
                }
                if ($key['is_close'] == 0) {
                    $is_close = '未禁用';
                } elseif ($key['is_close'] == 1) {
                    $is_close = '已禁用';
                }

                $resultPHPExcel->getActiveSheet()->setCellValue('A' . $i, $key['name']);
                $resultPHPExcel->getActiveSheet()->setCellValue('B' . $i, $key['password']);
                $resultPHPExcel->getActiveSheet()->setCellValue('C' . $i, '￥' . format_price($key['amount']));
                $resultPHPExcel->getActiveSheet()->setCellValue('D' . $i, $status);
                $resultPHPExcel->getActiveSheet()->setCellValue('E' . $i, $is_send);
                $resultPHPExcel->getActiveSheet()->setCellValue('F' . $i, $is_close);
            }
            $outputFileName = "优惠券.xls";
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
     * 发放优惠券
     */
    public function detail_send()
    {
        $id = $this->input->post('id', true);
        if (empty($id)) error_json('id错误');
        $update_where = array(
            'where_in' => array('id' => $id),
            'where'    => array('shop_id' => $this->shop_id),
        );
        $res          = $this->loop_model->update_where('coupons_detail', array('is_send' => 1), $update_where);
        if (!empty($res)) {
            shop_admin_log_insert('发放优惠券' . $id);
            error_json('y');
        } else {
            error_json('发放失败');
        }
    }


    /**
     * 修改优惠券状态
     */
    public function detail_update_is_close()
    {
        $id       = $this->input->post('id', true);
        $is_close = $this->input->get_post('is_close', true);
        if (empty($id) || $is_close == '') error_json('id错误');
        $update_data['is_close'] = (int)$is_close;
        $update_where            = array(
            'where_in' => array('id' => $id),
            'where'    => array('shop_id' => $this->shop_id),
        );
        $res                     = $this->loop_model->update_where('coupons_detail', $update_data, $update_where);
        if (!empty($res)) {
            admin_log_insert('修改优惠券is_close为' . $is_close . 'id为' . $id);
            error_json('y');
        } else {
            error_json('操作失败');
        }
    }

    /**
     * 删除优惠券
     */
    public function detail_delete()
    {
        $id = $this->input->post('id', true);
        if (empty($id)) error_json('id错误');
        $update_where = array(
            'where_in' => array('id' => $id),
            'where'    => array('shop_id' => $this->shop_id),
        );
        $res          = $this->loop_model->delete_where('coupons_detail', $update_where);
        if (!empty($res)) {
            shop_admin_log_insert('删除优惠券' . $id);
            error_json('y');
        } else {
            error_json('删除失败');
        }
    }

    /**
     * 给用户发放优惠券
     */
    public function detail_set_user($id)
    {
        $id       = (int)$id;
        $username = $this->input->post('username', true);
        if (empty($username)) error_json('用户名不能为空');
        $member_data = $this->loop_model->get_where('member', array('username' => $username));
        if (!empty($member_data)) {
            $coupons_detail_data = $this->loop_model->get_where('coupons_detail', array('id' => $id, 'shop_id' => $this->shop_id));
            if (!empty($coupons_detail_data) && $coupons_detail_data['m_id'] == 0) {
                $update_where = array(
                    'where' => array(
                        'id'      => $id,
                        'shop_id' => $this->shop_id
                    ),
                );
                $res          = $this->loop_model->update_where('coupons_detail', array('m_id' => $member_data['id'], 'is_send' => 1), $update_where);
                if (!empty($res)) {
                    shop_admin_log_insert('给用户' . $username . '绑定优惠券' . $id);
                    error_json('y');
                } else {
                    error_json('绑定失败');
                }
            } else {
                error_json('优惠券不存在或者已经绑定用户');
            }
        } else {
            error_json('用户不存在');
        }
    }
}
