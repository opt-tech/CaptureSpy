<?php
    /**
     * CRON処理でDBから対象URLを取得して来て、HTML取得＆S3アップロードのバッチ処理をキックする
     *
     * Created by PhpStorm.
     * User: ka.kubota
     * Date: 2016/10/07
     * Time: 9:34
     */
    require_once("/var/www/inc/const.php");

    $trgDate = date("Y/m/d");
    $logPublic = new Logger("crawler", "info");
    $logPublic->out("CapturesPy クローリング 開始 {$trgDate}", TRUE);

    $urls = Dao::getRows("urls", "*", "status=1");

    $uas = ["PC","SP"];

    foreach ($urls as $url) {
        //バッチ処理実行
        $sql = "delete from logs where url_id={$url['id']} AND date_at='{$trgDate}';";
        print Dao::sqlExec($sql);

        $htmlGetter = new HtmlGetter($url);

        foreach($uas as $ua) {

            /* file_get_contentsで取得するシンプルHTML
            --------------------------------------------------------------------------------------------*/
            $sdt = date("Y/m/d H:i:s");
            $ret = $htmlGetter->getSimpleHtml($ua);
            $edt = date("Y/m/d H:i:s");

            if ($ret == "") {
                //HTML取得失敗

                $sql = "
                    insert into logs(url_id,result_id,date_at,start_at,end_at,result_status,isfirst,s3path,ua) 
                    values({$url['id']},null,'{$trgDate}','{$sdt}','{$edt}',0,1,null,'{$ua}')";
                print Dao::sqlExec($sql);
            } else {
                $todayHtml = TEMP_DIR . $ret["fileName"];
                $todayS3 = $ret["s3path"] . $ret["fileName"];

                //昨日分のHTMLをダウンロード
                $ret = $htmlGetter->getYesterdayHtml(1,$ua);

                if ($ret == "") {
                    //HTML取得失敗
                    $sql = "
                         insert into logs(url_id,result_id,date_at,start_at,end_at,result_status,isfirst,s3path,ua) 
                         values({$url['id']},null,'{$trgDate}','{$sdt}','{$edt}',0,1,'{$todayS3}','{$ua}')";
                    print Dao::sqlExec($sql);
                } else {
                    $yesterdayHtml = TEMP_DIR . $ret["fileName"];
                    $result = diffFile($url["url"], $todayHtml, $yesterdayHtml);

                    if ($result["status"] == 1) {
                        //成功
                        $sql = "
                            insert into logs(url_id,result_id,date_at,start_at,end_at,result_status,isfirst,s3path,ua) 
                            values({$url['id']},{$result["result_id"]},'{$trgDate}','{$sdt}','{$edt}',1,1,'{$todayS3}','{$ua}')";
                    } else {
                        $sql = "
                            insert into logs(url_id,result_id,date_at,start_at,end_at,result_status,isfirst,s3path,ua) 
                            values({$url['id']},null,'{$trgDate}','{$sdt}','{$edt}',0,1,'{$todayS3}','{$ua}')";
                    }
                    print Dao::sqlExec($sql);
                }
            }


            /* casperJSで取得する完全版のHTML
            --------------------------------------------------------------------------------------------*/
            $sdt = date("Y/m/d H:i:s");
            $ret = $htmlGetter->getCompleteHtml($ua);
            $edt = date("Y/m/d H:i:s");

            if ($ret == "") {
                //HTML取得失敗
                $sql = "
                        insert into logs(url_id,result_id,date_at,start_at,end_at,result_status,isfirst,s3path,ua) 
                        values({$url['id']},null,'{$trgDate}','{$sdt}','{$edt}',0,0,null,'{$ua}')";
                print Dao::sqlExec($sql);
            } else {
                $todayHtml = TEMP_DIR . $ret["fileName"];
                $todayS3 = $ret["s3path"] . $ret["fileName"];

                //昨日分のHTMLをダウンロード
                $ret = $htmlGetter->getYesterdayHtml(0,$ua);
                if ($ret == "") {
                    //HTML取得失敗
                    $sql = "
                            insert into logs(url_id,result_id,date_at,start_at,end_at,result_status,isfirst,s3path,ua) 
                            values({$url['id']},null,'{$trgDate}','{$sdt}','{$edt}',0,0,'{$todayS3}','{$ua}')";
                    print Dao::sqlExec($sql);
                } else {
                    $yesterdayHtml = TEMP_DIR . $ret["fileName"];
                    $result = diffFile($url["url"], $todayHtml, $yesterdayHtml);

                    //var_dump($result);
                    if ($result["status"] == 1) {
                        //成功
                        $sql = "
                            insert into logs(url_id,result_id,date_at,start_at,end_at,result_status,isfirst,s3path,ua) 
                            values({$url['id']},{$result["result_id"]},'{$trgDate}','{$sdt}','{$edt}',1,0,'{$todayS3}','{$ua}')";
                    } else {
                        $sql = "
                            insert into logs(url_id,result_id,date_at,start_at,end_at,result_status,isfirst,s3path,ua) 
                            values({$url['id']},null,'{$trgDate}','{$sdt}','{$edt}',0,0,'{$todayS3}','{$ua}')";
                    }
                    print Dao::sqlExec($sql);
                }
            }
        }
    }

    $logPublic->out("CapturesPy クローリング 終了 ", TRUE);


    /**
     * diff APIを利用してdiffを実施
     *
     * @param $url  対象ファイルのURL
     * @param $file1
     * @param $file2
     * @return mixed
     */
    function diffFile($url, $file1, $file2) {

        if($file1 == "" || $file2 == "") {
            $ret["status"] = 0;
        } else {
            //ここでdiffを取る
            $postfields = [
                "url"   => $url,
                "file1" => new CURLFile($file1),
                "file2" => new CURLFile($file2),
                "leave" => 1,
            ];

            $opts = [
                CURLOPT_RETURNTRANSFER => TRUE,
                CURLOPT_URL            => 'http://localhost/capturespy/api.php',
                CURLOPT_POST           => TRUE,
                CURLOPT_POSTFIELDS     => $postfields,
                CURLOPT_USERAGENT      => 'User-Agent: Mozilla/5.0',
                CURLOPT_HTTPHEADER     => ['Accept-language: ja'],
            ];

            $ch = curl_init();
            curl_setopt_array($ch, $opts);
            $ret = curl_exec($ch);
            curl_close($ch);
        }

        return json_decode($ret, TRUE);
    }
