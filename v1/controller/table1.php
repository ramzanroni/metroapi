<?php
include_once('db.php');
include_once('../model/task.php');
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
    exit();
}
//get data
//get data
if ($_SERVER["REQUEST_METHOD"] === "GET") {
    try {
        if (array_key_exists("id", $_GET)) {
            $id = $_GET["id"];
            if ($id === "") {
                $response = new Response();
                $response->setHttpStatusCode(400);
                $response->setSuccess(false);
                $response->addMessage("id cannot be blank");
                $response->send();
                exit();
            }
            $tblSQL = $readDB->prepare("SELECT * FROM tbl WHERE id=:id");
            $tblSQL->bindParam(":id", $id, PDO::PARAM_STR);
        } else {
            $tblSQL = $readDB->prepare("SELECT * FROM tbl");
        }
        $tblSQL->execute();
        $rowCount = $tblSQL->rowCount();
        if ($rowCount === 0) {
            $response = new Response();
            $response->setHttpStatusCode(404);
            $response->setSuccess(false);
            $response->addMessage("Data not found");
            $response->send();
            exit();
        }
        $tblArray = array();
        while ($row = $tblSQL->fetch(PDO::FETCH_ASSOC)) {

            $tblData = new Table($row["id"], $row["name"]);
            $tblArray[] = $tblData->returnTableArray();
        }
        $returnData = array();
        $returnData["rows_returned"] = $rowCount;
        $returnData["tbl"] = $tblArray;
        $response = new Response();
        $response->setHttpStatusCode(200);
        $response->setSuccess(true);
        $response->toCache(true);
        $response->setData($returnData);
        $response->send();
        exit;
    } catch (TableException $ex) {
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
        $tbl = new Table("", $jsonData->name);
        $id = $tbl->getId();
        $name = $tbl->getName();
        $tblInsertSQL = $writeDB->prepare("INSERT INTO tasks (name) VALUES (:name)");
        $tblInsertSQL->bindParam(":name", $name, PDO::PARAM_STR);
        $tblInsertSQL->execute();
        $rowCount = $tblInsertSQL->rowCount();
        if ($rowCount === 0) {
            $response = new Response();
            $response->setHttpStatusCode(500);
            $response->setSuccess(false);
            $response->addMessage("Insert operation failed...");
            $response->send();
            exit();
        }
        if ($rowCount) {
            $response = new Response();
            $response->setHttpStatusCode(200);
            $response->setSuccess(true);
            $response->toCache(true);
            $response->addMessage("Insert data Successfully");
            $response->send();
            exit();
        }
    } catch (TableException $ex) {
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
} elseif ($_SERVER["REQUEST_METHOD"] === "DELETE") {
    $id = $_GET["id"];
    if ($id === "") {
        $response = new Response();
        $response->setHttpStatusCode(400);
        $response->setSuccess(false);
        $response->addMessage("id cannot be blank");
        $response->send();
        exit();
    }
    try {
        $checkData = $readDB->prepare("SELECT * FROM tbl WHERE id=:id");
        $checkData->bindParam(":id", $id, PDO::PARAM_STR);
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
        $deleteSQL = $writeDB->prepare("DELETE FROM tbl WHERE id=:id");
        $deleteSQL->bindParam(":id", $id, PDO::PARAM_STR);
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
    } catch (TableException $ex) {
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
} elseif ($_SERVER["REQUEST_METHOD"] === "PUT") {
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
        $tbl = new Table($jsonData->id, $jsonData->name);
        $id = $tbl->getId();
        $name = $tbl->getName();
        $checkData = $readDB->prepare("SELECT * FROM tbl WHERE id=:id");
        $checkData->bindParam(":id", $id, PDO::PARAM_STR);
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
        $mainQuery = "UPDATE tbl SET ";
        if ($name != "") {
            $mainQuery = $mainQuery . "name=:name,";
        }
        $mainQuery = substr($mainQuery, 0, -1) . " WHERE id=:id";
        $updateSql = $writeDB->prepare($mainQuery);
        if ($id != "") {
            $updateSql->bindParam("id", $id, PDO::PARAM_STR);
        }
        if ($name != "") {
            $updateSql->bindParam("name", $name, PDO::PARAM_STR);
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
    } catch (TableException $ex) {
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
