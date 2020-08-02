<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>5-1</title>
</head>
<body>
<?php
	// DB接続設定
	$dsn = 'データベース名';
	$user = 'ユーザー名';
	$password = 'パスワード';
	$pdo = new PDO($dsn, $user, $password, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING));

	//データベースサーバに、データを登録するための「テーブル」を作成
    $sql = "CREATE TABLE IF NOT EXISTS test"
  ." ("
  . "id INT AUTO_INCREMENT PRIMARY KEY,"
  . "name char(32),"
  . "comment TEXT,"
  . "now TEXT,"
  . "Password TEXT"
  .");";
  $stmt = $pdo->query($sql);

	//データベースに現在、どのようなテーブルが作成されているかを確認
	//全てのテーブル名を表示
  $sql ='SHOW TABLES';
	$result = $pdo -> query($sql);
	foreach ($result as $row){
		echo $row[0];
		echo '<br>';
	}
    echo "<hr>";


    //投稿機能
    //名前とコメント、パスワードフォーム内が空でない場合に以下を実行する
    if(!empty($_POST['name']) && !empty($_POST['comment']) && !empty($_POST['passSub'])) {     

      // editNoがあるときは編集、ない時は新規投稿
      if(!empty($_POST['editNO'])) {
  
        //編集機能
        //編集フォームの送信の有無で処理を分岐
        $editNO = $_POST['editNO'];

        $sql = 'SELECT * FROM test WHERE id='.$editNO;
        $results = $pdo->query($sql);
        $result = $results->fetch();
        $ID = $result['id'];
        $subPASS = $result['Password'];

        //編集番号と投稿番号が一致したら
        if($editNO == $ID) {

          $passSub = $_POST['passSub'];

          //かつパスワードが一致したら
          if($passSub == $subPASS) {

            $name = $_POST['name'];
            $comment = $_POST['comment'];
            $time = date("Y/m/d H:i:s");

            //DBのテーブルに登録したデータレコードをUPDATE文で更新
            $sql = 'UPDATE test SET name=:name,comment=:comment,now=:now,Password=:Password WHERE id='.$editNO;
            $stmt = $pdo->prepare($sql);

            $stmt->bindValue(':name', $name, PDO::PARAM_STR);
            $stmt->bindValue(':comment', $comment, PDO::PARAM_STR);
            $stmt->bindValue(':now', $time, PDO::PARAM_STR);
            $stmt->bindValue(':Password', $passSub, PDO::PARAM_STR);

            $stmt->execute();

          }         
        }

      } else {

        //新規投稿機能
        $time = date("Y/m/d H:i:s");
        $name = $_POST['name'];
        $comment = $_POST['comment'];
        $passSub = $_POST['passSub'];

        //データ（レコード）を登録
        $sql = $pdo -> prepare("INSERT INTO test (name, comment, now, Password) VALUES (:name, :comment, :now, :Password)");
        
        $sql -> bindValue(':name', $name, PDO::PARAM_STR);
        $sql -> bindValue(':comment', $comment, PDO::PARAM_STR);
        $sql -> bindValue(':now', $time, PDO::PARAM_STR);
        $sql -> bindValue(':Password', $passSub, PDO::PARAM_STR);
        
        $sql -> execute();

      }
        
    //編集したい投稿内容を投稿フォームに内容を表示
    } elseif(!empty($_POST['edit']) && !empty($_POST['passEdit'])) {

      $edit = $_POST['edit'];
      $passEdit = $_POST['passEdit'];

      $sql = 'SELECT * FROM test WHERE id='.$edit;
      $results = $pdo->query($sql);
      $result = $results->fetch();
      $editId =  $result['id'];
      $editPASS = $result['Password'];

      if($edit == $editId) {

        if($passEdit == $editPASS){

          $sql = 'SELECT * FROM test WHERE id='.$edit;
          $results = $pdo->query($sql);
          $result = $results->fetch();
  
          $editname = $result['name'];
          $editcomment = $result['comment'];
          $editnumber = $result['id'];

        } else {
          $pass_error = true;
        }         
      }
    }

    //削除機能
    //DELETE文で削除
    if(!empty($_POST['dnum']) && !empty($_POST['passDel'])) {

      $dnum = $_POST['dnum'];
      $passDel = $_POST['passDel'];

      $sql = 'SELECT * FROM test WHERE id='.$dnum;
      $results = $pdo->query($sql);
      $result = $results->fetch();
      $delId = $result['id'];
      $delPASS = $result['Password'];

      if($dnum == $delId){

        if($passDel == $delPASS){

          $sql = 'DELETE from test WHERE id='.$dnum;
          $stmt = $pdo->prepare($sql);
          $stmt->bindValue(':id', $dnum, PDO::PARAM_INT);
          $stmt->execute();

        } else {
          $pass_error = true;
        }
      }  
    }

    if(!empty($_POST['submit'])){
      if(empty($_POST['name'])){
        echo "Error: Name is Empty.<br>";
      }else if(empty($_POST['comment'])){
        echo "Error: Comment is Empty.<br>";
      }else if(empty($_POST['passSub'])){
        echo "Error: Password is Empty.<br>";
      }
    }else if(!empty($_POST['delete'])){
      if(empty($_POST['dnum'])){
        echo "Error: Delete-Number is Empty.<br>";
      }else if(empty($_POST['passDel'])){
        echo "Error: Password is Empty.<br>";
      }
    }else if(!empty($_POST['editbutton'])){
      if(empty($_POST['edit'])){
        echo "Error: Edit-Number is Empty.<br>";
      }else if(empty($_POST['passEdit'])){
        echo "Error: Password is Empty.<br>";
      }
    }

    if($pass_error){
      echo "Password is invalid.<br>";
    }

?>

<form action="5-1.php" method="post">
    【投稿フォーム】<br>
      <input type="text" name="name" placeholder="名前" value="<?php if(isset($editname)) {echo $editname;} ?>"><br>
      <input type="text" name="comment" placeholder="コメント" value="<?php if(isset($editcomment)) {echo $editcomment;} ?>"><br>
      <input type="hidden" name="editNO" value="<?php if(isset($editnumber)) {echo $editnumber;} ?>">
      <input type="text" name="passSub" placeholder="パスワード">
      <input type="submit" name="submit" value="送信">
    </form>
    
    <form action="5-1.php" method="post">
    【削除フォーム】<br>
      <input type="text" name="dnum" placeholder="削除対象番号"><br>
      <input type="text" name="passDel" placeholder="パスワード">
      <input type="submit" name="delete" value="削除">
    </form>

    <form action="5-1.php" method="post">
    【編集フォーム】<br>
      <input type="text" name="edit" placeholder="編集対象番号"><br>
      <input type="text" name="passEdit" placeholder="パスワード">
      <input type="submit" name="editbutton" value="編集">
    </form>

<?php
 	//テーブルに登録されたデータを取得し、全て表示
    $sql = 'SELECT * FROM test';
    $stmt = $pdo->query($sql);
    $results = $stmt->fetchAll();
    foreach ($results as $row){
        //$rowの中にはテーブルのカラム名が入る
        echo $row['id'].',';
        echo $row['name'].',';
        echo $row['comment'].',';
        echo $row['now'].'<br>';
    echo "<hr>";
    }
?>

</body>
</html>