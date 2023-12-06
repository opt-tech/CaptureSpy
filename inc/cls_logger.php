<?php


    /**
     * Logヘルパークラス
     *  即時コール　(new Logger('debug', 'info'))->Out("テストログ");
     * User: kazuki kubota@castler
     * Date: 2016/07/22
     * Time: 3:21
     *
     * Ver 1.0  2014/06/22  新規作成
     * Ver 2.0  2016/09/07  ログローテート機能追加
     * Ver 2.1  2016/09/07  outメソッドの画面出力内容の変更
     */
    class Logger {
        var     $sysID;
        var     $kind;
        private $logSaveMonth = 2;  //ログの保存期間（月）。これ以上のログは逐次圧縮されていく


        /*******************************************************************
         * 処理 : コンストラクタ
         * 引数 : $sysID = ファイル名に付与される識別子
         * $kind = ログファイル内に出力されるログタイプ
         * 戻り値 : HTML文
         *******************************************************************/
        public function Logger($sysID, $kind) {
            $this->sysID = $sysID;
            $this->kind = $kind;
        }


        /*******************************************************************
         * 処理 : ログ出力
         * 引数 : $msg ログ出力する文字列
         *       $isPrint 画面に表示する場合はTRUE
         *       $isHtml 画面に表示する場合のHTMLモード
         * 戻り値 : void
         *******************************************************************/
        public function out($msg, $isPrint = FALSE, $isHtml = FALSE) {
            if (DEBUG_MODE != 1) return;

            if ($this->sysID == "") $this->sysID = "error";

            $msg = date("Y-m-d(D) H:i:s") . " {$this->kind} " . $msg;

            if ($isPrint) print $msg.($isHtml ? "<br>" : "")."\n";

            $fName = LOG_DIR . date("Ymd") . "_$this->sysID.log";
            $fp = fopen($fName, "a");
            fputs($fp, $msg . "\r\n");
            fclose($fp);

            @chmod($fName, 0777);

            //ログローテート処理
            $this->rotate();
        }


        /**
         * ログローテート
         */
        private function rotate() {
            //アーカイブファイル
            $archiveFileName = LOG_DIR . "archive_" . date("Ym", Util::DateAdd(date("Ymd000000"), "m", ($this->logSaveMonth * -1))["unixtime"]) . ".zip";

            //アーカイブファイルが存在する場合は処理を中止（アーカイブ済みの為）
            if (file_exists($archiveFileName)) return;

            //対象ファイル名
            $trgFileName = LOG_DIR . date("Ym", Util::DateAdd(date("Ymd000000"), "m", ($this->logSaveMonth * -1))["unixtime"]) . "*.log";
            if (!empty(glob($trgFileName))) exec("zip -jrm {$archiveFileName} {$trgFileName} > /dev/null &");
        }
    }
