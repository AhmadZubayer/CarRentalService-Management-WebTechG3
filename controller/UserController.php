<?php

include_once 'model/UserModel.php';



    if ($action === 'deleteMember') {
        if ($_SESSION['role'] !== 'admin') {
            echo json_encode(array("status" => "error", "message" => "Unauthorized"));
            return;
        }
        $id = (int) trim($_POST['id']);
        if ($id <= 0) {
            echo json_encode(array("status" => "error", "message" => "Invalid member ID."));
            return;
        }
        deleteMember($conn, $id);
        echo json_encode(array("status" => "success", "message" => "Member deleted successfully."));
        return;
    }

    if ($action === 'getProfile') {
        $user = getUserById($conn, $userId);
        if ($user) {
            echo json_encode(array("status" => "success", "data" => $user));
        } else {
            echo json_encode(array("status" => "error", "message" => "User not found."));
        }
        return;
    }

   
