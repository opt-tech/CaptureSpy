<?php
	//============================================================
	//		ListAccessクラス
	//		DBアクセスメイン処理
	//------------------------------------------------------------
	//	変更履歴
	//
	//		2016/06/07	K.KUBOTA
	//		・新規作成
	//============================================================
	class ListAccess {
		var $listParam;
		var $listOrderBy;
		var $dbs;
		var $table;

		/*******************************************************************
			処理 : コンストラクタ
			引数 : void
			戻り値 : void
		*******************************************************************/
		public function ListAccess(){
			$this->dbs = new CONN;
		}

		/*******************************************************************
			処理 : ページング関連のイニシャライズ
			引数 : void
			戻り値 : void
		*******************************************************************/
		public function initPagingParamInit(){
			$this->listParam["pageNo"] = (Validate::isNumeric($this->listParam["pageNo"]) ? 1 : $this->listParam["pageNo"]);
			$this->listParam["viewRecordNum"] = (Validate::isNumeric($this->listParam["viewRecordNum"]) ? 20 : $this->listParam["viewRecordNum"]);
		}

		/*******************************************************************
			処理 : OrderBy作成
			引数 : void
			戻り値 : void
		*******************************************************************/
		public function createPagingOrder($withLimit = true){
			$fromPageNo = (($this->listParam["pageNo"] - 1) * $this->listParam["viewRecordNum"]);
			$orderBy  = $this->listParam["orderFld"];
			$orderBy .= " ".$this->listParam["orderBy"];

			if($withLimit){
				//$orderBy .= " LIMIT {$fromPageNo},{$this->listParam["viewRecordNum"]}";	//MySQL用
				$orderBy .= " LIMIT {$this->listParam["viewRecordNum"]} OFFSET {$fromPageNo}";	//PostgreSQL用
			}

			$this->listOrderBy = $orderBy;
			$this->listParam["order"][$this->listParam["orderFld"]] = ($this->listParam["orderBy"] == "desc" ? "▼" : "▲");
		}

		/*******************************************************************
			処理 : ページング作成
			引数 : void
			戻り値 : void
		*******************************************************************/
		public function createPaging(){
			$this->listParam["paging"] = Form::paging(ceil($this->listParam["totalCount"] / $this->listParam["viewRecordNum"]) , $this->listParam["pageNo"], 5);
		}

		/*******************************************************************
			処理 : OrderBy 作成処理（子クラス側で独自実装）
			引数 : void
			戻り値 : void
		*******************************************************************/
		public function createOrder(){

		}

		/*******************************************************************
			処理 : データ更新処理（1フィールドのみ）
			引数 : $where = where文
						 $fld = 更新対象フィールド
						 $val = 更新値
			戻り値 : boolean
		*******************************************************************/
		public function UpdateData($where, $fld, $val){
			$sql = "update {$this->table} set {$fld}='{$val}' where {$where}";
			return $this->dbs->execute($sql);
		}

		/*******************************************************************
			処理 : データ読み込み（1行のみの想定）
			引数 : $where = where文
			戻り値 : boolean
		*******************************************************************/
		public function load($where){
			$sql = "
				select
				    *
				from
						{$this->table}
				where
				    {$where}
			";

			$row=$this->dbs->query($sql);
			$this->listParam["data"] = $row;
		}

		/*******************************************************************
			処理 : 最大主キー取得（idというフィールド名想定）
			引数 : void
			戻り値 : 主キーの最大値
		*******************************************************************/
		public function getLastID(){
			return Dao::dlookup($this->table, "max(id)", "");
		}

		/*******************************************************************
			処理 : 削除処理
			引数 : $where = 条件
			戻り値 : ["result" => boolean, "error_msg" => エラーメッセージ]
		*******************************************************************/
		public function delete($where){
			$sql = "delete from {$this->table} where {$where}";

			if(!$this->dbs->execute($sql)){
				$ret["error_msg"]	= "削除に失敗しました";
				$ret["result"] = false;
				$this->dbs->rollback();
			} else {
				$this->dbs->commit();
				$ret["result"] = true;
			}

			return $ret;
		}

		/**
		 * プルダウン用フィールドリストの作成
		 * @param $findFld
		 */
		public function setPulldownList($findFld, $now = "", $where = ""){
			$sql = "select id, name from {$this->table} ".($where != "" ? " where {$where} " : "")."order by id";
			$row = $this->dbs->query($sql);
			while($row) {
				$data[$row['id']] = $row['name'];
				$row = $this->dbs->next();
			}
			$this->listParam["pullDown"] = Form::createComboKey($data,$now,$findFld);
		}

		/*******************************************************************
		 * 処理 : クライアント名取得
		 * 引数 : $id = 主キー
		 * 戻り値 : クライアント名
		 *******************************************************************/
		public function getName($id) {
			return Dao::dlookup($this->table, "name", "id={$id}");
		}
	}