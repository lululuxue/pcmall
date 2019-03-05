//加载本js的时候必须给定client变量
//购物车数量
$(function () {
    $.ajax({
        type: "POST",
        url: "/api/cart/cart_count",
        data: "",
        dataType: "json",
        success: function (data) {
            if (data.status == 'y') {
                if (data.result.sku_count > 0) {
                    $('#my_cart_count').text(data.result.sku_count);
                    $('#my_cart_count').show();
                } else {
                    $('#my_cart_count').hide();
                    $('#top_my_cart_count').hide();
                }
            }
        }
    });
})

//修改购买数量
function update_num(num) {
    var buy_num = parseInt($('#buy_num').val()) + num;
    var store_nums = parseInt($('#store_nums').text());//最大库存
    var sku_minimum = parseInt($('#sku_minimum').val());//起订量
    if (buy_num < sku_minimum) {
        layer.msg('购买数量不能小于' + sku_minimum + '件');
        return false;
    } else if (buy_num > store_nums) {
        layer.msg('购买数量不能大于' + store_nums + '件');
        return false;
    } else {
        $('#buy_num').val(buy_num);
    }
}

//验证购买数量是否正确
function check_sku_num() {
    var buy_num = parseInt($('#buy_num').val());
    var store_nums = parseInt($('#store_nums').text());//最大库存
    var sku_minimum = parseInt($('#sku_minimum').val());//起订量
    if (buy_num < sku_minimum) {
        layer.msg('购买数量不能小于' + sku_minimum + '件');
        return false;
    } else if (buy_num > store_nums) {
        layer.msg('购买数量不能大于' + store_nums + '件');
        return false;
    }
    return true;
}

//直接购买
function add_order() {
    //验证数量
    if (check_sku_num() === true) {
        var sku_id = $('#sku_id').val();
        var buy_num = parseInt($('#buy_num').val());
        if (sku_id == '') {
            layer.msg('请选择规格');
            return false;
        }
        window.location.href = '/' + client + '/cart/confirm?sku_id=' + sku_id + '&buy_num=' + buy_num + '&buy_type=sku';
    }
}

//加入购物车
function add_cart(url,id) {
    //验证数量
    if (check_sku_num() === true) {
        var sku_id = $('#sku_id').val();
        var buy_num = parseInt($('#buy_num').val());
        if (sku_id == '') {
            layer.msg('请选择规格');
            return false;
        }
        $.ajax({
            type: "POST",
            url: "/api/cart/add",
            data: "sku_id=" + sku_id + "&num=" + buy_num,
            dataType: "json",
            success: function (data) {
                if (data.status == 'y') {
                    layer.msg('加入成功');
                    parent.$('#my_cart_count').text(data.result.sku_count).show();
                    setTimeout(function () {
                        //隐藏选择框
                        $('.masker').removeClass('maskershow');
                        parent.layer.closeAll('iframe');//列表页关闭弹出框
                    }, 1000);
                } else {
                    if (data.info == '请登录后操作') {
                        layer.msg('正在跳转登录');
                        window.parent.parent.location.href = '/' + client + '/welcome/login?redirect_url=' + url+'/'+id;
                    } else {
                        setTimeout(function () {
                            //隐藏选择框
                            $('.masker').removeClass('maskershow');
                            parent.layer.closeAll('iframe');//列表页关闭弹出框
                        }, 1000);
                        layer.msg(data.info);
                    }
                }
            }
        });
    }
}

//加入收藏
function favorite_add(id) {
    if (id == '') {
        layer.msg('商品ID错误');
        return false;
    } else {
        $.ajax({
            type: "POST",
            url: "/api/member/goods_favorite/add_favorite",
            data: "id=" + id,
            dataType: "json",
            success: function (data) {
                if (data.status == 'y') {
                    $('#godos_favorite_' + id).attr('src', '/views/mobile/skin/images/is_favorite.png');
                    layer.msg('收藏成功');
                } else if (data.status == "reply") {
                    favorite_delete(data.info, id);
                } else {
                    if (data.info == '请登录后操作') {
                        layer.msg('正在跳转登录');
                        window.parent.parent.location.href = '/' + client + '/welcome/login?redirect_url=' + window.location.href;
                    } else {
                        layer.msg(data.info);
                    }
                }
            }
        });
    }
}

//取消收藏id收藏id,goods_id商品id
function favorite_delete(id, goods_id) {
    if (id == '') {
        layer.msg('收藏ID错误');
        return false;
    } else {
        $.ajax({
            type: "POST",
            url: "/api/member/goods_favorite/delete_favorite",
            data: "id=" + id,
            dataType: "json",
            success: function (data) {
                if (data.status == 'y') {
                    $('#godos_favorite_' + goods_id).attr('src', '/views/mobile/skin/images/no_favorite.png')
                    layer.msg('收藏取消');
                } else {
                    if (data.info == '请登录后操作') {
                        layer.msg('正在跳转登录');
                        window.parent.parent.location.href = '/' + client + '/welcome/login?redirect_url=' + window.location.href;
                    } else {
                        layer.msg(data.info);
                    }
                }
            }
        });
    }
}

//店铺收藏
function shop_favorite_add(id) {
    if (id == '') {
        layer.msg('店铺ID错误');
        return false;
    } else {
        $.ajax({
            type: "POST",
            url: "/api/member/shop_favorite/add_favorite",
            data: "id=" + id,
            dataType: "json",
            success: function (data) {
                if (data.status == 'y') {
                    layer.msg('收藏成功');
                } else if (data.status == "reply") {
                    layer.msg('已经收藏该店铺');
                } else {
                    if (data.info == '请登录后操作') {
                        layer.msg('正在跳转登录');
                        window.parent.parent.location.href = '/' + client + '/welcome/login?redirect_url=' + window.location.href;
                    } else {
                        layer.msg(data.info);
                    }
                }
            }
        });
    }
}

//商品评论
function comment_list(id) {
    if (id == '') {
        layer.msg('商品ID错误');
        return false;
    } else {
        $.ajax({
            type: "POST",
            url: "/api/goods/goods/comment_list",
            data: "id=" + id,
            dataType: "json",
            success: function (data) {
                if (data.status == 'y') {
                    var html = template('comment_list_Template', data);
                    $('#comment_list').html(html);
                    $('#all_count_comment').text(data.result.all_rows);
                } else {
                    layer.msg(data.info);
                }
            }
        });
    }
}


//****************************************************************
//********************************SKU属性选择**************************
//****************************************************************

//获取所有包含指定节点的路线
function filterProduct(ids) {
    var result = [];
    $(sku_list).each(function (k, v) {
        _attr = ';' + v['sku_key'] + ';';
        _all_ids_in = true;
        for (k in ids) {
            if (_attr.indexOf(';' + ids[k] + ';') == -1) {
                _all_ids_in = false;
                break;
            }
        }
        if (_all_ids_in) {
            result.push(v);
        }

    });
    return result;
}

//获取 经过已选节点 所有线路上的全部节点
// 根据已经选择得属性值，得到余下还能选择的属性值
function filterAttrs(ids) {
    var products = filterProduct(ids);
    //console.log(products);
    var result = [];
    $(products).each(function (k, v) {
        result = result.concat(v['sku_key'].split(';'));

    });
    return result;
}


//已选择的节点数组
function _getSelAttrId() {
    var list = [];
    $('.goods_attr .goods_sku.current').each(function () {
        list.push($(this).attr('val'));
    });
    return list;
}

$(function(){
    $('.goods_attr .goods_sku').click(function () {
        if ($(this).hasClass('no_select')) {
            return;//被锁定了
        }
        if ($(this).hasClass('current')) {
            $(this).removeClass('current');
        } else {
            if ($(this).attr('data-type') == 2) $('#sku_image').attr('src', $(this).attr('val'));
            $(this).siblings().removeClass('current');
            $(this).addClass('current');

        }
        var select_ids = _getSelAttrId();

        //已经选择了的规格
        var $_sel_goods_attr = $('.goods_sku.current').parents('.goods_attr');

        // step 1
        var all_ids = filterAttrs(select_ids);
        //比较选择的规格个数是否和键值个数是否一样
        if ($('.goods_sku.current').length == all_ids.length) {
            //根据键值查询数据对应信息,并赋值
            $.each(sku_list, function (idx, obj) {
                sel_sku_key = all_ids.join(';');
                if (obj['sku_key'] == sel_sku_key) {
                    $('#sell_price').text('￥' + obj['sell_price']);//销售价
                    //$('#market_price').text('￥' + obj['market_price']);//市场价
                    $('#store_nums').text(obj['store_nums']);//库存
                    $('#sku_id').val(obj['sku_id']);
                    $('#sku_minimum').val(obj['minimum']);//起订量
                    $('#buy_num').val(obj['minimum']);
                }
            });
        } else {
            $('#sku_id').val('');
        }

        //获取未选择的
        var $other_notsel_attr = $('.goods_attr').not($_sel_goods_attr);

        //设置为选择属性中的不可选节点
        $other_notsel_attr.each(function () {
            set_block($(this), all_ids);

        });

        //step 2
        //设置已选节点的同级节点是否可选
        $_sel_goods_attr.each(function () {
            update_2($(this));
        });


    });
})


function update_2($goods_attr) {
    // 若该属性值 $li 是未选中状态的话，设置同级的其他属性是否可选
    var select_ids = _getSelAttrId();
    var $li = $goods_attr.find('.goods_sku.current');

    var select_ids2 = del_array_val(select_ids, $li.attr('val'));

    var all_ids = filterAttrs(select_ids2);

    set_block($goods_attr, all_ids);
}

function set_block($goods_attr, all_ids) {
    //根据 $goods_attr下的所有节点是否在可选节点中（all_ids） 来设置可选状态
    $goods_attr.find('.goods_sku').each(function (k2, li2) {
        if ($.inArray($(li2).attr('val'), all_ids) == -1) {
            $(li2).addClass('no_select');
        } else {
            $(li2).removeClass('no_select');
        }
    });

}
function del_array_val(arr, val) {
    //去除 数组 arr中的 val ，返回一个新数组
    var a = [];
    for (k in arr) {
        if (arr[k] != val) {
            a.push(arr[k]);
        }
    }
    return a;
}