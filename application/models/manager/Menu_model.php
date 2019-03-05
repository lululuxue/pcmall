<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Menu_model extends CI_Model
{

    /**
     * 添加
     * @param array $data 添加数据
     */
    public function get_menu($type = 'order')
    {
        $menu_array = array(
            'goods' => array(
                '<i class="Hui-iconfont">&#xe620;</i> 商品管理' => array(
                    '商品管理' => '/manager/goods/goods/index',
                ),
                '<i class="Hui-iconfont">&#xe681;</i> 商品分类' => array(
                    '分类管理' => '/manager/goods/category/index',
                ),
                '<i class="Hui-iconfont">&#xe64b;</i> 商品品牌' => array(
                    '品牌管理' => '/manager/goods/brand/index',
                ),
                '<i class="Hui-iconfont">&#xe6ad;</i> 模型管理' => array(
                    '模型列表' => '/manager/goods/model/index'
                ),
            ),
            'member' => array(
                '<i class="Hui-iconfont">&#xe60d;</i> 会员管理' => array(
                    '会员管理' => '/manager/member/user/index',
                    '会员组管理' => '/manager/member/user_group/index',
                    '会员提现管理' => '/manager/member/user_withdraw/index',
                ),
                '<i class="Hui-iconfont">&#xe66a;</i> 店铺管理' => array(
                    '店铺管理' => '/manager/member/shop/index',
                ),
            ),
            'order' => array(
                '<i class="Hui-iconfont">&#xe673;</i> 订单管理' => array(
                    '订单管理' => '/manager/order/order/index',
                ),
                '<i class="Hui-iconfont">&#xe637;</i> 单据管理' => array(
                    '收款单' => '/manager/order/collection_doc/index',
                    '退款单' => '/manager/order/refund_doc/index',
                    '发货单' => '/manager/order/delivery_doc/index',
                    '退款申请' => '/manager/order/refund_doc/pending',
                ),
                '<i class="Hui-iconfont">&#xe643;</i> 发货管理' => array(
                    '发货地址管理' => '/manager/order/address/index',
                ),
            ),
            'tool' => array(
                '<i class="Hui-iconfont">&#xe616;</i> 文章管理' => array(
                    '文章管理' => '/manager/tool/article/index',
                    '文章分类' => '/manager/tool/article_cat/index',
                ),
                '<i class="Hui-iconfont">&#xe635;</i> 广告管理' => array(
                    '广告管理' => '/manager/tool/adv/index',
                    '广告位管理' => '/manager/tool/adv_position/index',
                ),
            ),
            'market' => array(
                '<i class="Hui-iconfont">&#xe6bb;</i> 促销管理' => array(
                    '促销管理' => '/manager/market/promotion/index',
                ),
                '<i class="Hui-iconfont">&#xe6ca;</i> 优惠券' => array(
                    '优惠券管理' => '/manager/market/coupons/index',
                ),
            ),
            'report' => array(
                '<i class="Hui-iconfont">&#xe61e;</i> 数据统计' => array(
                    '用户注册统计' => '/manager/report/statistics/user_reg',
                    '人均消费统计' => '/manager/report/statistics/consumption_avg',
                    '销售金额统计' => '/manager/report/statistics/consumption_sum',
                    '订单数量统计'  => '/manager/report/statistics/order_count',
                ),
                '<i class="Hui-iconfont">&#xe695;</i> 操作记录' => array(
                    '充值记录' => '/manager/report/online_recharge/index',
                    '后台记录' => '/manager/report/admin_log/index',
                ),
            ),
            'system' => array(
                '<i class="Hui-iconfont">&#xe61d;</i> 网站管理' => array(
                    '站点设置' => '/manager/system/web_config/index',
                ),
                '<i class="Hui-iconfont">&#xe669;</i> 配送管理' => array(
                    '配送方式' => '/manager/system/delivery/index',
                    '快递公司' => '/manager/system/express_company/index',
                ),
                '<i class="Hui-iconfont">&#xe628;</i> 支付管理' => array(
                    '支付方式' => '/manager/system/payment/index',
                ),
                '<i class="Hui-iconfont">&#xe605;</i> 权限' => array(
                    '管理员' => '/manager/system/admin/index',
                    '修改密码' => '/manager/system/admin/update_password',
                    '角色管理' => '/manager/system/role/index',
                    '管理员权限资源' => '/manager/system/role_right/index?type=manager',
                    '店铺权限资源' => '/manager/system/role_right/index?type=shop',
                ),
            ),
        );
        return $menu_array[$type];
    }
}
