<?php
include_once('../../db.php');
include_once('../../../model/admin/purchase/purchaseModel.php');
include_once('../../../model/response.php');
include_once('../../validation.php');
$validation = new Validation();
$allHeaders = getallheaders();
$apiSecurity = $allHeaders["Authorization"];
if ($apiKey != $apiSecurity) {
    $response = new Response();
    $response->setHttpStatusCode(401);
    $response->addMessage("API Security Key Doesnt exist.");
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
    exit();
}
//get data
if ($_SERVER["REQUEST_METHOD"] === "GET") {
    try {
        if (array_key_exists("orderno", $_GET)) {
            $orderno = $_GET["orderno"];
            if ($orderno === "") {
                $response = new Response();
                $response->setHttpStatusCode(400);
                $response->setSuccess(false);
                $response->addMessage("orderno cannot be blank");
                $response->send();
                exit();
            }
            $purchordersSQL = $readDB->prepare("SELECT * FROM purchorders WHERE orderno=:orderno");
            $purchordersSQL->bindParam(":orderno", $orderno, PDO::PARAM_STR);
        } else {
            $purchordersSQL = $readDB->prepare("SELECT * FROM purchorders");
        }
        $purchordersSQL->execute();
        $rowCount = $purchordersSQL->rowCount();
        if ($rowCount === 0) {
            $response = new Response();
            $response->setHttpStatusCode(404);
            $response->setSuccess(false);
            $response->addMessage("Data not found");
            $response->send();
            exit();
        }
        $purchordersArray = array();
        while ($row = $purchordersSQL->fetch(PDO::FETCH_ASSOC)) {
            $orderNo = $row["orderno"];
            $totalPriceSql = $readDB->prepare('SELECT `unitprice`,`quantityord` FROM `purchorderdetails` WHERE `orderno`=:orderno');
            $totalPriceSql->bindParam(':orderno', $orderNo, PDO::PARAM_STR);
            $totalPriceSql->execute();
            $totalPrice = 0;
            while ($itemRow = $totalPriceSql->fetch(PDO::FETCH_ASSOC)) {
                $totalPrice += $itemRow['unitprice'] * $itemRow['quantityord'];
            }

            $purchordersData = new Purchorders($orderNo, $row["supplierno"], $row["orddate"], $row["dateprinted"], $row["initiator"], $row["intostocklocation"], $row["deladd1"], $row["deliverydate"], $row["status"], $row["stat_comment"], $row["authorized_by"], $row["authorized_date"], $totalPrice);
            $purchordersArray[] = $purchordersData->returnPurchordersArray();
        }
        $returnData = array();
        $returnData["rows_returned"] = $rowCount;
        $returnData["purchorders"] = $purchordersArray;
        $response = new Response();
        $response->setHttpStatusCode(200);
        $response->setSuccess(true);
        $response->toCache(true);
        $response->setData($returnData);
        $response->send();
        exit;
    } catch (PurchordersException $ex) {
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
        $response->addMessage($ex->getMessage());
        $response->send();
        exit();
    }
}
//Post data
elseif ($_SERVER["REQUEST_METHOD"] === "POST") {
    try {
        if ($_SERVER["CONTENT_TYPE"] !== "application/json") {
            $response = new Response();
            $response->setHttpStatusCode(400);
            $response->setSuccess(false);
            $response->addMessage("Content type header is not set to JSON");
            $response->send();
            exit();
        }
        $rawPostData = file_get_contents("php://input");
        if (!$jsonData = json_decode($rawPostData)) {
            $response = new Response();
            $response->setHttpStatusCode(400);
            $response->setSuccess(false);
            $response->addMessage("Request body is not valid JSON");
            $response->send();
            exit();
        }
        $errorArr = array();
        if (isset($jsonData->orderno)) {
            $intValidation = $validation->intValidation($jsonData->orderno);
            if ($intValidation != "") {
                $errorArr["orderno"] = "orderno - " . $intValidation;
            }
        }
        if (isset($jsonData->supplierno)) {
            $intValidation = $validation->intValidation($jsonData->supplierno);
            if ($intValidation != "") {
                $errorArr["supplierno"] = "supplierno - " . $intValidation;
            }
        }
        if (isset($jsonData->orddate)) {
            $stringValidation = $validation->stringValidation($jsonData->orddate);
            if ($stringValidation != "") {
                $errorArr["orddate"] = "orddate - " . $stringValidation;
            }
        }
        if (isset($jsonData->dateprinted)) {
            $stringValidation = $validation->stringValidation($jsonData->dateprinted);
            if ($stringValidation != "") {
                $errorArr["dateprinted"] = "dateprinted - " . $stringValidation;
            }
        }
        if (isset($jsonData->initiator)) {
            $stringValidation = $validation->stringValidation($jsonData->initiator);
            if ($stringValidation != "") {
                $errorArr["initiator"] = "initiator - " . $stringValidation;
            }
        }
        if (isset($jsonData->intostocklocation)) {
            $intValidation = $validation->intValidation($jsonData->intostocklocation);
            if ($intValidation != "") {
                $errorArr["intostocklocation"] = "intostocklocation - " . $intValidation;
            }
        }
        if (isset($jsonData->deladd1)) {
            $stringValidation = $validation->stringValidation($jsonData->deladd1);
            if ($stringValidation != "") {
                $errorArr["deladd1"] = "deladd1 - " . $stringValidation;
            }
        }
        if (isset($jsonData->deliverydate)) {
            $stringValidation = $validation->stringValidation($jsonData->deliverydate);
            if ($stringValidation != "") {
                $errorArr["deliverydate"] = "deliverydate - " . $stringValidation;
            }
        }
        if (isset($jsonData->status)) {
            $stringValidation = $validation->stringValidation($jsonData->status);
            if ($stringValidation != "") {
                $errorArr["status"] = "status - " . $stringValidation;
            }
        }
        if (isset($jsonData->stat_comment)) {
            $stringValidation = $validation->stringValidation($jsonData->stat_comment);
            if ($stringValidation != "") {
                $errorArr["stat_comment"] = "stat_comment - " . $stringValidation;
            }
        }
        if (isset($jsonData->authorized_by)) {
            $stringValidation = $validation->stringValidation($jsonData->authorized_by);
            if ($stringValidation != "") {
                $errorArr["authorized_by"] = "authorized_by - " . $stringValidation;
            }
        }
        if (isset($jsonData->authorized_date)) {
            $stringValidation = $validation->stringValidation($jsonData->authorized_date);
            if ($stringValidation != "") {
                $errorArr["authorized_date"] = "authorized_date - " . $stringValidation;
            }
        }
        if (count($errorArr) > 0) {
            $returnData = array();
            $returnData["rows_returned"] = count($errorArr);
            $returnData["error"] = $errorArr;
            $response = new Response();
            $response->setHttpStatusCode(400);
            $response->setSuccess(false);
            $response->addMessage("Validation failed");
            $response->setData($returnData);
            $response->send();
            exit;
        }
        $purchorders = new Purchorders("", $jsonData->supplierno, $jsonData->orddate, $jsonData->dateprinted, $jsonData->initiator, $jsonData->intostocklocation, $jsonData->deladd1, $jsonData->deliverydate, $jsonData->status, $jsonData->stat_comment, $jsonData->authorized_by, $jsonData->authorized_date, '');
        $orderno = $purchorders->getOrderno();
        $supplierno = $purchorders->getSupplierno();
        $orddate = $purchorders->getOrddate();
        $dateprinted = $purchorders->getDateprinted();
        $initiator = $purchorders->getInitiator();
        $intostocklocation = $purchorders->getIntostocklocation();
        $deladd1 = $purchorders->getDeladd1();
        $deliverydate = $purchorders->getDeliverydate();
        $status = $purchorders->getStatus();
        $stat_comment = $purchorders->getStat_comment();
        $authorized_by = $purchorders->getAuthorized_by();
        $authorized_date = $purchorders->getAuthorized_date();

        $writeDB->beginTransaction();
        // extra 
        $customer_ref = '';
        $mask_date = '0000-00-00';
        $tag = "1";
        $so = 0;
        $vat_type = 0;
        $vat_percentage = '';
        $ait_rate = '';
        $purchordersInsertSQL = $writeDB->prepare("INSERT INTO purchorders (supplierno,orddate,dateprinted,initiator,intostocklocation,deladd1,deliverydate,status,stat_comment,customer_ref,mask_date,tag,authorized_by,authorized_date,so,vat_type,vat_percentage,ait_rate) VALUES (:supplierno,:orddate,:dateprinted,:initiator,:intostocklocation,:deladd1,:deliverydate,:status,:stat_comment,:customer_ref,:mask_date,:authorized_by,:tag,:authorized_date,:so,:vat_type,:vat_percentage,:ait_rate)");
        $purchordersInsertSQL->bindParam(":supplierno", $supplierno, PDO::PARAM_STR);
        $purchordersInsertSQL->bindParam(":orddate", $orddate, PDO::PARAM_STR);
        $purchordersInsertSQL->bindParam(":dateprinted", $dateprinted, PDO::PARAM_STR);
        $purchordersInsertSQL->bindParam(":initiator", $initiator, PDO::PARAM_STR);
        $purchordersInsertSQL->bindParam(":intostocklocation", $intostocklocation, PDO::PARAM_STR);
        $purchordersInsertSQL->bindParam(":deladd1", $deladd1, PDO::PARAM_STR);
        $purchordersInsertSQL->bindParam(":deliverydate", $deliverydate, PDO::PARAM_STR);
        $purchordersInsertSQL->bindParam(":status", $status, PDO::PARAM_STR);
        $purchordersInsertSQL->bindParam(":stat_comment", $stat_comment, PDO::PARAM_STR);
        $purchordersInsertSQL->bindParam(":customer_ref", $customer_ref, PDO::PARAM_STR);
        $purchordersInsertSQL->bindParam(":mask_date", $mask_date, PDO::PARAM_STR);
        $purchordersInsertSQL->bindParam(":authorized_by", $authorized_by, PDO::PARAM_STR);
        $purchordersInsertSQL->bindParam(":tag", $tag, PDO::PARAM_STR);
        $purchordersInsertSQL->bindParam(":authorized_date", $authorized_date, PDO::PARAM_STR);
        $purchordersInsertSQL->bindParam(":so", $so, PDO::PARAM_STR);
        $purchordersInsertSQL->bindParam(":vat_type", $vat_type, PDO::PARAM_STR);
        $purchordersInsertSQL->bindParam(":vat_percentage", $vat_percentage, PDO::PARAM_STR);
        $purchordersInsertSQL->bindParam(":ait_rate", $ait_rate, PDO::PARAM_STR);
        $purchordersInsertSQL->execute();
        $rowCount = $purchordersInsertSQL->rowCount();
        if ($rowCount === 0) {
            $response = new Response();
            $response->setHttpStatusCode(500);
            $response->setSuccess(false);
            $response->addMessage("Insert operation failed...");
            $response->send();
            exit();
        }
        if ($rowCount) {
            $orderNo = $writeDB->lastInsertId();
            $purchaseItems = $jsonData->itemInfo;
            $insertNeeded = count($purchaseItems);
            $i = 0;
            foreach ($purchaseItems as $purchaseItem) {

                $errorArr = array();

                if (isset($purchaseItem->orderno)) {
                    $intValidation = $validation->intValidation($purchaseItem->orderno);
                    if ($intValidation != "") {
                        $errorArr["orderno"] = "orderno - " . $intValidation;
                    }
                }
                if (isset($purchaseItem->itemcode)) {
                    $intValidation = $validation->intValidation($purchaseItem->itemcode);
                    if ($intValidation != "") {
                        $errorArr["itemcode"] = "itemcode - " . $intValidation;
                    }
                }
                if (isset($purchaseItem->itemdescription)) {
                    $stringValidation = $validation->stringValidation($purchaseItem->itemdescription);
                    if ($stringValidation != "") {
                        $errorArr["itemdescription"] = "itemdescription - " . $stringValidation;
                    }
                }
                if (isset($purchaseItem->glcode)) {
                    $intValidation = $validation->intValidation($purchaseItem->glcode);
                    if ($intValidation != "") {
                        $errorArr["glcode"] = "glcode - " . $intValidation;
                    }
                }
                if (isset($purchaseItem->unitprice)) {
                    $stringValidation = $validation->stringValidation($purchaseItem->unitprice);
                    if ($stringValidation != "") {
                        $errorArr["unitprice"] = "unitprice - " . $stringValidation;
                    }
                }
                if (isset($purchaseItem->quantityord)) {
                    $intValidation = $validation->intValidation($purchaseItem->quantityord);
                    if ($intValidation != "") {
                        $errorArr["quantityord"] = "quantityord - " . $intValidation;
                    }
                }
                if (count($errorArr) > 0) {
                    $returnData = array();
                    $returnData["rows_returned"] = count($errorArr);
                    $returnData["error"] = $errorArr;
                    $response = new Response();
                    $response->setHttpStatusCode(400);
                    $response->setSuccess(false);
                    $response->addMessage("Validation failed");
                    $response->setData($returnData);
                    $response->send();
                    exit;
                }
                $totalAmount = $purchaseItem->unitprice * $purchaseItem->quantityord;
                $purchorderdetails = new PurchorderDetails("", $orderNo, $purchaseItem->itemcode, $purchaseItem->itemdescription, $purchaseItem->glcode, $purchaseItem->unitprice, $purchaseItem->quantityord, $deliverydate, $totalAmount);


                $podetailitem = $purchorderdetails->getPodetailitem();
                $orderno = $purchorderdetails->getOrderno();
                $itemcode = $purchorderdetails->getItemcode();
                $itemdescription = $purchorderdetails->getItemdescription();
                $glcode = $purchorderdetails->getGlcode();
                $unitprice = $purchorderdetails->getUnitprice();
                $quantityord = $purchorderdetails->getQuantityord();
                $deliverydate = $purchorderdetails->getDeliverydate();
                $total_amount = $purchorderdetails->getTotal_amount();

                // others 
                $tender_id = 0;
                $requistion_id = 0;
                $purchorderdetailsInsertSQL = $writeDB->prepare("INSERT INTO purchorderdetails (orderno,itemcode,itemdescription,glcode,qtyinvoiced,unitprice,quantityord,deliverydate,total_quantity,total_amount,tender_id,requistion_id) VALUES (:orderno,:itemcode,:itemdescription,:glcode,:qtyinvoiced,:unitprice,:quantityord,:deliverydate,:total_quantity,:total_amount,:tender_id,:requistion_id)");
                $purchorderdetailsInsertSQL->bindParam(":orderno", $orderno, PDO::PARAM_STR);
                $purchorderdetailsInsertSQL->bindParam(":itemcode", $itemcode, PDO::PARAM_STR);
                $purchorderdetailsInsertSQL->bindParam(":itemdescription", $itemdescription, PDO::PARAM_STR);
                $purchorderdetailsInsertSQL->bindParam(":glcode", $glcode, PDO::PARAM_STR);
                $purchorderdetailsInsertSQL->bindParam(":qtyinvoiced", $quantityord, PDO::PARAM_STR);
                $purchorderdetailsInsertSQL->bindParam(":unitprice", $unitprice, PDO::PARAM_STR);
                $purchorderdetailsInsertSQL->bindParam(":quantityord", $quantityord, PDO::PARAM_STR);
                $purchorderdetailsInsertSQL->bindParam(":deliverydate", $deliverydate, PDO::PARAM_STR);
                $purchorderdetailsInsertSQL->bindParam(":total_quantity", $quantityord, PDO::PARAM_STR);
                $purchorderdetailsInsertSQL->bindParam(":total_amount", $total_amount, PDO::PARAM_STR);
                $purchorderdetailsInsertSQL->bindParam(":tender_id", $tender_id, PDO::PARAM_STR);
                $purchorderdetailsInsertSQL->bindParam(":requistion_id", $requistion_id, PDO::PARAM_STR);
                $purchorderdetailsInsertSQL->execute();
                $rowCount = $purchorderdetailsInsertSQL->rowCount();
                if ($rowCount == 1) {
                    $i++;
                }
            }
            $writeDB->commit();
            if ($insertNeeded == $i) {
                $response = new Response();
                $response->setHttpStatusCode(200);
                $response->setSuccess(true);
                $response->toCache(true);
                $response->addMessage('Purchase add success');
                $response->send();
                exit();
            }
        }
    } catch (PurchordersException $ex) {
        $response = new Response();
        $response->setHttpStatusCode(400);
        $response->setSuccess(false);
        $response->addMessage($ex->getMessage());
        $response->send();
        exit();
    } catch (PDOException $ex) {
        $writeDB->rollback();
        error_log("Database query error - " . $ex, 1);
        $response = new Response();
        $response->setHttpStatusCode(500);
        $response->setSuccess(false);
        $response->addMessage($ex->getMessage());
        $response->send();
        exit();
    }
}
//delete
elseif ($_SERVER["REQUEST_METHOD"] === "DELETE") {
    $orderno = $_GET["orderno"];
    if ($orderno === "") {
        $response = new Response();
        $response->setHttpStatusCode(400);
        $response->setSuccess(false);
        $response->addMessage("orderno cannot be blank");
        $response->send();
        exit();
    }
    try {
        $checkData = $readDB->prepare("SELECT * FROM purchorders WHERE orderno=:orderno");
        $checkData->bindParam(":orderno", $orderno, PDO::PARAM_STR);
        $checkData->execute();
        $rowCount = $checkData->rowCount();
        if ($rowCount === 0) {
            $response = new Response();
            $response->setHttpStatusCode(404);
            $response->setSuccess(false);
            $response->addMessage("Data not found");
            $response->send();
            exit();
        }
        //delete data
        $deleteSQL = $writeDB->prepare("DELETE FROM purchorders WHERE orderno=:orderno");
        $deleteSQL->bindParam(":orderno", $orderno, PDO::PARAM_STR);
        $deleteSQL->execute();
        $rowCount = $deleteSQL->rowCount();
        if ($rowCount != 0) {
            $response = new Response();
            $response->setHttpStatusCode(200);
            $response->setSuccess(true);
            $response->addMessage("Delete data successfully");
            $response->send();
            exit();
        }
    } catch (PurchordersException $ex) {
        $response = new Response();
        $response->setHttpStatusCode(400);
        $response->setSuccess(false);
        $response->addMessage($ex->getMessage());
        $response->send();
        exit();
    } catch (PDOException $ex) {
        error_log("Database query error - " . $ex, 1);
        $response = new Response();
        $response->setHttpStatusCode(500);
        $response->setSuccess(false);
        $response->addMessage($ex->getMessage());
        $response->send();
        exit();
    }
}
//update
elseif ($_SERVER["REQUEST_METHOD"] === "PUT") {
    try {
        if ($_SERVER["CONTENT_TYPE"] !== "application/json") {
            $response = new Response();
            $response->setHttpStatusCode(400);
            $response->setSuccess(false);
            $response->addMessage("Content type header is not set to JSON");
            $response->send();
            exit();
        }
        $rawPostData = file_get_contents("php://input");
        if (!$jsonData = json_decode($rawPostData)) {
            $response = new Response();
            $response->setHttpStatusCode(400);
            $response->setSuccess(false);
            $response->addMessage("Request body is not valid JSON");
            $response->send();
            exit();
        }
        $errorArr = array();
        if (isset($jsonData->orderno)) {
            $intValidation = $validation->intValidation($jsonData->orderno);
            if ($intValidation != "") {
                $errorArr["orderno"] = "orderno - " . $intValidation;
            }
        }
        if (isset($jsonData->supplierno)) {
            $intValidation = $validation->intValidation($jsonData->supplierno);
            if ($intValidation != "") {
                $errorArr["supplierno"] = "supplierno - " . $intValidation;
            }
        }
        if (isset($jsonData->orddate)) {
            $stringValidation = $validation->stringValidation($jsonData->orddate);
            if ($stringValidation != "") {
                $errorArr["orddate"] = "orddate - " . $stringValidation;
            }
        }
        if (isset($jsonData->dateprinted)) {
            $stringValidation = $validation->stringValidation($jsonData->dateprinted);
            if ($stringValidation != "") {
                $errorArr["dateprinted"] = "dateprinted - " . $stringValidation;
            }
        }
        if (isset($jsonData->initiator)) {
            $stringValidation = $validation->stringValidation($jsonData->initiator);
            if ($stringValidation != "") {
                $errorArr["initiator"] = "initiator - " . $stringValidation;
            }
        }
        if (isset($jsonData->intostocklocation)) {
            $intValidation = $validation->intValidation($jsonData->intostocklocation);
            if ($intValidation != "") {
                $errorArr["intostocklocation"] = "intostocklocation - " . $intValidation;
            }
        }
        if (isset($jsonData->deladd1)) {
            $stringValidation = $validation->stringValidation($jsonData->deladd1);
            if ($stringValidation != "") {
                $errorArr["deladd1"] = "deladd1 - " . $stringValidation;
            }
        }
        if (isset($jsonData->deliverydate)) {
            $stringValidation = $validation->stringValidation($jsonData->deliverydate);
            if ($stringValidation != "") {
                $errorArr["deliverydate"] = "deliverydate - " . $stringValidation;
            }
        }
        if (isset($jsonData->status)) {
            $stringValidation = $validation->stringValidation($jsonData->status);
            if ($stringValidation != "") {
                $errorArr["status"] = "status - " . $stringValidation;
            }
        }
        if (isset($jsonData->stat_comment)) {
            $stringValidation = $validation->stringValidation($jsonData->stat_comment);
            if ($stringValidation != "") {
                $errorArr["stat_comment"] = "stat_comment - " . $stringValidation;
            }
        }
        if (isset($jsonData->authorized_by)) {
            $stringValidation = $validation->stringValidation($jsonData->authorized_by);
            if ($stringValidation != "") {
                $errorArr["authorized_by"] = "authorized_by - " . $stringValidation;
            }
        }
        if (isset($jsonData->authorized_date)) {
            $stringValidation = $validation->stringValidation($jsonData->authorized_date);
            if ($stringValidation != "") {
                $errorArr["authorized_date"] = "authorized_date - " . $stringValidation;
            }
        }
        if (count($errorArr) > 0) {
            $returnData = array();
            $returnData["rows_returned"] = count($errorArr);
            $returnData["error"] = $errorArr;
            $response = new Response();
            $response->setHttpStatusCode(400);
            $response->setSuccess(false);
            $response->addMessage("Validation failed");
            $response->setData($returnData);
            $response->send();
            exit;
        }
        $purchorders = new Purchorders($jsonData->orderno, $jsonData->supplierno, $jsonData->orddate, $jsonData->dateprinted, $jsonData->initiator, $jsonData->intostocklocation, $jsonData->deladd1, $jsonData->deliverydate, $jsonData->status, $jsonData->stat_comment, $jsonData->authorized_by, $jsonData->authorized_date, 0);
        $orderno = $purchorders->getOrderno();
        $supplierno = $purchorders->getSupplierno();
        $orddate = $purchorders->getOrddate();
        $dateprinted = $purchorders->getDateprinted();
        $initiator = $purchorders->getInitiator();
        $intostocklocation = $purchorders->getIntostocklocation();
        $deladd1 = $purchorders->getDeladd1();
        $deliverydate = $purchorders->getDeliverydate();
        $status = $purchorders->getStatus();
        $stat_comment = $purchorders->getStat_comment();
        $authorized_by = $purchorders->getAuthorized_by();
        $authorized_date = $purchorders->getAuthorized_date();
        $checkData = $readDB->prepare("SELECT * FROM purchorders WHERE orderno=:orderno");
        $checkData->bindParam(":orderno", $orderno, PDO::PARAM_STR);
        $checkData->execute();
        $rowCount = $checkData->rowCount();
        if ($rowCount === 0) {
            $response = new Response();
            $response->setHttpStatusCode(404);
            $response->setSuccess(false);
            $response->addMessage("Data not found");
            $response->send();
            exit();
        }
        $mainQuery = "UPDATE purchorders SET ";
        if ($supplierno != "") {
            $mainQuery = $mainQuery . "supplierno=:supplierno,";
        }
        if ($orddate != "") {
            $mainQuery = $mainQuery . "orddate=:orddate,";
        }
        if ($dateprinted != "") {
            $mainQuery = $mainQuery . "dateprinted=:dateprinted,";
        }
        if ($initiator != "") {
            $mainQuery = $mainQuery . "initiator=:initiator,";
        }
        if ($intostocklocation != "") {
            $mainQuery = $mainQuery . "intostocklocation=:intostocklocation,";
        }
        if ($deladd1 != "") {
            $mainQuery = $mainQuery . "deladd1=:deladd1,";
        }
        if ($deliverydate != "") {
            $mainQuery = $mainQuery . "deliverydate=:deliverydate,";
        }
        if ($status != "") {
            $mainQuery = $mainQuery . "status=:status,";
        }
        if ($stat_comment != "") {
            $mainQuery = $mainQuery . "stat_comment=:stat_comment,";
        }
        if ($authorized_by != "") {
            $mainQuery = $mainQuery . "authorized_by=:authorized_by,";
        }
        if ($authorized_date != "") {
            $mainQuery = $mainQuery . "authorized_date=:authorized_date,";
        }
        $mainQuery = substr($mainQuery, 0, -1) . " WHERE orderno=:orderno";
        $updateSql = $writeDB->prepare($mainQuery);
        if ($orderno != "") {
            $updateSql->bindParam("orderno", $orderno, PDO::PARAM_STR);
        }
        if ($supplierno != "") {
            $updateSql->bindParam("supplierno", $supplierno, PDO::PARAM_STR);
        }
        if ($orddate != "") {
            $updateSql->bindParam("orddate", $orddate, PDO::PARAM_STR);
        }
        if ($dateprinted != "") {
            $updateSql->bindParam("dateprinted", $dateprinted, PDO::PARAM_STR);
        }
        if ($initiator != "") {
            $updateSql->bindParam("initiator", $initiator, PDO::PARAM_STR);
        }
        if ($intostocklocation != "") {
            $updateSql->bindParam("intostocklocation", $intostocklocation, PDO::PARAM_STR);
        }
        if ($deladd1 != "") {
            $updateSql->bindParam("deladd1", $deladd1, PDO::PARAM_STR);
        }
        if ($deliverydate != "") {
            $updateSql->bindParam("deliverydate", $deliverydate, PDO::PARAM_STR);
        }
        if ($status != "") {
            $updateSql->bindParam("status", $status, PDO::PARAM_STR);
        }
        if ($stat_comment != "") {
            $updateSql->bindParam("stat_comment", $stat_comment, PDO::PARAM_STR);
        }
        if ($authorized_by != "") {
            $updateSql->bindParam("authorized_by", $authorized_by, PDO::PARAM_STR);
        }
        if ($authorized_date != "") {
            $updateSql->bindParam("authorized_date", $authorized_date, PDO::PARAM_STR);
        }
        $updateSql->execute();
        $rowCount = $updateSql->rowCount();
        if ($rowCount == 1) {
            $response = new Response();
            $response->setHttpStatusCode(200);
            $response->setSuccess(true);
            $response->addMessage("Data Update success.");
            $response->send();
            exit();
        } else {
            $response = new Response();
            $response->setHttpStatusCode(400);
            $response->setSuccess(false);
            $response->addMessage("Nothing to change for update!!!");
            $response->send();
            exit();
        }
    } catch (PurchordersException $ex) {
        $response = new Response();
        $response->setHttpStatusCode(400);
        $response->setSuccess(false);
        $response->addMessage($ex->getMessage());
        $response->send();
        exit();
    } catch (PDOException $ex) {
        error_log("Database query error - " . $ex, 1);
        $response = new Response();
        $response->setHttpStatusCode(500);
        $response->setSuccess(false);
        $response->addMessage($ex->getMessage());
        $response->send();
        exit();
    }
}
