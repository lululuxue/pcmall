<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Complain_model extends CI_Model
{

    public function __construct()
    {
        /**
         * 载入数据库类
         */
        $this->load->database();
    }

    /**
     * 处理结果
     */
    public function update($data_post,$data)
    {   //数据验证

        if (empty($data_post['id'])) {
            return '该投诉不存在';
        }
        //新回访
        if(empty($data_post['id'])){
            $res = $this->loop_model->get_where('order_deal',array('id'=>$data_post['id']));
            if(!$res){
                return '该投诉不存在';
            }
        }

        $update_data = array(
            'dealtime'      => strtotime($data_post['dealtime']),
            'deal_status'      => $data_post['deal_status'],
            'admin_name'      => $data['full_name']

        );
        if(empty($data_post['id'])){
            $update_data['addtime'] = time();
            $update_data['order_id'] = $data_post['order_id'];
            $update_data['note'] = $data_post['note'];
        }

        if(!empty($data_post['id'])){
            //修改
            $where = array('id'=>$data_post['id']);
            $res = $this->loop_model->update_where('order_deal',$update_data,$where);
        }else{
            $res = $this->loop_model->insert('order_deal', $update_data);

        }
        if($res){
            return 'y';
        }else{
            return '信息保存失败';
        }

    }


}
