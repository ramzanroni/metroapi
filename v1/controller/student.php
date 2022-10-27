<?php
include_once('db.php');
include_once('./validation.php');
include_once('../model/studentModel.php');
include_once('../model/response.php');
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
            $studentsSQL = $readDB->prepare("SELECT * FROM students WHERE id=:id");
            $studentsSQL->bindParam(":id", $id, PDO::PARAM_STR);
        } else {
            $studentsSQL = $readDB->prepare("SELECT * FROM students");
        }
        $studentsSQL->execute();
        $rowCount = $studentsSQL->rowCount();
        if ($rowCount === 0) {
            $response = new Response();
            $response->setHttpStatusCode(404);
            $response->setSuccess(false);
            $response->addMessage("Data not found");
            $response->send();
            exit();
        }
        $studentsArray = array();
        while ($row = $studentsSQL->fetch(PDO::FETCH_ASSOC)) {
            $studentsData = new Student($row["id"], $row["name"], $row["school"], $row["phone"], $row["address"], $row["status"]);
            $studentsArray[] = $studentsData->returnStudentArray();
        }
        $returnData = array();
        $returnData["rows_returned"] = $rowCount;
        $returnData["students"] = $studentsArray;
        $response = new Response();
        $response->setHttpStatusCode(200);
        $response->setSuccess(true);
        $response->toCache(true);
        $response->setData($returnData);
        $response->send();
        exit;
    } catch (StudentException $ex) {
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
        if (isset($jsonData->name)) {
            $stringValidation = $validation->stringValidation($jsonData->name);
            if ($stringValidation != "") {
                $errorArr["name"] = "name - " . $stringValidation;
            }
        }
        if (isset($jsonData->school)) {
            $stringValidation = $validation->stringValidation($jsonData->school);
            if ($stringValidation != "") {
                $errorArr["school"] = "school - " . $stringValidation;
            }
        }
        if (isset($jsonData->phone)) {
            $phoneValidation = $validation->phoneValidation($jsonData->phone);
            if ($phoneValidation != "") {
                $errorArr["phone"] = "phone - " . $phoneValidation;
            }
        }
        if (isset($jsonData->address)) {
            $stringValidation = $validation->stringValidation($jsonData->address);
            if ($stringValidation != "") {
                $errorArr["address"] = "address - " . $stringValidation;
            }
        }
        if (isset($jsonData->status)) {
            $intValidation = $validation->intValidation($jsonData->status);
            if ($intValidation != "") {
                $errorArr["status"] = "status - " . $intValidation;
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
        $students = new Student("", $jsonData->name, $jsonData->school, $jsonData->phone, $jsonData->address, $jsonData->status);
        $id = $students->getId();
        $name = $students->getName();
        $school = $students->getSchool();
        $phone = $students->getPhone();
        $address = $students->getAddress();
        $status = $students->getStatus();
        $studentsInsertSQL = $writeDB->prepare("INSERT INTO students (name,school,phone,address,status) VALUES (:name,:school,:phone,:address,:status)");
        $studentsInsertSQL->bindParam(":name", $name, PDO::PARAM_STR);
        $studentsInsertSQL->bindParam(":school", $school, PDO::PARAM_STR);
        $studentsInsertSQL->bindParam(":phone", $phone, PDO::PARAM_STR);
        $studentsInsertSQL->bindParam(":address", $address, PDO::PARAM_STR);
        $studentsInsertSQL->bindParam(":status", $status, PDO::PARAM_STR);
        $studentsInsertSQL->execute();
        $rowCount = $studentsInsertSQL->rowCount();
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
    } catch (StudentException $ex) {
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
//delete
elseif ($_SERVER["REQUEST_METHOD"] === "DELETE") {
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
        $checkData = $readDB->prepare("SELECT * FROM students WHERE id=:id");
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
        $deleteSQL = $writeDB->prepare("DELETE FROM students WHERE id=:id");
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
    } catch (StudentException $ex) {
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
        if (isset($jsonData->name)) {
            $stringValidation = $validation->stringValidation($jsonData->name);
            if ($stringValidation != "") {
                $errorArr["name"] = "name - " . $stringValidation;
            }
        }
        if (isset($jsonData->school)) {
            $stringValidation = $validation->stringValidation($jsonData->school);
            if ($stringValidation != "") {
                $errorArr["school"] = "school - " . $stringValidation;
            }
        }
        if (isset($jsonData->phone)) {
            $phoneValidation = $validation->phoneValidation($jsonData->phone);
            if ($phoneValidation != "") {
                $errorArr["phone"] = "phone - " . $phoneValidation;
            }
        }
        if (isset($jsonData->address)) {
            $stringValidation = $validation->stringValidation($jsonData->address);
            if ($stringValidation != "") {
                $errorArr["address"] = "address - " . $stringValidation;
            }
        }
        if (isset($jsonData->status)) {
            $intValidation = $validation->intValidation($jsonData->status);
            if ($intValidation != "") {
                $errorArr["status"] = "status - " . $intValidation;
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
        $students = new Student($jsonData->id, $jsonData->name, $jsonData->school, $jsonData->phone, $jsonData->address, $jsonData->status);
        $id = $students->getId();
        $name = $students->getName();
        $school = $students->getSchool();
        $phone = $students->getPhone();
        $address = $students->getAddress();
        $status = $students->getStatus();
        $checkData = $readDB->prepare("SELECT * FROM students WHERE id=:id");
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
        $mainQuery = "UPDATE students SET ";
        if ($name != "") {
            $mainQuery = $mainQuery . "name=:name,";
        }
        if ($school != "") {
            $mainQuery = $mainQuery . "school=:school,";
        }
        if ($phone != "") {
            $mainQuery = $mainQuery . "phone=:phone,";
        }
        if ($address != "") {
            $mainQuery = $mainQuery . "address=:address,";
        }
        if ($status != "") {
            $mainQuery = $mainQuery . "status=:status,";
        }
        $mainQuery = substr($mainQuery, 0, -1) . " WHERE id=:id";
        $updateSql = $writeDB->prepare($mainQuery);
        if ($id != "") {
            $updateSql->bindParam("id", $id, PDO::PARAM_STR);
        }
        if ($name != "") {
            $updateSql->bindParam("name", $name, PDO::PARAM_STR);
        }
        if ($school != "") {
            $updateSql->bindParam("school", $school, PDO::PARAM_STR);
        }
        if ($phone != "") {
            $updateSql->bindParam("phone", $phone, PDO::PARAM_STR);
        }
        if ($address != "") {
            $updateSql->bindParam("address", $address, PDO::PARAM_STR);
        }
        if ($status != "") {
            $updateSql->bindParam("status", $status, PDO::PARAM_STR);
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
    } catch (StudentException $ex) {
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
