<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Article extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->helpers('web_helper');
        //$this->member_data = member_login();
        $this->load->model('loop_model');
    }

    /**
     * 列表
     */
    public function index()
    {
        //搜索条件start
        $cat_id = (int)$this->input->get_post('cat_id');

        $search_where = array(
            'cat_id' => $cat_id,
        );
        assign('search_where', $search_where);
        //搜索条件end
        //文章分类
        $article_cat_list = $this->loop_model->get_list('article_cat', array(), '', '', 'sortnum asc,id desc');
        assign('article_cat_list', $article_cat_list);

        //当前栏目信息
        $cat_data = $this->loop_model->get_id('article_cat', $cat_id);
        assign('cat_data', $cat_data);
        display('/article/index.html');
    }

    /**
     * ajax列表
     */
    public function ajax_index()
    {
        $pagesize = 20;//分页大小
        $page     = (int)$this->input->get_post('per_page');
        $page <= 1 ? $page = 1 : $page = $page;//当前页数
        //搜索条件start
        $cat_id = (int)$this->input->get_post('cat_id');
        if (!empty($cat_id)) $where_data['where']['cat_id'] = $cat_id;
        $search_where = array(
            'cat_id' => $cat_id,
        );
        assign('search_where', $search_where);
        //查到数据
        $article_list = $this->loop_model->get_list('article', $where_data, $pagesize, $pagesize * ($page - 1), 'id desc');//列表
        foreach ($article_list as $key) {
            $key['desc']    = cn_substr(strip_tags($key['desc']), 100);
            $key['addtime'] = date('Y-m-d', $key['addtime']);
            $list[]         = $key;
        }
        //开始分页start
        $all_rows   = $this->loop_model->get_list_num('article', $where_data);//所有数量;
        $page_count = ceil($all_rows / $pagesize);
        //开始分页end

        $res = array('list' => $list, 'page_count' => $page_count);
        error_json($res);
    }

    /**
     * 文章详情
     */
    public function view()
    {
        $id           = (int)$this->input->get_post('id');
        $m_id         = $this->member_data['id'];
        $article_data = $this->loop_model->get_id('article', $id);
        assign('article_data', $article_data);
        display('/article/view.html');
    }
}
