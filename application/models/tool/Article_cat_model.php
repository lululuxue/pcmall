<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Article_cat_model extends CI_Model
{

    public function __construct()
    {
        /**
         * 载入数据库类
         */
        $this->load->database();
    }

    /**
     * 后台查询所有数据
     * @return array
     */
    public function get_all($reid = 0)
    {
        $reid = (int)$reid;
        $this->db->where('reid', $reid);
        $this->db->order_by('sortnum asc,id asc');
        $query = $this->db->get('article_cat');
        $list = $query->result_array();//echo $this->db->last_query();
        foreach ($list as $key) {
            $key['down'] = self::get_all($key['id']);
            $cat_list[] = $key;
        }
        return $cat_list;
    }

    /**
     * 查询指定ID的所有下级菜单id
     * @param int $reid id
     * @return array
     */
    public function get_reid_down($reid = '')
    {
        $reid = (int)$reid;
        if (!empty($reid)) {
            $id[] = $reid;
            $this->db->where(array('reid' => $reid));
            $query = $this->db->get('article_cat');
            $reid_list = $query->result_array();
            foreach ($reid_list as $key) {
                $id[] = $key['id'];
                $down_id = $this->get_reid_down($key['id']);
                if (!empty($down_id)) {
                    $id = array_merge($id, $down_id);
                }
            }
            return $id;
        }
    }
}
