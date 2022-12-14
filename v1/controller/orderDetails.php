<?php
include_once('db.php');
include_once('../model/orderDatailsModel.php');
include_once('../model/response.php');
$allHeaders = getallheaders();
$apiSecurity = $allHeaders['Authorization'];
if ($apiKey != $apiSecurity) {
    $response = new Response();
    $response->setHttpStatusCode(401);
    $response->setSuccess(false);
    $response->addMessage("API Security Key Doesn't exist.");
    $response->send();
    exit;
}
try {
    $writeDB = DB::connectWriteDB();
    $readDB = DB::connectReadDB();
} catch (PDOException $ex) {
    error_log("Connection error - " . $ex, 0);
    $response = new Response();
    $response->setHttpStatusCode(500);
    $response->setSuccess(false);
    $response->addMessage("Database Connection Error");
    $response->send();
    exit;
}
if (array_key_exists('order_id', $_GET)) {
    $order_id = $_GET['order_id'];
    if ($order_id == '' || !is_numeric($order_id)) {
        $response = new Response();
        $response->setHttpStatusCode(400);
        $response->setSuccess(false);
        $response->addMessage("Task ID cannot be blank its must be numeric");
        $response->send();
        exit();
    }
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        try {
            // $ip_server = 'https://neo.fuljor.com/erp/companies/neo_bazar/part_pics/';
            $ip_server = $_SERVER['SERVER_ADDR'] . "/" . "metroapi/v1/images/";

            $orderAddress = $readDB->prepare("SELECT
                                                salesorders.deladd1 AS deladd1,
                                                salesorders.so_status AS so_status,
                                                debtorsmaster.name AS realname,
                                                debtorsmaster.email AS email,
                                                debtorsmaster.phone1 AS phone
                                            FROM
                                                salesorders
                                            INNER JOIN debtorsmaster ON salesorders.debtorno = debtorsmaster.debtorno
                                            WHERE
                                                salesorders.orderno =:order_id");
            $orderAddress->bindParam(':order_id', $order_id, PDO::PARAM_STR);
            $orderAddress->execute();
            // echo $selectUserData->debugDumpParams();

            $addressArray = array();
            while ($rowAdd = $orderAddress->fetch(PDO::FETCH_ASSOC)) {
                $addressArray['address'] = $rowAdd['deladd1'];
                $addressArray['so_status'] = $rowAdd['so_status'];
                $addressArray['realname'] = $rowAdd['realname'];
                $addressArray['email'] = $rowAdd['email'];
                $addressArray['phone'] = $rowAdd['phone'];
            }
            // print_r($addressArray);

            $orderdetails = $readDB->prepare("SELECT
                                                (
                                                SELECT
                                                    SUM(d.qty)
                                                FROM
                                                    carton_list l,
                                                    carton_list_details d,
                                                    carton_status_details s
                                                WHERE
                                                    l.id = d.cid AND l.delivered = 0 AND l.so = :order_idi AND d.stockid = salesorderdetails.stkcode  AND l.id = s.cid AND s.sid = 2
                                            ) AS qtyshipping,
                                            salesorderdetails.orderlineno AS orderlineno,
                                            salesorderdetails.orderno AS orderno,
                                            salesorderdetails.stkcode AS stkcode,
                                            salesorderdetails.unitprice AS unitprice,
                                            salesorderdetails.quantity AS quantity,
                                            salesorderdetails.qtyinvoiced AS qtyinvoiced,
                                            salesorderdetails.completed AS completed,
                                            stockmaster.description AS 'description',
                                            stockmaster.stockid AS 'stockid',
                                            stockmaster.img AS img
                                            FROM
                                                salesorderdetails
                                            INNER JOIN stockmaster ON stockmaster.stockid = salesorderdetails.stkcode
                                            WHERE
                                                salesorderdetails.orderno =:order_id");
            $orderdetails->bindParam(':order_idi', $order_id, PDO::PARAM_INT);
            $orderdetails->bindParam(':order_id', $order_id, PDO::PARAM_INT);
            $orderdetails->execute();
            //             echo $orderdetails->debugDumpParams();
            // exit;
            $rowCount = $orderdetails->rowCount();
            if ($rowCount === 0) {
                $response = new Response();
                $response->setHttpStatusCode(404);
                $response->setSuccess(true);
                $response->addMessage("No data found");
                $response->send();
                exit;
            } else {
                $orderArray = array();
                while ($row = $orderdetails->fetch(PDO::FETCH_ASSOC)) {
                    $order = new Item($row['orderlineno'], $row['orderno'], $row['stkcode'], $row['unitprice'], $row['quantity'], $row['qtyshipping'], $row['qtyinvoiced'], $row['description'], $row['stockid'], $ip_server . $row['stockid'] . '.jpg', $row['completed']);
                    $orderArray[] = $order->returnOrderDetailsArray();
                }




                $dataInfo = new OrderDetails($orderArray, $addressArray);
                $returnInfo = $dataInfo->returnDetailsArray();

                $returnData = array();
                $returnData['rows_returned'] = $rowCount;
                $returnData['data'] = $returnInfo;

                $response = new Response();
                $response->setHttpStatusCode(200);
                $response->setSuccess(true);
                $response->toCache(true);
                $response->setData($returnData);
                $response->send();
                exit;
            }
        } catch (TaskException $ex) {
            $response = new Response();
            $response->setHttpStatusCode(500);
            $response->setSuccess(false);
            $response->addMessage($ex->getMessage());
            $response->send();
            exit;
        } catch (PDOException $ex) {
            error_log("Database query error - " . $ex, 1);
            $response = new Response();
            $response->setHttpStatusCode(500);
            $response->setSuccess(false);
            $response->addMessage("Failed to get task");
            $response->send();
            exit();
        }
    } else {
        $response = new Response();
        $response->setHttpStatusCode(405);
        $response->setSuccess(false);
        $response->addMessage("Request method not allowed");
        $response->send();
        exit();
    }
}
