//加入收藏
function favorite_add(id){
    if (id=='') {
        layer.msg('店铺ID错误');return false;
    } else {
        $.ajax({
            type:"POST",
            url: "/api/member/shop_favorite/add_favorite",
            data: "id="+id,
            dataType:"json",
            success: function(data){
                if (data.status=='y') {
                    $('#favorite').text('已关注');
                    layer.msg('关注成功');
                } else if (data.status=="reply") {
                    favorite_delete(data.info,id);
                } else {
                    if (data.info=='请登录后操作') {
                        layer.msg('正在跳转登录');
                        window.parent.parent.location.href='/mobile/welcome/login?redirect_url='+window.location.href;
                    } else {
                        layer.msg(data.info);
                    }
                }
            }
        });
    }
}

//取消收藏
function favorite_delete(id,shop_id){
    if (id=='') {
        layer.msg('收藏ID错误');return false;
    } else {
        $.ajax({
            type:"POST",
            url: "/api/member/shop_favorite/delete_favorite",
            data: "id="+id,
            dataType:"json",
            success: function(data){
                if (data.status=='y') {
                    $('#favorite').text('关注');
                    layer.msg('关注取消');
                } else {
                    if (data.info=='请登录后操作') {
                        layer.msg('正在跳转登录');
                        window.parent.parent.location.href='/mobile/welcome/login?redirect_url='+window.location.href;
                    } else {
                        layer.msg(data.info);
                    }
                }
            }
        });
    }
}
