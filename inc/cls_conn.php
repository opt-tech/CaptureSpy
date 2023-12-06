<?php
    //============================================================
    //	DB接続用クラス
    //	MySQL用
    //------------------------------------------------------------
    //	変更履歴
    //
    //		2016/06/07	K.KUBOTA
    //		・新規作成
    //============================================================
    class CONN {

        /* 変数制限
        ---------------------------------------------------------------------*/
        var $Server;
        var $Port;
        var $Username;
        var $Password;
        var $Database;

        var $MyConn;
        var $MyRow;
        var $MyCurrentRow;
        var $Error;
        var $errNo;

        var $varsion = "1.0";

        var $logger;


        /*******************************************************************
         * 処理 : コンストラクタ
         * 引数 : void
         * 戻り値 : void
         *******************************************************************/
        public function CONN() {
            $this->Server = DB_SERVER;        // サーバ名
            $this->Port = DB_PORT;            // ポート番号
            $this->Database = DB_NAME;            // データベース
            $this->Username = DB_USER;            // ユーザ名
            $this->Password = DB_PASS;            // パスワード

            $this->MyConn = 0;                    // 接続ID
            $this->MyRow = 0;                        // 現在の抽出行
            $this->MyCurrentRow = 0;                // 現在の対象行

            $this->Error = "";                    // エラー文字列
            $this->logger = new Logger('sql', 'debug');
        }


        /*******************************************************************
         * 処理 : 接続処理
         * 引数 : void
         * 戻り値 : boolean
         *******************************************************************/
        public function connect() {
            // エラークリア
            $this->Error = "";
            $this->ErrNo = 0;

            // DB接続
            if (!$this->MyConn = @mysql_connect($this->Server, $this->Username, $this->Password)) { // 接続
                $this->Error = "connect:" . mysql_error();
                $this->logger->out($this->Error);

                return 0;
            }

            // DB選択
            if (!mysql_select_db($this->Database)) { //
                $this->Error = "connect:" . mysql_error();

                return 0;
            }

            $this->execute("SET NAMES utf8");

            return 1;
        }


        /*******************************************************************
         * 処理 : クエリー実行
         * 引数 : $strSql = 実行するSQL
         * 戻り値 : 取得成功時は1レコード目
         *******************************************************************/
        public function query($sqlString) {
            if (!$this->reConnect()) return 0;

            // クエリ発行
            $this->MyCurrentRow = 0;
            if(DEBUG_MODE == 1) $this->logger->out($sqlString);
            if (!$this->MyRow = @mysql_query($sqlString)) { // クエリ発行
                $this->Error = "query:" . mysql_error() . "::" . $sqlString;

                $this->logger->out($this->Error);

                return 0;
            }
            // 残り行数と比較
            if (mysql_num_rows($this->MyRow) <= $this->MyCurrentRow) {
                //$this->Error="query:End of rows.";
                $this->MyCurrentRow = 0;

                return 0;
            }
            // 結果取得
            if (!$row = @mysql_fetch_array($this->MyRow)) {
                $this->Error = "query:" . mysql_error();

                logPrint("sql", "error", $this->Error);

                return 0;
            }
            $this->MyCurrentRow++;

            return $row;
        }


        /*******************************************************************
         * 処理 : クエリ実行（戻り値なし）
         * 引数 : $strSql = 実行SQL
         * 戻り値 : boolean
         *******************************************************************/
        public function execute($sqlString) {
            if (!$this->reConnect()) return 0;

            // クエリ発行
            if(DEBUG_MODE == 1) $this->logger->out($sqlString);
            if (!$this->MyRow = @mysql_query($sqlString)) { // クエリ発行
                $this->Error = "execute:" . mysql_error() . "::" . $sqlString;

                $this->logger->out($this->Error);

                return 0;
            }

            return 1;
        }


        /*******************************************************************
         * 処理 : Queryで取得したDatasetの次行を取得
         * 引数 : void
         * 戻り値 : 行データ
         *******************************************************************/
        public function next() {
            if (!$this->reConnect()) return 0;

            // 残り行数と比較
            if (mysql_num_rows($this->MyRow) <= $this->MyCurrentRow) {
                $this->Error = "next:End of rows.";
                $this->MyCurrentRow = 0;

                return 0;
            }

            // 次行取得
            if (!$row = @mysql_fetch_array($this->MyRow)) {        // 結果取得
                $this->Error = "next:" . mysql_error();
                $this->logger->out($this->Error);
                $this->MyCurrentRow = 0;

                return 0;
            }
            $this->MyCurrentRow++;

            return $row;
        }


        /*******************************************************************
         * 処理 : メモリ解放
         * 引数 : void
         * 戻り値 : boolean
         *******************************************************************/
        public function free() {
            if (!$this->reConnect()) return 0;

            // 結果行メモリ開放
            mysql_free_result($this->MyRow);
            $this->MyCurrentRow = 0;

            return 0;
        }


        /*******************************************************************
         * 処理 : 最終エラー取得
         * 引数 : void
         * 戻り値 : エラーメッセージ
         *******************************************************************/
        public function getError() {
            return $this->Error;
        }


        /*******************************************************************
         * 処理 : コミット
         * 引数 : void
         * 戻り値 : boolean
         *******************************************************************/
        public function commit() {
            return $this->execute("commit");
        }


        /*******************************************************************
         * 処理 : トランザクション開始
         * 引数 : void
         * 戻り値 : boolean
         *******************************************************************/
        public function beginTrans() {
            return $this->execute("begin");
        }


        /*******************************************************************
         * 処理 : ロールバック
         * 引数 : void
         * 戻り値 : boolean
         *******************************************************************/
        public function rollback() {
            return $this->execute("rollback");
        }


        /*******************************************************************
         * 処理 : 再接続処理
         * 引数 : void
         * 戻り値 : boolean
         *******************************************************************/
        private function reConnect() {
            if (!$this->MyConn) {
                return $this->connect();
            } else {
                return TRUE;
            }
        }


        /*******************************************************************
         * 処理 : シーケンス最終配布番号取得
         * 引数 : $strSeq = シーケンステーブル名
         * $fld = フィールド名
         * 戻り値 : 現在のシーケンス値
         *******************************************************************/
        public function getSeqLastValue($strSeq, $fld) {
            if (!$this->reConnect()) return 0;

            $sql = "select max({$fld}) as max from {$strSeq}";
            $row = $this->query($sql);

            return $row["max"];
        }
    }