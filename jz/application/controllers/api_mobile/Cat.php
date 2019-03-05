<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Cat extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->model('loop_model');
        $this->load->helpers('wechat_helper');
    }

    /**
     * 分类列表
     */
    public function cat()
    {
        header('Access-Control-Allow-Origin: *');
        // 响应类型
        header('Access-Control-Allow-Methods:GET, POST, PUT,DELETE');
        // 响应头设置
        header('Access-Control-Allow-Headers:x-requested-with,content-type');

        $reid  = $this->input->get('reid', true);//第一级分类为0，子分类大于0
        $level = $this->input->get('level', true);//大于0的时候需要展示下级
        $where_data['where_not_in']['sortnum']  = array(1); //去掉充值
        if ($reid == 'flag') {
            $where_data['where'] = array('flag' => 1,'is_del'=>0);
        } else {
            $reid = (int)$reid;
            if (empty($reid)) $reid = 0;
            $where_data['where'] = array('reid' => $reid,'is_del'=>0);
        }
        $where_data['select'] = 'id,name,image,show_image,reid,sub_desc';
        $list_data            = $this->loop_model->get_list('service', $where_data, '', '', 'sortnum asc,id asc');
        if (!empty($list_data)) {
            foreach ($list_data as $key) {
                $key['down'] = '';
                //下级栏目(因为$reid可能为0,0 == 'flag'为真)
                if (!empty($level) && $reid !== 'flag') {
                    $where_down_data['where'] = array('reid' => $key['id'],'is_del'=>0);
                    $where_down_data['select'] = 'id,name,image,sub_desc,show_image,reid';
                    $key['down'] = $this->loop_model->get_list('service', $where_down_data, '', '', 'sortnum asc,id asc');
                }
                $list[] = $key;
            }
            error_json($list);
        } else {
            error_json('没有数据');
        }
    }

    /**
     * 根据id获取项目详情
     */
    public function info()
    {
        header('Access-Control-Allow-Origin: *');
        // 响应类型
        header('Access-Control-Allow-Methods:GET, POST, PUT,DELETE');
        // 响应头设置
        header('Access-Control-Allow-Headers:x-requested-with,content-type');
        $id = (int)$this->input->get('id', true);
        if (!empty($id)) {
            $category = $this->loop_model->get_id('service', $id);
            if (!empty($category)) {
                error_json($category);
            } else {
                error_json('没有数据');
            }
        } else {
            error_json('缺少参数');
        }
    }

    /**
     * 获取案例
     */
    public function demo_list()
    {
        header('Access-Control-Allow-Origin: *');
        // 响应类型
        header('Access-Control-Allow-Methods:GET, POST, PUT,DELETE');
        // 响应头设置
        header('Access-Control-Allow-Headers:x-requested-with,content-type');

        $category = $this->loop_model->get_list('demo');
        if (!empty($category)) {
            error_json($category);
        } else {
            error_json('没有数据');
        }
    }

    /**
     * 获取案例
     */
    public function team_list()
    {
        header('Access-Control-Allow-Origin: *');
        // 响应类型
        header('Access-Control-Allow-Methods:GET, POST, PUT,DELETE');
        // 响应头设置
        header('Access-Control-Allow-Headers:x-requested-with,content-type');
        $where['where']['type'] = 0;
        $team_list = $this->loop_model->get_list('position',$where);
        if (!empty($team_list)) {
            error_json($team_list);
        } else {
            error_json('没有数据');
        }
    }

    function getuserinfo()
    {
        header('Access-Control-Allow-Origin: *');
        // 响应类型
        header('Access-Control-Allow-Methods:GET, POST, PUT,DELETE');
        // 响应头设置
        header('Access-Control-Allow-Headers:x-requested-with,content-type');
        $appid  = config_item('wx_appid');//appid
        $secret = config_item('wx_secret');//secret

        $CI = &get_instance();
       // $user_agent = $CI->input->user_agent();//print_r($user_agent);exit;
       // if(strpos($user_agent, 'MicroMessenger')!== false) {
//            $user_data = json_decode(decrypt($CI->input->cookie('wx_userinfo')), true);

            if(empty($user_data)){
                $code = $CI->input->get('code',true);
                if($code == '')
                {
                    $redirect_uri = urlencode(get_now_url());//授权后的跳转连接
                    $url = 'https://open.weixin.qq.com/connect/oauth2/authorize?appid='.$appid.'&redirect_uri='.$redirect_uri.'&response_type=code&scope=snsapi_userinfo&state=STATE#wechat_redirect';
                    header("location:$url");exit;
                }else{
                    $url = 'https://api.weixin.qq.com/sns/oauth2/access_token?appid='.$appid.'&secret='.$secret.'&code='.$code.'&grant_type=authorization_code';
                    $get_token = curl_get($url);
                    $token = json_decode($get_token,true);//通过code换取网页授权access_token
                    curl_get('https://api.weixin.qq.com/sns/oauth2/refresh_token?appid='.$appid.'&grant_type=refresh_token&refresh_token='.$token['refresh_token']);//刷新access_token
                    $get_user = curl_get('https://api.weixin.qq.com/sns/userinfo?access_token='.$token['access_token'].'&openid='.$token['openid'].'&lang=zh_CN');
                    $user_data = json_decode($get_user,true);
                    if($user_data['openid']==''){
                        msg('用户信息获取失败', 'stop');
                    }
                }
            }
            //查询用户信息,没有用户信息就注册新用户
           // $weixin_member = reg_weixin_member($user_data);
            $member_oauth = $this->loop_model->get_where('auth', array('openid'=>$user_data['openid']));
            if (empty($member_oauth)) {
               // $flag_user = decrypt($CI->input->cookie('flag_user'));//推荐人id
                //不存在用户开始注册
                $member_data = array(
                    // 'username'   => 'wx_'.date('mdHis', time()).get_rand_num('str', 5),
                    // 'password'   => get_rand_num('str', 15),
                    'headimgurl' => $user_data['headimgurl'],
                    'openid' => $user_data['openid'],
                    'add_time' => time(),
                    //'flag_user'  => $flag_user,
                    'nickname' => $user_data['nickname'],
                    // 'sex'        => $user_data['sex'],
                );
//                $CI->load->model('member/user_model');
                //$res = $CI->user_model->update($member_data);
                $oauth_id = $this->loop_model->insert('auth', $member_data);
                $id = $oauth_id;
            }else{
                $member_data = array(
                    // 'username'   => 'wx_'.date('mdHis', time()).get_rand_num('str', 5),
                    // 'password'   => get_rand_num('str', 15),
                    'headimgurl' => $user_data['headimgurl'],
                   // 'openid' => $user_data['openid'],
                   // 'add_time' => time(),
                    //'flag_user'  => $flag_user,
                    'nickname' => $user_data['nickname'],
                    // 'sex'        => $user_data['sex'],
                );
//                $CI->load->model('member/user_model');
                $oauth_id = $this->loop_model->update_where('auth', $member_data,['openid'=>$user_data['openid']]);
                $id = $member_oauth['id'];
            }
//                error_json($member_data);//启用微信登录
        header('location:http://dev.mjkx.yaokexing.com/#/talk?openid='.$id);
            //return $user_data;//不启用微信登录
//        } else {
//            return false;
//        }
    }

    /**
     * 评论的图片上传
     */
    function upload(){
        header('Access-Control-Allow-Origin: *');
        // 响应类型
        header('Access-Control-Allow-Methods:GET, POST, PUT,DELETE');
        // 响应头设置
        header('Access-Control-Allow-Headers:x-requested-with,content-type');
        $this->load->model('upload_model');
        $data = $this->upload_model->upload('file');
        error_json($data);
    }

    /**
     *发布评论
     */
    function send_message(){
        header('Access-Control-Allow-Origin: *');
        // 响应类型
        header('Access-Control-Allow-Methods:GET, POST, PUT,DELETE');
        // 响应头设置
        header('Access-Control-Allow-Headers:x-requested-with,content-type');
        $note = $this->input->get('note', true);
        $people_id = $this->input->get('auth_id', true);
        $images = '';
        if($this->input->get('file1', true)){
            $images .= ','.$this->input->get('file1', true);
        }
       if($this->input->get('file2', true)){
            $images .= ','.$this->input->get('file2', true);
        }
        if($this->input->get('file3', true)){
            $images .= ','.$this->input->get('file3', true);
        }
        if($this->input->get('file4', true)){
            $images .= ','.$this->input->get('file4', true);
        }
        if($this->input->get('file5', true)){
            $images .= ','.$this->input->get('file5', true);
        }
        if($this->input->get('file6', true)){
            $images .= ','.$this->input->get('file6', true);
        }
        if($this->input->get('file7', true)){
            $images .= ','.$this->input->get('file7', true);
        }
        if($this->input->get('file8', true)){
            $images .= ','.$this->input->get('file8', true);
        }
        if($this->input->get('file9', true)){
            $images .= ','.$this->input->get('file9', true);
        }

        $insert['comment'] = $note;
        $insert['images'] = $images ? substr($images,1) : '';
        $insert['add_time'] = time();
        $insert['people_id'] = $people_id;
        $id = $this->loop_model->insert('actives', $insert);
        if($id > 0){
            error_json('y');
        }else{
            error_json('保存错误');
        }
    }

    /**
     * 获取动态
     */
    public function active_list(){
        header('Access-Control-Allow-Origin: *');
        // 响应类型
        header('Access-Control-Allow-Methods:GET, POST, PUT,DELETE');
        // 响应头设置
        header('Access-Control-Allow-Headers:x-requested-with,content-type');
        $where_data['select']  = array('a.*,b.nickname,b.headimgurl');
        $where_data['join']  = array(
            array('auth b','b.id=a.people_id','left')

        );
       // var_dump($where_data);exit;
        $list = $this->loop_model->get_list('actives a',$where_data);
        foreach($list as $k=>$v){
            $list[$k]['image_list'] = $v['images'] ? (explode(',',$v['images'])) : [];
            $list[$k]['time'] = date('Y-m-d H:i:s',$v['add_time']);
            //获取喜爱数
            $list[$k]['loves'] = $this->loop_model->get_list_num('loves',array('where'=>array('active_id'=>$v['id'])));
            //获取点赞数
            $list[$k]['goods'] = $this->loop_model->get_list_num('goods',array('where'=>array('active_id'=>$v['id'])));
            //获取评论数
            $list[$k]['talks'] = $this->loop_model->get_list_num('talk',array('where'=>array('active_id'=>$v['id'])));
            //获取评论
            $where_data['where'] = ['a.active_id'=>$v['id'],'a.type'=>1];
            $where_data['select'] = 'a.*,FROM_UNIXTIME(a.add_time,"%Y-%m-%d %H:%i:%s") as add_time,b.nickname,b.headimgurl';
            $where_data['join'] = [
                ['auth b','a.comment_people_id=b.id','left']
            ];
            $list[$k]['talk_list'] = $this->loop_model->get_list('talk a',$where_data,'','','a.add_time desc');
            //error_json($list);
           // $data1 = $this->get_list($list,$v['id'],$k);
            //error_json($data1);

            foreach($list[$k]['talk_list']  as $key=>$val){
                $where['where'] = array('a.active_id'=>$v['id'],'a.type'=>2,'a.parent_id'=>$val['id']);
                $where['select'] = array('a.*,FROM_UNIXTIME( a.add_time,"%Y-%m-%d %H:%i:%s") as add_time,b.nickname,b.headimgurl,d.nickname as nickname1,d.headimgurl as headimgurl1');
                $where['join'] = array(
                    array('auth b','a.reback_people_id=b.id','left'),
                    array('talk c','a.talk_id=c.id','left'),
                    array('auth d','c.reback_people_id=d.id or c.comment_people_id=d.id','left')
                );
                $list[$k]['talk_list'][$key]['list'] = $this->loop_model->get_list('talk a',$where,'','','a.add_time asc');
            }
           
            if($openid = $this->input->get('openid', true)){
                $loves = $this->loop_model->get_where('loves',array('active_id'=>$v['id'],'people_id'=>$openid));
                $list[$k]['is_love'] = $loves ? 1 : 0 ;
                $goods = $this->loop_model->get_where('goods',array('active_id'=>$v['id'],'people_id'=>$openid));
                $list[$k]['is_good'] = $goods ? 1 : 0 ;
                $talks = $this->loop_model->get_where('talk',array('active_id'=>$v['id'],'comment_people_id'=>$openid));
                $list[$k]['is_talk'] = $talks ? 1 : 0 ;
            }else{
                $list[$k]['is_love'] =  0 ;
                $list[$k]['is_good'] =  0 ;
            }
        }
        error_json($list);
    }

    /**
     * 获取我的动态
     */
    public function my_active_list(){
        header('Access-Control-Allow-Origin: *');
        // 响应类型
        header('Access-Control-Allow-Methods:GET, POST, PUT,DELETE');
        // 响应头设置
        header('Access-Control-Allow-Headers:x-requested-with,content-type');
        $openid = $this->input->get('openid', true);
        $where_data['select']  = array('a.*,b.nickname,b.headimgurl');
        $where_data['where']['people_id']  = $openid;
        $where_data['join']  = array(
            array('auth b','b.id=a.people_id','left')

        );
        // var_dump($where_data);exit;
        $list = $this->loop_model->get_list('actives a',$where_data);
        foreach($list as $k=>$v){
            $list[$k]['image_list'] = $v['images'] ? (explode(',',$v['images'])) : [];
            $list[$k]['time'] = date('Y-m-d H:i:s',$v['add_time']);
            //获取喜爱数
            $list[$k]['loves'] = $this->loop_model->get_list_num('loves',array('where'=>array('active_id'=>$v['id'])));
            //获取点赞数
            $list[$k]['goods'] = $this->loop_model->get_list_num('goods',array('where'=>array('active_id'=>$v['id'])));
            //获取评论数
            $list[$k]['talks'] = $this->loop_model->get_list_num('talk',array('where'=>array('active_id'=>$v['id'])));
            //获取评论
            $where_data['where'] = ['a.active_id'=>$v['id'],'a.type'=>1];
            $where_data['select'] = 'a.*,FROM_UNIXTIME(a.add_time,"%Y-%m-%d %H:%i:%s") as add_time,b.nickname,b.headimgurl';
            $where_data['join'] = [
                ['auth b','a.comment_people_id=b.id','left']
            ];
            $list[$k]['talk_list'] = $this->loop_model->get_list('talk a',$where_data,'','','a.add_time desc');
            //error_json($list);
            // $data1 = $this->get_list($list,$v['id'],$k);
            //error_json($data1);

            foreach($list[$k]['talk_list']  as $key=>$val){
                $where['where'] = array('a.active_id'=>$v['id'],'a.type'=>2,'a.parent_id'=>$val['id']);
                $where['select'] = array('a.*,FROM_UNIXTIME( a.add_time,"%Y-%m-%d %H:%i:%s") as add_time,b.nickname,b.headimgurl,d.nickname as nickname1,d.headimgurl as headimgurl1');
                $where['join'] = array(
                    array('auth b','a.reback_people_id=b.id','left'),
                    array('talk c','a.talk_id=c.id','left'),
                    array('auth d','c.reback_people_id=d.id or c.comment_people_id=d.id','left')
                );
                $list[$k]['talk_list'][$key]['list'] = $this->loop_model->get_list('talk a',$where,'','','a.add_time asc');
            }

            if($openid = $this->input->get('openid', true)){
                $loves = $this->loop_model->get_where('loves',array('active_id'=>$v['id'],'people_id'=>$openid));
                $list[$k]['is_love'] = $loves ? 1 : 0 ;
                $goods = $this->loop_model->get_where('goods',array('active_id'=>$v['id'],'people_id'=>$openid));
                $list[$k]['is_good'] = $goods ? 1 : 0 ;
                $talks = $this->loop_model->get_where('talk',array('active_id'=>$v['id'],'comment_people_id'=>$openid));
                $list[$k]['is_talk'] = $talks ? 1 : 0 ;
            }else{
                $list[$k]['is_love'] =  0 ;
                $list[$k]['is_good'] =  0 ;
            }
        }
        error_json($list);
    }

    public function get_list($list,$active_id,$k){
        foreach($list[$k]['talk_list']  as $key=>$val){
            $where['where'] = array('a.active_id'=>$active_id,'a.type'=>2,'a.parent_id'=>$val['id']);
            $where['select'] = array('a.*,FROM_UNIXTIME( a.add_time,"%Y-%m-%d %H:%i:%s") as add_time,b.nickname,b.headimgurl,d.nickname as nickname1,d.headimgurl as headimgurl1');
            $where['join'] = array(
                array('auth b','a.reback_people_id=b.id','left'),
                array('talk c','a.talk_id=c.id','left'),
                array('auth d','c.reback_people_id=d.id or c.comment_people_id=d.id','left')
            );
            $list[$k]['talk_list'][$key]['list'] = $this->loop_model->get_list('talk a',$where,'','','a.add_time asc');
        }
        return $list;
    }

    /**
     * 点赞
     */
    public function add_good(){
        header('Access-Control-Allow-Origin: *');
        // 响应类型
        header('Access-Control-Allow-Methods:GET, POST, PUT,DELETE');
        // 响应头设置
        header('Access-Control-Allow-Headers:x-requested-with,content-type');
        $active_id = $this->input->get('active_id', true);
        $openid = $this->input->get('openid',true);
        $where = ['active_id'=>$active_id,'people_id'=>$openid];
        $goodData = $this->loop_model->get_where('goods',$where);
        if($goodData){
            //取消喜愛
            $id = $this->loop_model->delete_id('goods', $goodData['id']);
        }else{
            //加入喜愛
            $insert = [
                'active_id' => $active_id,
                'people_id' => $openid,
                'type'=> 1,
                'add_time'=>time()
            ];
            $id = $this->loop_model->insert('goods', $insert);
        }
        if($id > 0){
            error_json('y');
        }else{
            error_json('操作失败');
        }

    }
    /**
     * 喜爱
     */
    public function add_love(){
        header('Access-Control-Allow-Origin: *');
        // 响应类型
        header('Access-Control-Allow-Methods:GET, POST, PUT,DELETE');
        // 响应头设置
        header('Access-Control-Allow-Headers:x-requested-with,content-type');
        $active_id = $this->input->get('active_id', true);
        $openid = $this->input->get('openid',true);
        $where = ['active_id'=>$active_id,'people_id'=>$openid];
        $goodData = $this->loop_model->get_where('loves',$where);
        if($goodData){
            //取消喜愛
            $id = $this->loop_model->delete_id('loves', $goodData['id']);
        }else{
            //加入喜愛
            $insert = [
                'active_id' => $active_id,
                'people_id' => $openid,
                'type'=> 1,
                'add_time'=>time()
            ];
            $id = $this->loop_model->insert('loves', $insert);
        }
        if($id > 0){
            error_json('y');
        }else{
            error_json('操作失败');
        }

    }

    /**
     * 添加评论
     */
    public function add_comment(){
        header('Access-Control-Allow-Origin: *');
        // 响应类型
        header('Access-Control-Allow-Methods:GET, POST, PUT,DELETE');
        // 响应头设置
        header('Access-Control-Allow-Headers:x-requested-with,content-type');
        $active_id = $this->input->get('active_id', true);
        $comment_people_id = $this->input->get('id',true);
        $comment = $this->input->get('comment',true);
        $type = $this->input->get('type',true);
        $reback_people_id = $this->input->get('reback_id',true);
        $talk_id = $this->input->get('talk_id',true);
        $reback_comment = $this->input->get('reback_comment',true);
        $parent_id = $this->input->get('parent_id',true);
        //判断是评论还是回复
        if($type == 1){
            $insert = [
                'active_id'=>$active_id,
                'comment'=>$comment,
                'comment_people_id'=>$comment_people_id,
                'add_time'=>time(),
                'type'=>$type
            ];
        }else{
            $insert = [
                'active_id'=>$active_id,
                'talk_id'=>$talk_id,
                'reback_comment'=>$reback_comment,
                'reback_people_id'=>$reback_people_id,
                'add_time'=>time(),
                'parent_id'=>$parent_id,
                'type'=>$type
            ];
        }

        $id = $this->loop_model->insert('talk', $insert);

        if($id > 0){
            error_json('y');
        }else{
            error_json('操作失败');
        }

    }

    /**
     * 获取动态列表
     */
    public function new_list()
    {
        header('Access-Control-Allow-Origin: *');
        // 响应类型
        header('Access-Control-Allow-Methods:GET, POST, PUT,DELETE');
        // 响应头设置
        header('Access-Control-Allow-Headers:x-requested-with,content-type');
        $category = $this->loop_model->get_list('news');
        if (!empty($category)) {
            error_json($category);
        } else {
            error_json('没有数据');
        }
    }

    /**
     * 获取动态详情
     */
    public function new_detail()
    {
        header('Access-Control-Allow-Origin: *');
        // 响应类型
        header('Access-Control-Allow-Methods:GET, POST, PUT,DELETE');
        // 响应头设置
        header('Access-Control-Allow-Headers:x-requested-with,content-type');
        $id = $this->input->get('id', true);
        $category = $this->loop_model->get_id('news',$id);
        if (!empty($category)) {
            error_json($category);
        } else {
            error_json('没有数据');
        }

    }

    /**
     * 获取活动预告图
     */
    public function actives_images()
    {
        header('Access-Control-Allow-Origin: *');
        // 响应类型
        header('Access-Control-Allow-Methods:GET, POST, PUT,DELETE');
        // 响应头设置
        header('Access-Control-Allow-Headers:x-requested-with,content-type');
        $images = $this->loop_model->get_where('pre_actives',array('is_delete'=>0));

        error_json($images);


    }
    

}
