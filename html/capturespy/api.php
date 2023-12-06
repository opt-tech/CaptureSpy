<?php
/**
 * Created by PhpStorm.
 * User: ka.kubota
 * Date: 2016/10/07
 * Time: 15:38
 */
require_once("../../inc/const.php");

$url = $_POST["url"];
$leave = (isset($_POST["leave"]) ? $_POST["leave"] : 0);

if ($leave == 1) {
    $token = Util::createRandomWithLength(32);
    $ymd = date("Y-m-d");
    $dir = DIFF_HISTORY_FILES_DIR . "{$ymd}/";
    @mkdir($dir, 0777, TRUE);
    @chmod($dir, 0777);
    $dir = DIFF_HISTORY_FILES_DIR . "{$ymd}/{$token}/";
    @mkdir($dir, 0777, TRUE);
    @chmod($dir, 0777);
}


if (is_uploaded_file($_FILES["file1"]["tmp_name"])) {
    if ($leave != 1) {
        $file1 = $_FILES["file1"]["tmp_name"];
    } else {
        $file1 = $dir . "1_" . $_FILES["file1"]["name"];

        if (move_uploaded_file($_FILES["file1"]["tmp_name"], $file1)) {
            @chmod($file1, 0777);
        }
    }
}


if (is_uploaded_file($_FILES["file2"]["tmp_name"])) {
    if ($leave != 1) {
        $file2 = $_FILES["file2"]["tmp_name"];
    } else {

        $file2 = $dir . "2_" . $_FILES["file2"]["name"];
        if (move_uploaded_file($_FILES["file2"]["tmp_name"], $file2)) {
            chmod($file2, 0777);
        }

    }
}

if (!file_exists($file1) || !file_exists($file2)) {
    $ret["status"] = 0;
    $ret["error"] = "APIへファイルの送信に失敗しました";
} else {
    //Diff
    $diff = new Diff(file_get_contents($file1), file_get_contents($file2));

    $ret = $diff->exec();
    $ret["status"] = 1;

    //(new Logger("diff2","debug"))->out(
    //  print_r($diff->diff,true)
    //);

    //結果をDBに保存
    if ($leave == 1) {

        $dbs = new CONN();

        $sql = "insert into results(total,diffs,url,date_at,token) 
                values({$ret['totalCount']},{$ret['diffCount']},'{$url}','" . date("Y-m-d H:i:s") . "','{$token}')";
        $dbs->execute($sql);

        $ret["result_id"] = $dbs->getSeqLastValue("results", "id");
        $ret["diffUrl"] = BASE_URL . "capturespy/view.php?t=" . $token;
    }

    $ret["ratio"] = $diff->getDiffRatio();

    //(new Logger('API', 'info'))->Out(print_r($ret,true));
}

$json = json_encode($ret);

print $json;