<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Adv_model extends CI_Model
{

    public function __construct()
    {
        /**
         * 载入数据库类
         */
        $this->load->database();
    }

    /**
     * 列表
     */
    public function get_adv_position($position_id = '', $html_start = '', $html_end = '')
    {
        $position_id = (int)$position_id;
        if (!empty($position_id)) {
            $this->load->model('loop_model');
            $position_data = $this->loop_model->get_id('adv_position', $position_id);
            $adv_html      = '';
            if ($position_data['status'] == '0') {
                //单个的
                if ($position_data['play_type'] == 1) {
                    $adv_data = $this->loop_model->get_where('adv', array('position_id' => $position_id, 'start_time<=' => time(), 'end_time>=' => time()));
                    $adv_html = self::show_adv($adv_data, $position_data, $html_start, $html_end);
                } elseif ($position_data['play_type'] == 2) {
                    //列表
                    $adv_data = $this->loop_model->get_list('adv', array('where' => array('position_id' => $position_id, 'start_time<=' => time(), 'end_time>=' => time())), '', '', 'sortnum asc,id desc');
                    foreach ($adv_data as $key) {
                        $adv_html .= self::show_adv($key, $position_data, $html_start, $html_end);
                    }
                } elseif ($position_data['play_type'] == 3) {
                    //随机
                    $adv_data = $this->loop_model->get_where('adv', array('position_id' => $position_id, 'start_time<=' => time(), 'end_time>=' => time()), '*', 'rand()');
                    $adv_html = self::show_adv($adv_data, $position_data, $html_start, $html_end);
                }
                return $adv_html;
            }
        }
    }


    /**
     * 展示方式html
     */
    private function show_adv($adv_data = '', $position_data = '', $html_start = '', $html_end = '')
    {
        //图片
        if ($adv_data['type'] == 1) {
            $position_data['width'] > 0 ? $width = $position_data['width'] : $width = '';
            $position_data['height'] > 0 ? $height = $position_data['height'] : $height = '';
            $adv_data['link'] != '' ? $link = $adv_data['link'] : $link = "javascript:void(0);";
            return $html = <<<Eof
                  $html_start<a href="$link" target="_blank"><img src="$adv_data[desc]" height="$height" width="$width"></a>$html_end
Eof;
        } //文字
        elseif ($adv_data['type'] == 2) {
            $adv_data['link'] != '' ? $link = $adv_data['link'] : $link = "javascript:void(0);";
            return $html = <<<Eof
                  $html_start<a href="$link" target="_blank">$adv_data[desc]</a>$html_end
Eof;
        } //代码
        elseif ($adv_data['type'] == 3) {
            $adv_data['link'] != '' ? $link = $adv_data['link'] : $link = "javascript:void(0);";
            return $html = $adv_data['desc'];
        }
    }

}
