<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Card_model extends CI_Model
{

    public function __construct()
    {
        /**
         * 载入数据库类
         */
        $this->load->database();
    }

    /**
     * 申请会员卡
     * @return array
     */
    public function update_card($data)
    {
        if (empty($data['id'])) {
            return '手机号码错误';
        }
        $user_data = $this->loop_model->get_where('user_card', array('m_id'=>$data['id']));
        if($user_data){
            //修改卡券信息
            $update['is_member'] = 2;
            $update['name'] = $data['name'];
        }else{
            //该用户没有注册(提示先去注册)
            return '您还不是用户，请先去注册';
        }
        //新增用户信息
        if (empty($data['gender'])){
            return '性别错误';
        }
        if (empty($data['birthday'])){
            return  '生日日期错误';
        }
        if (empty($data['name'])){
            return '姓名错误';
        }
        $this->db->trans_begin();
        $insertdata['gender'] = $data['gender'];
        $insertdata['birthday'] =$data['birthday'];
        $insertdata['name'] = $data['name'];
        $insertdata['addtime'] = time();
        $insertdata['m_id'] = $user_data['m_id'];

        $res = $this->loop_model->update_where('user', $update,array('Id'=>$data['id']));
        $res1 = $this->loop_model->insert('user_info', $insertdata);

        if ($this->db->trans_status() === TRUE) {
            $this->db->trans_commit();
            return 'y';
        } else {

            $this->db->trans_rollback();
            return '信息保存失败';
            //@todo 异常处理部分
        }
    }

    /**
     * 挂失会员卡
     * @return array
     */
    public function change_card($data)
    {
        if (empty($data['card_no'])) {
            return '会员卡号不能为空';
        }
        $user_data = $this->loop_model->get_where('user_card', array('card_no'=>$data['card_no']));
        if(!$user_data){
            return '会员卡错误';
        }
        $updata['card_no'] = $data['new_card_no'];
        $insert['user_card_id'] = $user_data['Id'];
        $insert['image'] = $data['image'];
        $insert['old_card_no'] = $data['card_no'];
        $insert['new_card_no'] = $data['new_card_no'];
        $insert['change_note'] = $data['change_note'];
        $insert['addtime'] = time();
        $this->db->trans_begin();
        $res = $this->loop_model->update_where('user_card', $updata,array('Id'=>$user_data['Id']));
        $res = $this->loop_model->insert('user_card_change', $insert);
        if ($this->db->trans_status() === TRUE) {
            $this->db->trans_commit();
            return 'y';
        } else {

            $this->db->trans_rollback();
            return '信息保存失败';
            //@todo 异常处理部分
        }
    }

    /**
     * 会员卡变更申请
     * @return array
     */
    public function user_card_up($data)
    {
        if (empty($data['id'])) {
            return '会员卡id不能为空';
        }
        $user_data = $this->loop_model->get_where('user_card', array('Id'=>$data['id']));
        if(!$user_data){
            return '会员卡错误';
        }
        //获取卡信息
        $insert_data = $user_data;
        $insert_data['user_card_id'] = $user_data['Id'];
        $insert_data['image'] = $data['image'];
        $insert_data['Id'] = NULl;
        $insert_data['cat'] = $data['cat'];
        $insert_data['note'] = $data['note'];
        $insert_data['rechargetime'] = time();  //申请时间
        if($data['cat'] == 2){
            $insert_data['name'] = $data['name'];
            $insert_data['account_name'] = $data['account_name'];
            $insert_data['reback_money'] = $data['reback_money'];
            $insert_data['account_number'] = $data['account_number'];

        }
        $res = $this->loop_model->insert('user_card_up', $insert_data);
        if ($res > 0) {
            $insert_data['Id'] = $res;
            $insert_data['endtime'] = date('Y-m-d',$insert_data["endtime"]);
            return array('status'=>'y','result'=>$insert_data);
        } else {
            return '信息保存失败';
        }
    }

    /**
     * 更换卡
     */
    public function user_card_verify($data)
    {
        if (empty($data['id'])) {
            return '变更申请的id不能为空';
        }
        if (empty($data['card_id'])) {
            return '未选中会员卡';
        }
        $user_data = $this->loop_model->get_where('user_card', array('Id'=>$data['id']));
        if(!$user_data){
            return '用户会员卡错误';
        }
        $card_data = $this->loop_model->get_where('card', array('Id'=>$data['card_id']));
        if(!$user_data){
            return '会员卡错误';
        }
        //获取卡信息
        $update['total_count'] = $user_data['total_count'] + $data['money'];
        $update['count'] = $user_data['count'] + $data['money'];
        $update['count'] = $user_data['count'] + $data['money'];
        $update['total_care_count'] = $user_data['total_care_count'] + $data['add_care'];
        $update['care_count'] = $user_data['care_count'] + $data['add_care'];
        $update['care_discount'] = $card_data['care_discount'];
        $update['people_discount'] = $card_data['people_discount'];
        $update['card_id'] = $data['card_id'];
        $update['time'] = $card_data['time'];
        $update['addtime'] = time();
        $update['endtime'] = strtotime("+". $card_data['time'] ." month",time());
        $this->db->trans_begin();
        $res = $this->loop_model->update_where('user_card',$update, array('Id'=>$data['id']));
        $res1 = $this->loop_model->update_where('user_card_up',array('status'=>1), array('user_card_id'=>$data['id']));
        if ($this->db->trans_status() === TRUE) {
            $this->db->trans_commit();
            return 'y';
        } else {

            $this->db->trans_rollback();
            return '信息保存失败';
            //@todo 异常处理部分
        }
    }

    /**
     * 会员信息
     * @return array
     */
    public function get_member($m_id)
    {
        $m_id = (int)$m_id;
        $this->load->model('loop_model');
        $member_data = $this->loop_model->get_where('member_user', array('m_id' => $m_id));
        if (!empty($member_data)) {
            return $member_data;
        }
    }

    /**
     * 资金类型
     * @return array
     */
    public function get_type($event)
    {
        $event = (int)$event;
        //1为增加2为减少
        switch ($event) {
            case 1:
                $type = 1;//下单获得
                break;
            case 2:
                $type = 1;//活动赠送
                break;
            case 3:
                $type = 2;//订单支付
                break;
            case 4:
                $type = 1;//系统充值
                break;
            case 5:
                $type = 2;//系统扣除
                break;
            default:
                $type = '';
        }
        //1为增加2为减少
        if ($type == 1 || $type == 2) {
            return $type;
        }
    }

    /**
     * 资金变动类型名称
     * @return array
     */
    public function get_type_name()
    {
        return array(1 => '下单获得', 2 => '活动赠送', 3 => '订单支付', 4 => '系统充值', 5 => '系统扣除');
    }
}
