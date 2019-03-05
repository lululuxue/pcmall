<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Goods extends CI_Controller
{

    private $admin_data;//后台用户登录信息

    public function __construct()
    {
        parent::__construct();
        $this->load->helpers('manager_helper');
        $this->admin_data = manager_login();
        assign('admin_data', $this->admin_data);
        $this->load->model('loop_model');
    }

    /**
     * 列表
     */
    public function index()
    {
        $pagesize = 20;//分页大小
        $page     = (int)$this->input->get('per_page');
        $page <= 1 ? $page = 1 : $page = $page;//当前页数
        //搜索条件start
        //状态
        $status = $this->input->post_get('status');
        if (isset($status) && $status != '') {
            $where_data['where']['g.status'] = $status;
        } else {
            $where_data['where']['g.status!='] = '1';
        }
        //来源店铺
        $shop_id = $this->input->post_get('shop_id');
        if ($shop_id != '') $where_data['where']['shop_id'] = $shop_id;

        //分类
        $cat_id = $this->input->post_get('cat_id');
        if (!empty($cat_id)) {
            $this->load->model('goods/category_model');
            $cat_id_list                      = $this->category_model->get_reid_down($cat_id);
            $where_data['where_in']['cat_id'] = $cat_id_list;
        }

        //品牌
        $brand_id = $this->input->post_get('brand_id');
        if (!empty($brand_id)) $where_data['where']['brand_id'] = $brand_id;

        //关键字
        $name = $this->input->post_get('name');
        if (!empty($name)) $where_data['like']['g.name'] = $name;

        //推荐类型
        $flag_type = $this->input->post_get('flag_type');
        if (!empty($flag_type)) $where_data['where'][$flag_type] = 1;

        //库存预警
        $goods_store_nums = $this->input->post_get('goods_store_nums');
        if (!empty($goods_store_nums)) $where_data['where']['g.store_nums<='] = config_item('goods_store_nums');
        $search_where = array(
            'status'           => $status,
            'shop_id'          => $shop_id,
            'cat_id'           => $cat_id,
            'brand_id'         => $brand_id,
            'name'             => $name,
            'flag_type'        => $flag_type,
            'goods_store_nums' => $goods_store_nums,
        );
        assign('search_where', $search_where);

        $where_data['select'] = array('g.*,cat.name as cat_name');
        $where_data['join']   = array(
            array('goods_category as cat', 'g.cat_id=cat.id', 'left')
        );
        //搜索条件end
        //查到数据
        $list_data = $this->loop_model->get_list('goods as g', $where_data, $pagesize, $pagesize * ($page - 1), 'g.id desc');//列表
        foreach ($list_data as $key) {
            $key['market_price'] = format_price($key['market_price']);
            $key['sell_price']   = format_price($key['sell_price']);
            $list[]              = $key;
        }
        assign('list', $list);
        //开始分页start
        $all_rows = $this->loop_model->get_list_num('goods as g', $where_data);//所有数量
        assign('page_count', ceil($all_rows / $pagesize));
        //开始分页end

        assign('goods_status', array('0' => '上架', 2 => '下架', 3 => '等待审核', 4 => '审核拒绝'));//商品状态
        //商品分类
        $this->load->model('goods/category_model');
        $cat_list = $this->category_model->get_all();
        assign('cat_list', $cat_list);

        //店铺列表
        $shop_list = $this->loop_model->get_list('member_shop', array('select' => 'm_id,shop_name', 'where' => array('status' => '0')), '', '', 'm_id asc');
        assign('shop_list', $shop_list);

        //品牌列表
        $brand_list = $this->loop_model->get_list('goods_brand', array('select' => 'id,name'), '', '', 'sortnum asc,id desc');
        assign('brand_list', $brand_list);
        display('/goods/goods/list.html');
    }


    /**
     * 添加编辑
     */
    public function edit($id = '')
    {
        $id = (int)$id;
        if (!empty($id)) {
            $this->load->model('goods/goods_model');
            $item = $this->goods_model->admin_edit($id);
            assign('item', $item);
        }

        //商品分类
        $this->load->model('goods/category_model');
        $cat_list = $this->category_model->get_all();
        assign('cat_list', $cat_list);

        //店铺列表
        $shop_list = $this->loop_model->get_list('member_shop', array('select' => 'm_id,shop_name', 'where' => array('status' => '0')), '', '', 'm_id asc');
        assign('shop_list', $shop_list);

        $this->load->helpers('upload_helper');//加载上传文件插件
        display('/goods/goods/add.html');
    }

    /**
     * 保存数据
     */
    public function save()
    {
        if (is_post()) {
            $data_post = $this->input->post(NULL, true);
            $this->load->model('goods/goods_model');
            $res = $this->goods_model->update($data_post, 0);
            error_json($res);
        } else {
            error_json('提交方式错误');
        }

    }

    /**
     * 删除数据到回收站
     */
    public function delete_recycle()
    {
        $id = $this->input->post('id', true);
        if (empty($id)) error_json('id错误');
        $res = $this->loop_model->update_id('goods', array('status' => 1), $id);
        if (!empty($res)) {
            if (is_array($id)) $id = join(',', $id);
            admin_log_insert('删除商品到回收站' . $id);
            error_json('y');
        } else {
            error_json('删除失败');
        }
    }

    /**
     * 回收站还原
     */
    public function reduction_recycle()
    {
        $id = $this->input->post('id', true);
        if (empty($id)) error_json('id错误');
        $res = $this->loop_model->update_id('goods', array('status' => 3), $id);
        if (!empty($res)) {
            if (is_array($id)) $id = join(',', $id);
            admin_log_insert('还原商品' . $id);
            error_json('y');
        } else {
            error_json('还原失败');
        }
    }

    /**
     * 彻底删除数据
     */
    public function delete()
    {
        $id = $this->input->post('id', true);
        if (empty($id)) error_json('id错误');
        $res = $this->loop_model->delete_id('goods', $id);
        if (!empty($res)) {
            if (is_array($id)) $id = join(',', $id);
            admin_log_insert('彻底删除商品' . $id);
            error_json('y');
        } else {
            error_json('删除失败');
        }
    }

    /**
     * 修改数据状态
     */
    public function update_status()
    {
        $id     = $this->input->post('id', true);
        $status = $this->input->get_post('status', true);
        if (empty($id) || $status == '') error_json('id错误');
        $update_data['status'] = (int)$status;
        if ($status == 0) {
            $update_data['up_time'] = time();
        } elseif ($status == 3) {
            $update_data['down_time'] = time();
        }

        $res = $this->loop_model->update_id('goods', $update_data, $id);
        if (!empty($res)) {
            if (is_array($id)) $id = join(',', $id);
            admin_log_insert('修改商品status为' . $status . 'id为' . $id);
            error_json('y');
        } else {
            error_json('修改失败');
        }
    }

    /**
     * 修改数据推荐状态
     */
    public function update_flag()
    {
        $id    = $this->input->post('id', true);
        $type  = $this->input->get_post('type', true);
        $value = $this->input->get_post('value', true);
        if (empty($id) || empty($type) || $value == '') error_json('参数错误');
        $value = (int)$value;
        if ($type == 'is_hot' || $type == 'is_new' || $type == 'is_flag') {
            $update_data[$type] = $value;
            $res                = $this->loop_model->update_id('goods', $update_data, $id);
            if (!empty($res)) {
                if (is_array($id)) $id = join(',', $id);
                admin_log_insert('修改商品' . $type . '为' . $value . 'id为' . $id);
                error_json('y');
            } else {
                error_json('修改失败');
            }
        }
    }

    /**
     * 查询分类下所有品牌
     */
    public function cat_brand()
    {
        $where_data = array();
        $cat_id     = $this->input->post('cat_id', true);
        if (!empty($cat_id)) $where_data['sql'] = "find_in_set(" . $cat_id . ", cat_id)";
        $list = $this->loop_model->get_list('goods_brand', $where_data, '', '', 'sortnum asc,id asc');
        if (!empty($list)) {
            error_json($list);
        } else {
            error_json('没有数据');
        }
    }

    /**
     * 查询分类下所有模型
     */
    public function cat_model()
    {
        $cat_id                        = $this->input->post('cat_id', true);
        $where_data['where']['status'] = 0;
        if (!empty($cat_id)) $where_data['sql'] = "find_in_set(" . $cat_id . ", cat_id)";
        $list = $this->loop_model->get_list('goods_model', $where_data, '', '', 'id asc');
        if (!empty($list)) {
            error_json($list);
        } else {
            error_json('没有数据');
        }
    }

    /**
     * 根据id获得商品的模型json数据
     */
    public function goods_model_select()
    {
        $model_id = $this->input->post('model_id', true);
        $model_id = (int)$model_id;
        if (empty($model_id)) error_json('id错误');

        $goods_id = $this->input->post('goods_id', true);
        //扩展属性
        $goods_id        = (int)$goods_id;
        $goods_attr_data = array();
        if (!empty($goods_id)) {
            $attr_list = $this->loop_model->get_list('goods_attr', array('where' => array('goods_id' => $goods_id, 'model_id' => $model_id)));
            foreach ($attr_list as $k) {
                if (strpos($k['attr_value'], ',') !== false) {
                    $k['attr_value'] = explode(',', $k['attr_value']);
                }
                $goods_attr_data[$k['attr_id']] = $k['attr_value'];
            }
        }

        //查询扩展属性
        $attr_data = $this->loop_model->get_list('goods_model_attr', array('where' => array('model_id' => $model_id)), '', '', 'id asc');
        foreach ($attr_data as $k) {
            $k['value']   = explode(',', $k['value']);
            $k['checked'] = $goods_attr_data[$k['id']];
            $model_attr[] = $k;
        }
        echo ch_json_encode($model_attr);
    }
}
