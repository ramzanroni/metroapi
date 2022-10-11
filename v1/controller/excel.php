<?php
$servername = "eon_bazar";
$username = "roni";
$password = "roni";

// Create connection
$conn = new mysqli("localhost", "metrosoft", "metrosoft", "eon_bazar");
// $conn = new mysqli($servername, $username, $password);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$SQL = "SELECT stockmaster.stockid, stockmaster.code, stockgroup.groupname, stockmaster.description, stockmaster.longdescription, stockmaster.webprice, SUM(locstock.quantity) AS qoh, stockmaster.units, stockmaster.mbflag, stockmaster.decimalplaces, stockmaster.categoryid FROM stockmaster LEFT JOIN stockgroup ON stockmaster.groupid = stockgroup.groupid LEFT JOIN locstock ON locstock.stockid=stockmaster.stockid";
$SQL .= " GROUP BY stockmaster.stockid,
       stockmaster.description,
       stockmaster.units,
       stockmaster.mbflag,
       stockmaster.decimalplaces
   ORDER BY stockmaster.code";

$data = mysqli_query($conn, $SQL);

$time = date('d-m-Y');

header('Content-Description: File Transfer');
header('Content-type: application/csv;charset=utf-8');
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename=ProductList' . $time . '.xls');
header('Content-Transfer-Encoding: binary');
header('Expires: 0');
header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
header('Pragma: public');
echo "\xEF\xBB\xBF";
// header('Content-Type: text/html; charset=utf-8');
// header("Content-Disposition: attachment; filename=ProductList" . $time . ".xls");  //File name extension was wrong


echo '<table><tr><td colspan="5">' . $_SESSION["CompanyRecord"]["coyname"] . '</td></tr><tr><td>Code</td><td>Category</td><td>Name</td><td>Description</td><td>Item Class</td><td>QoH</td><td>Price</td><td>Unit</td></tr>';
while ($row = mysqli_fetch_assoc($data)) {
    echo '<tr><td>' . $row["code"] . '</td><td>' . $row["groupname"] . '</td><td>' . $row["description"] . '</td><td>' . $row["longdescription"] . '</td><td>' . $row["categorydescription"] . '</td><td>' . $row["qoh"] . '</td><td>' . $row["webprice"] . '</td><td>' . $row["units"] . '</td></tr>';
}
echo '</table>';
