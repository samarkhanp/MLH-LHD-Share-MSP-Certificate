<?php
ob_start();
session_start();

if (isset($_POST['btnsubmit'])) {
    $email = strtolower(trim($_POST['email']));
//   echo $user . " - " . $user_type . " - " . $pass;
    if ($email != "") {
        require_once 'app/dbfile.php';
        $obj = new Database();
        $obj->connect();
        $table = "data";
         $where = "LCASE(email)='$email'";
        $obj->select($table, "id",$where);
        $res = $obj->getResult();
        //id, uname, name, rno, passw, email, dob, phno, type, cdate, rec_status
        $a = 0;
       if ($res){
            $id = $res[$a]['id'];
         header("location: down?certificate=$id");
        } 
        else {
            header("Location: index?sorry");
        }
       }
       else {
            header("Location: index?sorry=0&opt=1");
        }
    }

?>
