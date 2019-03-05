<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Menu_model extends CI_Model
{

    /**
     * 添加
     * @param array $data 添加数据
     */
    public function get_menu($type = 'service')
    {
        $menu_array = array(
            //客户管理
            'service' => array(
                '<i class="Hui-iconfont">&#xe620;</i> 客户档案' => array(
                    '客户档案' => '/admin/user/index',
                ),
                '<i class="Hui-iconfont">&#xe681;</i> 固定客户' => array(
                    '固定客户' => '/admin/user/gu_list',
                ),
                '<i class="Hui-iconfont">&#xe64b;</i> 会员客户' => array(
                    '会员客户' => '/admin/user/member_list',
                ),
            ),
            //工单管理
            'work' => array(
                '<i class="Hui-iconfont">&#xe60d;</i> 待联系订单' => array(
                    '待联系订单' => '/admin/work/unlink',
                ),
                '<i class="Hui-iconfont">&#xe66a;</i> 全部工单' => array(
                    '全部工单' => '/admin/work/all_list',
                ),
                '<i class="Hui-iconfont">&#xe66a;</i> 未派工单' => array(
                    '未派工单' => '/admin/work/unsend_list',
                ),
                '<i class="Hui-iconfont">&#xe66a;</i> 已派工单' => array(
                    '已派工单' => '/admin/work/send_list',
                ),
                '<i class="Hui-iconfont">&#xe66a;</i> 现金工单' => array(
                    '现金工单' => '/admin/work/cash_list',
                ),
                '<i class="Hui-iconfont">&#xe66a;</i> 会员工单' => array(
                    '会员工单' => '/admin/work/member_list',
                ),
                '<i class="Hui-iconfont">&#xe66a;</i> 固定工单' => array(
                    '固定工单' => '/admin/work/gu_list',
                ),
                '<i class="Hui-iconfont">&#xe643;</i> 外派工单' => array(
                    '外派工单' => '/admin/work/out_list',
                ),
            ),
            //客服管理
            'check' => array(
                '<i class="Hui-iconfont">&#xe673;</i> 待付款工单' => array(
                    '待付款工单' => '/admin/check/unpay_list',
                ),
                '<i class="Hui-iconfont">&#xe637;</i> 已付款工单' => array(
                    '已付款工单' => '/admin/check/pay_list',
                ),
                '<i class="Hui-iconfont">&#xe643;</i> 安检工单' => array(
                    '安检工单' => '/admin/check/safe_list',
                ),
                '<i class="Hui-iconfont">&#xe643;</i> 回访工单' => array(
                    '回访工单' => '/admin/check/phone_list',
                ),
            ),
            //宣传
            'area' => array(
                '<i class="Hui-iconfont">&#xe616;</i> 小区数据库' => array(
                    '小区数据库' => '/manager/area/area/area_list',
                ),
                '<i class="Hui-iconfont">&#xe635;</i> 派工' => array(
                    '派工' => '/manager/area/area/work',
                ),
                '<i class="Hui-iconfont">&#xe635;</i> 查询' => array(
                    '查询' => '/manager/area/area/search_list',
                ),
                '<i class="Hui-iconfont">&#xe635;</i> 考勤' => array(
                    '考勤' => '/manager/area/area/check_work',
                ),
            ),
            //会员卡管理
            'member' => array(
                '<i class="Hui-iconfont">&#xe6bb;</i> 会员卡查询' => array(
                    '会员卡查询' => '/manager/member/member/member_list',
                ),
                '<i class="Hui-iconfont">&#xe6ca;</i> 开卡申请' => array(
                    '开卡申请' => '/manager/member/member/open_card',
                ),
                '<i class="Hui-iconfont">&#xe6ca;</i> 充值申请' => array(
                    '充值申请' => '/manager/member/member/recharge_list',
                ),
                '<i class="Hui-iconfont">&#xe6ca;</i> 变更申请' => array(
                    '变更申请' => '/manager/member/member/change_card',
                ),
                '<i class="Hui-iconfont">&#xe6ca;</i> 挂失申请' => array(
                    '挂失申请' => '/manager/member/member/lose_card',
                ),
                '<i class="Hui-iconfont">&#xe6ca;</i> 退卡申请' => array(
                    '退卡申请' => '/manager/member/member/card_back',
                ),
            ),
            //人事
            'personnel' => array(
                '<i class="Hui-iconfont">&#xe61e;</i> 员工档案' => array(
                    '员工档案' => '/admin/personnel/position_list',
                ),
                '<i class="Hui-iconfont">&#xe61e;</i> 职工档案' => array(
                    '职工档案' => '/admin/personnel/professor_list',
                ),
                /*
                '<i class="Hui-iconfont">&#xe695;</i> 小时工资' => array(
                    '小时工资' => '/admin/personnel/hour_salary',
                ),
       */
            ),
            //财务
            'finance' => array(
                '<i class="Hui-iconfont">&#xe61e;</i> 日常开支' => array(
                    '日常开支' => '/manager/finance/finance/finance_list',
                ),
            ),
            //统计分许
            'statistics' => array(
                '<i class="Hui-iconfont">&#xe61d;</i> 现金收费统计' => array(
                    '现金收费统计' => '/manager/statistics/statistics/cash_total',
                ),
                '<i class="Hui-iconfont">&#xe669;</i> 折卡消费统计' => array(
                    '折卡消费统计' => '/manager/statistics/statistics/consume_total',
                ),
                '<i class="Hui-iconfont">&#xe628;</i> 业务来源统计' => array(
                    '业务来源统计' => '/manager/statistics/statistics/source_total',
                ),
                '<i class="Hui-iconfont">&#xe605;</i> 服务项目统计' => array(
                    '服务项目统计' => '/manager/statistics/statistics/service_total',
                ),
                '<i class="Hui-iconfont">&#xe605;</i> 业务查询' => array(
                    '业务查询' => '/manager/statistics/statistics/business_query',
                ),
                '<i class="Hui-iconfont">&#xe605;</i> 排名统计' => array(
                    '排名统计' => '/manager/statistics/statistics/rank_total',
                ),
                '<i class="Hui-iconfont">&#xe605;</i> 会员卡统计' => array(
                    '会员卡统计' => '/manager/statistics/statistics/card_total',
                ),
                '<i class="Hui-iconfont">&#xe605;</i> 即时排名' => array(
                    '即时排名' => '/manager/statistics/statistics/short_rank',
                ),
                '<i class="Hui-iconfont">&#xe605;</i> 帮扶业务统计' => array(
                    '帮扶业务统计' => '/manager/statistics/statistics/help_total',
                ),
            ),
            //服务管理
            'cat' => array(
                '<i class="Hui-iconfont">&#xe61d;</i> 服务项目列表' => array(
                    '服务项目列表' => '/admin/cat/index',
                ),
                '<i class="Hui-iconfont">&#xe61d;</i> 业务来源列表' => array(
                    '业务来源列表' => '/admin/cat/source_list',
                ),
                '<i class="Hui-iconfont">&#xe61d;</i> 广告管理' => array(
                    '广告列表' => '/admin/cat/ad_list',
                ),
                '<i class="Hui-iconfont">&#xe61d;</i> 充值管理' => array(
                    '充值列表' => '/admin/cat/recharge_list',
                ),
                '<i class="Hui-iconfont">&#xe61d;</i> 部门管理' => array(
                    '部门列表' => '/admin/cat/department_list',
                ),
                '<i class="Hui-iconfont">&#xe61d;</i> 职位管理' => array(
                    '职位列表' => '/admin/cat/job_list',
                ),
                '<i class="Hui-iconfont">&#xe61d;</i> 店铺管理' => array(
                    '店铺列表' => '/manager/cat/cat/shop_list',
                ),
                '<i class="Hui-iconfont">&#xe61d;</i> 客户资料' => array(
                    '客户资料' => '/admin/cat/demo_list',
                ),
                '<i class="Hui-iconfont">&#xe61d;</i> 最新动态' => array(
                    '最新动态' => '/admin/cat/new_list',
                ),
                '<i class="Hui-iconfont">&#xe61d;</i> 通知设置' => array(
                    '通知设置' => '/admin/cat/phone_list',
                ),
                '<i class="Hui-iconfont">&#xe61d;</i> 活动预告' => array(
                    '活动预告' => '/admin/cat/actives_list',
                ),

            ),
            //消费管理
            'consume' => array(
                '<i class="Hui-iconfont">&#xe61d;</i> 积分列表' => array(
                    '积分列表' => '/admin/consume/index',
                ),
                '<i class="Hui-iconfont">&#xe61d;</i> 账单列表' => array(
                    '账单列表' => '/admin/consume/bill_list',
                ),

            ),
            //评价管理
            'comment' => array(
                '<i class="Hui-iconfont">&#xe61d;</i> 评价列表' => array(
                    '评价列表' => '/admin/comment/index',
                ),

            ),
            //权限管理
            'role' => array(
                '<i class="Hui-iconfont">&#xe61d;</i> 管理员列表' => array(
                    '管理员列表' => '/admin/role/admin_list',
                ),
                '<i class="Hui-iconfont">&#xe61d;</i> 修改密码' => array(
                    '修改密码' => '/admin/role/update_password',
                ),
                '<i class="Hui-iconfont">&#xe61d;</i> 角色列表' => array(
                    '角色列表' => '/admin/role/role_list',
                ),
                '<i class="Hui-iconfont">&#xe61d;</i> 管理员权限资源' => array(
                    '管理员权限资源' => '/admin/role/right_list?type=admin',
                ),
            ),

        );
        return $menu_array[$type];
    }
}
