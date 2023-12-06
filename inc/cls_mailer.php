<?php
    //============================================================
    //	Mailヘルパークラス
    //	メール関連
    //------------------------------------------------------------
    //	変更履歴
    //
    //		2016/06/07	K.KUBOTA
    //		・新規作成
    //============================================================
    class Mailer {
        var $from;
        var $to;
        var $bcc;
        var $title;


        /*******************************************************************
         * 処理 : コンストラクタ
         * 引数 : $from = 差出人アドレス
         * $to = 送り先アドレス
         * $bcc = Bccアドレス
         * $title = メールタイトル
         * 戻り値 : void
         *******************************************************************/
        public function Mailer($from, $to, $bcc, $title) {
            $this->from = $from;
            $this->to = $to;
            $this->bcc = $bcc;
            $this->title = $title;
        }


        /*******************************************************************
         * 処理 : メール送信処理
         * 引数 : $body = 送信本文
         * 戻り値 : mb_send_mail関数の実行結果（実際のメール送信結果ではない）
         *******************************************************************/
        public function send($body, $isHTML = FALSE) {
            mb_language("Japanese");
            mb_internal_encoding("UTF-8");

            $addHeader = "From: {$this->from}\n";
            if ($this->bcc != NULL) $addHeader .= "Bcc: {$this->bcc}\n";
            if ($isHTML) $addHeader .= "Content-Type: text/html;";
            $addHeader .= "X-Mailer: PHP/" . phpversion() . "\n";

            //(new Logger('mail', 'debug'))->out("{$this->from} => {$this->to} 　件名：{$this->title}");

            return mb_send_mail($this->to, $this->title, $body, $addHeader);
        }
    }