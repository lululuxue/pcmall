<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class User_address_model extends CI_Model
{

    public function __construct()
    {
        /**
         * 载入数据库类
         */
        $this->load->database();
    }

    /**
     * 查询一个收货地址
     * @param array $where 条件数组
     * @param string $orderby 排序
     * @return array
     */
    public function get_address($where, $orderby = 'is_default desc')
    {
        if (!empty($where)) {
            if (!empty($orderby)) {
                $this->db->order_by($orderby);
            }
            $query = $this->db->get_where('member_user_address', $where);
            $data = $query->row_array();//echo $this->db->last_query()."<br>";
            return $data;
        }
    }

    /**
     * 新增、修改收货地址
     */
    public function update($post_data)
    {
        if(!$post_data['id']){
            if(empty($post_data['full_name'])){
                return '姓名不能为空';
            }
            if(empty($post_data['tel'])){
                return '联系电话不能为空';
            }
            if(empty($post_data['scale'])){
                return '平方数不能为空';
            }
            if(empty($post_data['m_id'])){
                return 'm_id不能为空';
            }
            if(empty($post_data['province']) || empty($post_data['city']) || empty($post_data['area'])){
                return '请选择地址';
            }
        }


        $post_data['full_name'] ? $update['full_name'] = $post_data['full_name'] :'';
        $post_data['tel'] ? $update['tel'] = $post_data['tel'] : '';
        $post_data['scale'] ? $update['scale'] = $post_data['scale'] : '';
        $post_data['province'] ? $update['province'] = $post_data['province'] : '';
        $post_data['city'] ? $update['city'] = $post_data['city'] : '';
        $post_data['area'] ? $update['area'] = $post_data['area'] : '';
        $post_data['address'] ? $update['address'] = $post_data['address'] : '';
        $post_data['is_default'] ? $update['is_default'] = $post_data['is_default'] : '';
        $post_data['m_id'] ? $update['m_id'] = $post_data['m_id'] : '';
        if($post_data['id']){
            //修改
            $this->db->trans_begin();
            if($update['is_default'] == 1){
                $this->loop_model->update_where('user_address',array('is_default'=>0),array('m_id'=>$post_data['m_id'],'is_default'=>1));
            }else{
                $update['is_default'] = 0;
            }
            $update['updatetime'] = date('Y-m-d H:i:s',time());
            $res = $this->loop_model->update_where('user_address',$update,array('id'=>$post_data['id']));
            if ($this->db->trans_status() === TRUE) {
                $this->db->trans_commit();
                return 'y';
            } else {
                $this->db->trans_rollback();
                return '信息保存失败';
                //@todo 异常处理部分
            }
        }else{
            //新增
            $data = $this->loop_model->get_where('user_address',array('m_id'=>$post_data['m_id']));
            $this->db->trans_begin();
            if($update['is_default'] == 1 && $data){
                $this->loop_model->update_where('user_address',array('is_default'=>0),array('m_id'=>$post_data['m_id'],'is_default'=>1));
            }
            $update['addtime'] = date('Y-m-d H:i:s',time());
            $res = $this->loop_model->insert('user_address',$update);
            if ($this->db->trans_status() === TRUE) {
                $this->db->trans_commit();
                return 'y';
            } else {
                $this->db->trans_rollback();
                return '信息保存失败';
                //@todo 异常处理部分
            }
        }
    }
}
