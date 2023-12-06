<?php
    /**
     * Created by PhpStorm.
     * User: ka.kubota
     * Date: 2016/10/04
     * Time: 15:48
     */
    require_once("../inc/const.php");

    global $UA;

    $uaText = "PC" ;

    $fileName = "_complete({$uaText}).html";

    $casper = new CasperEx();
    $casper->setUserAgent($UA[ $uaText ]);

    $casper->setUrl("https://www.google.co.jp/webhp?sourceid=chrome-instant&ion=1&espv=2&ie=UTF-8#q=%E8%B3%83%E8%B2%B8%20%E6%9D%B1%E4%BA%AC&uc=".time());

    $casper->waitForSelector("html", 500);

    $casper->run();


    $buf = $casper->getHTML();

    file_put_contents(TEMP_DIR . $fileName, $buf);

    $log = $casper->getOutput();

    preg_match( "/<title>(.*?)<\/title>/i", $buf, $matches);
    print "title = ". $matches[1];

    //print_r( $log );

