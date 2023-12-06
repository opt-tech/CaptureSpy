<?php
    /**
     * Created by PhpStorm.
     * User: ka.kubota
     * Date: 2016/10/11
     * Time: 14:36
     */
    require_once("../../inc/const.php");
    require_once(LIB_DIR . 'Diff/TextDiff.php');

    $token = $_GET["t"];
    $param = Dao::dlookupMulti("results", "*", "token='$token'");

    if ($param == "") {
        die("指定された差分結果情報が存在しませんでした");
        exit;
    }

    $ymd = date("Y-m-d", strtotime($param["date_at"]));

    $dir = DIFF_HISTORY_FILES_DIR . "{$ymd}/{$token}/*";
    $files = glob($dir);

    $diff = new Diff( file_get_contents( $files[0] ),
                      file_get_contents( $files[1] ));


    $html = $diff->getHtml();

?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <link href="/favicon.ico" type="image/x-icon" rel="icon"/>
    <link href="/favicon.ico" type="image/x-icon" rel="shortcut icon"/>

    <meta charset="UTF-8">
    <title>差分解析結果</title>
    <link rel="stylesheet"
          href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css"
          integrity="sha512-dTfge/zgoMYpP7QbHy4gWMEGsbsdZeCXz7irItjcC3sPUFtf0kuFbDz/ixG7ArTxmDjLXDmezHubeNikyKGVyQ=="
          crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/octicons/3.1.0/octicons.min.css">
    <style media="screen">
        body, html {
            height: 100%;
        }

        .differ {
            width: 100%;
            margin: 0;
            background: #f9f9f9;
            border: 1px solid #ddd;
            font-size: inherit;
            border-collapse: separate;
            border-spacing: 0;
            border: 1px solid #ddd;
        }

        .differ td {
            padding: 1px 4px;
            font-size: inherit;
            border-top: 1px dotted #eee;
            border-left: 1px solid #ddd;
        }

        .differ .-line:first-child td {
            border-top: 0;
        }

        .differ .-line td:first-child {
            border-left: 0;
        }

        .differ .-number {
            width: 5%;
            padding-top: .4em;
            white-space: nowrap;
            text-align: right;
            vertical-align: top;
            font-size: 80%;
            font-family: Arial;
            border-top: 1px solid #e6e6e6;
            color: #999;
        }

        .differ .-text {
            word-wrap: break-word;
            word-break: break-all;
            overflow-wrap: break-word;

            padding-left: 8px;
            border-left: 3px double #ddd;
            background: #fff;
            width: 45%;
        }

        .differ .-is-differ .-text {
            background: #FFFBE6;
        }

        .differ .-no-differ .-text {
            color: #777;
        }

        .differ .-word {
            word-wrap: break-word;
            word-break: break-all;
            overflow-wrap: break-word;

            display: inline-block;
            vertical-align: middle;
            /*font-weight:bold;*/
        }

        .differ .-word.-source {
            color: green;
            background: #dfd;
        }

        .differ .-word.-change {
            color: red;
            background: #fdd;
        }

        .output {
            margin-top: 20px;
            padding-bottom: 30px;
        }

        .container {
            width: 100%;
            position: relative;
            height: auto !important;
            height: 100%;
            min-height: 100%;
        }

        .col-left {
            width: 50%;
            float: left;
        }

        .col-right {
            width: 50%;
            float: right;
        }

        #footer {
            position: fixed;
            bottom: 0;
            width: 100%;
            height: 20px;
            background-color: #4E9ABE;
            color: #fff;
            margin-left: -15px;
        }
    </style>
</head>
<body>
<header>
    <nav class="navbar navbar-default">
        <div class="container-fluid">
            <div class="navbar-header">
                <a href="/">
                    <img src="/common/img/icon.png" height="45px" alt="">
                </a>
            </div>
        </div>
    </nav>
</header>
<div class="container">

    <div class="row output">
        <div id="out_source" class="col-left">
            <div style="margin:0 0 10px 95px;"><a href="file_dl.php?t=<?php echo $token ?>&i=0">対象ファイルダウンロード</a></div>
            <?php echo $html["source"] ?>
        </div>
        <div id="out_change" class="col-right">
            <div style="margin:0 0 10px 95px;"><a href="file_dl.php?t=<?php echo $token ?>&i=1">対象ファイルダウンロード</a></div>
            <?php echo $html["change"] ?>
        </div>
    </div>

    <div id="footer">検査日時：<?php echo $param["date_at"] ?>　検索タグ数：<?php echo $param["total"] ?>　相違点数：<?php echo $param["diffs"] ?>　対象URL：<?php echo $param["url"] ?>　</div>
</div>

</body>
</html>
