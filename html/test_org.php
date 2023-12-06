<?php
    /**
     * Created by PhpStorm.
     * User: ka.kubota
     * Date: 2016/10/04
     * Time: 15:48
     */

    //$url = "https://www.google.co.jp/webhp?sourceid=chrome-instant&ion=1&espv=2&ie=UTF-8#q=%E3%83%86%E3%82%B9%E3%83%88";
    $url = "http://qiita.com/mpyw/items/58c7aa797d7735469e3a";
    $html = file_get_contents($url);

    $domDocument = new DOMDocument();
    $domDocument->loadHTML($html);
    $xmlString = $domDocument->saveXML();
    $source1 = simplexml_load_string($xmlString);
    $source1 = json_encode($source1);
    $source1 = json_decode($source1, TRUE);


    $url = "http://qiita.com/mpyw/items/58c7aa797d7735469e3a";
    $html = file_get_contents($url);

    $domDocument = new DOMDocument();
    $domDocument->loadHTML($html);
    $xmlString = $domDocument->saveXML();
    $source2 = simplexml_load_string($xmlString);
    $source2 = json_encode($source2);
    $source2 = json_decode($source2, TRUE);


    $ret = ["totalCount" => 0];
    $ret = diffSimpleParse($source1, $source2, $ret);
    $ret["diffCount"] = count($ret["diff"]);

    //print_r($source1);
    //print_r($ret);
    //print_r($source2);

    function diffSimpleParse($data1, $data2, $ret, $keyHistory = ""){
        foreach($data1 as $key => $val){
            if(is_array($val)){
                //$ret = diffSimpleParse($val, $data2[$key], $ret, $keyHistory.$key."->");
                $ret = diffSimpleParse($val, $data2, $ret, $keyHistory.$key."->");
            } else {
                if($key != "content" && $key != "textarea" ){
                    //コンテンツは変わるので無視

                    $keis = explode("->", $keyHistory);

                    //print "keis=".print_r($keis)."¥n¥n<br><br>";

                    $val2 = $data2;
                    $val2Keis = "";
                    foreach ($keis as $v) {
                        if ($v !== "") {
                            $val2Keis .= $v . "->";
                            $val2 = $val2[ $v ];
                        }
                    }
                    $val2Keis .= $key;
                    $val2 = $val2[ $key ];

                    $ret["totalCount"]++;
                    if ($val != $val2) {
                        $diff = [
                            "key"  => $keyHistory . $key,
                            "val1" => $val,
                            "key2" => $val2Keis,
                            "val2" => $val2,
                        ];
                        $ret["diff"][] = $diff;
                    }
                }
            }
        }

        return $ret;
    }

