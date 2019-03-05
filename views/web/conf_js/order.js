
//用户取消订单
function order_cancel(order_id){
    if (order_id=='') {
        layer.msg('订单ID错误');return false;
    }
    $.ajax({
        type:"POST",
        url: "/api/member/order/cancel",
        data: "id="+order_id,
        dataType:"json",
        success: function(data){
            if (data.status=='y') {
                window.location.reload();
            } else {
                layer.msg(data.info);
            }
        }
    });
}

//用户确认订单
function order_confirm(order_id){
    if (order_id=='') {
        layer.msg('订单ID错误');return false;
    }
    $.ajax({
        type:"POST",
        url: "/api/member/order/confirm",
        data: "id="+order_id,
        dataType:"json",
        success: function(data){
            if (data.status=='y') {
                window.location.reload();
            } else {
                layer.msg(data.info);
            }
        }
    });
}

//用户删除订单
function order_delete(order_id){
    if (order_id=='') {
        layer.msg('订单ID错误');return false;
    }
    $.ajax({
        type:"POST",
        url: "/api/member/order/delete",
        data: "id="+order_id,
        dataType:"json",
        success: function(data){
            if (data.status=='y') {
                window.location.reload();
            } else {
                layer.msg(data.info);
            }
        }
    });
}

//更换订单支付方式展示支付方式
function select_payment(order_id,payment_id) {
    if (order_id=='') {
        layer.msg('订单ID错误');return false;
    }
    //支付方式请求
    $.ajax({
        type:"POST",
        url: "/api/payment/online_pay_list",
        data: '',
        dataType:"json",
        success: function(data){
            if (data.status=='y') {
                var html = '<div class="order_select_payment">';
                $.each(data.result, function(index, obj){
                    html += '<li><input';
                    if (payment_id==obj.id) {
                        html += ' checked';
                    }
                    html += ' type="radio" name="payment_id" value="'+obj.id+'" id="payment_'+obj.id+'"/>&nbsp;&nbsp;'+obj.name+'</li>';
                })
                html += '</div>'
                //支付方式展示
                layer.open({
                    type: 1,
                    title: '更换支付方式',
                    btn: ['确认', '取消'],
                    yes: function(index, layero){
                        update_payment(order_id,$('[name="payment_id"]:checked').val());//修改支付方式
                    },
                    area: ['300px'],
                    content: html
                });
            }
        }
    });
}

//更换订单支付方式保存
function update_payment(order_id,payment_id){
    if (order_id=='') {
        layer.msg('订单ID错误');return false;
    }
    $.ajax({
        type:"POST",
        url: "/api/member/order/update_payment",
        data: "id="+order_id+'&payment_id='+payment_id,
        dataType:"json",
        success: function(data){
            layer.closeAll()
            if (data.status=='y') {
                layer.msg('修改成功');
            } else {
                layer.msg(data.info);
            }
        }
    });
}




