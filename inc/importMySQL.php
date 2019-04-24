<?php
/**
 * Created by PhpStorm.
 * User: sarthak
 * Date: 4/23/19
 * Time: 7:49 PM
 */

if(isset($_GET['outputType']) and isset($_GET['sortType']) and isset($_GET['sortField']) and isset($_GET['pkTable1']) and isset($_GET['pkTable2']) and isset($_GET['selectTable1Input']) and isset($_GET['selectTable2Input'])){

    $table1 = $_GET['selectTable1Input'];
    $table2 = $_GET['selectTable2Input'];
    $pk1 = $_GET['pkTable1'];
    $pk2 = $_GET['pkTable2'];
    $sortType = $_GET['sortType'];
    $sortField = $_GET['sortField'];
    $output = $_GET['outputType'];

    $message = array();

    include_once "./globals.php";
    $db = mysqli_connect($dbHost, $dbUser, $dbPwd, $dbName);
    if(!$db){
        die('Not Connected to Database Engine.');
    }
    $tableName = $table1.'_'.$table2.'_'.$pk1.'_'.$sortField.'_'.$sortType;

    $checkTableExists = $db->query('SELECT * FROM '.$tableName);
    $columnTableMap = array();

    mysqli_select_db($db, 'information_schema');
    $getCol = "SELECT DISTINCT COLUMN_NAME, DATA_TYPE, CHARACTER_MAXIMUM_LENGTH, TABLE_NAME FROM COLUMNS WHERE TABLE_NAME LIKE '".$table1."' OR TABLE_NAME LIKE '".$table2."'";
    $colData = $db->query($getCol);
    $createTable = "CREATE TABLE ".$tableName." (";
    $tableCols = array();
    while($row = $colData->fetch_object()){
        $columnTableMap[$row->COLUMN_NAME] = $row->TABLE_NAME;
        $column = "$row->COLUMN_NAME $row->DATA_TYPE";
        if(!$row->CHARACTER_MAXIMUM_LENGTH){
            $row->CHARACTER_MAXIMUM_LENGTH = 60;
        }
        $column .= "($row->CHARACTER_MAXIMUM_LENGTH)";
        array_push($tableCols, $column);
    }
    $createTable .= implode(", ", array_unique($tableCols)).")";

    mysqli_select_db($db, 'busigence');
    if(!$checkTableExists){
        $table = $db->query($createTable);
        if(!$table){
            $message = "Unable to create mysql table.";
        }
    } else{
        $db->query("TRUNCATE TABLE ".$tableName);
        $table = true;
    }

    if($table){
        mysqli_select_db($db, 'busigence');
        $table3 = $columnTableMap[$sortField];
        $query = "SELECT * FROM ".$table1." JOIN ".$table2." ON ".$table1.".".$pk1."=".$table2.".".$pk2." ORDER BY ".$table3.".".$sortField." ".$sortType;
        $newData = $db->query($query);

        if(!$newData){
            $message = "Unable to Import Data.";
        } else{
            $row = array_keys(get_object_vars($newData->fetch_object()));
            $newColumns = array();
            foreach ($row as $col){
                array_push($newColumns, $col);
            }
            $columnName = implode(", ", $newColumns);
            $error = false;

            while($row = $newData->fetch_array()){
                $i = 0;
                $rowArray = array();

                $data = array();
                foreach($newColumns as $col){
                    array_push($data, addslashes($row[$i]));
                    $i++;
                }
                $values = implode("', '", $data);
                $query = "INSERT INTO ".$tableName." (".$columnName.") VALUES ('".$values."')";
                $result = $db->query($query);
                if(!$result){
                    $error = true;
                    $message = mysqli_error($db);
                }
            }
            if(!$error){
                $message = "Data successfully imported into MySQL table $tableName";
            }
        }
    }
    echo $message;
} else{
    echo "<script>window.open('/', '_self')</script>";
}
