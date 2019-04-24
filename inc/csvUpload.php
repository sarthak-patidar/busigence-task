<?php
/**
 * Created by PhpStorm.
 * User: sarthak
 * Date: 4/23/19
 * Time: 12:48 AM
 */

include_once "./globals.php";
$db = mysqli_connect($dbHost, $dbUser, $dbPwd, $dbName);
if(!$db){
    die('Not Connected to Database Engine.');
}

function create_if_not_exists($table, $db, $columns){
    $checkTableExists = $db->query('SELECT * FROM '.$table);
    if($checkTableExists){
        $db->query("TRUNCATE TABLE ".$table);
    } else{
        $createTable = "CREATE TABLE ".$table." (";

        $tableCols = array();
        foreach ($columns as $column){
            $str = $column." varchar(255)";
            array_push($tableCols, $str);
        }
        $createTable .= implode(", ", array_unique($tableCols)).")";
        $table = $db->query($createTable);

        if(!$table){
            array_push($message, "Unable to create mysql table.");
            echo json_encode($message);
            die();
        }
    }
}

if(isset($_FILES['csv'])) {

    $filename = $_FILES['csv']['name'];
    $file_loc = $_FILES['csv']['tmp_name'];
    $target = "../uploads/" . $filename;
    $tableName = explode(".", $filename)[0];

    if (move_uploaded_file($file_loc, $target)) {
        $response = array(
            "status" => true,
            "source" => 'csv',
            "filename" => $filename,
            "message" => "File Uploaded Successfully.",
        );
    } else {
        $response = array(
            "status" => false,
            "message" => "Unable to upload csv file."
        );
    }

    $file = fopen($target,"r");
    $columns = fgetcsv($file);
    create_if_not_exists($tableName, $db, $columns);
    $columnName = implode(", ", $columns);
    $errors = array();
    while(! feof($file)){
        $row = fgetcsv($file);
        $i = 0;
        $data = array();
        foreach($columns as $col){
            array_push($data, addslashes($row[$i]));
            $i++;
        }
        $values = implode("', '", $data);
        $query = "INSERT INTO ".$tableName." (".$columnName.") VALUES ('".$values."')";
        $result = $db->query($query);
        if(!$result){
            array_push($errors, mysqli_error($db));
        }
    }
    fclose($file);

    if(count($errors)){
        $response['importErrors'] = $errors;
    }
    echo json_encode($response);
}
