<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Cat extends CI_Controller
{

    private $admin_data;//后台用户登录信息

    public function __construct()
    {
        parent::__construct();
        $this->load->helpers('admin_helper');
        $this->admin_data = admin_login();
        assign('admin_data', $this->admin_data);
        $this->load->model('loop_model');
    }

    /**
     * 服务项目列表
     */
    public function index()
    {
        //查到数据
        $this->load->model('service/service_model');
        $list = $this->service_model->get_all();//列表

        assign('list', $list);//print_r($list);
        display('/cat/service/list.html');
    }

    /**
     * 添加
     */
    public function add($reid = 0)
    {
        $reid = (int)$reid;
        assign('reid', $reid);
        $this->load->helpers('upload_helper');//加载上传文件插件
        display('/cat/service/add.html');
    }

    /**
     * 编辑
     */
    public function edit($id)
    {
        $id = (int)$id;
        if (!empty($id)) {
            $item = $this->loop_model->get_id('service', $id);
            assign('item', $item);
        }

        $this->load->helpers('upload_helper');//加载上传文件插件
        display('/cat/service/add.html');
    }

    /**
     * 保存数据
     */
    public function save()
    {
        if (is_post()) {
            $data_post = $this->input->post(NULL, true);
            $update_data = array(
                'name'    => $data_post['name'],
                'sortnum' => (int)$data_post['sortnum'],
                'reid'    => $data_post['reid'] != '' ? $data_post['reid'] : 0,
                'image'   => $data_post['image'],
                'show_image' => $data_post['show_image'],
                'desc_image' => $data_post['desc_image'],
                'flag'    => $data_post['flag'],
                'type_name'    => $data_post['type_name'],
                'amount'    => $data_post['amount'],
                'sub_desc'    => $data_post['sub_desc'],
                'desc'    => remove_xss($this->input->post('desc'))//单独过滤详情xss
            );

            if (empty($update_data['name'])) {
                error_json('分类名称不能为空');
            }
           // var_dump(remove_xss($this->input->post('desc')));
            if (!empty($data_post['id'])) {
                //修改数据
                $res = $this->loop_model->update_where('service', $update_data, array('id'=>$data_post['id']));
                //$this->loop_model->update_where('goods_desc', array('desc' => $data_post['desc']), array('goods_id' => $where['id']));
                admin_log_insert('修改分类' . $data_post['id']);
            } else {
                //增加数据
                $res = $this->loop_model->insert('service', $update_data);
                admin_log_insert('增加分类' . $res);
            }
            if (!empty($res) || $res == 0) {
                error_json('y');
            } else {
                error_json('保存失败');
            }
        } else {
            error_json('提交方式错误');
        }

    }

    /**
     * 删除数据
     */
    public function delete()
    {
        $id = (int)$this->input->post('id', true);
        if (empty($id)) error_json('id不能为空');
        $re_item = $this->loop_model->get_where('service', array('reid' => $id));
        if (!empty($re_item)) {
            error_json('下级栏目不为空不能删除');
        } else {
            $res = $this->loop_model->delete_id('service', $id);
            if (!empty($res)) {
                admin_log_insert('删除分类' . $id);
                error_json('y');
            } else {
                error_json('删除失败');
            }
        }
    }

    /**
     * 来源列表
     */
    public function source_list()
    {
        //查到数据
        $list = $this->loop_model->get_list('source','','','','id asc');//列表
        assign('list', $list);//print_r($list);
        display('/cat/source/list.html');
    }


    /**
     * 编辑/修改
     */
    public function edit_source($id)
    {
        $id = (int)$id;
        if (!empty($id)) {
            $item = $this->loop_model->get_id('source', $id);
            assign('item', $item);
        }

        $this->load->helpers('upload_helper');//加载上传文件插件
        display('/cat/source/add.html');
    }

    /**
     * 保存数据
     */
    public function save_source()
    {
        if (is_post()) {
            $data_post = $this->input->post(NULL, true);
            $update_data['source_name'] = $data_post['source_name'];

            if (empty($update_data['source_name'])) {
                error_json('来源名称不能为空');
            }

            if (!empty($data_post['id'])) {
                //修改数据
                $update_data['updatetime'] = date('Y-m-d H:i:s',time());
                $res = $this->loop_model->update_where('source', $update_data, array('id'=>$data_post['id']));
                //$this->loop_model->update_where('goods_desc', array('desc' => $data_post['desc']), array('goods_id' => $where['id']));
                admin_log_insert('修改来源' . $data_post['id']);
            } else {
                //增加数据
                $update_data['addtime'] = date('Y-m-d H:i:s',time());
                $res = $this->loop_model->insert('source', $update_data);
                admin_log_insert('增加来源' . $res);
            }
            if (!empty($res) || $res == 0) {
                error_json('y');
            } else {
                error_json('保存失败');
            }
        } else {
            error_json('提交方式错误');
        }

    }

    /**
     * 删除数据
     */
    public function delete_source()
    {
        $id = (int)$this->input->post('id', true);
        if (empty($id)) error_json('id不能为空');

        $res = $this->loop_model->delete_id('source', $id);
        if (!empty($res)) {
            admin_log_insert('删除来源' . $id);
            error_json('y');
        } else {
            error_json('删除失败');
        }

    }

    /**
     * 广告列表
     */
    public function ad_list()
    {
        $where_data['select'] = array('a.*,ap.name as position_name');
        $where_data['join']   = array(
            array('adv_position as ap', 'a.position_id=ap.id', 'left')
        );
        //查到数据
        $list = $this->loop_model->get_list('adv as a', $where_data, '', '', 'a.id desc');//列表
        assign('list', $list);
        display('/cat/ad/list.html');

    }

    /**
     * 添加/编辑广告
     */
    public function edit_ad($id = '')
    {
        $id = (int)$id;
        if (!empty($id)) {
            $item               = $this->loop_model->get_id('adv', $id);
            $item['start_time'] = date('Y-m-d H:i:s', $item['start_time']);
            $item['end_time']   = date('Y-m-d H:i:s', $item['end_time']);
            assign('item', $item);
        }
        //广告位列表
        $position_list = $this->loop_model->get_list('adv_position', array('where' => array('status' => '0')));
        assign('position_list', $position_list);

        $this->load->helpers('upload_helper');//加载上传文件插件
        display('/cat/ad/add.html');
    }

    /**
     * 删除广告
     */
    public function delete_ad()
    {
        $id = $this->input->post('id', true);
        if (empty($id)) error_json('id错误');
        $res = $this->loop_model->delete_id('adv', $id);
        if (!empty($res)) {
            admin_log_insert('删除广告' . $id);
            error_json('y');
        } else {
            error_json('删除失败');
        }
    }

    /**
     * 保存广告
     */
    public function save_ad()
    {
        if (is_post()) {
            $data_post   = $this->input->post(NULL, true);
            $update_data = array(
                'title'       => $data_post['title'],
                'type'        => (int)$data_post['type'],
                'position_id' => (int)$data_post['position_id'],
                'start_time'  => strtotime($data_post['start_time']),
                'end_time'    => strtotime($data_post['end_time']),
                'link'        => $data_post['link'],
                'sortnum'     => (int)$data_post['sortnum'],
            );
            //内容
            switch ($update_data['type']) {
                case 1:
                    $update_data['desc'] = $data_post['desc_1'];
                    break;
                case 2:
                    $update_data['desc'] = $data_post['desc_2'];
                    break;
                case 3:
                    $update_data['desc'] = $data_post['desc_3'];
                    break;
            }
            if (empty($update_data['title'])) {
                error_json('名称不能为空');
            }
            $this->load->helpers('form_validation_helper');
            if (!is_url($data_post['link']) && !empty($data_post['link'])) {
                error_json('链接地址错误');
            }
            if (!empty($data_post['id'])) {
                //修改数据
                $res = $this->loop_model->update_id('adv', $update_data, $data_post['id']);
                admin_log_insert('修改广告' . $data_post['id']);
            } else {
                //增加数据
                $res = $this->loop_model->insert('adv', $update_data);
                admin_log_insert('增加广告' . $res);
            }
            if (!empty($res)) {
                error_json('y');
            } else {
                error_json('保存失败');
            }
        } else {
            error_json('提交方式错误');
        }

    }

    /**
     * 充值优惠列表
     */
    public function recharge_list()
    {
        //查到数据
        $list = $this->loop_model->get_list('new_card','','','','sortnum asc');//列表
        assign('list', $list);//print_r($list);
        display('/cat/recharge/list.html');
    }

    /**
     * 新增、修改充值优惠
     */
    public function edit_recharge($id)
    {
        $id = (int)$id;
        if (!empty($id)) {
            $item               = $this->loop_model->get_id('new_card', $id);
            assign('item', $item);
        }
        $this->load->helpers('upload_helper');//加载上传文件插件
        display('/cat/recharge/add.html');
    }

    /**
     * 保存
     */
    public function save_recharge(){
        if (is_post()) {
            $data_post   = $this->input->post(NULL, true);
            $update_data = array(
                'name'         => $data_post['name'],
                'image'        => $data_post['image'],
                'count'        => $data_post['count'],
                'cue'        => $data_post['cue'],
                'add_money'   => $data_post['add_money'],
                'sortnum'     => (int)$data_post['sortnum']
            );
            if (!empty($data_post['Id'])) {
                //修改数据
                $update_data['updatetime'] = time();
                $res = $this->loop_model->update_id('new_card', $update_data, $data_post['Id']);
            } else {
                //增加数据
                $update_data['addtime'] = time();
                $res = $this->loop_model->insert('new_card', $update_data);
            }
            if (!empty($res)) {
                error_json('y');
            } else {
                error_json('保存失败');
            }
        } else {
            error_json('提交方式错误');
        }
    }

    /**
     * 删除优惠
     */
    public function delete_recharge()
    {
        $id = $this->input->post('id', true);
        if (empty($id)) error_json('id错误');
        $res = $this->loop_model->delete_id('new_card', $id);
        if (!empty($res)) {
            admin_log_insert('删除优惠' . $id);
            error_json('y');
        } else {
            error_json('删除失败');
        }
    }


    /**
     * 部门列表
     */
    public function department_list()
    {
        //查到数据
        $list = $this->loop_model->get_list('department','','','','id asc');//列表
        assign('list', $list);//print_r($list);
        display('/cat/department/list.html');
    }

    /**
     * 新增、修改部门
     */
    public function edit_department($id)
    {
        $id = (int)$id;
        if (!empty($id)) {
            $item               = $this->loop_model->get_id('department', $id);
            assign('item', $item);
        }
        display('/cat/department/add.html');
    }

    /**
     * 保存部门
     */
    public function save_department(){
        if (is_post()) {
            $data_post   = $this->input->post(NULL, true);
            $update_data = array(
                'name'         => $data_post['name'],
            );
            if (!empty($data_post['id'])) {
                //修改数据
                $update_data['updatetime'] = time();
                $res = $this->loop_model->update_id('department', $update_data, $data_post['id']);
            } else {
                //增加数据
                $update_data['addtime'] = time();
                $res = $this->loop_model->insert('department', $update_data);
            }
            if (!empty($res)) {
                error_json('y');
            } else {
                error_json('保存失败');
            }
        } else {
            error_json('提交方式错误');
        }
    }

    /**
     * 删除部门
     */
    public function delete_department()
    {
        $id = $this->input->post('id', true);
        if (empty($id)) error_json('id错误');
        $res = $this->loop_model->delete_id('department', $id);
        if (!empty($res)) {
            admin_log_insert('删除优惠' . $id);
            error_json('y');
        } else {
            error_json('删除失败');
        }
    }

    /**
     * 职位列表
     */
    public function job_list()
    {
        //查到数据
        $where_data['join'] = array(
            array('department f','f.id=a.department_id','left')
        );
        $where_data['select'] = array('a.*,f.name');
        $list = $this->loop_model->get_list('job a',$where_data,'','','a.id asc');//列表
        assign('list', $list);
        display('/cat/job/list.html');
    }

    /**
     * 新增、修改职位
     */
    public function edit_job($id)
    {
        $department_list = $this->loop_model->get_list('department', '','','','');
        assign('department_list', $department_list);
        $id = (int)$id;
        if (!empty($id)) {
            $item               = $this->loop_model->get_id('job', $id);
            assign('item', $item);
        }
        display('/cat/job/add.html');
    }

    /**
     * 保存部门
     */
    public function save_job(){
        if (is_post()) {
            $data_post   = $this->input->post(NULL, true);
            $update_data = array(
                'job_name'         => $data_post['job_name'],
                'department_id'   => $data_post['department_id'],
                'base_salary' => $data_post['base_salary'],
                'percentage'  => $data_post['percentage'],
            );
            if (!empty($data_post['id'])) {
                //修改数据
                $update_data['updatetime'] = time();
                $res = $this->loop_model->update_id('job', $update_data, $data_post['id']);
            } else {
                //增加数据
                $update_data['addtime'] = time();
                $res = $this->loop_model->insert('job', $update_data);
            }
            if ($res> 0) {
                error_json('y');
            } else {
                error_json('保存失败');
            }
        } else {
            error_json('提交方式错误');
        }
    }

    /**
     * 删除部门
     */
    public function delete_job()
    {
        $id = $this->input->post('id', true);
        if (empty($id)) error_json('id错误');
        $res = $this->loop_model->delete_id('job', $id);
        if (!empty($res)) {
            admin_log_insert('删除优惠' . $id);
            error_json('y');
        } else {
            error_json('删除失败');
        }
    }
    //*********************店铺管理*******************************************************
    /**
     * 服务项目列表
     */
    public function shop_list($reid = 0)
    {
         $this->load->model('service/service_model');
        $list = $this->service_model->get_shop_all();//列表
        assign('list', $list);//print_r($list);
        display('/cat/shop/list.html');
    }

    /**
     * 添加
     */
    public function shop_add($reid = 0)
    {
        $reid = (int)$reid;
        assign('reid', $reid);
        display('/cat/shop/add.html');
    }

    /**
     * 编辑
     */
    public function shop_edit($id)
    {
        $id = (int)$id;
        if (!empty($id)) {
            $item = $this->loop_model->get_id('shop', $id);
            assign('item', $item);
        }
        $this->load->helpers('upload_helper');//加载上传文件插件
        display('/cat/shop/add.html');
    }

    /**
     * 保存数据
     */
    public function shop_save()
    {
        if (is_post()) {
            $data_post = $this->input->post(NULL, true);
            $update_data = array(
                'name'    => $data_post['name'],
                'sortnum' => (int)$data_post['sortnum'],
                'reid'    => $data_post['reid'] != '' ? $data_post['reid'] : 0,
            );

            if (empty($update_data['name'])) {
                error_json('分类名称不能为空');
            }
            // var_dump(remove_xss($this->input->post('desc')));
            if (!empty($data_post['id'])) {
                //修改数据
                $update_data['updatetime'] = time();
                $res = $this->loop_model->update_where('shop', $update_data, array('id'=>$data_post['id']));
                admin_log_insert('修改店铺' . $data_post['id']);
            } else {
                //增加数据
                $update_data['addtime'] = time();
                $res = $this->loop_model->insert('shop', $update_data);
                admin_log_insert('增加店铺' . $res);
            }
            if (!empty($res) || $res == 0) {
                error_json('y');
            } else {
                error_json('保存失败');
            }
        } else {
            error_json('提交方式错误');
        }

    }

    /**
     * 删除数据
     */
    public function shop_delete()
    {
        $id = (int)$this->input->post('id', true);
        if (empty($id)) error_json('id不能为空');
        $re_item = $this->loop_model->get_where('shop', array('reid' => $id));
        if (!empty($re_item)) {
            error_json('下级分店不为空不能删除');
        } else {
            $res = $this->loop_model->delete_id('shop', $id);
            if (!empty($res)) {
                admin_log_insert('删除店铺' . $id);
                error_json('y');
            } else {
                error_json('删除失败');
            }
        }
    }

    /**
     * 公司资料列表
     */
    public function demo_list()
    {
        $list = $this->loop_model->get_list('demo');//列表
        assign('list', $list);//print_r($list);
        display('/cat/demo/list.html');
    }
    /**
     * 公司资料列表
     */
    public function add_demo($id)
    {
        $id = (int)$id;
        if (!empty($id)) {
            $item = $this->loop_model->get_id('demo', $id);
            assign('item', $item);
        }
        $this->load->helpers('upload_helper');//加载上传文件插件
        display('/cat/demo/add.html');
    }

    /**
     * 保存数据
     */
    public function demo_save()
    {
        if (is_post()) {
            $data_post = $this->input->post(NULL, true);
            $update_data = array(
                'name'    => $data_post['name'],
                'sortnum' => (int)$data_post['sortnum'],
                'image'    => $data_post['image'],
                'address'    => $data_post['address']
            );

            if (empty($update_data['name'])) {
                error_json('名称不能为空');
            }
            // var_dump(remove_xss($this->input->post('desc')));
            if (!empty($data_post['id'])) {
                //修改数据
                $res = $this->loop_model->update_where('demo', $update_data, array('id'=>$data_post['id']));
            } else {
                //增加数据
                $update_data['addtime'] = time();
                $res = $this->loop_model->insert('demo', $update_data);
            }
            if (!empty($res) || $res == 0) {
                error_json('y');
            } else {
                error_json('保存失败');
            }
        } else {
            error_json('提交方式错误');
        }

    }
     function delete_demo(){
         $id = $this->input->post('id', true);
         if (empty($id)) error_json('id错误');
         $res = $this->loop_model->delete_id('demo', $id);
         if (!empty($res)) {
             admin_log_insert('删除成功' . $id);
             error_json('y');
         } else {
             error_json('删除失败');
         }
     }

    /**
     * 最新动态设置
     */
    public function new_list()
    {
        $list = $this->loop_model->get_list('news');//列表
        assign('list', $list);//print_r($list);
        display('/cat/news/list.html');
    }

    /**
     * @param $id添加、修改最新动态
     */
    public function add_new($id)
    {
        $id = (int)$id;
        if (!empty($id)) {
            $item = $this->loop_model->get_id('news', $id);
            assign('item', $item);
        }
        $this->load->helpers('upload_helper');//加载上传文件插件
        display('/cat/news/add.html');
    }

    public function save_new()
    {
        if (is_post()) {
            $data_post = $this->input->post(NULL, true);
            $update_data = array(
                'name'    => $data_post['name'],
                'sortnum' => (int)$data_post['sortnum'],
                'image'   => $data_post['image'],
                'flag'    => $data_post['flag'],
                'desc'    => remove_xss($this->input->post('desc'))//单独过滤详情xss
            );

            if (empty($update_data['name'])) {
                error_json('动态名称不能为空');
            }
            // var_dump(remove_xss($this->input->post('desc')));
            if (!empty($data_post['id'])) {
                //修改数据
                $res = $this->loop_model->update_where('news', $update_data, array('id'=>$data_post['id']));
                //$this->loop_model->update_where('goods_desc', array('desc' => $data_post['desc']), array('goods_id' => $where['id']));
                admin_log_insert('修改动态' . $data_post['id']);
            } else {
                //增加数据
                $res = $this->loop_model->insert('news', $update_data);
                admin_log_insert('增加动态' . $res);
            }
            if (!empty($res) || $res == 0) {
                error_json('y');
            } else {
                error_json('保存失败');
            }
        } else {
            error_json('提交方式错误');
        }

    }

    /**
     * 通知手机列表
     */
    public function phone_list()
    {
        $list = $this->loop_model->get_list('phone_message');//列表
        assign('list', $list);//print_r($list);
        display('/cat/message/list.html');
    }

    /**
     * @param $id添加、修改手机
     */
    public function add_phone($id)
    {
        $id = (int)$id;
        if (!empty($id)) {
            $item = $this->loop_model->get_id('phone_message', $id);
            assign('item', $item);
        }
        $this->load->helpers('upload_helper');//加载上传文件插件
        display('/cat/message/add.html');
    }

    public function save_phone()
    {
        if (is_post()) {
            $data_post = $this->input->post(NULL, true);
            $update_data = array(
                'name'    => $data_post['name'],
                'phone'    => $data_post['phone']
            );

            if (empty($update_data['phone'])) {
                error_json('通知人手机不能为空');
            }
            if (empty($update_data['name'])) {
                error_json('通知人不能为空');
            }
            // var_dump(remove_xss($this->input->post('desc')));
            if (!empty($data_post['id'])) {
                //修改数据
                $update_data['updatetime'] = time();
                $res = $this->loop_model->update_where('phone_message', $update_data, array('id'=>$data_post['id']));
                //$this->loop_model->update_where('goods_desc', array('desc' => $data_post['desc']), array('goods_id' => $where['id']));
//                admin_log_insert('修改动态' . $data_post['id']);
            } else {
                //增加数据
                $update_data['addtime'] = time();
                $res = $this->loop_model->insert('phone_message', $update_data);
//                admin_log_insert('增加动态' . $res);
            }
            if (!empty($res) || $res == 0) {
                error_json('y');
            } else {
                error_json('保存失败');
            }
        } else {
            error_json('提交方式错误');
        }

    }

    /**
     * 删除通知人
     */
    public function phone_delete()
    {
        $id = (int)$this->input->post('id', true);
        if (empty($id)) error_json('id不能为空');

        $res = $this->loop_model->delete_id('phone_message', $id);
        if (!empty($res)) {
//            admin_log_insert('删除通知人' . $id);
            error_json('y');
        } else {
            error_json('删除失败');
        }

    }

    /**
     * 活动列表
     */
    public function actives_list()
    {
        $list = $this->loop_model->get_list('pre_actives','','','','is_delete asc');//列表
        assign('list', $list);//print_r($list);
        display('/cat/actives/list.html');
    }

    /**
     * @param $id添加、修改活动
     */
    public function add_actives($id)
    {
        $id = (int)$id;
        if (!empty($id)) {
            $item = $this->loop_model->get_id('pre_actives', $id);
            assign('item', $item);
        }
        $this->load->helpers('upload_helper');//加载上传文件插件
        display('/cat/actives/add.html');
    }

    public function save_actives()
    {
        if (is_post()) {
            $data_post = $this->input->post(NULL, true);
            $update_data['images']   = $data_post['images'];

            if (!empty($data_post['id'])) {
                //修改数据
                $update_data['updatetime'] = time();
                $res = $this->loop_model->update_where('pre_actives', $update_data, array('id'=>$data_post['id']));
                //$this->loop_model->update_where('goods_desc', array('desc' => $data_post['desc']), array('goods_id' => $where['id']));
//                admin_log_insert('修改动态' . $data_post['id']);
            } else {
                //增加数据
                $update_data['addtime'] = time();
                $res = $this->loop_model->insert('pre_actives', $update_data);
//                admin_log_insert('增加动态' . $res);
            }
            if (!empty($res) || $res == 0) {
                error_json('y');
            } else {
                error_json('保存失败');
            }
        } else {
            error_json('提交方式错误');
        }

    }

    /**
     * 删除活动
     */
    public function actives_delete()
    {
        $id = (int)$this->input->post('id', true);
        if (empty($id)) error_json('id不能为空');

        $res = $this->loop_model->update_where('pre_actives',array('is_delete'=>1), array('id'=>$id));
        if (!empty($res)) {
//            admin_log_insert('删除通知人' . $id);
            error_json('y');
        } else {
            error_json('删除失败');
        }

    }

    /**
     * 还原活动
     */
    public function actives_recycle()
    {
        $id = (int)$this->input->post('id', true);
        if (empty($id)) error_json('id不能为空');
        $res = $this->loop_model->update_where('pre_actives',array('is_delete'=>1), array('is_delete'=>0));
        $res = $this->loop_model->update_where('pre_actives',array('is_delete'=>0), array('id'=>$id));
        if (!empty($res)) {
//            admin_log_insert('删除通知人' . $id);
            error_json('y');
        } else {
            error_json('删除失败');
        }
    }

    /**
     * 展示图片
     */
    public function actives_img($id)
    {
        if (empty($id)) error_json('id不能为空');

        $info = $this->loop_model->get_id('pre_actives', $id);
        assign('info',$info);
        display('/cat/actives/img.html');
    }



}
