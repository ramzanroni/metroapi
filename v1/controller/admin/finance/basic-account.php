<?php
include_once('../../db.php');
include_once('../../../model/admin/finance/basic-accountModel.php');
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
        $basicAccount = new BasicAccountAdd(null, $jsonData->accountname, '');
        $accountname = $basicAccount->getAccountname();
        // check account name
        $checkAccount = $readDB->prepare('SELECT * FROM chartmaster WHERE accountname=:accountname');
        $checkAccount->bindParam(':accountname', $accountname, PDO::PARAM_STR);
        $checkAccount->execute();
        $rowCount = $checkAccount->rowCount();
        if ($rowCount > 0) {
            $response = new Response();
            $response->setHttpStatusCode(401);
            $response->setSuccess(false);
            $response->addMessage('This account name already exist.');
            $response->send();
            exit;
        }
        // get the max account code 
        $maxSql = $readDB->prepare('SELECT MAX(accountcode) AS maxaccount FROM `chartmaster`');
        $maxSql->execute();
        $maxValue = $maxSql->fetch(PDO::FETCH_ASSOC);
        $accountcode = $maxValue['maxaccount'] + 10;
        $modify_code = $jsonData->modify_code;
        // check modify code 
        $checkModifyCode = $readDB->prepare('SELECT * FROM `chartmaster` WHERE `modify_code`=:modify_code');
        $checkModifyCode->bindParam(':modify_code', $modify_code, PDO::PARAM_STR);
        $checkModifyCode->execute();
        $rowCount = $checkModifyCode->rowCount();
        if ($rowCount > 0) {
            $response = new Response();
            $response->setHttpStatusCode(401);
            $response->setSuccess(false);
            $response->addMessage('Modify number already exist.');
            $response->send();
            exit;
        }
        if ($modify_code == '') {
            $modify_code = $accountcode;
        }
        // insert basic account 
        $accounttype = 'D';
        $group_ = 'Administrative Expenses';
        $block_negbal = '0';
        $block_budget = '0';
        $cf = '0';
        $cashFlowGroup = '0';
        $tagfa = '0';
        $in_cash_flow_type = '0';
        $op_bal = '0';
        $display_si = '0';
        $cn_id = '0';
        $color = '0';
        $cn_id = '0';
        $threshold = '0';
        $upper_limit = '0';
        $lower_limit = '0';
        $threshold_flag = '0';
        $currency = 'BDT';
        $addBasicAccount = $writeDB->prepare('INSERT INTO chartmaster(accountcode, modify_code, accountname, accounttype, group_, block_negbal, block_budget, cf, cashFlowGroup, tagfa, in_cash_flow_type, op_bal, display_si, cn_id,color, threshold, upper_limit, lower_limit, threshold_flag, currency ) VALUES(:accountcode, :modify_code, :accountname, :accounttype, :group_, :block_negbal, :block_budget, :cf, :cashFlowGroup, :tagfa, :in_cash_flow_type, :op_bal, :display_si, :cn_id, :color, :threshold, :upper_limit, :lower_limit, :threshold_flag, :currency)');
        $addBasicAccount->bindParam(':accountcode', $accountcode, PDO::PARAM_STR);
        $addBasicAccount->bindParam(':modify_code', $modify_code, PDO::PARAM_STR);
        $addBasicAccount->bindParam(':accountname', $accountname, PDO::PARAM_STR);
        $addBasicAccount->bindParam(':accounttype', $accounttype, PDO::PARAM_STR);
        $addBasicAccount->bindParam(':group_', $group_, PDO::PARAM_STR);
        $addBasicAccount->bindParam(':block_negbal', $block_negbal, PDO::PARAM_STR);
        $addBasicAccount->bindParam(':block_budget', $block_budget, PDO::PARAM_STR);
        $addBasicAccount->bindParam(':cf', $cf, PDO::PARAM_STR);
        $addBasicAccount->bindParam(':cashFlowGroup', $cashFlowGroup, PDO::PARAM_STR);
        $addBasicAccount->bindParam(':tagfa', $tagfa, PDO::PARAM_STR);
        $addBasicAccount->bindParam(':in_cash_flow_type', $in_cash_flow_type, PDO::PARAM_STR);
        $addBasicAccount->bindParam(':op_bal', $op_bal, PDO::PARAM_STR);
        $addBasicAccount->bindParam(':display_si', $display_si, PDO::PARAM_STR);
        $addBasicAccount->bindParam(':cn_id', $cn_id, PDO::PARAM_STR);
        $addBasicAccount->bindParam(':color', $color, PDO::PARAM_STR);
        $addBasicAccount->bindParam(':threshold', $threshold, PDO::PARAM_STR);
        $addBasicAccount->bindParam(':upper_limit', $upper_limit, PDO::PARAM_STR);
        $addBasicAccount->bindParam(':lower_limit', $lower_limit, PDO::PARAM_STR);
        $addBasicAccount->bindParam(':threshold_flag', $threshold_flag, PDO::PARAM_STR);
        $addBasicAccount->bindParam(':currency', $currency, PDO::PARAM_STR);
        $addBasicAccount->execute();
        $rowCount = $addBasicAccount->rowCount();
        if ($rowCount === 1) {
            $response = new Response();
            $response->setHttpStatusCode(200);
            $response->setSuccess(true);
            $response->toCache(true);
            $response->addMessage('Basic account created success.');
            $response->send();
            exit;
        }
    } catch (ContactException $ex) {
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
        $basicAccount = new BasicAccountAdd($jsonData->accountcode, $jsonData->accountname, $jsonData->modify_code);
        $accountcode = $basicAccount->getAccountcode();
        $accountname = $basicAccount->getAccountname();
        $modify_code = $basicAccount->getModify_code();
        $checkAccountExist = $readDB->prepare('SELECT * FROM chartmaster WHERE accountcode=:accountcode');
        $checkAccountExist->bindParam(':accountcode', $accountcode, PDO::PARAM_STR);
        $checkAccountExist->execute();
        $rowCount = $checkAccountExist->rowCount();
        if ($rowCount != 1) {
            $response = new Response();
            $response->setHttpStatusCode(404);
            $response->setSuccess(false);
            $response->addMessage('Account not found.');
            $response->send();
            exit;
        }
        // check account name
        $checkAccount = $readDB->prepare('SELECT * FROM chartmaster WHERE accountname=:accountname AND accountcode!=:accountcode');
        $checkAccount->bindParam(':accountname', $accountname, PDO::PARAM_STR);
        $checkAccount->bindParam(':accountcode', $accountcode, PDO::PARAM_STR);
        $checkAccount->execute();
        $rowCount = $checkAccount->rowCount();
        if ($rowCount > 0) {
            $response = new Response();
            $response->setHttpStatusCode(401);
            $response->setSuccess(false);
            $response->addMessage('This account name already exist.');
            $response->send();
            exit;
        }
        // check modify code 
        $checkModifyCode = $readDB->prepare('SELECT * FROM `chartmaster` WHERE `modify_code`=:modify_code AND accountcode!=:accountcode');
        $checkModifyCode->bindParam(':modify_code', $modify_code, PDO::PARAM_STR);
        $checkModifyCode->bindParam(':accountcode', $accountcode, PDO::PARAM_STR);
        $checkModifyCode->execute();
        $rowCount = $checkModifyCode->rowCount();
        if ($rowCount > 0) {
            $response = new Response();
            $response->setHttpStatusCode(401);
            $response->setSuccess(false);
            $response->addMessage('Modify number already exist.');
            $response->send();
            exit;
        }
        // update basic account 
        $updateAccount = $writeDB->prepare('UPDATE chartmaster SET accountname=:accountname, modify_code=:modify_code WHERE accountcode=:accountcode');
        $updateAccount->bindParam(':accountcode', $accountcode, PDO::PARAM_STR);
        $updateAccount->bindParam(':accountname', $accountname, PDO::PARAM_STR);
        $updateAccount->bindParam(':modify_code', $modify_code, PDO::PARAM_STR);
        $updateAccount->execute();
        $rowCount = $updateAccount->rowCount();
        if ($rowCount === 1) {
            $response = new Response();
            $response->setHttpStatusCode(200);
            $response->setSuccess(true);
            $response->toCache(true);
            $response->addMessage('Basic account update success.');
            $response->send();
            exit;
        } else {
            $response = new Response();
            $response->setHttpStatusCode(400);
            $response->setSuccess(false);
            $response->addMessage('Nothing to be updated.');
            $response->send();
            exit;
        }
    } catch (ContactException $ex) {
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
} elseif ($_SERVER['REQUEST_METHOD'] === "DELETE") {
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
        $basicAccount = new BasicAccountAdd($jsonData->accountcode, null, null);
        $accountcode = $basicAccount->getAccountcode();
        $checkAccountExist = $readDB->prepare('SELECT * FROM chartmaster WHERE accountcode=:accountcode');
        $checkAccountExist->bindParam(':accountcode', $accountcode, PDO::PARAM_STR);
        $checkAccountExist->execute();
        $rowCount = $checkAccountExist->rowCount();
        if ($rowCount != 1) {
            $response = new Response();
            $response->setHttpStatusCode(404);
            $response->setSuccess(false);
            $response->addMessage('Account not found.');
            $response->send();
            exit;
        }
        // delete basic account 
        $deleteAccount = $writeDB->prepare('DELETE FROM chartmaster WHERE accountcode=:accountcode');
        $deleteAccount->bindParam(':accountcode', $accountcode, PDO::PARAM_STR);
        $deleteAccount->execute();
        $rowCount = $deleteAccount->rowCount();
        if ($rowCount === 1) {
            $response = new Response();
            $response->setHttpStatusCode(200);
            $response->setSuccess(true);
            $response->toCache(true);
            $response->addMessage('Basic account deleted success.');
            $response->send();
            exit;
        } else {
            $response = new Response();
            $response->setHttpStatusCode(400);
            $response->setSuccess(false);
            $response->addMessage('Nothing to be updated.');
            $response->send();
            exit;
        }
    } catch (ContactException $ex) {
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
    if (array_key_exists('accountcode', $_GET) && !array_key_exists('accountname', $_GET)) {
        $accountcode = $_GET['accountcode'];
        $basicAccounts = $readDB->prepare('SELECT accountcode,accountname,modify_code FROM chartmaster WHERE accountcode=:accountcode');
        $basicAccounts->bindParam(':accountcode', $accountcode, PDO::PARAM_STR);
    } elseif (array_key_exists('modify_code', $_GET) && !array_key_exists('accountname', $_GET)) {
        $modify_code = $_GET['modify_code'];
        $basicAccounts = $readDB->prepare('SELECT accountcode,accountname,modify_code FROM chartmaster WHERE modify_code=:modify_code');
        $basicAccounts->bindParam(':modify_code', $modify_code, PDO::PARAM_STR);
    } elseif (array_key_exists('accountname', $_GET) && !array_key_exists('accountcode', $_GET)  && !array_key_exists('modify_code', $_GET)) {
        $accountname = $_GET['accountname'];
        if ($accountname === '') {
            $response = new Response();
            $response->setHttpStatusCode(403);
            $response->setSuccess(false);
            $response->addMessage('accountname Name missing its not be null. ');
            $response->send();
            exit();
        }
        $subQry = "SELECT accountcode,accountname,modify_code FROM chartmaster WHERE ";
        $textsearchQury = '';

        $searchKeywordList = explode(' ', $accountname);

        foreach ($searchKeywordList as $searchKey) {
            $textsearchQury .= "accountname LIKE '%" . $searchKey . "%' OR ";
        }
        $textsearchQury = $subQry . rtrim($textsearchQury, 'OR ');
        $basicAccounts = $readDB->prepare($textsearchQury);
    } elseif (array_key_exists('accountname', $_GET) && array_key_exists('modify_code', $_GET)) {
        $modify_code = $_GET['modify_code'];
        $accountname = $_GET['accountname'];
        if ($accountname === '') {
            $response = new Response();
            $response->setHttpStatusCode(403);
            $response->setSuccess(false);
            $response->addMessage('accountname Name missing its not be null. ');
            $response->send();
            exit;
        }
        $mainQuery = "SELECT accountcode,accountname,modify_code FROM chartmaster WHERE modify_code='$modify_code' OR ";
        $searchKeywordList = explode(' ', $accountname);

        foreach ($searchKeywordList as $searchKey) {
            $textsearchQury .= "accountname LIKE '%" . $searchKey . "%' OR ";
        }
        $textsearchQury = $mainQuery . rtrim($textsearchQury, 'OR ');
        $basicAccounts = $readDB->prepare($textsearchQury);
    } else {
        $basicAccounts = $readDB->prepare('SELECT accountcode,accountname,modify_code FROM chartmaster');
    }
    $basicAccounts->execute();
    $rowCount = $basicAccounts->rowCount();
    $basicAccountArray = array();
    while ($row = $basicAccounts->fetch(PDO::FETCH_ASSOC)) {
        $basicAccountData = new BasicAccountAdd($row['accountcode'], $row['accountname'], $row['modify_code']);
        $basicAccountArray[] = $basicAccountData->returnBasicAccountArray();
    }
    $returnArray = array();
    $returnArray['rows_returned'] = $rowCount;
    $returnArray['basicAccount'] = $basicAccountArray;
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
    $response->addMessage("Endpoint not found");
    $response->send();
    exit;
}
