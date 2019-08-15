<?php

  //DB接続
  require_once 'functions.php';
  $dbh = connectDB();

  //　SQL文（クエリー作成）
  $stmt = $dbh->prepare('SELECT * FROM report_img WHERE image_id = :image_id');
  $stmt->bindValue(':image_id', (int)$_GET['id'], PDO::PARAM_INT);

  //処理を実行
  $stmt->execute();
  $image = $stmt->fetch();

  //拡張子の取得
  $finfo    = finfo_open(FILEINFO_MIME_TYPE);
  $mimeType = finfo_buffer($finfo, $image['image_content']);

  //画像の出力
  header('Content-type: ' . $mimeType);
  echo $image['image_content'];

  exit();

?>
