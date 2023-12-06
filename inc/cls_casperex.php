<?php


    /**
     * CasperEx
     *
     * php-Casperのラッパークラス
     *
     * Created by PhpStorm.
     * User: ka.kubota
     * Date: 2016/08/04
     * Time: 10:20
     */
    require_once(LIB_DIR . "Browser/Casper.php");

    use Browser\Casper;


    class CasperEx extends Casper {
        private   $url;
        private   $captureCount = 1;
        protected $config;


        /**
         * @return mixed
         */
        public function getUrl() {
            return $this->url;
        }


        /**
         * @param mixed $url
         */
        public function setUrl($url) {
            $this->url = $url;
            $this->start($url);
        }


        /**
         * CasperEx constructor.
         *
         * @param array[] $CRAWLER_CONFIG
         */
        public function __construct() {
            $this->setOptions([
                                  'ignore-ssl-errors' => 'yes',
                                  'ssl-protocol'      => 'any',
                              ]);
        }


        /**
         * 簡易キャプチャー
         *
         * @param int    $top    上位置
         * @param int    $left   左位置
         * @param int    $width  　幅
         * @param int    $height 高さ
         * @param string $name   キャプチャするファイルパス
         */
        public function quickCapture($top = 0, $left = 0, $width = 1024, $height = 1024, $name = "/tmp/capture") {
            $this->capture([
                               'top'    => 0,
                               'left'   => 0,
                               'width'  => 1024,
                               'height' => 1024,
                           ], "{$name}_{$this->captureCount}.png");

            $this->captureCount++;
        }


        /**
         * waitForSelectorした後に指定した秒数waitする
         *
         * @param     $selectorString
         * @param int $afterDelay
         * @param int $timeout
         */
        public function waitSelectorAndDelay($selectorString, $afterDelay = 0, $timeout = 5000) {
            $this->waitForSelector($selectorString, $timeout);
            $this->wait($afterDelay);
        }


        /**
         * waitForTextした後に指定した秒数waitする
         *
         * @param     $selectorString
         * @param int $afterDelay
         * @param int $timeout
         */
        public function waitTextAndDelay($selectorString, $afterDelay = 0, $timeout = 5000) {
            $this->waitForText($selectorString, $timeout);
            $this->wait($afterDelay);
        }


        /**
         * URLを直接指定で遷移する
         *
         * @param $url
         */
        public function moveTo($url) {
            $this->evaluate("window.location.href='{$url}';");
        }

    }