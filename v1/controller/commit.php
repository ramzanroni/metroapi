<?php

include_once('db.php');
include_once('../model/response.php');
$writeDB = DB::connectWriteDB();
$readDB = DB::connectReadDB();
try {
    $writeDB->beginTransaction();
    $test = $writeDB->execute('LOCK TABLES `tbl_1` WRITE');
    if (!$test) {
        print_r($writeDB->errorInfo());
    }
    exit;
    $statement1 = $writeDB->prepare("INSERT INTO `tbl_1`( `name`) VALUES ('Abdul')");
    $statement1->execute();
    $writeDB->commit();
    $writeDB->execute('UNLOCK TABLES');
    // $writeDB->execute('LOCK TABLES `tbl_2` WRITE');
    // $statement2 = $writeDB->prepare("INSERT INTO `tbl_2`(`industry`) VALUES ('Abdul')");
    // $statement2->execute();

    // $writeDB->execute('UNLOCK TABLES');
    // $writeDB->commit();
} catch (\Exception $e) {
    if ($writeDB->inTransaction()) {
        $writeDB->rollback();
    }
    throw $e;
}

// include 'db-conn.php';
// mysqli_autocommit($conn, FALSE);
// // $lock = mysqli_query($conn, 'LOCK TABLES tbl_1 WRITE');
// $insertTbl1 = mysqli_query($conn, "INSERT INTO `tbl_1`( `name`) VALUES ('Abdul')");
// // mysqli_query($conn, 'UNLOCK TABLES');
// // mysqli_query($conn, 'LOCK TABLES tbl_2 WRITE');
// $insertTbl2 = mysqli_query($conn, "INSERT INTO `tbl_2`( `industry`) VALUES ('Abdul')");
// // mysqli_query($conn, 'UNLOCK TABLES');
// $insertTbl1 = mysqli_query($conn, "INSERT INTO `tbl_1`( `sdasname`) VALUES ('Abdul')");
// if (!mysqli_commit($conn)) {
//     echo "Commit transaction failed";
//     exit();
// } else {
//     echo "Commit success";
// }

/* Tell mysqli to throw an exception if an error occurs */
// mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// $mysqli = mysqli_connect("localhost", "metrosoft", "metrosoft", "eon_bazar");



// /* Start transaction */
// mysqli_begin_transaction($mysqli);

// try {
//     /* Insert some values */
//     mysqli_query($mysqli, 'LOCK TABLES tbl_1,tbla_2 WRITE');
//     mysqli_query($mysqli, "INSERT INTO `tbl_1`( `name`) VALUES ('Abdul')");
//     // mysqli_query($mysqli, 'UNLOCK TABLES');

//     $stmt = mysqli_query($mysqli, "INSERT INTO `tbla_2`( `industry`) VALUES ('Abdul')");

//     mysqli_commit($mysqli);
//     mysqli_query($mysqli, 'UNLOCK TABLES');
// } catch (mysqli_sql_exception $exception) {
//     mysqli_rollback($mysqli);

//     print_r(($exception));
// }
