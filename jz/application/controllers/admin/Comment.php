<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Comment extends CI_Controller
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
        $where['select']= array('a.*,b.name,b.username');
        $where['join'] = array(
            array('user b','a.m_id=b.Id','left')
        );
        $list = $this->loop_model->get_group_list('comment a',$where,$pagesize, $pagesize * ($page - 1), 'a.m_id','a.id');//列表
        assign('list', $list);//print_r($list);
        $all_rows = $this->loop_model->get_list_num('comment a', $where);//所有数量
        // var_dump($all_rows);
        assign('page_count', ceil($all_rows / $pagesize));
        display('/comment/list.html');
    }

    /**
     * 回复
     */
    public function comment_call($id)
    {
        $where['id'] = $id;
        $info = $this->loop_model->get_where('comment',$where);
        assign('info', $info);//print_r($list);
        display('/comment/comment_call.html');
    }

    /**
     * 查看回复详情
     */
    public function comment_detail($id)
    {
        $where['id'] = $id;
        $info = $this->loop_model->get_where('comment',$where);
        assign('info', $info);//print_r($list);
        display('/comment/comment_detail.html');
    }


    /**
     * 保存数据
     */
    public function save()
    {
        if (is_post()) {
            $data_post = $this->input->post(NULL, true);
            $update_data = array(
                'call_back'    => $data_post['call_back'],
            );
            $res = $this->loop_model->update_where('comment', $update_data, array('id'=>$data_post['id']));
            if($res >=0){
                error_json('y');
            }else{
                error_json('回复失败');
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
        $id = (int)$this->input->post('id', true);
        if (empty($id)) error_json('id不能为空');
        $re_item = $this->loop_model->get_where('service', array('reid' => $id));
        if (!empty($re_item)) {
            error_json('下级栏目不为空不能删除');
        } else {
            $res = $this->loop_model->delete_id('service', $id);
            if (!empty($res)) {
                admin_log_insert('删除分类' . $id);
                error_json('y');
            } else {
                error_json('删除失败');
            }
        }
    }

}
