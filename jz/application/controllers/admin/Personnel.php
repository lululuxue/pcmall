<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Personnel extends CI_Controller
{

    private $admin_data;//后台用户登录信息

    public function __construct()
    {
        parent::__construct();
        $this->load->helpers('admin_helper');
        $this->admin_data = admin_login();
        assign('admin_data', $this->admin_data);
        $this->load->model('loop_model');

        $shop_list = $this->loop_model->get_list('shop',array('where'=>array('reid'=>0)));
        assign('shop_list', $shop_list);
    }

    /**
     * 员工档案
     */
    public function position_list()
    {
        $pagesize = 20;//分页大小
        $page     = (int)$this->input->get('per_page');
        $page <= 1 ? $page = 1 : $page = $page;//当前页数
        //关键字
        $shop = $this->input->post('shop');
        $sub_shop = $this->input->post('sub_shop');
        $department_id = $this->input->post('department_id');
       // $job_id = $this->input->post('job_id');
        $job = $this->input->post('job');
        $status = $this->input->post('status');
        $type = $this->input->post('type');
        $name = $this->input->post('name');

        if (!empty($shop)) $where_data['where']['a.shop'] = $shop;
        if (!empty($sub_shop)) $where_data['where']['a.sub_shop'] = $sub_shop;
        if (!empty($department_id)) $where_data['where']['a.department_id'] = $department_id;
       // if (!empty($job_id)) $where_data['where']['job_id']  = $job_id;
        if (!empty($job)) $where_data['where']['a.job']  = $job;
        if (!empty($status)) $where_data['where']['a.status']  = $status;
        if (!empty($type) &&!empty($name)) $where_data['like']['a'.$type]  = $name;
        //搜索条件end
        $search_where = array(
            'name'             => $name,
            'type'             => $type,
            'department_id'   => $department_id,
           // 'job_id'           => $job_id,
            'job'               =>$job,
            'status'           => $status,
            'sub_shop'        => $sub_shop,
            'shop'             => $shop,
        );
        assign('search_where',$search_where);
        //查到数据
        $where_data['where']['type']  = 0;
        $where_data['select']  = array('a.*,b.name as department_nam');
        $where_data['join']  = array(
            array('department b','b.id=a.department_id','left')
        );
        $list_data = $this->loop_model->get_list('position a', $where_data, $pagesize, $pagesize * ($page - 1), 'id desc');//列表
        //开始分页start
        $all_rows = $this->loop_model->get_list_num('position a', $where_data);//所有数量
        assign('page_count', ceil($all_rows / $pagesize));
        //开始分页end
        assign('list', $list_data);

        //获取职务列表
        $desc_list = $this->loop_model->get_list('job_desc',array('where'=>array('status'=>0)),'','','id desc');
        assign('desc_list',$desc_list);
        //获取职位列表
        $job_list = $this->loop_model->get_list('job',array('where'=>array('status'=>0)),'','','id desc');
        assign('job_list',$job_list);
        //获取部门列表
        $department_list = $this->loop_model->get_list('department',array('where'=>array('status'=>0)),'','','id desc');
        assign('department_list',$department_list);
        display('/personnel/position_list.html');
    }

    /**
     * 职工档案
     */
    public function professor_list()
    {
        $pagesize = 20;//分页大小
        $page     = (int)$this->input->get('per_page');
        $page <= 1 ? $page = 1 : $page = $page;//当前页数
        //关键字
        $shop = $this->input->post('shop');
        $sub_shop = $this->input->post('sub_shop');
        $department_id = $this->input->post('department_id');
        // $job_id = $this->input->post('job_id');
        $job = $this->input->post('job');
        $status = $this->input->post('status');
        $type = $this->input->post('type');
        $name = $this->input->post('name');

        if (!empty($shop)) $where_data['where']['a.shop'] = $shop;
        if (!empty($sub_shop)) $where_data['where']['a.sub_shop'] = $sub_shop;
        if (!empty($department_id)) $where_data['where']['a.department_id'] = $department_id;
        // if (!empty($job_id)) $where_data['where']['job_id']  = $job_id;
        if (!empty($job)) $where_data['where']['a.job']  = $job;
        if (!empty($status)) $where_data['where']['a.status']  = $status;
        if (!empty($type) &&!empty($name)) $where_data['like']['a'.$type]  = $name;
        //搜索条件end
        $search_where = array(
            'name'             => $name,
            'type'             => $type,
            'department_id'   => $department_id,
            // 'job_id'           => $job_id,
            'job'               =>$job,
            'status'           => $status,
            'sub_shop'        => $sub_shop,
            'shop'             => $shop,
        );
        assign('search_where',$search_where);
        //查到数据
        $where_data['where']['type']  = 1;
        $where_data['select']  = array('a.*,b.name as department_nam,c.job_name,c.base_salary');
        $where_data['join']  = array(
            array('department b','b.id=a.department_id','left'),
            array('job c','c.id=a.job_id','left')
        );
        $list_data = $this->loop_model->get_list('position a', $where_data, $pagesize, $pagesize * ($page - 1), 'id desc');//列表
        //开始分页start
        $all_rows = $this->loop_model->get_list_num('position a', $where_data);//所有数量
        assign('page_count', ceil($all_rows / $pagesize));
        //开始分页end
        assign('list', $list_data);

        //获取职务列表
        $desc_list = $this->loop_model->get_list('job_desc',array('where'=>array('status'=>0)),'','','id desc');
        assign('desc_list',$desc_list);
        //获取职位列表
        $job_list = $this->loop_model->get_list('job',array('where'=>array('status'=>0)),'','','id desc');
        assign('job_list',$job_list);
        //获取部门列表
        $department_list = $this->loop_model->get_list('department',array('where'=>array('status'=>0)),'','','id desc');
        assign('department_list',$department_list);
        display('/personnel/professor_list.html');
    }

    /**
     * 修改照片
     */
    public function picture($id)
    {
        $this->load->helpers('upload_helper');
        assign('id', $id);
        $info = $this->loop_model->get_where('position',array('id'=>$id),'photo,identify,fingerprint,healthimg');
        assign('info', $info);
        display('/personnel/picture.html');
    }
    /**
     * 修改身份证
     */
    public function identify($id)
    {
        $this->load->helpers('upload_helper');
        assign('id', $id);
        $info = $this->loop_model->get_where('position',array('id'=>$id),'photo,identify,fingerprint,healthimg');
        assign('info', $info);
        display('/personnel/identify.html');
    }
    /**
 * 修改指纹
 */
    public function fingerprint($id)
    {
        $this->load->helpers('upload_helper');
        assign('id', $id);
        $info = $this->loop_model->get_where('position',array('id'=>$id),'photo,identify,fingerprint,healthimg');
        assign('info', $info);
        display('/personnel/fingerprint.html');
    }
    /**
     * 修改健康证
     */
    public function healthimg($id)
    {
        $this->load->helpers('upload_helper');
        assign('id', $id);
        $info = $this->loop_model->get_where('position',array('id'=>$id),'photo,identify,fingerprint,healthimg');
        assign('info', $info);
        display('/personnel/healthimg.html');
    }

    /**
     * 上传图片
     */
    public function upload_picture()
    {
        $data_post = $this->input->post(NULL, true);
        $this->load->model('position_model');
        $res = $this->position_model->update_img($data_post);
        error_json($res);
    }

    /**
     * 增加员工信息
     */
    public function add_personnel()
    {
        $data['year'] = date('Y');
        $data['month'] = date('m');
        assign('time',$data);
        //获取部门列表
        $department_list = $this->loop_model->get_list('department',array('where'=>array('status'=>0)),'','','id desc');
        assign('department_list',$department_list);
        //获取职位列表
        $desc_list = $this->loop_model->get_list('job_desc',array('where'=>array('status'=>0)),'','','id desc');
        assign('desc_list',$desc_list);
        display('/personnel/add_people.html');
    }

    /**
     * 修改员工信息
     */
    public function edit_personnel($id)
    {
        $data['year'] = date('Y');
        $data['month'] = date('m');
        assign('time',$data);
        $info = $this->loop_model->get_where('position',array('id'=>$id));
        assign('info',$info);
        //获取部门列表
        $department_list = $this->loop_model->get_list('department',array('where'=>array('status'=>0)),'','','id desc');
        assign('department_list',$department_list);
        //获取职位列表
        $desc_list = $this->loop_model->get_list('job_desc',array('where'=>array('status'=>0)),'','','id desc');
        assign('desc_list',$desc_list);
        display('/personnel/edit_people.html');
    }


    /**
     * 提交信息
     */
    public function add_people()
    {
        $data_post = $this->input->post(NULL, true);
        $this->load->model('position_model');
        $res = $this->position_model->update($data_post);
        error_json($res);
    }

    /**
     * 删除员工
     */
    public function del_personnel()
    {
        $id = (int)$this->input->post('id', true);
        if (empty($id)) error_json('id不能为空');
        $res = $this->loop_model->update_where('position',array('is_del'=>1,'status'=>2),array('id'=>$id));
        if($res >=0){
            error_json('y');
        }else{
            error_json('删除失败');
        }
    }
    /**
     * 还原员工
     */
    public function back_personnel()
    {
        $id = (int)$this->input->post('id', true);
        if (empty($id)) error_json('id不能为空');
        $res = $this->loop_model->update_where('position',array('is_del'=>0,'status'=>1),array('id'=>$id));
        if($res >=0){
            error_json('y');
        }else{
            error_json('还原失败');
        }
    }

    /**
     * 小时工资
     */
    public function hour_salary()
    {
        $pagesize = 20;//分页大小
        $page     = (int)$this->input->get('per_page');
        $page <= 1 ? $page = 1 : $page = $page;//当前页数
        //关键字
        $shop = $this->input->post('shop');
        $sub_shop = $this->input->post('sub_shop');
        $department_id = $this->input->post('department_id');
       // $job_id = $this->input->post('job_id');
        $job = $this->input->post('job');
        $status = $this->input->post('status');
        $type = $this->input->post('type');
        $name = $this->input->post('name');
        $date = $this->input->post('date');

        if (!empty($shop)) $where_data['where']['a.shop'] = $shop;
        if (!empty($sub_shop)) $where_data['where']['a.sub_shop'] = $sub_shop;
        if (!empty($department_id)) $where_data['where']['a.department_id'] = $department_id;
       // if (!empty($job_id)) $where_data['where']['job_id']  = $job_id;
        if (!empty($job)) $where_data['where']['a.job']  = $job;
        if (!empty($status)) $where_data['where']['a.status']  = $status;
        if (!empty($date)) {
            $where_data['where']['a.month <=']  = $date;
        }
        if (!empty($type) &&!empty($name)){
            if($type == 'hour_money'){
                $where_data['where'][$type]  = $name;
            }else{
                $where_data['like'][$type]  = $name;
            }
        }
        //搜索条件end
        $search_where = array(
            'name'             => $name,
            'type'             => $type,
            'department_id'   => $department_id,
           // 'job_id'           => $job_id,
            'job'               =>$job,
            'status'           => $status,
            'sub_shop'        => $sub_shop,
            'shop'             => $shop,
        );
        assign('search_where', $search_where);
        //查到数据
        $where_data['where']['a.is_del']  = 0;//未删除的数据
        $where_data['select'] = array('a.*,b.name as department_name');
        $where_data['join'] = array(
            array('department b','b.id=a.department_id','left')
        );
        $list_data = $this->loop_model->get_list('position a', $where_data, $pagesize, $pagesize * ($page - 1), 'a.id desc');//列表
        //开始分页start
        $all_rows = $this->loop_model->get_list_num('position a', $where_data);//所有数量
        assign('page_count', ceil($all_rows / $pagesize));
        //开始分页end
        //var_dump($list_data);
        assign('list', $list_data);

        //获取职位务列表
        $desc_list = $this->loop_model->get_list('job_desc',array('where'=>array('status'=>0)),'','','id desc');
        assign('desc_list',$desc_list);

        //获取职位列表
        $job_list = $this->loop_model->get_list('job',array('where'=>array('status'=>0)),'','','id desc');
        assign('job_list',$job_list);
        //获取部门列表
        $department_list = $this->loop_model->get_list('department',array('where'=>array('status'=>0)),'','','id desc');
        assign('department_list',$department_list);
        display('/personnel/hour_salary.html');
    }

    /**
     * 历史小时工资记录
     */
    public function history_salary($id)
    {
        $pagesize = 20;//分页大小
        $page     = (int)$this->input->get('per_page');
        $page <= 1 ? $page = 1 : $page = $page;//当前页数
        //搜索条件start

        //关键字
        $name = $this->input->post_get('name');
        if (!empty($name)) $where_data['like']['a.name'] = $name;
        $where_data['where']['a.position_id'] = $id;
        $where_data['select'] = array('a.*,b.name as department_name');
        $where_data['join'] = array(
            array('department b','a.department_id=b.id','left')
        );

        //搜索条件end
        $search_where = array(
            'name'             => $name,
        );
        assign('search_where',$search_where);
        //查到数据
        $list_data = $this->loop_model->get_list('position_history a', $where_data, $pagesize, $pagesize * ($page - 1), 'a.id desc');//列表
        //开始分页start
        $all_rows = $this->loop_model->get_list_num('position_history a', $where_data);//所有数量
        assign('page_count', ceil($all_rows / $pagesize));
        //开始分页end
        assign('list', $list_data);
        display('/personnel/history_salary.html');

    }

    /**
     * 增加员工信息
     */
    public function add_professor()
    {
        $data['year'] = date('Y');
        $data['month'] = date('m');
        assign('time',$data);
        //获取部门列表
        $department_list = $this->loop_model->get_list('department',array('where'=>array('status'=>0)),'','','id desc');
        assign('department_list',$department_list);
        //获取职位列表
        $desc_list = $this->loop_model->get_list('job',array('where'=>array('status'=>0)),'','','id desc');
        assign('job_list',$desc_list);
        display('/personnel/add_professor.html');
    }

    /**
     * 修改职工信息
     */
    public function edit_professor($id)
    {
        $data['year'] = date('Y');
        $data['month'] = date('m');
        assign('time',$data);
        $info = $this->loop_model->get_where('position',array('id'=>$id));
        assign('info',$info);
        //获取部门列表
        $department_list = $this->loop_model->get_list('department',array('where'=>array('status'=>0)),'','','id desc');
        assign('department_list',$department_list);
        //获取职位列表
        $desc_list = $this->loop_model->get_list('job',array('where'=>array('status'=>0)),'','','id desc');
        assign('job_list',$desc_list);
        display('/personnel/edit_professor.html');
    }

    /**
     * 历史每月工资记录
     */
    public function history_month_salary($id)
    {
        $pagesize = 20;//分页大小
        $page     = (int)$this->input->get('per_page');
        $page <= 1 ? $page = 1 : $page = $page;//当前页数
        //搜索条件start

        //关键字
        $name = $this->input->post_get('name');
        if (!empty($name)) $where_data['like']['a.name'] = $name;
        $where_data['where']['a.position_id'] = $id;
        $where_data['select'] = array('a.*,b.name as department_name,c.job_name,c.base_salary');
        $where_data['join'] = array(
            array('department b','a.department_id=b.id','left'),
            array('job c','a.job_id=c.id','left')
        );

        //搜索条件end
        $search_where = array(
            'name'             => $name,
        );
        assign('search_where',$search_where);
        //查到数据
        $list_data = $this->loop_model->get_list('position_history a', $where_data, $pagesize, $pagesize * ($page - 1), 'a.id desc');//列表
        //开始分页start
        $all_rows = $this->loop_model->get_list_num('position_history a', $where_data);//所有数量
        assign('page_count', ceil($all_rows / $pagesize));
        //开始分页end
        assign('list', $list_data);
        display('/personnel/history_month_salary.html');

    }


}
