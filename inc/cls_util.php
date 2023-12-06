<?php


    /**
     * ユーティリティヘルパークラス
     *
     * Created by PhpStorm.
     * User: kazuki kubota@castler
     * Date: 2016/07/22
     * Time: 3:21
     *
     * Ver 4.0  2016/07/22  csvParse2　を追加
     */
    class Util {

        /*******************************************************************
         * 処理 : 日付のフォーマットを変更
         * 引数 : $date = 対象日付文字列（yyyy/mm/ddを想定）
         * $delimiter = デリミタ。空文字の場合はyyyymmddになる
         * 戻り値 : フォーマット済み文字列
         *******************************************************************/
        public static function dateFormat($date, $delimiter = "") {
            $y = substr($date, 0, 4);
            $m = substr($date, 5, 2);
            $d = substr($date, 8, 2);

            if ($y != "" && $m != "" && $d != "") {
                if ($delimiter == "") {
                    return "{$y}年{$m}月{$d}日";
                } else {
                    return $y . $delimiter . $m . $delimiter . $d;
                }
            } else {
                return $date;
            }
        }


        /**
         * hh:ii:ssをhh:ii形式に
         *
         * @param $time
         * @return mixed|string
         */
        public static function timeFormat($time) {
            $time = substr($time, 0, 5);
            $time = str_replace("00:00", "", $time);

            return $time;
        }


        /*******************************************************************
         * 処理 : yyyy/mm/dd hh:ii にして戻す
         * 引数 : $date = 対象日付文字列（yyyy/mm/dd hh:ii:ss+9:00 などを想定）
         * 戻り値 : フォーマット済み文字列
         *******************************************************************/
        public static function dateFormatFull($date) {
            return substr($date, 0, 16);
        }


        /*******************************************************************
         * 処理 : SQLサニタイジング処理
         * 引数 : $val 対象データ
         * 戻り値 : サニタイジングみデータ
         *******************************************************************/
        public static function check_sql($val) {
            return stripslashes(addslashes($val));
        }


        /*******************************************************************
         * 処理 : 対象年月の最終日取得
         * 引数 : $y = 年
         * $m = 月
         * 戻り値 : 最終日
         *******************************************************************/
        public static function LastDate($y, $m) {
            $m++;
            if ($m == 13) {
                $y++;
                $m = 1;
            }
            $t = strtotime("{$y}/{$m}/1 00:01") - 86400;

            return date("d", $t);
        }


        /*******************************************************************
         * 処理 : 改行コード除去
         * 引数 : $str = 対象データ
         * 戻り値 : 改行コード除去後のデータ
         *******************************************************************/
        public static function chomp($str) {
            $str = str_replace("\r", "", $str);
            $str = str_replace("\n", "", $str);
            $str = str_replace("\r\n", "", $str);

            return $str;
        }


        /*******************************************************************
         * 処理 : 日付計算処理
         * 引数 : $trg        = yyyymmddhhiiss
         * $typ    = 減算する項目(y,m,d,h,i,s)
         * $value = 減算する値
         * 戻り値 : array(['日時']=>yyyymmddhhiiss,['unixtime']=UnixTime)
         *******************************************************************/
        public static function DateAdd($trg, $typ, $value) {
            $trg = str_replace("/", "", $trg);
            $trg = str_replace("-", "", $trg);
            $trg = str_replace(":", "", $trg);
            $trg = str_replace(" ", "", $trg);
            $trg = str_replace("'", "", $trg);

            $year = substr($trg, 0, 4);
            $month = substr($trg, 4, 2);
            $day = substr($trg, 6, 2);
            $hour = substr($trg, 8, 2);
            $minute = substr($trg, 10, 2);
            $second = substr($trg, 12, 2);

            if ($month == "" || $month == "00") $month = "01";
            if ($day == "" || $day == "00") $day = "01";
            if ($hour == "") $hour = "00";
            if ($minute == "") $minute = "00";
            if ($second == "") $second = "00";

            switch ($typ) {
                case 'y':
                    $year += $value;
                    break;
                case 'm':
                    $month += $value;
                    break;
                case 'd':
                    $day += $value;
                    break;
                case 'h':
                    $hour += $value;
                    break;
                case 'i':
                    $minute += $value;
                    break;
                case 's':
                    $second += $value;
                    break;
            }
            $curTime = mktime($hour, $minute, $second, $month, $day, $year);
            $dt = DATE("YmdHis", $curTime);
            $arr = [
                '日時'       => $dt,
                'unixtime' => $curTime,
            ];

            return $arr;
        }


        /*******************************************************************
         * 処理：ファイル名を取得
         * 引数：void
         * 戻り値：ファイル名（拡張子なし）
         *******************************************************************/
        public static function getThisFileName() {
            $base_name = basename($_SERVER['PHP_SELF']);
            $fi = explode(".", $base_name);

            return $fi[0];
        }


        /*******************************************************************
         * 処理：ファイルの拡張子取得
         * 引数：$FilePath = ファイルのパスもしくはファイル名
         * 戻り値：ファイルの拡張子
         *******************************************************************/
        public static function GetExt($FilePath) {
            $f = strrev($FilePath);
            $ext = substr($f, 0, strpos($f, "."));

            return strrev($ext);
        }


        /*******************************************************************
         * 処理：ディレクトリごと削除
         * 引数：$dir = 対象ディレクトリ
         * 戻り値：void
         *******************************************************************/
        public static function remove_directory($dir) {
            if ($handle = opendir("$dir")) {
                while (FALSE !== ($item = readdir($handle))) {
                    if ($item != "." && $item != "..") {
                        if (is_dir("$dir/$item")) {
                            remove_directory("$dir/$item");
                        } else {
                            @unlink("$dir/$item");
                        }
                    }
                }

                closedir($handle);
                @rmdir($dir);
            }
        }


        /*******************************************************************
         * 処理：2つの年月の差を求める
         * 引数：$date1 = 日付1
         * $date2 = 日付2
         * 戻り値：差分（日）
         *******************************************************************/
        public static function month_diff($date1, $date2) {

            if (strlen($date1) != 6 || strlen($date2) != 6) return 99999; //不正な場合は無効にしておく

            $timestamp1 = substr($date1, 0, 4) * 12 + substr($date1, 4, 2);
            $timestamp2 = substr($date2, 0, 4) * 12 + substr($date2, 4, 2);

            $diff = ($timestamp1 - $timestamp2);

            return $diff;
        }


        /*******************************************************************
         * 処理：ディレクトリ内のファイルごとコピー
         * 引数：$imageDir = コピー元ディレクトリ
         * $destDir = コピー先ディレクトリ
         * 戻り値：boolean
         *******************************************************************/
        public static function copy_directory($imageDir, $destDir) {
            if (!file_exists($destDir)) {
                @mkdir($destDir, 0777, TRUE);
                @chmod($destDir, 0777);
            }

            $handle = opendir($imageDir);
            while ($filename = readdir($handle)) {
                if (strcmp($filename, ".") != 0 && strcmp($filename, "..") != 0) {
                    if (is_dir("{$imageDir}/{$filename}")) {
                    } else {
                        if (file_exists("{$destDir}/{$filename}")) @unlink("{$destDir}/{$filename}");
                        if (!copy("{$imageDir}/{$filename}", "{$destDir}/{$filename}")) return FALSE;

                        @chmod("{$destDir}/{$filename}", 0777);

                    }
                }
            }

            return TRUE;
        }


        /*******************************************************************
         * 処理：指定データが空、もしくは未定義の場合は、第2引数の値で戻す
         * 引数：$str = 検証データ
         * $ret = 戻すデータ
         * 戻り値：指定データが空、もしくは未定義の場合は、第2引数の値で戻す
         *******************************************************************/
        public static function nz($str, $ret) {
            if (empty($str) || !isset($str) || $str === "") return $ret;

            return $str;
        }


        /*******************************************************************
         * 処理：配列データが空、もしくは未定義の場合は、第2引数の値で戻す
         * 引数：$str = 検証データ
         * $ret = 戻すデータ
         * 戻り値：指定データが空、もしくは未定義の場合は、第2引数の値で戻す
         *******************************************************************/
        public static function nzArray($trg, $ret) {
            return (is_array($trg) && count($trg) > 0 ? $trg : $ret);
        }


        /*******************************************************************
         * 処理：当日の曜日を戻す
         * 引数：$trg = 日付指定Unittime
         * 戻り値：曜日（漢字）
         *******************************************************************/
        public static function getWeekDay($trg = "") {
            $weekday = [
                "日",
                "月",
                "火",
                "水",
                "木",
                "金",
                "土",
            ];

            if ($trg == "") {
                return $weekday[ date("w") ];
            } else {
                return $weekday[ date("w", $trg) ];
            }
        }


        /*******************************************************************
         * 処理：ハッシュされたランダムデータの作成（主にユニークキー作成用）
         * 引数：void
         * 戻り値：hash値
         *******************************************************************/
        public static function createRandomKey() {
            return sha1(uniqid(mt_rand(), TRUE));
        }


        /*******************************************************************
         * 処理：文字数を指定してハッシュされたランダムデータの作成
         * 引数：$length = 作成する文字数
         * 戻り値：hash値
         *******************************************************************/
        public static function createRandomWithLength($length) {
            $str = array_merge(range('a', 'z'), range('0', '9'), range('A', 'Z'));
            $r_str = NULL;
            for ($i = 0; $i < $length; $i++) {
                $r_str .= $str[ rand(0, count($str) - 1) ];
            }

            return $r_str;
        }


        public static function replace_text($str) {
            if ($str == "") return $str;


            global $ARY_SPECIAL_CHARS;
            $search = $ARY_SPECIAL_CHARS;

            $_encode = mb_detect_encoding($str, "UTF-8,SJIS-WIN,SJIS,EUC");
            if ($_encode != "UTF-8") {
                $str = mb_convert_encoding($str, "UTF-8", $_encode);
            }

            $str = strtr($str, $search);

            return $str;
        }


        /**
         * CSVをヘッダー行でアクセスできる連想配列にする
         *
         * @param     $buf
         * @param int $headerRow
         * @return array|string
         */
        public static function csvParse($buf, $headerRow = 0) {

            //$bufRet = preg_replace_callback('/"(\d|,|.*)"/',
            $bufRet = preg_replace_callback('/"(\d|.*)"/', function ($m) {
                return preg_replace('/,/', '', $m[0]);
            }, $buf);

            if (strpos("d" . $bufRet, '""') > 0) {
                $buf = str_replace('""', ",", $bufRet);
            }

            $buf = str_replace('"', "", $buf);
            $buf = str_replace("\r", "", $buf);
            $rows = explode("\n", $buf);

            $ret = "";
            $headers = explode(",", $rows[ $headerRow ]);

            for ($i = ($headerRow + 1); $i <= count($rows); $i++) {
                if (trim($rows[ $i ]) != "") {
                    $cols = explode(",", $rows[ $i ]);
                    $row = "";

                    if (count($cols) == count($headers)) {
                        for ($j = 0; $j < count($cols); $j++) {

                            if (@array_key_exists($headers[ $j ], $row)) {
                                $row[ $headers[ $j ] . "($j)" ] = $cols[ $j ];
                            } else {
                                $row[ $headers[ $j ] ] = $cols[ $j ];
                            }
                        }
                        $ret[] = $row;
                    }
                }
            }

            return $ret;

        }


        /**
         * テスト、検証用
         *
         * @param     $buf
         * @param int $headerRow
         * @return array|string
         */
        public static function csvParse2($buf, $headerRow = 0) {

            //$bufRet = preg_replace_callback('/"(\d|,|.*)"/',
            $bufRet = preg_replace_callback('/"(\d|.*)"/', function ($m) {
                return preg_replace('/,/', '', $m[0]);
            }, $buf);

            if (strpos("d" . $bufRet, '""') > 0) {
                $buf = str_replace('""', ",", $bufRet);
            }

            $buf = str_replace('"', "", $buf);
            $buf = str_replace("\r", "", $buf);
            $rows = explode("\n", $buf);

            $ret = "";
            $headers = explode(",", $rows[ $headerRow ]);

            for ($i = ($headerRow + 1); $i <= count($rows); $i++) {
                if (trim($rows[ $i ]) != "") {
                    $cols = explode(",", $rows[ $i ]);
                    $row = "";

                    if (count($cols) == count($headers)) {
                        for ($j = 0; $j < count($cols); $j++) {

                            if (@array_key_exists($headers[ $j ], $row)) {
                                $row[ $headers[ $j ] . "($j)" ] = $cols[ $j ];
                            } else {
                                $row[ $headers[ $j ] ] = $cols[ $j ];
                            }
                        }
                        $ret[] = $row;
                    }
                }
            }

            return $ret;

        }


        /**
         * 日付文字列の年月日、/、-を削除してyyyymmdd形式にする
         *
         * @param $date
         */
        public static function convertDateSimpleText($date) {
            $patterns = [
                '/\//',
                '/-/',
                '/年/',
                '/月/',
                '/日/',
            ];

            return preg_replace($patterns, '', $date);
        }
    }
