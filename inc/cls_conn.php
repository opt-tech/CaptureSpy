<?php


/**
 * DB接続用クラス
 *
 * Created by PhpStorm.
 * User: kazuki kubota@castler
 * Date: 2016/07/22
 * Time: 3:21
 *
 * Ver 7.2  2023/03/15  publicメソッドの頭文字を大文字にした
 * Ver 7.1  2018/06/26  エラーログは、isTraceがtrueの時だけ出力するようにした
 * Ver 7.0  2017/10/10  PDOのトランザクションがうまく動作していなかった不具合修正
 * Ver 6.0  2017/06/15  PostgreSQL PDO版実装
 * Ver 5.0  2017/06/12  MySQL PDO版実装
 * Ver 4.0  2017/05/09  logメソッドにスタックトレース追加
 */
class CONN
{

    /* 変数制限
        ---------------------------------------------------------------------*/
    var $server;
    var $port;
    var $username;
    var $password;
    var $database;

    var $myConn;
    var $rows;
    var $error;
    var $errNo;

    var $logger;
    var $isTrace;


    /*******************************************************************
     * 処理 : コンストラクタ
     * 引数 : void
     * 戻り値 : void
     *******************************************************************/
    public function __construct()
    {
        $this->server = DB_SERVER;        // サーバ名
        $this->port = DB_PORT;            // ポート番号
        $this->database = DB_NAME;            // データベース
        $this->username = DB_USER;            // ユーザ名
        $this->password = DB_PASS;            // パスワード

        $this->myConn = 0;                    // 接続ID
        $this->rows = NULL;
        $this->isTrace = false;

        $this->error = "";                    // エラー文字列
        $this->logger = new Logger('sql', 'debug');
    }


    /*******************************************************************
     * 処理 : 接続処理
     * 引数 : void
     * 戻り値 : boolean
     *******************************************************************/
    public function Connect()
    {
        // エラークリア
        $this->error = "";
        $this->errNo = 0;

        // DB接続
        try {
            if (DB_STORE == "mysql") {
                $dsn = DB_STORE . ":host={$this->server};dbname={$this->database};charset=utf8";
                $this->myConn = new PDO($dsn, $this->username, $this->password, [
                    PDO::ATTR_EMULATE_PREPARES         => FALSE,
                    //PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => TRUE,
                    PDO::ATTR_ERRMODE                  => PDO::ERRMODE_EXCEPTION,
                ]);

                $this->myConn->exec("SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;");
                $this->myConn->exec("SET NAMES utf8");
            } else {
                $dsn = DB_STORE . ":dbname={$this->database} host={$this->server} port={$this->port}";
                $this->myConn = new PDO($dsn, $this->username, $this->password, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
            }

            if ($this->isTrace) {
                $this->Log($dsn);
            }

            return 1;
        } catch (PDOException $e) {
            $this->Log("coonect error : " . $e->getMessage());

            return 0;
        }
    }


    /*******************************************************************
     * 処理 : クエリー実行
     * 引数 : $strSql = 実行するSQL
     * 戻り値 : 取得成功時は1レコード目
     *******************************************************************/
    public function Query($sqlString)
    {
        if (!$this->reConnect()) return 0;

        // クエリ発行
        try {
            if ($this->isTrace) {
                $this->Log($sqlString);
            }

            $this->logger->Out($sqlString);
            $this->rows = $this->myConn->Query($sqlString);


            if ($this->rows) {
                $row = $this->rows->fetch(PDO::FETCH_ASSOC);

                return $row;
            } else {
                if ($this->isTrace) {
                    $this->Log("query is null:" . $sqlString);
                    $this->Log(print_r($this, TRUE));
                }

                return 0;
            }
        } catch (PDOException $e) {
            $this->error = "query:" . $e->getMessage() . "::" . $sqlString;
            if ($this->isTrace) {
                $this->Log($this->error);
            }

            return 0;
        }
    }


    /*******************************************************************
     * 処理 : クエリ実行（戻り値なし）
     * 引数 : $strSql = 実行SQL
     * 戻り値 : boolean
     *******************************************************************/
    public function Execute($sqlString)
    {
        if (!$this->reConnect()) return 0;

        // クエリ発行
        try {
            if ($this->isTrace) {
                $this->Log($sqlString);
            }

            //$this->logger->Out($sqlString);
            $this->myConn->Query($sqlString);

            return 1;
        } catch (PDOException $e) {
            $this->error = "execute:" . $e->getMessage() . "::" . $sqlString;
            if ($this->isTrace) {
                $this->Log($this->error);
            }

            return 0;
        }
    }


    /*******************************************************************
     * 処理 : Queryで取得したDatasetの次行を取得
     * 引数 : void
     * 戻り値 : 行データ
     *******************************************************************/
    public function Next()
    {
        if (!$this->reConnect()) return 0;

        // クエリ発行
        try {
            $row = $this->rows->fetch(PDO::FETCH_ASSOC);

            return $row;
        } catch (PDOException $e) {
            $this->rows = NULL;

            return NULL;
        }
    }


    /*******************************************************************
     * 処理 : 最終エラー取得
     * 引数 : void
     * 戻り値 : エラーメッセージ
     *******************************************************************/
    public function GetError()
    {
        return $this->error;
    }


    /*******************************************************************
     * 処理 : コミット
     * 引数 : void
     * 戻り値 : boolean
     *******************************************************************/
    public function Commit()
    {
        if (!$this->reConnect()) return 0;

        try {

            $this->myConn->Commit();
            //return $this->Execute("commit");
        } catch (PDOException $e) {
            $this->error = "execute:" . $e->getMessage() . "::commit";
            $this->Log($this->error);

            return 0;
        }
    }


    /*******************************************************************
     * 処理 : トランザクション開始
     * 引数 : void
     * 戻り値 : boolean
     *******************************************************************/
    public function BeginTrans()
    {
        if (!$this->reConnect()) return 0;

        try {
            $this->myConn->beginTransaction();
        } catch (PDOException $e) {
            $this->error = "execute:" . $e->getMessage() . "::begin";
            $this->Log($this->error);

            return 0;
        }

        //return $this->Execute("begin");
    }


    /*******************************************************************
     * 処理 : ロールバック
     * 引数 : void
     * 戻り値 : boolean
     *******************************************************************/
    public function Rollback()
    {
        if (!$this->reConnect()) return 0;

        try {
            $this->myConn->Rollback();
        } catch (PDOException $e) {
            $this->error = "execute:" . $e->getMessage() . "::rollback";
            $this->Log($this->error);

            return 0;
        }
    }


    /*******************************************************************
     * 処理 : 再接続処理
     * 引数 : void
     * 戻り値 : boolean
     *******************************************************************/
    private function reConnect()
    {
        if (!$this->myConn) {
            return $this->Connect();
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
    public function GetSeqLastValue($strSeq, $fld)
    {
        if (!$this->reConnect()) return 0;

        $sql = "select max({$fld}) as mx from {$strSeq}";
        $row = $this->Query($sql);

        return $row["mx"];
    }


    /*******************************************************************
     * 処理 : ログ出力
     * 引数 : 本文
     * 戻り値 : void
     *******************************************************************/
    private function Log($msg)
    {
        //$dbg = debug_backtrace();
        $trace = var_export(debug_backtrace($limit = 2), TRUE);
        $this->logger->Out($msg . "\n" . print_r($trace, TRUE));
    }


    /*******************************************************************
     * 処理 : デストラクタ
     * 引数 :
     * 戻り値 : void
     *******************************************************************/
    function __destruct()
    {
        $this->myConn = NULL;
        $this->rows = NULL;
    }
}
