<?php


    /**
     * Created by PhpStorm.
     * User: ka.kubota
     * Date: 2016/10/07
     * Time: 9:51a
     *
     */
    class htmlGetter {
        private $param;
        private $ymd;
        private $logger;
        private $s3;


        /**
         * htmlGetter constructor.
         */
        public function __construct($row) {
            global $AWS_CONFIG;
            $this->param = $row;
            $this->ymd = date("Ymd");

            $this->s3 = new S3manager($AWS_CONFIG, "{$this->param['ext']}/" . date("Ym") . "/");

            $this->logger = new Logger("htmlgetter_" . $this->param["ext"] . "-" . $this->param["id"], "debug");
            $this->logger->out(print_r($row, TRUE));

            system("rm " . TEMP_DIR . "*");
        }


        public function getSimpleHtml($uaText) {
            global $UA;
            //file_get_contentした結果をtempディレクトリに保存する

            $fileName = $this->ymd . "_" . $this->param["ext"] . "-" . $this->param["id"] . "_simple({$uaText}).html";
            $this->logger->out("[getSimpleHtml]テンポラリファイル：" . TEMP_DIR . "{$fileName}", TRUE);

            $options = [
                'http' => [
                    'method' => 'GET',
                    'header' => 'User-Agent: ' . $UA[ $uaText ],
                ],
            ];

            $context = stream_context_create($options);

            $buf = file_get_contents($this->param["url"], FALSE, $context);

            if ($buf == "") {
                $this->logger->out($this->param["url"] . " からsimpleHTMLのダウンロードに失敗しました");

                return "";
            }
            file_put_contents(TEMP_DIR . $fileName, $buf);

            $this->s3->setFileName($fileName);
            $ret = $this->s3->upload();
            if ($ret != "") {
                $this->logger->out("当日のsimpleHTMLがS3へアップロードできませんでした（" . $this->s3->getRemoteDir() . $fileName . "）");

                return "";
            } else {
                $this->logger->out("当日のsimpleHTMLをS3へアップロードしました（" . $this->s3->getRemoteDir() . $fileName . "）");
                $ret = [
                    "s3path"   => $this->s3->getRemoteDir(),
                    "fileName" => $fileName,
                ];

                return $ret;
            }
        }


        public function getCompleteHtml($uaText) {
            global $UA;

            $fileName = $this->ymd . "_" . $this->param["ext"] . "-" . $this->param["id"] . "_complete({$uaText}).html";
            $this->logger->out("[getSimpleHtml]テンポラリファイル：" . TEMP_DIR . "{$fileName}", TRUE);
 

            for ($i=1;$i<100;$i++) { //50回リトライ
                $casper = new CasperEx();
                $casper->setUserAgent($UA[ $uaText ]);

                $casper->setUrl($this->param["url"]);

                $casper->waitForSelector("html", 60000);

                $casper->run();

                sleep(60);
                $buf = $casper->getHTML();

                if ($buf == "") {
                    $this->logger->out($this->param["url"] . " から完全版HTMLのダウンロードに失敗しました");

                    return "";
                }

                preg_match("/<title>(.*?)<\/title>/i", $buf, $matches);
                $title = $matches[1];
                if($title != "Google") break;
            }


            file_put_contents(TEMP_DIR . $fileName, $buf);

            $this->s3->setFileName($fileName);
            $ret = $this->s3->upload();
            if ($ret != "") {
                $this->logger->out("当日の完全版HTMLがS3へアップロードできませんでした（" . $this->s3->getRemoteDir() . $fileName . "）");

                return "";
            } else {
                $this->logger->out("当日の完全版HTMLをS3へアップロードしました（" . $this->s3->getRemoteDir() . $fileName . "）");
                $ret = [
                    "s3path"   => $this->s3->getRemoteDir(),
                    "fileName" => $fileName,
                ];

                return $ret;
            }

        }


        public function getYesterdayHtml($isFirst, $ua) {
            //直近のS3ファイルパスを取得する

            $remote = Dao::getRows("logs", "s3path", "ua='{$ua}' AND isfirst={$isFirst} AND url_id={$this->param["id"]} AND date_at<'" . date("Y-m-d") . "' AND s3path != ''", "date_at desc limit 1");
            if ($remote == "") {
                $this->logger->out("前回のHTMLがS3に存在しませんでした（" . print_r($remote, TRUE) . "）");

                return "";

            } else {
                $remote = $remote[0]["s3path"];
                $this->logger->out("lastS3Path：{$remote}", TRUE);

                $buf = explode("/", $remote);
                $fileName = $buf[ count($buf) - 1 ];
                $local = TEMP_DIR . $fileName;

                $ret = $this->s3->downloadDirect($remote, $local);

                if ($ret != "") {
                    $this->logger->out("前回のHTMLをS3からダウンロードできませんでした（{$remote}）" . $ret);

                    return "";
                } else {
                    $this->logger->out("前回のHTMLをS3からダウンロードしました（{$local}）");
                    $ret = [
                        "s3path"   => str_replace($fileName, "", $remote),
                        "fileName" => $fileName,
                    ];

                    return $ret;
                }
            }
        }
    }