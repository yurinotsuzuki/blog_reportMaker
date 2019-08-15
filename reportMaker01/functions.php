<?php

// データベースに接続
function connectDB() {

  $dsn = 'mysql:dbname=katurepo_report_img;host=localhost;charset=utf8';
  $user = 'root';
  $password = 'root';

  $options = array(
          // SQL実行失敗時に例外をスロー
          PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
          // デフォルトフェッチモードを連想配列形式に設定
          PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
          // バッファードクエリを使う(一度に結果セットをすべて取得し、サーバー負荷を軽減)
          // SELECTで得た結果に対してもrowCountメソッドを使えるようにする
          PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true,
      );

  // PDOオブジェクト生成（DBへ接続）
  $dbh = new PDO($dsn, $user, $password, $options);
  return $dbh;
}

//　Twitterカード用に画像をディレクトリへ設置（上書き保存）
function connectFolder($image_id,$content) {
  $save_file = 'report-img/image_'.($image_id).'.jpg';
  file_put_contents($save_file,$content);
}
?>
