<?php

//Debug モード
define("DEBUG_MODE", 1); //1=Debugモード。ログ吐き出し有り

//パス関連
define("ROOT_DIR", "/var/www/"); //プロジェクトディレクトリ
define("DOC_DIR", ROOT_DIR . "html");    //ドキュメントルート
define("ROOT_INC", ROOT_DIR . "inc/"); //クラス等インクルードフォルダ
define("LOG_DIR", ROOT_INC . "log/"); //ログフォルダ
define("TMPL_DIR", ROOT_INC . "tmpl/");    //テンプレートフォルダ
define("LIB_DIR", ROOT_INC . "libs/"); //サードパティライブラリフォルダ
define("CRON_DIR", ROOT_INC . "cron/"); //クーロンフォルダ
define("TEMP_DIR", ROOT_INC . "temp/"); //テンポラリディレクトリ
define("DIFF_HISTORY_FILES_DIR", ROOT_INC . "diff_files/");    //diff APIを利用した際のHTMLファイル

define("BASE_URL", "http://52.69.251.171/");   //URL


require_once(ROOT_INC . "htmltemplate.php");
require_once("/var/www/inc/libs/aws/autoload.php");

//DB関連情報定義
define("DB_SERVER", "capturespy.cfmlp9dk3yvw.ap-northeast-1.rds.amazonaws.com");                    //サーバ名
define("DB_NAME", "capturespydb");                            //DB名
define("DB_PORT", "3306");                                        //ポート番号
define("DB_USER", "capturespy");                                //ユーザID
define("DB_PASS", 'capturespy');                                //パスワード
define("DB_STORE", 'mysql');                                //データストア（mysql / postgresql）

function autoload($className)
{
    if (file_exists(ROOT_INC . strtolower("cls_" . $className . ".php"))) {
        include_once ROOT_INC . strtolower("cls_" . $className . ".php");

        return;
    }

    if (file_exists(ROOT_INC . "asps/" . strtolower("cls_" . $className . ".php"))) {
        include_once ROOT_INC . "asps/" . strtolower("cls_" . $className . ".php");

        return;
    }

    $pClassFilePath = PHPEXCEL_ROOT . str_replace('_', DIRECTORY_SEPARATOR, $className) . '.php';
    if (file_exists($pClassFilePath)) {
        include_once $pClassFilePath;

        return;
    }
}

spl_autoload_register("autoload");

//一覧表示の表示数候補
$RECORD_NUM = [
    '20'  => '20件',
    '50'  => '50件',
    '100' => '100件',
];

//グラフ表示期間の候補
$GRAPH_RANGE = [
    '30'  => '30日',
    '60'  => '60日',
    '90'  => '90日',
    '180' => '180日',
    '365' => '365日',
];

$AWS_CONFIG = [
    'credentials' => [
        'key'    => 'AKIAIKILQWMB6KFIONFA',
        'secret' => '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',
    ],
    'region'      => 'ap-northeast-1',
    'bucket'      => 'dev.capturespy',
    'version'     => 'latest',
];

$UA = [
    'PC' => 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/53.0.2785.143 Safari/537.36',
    'SP' => 'Mozilla/5.0 (iPhone; CPU iPhone OS 9_0 like Mac OS X) AppleWebKit/601.1.46 (KHTML, like Gecko) Version/9.0 Mobile/13A344 Safari/601.1',
];

$COLORS = [
    'ELEMENTS' => [
        'PC' => '243,105,46',
        'SP' => '246,195,71'
    ],
    'SOURCES' => [
        'PC' => '110,187,70',
        'SP' => '52,163,215'
    ],
];
