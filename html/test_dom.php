<?php
    /**
     * Created by PhpStorm.
     * User: ka.kubota
     * Date: 2016/10/04
     * Time: 15:48
     */
    require_once("../inc/const.php");

    $buf = file_get_contents("/var/www/inc/diff_files/2016-10-19/0kBSvgzbMe4OmNp40i1coiMxtWI4V69w/2_20161018_google.co.jp-1_complete(PC).html");

    $buf = Diff::extractTagBorn($buf);

    echo $buf;