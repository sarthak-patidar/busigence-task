<?php
/**
 * Created by PhpStorm.
 * User: sarthak
 * Date: 4/23/19
 * Time: 10:48 AM
 */

include_once "./globals.php";
$db = mysqli_connect($dbHost, $dbUser, $dbPwd, $dbName);
if(!$db){
    die('Not Connected to Database Engine.');
}

if(isset($_POST['outputType']) and isset($_POST['sortType']) and isset($_POST['sortField']) and isset($_POST['pkTable1']) and isset($_POST['pkTable2']) and isset($_POST['selectTable1Input']) and isset($_POST['selectTable2Input'])){

    $table1 = $_POST['selectTable1Input'];
    $table2 = $_POST['selectTable2Input'];
    $pk1 = $_POST['pkTable1'];
    $pk2 = $_POST['pkTable2'];
    $sortType = $_POST['sortType'];
    $sortField = $_POST['sortField'];
    $output = $_POST['outputType'];
    $mysql = false; // keep false by default

    $query = "SELECT * FROM ".$table1." JOIN ".$table2." ON ".$table1.".".$pk1."=".$table2.".".$pk2." ORDER BY ".$table1.".".$sortField." ".$sortType;
    $result = $db->query($query);
    if(!$result){
        $query = "SELECT * FROM ".$table1." JOIN ".$table2." ON ".$table1.".".$pk1."=".$table2.".".$pk2." ORDER BY ".$table2.".".$sortField." ".$sortType;
        $result = $db->query($query);
    }
    $tableMarkup = '<table class="highlight striped responsive-table"><thead><tr>';

    $row = array_keys(get_object_vars($result->fetch_object()));
    $cols = array();
    foreach ($row as $col){
        $tableMarkup .= '<th>'.$col.'</th>';
    }

    $tableMarkup .= '</tr></thead><tbody>';

    while($row = $result->fetch_object()){
        $tableMarkup .= '<tr>';
        foreach ($row as $col){
            $tableMarkup .= '<td>'.$col.'</td>';
        }
        $tableMarkup .= '</tr>';
    }
    $tableMarkup .= '</tbody></table>';


    $response = array(
        'status' => true,
        'source' => 'mysqlVisualizer',
        'data' => $_POST,
        'markup' => $tableMarkup,
    );

    echo json_encode($response);
} else{
    die();
}
