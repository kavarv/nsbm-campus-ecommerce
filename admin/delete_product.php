<?php
session_start();
include '../config/db.php';

/* Allow only admin */
if(!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit;
}

/* Check if ID exists */
if(isset($_GET['id']) && is_numeric($_GET['id'])) {

    $id = $_GET['id'];

    /* Optional: delete image file first */
    $stmt = $conn->prepare("SELECT image FROM products WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if($row = $result->fetch_assoc()){

        if(!empty($row['image']) && file_exists("../uploads/".$row['image'])){
            unlink("../uploads/".$row['image']);
        }

    }

    $stmt->close();


    /* Delete product from database */
    $stmt = $conn->prepare("DELETE FROM products WHERE id=?");
    $stmt->bind_param("i", $id);

    if($stmt->execute()){
        $_SESSION['success'] = "Product deleted successfully.";
    }
    else{
        $_SESSION['error'] = "Error deleting product.";
    }

    $stmt->close();

}

/* Redirect back to dashboard */
header("Location: dashboard.php");
exit;
?>
