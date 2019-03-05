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
            //客服
            'service' => array(
                '<i class="Hui-iconfont">&#xe620;</i> 录入工单' => array(
                    '录入工单' => '/manager/service/user/add',
                ),
                '<i class="Hui-iconfont">&#xe681;</i> 客户档案' => array(
                    '客户档案' => '/manager/service/user/index',
                ),
                '<i class="Hui-iconfont">&#xe64b;</i> 查待联系' => array(
                    '查待联系' => '/manager/service/user/unlink',
                ),
                '<i class="Hui-iconfont">&#xe6ad;</i> 工单记录' => array(
                    '工单记录' => '/manager/service/user/order_log'
                ),
                '<i class="Hui-iconfont">&#xe6ad;</i> 固定人员' => array(
                    '固定人员' => '/manager/service/user/gu_list'
                ),
                '<i class="Hui-iconfont">&#xe6ad;</i> 员工信息' => array(
                    '员工信息' => '/manager/service/user/position_list'
                ),
                '<i class="Hui-iconfont">&#xe6ad;</i> 客户固定查询' => array(
                    '客户固定查询' => '/manager/service/user/order_gu_list'
                ),
                '<i class="Hui-iconfont">&#xe6ad;</i> 会员卡消费记录' => array(
                    '会员卡消费记录' => '/manager/service/user/user_card_consume'
                ),
                '<i class="Hui-iconfont">&#xe6ad;</i> 积分商品' => array(
                    '积分商品' => '/manager/service/user/point_goods'
                ),
                '<i class="Hui-iconfont">&#xe6ad;</i> 电话回访' => array(
                    '电话回访' => '/manager/service/user/call_back_list'
                ),
                '<i class="Hui-iconfont">&#xe6ad;</i> 投诉处理' => array(
                    '投诉处理' => '/manager/service/user/complain_list'
                ),
            ),
            //派工
            'work' => array(
                '<i class="Hui-iconfont">&#xe60d;</i> 微信订单' => array(
                    '微信订单' => '/manager/work/work/wx_list',
                ),
                '<i class="Hui-iconfont">&#xe66a;</i> 全部工单' => array(
                    '全部工单' => '/manager/work/work/all_list',
                ),
                '<i class="Hui-iconfont">&#xe66a;</i> 现金工单' => array(
                    '现金工单' => '/manager/work/work/cash_list',
                ),
                '<i class="Hui-iconfont">&#xe66a;</i> 会员工单' => array(
                    '会员工单' => '/manager/work/work/member_list',
                ),
                '<i class="Hui-iconfont">&#xe66a;</i> 固定工单' => array(
                    '固定工单' => '/manager/work/work/gu_list',
                ),
                '<i class="Hui-iconfont">&#xe66a;</i> 微信工单' => array(
                    '微信工单' => '/manager/work/work/wx1_list',
                ),
                '<i class="Hui-iconfont">&#xe66a;</i> 派工' => array(
                    '派工' => '/manager/work/work/work_send',
                ),
                '<i class="Hui-iconfont">&#xe66a;</i> 已派工单' => array(
                    '已派工单' => '/manager/work/work/send_list',
                ),
                '<i class="Hui-iconfont">&#xe66a;</i> 安检工单' => array(
                    '安检工单' => '/manager/work/work/safe_list',
                ),
                '<i class="Hui-iconfont">&#xe66a;</i> 收费' => array(
                    '收费' => '/manager/work/work/fee_list',
                ),
            ),
            //考勤
            'check' => array(
                '<i class="Hui-iconfont">&#xe673;</i> 全部工单' => array(
                    '全部工单' => '/manager/check/check/all_list',
                ),
                '<i class="Hui-iconfont">&#xe637;</i> 现金工单' => array(
                    '现金工单' => '/manager/check/check/cash_list',
                ),
                '<i class="Hui-iconfont">&#xe643;</i> 会员工单' => array(
                    '会员工单' => '/manager/check/check/member_list',
                ),
                '<i class="Hui-iconfont">&#xe643;</i> 固定工单' => array(
                    '固定工单' => '/manager/check/check/gu_list',
                ),
                '<i class="Hui-iconfont">&#xe643;</i> 微信工单' => array(
                    '微信工单' => '/manager/check/check/wx_list',
                ),
                '<i class="Hui-iconfont">&#xe643;</i> 工单考勤' => array(
                    '工单考勤' => '/manager/check/check/check',
                ),
                '<i class="Hui-iconfont">&#xe643;</i> 工时记录' => array(
                    '工时记录' => '/manager/check/check/work_time',
                ),
                '<i class="Hui-iconfont">&#xe643;</i> 日常考勤' => array(
                    '日常考勤' => '/manager/check/check/day_work',
                ),
                '<i class="Hui-iconfont">&#xe643;</i> 工资' => array(
                    '工资' => '/manager/check/check/salary',
                ),
                '<i class="Hui-iconfont">&#xe643;</i> 工资单生成' => array(
                    '工资单生成' => '/manager/check/check/salary_produce',
                ),
                '<i class="Hui-iconfont">&#xe643;</i> 工资审核' => array(
                    '工资审核' => '/manager/check/check/salary_check',
                ),
                '<i class="Hui-iconfont">&#xe643;</i> 工资审核记录' => array(
                    '工资审核记录' => '/manager/check/check/salary_check_log',
                ),
                '<i class="Hui-iconfont">&#xe643;</i> 账号打印' => array(
                    '账号打印' => '/manager/check/check/account',
                ),
                '<i class="Hui-iconfont">&#xe643;</i> 工资历史记录' => array(
                    '工资历史记录' => '/manager/check/check/salary_user_list',
                ),
                '<i class="Hui-iconfont">&#xe643;</i> 外派工单' => array(
                    '外派工单' => '/manager/check/check/send_list',
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
                    '员工档案' => '/manager/personnel/personnel/professor_list',
                ),
                '<i class="Hui-iconfont">&#xe695;</i> 小时工资' => array(
                    '小时工资' => '/manager/personnel/personnel/hour_salary',
                ),
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
                    '服务项目列表' => '/manager/cat/cat/index',
                ),
                '<i class="Hui-iconfont">&#xe61d;</i> 业务来源列表' => array(
                    '业务来源列表' => '/manager/cat/cat/source_list',
                ),
                '<i class="Hui-iconfont">&#xe61d;</i> 广告管理' => array(
                    '广告列表' => '/manager/cat/cat/ad_list',
                ),
                '<i class="Hui-iconfont">&#xe61d;</i> 充值管理' => array(
                    '充值列表' => '/manager/cat/cat/recharge_list',
                ),
                '<i class="Hui-iconfont">&#xe61d;</i> 部门管理' => array(
                    '部门列表' => '/manager/cat/cat/department_list',
                ),
                '<i class="Hui-iconfont">&#xe61d;</i> 职位管理' => array(
                    '职位列表' => '/manager/cat/cat/job_list',
                ),
                '<i class="Hui-iconfont">&#xe61d;</i> 店铺管理' => array(
                    '店铺列表' => '/manager/cat/cat/shop_list',
                ),

            ),
            //权限管理
            'role' => array(
                '<i class="Hui-iconfont">&#xe61d;</i> 管理员列表' => array(
                    '管理员列表' => '/manager/role/role/admin_list',
                ),
                '<i class="Hui-iconfont">&#xe61d;</i> 修改密码' => array(
                    '修改密码' => '/manager/role/role/update_password',
                ),
                '<i class="Hui-iconfont">&#xe61d;</i> 角色列表' => array(
                    '角色列表' => '/manager/role/role/role_list',
                ),
                '<i class="Hui-iconfont">&#xe61d;</i> 管理员权限资源' => array(
                    '管理员权限资源' => '/manager/role/role/right_list?type=manager',
                ),
            ),

        );
        return $menu_array[$type];
    }
}
