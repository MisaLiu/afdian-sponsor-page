console.log('↓ 官方好皮！ヽ（≧□≦）ノ 给我也赞助下呗 → https://afdian.net/@MisaLiu');

function switchPage (pageId) {
    mdui.$.ajax({
        method: 'POST',
        url: 'index.php',
        data: {
            page: pageId
        },
        start: function () {
            mdui.$('#mdui_progress').removeClass('mdui-hidden');
            mdui.$.showOverlay();
            mdui.$.lockScreen();
        },
        complete: function () {
            mdui.$('#mdui_progress').addClass('mdui-hidden');
            mdui.$.hideOverlay();
            mdui.$.unlockScreen();
        },
        success: function (data) {
            let _json = undefined;

            try {
                _json = JSON.parse(data);
            } catch (e) {
                mdui.alert('拉取信息时出错', '出错啦！');
                return;
            }

            if (_json.code == 200) {
                if (_json.html != '') {
                    mdui.$('#afdian_sponsors').html(_json.html);
                    window.scroll(0, 348);
                } else {
                    mdui.alert('没有更多了', '提示');
                    return;
                }

            } else {
                mdui.alert(_json.msg, '出错啦！');
                return;
            }
        },
        error: function () {
            mdui.alert('拉取信息时出错', '出错啦！');
            return;
        }
    });
}