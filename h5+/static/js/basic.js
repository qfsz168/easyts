$.fn.serializeObject = function () {
    var o = {};
    var a = this.serializeArray();
    $.each(a, function () {
        if (o[this.name] !== undefined) {
            if (!o[this.name].push) {
                o[this.name] = [o[this.name]];
            }
            o[this.name].push(this.value || '');
        } else {
            o[this.name] = this.value || '';
        }
    });
    return o;
};

Date.prototype.format = function (format) {
    var o = {
        "M+": this.getMonth() + 1, //month
        "d+": this.getDate(), //day
        "h+": this.getHours(), //hour
        "m+": this.getMinutes(), //minute
        "s+": this.getSeconds(), //second
        "q+": Math.floor((this.getMonth() + 3) / 3), //quarter
        "S": this.getMilliseconds() //millisecond
    }
    if (/(y+)/.test(format)) format = format.replace(RegExp.$1,
        (this.getFullYear() + "").substr(4 - RegExp.$1.length));
    for (var k in o) if (new RegExp("(" + k + ")").test(format))
        format = format.replace(RegExp.$1,
            RegExp.$1.length == 1 ? o[k] :
                ("00" + o[k]).substr(("" + o[k]).length));
    return format;
};

function post(url, data, cfg) {
    var layerIndex = layer.load(0, {time: 10000});
    $.post(url, data, function (res) {
        layer.close(layerIndex);

        if (res.code !== 1) {
            layer.msg(res.msg);
            return false;
        }

        cfg(res.data);
    });
}

function dump(data) {
    console.log(data);
}

/**
 * 字符日期转php时间戳
 * @param str
 * @returns {number}
 */
function strtotime(str) {
    if (str === undefined) {
        return (new Date().getTime()) / 1000;
    }
    return new Date(Date.parse(str.replace(/-/g, "/"))).getTime() / 1000;
}

/**
 * 格式化php时间戳
 * @param timestamp
 */
function date(timestamp) {
    if (timestamp === undefined) {
        return new Date().format('yyyy-MM-dd h:m:s');
    }

    return new Date(timestamp * 1000).format('yyyy-MM-dd h:m:s');
}

/**
 * mui.post() 的封装
 * @param url post请求地址
 * @param data post数据
 * @param func 回调函数
 */
function muiPost(url, data, func) {

    mui("body").progressbar().show();
    mui.post(url, data, function (res) {
        mui("body").progressbar().hide();
        if (res.code != 1) {
            mui.alert(res.msg);
            return;
        }
        func(res.data);
    });

    //超时处理
    mui.later(function () {
        mui("body").progressbar().hide();
    }, 20 * 1000);

}

/**
 * 输入框
 * @param title 提示信息
 * @param selector
 * @param event
 * @param fuc
 */
function muiPrompt(title, fuc) {
    mui.prompt('', title, '', function (obj) {

        if (obj.index != 1) {
            return;
        }
        if (obj.value === "") {
            return;
        }

        fuc(obj.value);
    });
}

function muiOpen(url, laterTime) {
    if (!laterTime) {
        mui.openWindow({
            url: url,
            id: url,
        });
        return;
    }

    mui.later(function () {
        mui.openWindow({
            url: url,
            id: url,
        });
    }, laterTime);

}

function muiOn(event, selector, func) {
    mui(document.body).on(event, selector, function () {
        func(this);
    });

}

function isWeiXin() {
    var ua = window.navigator.userAgent.toLowerCase();
    if (ua.match(/MicroMessenger/i) == 'micromessenger') {
        return true;
    } else {
        return false;
    }
}