<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Normal_user_model extends CI_Model
{

    public function __construct()
    {
        /**
         * 载入数据库类
         */
        $this->load->database();
    }
    /**
     *保存或修改
     */
    public function update($data_post)
    {   //数据验证

        if (empty($data_post['normal_id'])) {
            return '固定类型不能为空';
        } elseif (empty($data_post['value'])) {
            return '固定值错误';
        } elseif (empty($data_post['dealtime'])) {
            return '服务时间错误';
        }

        $update_data = array(
            'name'         => $data_post['name'],
            'm_id'         => (int)$data_post['m_id'],
            'service_id'       => (int)$data_post['service_id'],
            'normal_id'     => (int)$data_post['normal_id'],
            'normal_no'   => date('Ymdhms',time()).substr(time(),-6),
            'value'          => (int)$data_post['value'],
            'addtime'     => time(),
            'dealtime'      => strtotime($data_post['dealtime']),
            'note'      => $data_post['note'],
            'point_people'      => $data_post['point_people'],
            'gender'      => 'gender0-'.$data_post['gender0'].'/gender1-'.$data_post['gender1'].'/gender2-'.$data_post['gender2']

        );

        if(!empty($data_post['id'])){
            //修改
            $where = array('id'=>$data_post['id']);
            $res = $this->loop_model->update_where('normal_user',$update_data,$where);
        }else{
            $res = $this->loop_model->insert('normal_user', $update_data);

        }
        if($res){
            return 'y';
        }else{
            return '信息保存失败';
        }

    }

}
