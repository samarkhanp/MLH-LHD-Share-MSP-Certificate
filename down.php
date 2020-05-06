<?php

require_once 'dompdf/autoload.inc.php';

use Dompdf\Dompdf;

$dompdf = new Dompdf();

//$dompdf->loadHtml('<h1>Welcome to CodexWorld.com</h1>');
$dompdf->set_paper(array(0, 0, 867, 667));
$id = trim($_GET['certificate']);
require_once 'app/dbfile.php';
$obj = new Database();
$obj->connect();
$table = "data";
$where = "id='$id'";
$obj->select($table, "fname, lname", $where);
$res = $obj->getResult();

$fname = ucwords(strtolower(($res[0]['fname'])));
$lname = ucwords(strtolower(($res[0]['lname'])));
$n = rand(1, 4);
if($n==1)
$img='bhaskar';
if($n==2)
$img='harika';
if($n==3)
$img='samar';
if($n==4)
$img='sasi';



// Load content from html file
$html = '<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Certificate</title>

    <style type="text/css">
    @page{
    margin: 0px;}
    body{
    margin: 0px;
     background-image: url("images/'.$img.'.jpg");
  background-repeat: no-repeat;
  background-attachment: fixed;
  background-size: cover;
  color: white;
    }
    @font-face {
    font-family: "Segoe UI";
    font-weight: 700;
    src: local("Segoe UI Bold Italic");
}
    </style>
</head>
<body>
    <div style="color: #0175EB;padding-top:310px;font-family: helvetica;font-size:35px;padding-left:70px">'.$fname.'<br>'.$lname.'</div><br>
</body>
</html>';
$dompdf->loadHtml($html);

// (Optional) Setup the paper size and orientation
//$dompdf->setPaper('A4', 'landscape');
// Render the HTML as PDF
$dompdf->render();

// Output the generated PDF (1 = download and 0 = preview)

$dompdf->stream('test', array("Attachment" => 0));

    