<?php
include_once('../../db.php');
include_once('../../../model/admin/orders/createorderModel.php');
include_once('../../../model/response.php');
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

if ($_SERVER['REQUEST_METHOD'] === "POST") {
    try {
        if ($_SERVER['CONTENT_TYPE'] !== 'application/json') {
            $response = new Response();
            $response->setHttpStatusCode(400);
            $response->setSuccess(false);
            $response->addMessage("Content type header is not set to JSON");
            $response->send();
            exit();
        }
        $rawPostData = file_get_contents('php://input');
        if (!$jsonData = json_decode($rawPostData)) {
            $response = new Response();
            $response->setHttpStatusCode(400);
            $response->setSuccess(false);
            $response->addMessage("Request body is not valid JSON");
            $response->send();
            exit();
        }
        $orderNumber = rand(1000, 9999); //// 30 auto order no code
        $newOrder = new CreateOrderInfo($jsonData->id, (isset($jsonData->additionalInfo) ? $jsonData->additionalInfo : null), $jsonData->deliveryTime, ($jsonData->otherCharge) ? $jsonData->otherCharge : null);
        $id = $newOrder->getId();
        $additionalInfo = $newOrder->getAdditionalInfo();
        $deliveryTime = $newOrder->getDeliveryTime();
        $otherCharge = $newOrder->getOtherCharge();

        $findUserData = $readDB->prepare('SELECT * FROM debtorsmaster WHERE debtorno=:id');
        $findUserData->bindParam(':id', $id, PDO::PARAM_STR);
        $findUserData->execute();
        $rowCount = $findUserData->rowCount();
        if ($rowCount === 1) {
            $userInfo = $findUserData->fetch(PDO::FETCH_ASSOC);
            $userId = $id;
            $delivery_status = 0;
            $orderDate = date("Y-m-d H:i:s");
            $branchcode = 1;
            $customerref = "SO: $orderNumber";
            $tag = 1;
            $ordertype = 'DP';
            $shipvia = 1;
            $deliverto = 'main';
            $deliverblind = 1;
            $fromstkloc = 1010;
            $printedpackingslip = 0;
            $delivery_status = 0;
            $address = $userInfo['address1'];
            $phone = $userInfo['phone1'];

            try {
                $writeDB->beginTransaction();
                $orderInsert = $writeDB->prepare('INSERT INTO salesorders(orderno, debtorno,comments, branchcode, customerref, tag,  orddate, ordertype, shipvia, deladd1, contactphone, deliverto, deliverblind,freightcost, fromstkloc, deliverydate, printedpackingslip,delivery_status,issue_date) VALUES (:orderno, :debtorno, :comments, :branchcode, :customerref, :tag,:orddate,:ordertype, :shipvia,:deladd1,:contactphone,:deliverto,:deliverblind,:freightcost,:fromstkloc,:deliverydate,:printedpackingslip,:delivery_status,:issue_date)');
                $orderInsert->bindParam(':orderno', $orderNumber, PDO::PARAM_STR);
                $orderInsert->bindParam(':debtorno', $userId, PDO::PARAM_STR);
                $orderInsert->bindParam(':comments', $additionalInfo, PDO::PARAM_STR);
                $orderInsert->bindParam(':branchcode', $branchcode, PDO::PARAM_STR);
                $orderInsert->bindParam(':customerref', $customerref, PDO::PARAM_STR);
                $orderInsert->bindParam(':tag', $tag, PDO::PARAM_STR);
                $orderInsert->bindParam(':orddate', $orderDate, PDO::PARAM_STR);
                $orderInsert->bindParam(':ordertype', $ordertype, PDO::PARAM_STR);
                $orderInsert->bindParam(':shipvia', $shipvia, PDO::PARAM_STR);
                $orderInsert->bindParam(':deladd1', $address, PDO::PARAM_STR);
                $orderInsert->bindParam(':contactphone', $phone, PDO::PARAM_STR);
                $orderInsert->bindParam(':deliverto', $deliverto, PDO::PARAM_STR);
                $orderInsert->bindParam(':deliverblind', $deliverblind, PDO::PARAM_STR);
                $orderInsert->bindParam(':freightcost', $otherCharge, PDO::PARAM_STR);
                $orderInsert->bindParam(':fromstkloc', $fromstkloc, PDO::PARAM_STR);
                $orderInsert->bindParam(':deliverydate', $deliveryTime, PDO::PARAM_STR);
                $orderInsert->bindParam(':printedpackingslip', $printedpackingslip, PDO::PARAM_STR);
                $orderInsert->bindParam(':delivery_status', $delivery_status, PDO::PARAM_STR);
                $orderInsert->bindParam(':issue_date', $orderDate, PDO::PARAM_STR);
                $orderInsert->execute();
                $rowCount = $orderInsert->rowCount();
                if ($rowCount == 1) {
                    $orderItem = $jsonData->itemInfo;
                    $stkcode = 0;
                    $discount_amount = 0;
                    $qtyinvoiced = 0;
                    $org_so_qty = 0;
                    $orderlineno = 1;
                    foreach ($orderItem as $itemValue) {
                        $itemInfo = new OrderItem($itemValue->productID, $itemValue->unitPrice, $itemValue->productQuantity);

                        $itemId = $itemInfo->getProductId();
                        $itemQuantity = $itemInfo->getProductQuantity();
                        $unitPrice = $itemInfo->getUnitPrice();
                        $addItem = $writeDB->prepare('INSERT INTO salesorderdetails(orderlineno, orderno, stkcode, qtyinvoiced, unitprice, quantity,discount_amount,org_so_qty) VALUES (:orderlineno,:orderno,:stkcode,:qtyinvoiced,:unitprice,:quantity,:discount_amount,:org_so_qty)');
                        $addItem->bindParam(':orderlineno', $orderlineno, PDO::PARAM_STR);
                        $addItem->bindParam(':orderno', $orderNumber, PDO::PARAM_STR);
                        $addItem->bindParam(':stkcode', $itemId, PDO::PARAM_STR);
                        $addItem->bindParam(':qtyinvoiced', $qtyinvoiced, PDO::PARAM_STR);
                        $addItem->bindParam(':unitprice', $unitPrice, PDO::PARAM_STR);
                        $addItem->bindParam(':quantity', $itemQuantity, PDO::PARAM_STR);
                        $addItem->bindParam(':discount_amount', $discount_amount, PDO::PARAM_STR);
                        $addItem->bindParam(':org_so_qty', $org_so_qty, PDO::PARAM_STR);

                        $addItem->execute();
                        $rowCount = $addItem->rowCount();
                        $orderlineno++;
                    }
                    $writeDB->commit();
                    $response = new Response();
                    $response->setHttpStatusCode(200);
                    $response->setSuccess(true);
                    $response->toCache(true);
                    $response->addMessage("Order Create Success.");
                    $response->send();
                    exit();
                }
            } catch (OrderException $ex) {
                $response = new Response();
                $response->setHttpStatusCode(400);
                $response->setSuccess(false);
                $response->addMessage($ex->getMessage());
                $response->send();
                exit();
            } catch (PDOException $ex) {
                $writeDB->rollback();
                error_log("Database query error." . $ex, 0);
                $response = new Response();
                $response->setHttpStatusCode(500);
                $response->setSuccess(false);
                $response->addMessage($ex->getMessage());
                $response->send();
                exit();
            }
        } else {
            $response = new Response();
            $response->setHttpStatusCode(401);
            $response->setSuccess(false);
            $response->addMessage("Cannot find the user.");
            $response->send();
            exit();
        }
    } catch (OrderException $ex) {
        $response = new Response();
        $response->setHttpStatusCode(400);
        $response->setSuccess(false);
        $response->addMessage($ex->getMessage());
        $response->send();
        exit();
    } catch (PDOException $ex) {
        error_log("Database query error." . $ex, 0);
        $response = new Response();
        $response->setHttpStatusCode(500);
        $response->setSuccess(false);
        $response->addMessage($ex->getMessage());
        $response->send();
        exit();
    }
} elseif ($_SERVER['REQUEST_METHOD'] === "GET") {
    $mainQuery = "SELECT salesorders.orderno, debtorsmaster.name, salesorders.debtorno, salesorders.comments, salesorders.deladd1, salesorders.contactphone, salesorders.freightcost, salesorders.deliverydate, salesorders.so_status, salesorders.delivery_status, salesorders.orddate, salesorders.issue_date FROM `salesorders` INNER JOIN debtorsmaster ON debtorsmaster.debtorno=salesorders.debtorno ";
    if (array_key_exists('orderno', $_GET)) {
        $orderno = $_GET['orderno'];
        if ($orderno == '' || !is_numeric($orderno)) {
            $response = new Response();
            $response->setHttpStatusCode(400);
            $response->setSuccess(false);
            $response->addMessage('Order no number be numeric and not null.');
            $response->send();
            exit();
        }
        $mainQuery .= "WHERE  salesorders.orderno=:orderno ";
    }
    $subConditionalSubQuery = '';
    if (array_key_exists('searchTxt', $_GET)) {
        $searchTxt = $_GET['searchTxt'];

        if ($searchkey === '') {
            $response = new Response();
            $response->setHttpStatusCode(403);
            $response->setSuccess(false);
            $response->addMessage('Search string missing, its not be null. ');
            $response->send();
            exit;
        }
        if ($subConditionalSubQuery == '') {
            $subConditionalSubQuery = ' WHERE ';
        }
        $searchKeywordList = explode(' ', $searchTxt);
        foreach ($searchKeywordList as $searchKey) {
            $textsearchQury .= "salesorders.contactphone LIKE '%" . $searchKey . "%' OR debtorsmaster.name LIKE '%" . $searchKey . "%' OR ";
        }
        $mainQuery = $mainQuery . $subConditionalSubQuery . ' (' . rtrim($textsearchQury, 'OR ') . ')';
    }
    if (array_key_exists('or_start_date', $_GET) || array_key_exists('or_end_date', $_GET)) {
        $startDate = date("Y-m-d", strtotime($_GET['or_start_date']));
        $endDate = date("Y-m-d", strtotime($_GET['or_end_date']));
        if ($startDate == '' || $endDate == '') {
            $response = new Response();
            $response->setHttpStatusCode(403);
            $response->setSuccess(false);
            $response->addMessage('Please provide start and end date. ');
            $response->send();
            exit;
        }
        if ($subConditionalSubQuery == '') {
            $subConditionalSubQuery = ' WHERE ';
        } else {
            $subConditionalSubQuery = " AND ";
        }
        $dateRangeQuery = ' (salesorders.orddate >="' . $startDate . '" AND salesorders.orddate <="' . $endDate . '") ';
        $mainQuery = $mainQuery . $subConditionalSubQuery . $dateRangeQuery;
    }
    if (array_key_exists('start_date', $_GET) || array_key_exists('end_date', $_GET)) {
        $startDate = date("Y-m-d H:s:i", strtotime($_GET['start_date'] . ' 00:00:00'));
        $endDate = date("Y-m-d H:s:i", strtotime($_GET['end_date'] . ' 23:59:59'));
        if ($startDate == '' || $endDate == '') {
            $response = new Response();
            $response->setHttpStatusCode(403);
            $response->setSuccess(false);
            $response->addMessage('Please provide start and end date. ');
            $response->send();
            exit;
        }
        if ($subConditionalSubQuery == '') {
            $subConditionalSubQuery = ' WHERE ';
        } else {
            $subConditionalSubQuery = " AND ";
        }
        $dateRangeQuery = ' (salesorders.issue_date >="' . $startDate . '" AND salesorders.issue_date <="' . $endDate . '") ';
        $mainQuery = $mainQuery . $subConditionalSubQuery . $dateRangeQuery;
    }
    if (array_key_exists('status', $_GET)) {
        $status = $_GET['status'];
        if ($status == '' || !is_numeric($status)) {
            $response = new Response();
            $response->setHttpStatusCode(400);
            $response->setSuccess(false);
            $response->addMessage('Status number be numeric and not null.');
            $response->send();
            exit();
        }
        if ($subConditionalSubQuery == '') {
            $subConditionalSubQuery = ' WHERE ';
        } else {
            $subConditionalSubQuery = " AND ";
        }
        $statusQuery = ' salesorders.so_status=' . $status;
        $mainQuery = $mainQuery . $subConditionalSubQuery . $statusQuery;
    }
    $mainQuery = $mainQuery . ' ORDER BY salesorders.issue_date DESC ';
    $orderSQL = $readDB->prepare($mainQuery);
    if ($orderno != '') {
        $orderSQL->bindParam(':orderno', $orderno, PDO::PARAM_STR);
    }
    $orderSQL->execute();
    $rowCount = $orderSQL->rowCount();
    if ($rowCount > 0) {
        $ordersArr = array();
        while ($orderRow = $orderSQL->fetch(PDO::FETCH_ASSOC)) {
            $orderNo = $orderRow['orderno'];
            $totalPrice = 0;
            $priceSql = $readDB->prepare('SELECT `quantity`,unitprice FROM `salesorderdetails` WHERE `orderno`=:orderno');
            $priceSql->bindParam(':orderno', $orderNo, PDO::PARAM_STR);
            $priceSql->execute();
            $rowCount = $priceSql->rowCount();
            if ($rowCount > 0) {
                while ($itemDetailsRow = $priceSql->fetch(PDO::FETCH_ASSOC)) {
                    $totalPrice += $itemDetailsRow['quantity'] * $itemDetailsRow['unitprice'];
                }
            }

            $orderInfo = new Orders($orderRow['orderno'], $orderRow['name'], $orderRow['debtorno'], $orderRow['comments'], $orderRow['deladd1'], $orderRow['contactphone'], $orderRow['freightcost'], $orderRow['deliverydate'], $orderRow['so_status'], $orderRow['delivery_status'], $orderRow['orddate'], $orderRow['issue_date'], $totalPrice);
            $ordersArr[] = $orderInfo->OrderReturnArray();
        }
        $returnArray = array();
        $returnArray['rows_returned'] = $rowCount;
        $returnArray['orders'] = $ordersArr;
        $response = new Response();
        $response->setHttpStatusCode(200);
        $response->toCache(true);
        $response->setSuccess(true);
        $response->setData($returnArray);
        $response->send();
        exit;
    } else {
        $response = new Response();
        $response->setHttpStatusCode(404);
        $response->setSuccess(false);
        $response->addMessage("No data found.");
        $response->send();
        exit();
    }
} else {
    $response = new Response();
    $response->setHttpStatusCode(405);
    $response->setSuccess(false);
    $response->addMessage("Request method not allowed.");
    $response->send();
    exit();
}
