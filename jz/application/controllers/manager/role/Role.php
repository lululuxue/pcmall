<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Role extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->helpers('manager_helper');
        $this->admin_data = manager_login();
        assign('admin_data', $this->admin_data);
        $this->load->model('loop_model');
    }

    /**
     * 管理员列表
     */
    public function admin_list()
    {
        $pagesize = 20;//分页大小
        $page     = (int)$this->input->get('per_page');
        $page <= 1 ? $page = 1 : $page = $page;//当前页数
        //搜索条件start
        $where_data = array();
        $status     = $this->input->post_get('status');
        if ($status >= '0') $where_data['where']['status'] = $status;
        //角色
        $role_id = $this->input->post_get('role_id');
        if ($role_id != '') $where_data['where']['role_id'] = $role_id;
        //用户名
        $username = $this->input->post_get('username');
        if (!empty($username)) $where_data['like']['username'] = $username;
        $search_where = array(
            'status'   => $status,
            'role_id'  => $role_id,
            'username' => $username,
        );
        assign('search_where', $search_where);
        //搜索条件end
        //查到数据
        $list = $this->loop_model->get_list('admin', $where_data, $pagesize, $pagesize * ($page - 1));//列表
        assign('list', $list);
        //开始分页start
        $all_rows = $this->loop_model->get_list_num('admin', $where_data);//所有数量
        assign('page_count', ceil($all_rows / $pagesize));
        //开始分页end

        //角色列表
        $role_data    = $this->loop_model->get_list('role', '');
        $role_list[0] = array('id' => 0, 'name' => '超级管理员');
        foreach ($role_data as $key) {
            $role_list[$key['id']] = $key;
        }
        assign('role_list', $role_list);

        assign('status', array('0' => '正常', 1 => '锁定'));//状态
        display('/admin/admin/list.html');
    }

    /**
     * 添加编辑
     */
    public function edit($id = '')
    {
        $id = (int)$id;
        if (!empty($id)) {
            $admin = $this->loop_model->get_id('admin', $id);
            assign('item', $admin);
        }
        //角色列表
        $role_list   = $this->loop_model->get_list('role', array('where' => array('status'=>0)));
        $role_list[] = array('id' => 0, 'name' => '超级管理员');
        assign('role_list', $role_list);
        display('/admin/admin/add.html');
    }

    /**
     * 保存数据
     */
    public function save()
    {
        if (is_post()) {
            $data_post = $this->input->post(NULL, true);
            $this->load->model('admin_model');
            $res = $this->admin_model->update($data_post);
            if (!empty($res)) {
                error_json($res);
            } else {
                error_json('保存失败');
            }
        } else {
            error_json('提交方式错误');
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
        $status                = (int)$status;
        $update_data['status'] = $status;
        $res                   = $this->loop_model->update_id('admin', $update_data, $id);
        if (!empty($res)) {
            if (is_array($id)) $id = join(',', $id);
            admin_log_insert('修改管理员status为' . $status . 'id为' . $id);
            error_json('y');
        } else {
            error_json('操作失败');
        }
    }

    /**
     * 彻底删除数据
     */
    public function delete()
    {
        $id = $this->input->post('id', true);
        if (empty($id)) error_json('id错误');
        $res = $this->loop_model->delete_where('admin', array('where_in' => array('id' => $id)));
        if (!empty($res)) {
            if (is_array($id)) $id = join(',', $id);
            admin_log_insert('彻底删除管理员' . $id);
            error_json('y');
        } else {
            error_json('删除失败');
        }
    }

    /**
     * 验证会员名是否存在
     */
    public function repeat_username()
    {
        $username = $this->input->post('param', true);
        if (!empty($username)) {
            $this->load->model('admin_model');
            $member_data = $this->admin_model->repeat_username($username);
            if (!empty($member_data)) {
                error_json('用户名已经存在');
            } else {
                error_json('y');
            }
        }
    }

    /**
     * 修改自己的密码
     */
    public function update_password()
    {
        $id = $this->admin_data['id'];
        if (!empty($id)) {
            $admin = $this->loop_model->get_id('admin', $id, 'username');
            assign('item', $admin);
        }

        display('/admin/admin/update_password.html');
    }

    /**
     * 修改自己的密码保存
     */
    public function update_password_save()
    {
        $id = $this->admin_data['id'];
        if (!empty($id)) {
            $data_post = $this->input->post(NULL, true);
            $admin     = $this->loop_model->get_id('admin', $id);
            if ($admin['password'] != md5(md5($data_post['old_password']) . $admin['salt'])) {
                error_json('原密码错误');
            } else {
                if ($data_post['password'] != $data_post['repassword']) {
                    error_json('两次密码不一样');
                } else {
                    $update_data['salt']     = get_rand_num();
                    $update_data['password'] = md5(md5($data_post['password']) . $update_data['salt']);
                    $res                     = $this->loop_model->update_id('admin', $update_data, $id);
                    if ($res) {
                        error_json('y');
                    } else {
                        error_json('修改失败');
                    }
                }
            }
        }
    }

    /**
     * 列表
     */
    public function role_list()
    {
        $pagesize = 20;//分页大小
        $page     = (int)$this->input->get('per_page');
        $page <= 1 ? $page = 1 : $page = $page;//当前页数
        //搜索条件start
        $status = $this->input->post_get('status');
        if (empty($status)) $status = 0;
        if (isset($status)) $where_data['where']['status'] = $status;
        $search_where                   = array(
            'status' => $status,
        );
        assign('search_where', $search_where);
        //搜索条件end
        //查到数据
        $list = $this->loop_model->get_list('role', $where_data, $pagesize, $pagesize * ($page - 1), 'name desc');//列表
        assign('list', $list);
        //开始分页start
        $all_rows = $this->loop_model->get_list_num('role', array());//所有数量
        assign('page_count', ceil($all_rows / $pagesize));
        //开始分页end
        display('/admin/role/list.html');
    }

    /**
     * 添加编辑
     */
    public function role_edit($id = '')
    {
        $id             = (int)$id;
        $item['rights'] = '';
        if (!empty($id)) {
            $item = $this->loop_model->get_id('role', $id);
        }
        assign('item', $item);

        //权限资源
        $where_data['where'] = array(
            'status' => '0',
            'type'   => 'manager',
        );
        $role_right_list     = $this->loop_model->get_list('role_right', $where_data, '', '', 'name desc');//列表
        foreach ($role_right_list as $val => $key) {
            preg_match('/\[.*?\]/', $key['name'], $right_pre);
            if (isset($right_pre[0])) {
                $arr_key               = trim($right_pre[0], '[]');
                $right_arr[$arr_key][] = $key;
            }
        }
        assign('right_arr', $right_arr);

        display('/admin/role/add.html');
    }

    /**
     * 保存数据
     */
    public function role_save()
    {
        if (is_post()) {
            $data_post   = $this->input->post(NULL, true);
            $update_data = array(
                'name' => $data_post['name'],
            );
            if (empty($update_data['name'])) {
                error_json('角色名称不能为空');
            }
            if (empty($data_post['right'])) {
                error_json('角色权限不能为空');
            }

            //组合权限
            $rights_arr = array();
            $right_list = $this->loop_model->get_list('role_right', array('where_in' => array('id'=>$data_post['right']), 'select' => '`right`'));

            foreach ($right_list as $val => $key) {
                $rights_arr[] = trim($key['right'], ',');
            }

            $update_data['rights'] = empty($rights_arr) ? '' : join(',', $rights_arr);

            if (!empty($data_post['id'])) {
                //修改数据
                $res = $this->loop_model->update_where('role', $update_data, array('id' => $data_post['id']));
                admin_log_insert('修改角色' . $data_post['id']);
            } else {
                //增加数据
                $res = $this->loop_model->insert('role', $update_data);
                admin_log_insert('增加角色' . $res);
            }
            if (!empty($res) && $res > 0) {
                error_json('y');
            } else {
                error_json('保存失败');
            }
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
        $res = $this->loop_model->update_id('role', array('status' => 1), $id);
        if (!empty($res)) {
            if (is_array($id)) $id = join(',', $id);
            admin_log_insert('删除角色到回收站' . $id);
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
        $res = $this->loop_model->update_id('role', array('status' => 0), $id);
        if (!empty($res)) {
            if (is_array($id)) $id = join(',', $id);
            admin_log_insert('还原角色' . $id);
            error_json('y');
        } else {
            error_json('还原失败');
        }
    }

    /**
     * 删除数据
     */
    public function role_delete()
    {
        $id = $this->input->post('id', true);
        if (empty($id)) error_json('id错误');
        $res = $this->loop_model->delete_id('role', $id);
        if (!empty($res)) {
            admin_log_insert('删除角色' . $id);
            error_json('y');
        } else {
            error_json('删除失败');
        }
    }

    /****************资源权限************************************/
    /**
     * 列表
     */
    public function right_list()
    {
        $pagesize = 20;//分页大小
        $page     = (int)$this->input->get('per_page');
        $page <= 1 ? $page = 1 : $page = $page;//当前页数
        //搜索条件start
        $status = $this->input->post_get('status');
        $type   = $this->input->post_get('type');
        if (empty($status)) $status = 0;
        if (isset($status)) $where_data['where']['status'] = $status;
        if (isset($type)) $where_data['where']['type'] = $type;
        $search_where = array(
            'status' => $status,
            'type'   => $type,
        );
        assign('search_where', $search_where);
        //搜索条件end
        //查到数据
        $list = $this->loop_model->get_list('role_right', $where_data, $pagesize, $pagesize * ($page - 1), 'name desc');//列表
        assign('list', $list);
        //开始分页start
        $all_rows = $this->loop_model->get_list_num('role_right', $where_data);//所有数量
        assign('page_count', ceil($all_rows / $pagesize));
        //开始分页end
        display('/admin/role/right_list.html');
    }

    /**
     * 添加
     */
    public function right_add()
    {
        $type = $this->input->get_post('type');
        assign('type', $type);

        $dir       = APPPATH . 'controllers/' . $type;
        $file_list = self::list_file($dir, $type);
        assign('file_list', $file_list);

        display('/admin/role/right_add.html');
    }

    /**
     * 编辑
     */
    public function right_edit($id)
    {
        $id = (int)$id;
        if (empty($id)) msg('id错误');
        $item          = $this->loop_model->get_id('role_right', $id);
        $item['right'] = explode(',', $item['right']);
        assign('item', $item);
        $type = $item['type'];
        assign('type', $type);
        $dir       = APPPATH . 'controllers/' . $type;
        $file_list = self::list_file($dir, $type);
        assign('file_list', $file_list);
        display('/admin/role/right_add.html');
    }

    /**
     * 保存数据
     */
    public function right_save()
    {
        if (is_post()) {
            $data_post   = $this->input->post(NULL, true);
            $update_data = array(
                'name'  => $data_post['name'],
                'right' => join(',', array_unique($data_post['right'])),
                'type'  => $data_post['type'],
            );
            if (empty($update_data['name'])) {
                error_json('权限名称不能为空');
            }
            if (empty($data_post['right'])) {
                error_json('权限码不能为空');
            }
            if (!empty($data_post['id'])) {
                //修改数据
                $res = $this->loop_model->update_id('role_right', $update_data, $data_post['id']);
                admin_log_insert('修改权限码' . $data_post['id']);
            } else {
                //增加数据
                $res = $this->loop_model->insert('role_right', $update_data);
                admin_log_insert('增加权限码' . $res);
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
     * 删除数据到回收站
     */
    public function right_delete_recycle()
    {
        $id = $this->input->post('id', true);
        if (empty($id)) error_json('id错误');
        $res = $this->loop_model->update_id('role_right', array('status' => 1), $id);
        if (!empty($res)) {
            if (is_array($id)) $id = join(',', $id);
            admin_log_insert('删除权限码到回收站' . $id);
            error_json('y');
        } else {
            error_json('删除失败');
        }
    }

    /**
     * 回收站还原
     */
    public function right_reduction_recycle()
    {
        $id = $this->input->post('id', true);
        if (empty($id)) error_json('id错误');
        $res = $this->loop_model->update_id('role_right', array('status' => 0), $id);
        if (!empty($res)) {
            if (is_array($id)) $id = join(',', $id);
            admin_log_insert('还原权限码' . $id);
            error_json('y');
        } else {
            error_json('还原失败');
        }
    }

    /**
     * 删除数据
     */
    public function right_delete()
    {
        $id = $this->input->post('id', true);
        if (empty($id)) error_json('id错误');
        $res = $this->loop_model->delete_id('role_right', $id);
        if (!empty($res)) {
            admin_log_insert('删除权限码' . $id);
            error_json('y');
        } else {
            error_json('删除失败');
        }
    }


    /**
     * 显示目录下的所有文件,包括子目录文件
     */
    public function list_file($file_path, $type)
    {
        $file_path      = $file_path . '/';
        $file_path_data = explode($type . '/', $file_path);
        static $file_list = array();
        $dir_data = opendir($file_path);

        while ($dir = readdir($dir_data)) {
            if (!in_array($dir, array('.', '..', '.svn', '.DS_Store'))) {
                if (is_dir($file_path . $dir)) {
                    self::list_file($file_path . $dir, $type);
                } else {
                    $file_list[] = $file_path_data[1] . basename($dir, '.php');
                }
            }
        }
        return $file_list;
    }

    /**
     * 显示文件下的所有action
     */
    public function list_action()
    {
        $file = $this->input->post('file_name', true);
        $type = $this->input->post('type', true);

        $file_dir   = APPPATH . 'controllers/' . $type . '/' . $file . '.php';
        $class_name = basename($file_dir, '.php');
        if ($class_name != 'Role_right') {
            include($file_dir);
        }
        $action_data = get_class_methods($class_name);
        foreach ($action_data as $key) {
            if (!in_array($key, array('__construct', 'get_instance'))) {
                $action_list[] = $key;
            }
        }
        echo ch_json_encode($action_list);
    }

}
