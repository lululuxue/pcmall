<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class User extends CI_Controller
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
        $pagesize = 20;//分页大小
        $page     = (int)$this->input->get('per_page');
        $page <= 1 ? $page = 1 : $page = $page;//当前页数
        //搜索条件start
        $status = $this->input->post_get('status');
        if (isset($status) && $status != '') {
            $where_data['where'] = array('u.status' => $status);
        } else {
            $where_data['where'] = array('u.status!=' => '1');
        }
        //用户组
        $group_id = $this->input->post_get('group_id');
        if ($group_id != '') $where_data['where']['group_id'] = $group_id;
        //关键字
        $username = $this->input->post_get('username');
        if (!empty($username)) $where_data['where']['username'] = $username;
        $search_where = array(
            'status'   => $status,
            'group_id' => $group_id,
            'username' => $username,
        );
        assign('search_where', $search_where);
        //搜索条件end
        $where_data['where']['id>'] = 1;
        $where_data['join']         = array(
            array('member as m', 'u.m_id=m.id')
        );
        //查到数据
        $list_data = $this->loop_model->get_list('member_user as u', $where_data, $pagesize, $pagesize * ($page - 1), 'm.id desc');//列表
        foreach ($list_data as $key) {
            $key['balance'] = format_price($key['balance']);
            $list[]         = $key;
        }
        assign('list', $list);
        //开始分页start
        $all_rows = $this->loop_model->get_list_num('member_user as u', $where_data);//所有数量
        assign('page_count', ceil($all_rows / $pagesize));
        //开始分页end

        assign('status', array('0' => '正常', 1 => '删除', 2 => '锁定'));//状态
        //会员组
        $user_group_list = $this->loop_model->get_list('member_user_group');
        foreach ($user_group_list as $k) {
            $group_list[$k['id']] = $k['group_name'];
        }
        assign('group_list', $group_list);
        assign('user_group_list', $user_group_list);
        display('/member/user/list.html');
    }


    /**
     * 添加编辑
     */
    public function edit($id = '')
    {
        $id = (int)$id;
        if (!empty($id)) {
            $member      = $this->loop_model->get_id('member', $id);
            $member_user = $this->loop_model->get_where('member_user', array('m_id' => $id));
            assign('item', array_merge($member, $member_user));
        }

        //会员组列表
        $user_group_list = $this->loop_model->get_list('member_user_group');
        assign('user_group_list', $user_group_list);
        display('/member/user/add.html');
    }

    /**
     * 保存数据
     */
    public function save()
    {
        if (is_post()) {
            $data_post = $this->input->post(NULL, true);
            $this->load->model('member/user_model');
            $res = $this->user_model->update($data_post);
            if (!empty($res)) {
                error_json($res);
            } else {
                error_json('保存失败');
            }
        } else {
            error_json('提交方式错误');
        }

    }

    /**
     * 删除数据到回收站
     */
    public function delete_recycle()
    {
        $id = $this->input->post('id', true);
        if (empty($id)) error_json('id错误');
        if ($id == 1 || in_array(1, $id)) {
            error_json('商城自营不允许删除');
        }
        $res = $this->loop_model->update_where('member_user', array('status' => 1), array('where_in' => array('m_id' => $id)));
        if (!empty($res)) {
            if (is_array($id)) $id = join(',', $id);
            admin_log_insert('删除用户到回收站' . $id);
            error_json('y');
        } else {
            error_json('删除失败');
        }
    }

    /**
     * 回收站还原
     */
    public function reduction_recycle()
    {
        $id = $this->input->post('id', true);
        if (empty($id)) error_json('id错误');
        $res = $this->loop_model->update_where('member_user', array('status' => 0), array('where_in' => array('m_id' => $id)));
        if (!empty($res)) {
            if (is_array($id)) $id = join(',', $id);
            admin_log_insert('还原用户' . $id);
            error_json('y');
        } else {
            error_json('还原失败');
        }
    }

    /**
     * 彻底删除数据
     */
    public function delete()
    {
        $id = $this->input->post('id', true);
        if (empty($id)) error_json('id错误');
        if ($id == 1 || in_array(1, $id)) {
            error_json('商城自营不允许删除');
        }
        $res = $this->loop_model->delete_where('member', array('where_in' => array('id' => $id)));
        if (!empty($res)) {
            if (is_array($id)) $id = join(',', $id);
            admin_log_insert('彻底删除用户' . $id);
            error_json('y');
        } else {
            error_json('删除失败');
        }
    }

    /**
     * 修改数据状态
     */
    public function update_status()
    {
        $id     = $this->input->post('id', true);
        $status = $this->input->get_post('status', true);
        if (empty($id) || $status == '') error_json('id错误');
        $status                = (int)$status;
        $update_data['status'] = $status;
        $res                   = $this->loop_model->update_where('member_user', $update_data, array('where_in' => array('m_id' => $id)));
        if (!empty($res)) {
            if (is_array($id)) $id = join(',', $id);
            admin_log_insert('修改用户status为' . $status . 'id为' . $id);
            error_json('y');
        } else {
            error_json('操作失败');
        }
    }

    /**
     * 验证会员名是否存在
     */
    public function repeat_username()
    {
        $username = $this->input->post('param', true);
        if (!empty($username)) {
            $this->load->helpers('form_validation_helper');
            if (!is_mobile($username)) {
                error_json('用户名必须是手机号码');
            }
            $this->load->model('member/user_model');
            $member_data = $this->user_model->repeat_username($username);
            if (!empty($member_data)) {
                error_json('手机号码已经存在');
            } else {
                error_json('y');
            }
        }
    }

    /**
     * 现金账户流水
     */
    public function account_log($m_id = '')
    {
        $m_id = (int)$m_id;
        if (!empty($m_id)) {
            $pagesize = 20;//分页大小
            $page     = (int)$this->input->get('per_page');
            $page <= 1 ? $page = 1 : $page = $page;//当前页数
            //搜索条件start

            //搜索条件end
            $where_data['where']['m_id'] = $m_id;
            //查到数据
            $list_data = $this->loop_model->get_list('member_user_account_log', $where_data, $pagesize, $pagesize * ($page - 1), 'id desc');//列表
            foreach ($list_data as $key) {
                $key['amount'] = format_price($key['amount']);
                $list[]        = $key;
            }
            assign('list', $list);
            //开始分页start
            $all_rows = $this->loop_model->get_list_num('member_user_account_log', $where_data);//所有数量
            assign('page_count', ceil($all_rows / $pagesize));
            //开始分页end
            assign('m_id', $m_id);
        }
        $this->load->model('member/user_account_log_model');
        assign('event_name', $this->user_account_log_model->get_type_name());
        display('/member/user/account_log.html');
    }

    /**
     * 现金账户充值
     */
    public function account_online_recharge($m_id = '')
    {
        $m_id   = (int)$m_id;
        $amount = price_format($this->input->post('amount'));
        if (!empty($m_id) && !empty($amount)) {
            //开始划入资金
            $this->load->model('member/user_account_log_model');
            $data = array(
                'm_id'       => $m_id,
                'amount'     => $amount,
                'event'      => 1,
                'note'       => '管理员后台直接充值',
                'admin_user' => $this->admin_data['username'],
            );
            $log_id = $this->user_account_log_model->insert($data);
            if ($log_id['status'] == 'y') {
                admin_log_insert('给用户' . $m_id . '充值'. format_price($amount) .'元');
                error_json('y');
            } else {
                error_json('资金转入失败');
            }
        } else {
            error_json('用户id和充值金额不能小于0');
        }
    }

    /**
     * 现金账户扣除
     */
    public function account_withdraw($m_id = '')
    {
        $m_id   = (int)$m_id;
        $amount = price_format($this->input->post('amount'));
        if (!empty($m_id) && !empty($amount)) {
            //开始划入资金
            $this->load->model('member/user_account_log_model');
            $data = array(
                'm_id'       => $m_id,
                'amount'     => $amount,
                'event'      => 7,
                'note'       => '管理员后台直接扣除',
                'admin_user' => $this->admin_data['username'],
            );
            $log_id = $this->user_account_log_model->insert($data);
            if ($log_id['status'] == 'y') {
                admin_log_insert('给用户' . $m_id . '扣除'. format_price($amount) .'元');
                error_json('y');
            } else {
                error_json('资金扣除失败');
            }
        } else {
            error_json('用户id和扣除金额不能小于0');
        }
    }

    /**
     * 积分账户流水
     */
    public function point_log($m_id = '')
    {
        $m_id = (int)$m_id;
        if (!empty($m_id)) {
            $pagesize = 20;//分页大小
            $page     = (int)$this->input->get('per_page');
            $page <= 1 ? $page = 1 : $page = $page;//当前页数
            //搜索条件start

            //搜索条件end
            $where_data['where']['m_id'] = $m_id;
            //查到数据
            $list = $this->loop_model->get_list('member_user_point_log', $where_data, $pagesize, $pagesize * ($page - 1), 'id desc');//列表
            assign('list', $list);
            //开始分页start
            $all_rows = $this->loop_model->get_list_num('member_user_point_log', $where_data);//所有数量
            assign('page_count', ceil($all_rows / $pagesize));
            //开始分页end
            assign('m_id', $m_id);
        }
        $this->load->model('member/user_point_log_model');
        assign('event_name', $this->user_point_log_model->get_type_name());
        display('/member/user/point_log.html');
    }

    /**
     * 积分账户增加
     */
    public function point_online_recharge($m_id = '')
    {
        $m_id   = (int)$m_id;
        $amount = (int)$this->input->post('amount');
        if (!empty($m_id) && !empty($amount)) {
            //开始转入积分
            $this->load->model('member/user_point_log_model');
            $data = array(
                'm_id'       => $m_id,
                'amount'     => $amount,
                'event'      => 4,
                'note'       => '管理员后台直接充值',
                'admin_user' => $this->admin_data['username'],
            );
            $log_id = $this->user_point_log_model->insert($data);
            if ($log_id['status']=='y') {
                admin_log_insert('给用户' . $m_id . '充值'. $amount .'积分');
                error_json('y');
            } else {
                error_json('积分转入失败');
            }
        } else {
            error_json('用户id和充值积分个数不能小于0');
        }
    }

    /**
     * 积分账户扣除
     */
    public function point_withdraw($m_id = '')
    {
        $m_id   = (int)$m_id;
        $amount = (int)$this->input->post('amount');
        if (!empty($m_id) && !empty($amount)) {
            //开始转入积分
            $this->load->model('member/user_point_log_model');
            $data = array(
                'm_id'       => $m_id,
                'amount'     => $amount,
                'event'      => 5,
                'note'       => '管理员后台直接扣除',
                'admin_user' => $this->admin_data['username'],
            );
            $log_id = $this->user_point_log_model->insert($data);
            if ($log_id['status']=='y') {
                admin_log_insert('给用户' . $m_id . '扣除'. $amount .'积分');
                error_json('y');
            } else {
                error_json('积分扣除失败');
            }
        } else {
            error_json('用户id和扣除积分个数不能小于0');
        }
    }
}
