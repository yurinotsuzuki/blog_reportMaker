<?php

error_reporting(E_ALL); //E_STRICTレベル以外のエラーを報告する
ini_set('display_errors','On'); //画面にエラーを表示させるか

// 部活名のリスト
const BU_NAME = ['部活を選択','HTML/CSS部 入門','JavaScript/jQuery部 入門','PHP/MySQL部',
  'ネットワーク/サーバー部','WEBサービス部','PHPオブジェクト指向部','WordPress部','javascript/jQuery部 中級','HTML/CSS部 中級',
  'HTML/CSS部 上級','PHPフレームワーク部','javascript/jQuery部 上級','営業部','起業部','WEBマーケティング部','保険部',
  'Ruby on Rails部','Laravel部','テスト部'];
const LESSON_NUM = [0,19,11,18,9,25,11,12,33,12,13,12,34,15,46,14,15,22,27,10];
$varJsSample=json_encode(LESSON_NUM);

//twitterカード画像の設定
$twitterImgURL = 'http://shiny-yoron-7678.whitesnow.jp/reportmaker/img/top_baner.png';

//1.post送信されていた場合
if(!empty($_POST) && empty($_GET)){

  //エラーメッセージを定数に設定
  define('MSG01','部活とLessonは入力必須です');
  define('MSG02','時間を記載する場合、日数・時間・合計時間は入力必須です');
  define('MSG03','日時は半角数字で記入してください');
  define('MSG04','合計時間は今日の勉強時間よりも大きい数にしてください');

  //配列$err_msgを用意
  $err_msg = array();

  //変数にユーザー情報を代入
  $select1_bu = $_POST['select1_bu'];
  $select2_lesson = $_POST['select2_lesson'];

  //CHK1: 部活・Lessonの入力チェック
  if($select1_bu === "0"){
    $err_msg['select1_bu'] = MSG01;
  }else if($select2_lesson === "0"){
    $err_msg['select1_bu'] = MSG01;
  }

  //日時チェック
  else if(!empty($_POST['days_flg'])&&$_POST['days_flg']==="1"){

    //CHK2: 日時の入力チェック
    if(empty($_POST['days_num'])){
      $err_msg['days_flg'] = MSG02;
    }
    if(empty($_POST['days_hour'])){
      $err_msg['days_flg'] = MSG02;
    }
    if(empty($_POST['days_total_hour'])){
      $err_msg['days_flg'] = MSG02;
    }

    if(empty($err_msg)){
      //変数にユーザー情報を代入
      $days_num = $_POST['days_num'];
      $days_hour = $_POST['days_hour'];
      $days_total_hour = $_POST['days_total_hour'];

      //CHK3: 日時が半角数字でない場合
      if(!preg_match("/^[0-9]+$/",$days_num)) {
        $err_msg['days_flg'] = MSG03;
      }else if(!preg_match("/^[0-9]+$/",$days_hour)) {
        $err_msg['days_flg'] = MSG03;
      }else if(!preg_match("/^[0-9]+$/",$days_total_hour)) {
        $err_msg['days_flg'] = MSG03;
      }
      //CHK4: 今日の勉強時間が合計よりも大きい場合
      else if($days_hour > $days_total_hour){
        $err_msg['days_flg'] = MSG04;
      }
    }
  }

    //画像生成-----------------------------------------
    if(empty($err_msg)){

      //　部活イメージを取得
      $jpg = "img/before_baner_".$select1_bu.".jpg";
      // Lesson数を取得
      $text = "Lesson".$select2_lesson;

      //合成１　画像＋Lesson
      $afjpg= "to.jpg";
      $font = "font/DIN-BlackAlternate.ttf";
      $image = imagecreatefromjpeg($jpg);
      $color = imagecolorallocate($image, 0, 0, 0);
      imagettftext($image, 150, 0, 980, 580, $color, $font, $text);
      imagejpeg($image, "to.jpg", 100);


      if(!empty($_POST['days_flg'])&&$_POST['days_flg']==="1"){
        //合成２　＋日時
        $jpg2 = "to.jpg";
        $text2 = "Day".$days_num.",Today".$days_hour."h,Total".$days_total_hour."h";
        $font2 = "font/OpenSans-Regular.ttf";
        $afjpg2= "to2.jpg";
        $image2 = imagecreatefromjpeg($jpg2);
        $color2 = imagecolorallocate($image2, 0, 0, 0);
        imagettftext($image2, 90, 0, 165, 850, $color2, $font2, $text2);
        imagejpeg($image2, "to2.jpg", 100);

        //　画像情報の生成
        $content = file_get_contents($afjpg2);

      }else{
        //　画像情報の生成（日時の記載なし）
        $content = file_get_contents($afjpg);
        $text2 = "";
      }

      //DB保存------------------------------------------
      //DB接続
      require_once 'functions.php';
      $dbh = connectDB();

      //　SQL文（クエリー作成）
      $stmt = $dbh->prepare('INSERT INTO report_img (image_content) VALUES (:afjpg)');
      $stmt->bindValue(':image_content', $content, PDO::PARAM_STR);

      //　プレースホルダに値をセットし、SQL文（画像の保存)を実行
      $stmt->execute(array(':afjpg' => $content ));

      //最新のIDを取得してGET送信の画面を表示
      $stmt = $dbh->prepare('SELECT * FROM report_img WHERE image_id = (SELECT max(image_id) FROM report_img)');
      $stmt->execute();
      $image_id = $stmt->fetchColumn();


      //Twitterカード用に画像をディレクトリへ設置（上書き保存）
      require_once 'functions.php';
      $conF = connectFolder($image_id,$content);

      //同じページをGET送信でリクエスト
      header("Location: http://shiny-yoron-7678.whitesnow.jp/reportmaker/index.php?id=".$image_id."&text=".$text2); //同じページを再描画

      //------------------------------------------------

    }
  }else if(!empty($_GET)){
    $twitterImgURL = 'http://shiny-yoron-7678.whitesnow.jp/reportmaker/report-img/image_'.$_GET['id'].'.jpg';
  }


?>

<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <title>ウェブカツ進捗報告</title>
    <link rel="stylesheet" type="text/css" href="css/style.css">
    <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>

    <!-- twitterカード設定　-->
    <meta name="twitter:card" content="summary_large_image" />
    <meta name="twitter:site" content="@swswstd" />
    <meta property="og:url" content="http://shiny-yoron-7678.whitesnow.jp/reportmaker/index.php" />
    <meta property="og:title" content="ウェブカツ進捗報告" />
    <meta property="og:description" content="今日の学習進捗" />
    <meta property="og:image" content="<?php echo ($twitterImgURL); ?>" />

  </head>
  <body>
    <!-- メインコンテンツ -->
    <section id="main">

    <!-- POST送信エリア -------------------------------------------------------------------------------->
    <!-- GETが空の場合、入力フォームを表示 -->
    <?php if(empty($_GET)): ?>

      <!-- トップバナー -->
    <img src="img/top_baner.png" id="top-baner">

      <!-- 入力フォーム -->
      <section id="form">
      <h1>レポート情報を入力</h1>

      <form method="post">

        <!-- 部活名、Lesson数 -->
        <section id="form_buLesson">

          <span class="err_msg"><?php if(!empty($err_msg['select1_bu'])) echo $err_msg['select1_bu']; ?></span>

          <div class="cp_ipselect cp_sl02">
            <select name="select1_bu">
              <?php for ($i=0; $i < count(BU_NAME); $i++) : ?>
              <option class="<?php echo $i ?>" value="<?php echo $i ?>" <?php if(!empty($_POST['select1_bu'])){if($_POST['select1_bu']==$i){echo 'selected';}}?>><?php echo BU_NAME[$i];?></option>
              <?php endfor; ?>
            </select>
          </div>

          <div id="select_lesson" class="cp_ipselect cp_sl02">
            <select name="select2_lesson">
              <option class="0" value="0" selected>Lessonを選択</option>
              <?php for ($i=1; $i <= max(LESSON_NUM); $i++) : ?>
                <option class="<?php echo $i ?>" value="<?php echo $i ?>" <?php if(!empty($_POST['select2_lesson'])){if($_POST['select2_lesson']==$i){echo 'selected';}}?>>Lesson<?php echo $i ?></option>
              <?php endfor; ?>
            </select>
          </div>

        </section>

        <!-- 日数、時間、合計時間 -->

          <div id="IDdays_flg">
            <label>
              <input type="checkbox" name="days_flg" value="1" id="Group2_0" class="checkbox02" <?php if(!empty($_POST['days_flg'])){if($_POST['days_flg']=="1"){echo 'checked';}}?>/>
              <label for="Group2_0" class="check_label">時間も記載する</label>
          </div>

          <div id="form_days">

            　<div id="days_error">
          　    <span class="err_msg"><?php if(!empty($err_msg['days_flg'])) echo $err_msg['days_flg']; ?></span>
          　  </div>

             <div class="cp_iptxt">
              <input id="days_input1" type="text" name="days_num" class="ef" value="<?php if(!empty($_POST['days_num'])) echo $_POST['days_num'];?>" size="" maxlength="3">
  	          <label>学習日数</label>
              <label id="tani">日目 </label>
             </div>

             <div class="cp_iptxt">
              <input id="days_input2" type="text" name="days_hour" class="ef" value="<?php if(!empty($_POST['days_hour'])) echo $_POST['days_hour'];?>" size="" maxlength="2">
  	          <label>今日の学習</label>
              <label id="tani">時間 </label>
             </div>

             <div class="cp_iptxt">
              <input id="days_input3" type="text" name="days_total_hour" class="ef" value="<?php if(!empty($_POST['days_total_hour'])) echo $_POST['days_total_hour'];?>" size="" maxlength="4">
  	          <label>合計</label>
              <label id="tani">時間 </label>
             </div>

             <br>

        </div>
        <br>

        <!-- 送信ボタン -->
        <div class="form_submit">
          <input type="submit" value="レポートを作成！"><br>
        </div>

        </form>
      </section>
      <!-- POST送信エリアここまで -------------------------------------------------------------------------------->
      <!-- GETの場合、tweetボタンと作成画面へ戻るボタンを表示 -->
        <?php elseif(!empty($_GET)): ?>

        <img src="image.php?id=<?= $_GET['id']; ?>" id="top-baner">

        <!-- Tweetボタン -->
        <a href="https://twitter.com/share"
        class="twitter-share-button"
        data-url="http://shiny-yoron-7678.whitesnow.jp/reportmaker/index.php?id=<?= $_GET['id']; ?>"
        data-text="ツイート文を記入してください：<?= $_GET['text']; ?>"
        data-size="large"
        data-count="none"
        data-hashtags="ウェブカツ">Tweet</a>

        <script>
        !function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0],p=/^http:/.test(d.location)?'http':'https';
        if(!d.getElementById(id)){
          js=d.createElement(s);
          js.id=id;
          js.src=p+'://platform.twitter.com/widgets.js';
          fjs.parentNode.insertBefore(js,fjs);
          }}(document, 'script', 'twitter-wjs');
        </script>

        <!-- 作成画面へ戻るボタン -->
        <a href="index.php">
          <button type="button">レポート作成画面へ戻る</button>
        </a>

      <?php endif; ?>

    <!-- GET送信画面エリアここまで -------------------------------------------------------------------------------->
  </section>
  <script type="text/javascript">
    var LESSON_NUM = JSON.parse('<?php echo $varJsSample; ?>');
  </script>
  <script type="text/javascript" src="js/main.js"></script>

  </body>
</html>
