<?php
    /**
     * Created by PhpStorm.
     * User: ka.kubota
     * Date: 2016/10/11
     * Time: 14:36
     */
    require_once("../../inc/const.php");

    $token = $_GET["t"];
    $idx = $_GET["i"];
    $param = Dao::dlookupMulti("results", "*", "token='$token'");

    if ($param == "") {
        die("指定された差分結果情報が存在しませんでした");
        exit;
    }

    $ymd = date("Y-m-d", strtotime($param["date_at"]));

    $dir = DIFF_HISTORY_FILES_DIR . "{$ymd}/{$token}/*";
    $files = glob($dir);

    $path_file = $files[$idx];

    /* ファイルの存在確認 */
    if (!file_exists($path_file)) {
        die("Error: File(".$path_file.") does not exist");
    }

    /* オープンできるか確認 */
    if (!($fp = fopen($path_file, "r"))) {
        die("Error: Cannot open the file(".$path_file.")");
    }
    fclose($fp);

    /* ファイルサイズの確認 */
    if (($content_length = filesize($path_file)) == 0) {
        die("Error: File size is 0.(".$path_file.")");
    }

    /* ダウンロード用のHTTPヘッダ送信 */
    $name = basename($path_file);
    $name = substr($name, 2,strlen(basename($path_file)) - 2);
    header("Content-Disposition: inline; filename=\"".$name."\"");
    header("Content-Length: ".$content_length);
    header("Content-Type: application/octet-stream");

    /* ファイルを読んで出力 */
    if (!readfile($path_file)) {
        die("Cannot read the file(".$path_file.")");
    }