<?php
    /**
     * Created by PhpStorm.
     * User: ka.kubota
     * Date: 2016/10/13
     * Time: 10:58
     */
    require_once("../inc/const.php");

    $id = 0;
    for($i=0;$i<1000;$i++){
        $dt = date("Y-m-d",strtotime("-{$i} day"));

        $id++;
        print Dao::sqlExec( "insert into results(id,total,diffs,url,date_at,token) values({$id},100, (CEIL(RAND() * 6) -1), 'https://www.google.co.jp/webhp?sourceid=chrome-instant&ion=1&espv=2&ie=UTF-8#q=%E3%83%86%E3%82%B9%E3%83%88','{$dt} 04:00:00',SUBSTRING(MD5(RAND()), 1, 32));" );
        print Dao::sqlExec( "insert into logs(url_id,result_id,date_at,result_status,isfirst,ua) values(1, {$id}, '{$dt}',1,1,'PC');" );
        $id++;
        print Dao::sqlExec( "insert into results(id,total,diffs,url,date_at,token) values({$id},100, (CEIL(RAND() * 6) -1), 'https://www.google.co.jp/webhp?sourceid=chrome-instant&ion=1&espv=2&ie=UTF-8#q=%E3%83%86%E3%82%B9%E3%83%88','{$dt} 04:00:00',SUBSTRING(MD5(RAND()), 1, 32));" );
        print Dao::sqlExec( "insert into logs(url_id,result_id,date_at,result_status,isfirst,ua) values(1, {$id}, '{$dt}',1,1,'SP');" );

        $id++;
        print Dao::sqlExec( "insert into results(id,total,diffs,url,date_at,token) values({$id},100, (CEIL(RAND() * 6) -1), 'https://www.google.co.jp/webhp?sourceid=chrome-instant&ion=1&espv=2&ie=UTF-8#q=%E3%83%86%E3%82%B9%E3%83%88','{$dt} 04:00:00',SUBSTRING(MD5(RAND()), 1, 32));" );
        print Dao::sqlExec( "insert into logs(url_id,result_id,date_at,result_status,isfirst,ua) values(1, {$id}, '{$dt}',1,0,'PC');" );

        $id++;
        print Dao::sqlExec( "insert into results(id,total,diffs,url,date_at,token) values({$id},100, (CEIL(RAND() * 6) -1), 'https://www.google.co.jp/webhp?sourceid=chrome-instant&ion=1&espv=2&ie=UTF-8#q=%E3%83%86%E3%82%B9%E3%83%88','{$dt} 04:00:00',SUBSTRING(MD5(RAND()), 1, 32));" );
        print Dao::sqlExec( "insert into logs(url_id,result_id,date_at,result_status,isfirst,ua) values(1, {$id}, '{$dt}',1,0,'SP');" );

    }