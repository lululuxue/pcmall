<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Delivery_model extends CI_Model
{

    public function __construct()
    {
        /**
         * 载入数据库类
         */
        $this->load->database();
    }

    /**
     * 根据id查询数据
     * @param int $id 数据id
     * @return array
     */
    public function get_where($where_data)
    {
        if (!empty($where_data)) {
            //配送信息
            $query               = $this->db->get_where('delivery', $where_data);//echo $this->db->last_query()."<br>";
            $row                 = $query->row_array();
            $row['first_price']  = format_price($row['first_price']);
            $row['second_price'] = format_price($row['second_price']);
            $row['area_groupid'] = json_decode($row['area_groupid'], true);
            $firstprice          = json_decode($row['firstprice'], true);
            $secondprice         = json_decode($row['secondprice'], true);
            if (is_array($firstprice)) {
                foreach ($firstprice as $key) {
                    $firstprice_list[] = format_price($key);
                }
                $row['firstprice'] = $firstprice_list;
            }
            if (is_array($secondprice)) {
                foreach ($secondprice as $key) {
                    $secondprice_list[] = format_price($key);
                }
                $row['secondprice'] = $secondprice_list;
            }
            return $row;
        }
    }


    /**
     * 店铺下的所有配送方式计算运费
     * @param $all_price  int 订单总价
     * @param $all_weight int 商品总重量
     * @param $prov       int 省份的ID
     * @param $shop_id    int 店铺ID
     * @param $m_id       int 用户id
     */
    public function shop_delivery($all_price = false, $all_weight = false, $prov = false, $shop_id = false, $m_id = false)
    {
        if (isset($all_price) && isset($all_weight) && !empty($prov) && isset($shop_id)) {
            //查询店铺下的所有配送方式
            $this->db->from('delivery');
            $this->db->where(array('status' => 0, 'shop_id' => $shop_id));
            $query         = $this->db->get();
            $delivery_list = $query->result_array();

            foreach ($delivery_list as $v => $k) {
                $delivery_data = self::order_delivery($all_price, $all_weight, $k['id'], $prov, $m_id);
                if (!empty($delivery_data)) {
                    $res[] = $delivery_data;
                }
            }
            return $res;
        }
    }

    /**
     * 根据配送方式id计算运费
     * @param $all_price   int 订单总价
     * @param $all_weight  int 商品总重量
     * @param $prov        int 省份的ID
     * @param $delivery_id int 配送方式ID
     * @param $m_id        int 用户id
     */
    public function order_delivery($all_price = false, $all_weight = false, $delivery_id = false, $prov = false, $m_id = false)
    {
        if (isset($all_price) && isset($all_weight) && !empty($delivery_id) && !empty($prov)) {
            //查询配送方式详情
            $query         = $this->db->get_where('delivery', array('id' => $delivery_id));//echo $this->db->last_query()."<br>";
            $delivery_data = $query->row_array();
            if ($delivery_data['status'] != 0) return false;
            $price       = $old_price = '';//运费
            $if_delivery = '0';//是否能送达

            //当配送方式是统一配置的时候，不区分地区
            if ($delivery_data['price_type'] == 0) {
                $price = self::get_free_weight($all_weight, $delivery_data['first_weight'], $delivery_data['second_weight'], $delivery_data['first_price'], $delivery_data['second_price']);//计算运费
            } //当配送方式为指定区域和价格的时候
            else {
                $special     = false;
                $special_key = '';
                //每项都是以';'隔开的省份IDkey
                $area_groupid = json_decode($delivery_data['area_groupid'], true);
                foreach ($area_groupid as $val => $key) {
                    //匹配到了特殊的省份运费价格
                    if (strpos($key, ';' . $prov . ';') !== false) {
                        $special     = true;
                        $special_key = $val;
                        break;
                    }
                }

                //匹配到了特殊的省份运费价格
                if ($special) {
                    //获取当前省份特殊的运费价格
                    $firstprice  = json_decode($delivery_data['firstprice'], true);
                    $secondprice = json_decode($delivery_data['secondprice'], true);

                    $price = self::get_free_weight($all_weight, $delivery_data['first_weight'], $delivery_data['second_weight'], $firstprice[$special_key], $secondprice[$special_key]);//计算运费
                } else {
                    //判断是否设置默认费用了
                    if ($delivery_data['open_default'] == 1) {
                        $price = self::get_free_weight($all_weight, $delivery_data['first_weight'], $delivery_data['second_weight'], $delivery_data['first_price'], $delivery_data['second_price']);//计算运费
                    } else {
                        $price       = '0';
                        $if_delivery = '1';
                    }
                }
            }
            $old_price = $price;

            //促销规则满足免运费
            $m_id                = get_m_id();
            $shop_all_sell_price = price_format($key['all_sell_price']);//订单商品销售总金额
            $this->load->model('market/promotion_model');
            $free_delivery = $this->promotion_model->free_delivery_list($m_id, $delivery_data['shop_id'], $all_price);
            if ($free_delivery) $price = 0;//免运费

            if ($if_delivery == '0') {
                $res = array(
                    'id'        => $delivery_data['id'],
                    'type'      => $delivery_data['type'],
                    'name'      => $delivery_data['name'],
                    'desc'      => $delivery_data['desc'],
                    'price'     => format_price($price),
                    'old_price' => format_price($old_price),
                );
                return $res;
            }
        }
    }

    /**
     * 根据重量计算给定价格
     * @param $weight         int 总重量
     * @param $delivery_data  array 配送方式数据
     */
    private static function get_free_weight($weight, $first_weight, $second_weight, $firstprice, $secondprice)
    {
        //当商品重量小于或等于首重的时候
        if ($weight <= $first_weight) {
            return $firstprice;
        }

        //当商品重量大于首重时，根据次重进行累加计算
        $num = ceil(($weight - $first_weight) / $second_weight);
        return ($firstprice + $secondprice * $num);
    }
}
