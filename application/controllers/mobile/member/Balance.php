<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Balance extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->helpers('web_helper');
        $this->member_data = member_login();
        $this->load->model('loop_model');
    }

    /**
     * 在线充值
     */
    public function online_recharge()
    {
        display('/member/balance/online_recharge.html');
    }

    /**
     * 在线充值提交
     */
    public function online_recharge_save()
    {
        if (is_post()) {
            $data_post = $this->input->post(NULL, true);
            if (empty($data_post['amount'])) msg('充值金额不能为空');
            if (empty($data_post['payment_id'])) msg('请选择充值方式');
            $amount = price_format($data_post['amount']);
            if ($amount <= 0) {
                msg('充值金额不能小等于0');
            } else {
                //查询支付方式
                $payment_data = $this->loop_model->get_id('payment', $data_post['payment_id']);
                if ($payment_data['status'] == 0) {
                    //开始添加充值单
                    $online_recharge_data = array(
                        'm_id'         => $this->member_data['id'],
                        'recharge_no'  => date('YmdHis') . get_rand_num('int', 6),
                        'amount'       => $amount,
                        'addtime'      => time(),
                        'payment_id'   => $payment_data['id'],
                        'payment_name' => $payment_data['name'],
                    );
                    $res                  = $this->loop_model->insert('member_user_online_recharge', $online_recharge_data);
                    if (!empty($res)) {
                        header('location:' . site_url('/api/pay/do_pay?client=' . get_web_type() . '&recharge_no=' . $online_recharge_data['recharge_no']));
                    } else {
                        msg('提交失败');
                    }
                } else {
                    msg('充值方式不存在');
                }
            }
        } else {
            error_json('提交方式错误');
        }
    }

    /**
     * 提现申请
     */
    public function withdraw()
    {
        $member_user_data            = $this->loop_model->get_where('member_user', array('m_id' => $this->member_data['id']));
        $member_user_data['balance'] = format_price($member_user_data['balance']);
        assign('member_user_data', $member_user_data);
        display('/member/balance/withdraw.html');
    }

    /**
     * 资金流水
     */
    public function index()
    {
        $pagesize = 20;//分页大小
        $page     = (int)$this->input->get('per_page');
        $page <= 1 ? $page = 1 : $page = $page;//当前页数
        //搜索条件start
        $event = $this->input->get_post('event');
        if (!empty($event)) $where_data['where']['event'] = $event;

        $search_where = array(
            'event' => $event,
        );
        assign('search_where', $search_where);
        //搜索条件end
        $where_data['where']['m_id'] = $this->member_data['id'];//过滤用户
        //查到数据
        $list_data = $this->loop_model->get_list('member_user_account_log', $where_data, $pagesize, $pagesize * ($page - 1), 'id desc');//列表
        foreach ($list_data as $key) {
            $key['amount'] = format_price($key['amount']);
            $list[]        = $key;
        }
        assign('list', $list);
        //开始分页start
        $all_rows = $this->loop_model->get_list_num('member_user_account_log', $where_data);//所有数量;
        assign('page_count', ceil($all_rows / $pagesize));
        //开始分页end

        //自己类型
        $this->load->model('member/user_account_log_model');
        assign('event_name', $this->user_account_log_model->get_type_name());

        $member_user_data            = $this->loop_model->get_where('member_user', array('m_id' => $this->member_data['id']));
        $member_user_data['balance'] = format_price($member_user_data['balance']);
        assign('member_user_data', $member_user_data);
        display('/member/balance/index.html');
    }
}
