<?php
/**
 * Created by PhpStorm.
 * User: sarthak
 * Date: 4/23/19
 * Time: 7:49 PM
 */

include_once "./globals.php";
$db = mysqli_connect($dbHost, $dbUser, $dbPwd, $dbName);
if(!$db){
    die('Not Connected to Database Engine.');
}

if(isset($_GET['outputType']) and isset($_GET['sortType']) and isset($_GET['sortField']) and isset($_GET['pkTable1']) and isset($_GET['pkTable2']) and isset($_GET['selectTable1Input']) and isset($_GET['selectTable2Input'])){

    $table1 = $_GET['selectTable1Input'];
    $table2 = $_GET['selectTable2Input'];
    $pk1 = $_GET['pkTable1'];
    $pk2 = $_GET['pkTable2'];
    $sortType = $_GET['sortType'];
    $sortField = $_GET['sortField'];
    $output = $_GET['outputType'];

    $query = "SELECT * FROM ".$table1." JOIN ".$table2." ON ".$table1.".".$pk1."=".$table2.".".$pk2." ORDER BY ".$table1.".".$sortField." ".$sortType;
    $result = $db->query($query);
    $tableName = $table1.'_'.$table2.'_'.$pk1.'_'.$sortField.'_'.$sortType;

    //checking if file output.csv exists and delete if yes
    $myFile = "downloads/output.csv";
    if(file_exists($myFile)){
        unlink($myFile) or die("Couldn't delete file");
    }

    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename='.$tableName.'.csv');
    $output = fopen('php://output', 'w');

    $row = array_keys(get_object_vars($result->fetch_object()));
    $cols = array();
    foreach ($row as $col){
        array_push($cols, $col);
    }
    fputcsv($output, $cols);

    while($row = $result->fetch_object()){
        $rowArray = array();
        foreach ($row as $col){
            array_push($rowArray, $col);
        }
        fputcsv($output, $rowArray);
    }

} else{
    die();
}
?>

<script>window.close();</script>
