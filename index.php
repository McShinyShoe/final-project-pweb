<?php
    $usr=$_GET["usr"] ?? null;
    $token=$_GET["token"] ?? null;
    $type = @$_GET['type'] ?? -1;
    $id = @$_GET['id'] ?? -1;
    include("_functions.php");
    $conn=dbconn();
    if (!$conn) {
      die("koneksi error");
    }
    $sql = "SELECT ID, Password FROM USR WHERE Username = \"$usr\"";
    $result=mysqli_query($conn, $sql);
    $temp = mysqli_fetch_array($result);
    if($temp == null) {
      header("Location: login.php");
      exit();
    }
    $usrid = $temp['ID'];
    $usrpass = $temp['Password'];

    if($usrpass != $token || $usr == null || $token == null) {
      header("Location: login.php");
      exit();
    }


    if ($_SERVER["REQUEST_METHOD"] == "POST") {
      // Collect username and password from the form
      $msg = @$_POST['msg'] ?? null;
      $newusr = @$_POST['newusr'] ?? null;
      $newgrp = @$_POST['newgrp'] ?? null;
      $crtgrp = @$_POST['crtgrp'] ?? null;
  
      // Perform SQL insertion (This is just an example, please hash passwords properly in a real scenario)
      if($newusr != null) {
        $sql = "INSERT INTO DRCT_MSG (Sender, Receiver, Message) VALUES ($usrid, (SELECT ID FROM USR WHERE Username = \"$newusr\"), \"$msg\");";
      }
      else if($newgrp != null) {
        $sql = "INSERT INTO GRP_MMBR (Group_ID, User_ID) VALUES ((SELECT ID FROM GRP WHERE Name = \"$newgrp\"), $usrid)";
      }
      else if($crtgrp != null) {
        $sql = "INSERT INTO GRP (Name) VALUES (\"$crtgrp\");";
        mysqli_query($conn, $sql);
        $sql = "INSERT INTO GRP_MMBR (Group_ID, User_ID) VALUES ((SELECT ID FROM GRP WHERE Name = \"$crtgrp\"), $usrid)";
        mysqli_query($conn, $sql);
        $sql = "INSERT INTO GRP_MSG (Group_ID, Sender, Message) VALUES ((SELECT ID FROM GRP WHERE Name = \"$crtgrp\"), $usrid, \"Hello!\");";
    }
      else if($type) {
        $sql = "INSERT INTO GRP_MSG (Group_ID, Sender, Message) VALUES ($id, $usrid, \"$msg\");";
      }
      else {
        $sql = "INSERT INTO DRCT_MSG (Sender, Receiver, Message) VALUES ($usrid, $id, \"$msg\");";
      }
      
      // echo $sql;
      mysqli_query($conn, $sql);
      // echo "OK";
      // echo $sql;
      $_POST['msg'] = null;
      $_POST['newusr'] = null;
      $_POST['newgrp'] = null;
      $_POST['crtgrp'] = null;
      header("Location: index.php?usr=$usr&token=$token&type=$type&id=$id");
      exit();
  }


    $sql = "SELECT Chat_ID, Nama, Sender, IF(DATE(Date_Sent) = CURRENT_DATE(), DATE_FORMAT(Date_Sent, \"%H:%i\"), DATE_FORMAT(Date_Sent, \"%d/%m/%Y\")) AS Date_Sent, Message, ISGROUP FROM (
      SELECT *, 0 AS ISGROUP FROM (
          SELECT IF(Sender = $usrid, Receiver, Sender) AS Chat_ID, IF(Sender = $usrid, RCV.Username, SNDR.Username) AS Nama, SNDR.Username AS Sender, Date_Sent, Message
          FROM DRCT_MSG
          JOIN USR AS SNDR ON Sender = SNDR.ID
          JOIN USR AS RCV ON Receiver = RCV.ID
          WHERE Sender = $usrid 
          OR Receiver = $usrid
      ) AS TEMP
      WHERE (Nama, Date_Sent) IN (
          SELECT Nama, MAX(Date_Sent)
          FROM (
              SELECT IF(Sender = $usrid, Receiver, Sender) AS Chat_ID, DRCT_MSG.ID, IF(Sender = $usrid, RCV.Username, SNDR.Username) AS Nama, SNDR.Username AS Sender, Date_Sent, Message FROM DRCT_MSG
              JOIN USR AS SNDR ON Sender = SNDR.ID
              JOIN USR AS RCV ON Receiver = RCV.ID
              WHERE Sender = $usrid OR Receiver = $usrid
          ) AS TEMP GROUP BY Nama
      )
      UNION
      SELECT *, $usrid AS ISGROUP FROM (
          SELECT GRP.ID AS Chat_ID, GRP.Name, USR.Username AS Sender, Date_Sent, Message FROM GRP_MSG
          JOIN USR ON Sender = USR.ID
          JOIN GRP_MMBR ON GRP_MSG.Group_ID = GRP_MMBR.Group_ID
          JOIN GRP ON GRP.ID = GRP_MMBR.Group_ID
          WHERE GRP_MMBR.User_ID = $usrid
      ) AS TEMP
      WHERE (Name, Date_Sent) IN (
          SELECT Name, MAX(Date_Sent)
          FROM (
              SELECT GRP.ID AS Chat_ID, GRP.Name, USR.Username AS Sender, Date_Sent, Message FROM GRP_MSG
              JOIN USR ON Sender = USR.ID
              JOIN GRP_MMBR ON GRP_MSG.Group_ID = GRP_MMBR.Group_ID
              JOIN GRP ON GRP.ID = GRP_MMBR.Group_ID
              WHERE GRP_MMBR.User_ID = $usrid
          ) AS TEMP GROUP BY Name
      )
  ) AS RESULT
  ORDER BY Date_Sent DESC;";
	$result=mysqli_query($conn, $sql);
  $i=0;
	while ($row = mysqli_fetch_array($result)) {
        $Chat_ID[$i] = $row['Chat_ID'];
        $Nama[$i] = $row['Nama'];
        $Sender[$i] = $row['Sender'];
        $Date_Sent[$i] = $row['Date_Sent'];
        $Message[$i] = $row['Message'];
        $ISGROUP[$i] = $row['ISGROUP'];
		$i++;
	}

    if($type == -1 || $id == -1) {

    }
    else if($type) {
        $sql = "SELECT USR.ID AS USR_ID, USR.Username AS USR_Username, Date_Sent, Message FROM GRP_MSG
        JOIN USR ON Sender = USR.ID
        WHERE Group_ID = $id
        ORDER BY Date_Sent;";
        $result=mysqli_query($conn, $sql);
        $i=0;
        while ($chats = mysqli_fetch_array($result)) {
            $chats_USR_ID[$i] = $chats['USR_ID'];
            $chats_USR_Username[$i] = $chats['USR_Username'];
            $chats_Date_Sent[$i] = $chats['Date_Sent'];
            $chats_Message[$i] = $chats['Message'];
            $i++;
        }
    }
    else {
        $sql = "SELECT SNDR.ID AS SNDR_ID, SNDR.Username AS SNDR_Username, RCV.ID AS RCV_ID, RCV.Username AS RCV_Username, Date_Sent, Message FROM DRCT_MSG
        JOIN USR AS SNDR ON Sender = SNDR.ID
        JOIN USR AS RCV ON Receiver = RCV.ID
        WHERE (Sender = $usrid AND Receiver = $id)
        OR (Sender = $id AND Receiver = $usrid)
        ORDER BY Date_Sent;";
        $result=mysqli_query($conn, $sql);
        $i=0;
        while ($chats = mysqli_fetch_array($result)) {
            $chats_SNDR_ID[$i] = $chats['SNDR_ID'];
            $chats_SNDR_Username[$i] = $chats['SNDR_Username'];
            $chats_RCV_ID[$i] = $chats['RCV_ID'];
            $chats_RCV_Username[$i] = $chats['RCV_Username'];
            $chats_Date_Sent[$i] = $chats['Date_Sent'];
            $chats_Message[$i] = $chats['Message'];
            $i++;
        }
    }
	mysqli_close($conn);
?>
<!doctype html>
<html lang="en" class="full-height">

<head>
  <title>ShinyChat</title>
  <!-- Required meta tags -->
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

  <!-- Bootstrap CSS v5.2.1 -->

  <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js" integrity="sha384-IQsoLXl5PILFhosVNubq5LC7Qb9DXgDA9i+tQ8Zj3iwWAwPtgFTxbJ8NT4GN1R8p" crossorigin="anonymous"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.min.js" integrity="sha384-cVKIPhGWiC2Al4u+LWgxfKTRIcfu0JTxR+EQDz/bgldoEyl4H0zUF0QKbrJ0EcQF" crossorigin="anonymous"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
  <link rel="stylesheet" href="style.css">
  <script>
      <?php for ($i=0; $i<sizeof($Nama); $i++) { ?>
      function submit_<?php echo $ISGROUP[$i]?>_<?php echo $Chat_ID[$i]?>() {
          document.getElementById('chat_<?php echo $ISGROUP[$i]?>_<?php echo $Chat_ID[$i]?>').submit();
      }
      <?php } ?>
  </script>
  <link rel="icon" href="img/favicon.ico" type="img/x-icon">
</head>

<body class="bg-dark container-fluid p-0 p-0 full-height">
  <header>
    <nav class="navbar navbar-expand-sm soft-sahdow">
        <div class="container-fluid">
          <a class="navbar-brand" href="#">
            <img src="img/favicon.ico" alt="" width="30" height="24" class="d-inline-block align-text-top">
            ShinyChat
          </a>
          <ul class="navbar-nav">
            <li class="nav-item px-2">
              <a class="nav-link" href="login.php"><i class="fa fa-user px-1" aria-hidden="true"></i> Log Out</a>
            </li>
          </ul>
        </div>
      </nav>
  </header>
  <main class="container-fluid row">
            <div class="contacts p-0" style="width: 20%;">
                <?php for ($i=0; $i<sizeof($Nama); $i++) { ?>
                <form id="chat_<?php echo $ISGROUP[$i]?>_<?php echo $Chat_ID[$i]?>" method="get" action="index.php"> <!-- Replace 'target_page.php' with the desired target URL -->
                  <input type="hidden" name="usr" value=<?php echo $usr?>>
                  <input type="hidden" name="token" value=<?php echo $token?>>
                  <input type="hidden" name="type" value=<?php echo $ISGROUP[$i]?>>
                  <input type="hidden" name="id" value=<?php echo $Chat_ID[$i]?>>
                </form>
                <div class="contact m-3 px-4 py-2" onclick="submit_<?php echo $ISGROUP[$i]?>_<?php echo $Chat_ID[$i]?>()" <?php if($type == $ISGROUP[$i] && $id == $Chat_ID[$i]) {echo "style=\"background-color: #cccccc\"";}?>>
                      <div class="row p-2">
                          <div class="col p-0 m-0">
                              <p class="fw-bold h5"><?php echo $Nama[$i]?></p>
                          </div>
                          <div class="p-0 m-0" style="width: 20%;">
                              <p style="text-align: right;"><?php echo $Date_Sent[$i]?> <i class="fa fa-clock-o" aria-hidden="true"></i></p>
                          </div>
                      </div>
                        <p><?php echo $Sender[$i]?>: <?php echo substr($Message[$i], 0, 16)?></p>
                </div>
                <?php } ?>

                <div class="contact m-3 px-4 py-2" style="background-color: #cccccc">
                  <p class="fw-bold h3">New DM</p>
                  <form method="post" action="index.php?usr=<?php echo $usr?>&token=<?php echo $token?>&type=<?php echo $type?>&id=<?php echo $id?>">
                    <div class="container-fluid col pu-2">
                      <input placeholder="Username" class="form-control bg-dark text-light" type="text" name="newusr">
                    </div>
                    <div class="container-fluid col pb-2">
                      <input placeholder="Message" class="form-control bg-dark text-light" type="text" name="msg">
                    </div>
                    <div class="container-fluid col py-2">
                      <button type="submit" class="btn btn-success">Send</button>
                    </div>
                  </form>
                </div>

                <div class="contact m-3 px-4 py-2" style="background-color: #cccccc">
                  <p class="fw-bold h3">Join Group</p>
                  <form method="post" action="index.php?usr=<?php echo $usr?>&token=<?php echo $token?>&type=<?php echo $type?>&id=<?php echo $id?>">
                    <div class="container-fluid col py-2">
                      <input placeholder="Group Name" class="form-control bg-dark text-light" type="text" name="newgrp">
                    </div>
                    <div class="container-fluid col py-2">
                      <button type="submit" class="btn btn-success">Join</button>
                    </div>
                  </form>
                </div>


                <div class="contact m-3 px-4 py-2" style="background-color: #cccccc">
                  <p class="fw-bold h3">Create Group</p>
                  <form method="post" action="index.php?usr=<?php echo $usr?>&token=<?php echo $token?>&type=<?php echo $type?>&id=<?php echo $id?>">
                    <div class="container-fluid col py-2">
                      <input placeholder="Group Name" class="form-control bg-dark text-light" type="text" name="crtgrp">
                    </div>
                    <div class="container-fluid col py-2">
                      <button type="submit" class="btn btn-success">Create</button>
                    </div>
                  </form>
                </div>

                
            </div>
            <div class="col bg p-0 container-fluid vh-100">
                <div class="p-5 row vh-100">
                    <?php if($type) {?>
                        <?php for ($i=0; $i<sizeof($chats_Message); $i++) { ?>
                            <div class="col m-2 p-4" style="background-color: white; border-radius: 10px">
                                <div class="row">
                                    <div class="col-sm-8">
                                        <p class="fw-bold h4"><i class="fa fa-user" aria-hidden="true"></i> <?php echo $chats_USR_Username[$i]?></p>
                                    </div>
                                    <div class="col-sm-4">
                                        <p style="text-align: right;"><?php echo $chats_Date_Sent[$i]?> <i class="fa fa-clock-o" aria-hidden="true"></i></p>
                                    </div>
                                </div>
                                
                                <p><?php echo $chats_Message[$i]?></p>    
                            </div>
                            <div class="container-fluid"></div>
                        <?php }?>
                    <?php } else {?>
                        <?php for ($i=0; $i<sizeof($chats_Message); $i++) { ?>
                            <div class="col m-2 p-4" style="background-color: white; border-radius: 10px">
                                <div class="row">
                                    <div class="col-sm-8">
                                        <p class="fw-bold h4"><i class="fa fa-user" aria-hidden="true"></i> <?php echo $chats_SNDR_Username[$i]?></p>
                                    </div>
                                    <div class="col-sm-4">
                                        <p style="text-align: right;"><?php echo $chats_Date_Sent[$i]?> <i class="fa fa-clock-o" aria-hidden="true"></i></p>
                                    </div>
                                </div>
                                
                                <p><?php echo $chats_Message[$i]?></p>    
                            </div>
                            <div class="container-fluid"></div>
                        <?php }?>
                    <?php }?>
                    <div class="col m-2 p-4">
                    </div>
                </div>

                <form method="post" action="index.php?usr=<?php echo $usr?>&token=<?php echo $token?>&type=<?php echo $type?>&id=<?php echo $id?>">
                <div class="container textinput px-5 py-2 row">
                  <div class="container-fluid col">
                    <input placeholder="Message" class="form-control bg-dark text-light" type="text" name="msg">
                  </div>
                  <div style="width: 10%">
                    <button type="submit" class="btn btn-success">Enter</button>
                  </div>
                </div>
                </form>
            </div>
        </main>
        <footer class="footer">
        </footer>
</body>
</html>
