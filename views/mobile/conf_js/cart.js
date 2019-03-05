//***************************************************************************************
//***************************************购物车页面 start**********************************
//***************************************************************************************
//购物车数量增加一个
function cart_increase(sku_id) {
    var old_sku_num = parseInt($('#cart_sku_' + sku_id + ' [name="sku_num"]').val());//修改前数量
    var store_nums = parseInt($('#cart_sku_' + sku_id + ' [name="store_nums"]').val());//库存
    var sku_num = Number(old_sku_num + 1);
    cart_update(sku_id, sku_num, store_nums, old_sku_num);
}

//购物车数量减少一个
function cart_reduce(sku_id) {
    var old_sku_num = parseInt($('#cart_sku_' + sku_id + ' [name="sku_num"]').val());//修改前数量
    var store_nums = parseInt($('#cart_sku_' + sku_id + ' [name="store_nums"]').val());//库存
    var minimum = parseInt($('#cart_sku_' + sku_id + ' [name="minimum"]').val());//起订量
    var sku_num = Number(old_sku_num - 1);
    if (sku_num < minimum) {
        layer.msg('商品数量不能少于' + minimum + '件');
        return false;
    }
    cart_update(sku_id, sku_num, store_nums, old_sku_num);
}

//直接修改购物车数量
function cart_update_sku_num(sku_id) {
    var old_sku_num = parseInt($('#cart_sku_' + sku_id + ' [name="sku_num"]').val());//修改前数量
    var store_nums = parseInt($('#cart_sku_' + sku_id + ' [name="store_nums"]').val());//库存
    var minimum = parseInt($('#cart_sku_' + sku_id + ' [name="minimum"]').val());//起订量
    if (sku_num < minimum) {
        layer.msg('商品数量不能少于' + minimum + '件');
        return false;
    }
    cart_update(sku_id, sku_num, store_nums);
}

//修改购物车数量
function cart_update(sku_id, sku_num, store_nums, old_sku_num) {
    if (sku_id == '') {
        layer.msg('商品ID错误');
        return false;
    }
    if (sku_num == '') {
        layer.msg('商品数量错误');
        return false;
    }
    if (store_nums < sku_num) {
        layer.msg('商品数量不能大于' + store_nums + '件');
        return false;
    }
    $.ajax({
        type: "POST",
        url: "/api/cart/update",
        data: "sku_id=" + sku_id + "&num=" + sku_num,
        dataType: "json",
        success: function (data) {
            if (data.status != 'y') {
                layer.msg(data.info);
                return false;
            }
        }

    });
    $('#cart_sku_' + sku_id + ' [name="sku_num"]').val(sku_num);//修改数量
    check_cart_all();
    return false;
}

//删除购物车指定商品
function cart_delete(sku_id, shop_id) {
    if (sku_id == '') {
        layer.msg('商品ID错误');
        return false;
    }
    $.ajax({
        type: "POST",
        url: "/api/cart/delete",
        data: "sku_id=" + sku_id,
        dataType: "json",
        success: function (data) {
            if (data.status == 'y') {
                $('#cart_sku_' + sku_id).remove();
                $("#sku_count").text('(' + data.result.sku_count + ')');//总数
                delete_shop(shop_id);//删除购物车商品后计算该店铺下是否还有商品没有的删除店铺信息
                if (data.result.sku_count < 1) {
                    $('.cartnonebox').show();
                }
                ;
            }
        }
    });
    return false;
}

//批量删除
function cart_delete_batch() {
    layer.msg('确认要删除吗？', {
        time: 0 //不自动关闭
        , btn: ['确认', '取消']
        , yes: function (index) {
            layer.close(index);
            $('#cart_list [name="sku_id[]"]:checked').each(function () {
                cart_delete($(this).val(), $(this).attr('shop_id'));
            })
        }
    });
}

//删除购物车商品后计算该店铺下是否还有商品没有的删除店铺信息
function delete_shop(shop_id) {
    //店铺内的全部商品总数
    var total_shop_num = $('#shop_' + shop_id + ' [name="sku_id[]"]').length;

    if (total_shop_num <= 0) {
        $('#shop_' + shop_id).remove();
    }
}

//清空购物车
function cart_clear() {
    $.ajax({
        type: "POST",
        url: "/api/cart/clear",
        data: "",
        dataType: "json",
        success: function (data) {
            if (data.status == 'y') {
                window.location.reload();
            }
        }
    });
}

//选择全部商品
function check_all(obj) {
    if (obj.checked == true) {
        $('#cart_list [type="checkbox"]').prop('checked', true);
    } else {
        $('#cart_list [type="checkbox"]').prop('checked', false);
    }
    check_cart_all();
}

//店铺商品全选
function check_shop_all(obj, shop_id) {
    if (obj.checked == true) {
        $('#shop_' + shop_id + ' [name="sku_id[]"]').prop('checked', true);
    } else {
        $('#shop_' + shop_id + ' [name="sku_id[]"]').prop('checked', false);
    }
    check_cart_all();
}

//选择商品
function check_sku(shop_id) {
    //店铺内的全部商品
    var total_shop_num = $('#shop_' + shop_id + ' [name="sku_id[]"]').length;
    var checked_shop_num = $('#shop_' + shop_id + ' [name="sku_id[]"]:checked').length;

    if (checked_shop_num >= total_shop_num) {
        $('#checkbox_shop_' + shop_id).prop('checked', true);
    } else {
        $('#checkbox_shop_' + shop_id).prop('checked', false);
    }
    check_cart_all();
}

//是否选择购物车的全部商品,并计算总金额
function check_cart_all() {
    var total_sku_num = $('#cart_list [name="sku_id[]"]').length;
    var checked_sku_num = $('#cart_list [name="sku_id[]"]:checked').length;
    if (checked_sku_num >= total_sku_num) {
        $('#checkbox_all').prop('checked', true);
    } else {
        $('#checkbox_all').prop('checked', false);
    }
    //计算总价
    var cart_price = 0;
    $('#cart_list [name="sku_id[]"]').each(function () {
        if ($(this).prop('checked') == true) {
            cart_price = cart_price + parseFloat($('#cart_sku_' + $(this).val() + ' [name="sku_price"]').val()*$('#cart_sku_' + $(this).val() + ' [name="sku_num"]').val());
        }
    })
    $('#sum_price').text(cart_price.toFixed(2));

}

//提交购物车
function check_confirm() {
    var checked_sku_num = $('#cart_list [name="sku_id[]"]:checked').length;
    if (checked_sku_num <= 0) {
        layer.msg('请选择商品');
        return false;
    }
    $('#cart').submit();
}
//***************************************************************************************
//***************************************购物车页面 end***********************************
//***************************************************************************************

//***************************************************************************************
//***************************************下单结算页面 start********************************
//***************************************************************************************
//开始计算所有费用
function sum_order_price() {
    var order_all_price = 0;
    //计算每个店铺内的总金额
    $('.shop_view').each(function () {
        var shop_sell_price = delivery_price = coupons_amount = promotion_price = shop_all_price = 0;
        //商品价格
        shop_sell_price = parseFloat($(this).find('[class="shop_sell_price"]').val());
        //运费价格
        delivery_price = parseFloat($(this).find('[data-delivery_price="delivery_price"]:checked').attr('deliveryprice'));
        //优惠券价格
        coupons_amount_num = $(this).find('[data-coupons_amount="coupons_amount"]:checked').attr('couponsamount');
        if (typeof(coupons_amount_num) != "undefined") coupons_amount = parseFloat(coupons_amount_num);
        //优惠活动价格
        promotion_price_num = $(this).find('[name="promotion_price[]"]').val();
        if (typeof(promotion_price_num) != "undefined") promotion_price = parseFloat(promotion_price_num);
        //总价
        shop_all_price = shop_sell_price + delivery_price - coupons_amount - promotion_price;
        $(this).find('.shop_all_price').text('￥' + shop_all_price.toFixed(2));
        //订单总价
        order_all_price = order_all_price + shop_all_price;
    })
    $('#order_all_price').text('￥' + order_all_price.toFixed(2));
    return false;
}

//配送方式操作start************************************
//选择配送方式
function select_delivery(id) {
    $('#delivery_list_' + id).addClass('maskershow');
    return false;
}

//关闭配送方式
function hide_select_delivery(id) {
    $('#delivery_list_' + id).removeClass('maskershow');
    return false;
}

//确认配送方式
function confirm_select_delivery(id) {
    $('#delivery_list_' + id).removeClass('maskershow');
    deliveryprice = $('#delivery_list_' + id + ' [data-delivery_price="delivery_price"]:checked').attr('deliveryprice');
    deliveryname = $('#delivery_list_' + id + ' [data-delivery_price="delivery_price"]:checked').attr('deliveryname');
    $('#delivery_text_' + id).html(deliveryname + ' 运费￥' + deliveryprice);
    sum_order_price();
    return false;
}
//配送方式操作end************************************

//优惠券操作start************************************
//选择优惠券
function select_coupons(id) {
    $('#coupons_list_' + id).addClass('maskershow');
    return false;
}

//关闭优惠券
function hide_select_coupons(id) {
    $('#coupons_list_' + id).removeClass('maskershow');
    return false;
}

//确认优惠券
function confirm_select_coupons(id) {
    $('#coupons_list_' + id).removeClass('maskershow');
    couponsamount = $('#coupons_list_' + id + ' [data-coupons_amount="coupons_amount"]:checked').attr('couponsamount');
    couponsname = $('#coupons_list_' + id + ' [data-coupons_amount="coupons_amount"]:checked').attr('couponsname');
    $('#coupons_text_' + id).html(couponsname + ' ￥' + couponsamount);
    sum_order_price();
    return false;
}
//优惠券操作end************************************
//***************************************************************************************
//***************************************下单结算页面 end**********************************
//***************************************************************************************