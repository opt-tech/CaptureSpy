<?php


    /**
     * URL関連
     *
     * Created by PhpStorm.
     * User: ka.kubota
     * Date: 2016/10/13
     * Time: 12:15
     */
    class Urls extends ListAccess {
                /*******************************************************************
         * 処理 : コンストラクタ
         * 引数 : $param = 入力条件等のInputデータ
         * 戻り値 : void
         *******************************************************************/
        public function Urls($param = NULL) {
            $this->table = "urls";
            $this->listParam = $param;

            parent::__construct();
        }


		/**
		 * プルダウン用フィールドリストの作成
		 * @param $findFld
		 */
		public function setPulldownList($findFld, $now = "", $where = ""){
			$sql = "select id, ext from {$this->table} ".($where != "" ? " where {$where} " : "")."order by id";
			$row = $this->dbs->query($sql);
			while($row) {
				$data[$row['id']] = $row['ext'];
				$row = $this->dbs->next();
			}
			$this->listParam["pullDown"] = Form::createComboKey($data, $now, $findFld);
		}



    }