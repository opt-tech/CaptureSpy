<?php
    /**
     * Created by PhpStorm.
     * User: ka.kubota
     * Date: 2016/10/21
     * Time: 10:57
     */
    require_once("../inc/const.php");
    $s3 = new S3manager($AWS_CONFIG);

    $key = $_GET["key"];
    $s3->DirectDownload($key);