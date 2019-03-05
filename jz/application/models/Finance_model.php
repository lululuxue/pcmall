<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Finance_model extends CI_Model
{

    public function __construct()
    {
        /**
         * 载入数据库类
         */
        $this->load->database();
    }

    /**
     * 新增日常开支
     */
    public function update($data,$user_data)
    {
        //数据验证
        if (empty($data['name'])) {
            return '开支项目不能为空';
        }
        if (empty($data['amount'])) {
            return '开支金额不能为空';
        }
        if (empty($data['company'])) {
            return '开支公司不能为空';
        }
        if (empty($data['people'])) {
            return '开支人不能为空';
        }
        if (empty($data['time'])) {
            return '开支时间不能为空';
        }
        if (empty($data['reason'])) {
            return '开支事由不能为空';
        }
        $insert_data['name']  = $data['name'];
        $insert_data['amount']  = $data['amount'];
        $insert_data['company']  = $data['company'];
        $insert_data['people']  = $data['people'];
        $insert_data['time']  = $data['time'];
        $insert_data['reason']  = $data['reason'];
        $insert_data['note']  = $data['note'];
        $insert_data['addtime']  = time();
        $insert_data['add_people']  = $user_data['full_name'];
        $res = $this->loop_model->insert('finance_everyday', $insert_data);
        if($res > 0){
            return 'y';
        }else{
            return '信息保存失败';
        }

    }

    public function verify($data)
    {
        if(!$data['id']){
            return 'id不能为空';
        }

        $info = $this->loop_model->get_where('finance_everyday',array('id'=>$data['id']));
        if(!$info){
            return '此记录不存在';
        }
        $where = array('id'=>$data['id']);
        $update_data ['status'] = $data['status'];
        $res = $this->loop_model->update_where('finance_everyday',$update_data,$where);
        if($res > 0){
            return 'y';
        }else{
            return '信息保存失败';
        }

    }

    /**
     * 删除小区数据
     */
    public function delete($data)
    {
        //数据验证
        if (empty($data['id'])) {
            return '小区id不能为空';
        }
        $res = $this->loop_model->get_where('areas_data',array('id'=>$data['id'],'is_delete'=>1));
        if(!$res){
            return "该小区数据不存在";
        }
        $res1 = $this->loop_model->update_where('areas_data',array('is_delete'=>2),array('id'=>$data['id']));
        if($res1){
            return 'y';
        }else{
            return '删除失败';
        }
    }

    /**
     * 派工传单
     */
    public function insert($data)
    {
        //数据验证
        if (empty($data['id'])) {
            return '小区id不能为空';
        }
        //array_filter除去数组中的空字符元素
        if (empty(array_filter($data['people']))) {
            return '工作人员不能为空';
        }
        if (empty($data['worktime'])) {
            return '服务时间不能为空';
        }
        $res = $this->loop_model->get_where('areas_data',array('id'=>$data['id'],'is_delete'=>1));
        if(!$res){
            return "该小区数据不存在";
        }
        $insert['areas_data_id'] = $data['id'];
        $insert['addtime'] = time();
        $insert['worktime'] = strtotime($data['worktime']);
        if(is_array($data['people'])){
            $insert['work_people'] = implode('、',array_filter($data['people']));
        }else{
            $insert['work_people'] = $data['people'];
        }

        $res1 = $this->loop_model->insert('broadcast_order',$insert);
        if($res1 > 0){
            return 'y';
        }else{
            return '添加失败';
        }

    }

    /**
     * 修改工单
     */

    public function update_work($data,$user)
    {
        //数据验证
        if (empty($data['broadcast_order_id'])) {
            return '工单id不能为空';
        }
        if (empty($data['salary'])) {
            return '工资不能为空';
        }
        $res = $this->loop_model->get_where('broadcast_order',array('id'=>$data['broadcast_order_id'],'is_del'=>0));
        if(!$res){
            return "此工单不存在";
        }
       foreach($data['content'] as $key=>$val){
           foreach($val as $k=>$v){
              $list[$k][] = $v.'-'.$data['content1'][$key][$k].'-'.$data['content2'][$key][$k];
              $note[$k][] = $v.'栋'.$data['content1'][$key][$k].'单元'.$data['content2'][$key][$k];
           }
       }
       $array_people = explode('、',$res['work_people']);
        $total_salary = 0;
       foreach($array_people as $key=>$val){
               $user_data[] = $val.':'.implode('|',$list[$key]);
               $content[] = implode(' ',$note[$key]);
               $people_salary[] = $val.':'.$data['salary'][$key];
               $total_salary += $data['salary'][$key];
       }
        $user_data = implode('||',$user_data);
        $content = implode(' ',$content);
        $people_salary = implode(' ',$people_salary);

        $update['people_content'] = $user_data;
        $update['content'] = $content;
        $update['status'] = 1;//已服务
        $update['people_salary'] = $people_salary;
        $update['total_salary'] = $total_salary;
        $update['update_people'] = $user['job_no'];

        $where = array('id'=>$data['broadcast_order_id']);
        $res1 = $this->loop_model->update_where('broadcast_order',$update,$where);
        if($res1 > 0){
            return 'y';
        }else{
            return '录入失败';
        }
    }

    /**
     * 考勤
     */

    public function check($data,$user)
    {
        //数据验证
        if (empty($data['broadcast_order_id'])) {
            return '工单id不能为空';
        }
        $res = $this->loop_model->get_where('broadcast_order',array('id'=>$data['broadcast_order_id'],'is_del'=>0));
        if(!$res){
            return "此工单不存在";
        }

        $update['status'] = 2;//已服务
        $update['check_people'] = $user['job_no'];
        $update['checktime'] = time();

        $where = array('id'=>$data['broadcast_order_id']);
        $res1 = $this->loop_model->update_where('broadcast_order',$update,$where);
        if($res1 > 0){
            return 'y';
        }else{
            return '录入失败';
        }

    }

    /**
     * 根据父栏目id获取列表
     * @param int $parent_id 父栏目id
     * @return array
     */
    public function get_list($parent_id = '0')
    {
        if ($parent_id != '') {
            $this->db->where(array('parent_id' => $parent_id));
            $this->db->order_by('sortnum asc,area_id asc');
            $query = $this->db->get('areas');
            $list = $query->result_array();
            foreach ($list as $key) {
                $area_list[$key['area_id']] = $key['area_name'];
            }
            return $area_list;
        }
        return false;
    }

    /**
     * 根据地区id获取名称
     * @param int array $id 数据id或者数据id数组
     * @return array
     */
    public function get_name($id = FALSE)
    {
        if (!empty($id)) {
            if (is_array($id)) {
                $this->db->where_in('area_id', $id);
                $query = $this->db->get('areas');
                $list = $query->result_array();
                foreach ($list as $k) {
                    $area[$k['area_id']] = $k['area_name'];
                }
            } else {
                $query = $this->db->get_where('areas', array('area_id' => $id));//echo $this->db->last_query()."<br>";
                $row = $query->row_array();
                $area[$row['area_id']] = $row['area_name'];
            }
            return $area;
        }
        return false;
    }
}
