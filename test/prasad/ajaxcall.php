<?php
// config db
require_once 'db_connection.php';
require_once 'ListGenerator.php';

$listGenerator = new ListGenerator($conn);

function handleAjaxRequest($conn) {
    if(isset($_GET['action'])) {
        $action = $_GET['action'];
        switch($action) {
            case 'drop_down':
                dropdownlistdata($conn);
                break;
            case 'generate_list':
                memberlistdata($conn);
                break;
            default:
                echo json_encode(array("status" => "error", "message" => "Unknown action"));
                break;
        }
    } else {
        echo json_encode(array("status" => "error", "message" => "Action parameter is missing"));
    }
}
function dropdownlistdata($conn) {
    
    $listGenerator = new ListGenerator($conn);
    $getDrop= $listGenerator->getDropdownData();

    $response = array();
    if ($getDrop) {
        $response['status'] = 'success';
        $response['data'] = $getDrop;
    } else {
        $response['status'] = 'error';
        $response['message'] = 'Failed to fetch member list';
    }
    header('Content-Type: application/json');
    echo json_encode($response);
}

function memberlistdata($conn) {
    
    $listGenerator = new ListGenerator($conn);
    $members= $listGenerator->generateList();

    $response = array();
    if ($members) {
        $response['status'] = 'success';
        $response['data'] = $members;
    } else {
        $response['status'] = 'error';
        $response['message'] = 'Failed to fetch member list';
    }
    
    header('Content-Type: application/json');
    echo json_encode($response);
}
handleAjaxRequest($conn);
?>
