var lockId = "lockId";

// $("#frmMain").submit(function(){
//     lockScreen();
// });

 $(function() {
	unlockScreen();
 });




/*
 * 画面操作を無効にする
 */
function lockScreen() {

    /*
     * 現在画面を覆い隠すためのDIVタグを作成する
     */
    var divTag = $('<div />').attr("id", lockId);

    /*
     * スタイルを設定
     */
    divTag.css("z-index", "99999")
          .css("position", "absolute")
          .css("top", "0px")
          .css("left", "0px")
          .css("right", "0px")
          .css("bottom", "0px")
          .css("background-image", 'url("/common/img/loading.gif")')
		      .css("background-repeat", 'no-repeat')
					.css("background-attachment", 'fixed')
					.css("background-position", 'center center')
      		.css("background-color", "black")
          .css("opacity", "0.6");

    /*
     * BODYタグに作成したDIVタグを追加
     */
    $('body').append(divTag);
}

/*
 * 画面操作無効を解除する
 */
function unlockScreen() {

    /*
     * 画面を覆っているタグを削除する
     */
    $("#" + lockId).remove();
}


