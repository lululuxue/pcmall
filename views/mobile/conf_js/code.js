//发送验证码
$(function () {
    //获取验证码
    $('[name="get_code"]').click(function () {
        var imgcode_url = '/api/imgcode/index';
        var mobile = $('[name="username"]').val();
        if (mobile == '') {
            layer.msg('手机号码不能为空');
            return false;
        }
        layer.open({
            type: 1,
            title: '请输入验证码',
            closeBtn: 1,
            shadeClose: true,
            area: ['200px'],
            btn: ['确认', '取消'],
            yes: function (index, layero) {
                var code_img = $('[name="code_img"]').val();
                if (code_img == '') {
                    layer.msg('验证码不能为空');
                    return false;
                }
                //获取短信
                $.ajax({
                    type: "POST",
                    url: "/api/sms_send/send",
                    data: "mobile=" + mobile + "&code_img=" + code_img,
                    dataType: "json",
                    success: function (data) {
                        if (data.status == 'y') {
                            layer.closeAll();
                            layer.msg('发送成功');
                            time = 60;
                            code_time = setInterval("set_time()", 1000);
                        } else {
                            layer.msg(data.info);
                        }

                    }
                });
            },
            content: '<div class="imgcode" style="padding: 5px;height: 30px;"><input type="text" name="code_img" style="width: 50px;height: 28px;border: 1px solid #cccccc;float: left;"><img id="imgcode" src="' + imgcode_url + '" style="width:80px;height: 30px;float: left;margin: 0 2px;"><span onclick="$(\'#imgcode\').attr(\'src\', \'' + imgcode_url + '\')">换一张</span></div>'
        });
    });
})

var time = 0;
var code_time = '';

//倒计时
function set_time() {
    if (time <= 0) {
        clearInterval(code_time);
        $('[name="get_code"]').val('点击获取验证码');
        $('[name="get_code"]').removeAttr('disabled');
    } else {
        $('[name="get_code"]').val(time + 'S后重新发送');
        $('[name="get_code"]').attr('disabled', 'disabled');
        time--;
    }
}