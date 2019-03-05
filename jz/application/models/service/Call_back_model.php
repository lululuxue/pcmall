<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Call_back_model extends CI_Model
{

    public function __construct()
    {
        /**
         * 载入数据库类
         */
        $this->load->database();
    }

    /**
     * 新增回访内容
     */
    public function add($data_post,$data)
    {   //数据验证

        if (empty($data_post['order_id'])) {
            return '订单号不存在';
        }
        //新回访
        if(empty($data_post['id'])){
            $res = $this->loop_model->get_where('order_callback',array('order_id'=>$data_post['order_id']));
            if($res){
                return '该订单已经回访';
            }
        }

        $update_data = array(
            'order_id'         => $data_post['order_id'],
            'admin_id'         => (int)$data['id'],
            'addtime'     => time(),
            'calltime'      => strtotime($data_post['calltime']),
            'callback'      => $data_post['callback'],
            'note'      => $data_post['note'],
            'people'      => $data['full_name'],
            'status'      => 1

        );

        if(!empty($data_post['id'])){
            //修改
            $where = array('id'=>$data_post['id']);
            $res = $this->loop_model->update_where('order_callback',$update_data,$where);
        }else{
            $res = $this->loop_model->insert('order_callback', $update_data);

        }
        if($res){
            return 'y';
        }else{
            return '信息保存失败';
        }

    }


}
