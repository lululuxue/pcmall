<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Position_model extends CI_Model
{

    public function __construct()
    {
        /**
         * 载入数据库类
         */
        $this->load->database();
    }

    /**
     * 新增/编辑员工数据
     */
    public function update($data)
    {
        //数据验证
        if (empty($data['address'])) {
            return '现住地址不能为空';
        }
        if (empty($data['ident_address'])) {
            return '证件地址不能为空';
        }
        if (empty($data['position_no'])) {
            return '员工工号不能为空';
        }
        if (empty($data['name'])) {
            return '员工姓名不能为空';
        }
        if (empty($data['identify_number'])) {
            return '身份证号不能为空';
        }
        if (empty($data['nation'])) {
            return '名族不能为空';
        }
        if (empty($data['entrytime'])) {
            return '入职日期不能为空';
        }
        if (empty($data['birthday'])) {
            return '出生日期不能为空';
        }
        if (empty($data['phone'])) {
            return '联系方式';
        }
        if (empty($data['cat_id']) && $data['type'] == 0) {
            return '员工类别不能为空';
        }
        if (empty($data['level_id']) && $data['type'] == 0) {
            return '工资星级不能为空';
        }
        if (empty($data['department_id'])) {
            return '部门不能为空';
        }
        if (empty($data['job']) && $data['type'] == 0) {
            return '职务不能为空';
        }
        if (!isset($data['day_off'])) {
            return '休息日不能为空';
        }

        $update_data['address']  = $data['address'];
        $update_data['age']  = $data['age'];
        $update_data['bank_account']  = $data['bank_account'];
        $update_data['birthday']  = $data['birthday'];
        $update_data['cat_id']  = $data['cat_id'];
        $update_data['day_off']  = $data['day_off'];
        $update_data['department_id']  = $data['department_id'];
        $update_data['eductional']  = $data['eductional'];
        $update_data['entrytime']  = $data['entrytime'];
        $update_data['file_number']  = $data['file_number'];
        $update_data['gender']  = $data['gender'];
        $update_data['ident_address']  = $data['ident_address'];
        $update_data['identify_number']  = $data['identify_number'];
        $update_data['is_check']  = $data['is_check'];
        $update_data['is_merchant']  = $data['is_merchant'];
        $update_data['job']  = $data['job'];
        $update_data['job_id']  = $data['job_id'];
        $update_data['level_id']  = $data['level_id'];
        $update_data['hour_money']  = $data['money'];
        $update_data['name']  = $data['name'];
        $update_data['note']  = $data['note'];
        $update_data['nation']  = $data['nation'];
        $update_data['phone']  = $data['phone'];
        $update_data['phone1']  = $data['phone1'];
        $update_data['position_no']  = $data['position_no'];
        $update_data['resigntime']  = $data['resigntime'];
        $update_data['contract_start']  = $data['contract_start'];
        $update_data['contract_end']  = $data['contract_end'];
        $update_data['contract_end']  = $data['contract_end'];
        $update_data['safe_number']  = $data['safe_number'];
        $update_data['school']  = $data['school'];
        $update_data['shop']  = $data['shop'];
        $update_data['sub_shop']  = $data['sub_shop'];
        $update_data['month']  = $data['month'];
        $update_data['status']  = $data['status'];
        $update_data['type']  = $data['type'];

        if(empty($data['id'])){
            $update_data ['addtime'] = time();
            $res = $this->loop_model->insert('position', $update_data);
        }else {
            //修改
            $personnel = $this->loop_model->get_where('position', array('id' => $data['id']));
            if (!$personnel) {
                return '员工不存在';
            }
            $where = array('id'=>$data['id']);
            $update_data ['updatetime'] = time();
            /**
             * 查看月份是否一致
             */
            if ($data['month'] != $personnel['month']){
                $personnel['position_id'] =  $personnel['id'];
                $personnel['id'] = NULL;
                $this->db->trans_begin();
                $res1 = $this->loop_model->insert('position_history',$personnel);
                $res = $this->loop_model->update_where('position',$update_data,$where);
                if ($this->db->trans_status() === TRUE) {
                    $this->db->trans_commit();
                    return 'y';
                } else {
                    $this->db->trans_rollback();
                    return '信息保存失败';
                    //@todo 异常处理部分
                }
            }else{
                 $res = $this->loop_model->update_where('position',$update_data,$where);

             }
        }
        if($res > 0){
            return 'y';
        }else{
            return '信息保存失败';
        }

    }

    public function update_img($data)
    {
        if(!$data['id']){
            return '员工id不能为空';
        }
        if($data['photo']) $update_data ['photo'] = $data['photo'];
        if($data['identify']) $update_data ['identify'] = $data['identify'];
        if($data['fingerprint']) $update_data ['fingerprint'] = $data['fingerprint'];
        if($data['healthimg']) $update_data ['healthimg'] = $data['healthimg'];
        $personnel = $this->loop_model->get_where('position',array('id'=>$data['id']));
        if(!$personnel){
            return '员工不存在';
        }
        $where = array('id'=>$data['id']);
        $update_data ['updatetime'] = date("Y-m-d H:i:s",time());
        $res = $this->loop_model->update_where('position',$update_data,$where);
        if($res > 0){
            return 'y';
        }else{
            return '信息保存失败';
        }

    }

}
