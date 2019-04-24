<?php
/**
 * Created by PhpStorm.
 * User: sarthak
 * Date: 4/23/19
 * Time: 12:09 AM
 */


if(isset($_POST['dbHost'])){
    $host = $_POST['dbHost'];
    $user = $_POST['dbUser'];
    $pwd = $_POST['dbPwd'];
    $db = mysqli_connect($host, $user, $pwd, 'busigence');
    if($db !== false){

        $query = 'SHOW TABLES';
        $result = $db->query($query);
        $schema = array();
        while($row = $result->fetch_object()){
            $tableArray = array(
                'table' => $row->Tables_in_busigence
            );
            $query2 = "SHOW COLUMNS FROM ".$row->Tables_in_busigence;
            $result2 = $db->query($query2);
            $columns = array();
            while($col = $result2->fetch_object()){
                array_push($columns, $col->Field);
            }
            $tableArray['columns'] = $columns;
            array_push($schema, $tableArray);
        }
        $response = array(
            'status' => true,
            'message' => "Connected to MySQL Host",
            'data' => $db->get_server_info(),
            'source' => 'mysql',
            'schema' => $schema
        );
    }else{
        $response = array(
            'status' => false,
            'message' => "Couldn't Connect to MySQL Host. Check if all the details are right.",
            'data' => false,
            'source' => 'mysql'
        );
    }
    echo json_encode($response);
} else{
    include_once "./globals.php";
    $db = mysqli_connect($dbHost, $dbUser, $dbPwd, $dbName);
    if(!$db){
        die('Globals not loaded');
    }
}
