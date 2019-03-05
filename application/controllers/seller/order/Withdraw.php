<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Withdraw extends CI_Controller
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
        //搜索条件start
        $where_data['where']['m_id'] = $this->shop_id;
        //搜索条件end
        //查到数据
        $list_data = $this->loop_model->get_list('member_user_withdraw', $where_data, $pagesize, $pagesize * ($page - 1), 'id desc');//列表
        foreach ($list_data as $key) {
            $key['amount'] = format_price($key['amount']);
            $list[]        = $key;
        }
        assign('list', $list);
        //开始分页start
        $all_rows = $this->loop_model->get_list_num('member_user_withdraw', $where_data);//所有数量
        assign('page_count', ceil($all_rows / $pagesize));
        //开始分页end

        assign('status', array('0' => '等待审核', '1' => '审核成功', '2' => '拒绝并退还资金', '3' => '拒绝不退还资金'));//状态
        assign('type', array('1' => '银行', '2' => '支付宝', '3' => '微信'));//状态
        display('/order/withdraw/list.html');
    }

    /**
     * 编辑
     */
    public function edit()
    {
        $member_user_data            = $this->loop_model->get_where('member_user', array('m_id' => $this->shop_id));
        $member_user_data['balance'] = format_price($member_user_data['balance']);
        assign('member_user_data', $member_user_data);
        display('/order/withdraw/add.html');
    }

    /**
     * 提现申请处理
     */
    public function save()
    {
        if (is_post()) {
            $data_post = $this->input->post(NULL, true);
            if (empty($data_post['amount'])) error_json('提现金额不能为空');
            if (empty($data_post['name'])) error_json('姓名不能为空');
            if (empty($data_post['pay_number'])) error_json('账户不能为空');
            if ($data_post['type'] == 1 && empty($data_post['bank_name'])) error_json('开户银行不能为空');
            $member_user_data = $this->loop_model->get_where('member_user', array('m_id' => $this->shop_id));
            $amount           = price_format($data_post['amount']);
            if ($amount <= 0) {
                error_json('提现金额不能小等于0');
            } elseif ($member_user_data['balance'] < $amount) {
                error_json('账户余额不足');
            } else {
                //开始扣除资金
                $this->load->model('member/user_account_log_model');
                $data   = array(
                    'm_id'   => $this->shop_id,
                    'amount' => $amount,
                    'event'  => 2,
                    'note'   => '用户申请提现',
                );
                $log_id = $this->user_account_log_model->insert($data);
                if ($log_id['status'] == 'y') {
                    //开始添加提现单
                    $withdraw_data = array(
                        'm_id'       => $this->shop_id,
                        'amount'     => $amount,
                        'name'       => $data_post['name'],
                        'bank_name'  => $data_post['bank_name'],
                        'pay_number' => $data_post['pay_number'],
                        'type'       => $data_post['type'],
                        'addtime'    => time(),
                        'note'       => $data_post['note'],
                    );
                    $res           = $this->loop_model->insert('member_user_withdraw', $withdraw_data);
                    if (!empty($res)) {
                        error_json('y');
                    } else {
                        error_json('申请失败');
                    }
                } else {
                    error_json($log_id['info']);
                }
            }
        } else {
            error_json('提交方式错误');
        }
    }
}
