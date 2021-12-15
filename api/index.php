<?php
    // 从环境变量中获取参数
    // 以下环境变量必须被设置
    $pagetitlevar = getenv('PAGETITLE'); // 网页标题
    $usernamevar = getenv('USERNAME'); // 你的用户名，即你的主页地址 @ 后面的那部分，如 https://afdian.net/@MisaLiu，那么 MisaLiu 就是你的用户名
    $useridvar = getenv('USERID'); // 你的用户 ID，请前往 https://afdian.net/dashboard/dev 获取
    $tokenvar = getenv('TOKEN'); // 你的 API Token，请前往 https://afdian.net/dashboard/dev 获取
    $_AFDIAN = array(
        'pageTitle' => $pagetitlevar,
        'userName'  => $usernamevar,
        'userId'    => $useridvar,
        'token'     => $tokenvar
    );

    $currentPage = !empty($_POST['page']) ? $_POST['page'] : 1;

    $data = array();
    $data['user_id'] = $_AFDIAN['userId'];
    $data['params']  = json_encode(array('page' => $currentPage));
    $data['ts']      = time();
    $data['sign']    = SignAfdian($_AFDIAN['token'], $data['params'], $_AFDIAN['userId']);

    $result = HttpGet('https://afdian.net/api/open/query-sponsor?' . http_build_query($data));
    $result = json_decode($result, true);

    $donator['total']     = $result['data']['total_count'];
    $donator['totalPage'] = $result['data']['total_page'];
    $donator['list']      = $result['data']['list'];

    $donatorsHTML = '';
    for ($i = 0; $i < count($donator['list']); $i++) {
        $_donator = $donator['list'][$i];
        $_donator['last_sponsor'] = (empty(end($_donator['sponsor_plans'])['name']) ?
            (empty($_donator['current_plan']['name']) ? array('name' => '') : $_donator['current_plan']) :
            end($_donator['sponsor_plans']));
        
        $donatorsHTML .= '<div class="mdui-col-xs-12 mdui-col-md-6 mdui-m-b-2">
            <div class="mdui-card">
                <div class="mdui-card-header">
                    <img class="mdui-card-header-avatar" src="' . $_donator['user']['avatar'] . '" />
                    <div class="mdui-card-header-title">' . $_donator['user']['name'] .
                    '&nbsp;&nbsp;&nbsp;&nbsp;共' . $_donator['all_sum_amount'] . '元' . '</div>
                    <div class="mdui-card-header-subtitle">最后发电：' .
                    (empty($_donator['last_sponsor']['name']) ?
                        '暂无' :
                        $_donator['last_sponsor']['name'] . '&nbsp;&nbsp;' . $_donator['last_sponsor']['show_price'] . '元，于 ' . date('Y-m-d H:i:s', $_donator['last_pay_time'])) .
                    '</div>
                </div>' .
                (!empty($_donator['last_sponsor']['pi   c']) ? '
                    <div class="mdui-card-media">
                        <img src="' . $_donator['last_sponsor']['pic'] . '"/>
                    </div>' :
                    '') .
            '</div></div>';

    }

    $pageControlHTML = '<div class="mdui-row">
        <button onclick="switchPage(' . ($currentPage - 1) . ')" class="mdui-btn mdui-btm-raised mdui-ripple mdui-color-theme-accent mdui-float-left"' . ($currentPage == 1 ? ' disabled' : '') . '>
            <i class="mdui-icon material-icons">keyboard_arrow_left</i>
            上一页
        </button>
        <div class="mdui-btn-group -center">';
    for ($i = 0; $i < $donator['totalPage']; $i++) {
        $pageControlHTML .= '<button onclick="switchPage(' . ($i + 1) . ')" class="mdui-btn ' .
        ($i + 1 == $currentPage ? 'mdui-btn-active mdui-color-theme-accent' : 'mdui-text-color-theme-text') .
        '">' . ($i + 1) . '</button>';
    }
    $pageControlHTML .= '</div>
        <button onclick="switchPage(' . ($currentPage + 1) . ')" class="mdui-btn mdui-btm-raised mdui-ripple mdui-color-theme-accent mdui-float-right"' . ($donator['totalPage'] == 1 ? ' disabled' : '') . '>
            下一页
            <i class="mdui-icon material-icons">keyboard_arrow_right</i>
        </button>
    </div>';

    if (empty($_POST)) {
$html = <<< HTML
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf8" />
        <meta name="viewport" content="width=device-width" />
        <link rel="stylesheet" href="./css/mdui.min.css" />
        <link rel="stylesheet" href="./css/main.css" />
        <script src="./js/mdui.min.js"></script>
        <title>${_AFDIAN['pageTitle']}</title>
    </head>
    <body class="mdui-appbar-with-toolbar mdui-theme-primary-blue-grey mdui-theme-accent-red mdui-theme-layout-auto">
        <header class="mdui-appbar mdui-appbar-fixed">
            <div class="mdui-progress mdui-hidden" style="position:absolute;top:0;width:100%" id="mdui_progress">
                <div class="mdui-progress-indeterminate" style="background-color:white"></div>
            </div>
            <div class="mdui-toolbar mdui-color-theme">
                <button class="mdui-btn mdui-btn-icon mdui-ripple" mdui-drawer="{target:'#drawer',swipe:true}"><i class="mdui-icon material-icons">menu</i></button>
                <a href="javascript:;" class="mdui-typo-headline">${_AFDIAN['pageTitle']}</a>
            </div>
        </header>

        <drawer class="mdui-drawer mdui-drawer-close" id="drawer">
            <div class="mdui-list">
                <a class="mdui-list-item mdui-ripple">
                    <i class="mdui-list-item-icon mdui-icon material-icons">home</i>
                    <div class="mdui-list-item-content">首页</div>
                </a>
            </div>
        </drawer>

        <main class="mdui-container mdui-typo">
            <h1 class="mdui-text-center">支持我，为我发电</h1>
            <iframe id="afdian_leaflet" class="mdui-center" src="https://afdian.net/leaflet?slug=${_AFDIAN['userName']}" scrolling="no" frameborder="0"></iframe>
            <div class="mdui-divider mdui-m-t-5"></div>
            <h2 class="mdui-text-center">感谢以下小伙伴的发电支持！</h2>
            
            <div class="mdui-m-b-2" id="afdian_sponsors">
                <div class="mdui-row">
                    ${donatorsHTML}
                </div>
                ${pageControlHTML}
            </div>
        </main>

        <script src="./js/main.js"></script>
    </body>
</html>
HTML;

        echo $html;
    } else {
        $return = array();
        $return['code'] = $result['ec'];
        $return['msg']  = $result['em'];
        $return['html'] = (!empty($donatorsHTML) ? '<div class="mdui-row">' . $donatorsHTML . "</div>" . $pageControlHTML : '');

        echo json_encode($return);
    }

    function SignAfdian ($token, $params, $userId) {
        $sign = $token;
        $sign .= 'params' . $params;
        $sign .= 'ts' . time();
        $sign .= 'user_id' . $userId;
        return md5($sign, false);
    }

    function HttpGet ($url, $method = 'GET', $data = '', $contentType = '', $timeout = 10) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        if ($method == 'POST') {
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        } else {
            curl_setopt($ch, CURLOPT_URL, $url);
        }
        if (!empty($contentType)) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $contentType);
        }
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $output = curl_exec($ch);
        curl_close($ch);
        return $output;
    }
