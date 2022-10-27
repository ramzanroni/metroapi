<?php
include_once('../../db.php');
include_once('../../../model/admin/supplier/supplierModel.php');
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
        $supplier = new Supplier('', '', $jsonData->suppname, $jsonData->address1, $jsonData->address2, $jsonData->address3, $jsonData->phn, 1);
        $suppname = $supplier->getSuppname();
        $address1 = $supplier->getAddress1();
        $address2 = $supplier->getAddress2();
        $address3 = $supplier->getAddress3();
        $phn = $supplier->getPhn();
        $status = $supplier->getStatus();

        // find max code 
        $findMaxCode = $readDB->prepare('SELECT MAX(code) as code FROM `suppliers`');
        $findMaxCode->execute();
        $fetchMaxCode = $findMaxCode->fetch(PDO::FETCH_ASSOC);
        $code = $fetchMaxCode['code'] + 1;

        // insert supplier 

        $currcode = "BDT";
        $suppliersince = gmdate('Y-m-d H:i:s');
        $paymentterms = "CA";
        $bankact = '';
        $bankref = 0;
        $bankpartics = 0;
        $remittance = 0;
        $taxgroupid = 1;
        $taxref = '';
        $port = '';
        $updated_at = "0000-00-00 00:00:00";
        $updated_by = 0;
        $bin_no = '';
        $nid_no = '';
        $inv_ser_com = 1;
        $insertSupplier = $writeDB->prepare("INSERT INTO `suppliers`(`code`,`suppname`,`address1`,`address2`,`address3`,`currcode`,`suppliersince`,`paymentterms`,`bankact`,`bankref`,`bankpartics`,`remittance`,`taxgroupid`,`taxref`,`phn`,`port`,`updated_at`,`updated_by`,`status`,`bin_no`,`nid_no`,`inv_ser_com`) VALUES (:code,:suppname,:address1,:address2, :address3, :currcode,:suppliersince,:paymentterms,:bankact,:bankref,:bankpartics,:remittance,:taxgroupid,:taxref,:phn,:port,:updated_at,:updated_by,:status,:bin_no,:nid_no,:inv_ser_com)");
        $insertSupplier->bindParam(':code', $code, PDO::PARAM_STR);
        $insertSupplier->bindParam(':suppname', $suppname, PDO::PARAM_STR);
        $insertSupplier->bindParam(':address1', $address1, PDO::PARAM_STR);
        $insertSupplier->bindParam(':address2', $address2, PDO::PARAM_STR);
        $insertSupplier->bindParam(':address3', $address3, PDO::PARAM_STR);
        $insertSupplier->bindParam(':currcode', $currcode, PDO::PARAM_STR);
        $insertSupplier->bindParam(':suppliersince', $suppliersince, PDO::PARAM_STR);
        $insertSupplier->bindParam(':paymentterms', $paymentterms, PDO::PARAM_STR);
        $insertSupplier->bindParam(':bankact', $bankact, PDO::PARAM_STR);
        $insertSupplier->bindParam(':bankref', $bankref, PDO::PARAM_STR);
        $insertSupplier->bindParam(':bankpartics', $bankpartics, PDO::PARAM_STR);
        $insertSupplier->bindParam(':remittance', $remittance, PDO::PARAM_STR);
        $insertSupplier->bindParam(':taxgroupid', $taxgroupid, PDO::PARAM_STR);
        $insertSupplier->bindParam(':taxref', $taxref, PDO::PARAM_STR);
        $insertSupplier->bindParam(':phn', $phn, PDO::PARAM_STR);
        $insertSupplier->bindParam(':port', $port, PDO::PARAM_STR);
        $insertSupplier->bindParam(':updated_at', $updated_at, PDO::PARAM_STR);
        $insertSupplier->bindParam(':updated_by', $updated_by, PDO::PARAM_STR);
        $insertSupplier->bindParam(':status', $status, PDO::PARAM_STR);
        $insertSupplier->bindParam(':bin_no', $bin_no, PDO::PARAM_STR);
        $insertSupplier->bindParam(':nid_no', $nid_no, PDO::PARAM_STR);
        $insertSupplier->bindParam(':inv_ser_com', $inv_ser_com, PDO::PARAM_STR);
        $insertSupplier->execute();
        $rowCount = $insertSupplier->rowCount();
        if ($rowCount === 1) {
            // insert supplier branch 
            $supplier_id = $writeDB->lastInsertId();
            $lastBranchCode = $readDB->prepare('SELECT MAX(branchcode) as branchcode FROM `supbranch`');
            $lastBranchCode->execute();
            $fetchLastBranchCode = $lastBranchCode->fetch(PDO::FETCH_ASSOC);
            $branchcode = $fetchLastBranchCode['branchcode'] + 1;

            $lat = "0.000000";
            $lng = "0.000000";
            $estdeliverydays = "0";
            $area = "2780";
            $salesman = '1';
            $fwddate = "0";
            $defaultlocation = "1010";
            $taxgroupid = "1";
            $defaultshipvia = "1";
            $deliverblind = "1";
            $disabletrans = "0";
            $branchdistance = "0.00";
            $travelrate = "0.00";
            $businessunit = "1";
            $emi = "0";
            $esd = "0000-00-00";
            $branchstatus = "1";
            $tag = "1";
            $op_bal = "0";
            $aggrigate_ap = "1";
            $specialinstructions = '';
            $branchsince = gmdate('Y-m-d H:i:s');
            $insertSupplierBranch = $writeDB->prepare('INSERT INTO supbranch(branchcode, supplier_id, brname, braddress1, braddress2, braddress3, lat, lng, estdeliverydays, area,  fwddate, phoneno, defaultlocation, taxgroupid, defaultshipvia, deliverblind, disabletrans,specialinstructions, branchdistance, travelrate, businessunit, emi, esd, branchsince, branchstatus, tag, op_bal, aggrigate_ap)VALUES(:branchcode, :supplier_id, :brname, :braddress1,:braddress2,:braddress3, :lat, :lng, :estdeliverydays, :area, :fwddate, :phoneno, :defaultlocation, :taxgroupid, :defaultshipvia, :deliverblind, :disabletrans, :specialinstructions, :branchdistance, :travelrate, :businessunit, :emi, :esd, :branchsince, :branchstatus, :tag, :op_bal, :aggrigate_ap)');
            $insertSupplierBranch->bindParam(':branchcode', $branchcode, PDO::PARAM_STR);
            $insertSupplierBranch->bindParam(':supplier_id', $supplier_id, PDO::PARAM_STR);
            $insertSupplierBranch->bindParam(':brname', $suppname, PDO::PARAM_STR);
            $insertSupplierBranch->bindParam(':braddress1', $address1, PDO::PARAM_STR);
            $insertSupplierBranch->bindParam(':braddress2', $address2, PDO::PARAM_STR);
            $insertSupplierBranch->bindParam(':braddress3', $address3, PDO::PARAM_STR);
            $insertSupplierBranch->bindParam(':lat', $lat, PDO::PARAM_STR);
            $insertSupplierBranch->bindParam(':lng', $lng, PDO::PARAM_STR);
            $insertSupplierBranch->bindParam(':estdeliverydays', $estdeliverydays, PDO::PARAM_STR);
            $insertSupplierBranch->bindParam(':area', $area, PDO::PARAM_STR);
            $insertSupplierBranch->bindParam(':fwddate', $fwddate, PDO::PARAM_STR);
            $insertSupplierBranch->bindParam(':phoneno', $phn, PDO::PARAM_STR);
            $insertSupplierBranch->bindParam(':defaultlocation', $defaultlocation, PDO::PARAM_STR);
            $insertSupplierBranch->bindParam(':taxgroupid', $taxgroupid, PDO::PARAM_STR);
            $insertSupplierBranch->bindParam(':defaultshipvia', $defaultshipvia, PDO::PARAM_STR);
            $insertSupplierBranch->bindParam(':deliverblind', $deliverblind, PDO::PARAM_STR);
            $insertSupplierBranch->bindParam(':disabletrans', $disabletrans, PDO::PARAM_STR);
            $insertSupplierBranch->bindParam(':specialinstructions', $specialinstructions, PDO::PARAM_STR);
            $insertSupplierBranch->bindParam(':branchdistance', $branchdistance, PDO::PARAM_STR);
            $insertSupplierBranch->bindParam(':travelrate', $travelrate, PDO::PARAM_STR);
            $insertSupplierBranch->bindParam(':businessunit', $businessunit, PDO::PARAM_STR);
            $insertSupplierBranch->bindParam(':emi', $emi, PDO::PARAM_STR);
            $insertSupplierBranch->bindParam(':esd', $esd, PDO::PARAM_STR);
            $insertSupplierBranch->bindParam(':branchsince', $branchsince, PDO::PARAM_STR);
            $insertSupplierBranch->bindParam(':branchstatus', $branchstatus, PDO::PARAM_STR);
            $insertSupplierBranch->bindParam(':tag', $tag, PDO::PARAM_STR);
            $insertSupplierBranch->bindParam(':op_bal', $op_bal, PDO::PARAM_STR);
            $insertSupplierBranch->bindParam(':aggrigate_ap', $aggrigate_ap, PDO::PARAM_STR);
            $insertSupplierBranch->execute();
            $rowCount = $insertSupplierBranch->rowCount();
            if ($rowCount) {
                $response = new Response();
                $response->setHttpStatusCode(200);
                $response->setSuccess(true);
                $response->toCache(true);
                $response->addMessage("Add Supplier Success");
                $response->send();
                exit;
            }
        }
    } catch (SupplierException $ex) {
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
    if (array_key_exists('supplierid', $_GET)) {
        $supplierid = $_GET['supplierid'];
        if ($supplierid === '') {
            $response = new Response();
            $response->setHttpStatusCode(400);
            $response->setSuccess(false);
            $response->addMessage("Supplier ID cannot be blank");
            $response->send();
            exit();
        }
        $suppliers = $readDB->prepare('SELECT * FROM `suppliers` WHERE supplierid=:supplierid ORDER BY supplierid DESC');
        $suppliers->bindParam(':supplierid', $supplierid, PDO::PARAM_STR);
    } elseif (array_key_exists('phone', $_GET)) {
        $phone = $_GET['phone'];
        if ($phone === '') {
            $response = new Response();
            $response->setHttpStatusCode(400);
            $response->setSuccess(false);
            $response->addMessage("phone cannot be blank");
            $response->send();
            exit();
        }
        $suppliers = $readDB->prepare("SELECT * FROM `suppliers` WHERE phn LIKE '%" . $phone . "%' ORDER BY supplierid DESC");
    } elseif (array_key_exists('name', $_GET)) {
        $name = $_GET['name'];
        if ($name == '') {
            $response = new Response();
            $response->setHttpStatusCode(400);
            $response->setSuccess(false);
            $response->addMessage('Name can not be null.');
            $response->send();
            exit();
        }
        $subQry = "SELECT * FROM suppliers WHERE ";
        $textsearchQury = '';

        $searchKeywordList = explode(' ', $name);

        foreach ($searchKeywordList as $searchKey) {
            $textsearchQury .= "suppname LIKE '%" . $searchKey . "%' OR ";
        }
        $textsearchQury = $subQry . rtrim($textsearchQury, 'OR ');
        $suppliers = $readDB->prepare($textsearchQury);
    } elseif (array_key_exists('all', $_GET)) {
        $string = $_GET['all'];
        if ($string == '') {
            $response = new Response();
            $response->setHttpStatusCode(400);
            $response->setSuccess(false);
            $response->addMessage('search item can not be null.');
            $response->send();
            exit();
        }
        $subQry = "SELECT * FROM suppliers WHERE ";
        $textsearchQury = '';

        $searchKeywordList = explode(' ', $string);
        foreach ($searchKeywordList as $searchKey) {
            $textsearchQury .= "suppname LIKE '%" . $searchKey . "%' OR phn LIKE '%" . $searchKey . "%' OR ";
        }
        $textsearchQury = $subQry . rtrim($textsearchQury, 'OR ') . " ORDER BY suppname ASC";
        $suppliers = $readDB->prepare($textsearchQury);
    } else {
        $suppliers = $readDB->prepare('SELECT * FROM `suppliers` ORDER BY supplierid DESC');
    }
    $suppliers->execute();
    $rowCount = $suppliers->rowCount();
    $supplierArr = array();
    while ($supplierRow = $suppliers->fetch(PDO::FETCH_ASSOC)) {
        $supplierData = new Supplier($supplierRow['supplierid'], $supplierRow['code'], $supplierRow['suppname'], $supplierRow['address1'], $supplierRow['address2'], $supplierRow['address3'], $supplierRow['phn'], $supplierRow['status']);
        $supplierArr[] = $supplierData->returnSupplierArray();
    }
    $returnArray = array();
    $returnArray['row_returned'] = $rowCount;
    $returnArray['suppliers'] = $supplierArr;
    $response = new Response();
    $response->setHttpStatusCode(200);
    $response->setSuccess(true);
    $response->toCache(true);
    $response->setData($returnArray);
    $response->send();
    exit;
} elseif ($_SERVER['REQUEST_METHOD'] === "PUT") {
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
        $supplier = new Supplier($jsonData->supplierid, '', $jsonData->suppname, $jsonData->address1, $jsonData->address2, $jsonData->address3, $jsonData->phn, 1);
        $supplierid = $supplier->getSupplierid();
        $suppname = $supplier->getSuppname();
        $address1 = $supplier->getAddress1();
        $address2 = $supplier->getAddress2();
        $address3 = $supplier->getAddress3();
        $phn = $supplier->getPhn();
        $status = $supplier->getStatus();


        // check supplier  

        $checkSupplier = $readDB->prepare('SELECT * FROM `suppliers` WHERE `supplierid`=:supplierid');
        $checkSupplier->bindParam(':supplierid', $supplierid, PDO::PARAM_STR);
        $checkSupplier->execute();
        $rowCount = $checkSupplier->rowCount();
        if ($rowCount === 1) {
            $updated_at = gmdate('Y-m-d H:i:s');
            $updated_by = 0;
            // update supplier 
            $updateSupplier = $writeDB->prepare('UPDATE `suppliers` SET `suppname`=:suppname,`address1`=:address1,`address2`=:address2,`address3`=:address3,`phn`=:phn,`updated_at`=:updated_at,`updated_by`=:updated_by,`status`=:status WHERE `supplierid`=:supplierid');
            $updateSupplier->bindParam(':suppname', $suppname, PDO::PARAM_STR);
            $updateSupplier->bindParam(':address1', $address1, PDO::PARAM_STR);
            $updateSupplier->bindParam(':address2', $address2, PDO::PARAM_STR);
            $updateSupplier->bindParam(':address3', $address3, PDO::PARAM_STR);
            $updateSupplier->bindParam(':phn', $phn, PDO::PARAM_STR);
            $updateSupplier->bindParam(':updated_at', $updated_at, PDO::PARAM_STR);
            $updateSupplier->bindParam(':updated_by', $updated_by, PDO::PARAM_STR);
            $updateSupplier->bindParam(':status', $status, PDO::PARAM_STR);
            $updateSupplier->bindParam(':supplierid', $supplierid, PDO::PARAM_STR);
            $updateSupplier->execute();
            $rowCount = $updateSupplier->rowCount();
            if ($rowCount === 1) {
                // update supplier branch 
                $updateSupplierBranch = $writeDB->prepare('UPDATE `supbranch` SET `brname`=:brname,`braddress1`=:braddress1,`braddress2`=:braddress2,`braddress3`=:braddress3,`phoneno`=:phoneno WHERE `supplier_id`=:supplier_id');
                $updateSupplierBranch->bindParam(':brname', $suppname, PDO::PARAM_STR);
                $updateSupplierBranch->bindParam(':braddress1', $address1, PDO::PARAM_STR);
                $updateSupplierBranch->bindParam(':braddress2', $address2, PDO::PARAM_STR);
                $updateSupplierBranch->bindParam(':braddress3', $address3, PDO::PARAM_STR);
                $updateSupplierBranch->bindParam(':phoneno', $phn, PDO::PARAM_STR);
                $updateSupplierBranch->bindParam(':supplier_id', $supplierid, PDO::PARAM_STR);
                $updateSupplierBranch->execute();
                $rowCount = $updateSupplierBranch->rowCount();
                if ($rowCount === 1) {
                    $response = new Response();
                    $response->setHttpStatusCode(200);
                    $response->setSuccess(true);
                    $response->toCache(true);
                    $response->addMessage("Update success.");
                    $response->send();
                    exit;
                } else {
                    $response = new Response();
                    $response->setHttpStatusCode(400);
                    $response->setSuccess(false);
                    $response->addMessage("Nothing to be updated");
                    $response->send();
                    exit;
                }
            } else {
                $response = new Response();
                $response->setHttpStatusCode(400);
                $response->setSuccess(false);
                $response->addMessage("Nothing to be updated");
                $response->send();
                exit;
            }
        }
    } catch (SupplierException $ex) {
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
    }
} else {
    $response = new Response();
    $response->setHttpStatusCode(405);
    $response->setSuccess(false);
    $response->addMessage("Request method not allowed.");
    $response->send();
    exit();
}
