
// 部活とレッスンの表示ーーーーーーーーーーーーーーーーーー

$(function() {
	// 部活が変更されたら発動
	$('select[name="select1_bu"]').change(function() {

		// 選択されている部活のクラス名を取得
		var buName = $('select[name="select1_bu"] option:selected').attr("class");

		// Lesson選択の要素数を取得
		var less_count = LESSON_NUM[buName] + 1;
		var count = $('select[name="select2_lesson"]').children().length;

    // Lesson選択の要素数分、for文で回す
		for (var i=0; i<count; i++) {
		    var lesson = $('select[name="select2_lesson"] option:eq(' + i + ')');
        if(i < less_count){
		        lesson.show();
        }else{
            lesson.hide();
        }
		}
	})
})

// 予測変換の無効化ーーーーーーーーーーーーーーーーーー

	$('form').attr('autocomplete', 'off');

// 入力フォームの表示・非表示ーーーーーーーーーーーーーーーーーー

    // 日時チェック　→　日時入力
    $(function(){
        $("#IDdays_flg").change(function(){
          $("#form_days").addClass('hide');

          // 選択されている部活のクラス名を取得
          var daysFlg = $("[name=days_flg]:checked").val()

          if (daysFlg === "1") {
            $("#form_days").removeClass('hide');
          }else{
            $("#form_days").addClass('hide');
          }
        }).trigger("change");
    });
