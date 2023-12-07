<?php

/**
 * Created by PhpStorm.
 * User: ka.kubota
 * Date: 2016/10/13
 * Time: 10:58
 */

require_once("../inc/const.php");

/* 初期処理
    ---------------------------------------------------------------------*/
$param = Form::getParam($_POST, $_GET);

$trgDate = date("Y/m/d");
$range = Util::nz(@$param["range"], 30);
$dt = Util::DateAdd($trgDate, "d", $range * -1);
$sdt = date("Y/m/d", $dt["unixtime"]);

/* URLの取得
    ---------------------------------------------------------------------*/
$urls = new Urls();

if (@$param["url"] == "") $param["url"] = Dao::dlookup("urls", "min(id) as val", "status=1");;

$urls->setPulldownList(false, $param["url"], "status=1");
$args["url"] = $urls->listParam["pullDown"];


/* グラフデータの取得
    ---------------------------------------------------------------------*/
//print "select logs.date_at, logs.isfirst, logs.ua, results.total, results.diffs from logs left join results on logs.result_id=results.id where logs.url_id={$param["url"]} AND logs.date_at between '{$sdt} 00:00:00' AND '{$trgDate} 23:59:59'";

$rows = Dao::getRows("logs left join results on logs.result_id=results.id", "logs.date_at, logs.isfirst, logs.ua, results.total, results.diffs", "logs.url_id={$param["url"]} AND logs.date_at between '{$sdt} 00:00:00' AND '{$trgDate} 23:59:59'");
//扱いやすいように連想配列にしておく
foreach ($rows as $row) {
    try {
        if ($row["diffs"] > 0) $data[$row["date_at"]][$row["ua"]][$row["isfirst"]] = round($row["diffs"] / $row["total"] * 100, 2);
    } catch (Exception $e) {
        $data[$row["date_at"]][$row["ua"]][$row["isfirst"]] = "";
    }
}

/* 日付ラベルとグラフデータの作成
    ---------------------------------------------------------------------*/
for ($i = 0; $i <= $range; $i++) {
    $dt = Util::DateAdd($trgDate, "d", ($range - $i) * -1);

    $ymd = date("Y-m-d", $dt["unixtime"]);
    $dt = date("m/d", $dt["unixtime"]);


    $row = @$data[$ymd];

    $simplePC[] = $row["PC"][1];
    $compPC[] = $row["PC"][0];
    $simpleSP[] = $row["SP"][1];
    $compSP[] = $row["SP"][0];

    $dates[] = '"' . $dt . '"';
}

$args["graph"]["label"] = implode(",", $dates);
$args["graph"]["pc"]["simple"] = implode(",", $simplePC);
$args["graph"]["pc"]["complete"] = implode(",", $compPC);
$args["graph"]["sp"]["simple"] = implode(",", $simpleSP);
$args["graph"]["sp"]["complete"] = implode(",", $compSP);

$args["range"] = Form::createComboKey($GRAPH_RANGE, $range, false);

$args["COLORS"] = $COLORS;
$args["about"] = file_get_contents(TMPL_DIR . "about.txt");

htmltemplate::t_include(TMPL_DIR . util::getThisFileName() . ".tpl", $args);
