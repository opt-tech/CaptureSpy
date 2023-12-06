<?php
	//============================================================
	//	DBヘルパークラス（基本的にstaticクラス）
	//	DAO関連
	//------------------------------------------------------------
	//	変更履歴
	//
	//		2016/06/07	K.KUBOTA
	//		・新規作成
	//============================================================
	class Dao{

		/*******************************************************************
			処理 : 簡易SQL実行（Selectは不可。CUDのみ）
			引数 : $sql = 実行するSQL文字列
			戻り値 : SQLの実行に成功した場合は空文字。エラー時はエラーメッセージ
		*******************************************************************/
		public static function sqlExec($sql){
			$dbs = new CONN;

			$row = $dbs->execute($sql);
            (new Logger('sql', 'sqlExec'))->Out($sql." ".$dbs->getError());
			return $dbs->getError();
		}

		/*******************************************************************
			処理 : 指定したテーブル、フィールドの値を取得（Where対応）複数あるときは、1件目のみ
			引数 : $tbl = 対象テーブル,
						 $fld = 取得するフィールド
						 $where = 条件
			戻り値 : 検索条件にマッチしたレコードが存在する場合は対象行の$fldデータ。存在しない場合は空文字
		*******************************************************************/
		public static function dlookup($tbl, $fld, $where){
			$dbs = new CONN;

			$sql = "select $fld from $tbl".($where != ""?" where $where;":"");

			if(!$row=$dbs->query($sql)){
				return "";
			} else {
				return $row[0];
			}
		}

		/*******************************************************************
			処理 : 指定したテーブル、フィールドの値を取得（Where対応）
						 複数フィールド取得することが可能で、連想配列で戻す
			引数 : $tbl = 対象テーブル,
						 $fld = 取得するフィールド（取得したいフィールドをカンマ区切りの文字列にて指定）
						 $where = 条件
			戻り値 : 検索条件にマッチしたレコード。行が存在しない場合は空文字
		*******************************************************************/
		public static function dlookupMulti($tbl, $fld, $where){
			$dbs = new CONN;

			$sql = "select $fld from $tbl".($where != ""?" where $where;":"");

			if(!$row=$dbs->query($sql)){
				return "";
			} else {
				return $row;
			}
		}

		/*******************************************************************
			処理 : 指定したテーブルの件数を取得（Where対応）
			引数 : $tbl = 対象テーブル,
						 $where = 条件
			戻り値 : 検索条件にマッチしたレコード数を取得
		*******************************************************************/
		public static function dlookCount($tbl, $where){
			$dbs = new CONN;

			$sql = "select count(*) as cnt from $tbl".($where != ""?" where $where;":"");
            //(new Logger('sql', 'dlookCount'))->Out($sql);
			if(!$row=$dbs->query($sql)){
				return "";
			} else {
				return $row[0];
			}
		}

        /*******************************************************************
         * 処理 : 条件を指定して一覧を返す
         * 引数 : $where = where文
         * 戻り値 : result[]
         *******************************************************************/
        public static function getRows($table, $field, $where = "", $sortField = "") {
            $dbs = new CONN;
            $sql = /** @lang text */
                "
				select
				    {$field}
				from
					{$table}
				" . ($where != "" ? "where {$where}" : "") . "				    
				" . ($sortField != "" ? "order by {$sortField}" : "") . "
			";

            $row = $dbs->query($sql);

            $ret = "";
            while ($row) {
                $ret[] = $row;
                $row = $dbs->next();
            }

            return $ret;
        }

	}
