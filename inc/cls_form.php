<?php
	//============================================================
	//	Formヘルパークラス（基本的にstaticクラス）
	//	フォーム関連
	//------------------------------------------------------------
	//	変更履歴
	//
	//		2016/06/07	K.KUBOTA
	//		・新規作成
	//============================================================
	class Form{

		/*******************************************************************
			処理 : 配列からプルダウンを選択。
						 value値には配列の値を使用
			引数 : $src = ソース配列
						 $now = 現在値
						 $findFld = trueだとプルダウンの先頭に「選択してください」を設置
			戻り値 : HTML文
		*******************************************************************/
		public static function createComboVal($src, $now, $findFld=false){
			$ret = "";
			if($findFld) $ret .= "<option value='' selected='selected'>選択してください</option>\n";

			foreach ($src as $key => $val) {
				$ret .= "<option value='$val'".($now == $val ? " selected='selected'":"").">".$val."</option>\n";
			}

			return $ret;
		}

		/*******************************************************************
			処理 : 配列からプルダウンを選択。
						 value値には配列のキーを使用
			引数 : $src = ソース配列
						 $now = 現在値
						 $findFld = trueだとプルダウンの先頭に「選択してください」を設置
			戻り値 : HTML文
		*******************************************************************/
		public static function createComboKey($src, $now, $findFld=false){

			$ret = "";
			if($findFld) $ret .= "<option value='' selected='selected'>選択してください</option>\n";

			foreach ($src as $key => $val) {
				$ret .= "<option value='$key'".($now == $key ? " selected='selected'":"").">".$val."</option>\n";
			}

			return $ret;
		}

		/*******************************************************************
			処理 : 配列からチェックボックスを選択。
						 value値には配列の値を使用
			引数 : $name = エレメント名
						 $src = ソース配列
						 $now = 現在値
						 $separate = 折り返す個数
			戻り値 : HTML文
		*******************************************************************/
		public static function createCheckboxVal($name, $src, $now, $separate=0){
			$ret = "";
			$cnt = 0;
			foreach ($src as $key => $val) {
				$cnt++;
				$ret .= "<label style='font-weight:100; margin-right: 30px;'><input type='checkbox' name='{$name}[]' value='{$val}'".(in_array($val, $now) ? " checked" : "").">{$val}　</label>";

				if($separate && ($cnt % $separate) == 0) $ret .= "<br>";
			}

			return $ret;
		}

		/*******************************************************************
			処理 : 配列からチェックボックスを選択。
						 value値には配列のキーを使用
			引数 : $name = エレメント名
						 $src = ソース配列
						 $now = 現在値
						 $separate = 折り返す個数
			戻り値 : HTML文
		*******************************************************************/
		public static function createCheckboxKey($name, $src, $now, $separate = 0){
			$ret = "";
			$cnt = 0;
			foreach ($src as $key => $val) {
				$cnt++;
				$ret .= "<label style='font-weight: 100; margin-right: 30px;'><input type='checkbox' name='{$name}[]' value='{$key}'".(in_array($key, $now) ? " checked" : "").">{$val}　</label>";

				if($separate && ($cnt % $separate) == 0) $ret .= "<br>";
			}

			return $ret;
		}

		/*******************************************************************
			処理 : 配列からラジオボックスを選択。
						 value値には配列の値を使用
			引数 : $name = エレメント名
						 $src = ソース配列
						 $now = 現在値
						 $separate = 折り返す個数
			戻り値 : HTML文
		*******************************************************************/
		public static function createRadioVal($name,$src,$now,$separate = 0){
			$ret = "";
			$cnt = 0;
			foreach ($src as $key => $val) {
				$cnt++;
				$ret .= "<label style='font-weight:100; margin-right: 30px;'><input type='radio' name='{$name}' value='{$val}'".($val == $now ? " checked" : "").">{$val}　</label>";

				if($separate && ($cnt % $separate) == 0) $ret .= "<br>";
			}

			return $ret;
		}

		/*******************************************************************
			処理 : 配列からラジオボックスを選択。
						 value値には配列のキーを使用
			引数 : $name = エレメント名
						 $src = ソース配列
						 $now = 現在値
						 $separate = 折り返す個数
			戻り値 : HTML文
		*******************************************************************/
		public static function createRadioKey($name, $src, $now, $separate = 0){
			$ret = "";
			$cnt = 0;
			foreach ($src as $key => $val) {
				$cnt++;

				$ret .= "<label style='font-weight: 100; margin-right: 30px;'><input type='radio' name='{$name}' value='{$key}'".($key == $now ? " checked" : "").">{$val}　</label>";

				if($separate && ($cnt % $separate) == 0) $ret .= "<br>";
			}

			return $ret;
		}

		/*******************************************************************
			処理 : ページング部分の作成
			引数 : $limit = 最大ページ数
						 $page = 現在のページ番号
						 $disp = 1ページの表示件数
			戻り値 : HTML文
		*******************************************************************/
		public static function paging($limit, $page, $disp){
	    $next = $page+1;
	    $prev = $page-1;

	    //ページ番号リンク用
	    $start =  ($page-floor($disp/2) > 0) ? ($page-floor($disp/2)) : 1;	//始点
	    $end =  ($start > 1) ? ($page+floor($disp/2)) : $disp;							//終点
	    $start = ($limit < $end)? $start-($end-$limit):$start;							//始点再計算
			$ret = "";

	    if($page != 1 ) {
	    	$ret = '<li onclick="setPage('.$prev.');return false;"><a href="#"><<</a></li>';
	    	//$ret = '<li onclick="setPage('.$prev.');"><a href="#"><img src="/images/admin/arrow_left.png" alt="前へ"></a></li>';
	    }

	    //最初のページへのリンク
	    if($start >= floor($disp/2)){
	    	if($page == 1){
	    		$ret .= '<li class="current">1</li>';
	    	} else {
	    		$ret .= '<li onclick="setPage(1);return false;"><a href="#">1</a></li>';
	    	}
	      if($start > floor($disp/2)) $ret .= "..."; 	//ドットの表示
	    }

			//ページリンク表示ループ
	    for($i=$start; $i <= $end ; $i++){
        if($i <= $limit && $i > 0 ){
        	if($page == $i){
        		$ret .= '<li class="current">'.$i.'</li>';
        	} else {
        		$ret .= '<li onclick="setPage('.$i.');return false;"><a href="#">'.$i.'</a></li>';
        	}
        }
	    }

	    //最後のページへのリンク
	    if($limit > $end){
        if($limit-1 > $end ) $ret .= "...";    //ドットの表示
        $ret .= '<li onclick="setPage('.$limit.');return false;"><a href="#">'.$limit.'</a></li>';
	    }

	    if($page < $limit){
	    	//$ret .= '<li onclick="setPage('.$next.');"><a href="#"><img src="/images/admin/arrow_right.png" alt="次へ"></a></li>';
	    	$ret .= '<li onclick="setPage('.$next.');return false;"><a href="#">>></a></li>';
	    }

			return $ret;
		}

        /**
         * Util::nzArray のラッパー
         * @param $trg
         * @param $ret
         */
        public static function getParam($trg, $ret){
            return Util::nzArray($trg, $ret);
        }

	}