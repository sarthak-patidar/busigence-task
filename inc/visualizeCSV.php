<?php
/**
 * Created by PhpStorm.
 * User: sarthak
 * Date: 4/23/19
 * Time: 4:46 AM
 */


if(isset($_POST['fileName'])){
    $fileLocation = '../uploads/'.$_POST['fileName'];

    $tableName = explode(".", $_POST['fileName'])[0];

    $file = fopen($fileLocation,"r");
    $tableMarkup = '<table class="highlight striped responsive-table"><thead><tr>';

    $columns = fgetcsv($file);
    foreach ($columns as $column){
        $tableMarkup .= '<th>'.$column.'</th>';
    }
    $tableMarkup .= '</tr></thead><tbody>';

    while(! feof($file)){
        $row = fgetcsv($file);
        $tableMarkup .= '<tr>';
        foreach($row as $item){
            $tableMarkup .= '<td>'.$item.'</td>';
        }
        $tableMarkup .= '</tr>';
    }
    fclose($file);

    $tableMarkup .= '</tbody></table>';

    $response = array(
        'source' => 'csvVisualizer',
        'status' => true,
        'message' => 'Creating Preview',
        'table' => $tableName,
        'markup' => $tableMarkup
    );
} else{
    $response = array(
        'source' => 'csvVisualizer',
        'status' => false,
        'message' => 'No CSV File Provided.',
    );
}

echo json_encode($response);
