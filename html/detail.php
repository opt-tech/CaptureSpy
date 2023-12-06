<?php
    //ini_set("display_errors", 1);

    /**
     * Created by PhpStorm.
     * User: ka.kubota
     * Date: 2016/10/11
     * Time: 14:36
     */
    require_once("../inc/const.php");
    require_once(LIB_DIR . 'Diff/TextDiff.php');
    $s3 = new S3manager($AWS_CONFIG);

    $url_id = $_GET["url_id"];
    $dt2 = $_GET["dt"];
    $dt1 = @$_GET["dt0"];

    if($dt1 == ""){
        $dt1 = Util::DateAdd($dt2."000000","d", -1);
        $dt1 = date("Y-m-d", $dt1["unixtime"]);
    }

    /* データ取得
    ----------------------------------------------------------------------- */
    //1日目のPC、シンプルデータ
    $file1["PC"]["simple"] = Dao::dlookup("logs", "s3path", "url_id={$url_id} AND logs.date_at='{$dt1}' AND ua='PC' AND isfirst=1");
    $data1["PC"]["simple"] = $s3->read($file1["PC"]["simple"]);

    //1日目のSP、シンプルデータ
    $file1["SP"]["simple"] = Dao::dlookup("logs", "s3path", "url_id={$url_id} AND logs.date_at='{$dt1}' AND ua='SP' AND isfirst=1");
    $data1["SP"]["simple"] = $s3->read($file1["SP"]["simple"]);

    //1日目のPC、completeデータ
    $file1["PC"]["complete"] = Dao::dlookup("logs", "s3path", "url_id={$url_id} AND logs.date_at='{$dt1}' AND ua='PC' AND isfirst=0");
    $data1["PC"]["complete"] = $s3->read( $file1["PC"]["complete"] );

    //1日目のSP、completeデータ
    $file1["SP"]["complete"] = Dao::dlookup("logs", "s3path", "url_id={$url_id} AND logs.date_at='{$dt1}' AND ua='SP' AND isfirst=0");
    $data1["SP"]["complete"] = $s3->read($file1["SP"]["complete"]);


    //2日目のPC、シンプルデータ
    $file2["PC"]["simple"] = Dao::dlookup("logs", "s3path", "url_id={$url_id} AND logs.date_at='{$dt2}' AND ua='PC' AND isfirst=1");
    $data2["PC"]["simple"] = $s3->read($file2["PC"]["simple"]);

    //2日目のSP、シンプルデータ
    $file2["SP"]["simple"] = Dao::dlookup("logs", "s3path", "url_id={$url_id} AND logs.date_at='{$dt2}' AND ua='SP' AND isfirst=1");
    $data2["SP"]["simple"] = $s3->read($file2["SP"]["simple"]);

    //2日目のPC、completeデータ
    $file2["PC"]["complete"] = Dao::dlookup("logs", "s3path", "url_id={$url_id} AND logs.date_at='{$dt2}' AND ua='PC' AND isfirst=0");
    $data2["PC"]["complete"] = $s3->read($file2["PC"]["complete"]);

    //2日目のSP、completeデータ
    $file2["SP"]["complete"] = Dao::dlookup("logs", "s3path", "url_id={$url_id} AND logs.date_at='{$dt2}' AND ua='SP' AND isfirst=0");
    $data2["SP"]["complete"] = $s3->read($file2["SP"]["complete"]);


    /* diff計算
    ----------------------------------------------------------------------- */
    $diff = new Diff($data1["PC"]["simple"], $data2["PC"]["simple"]);
    $html["PC"]["simple"] = $diff->getHtml();
    $html["PC"]["simple"]['count'] = $diff->getDiffRatio(). "%<br>{$diff->getDiffCount() } / {$diff->getTotalCount()}";

    $diff = new Diff($data1["PC"]["complete"], $data2["PC"]["complete"]);
    $html["PC"]["complete"] = $diff->getHtml();
    $html["PC"]["complete"]['count'] = $diff->getDiffRatio(). "%<br>{$diff->getDiffCount() } / {$diff->getTotalCount()}";

    $diff = new Diff($data1["SP"]["simple"], $data2["SP"]["simple"]);
    $html["SP"]["simple"] = $diff->getHtml();
    $html["SP"]["simple"]['count'] = $diff->getDiffRatio(). "%<br>{$diff->getDiffCount() } / {$diff->getTotalCount()}";

    $diff = new Diff($data1["SP"]["complete"], $data2["SP"]["complete"]);
    $html["SP"]["complete"] = $diff->getHtml();
    $html["SP"]["complete"]['count'] = $diff->getDiffRatio(). "%<br>{$diff->getDiffCount() } / {$diff->getTotalCount()}";

?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <!--script src="/common/js/jquery-2.0.2.min.js"></script-->

    <link rel="stylesheet" href="http://code.jquery.com/ui/1.9.2/themes/base/jquery-ui.css" />
    <script src="http://code.jquery.com/jquery-2.0.2.js"></script>
    <script src="http://code.jquery.com/ui/1.9.2/jquery-ui.js"></script>

    <script src="/common/js/sync_scroll.js"></script>
    <script src="/common/js/lock.js"></script>

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


        .container {
            width: 100%;
            position: relative;
            /*height: auto !important;*/
            height: 100%;
            min-height: 100%;
        }

        .tdButton {
            cursor: pointer;
            cursor: pointer;
        }

        .tdButton:hover{
            color:#ff5555;
            background-color:#FFFBE6;
        }

    </style>

    <script type="text/javascript">
    window.onload = function () {
        $(".datepicker").datepicker();
        $(".datepicker").datepicker( "option", "dateFormat", "yy-mm-dd");

        $("#dt1").val("<?php echo $dt1 ?>");
        $("#dt2").val("<?php echo $dt2 ?>");

        // 縦方向のみ
        var syncScroll1 = new SyncScroll(
            document.getElementById('pc_simple_1'),
            document.getElementById('pc_simple_2'));
        syncScroll1.enableHorizontal = false;

        var syncScroll2 = new SyncScroll(
            document.getElementById('pc_comp_1'),
            document.getElementById('pc_comp_2'));
        syncScroll2.enableHorizontal = false;

        var syncScroll3 = new SyncScroll(
            document.getElementById('sp_simple_1'),
            document.getElementById('sp_simple_2'));
        syncScroll3.enableHorizontal = false;

        var syncScroll4 = new SyncScroll(
            document.getElementById('sp_comp_1'),
            document.getElementById('sp_comp_2'));
        syncScroll4.enableHorizontal = false;

    };

    function toggleTd(trg){
        var height = $("#"+trg).height();
        var trg2 = trg.replace("_1", "_2");

        if(height >= 300){
            $("#"+trg).css('height', '100%');
            $("#"+trg2).css('height', '100%');
        } else {
            $("#"+trg).css('height', 'auto');
            $("#"+trg2).css('height', 'auto');
        }
    }



    </script>
</head>
<body>
<form action="detail.php" action="GET">
    <input type="hidden" name="url_id" value="<?php echo $url_id ?>">
    <div id="lockId" style="z-index: 99999; position: absolute; top: 0px; left: 0px; right: 0px; bottom: 0px; opacity: 0.6; background-image: url(&quot;/common/img/loading.gif&quot;); background-attachment: fixed; background-color: black; background-position: 50% 50%; background-repeat: no-repeat;"></div>

    <div class="container">
        <nav class="navbar navbar-default">
            <div class="container-fluid">
                <div class="navbar-header">
                    <a href="/">
                        <img src="/common/img/icon.png" height="45px" alt="">
                    </a>
                </div>
            </div>
        </nav>

        <table width="100%" height="90%" border="1">
            <tr height="20">
                <td colspan="2">&nbsp;</td>
                <td align="center">対象データ1：<input class="datepicker" id="dt1" name="dt0"> <input type="submit" value="日付変更"></td>
                <td align="center">対象データ2：<input class="datepicker" id="dt2" name="dt"> <input type="button" value="日付変更" onclick="document.forms[0].submit();"></td>
                <td width="70" align="center">差異率</td>
            </tr>



            <tr>
                <td align="center" rowspan="2">E<br>l<br>e<br>m<br>e<br>n<br>t<br>s</td>
                <td style="background-color:rgba(<?php echo $COLORS["ELEMENTS"]["PC"] ?>,0.4)" class="tdButton" align="center" onclick="toggleTd('pc_comp_1');"><br><br><br>P<br><br>C<br><br><br></td>
                <td style="background-color:rgba(<?php echo $COLORS["ELEMENTS"]["PC"] ?>,0.4)" width="45%" valign="top"> <div id="pc_comp_1" style="height:100%;overflow: scroll;"><div align="center"><a href="directLink.php?key=<?php echo $file1["PC"]["complete"] ?>"><?php echo basename($file1["PC"]["complete"]) ?></a></div><?php echo $html["PC"]["complete"]["source"] ?></div></td>
                <td style="background-color:rgba(<?php echo $COLORS["ELEMENTS"]["PC"] ?>,0.4)" width="45%" valign="top"> <div id="pc_comp_2" style="height:100%;overflow: scroll;"><div align="center"><a href="directLink.php?key=<?php echo $file2["PC"]["complete"] ?>"><?php echo basename($file2["PC"]["complete"]) ?></a></div><?php echo $html["PC"]["complete"]["change"] ?></div></td>
                <td style="background-color:rgba(<?php echo $COLORS["ELEMENTS"]["PC"] ?>,0.4)" align="center"><?php echo $html["PC"]["complete"]['count']; ?></td>
            </tr>

            <tr>
                <td style="background-color:rgba(<?php echo $COLORS["ELEMENTS"]["SP"] ?>,0.4)" class="tdButton" align="center" onclick="toggleTd('sp_comp_1');"><br><br><br>S<br><br>P<br><br><br></td>
                <td style="background-color:rgba(<?php echo $COLORS["ELEMENTS"]["SP"] ?>,0.4)" width="45%" valign="top"> <div id="sp_comp_1" style="height:100%;overflow: scroll;"><div align="center"><a href="directLink.php?key=<?php echo $file1["SP"]["complete"] ?>"><?php echo basename($file1["SP"]["complete"]) ?></a></div><?php echo $html["SP"]["complete"]["source"] ?></div></td>
                <td style="background-color:rgba(<?php echo $COLORS["ELEMENTS"]["SP"] ?>,0.4)" width="45%" valign="top"> <div id="sp_comp_2" style="height:100%;overflow: scroll;"><div align="center"><a href="directLink.php?key=<?php echo $file2["SP"]["complete"] ?>"><?php echo basename($file2["SP"]["complete"]) ?></a></div><?php echo $html["SP"]["complete"]["change"] ?></div></td>
                <td style="background-color:rgba(<?php echo $COLORS["ELEMENTS"]["SP"] ?>,0.4)" align="center"><?php echo $html["SP"]["complete"]['count']; ?></td>
            </tr>

            <tr>
                <td align="center" rowspan="2">S<br>o<br>u<br>r<br>c<br>e<br>s</td>
                <td style="background-color:rgba(<?php echo $COLORS["SOURCES"]["PC"] ?>,0.4)" class="tdButton" align="center" onclick="toggleTd('pc_simple_1');"><br><br><br>P<br><br>C<br><br><br></td>
                <td style="background-color:rgba(<?php echo $COLORS["SOURCES"]["PC"] ?>,0.4)" width="45%" valign="top"> <div id="pc_simple_1" style="height:100%;overflow: scroll;"><div align="center"><a href="directLink.php?key=<?php echo $file1["PC"]["simple"] ?>"><?php echo basename($file1["PC"]["simple"]) ?></a></div><?php echo $html["PC"]["simple"]["source"] ?></div></td>
                <td style="background-color:rgba(<?php echo $COLORS["SOURCES"]["PC"] ?>,0.4)" width="45%" valign="top"> <div id="pc_simple_2" style="height:100%;overflow: scroll;"><div align="center"><a href="directLink.php?key=<?php echo $file2["PC"]["simple"] ?>"><?php echo basename($file2["PC"]["simple"]) ?></a></div><?php echo $html["PC"]["simple"]["change"] ?></div></td>
                <td style="background-color:rgba(<?php echo $COLORS["SOURCES"]["PC"] ?>,0.4)" align="center"><?php echo $html["PC"]["simple"]['count']; ?></td>
            </tr>

            <tr>
                <td style="background-color:rgba(<?php echo $COLORS["SOURCES"]["SP"] ?>,0.4)" class="tdButton" align="center" onclick="toggleTd('sp_simple_1');"><br><br><br>S<br><br>P<br><br><br></td>
                <td style="background-color:rgba(<?php echo $COLORS["SOURCES"]["SP"] ?>,0.4)" width="45%" valign="top"> <div id="sp_simple_1" style="height:100%;overflow: scroll;"><div align="center"><a href="directLink.php?key=<?php echo $file1["SP"]["simple"] ?>"><?php echo basename($file1["SP"]["simple"]) ?></a></div><?php echo $html["SP"]["simple"]["source"] ?></div></td>
                <td style="background-color:rgba(<?php echo $COLORS["SOURCES"]["SP"] ?>,0.4)" width="45%" valign="top"> <div id="sp_simple_2" style="height:100%;overflow: scroll;"><div align="center"><a href="directLink.php?key=<?php echo $file2["SP"]["simple"] ?>"><?php echo basename($file2["SP"]["simple"]) ?></a></div><?php echo $html["SP"]["simple"]["change"] ?></div></td>
                <td style="background-color:rgba(<?php echo $COLORS["SOURCES"]["SP"] ?>,0.4)" align="center"><?php echo $html["SP"]["simple"]['count']; ?></td>
            </tr>





        </table>

    </div>
</form>
</body>

</html>
