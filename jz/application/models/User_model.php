<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class User_model extends CI_Model
{

    public $m_id;

    public function __construct()
    {
        /**
         * 载入数据库类
         */
        $this->load->database();
    }

    /**
     * 查询用户是否已经存在
     * @param string $username 用户名
     * @return array
     */
    public function repeat_username($username = '')
    {
        if (!empty($username)) {
            $this->db->limit(1);
            $query = $this->db->get_where('user', array('username' => $username));//echo $this->db->last_query()."<br>";
            return $query->row_array();
        }
    }

    /**
     * 更新数据
     * @return array
     */
    public function update($data_post = array())
    {
        $update_data = array(
            'username'=>$data_post['username'],
            'name'=>$data_post['name'],
            'updatetime'=>time(),
        );

        $this->load->model('loop_model');
        if (!empty($data_post['id'])) {
            //修改数据
            $res = $this->loop_model->update_where('user', $update_data, array('Id' => $data_post['id']));

        } else {
            //增加数据
            if (empty($data_post['username'])) {
                return '用户名不能为空';
            } else {
                //判断用户名是否重复
                $member_data = self::repeat_username($data_post['username']);
                if (!empty($member_data)) {
                    //手机号码已经存在
                    $res = $this->loop_model->update_where('user', array('endtime' => time()), array('Id' => $member_data['Id']));
                    //return '用户已存在';
                    return 'y';
                } else {
                    //查询推荐用户是否存在
                    if (!empty($data_post['flag_user'])) {
                        $flag_user_query = $this->db->get_where('user', array('Id' => $data_post['flag_user']));
                        $flag_user_data  = $flag_user_query->row_array();
                        if (empty($flag_user_data)) {
                            $data_post['flag_user'] = 0;
                        }
                    }
                    $salt        = get_rand_num();
                    $insert_data = array(
                        'username'   => $data_post['username'],
                        'salt'       => $salt,
                        'name'   =>   $data_post['name'],
                        'flag_user'  => $data_post['flag_user'] != '' ? $data_post['flag_user'] : 0,
                        'addtime'  => time()
                    );

                    $this->db->trans_begin();
                    $this->db->insert('user', $insert_data);
                    $m_id = $this->db->insert_id();
                    //增加用户卡数据
                    /*
                    $insert_card = array(
                        'card_no'=>$data_post['username'],
                        'people_discount'=>10,
                        'care_discount'=>10,
                        'count'=>0,
                        'total_count'=>0,
                        'time'=>'',
                        'addtime'=>time(),
                        'endtime'=>'',
                        'm_id'=>$m_id,
                        'card_id'=> 1,
                        'type' => 0
                    );
                    */
                    $insert_card = array(
                    'card_no'=>$data_post['username'],
                        'count'=>0,
                        'total_count'=>0,
                        'addtime'=>time(),
                        'm_id'=>$m_id
                    );
                    $res = $this->loop_model->insert('user_new_card', $insert_card);
                    if ($this->db->trans_status() === TRUE) {
                        $this->db->trans_commit();
                        return 'y';
                    } else {
                        $this->db->trans_rollback();
                        return '信息保存失败';
                        //@todo 异常处理部分
                    }

                    //发送推荐成功消息
                    /*
                    if (!empty($flag_user_data)) {

                        $oauth_query = $this->db->get_where('user', array('id' => $flag_user_data['id']));
                        $oauth_data  = $oauth_query->row_array();
                        if (!empty($oauth_data['oauth_id'])) {
                            $this->load->library('wechat/message_template');
                            $update_data['full_name'] != '' ? $recommended = $update_data['full_name'] : $recommended = $insert_data['username'];
                            $this->message_template->flag_user($oauth_data['oauth_id'], $flag_user_data['username'], $recommended);
                        }
                    }
                     */
                }
            }
        }
        if (!empty($res)) {
            return 'y';
        } else {
            return '保存失败';
        }
    }

    /**
     * 更新数据
     * @return array
     */
    public function update_per($data_post = array())
    {
        $update_data = array(
            'username'=>$data_post['username'],
            'name'=>$data_post['name'],
            'updatetime'=>time(),
        );
        $this->load->model('loop_model');

            //增加数据
            if (empty($data_post['username'])) {
                return '用户名不能为空';
            } else {
                //判断用户名是否重复
                $member_data = self::repeat_username($data_post['username']);
                if (!empty($member_data)) {
                    //手机号码已经存在
                    $res = $this->loop_model->update_where('user', array('endtime' => time()), array('Id' => $member_data['Id']));
                    //return '用户已存在';
                    return 'y';
                } else {
                    //查询推荐用户是否存在
                    if (!empty($data_post['flag_user'])) {
                        $flag_user_query = $this->db->get_where('user', array('Id' => $data_post['flag_user']));
                        $flag_user_data  = $flag_user_query->row_array();
                        if (empty($flag_user_data)) {
                            $data_post['flag_user'] = 0;
                        }
                    }
                    $salt        = get_rand_num();
                    $insert_data = array(
                        'username'   => $data_post['username'],
                        'address'   => $data_post['address'],
                        'company'   => $data_post['company'],
                        'is_big'   => $data_post['is_big'],
                        'salt'       => $salt,
                        'name'   =>   $data_post['name'],
                        'flag_user'  => $data_post['flag_user'] != '' ? $data_post['flag_user'] : 0,
                        'addtime'  => time()
                    );
                    $res = $this->db->insert('user', $insert_data);
                    $m_id = $this->db->insert_id();
                    $this->db->trans_begin();
                    //增加用户卡数据
//                    $insert_card = array(
//                        'card_no'=>$data_post['username'],
//                        'people_discount'=>10,
//                        'care_discount'=>10,
//                        'count'=>0,
//                        'total_count'=>0,
//                        'time'=>'',
//                        'addtime'=>time(),
//                        'endtime'=>'',
//                        'm_id'=>$m_id,
//                        'card_id'=> 1,
//                        'type' => 0
//                    );
                   $insert_card = array(
                        'card_no'=>$data_post['username'],
                        'count'=>0,
                        'total_count'=>0,
                        'addtime'=>time(),
                        'm_id'=>$m_id
                    );
                    $res = $this->loop_model->insert('user_new_card', $insert_card);
                    if ($this->db->trans_status() === TRUE) {
                        $this->db->trans_commit();
                        return 'y';
                    } else {
                        $this->db->trans_rollback();
                        return '信息保存失败';
                        //@todo 异常处理部分
                    }

                    //发送推荐成功消息
                    /*
                    if (!empty($flag_user_data)) {

                        $oauth_query = $this->db->get_where('user', array('id' => $flag_user_data['id']));
                        $oauth_data  = $oauth_query->row_array();
                        if (!empty($oauth_data['oauth_id'])) {
                            $this->load->library('wechat/message_template');
                            $update_data['full_name'] != '' ? $recommended = $update_data['full_name'] : $recommended = $insert_data['username'];
                            $this->message_template->flag_user($oauth_data['oauth_id'], $flag_user_data['username'], $recommended);
                        }
                    }
                     */
                }
            }
        if (!empty($res) && $res > 0) {
            return 'y';
        } else {
            return '保存失败';
        }
    }
}
