<?php
include 'db-conn.php';


$suppname = "test";
$address1 = "sector-7, uttara";
$address2 = "Bangladesh";
// $code = 12212;
$currcode = "BDT";
$suppliersince = gmdate('Y-m-d H:i:s');
$paymentterms = "CA";
$bankact = '';
$bankref = 0;
$bankpartics = 0;
$remittance = 0;
$taxgroupid = 1;
$taxref = '';
$phn = '01767270653';
$port = '';
$updated_at = "0000-00-00 00:00:00";
$updated_by = 0;
$status = 1;
$bin_no = '';
$nid_no = '';
$inv_ser_com = 1;



$lat = "0.000000";
$lng = "0.000000";
$estdeliverydays = "0";
$AREA = "2780";
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
if ($suppname != '' && $address1 != '') {
    try {
        $lastCode = mysqli_fetch_assoc(mysqli_query($conn, 'SELECT MAX(code) as code FROM `suppliers`'));
        $code = $lastCode['code'] + 1;
        $insertSupplier = mysqli_query($conn, "INSERT INTO `suppliers`(`code`,`suppname`,`address1`,`address2`,`currcode`,`suppliersince`,`paymentterms`,`bankact`,`bankref`,`bankpartics`,`remittance`,`taxgroupid`,`taxref`,`phn`,`port`,`updated_at`,`updated_by`,`status`,`bin_no`,`nid_no`,`inv_ser_com`) VALUES ('$code','$suppname','$address1','$address2','$currcode','$suppliersince','$paymentterms','$bankact',$bankref,'$bankpartics','$remittance','$taxgroupid','$taxref','$pnh','$port','$updated_at','$updated_by','$status','$bin_no','$nid_no','$inv_ser_com')");
        if ($insertSupplier === FALSE) {
            throw new Exception($conn->error);
        }
        $supplierId = $conn->insert_id;
        $lastBranchCode = mysqli_fetch_assoc(mysqli_query($conn, 'SELECT MAX(branchcode) as branchcode FROM `supbranch`'));
        $branchcode = $lastBranchCode['branchcode'] + 1;
        $queryCustbranch = mysqli_query($conn, "INSERT INTO supbranch(branchcode, supplier_id, brname, braddress1, lat, lng, estdeliverydays, area,  fwddate, phoneno, defaultlocation, taxgroupid, defaultshipvia, deliverblind, disabletrans,specialinstructions, branchdistance, travelrate, businessunit, emi, esd, branchsince, branchstatus, tag, op_bal, aggrigate_ap)VALUES('$branchcode', '$supplierId', '$suppname', '$address1', '$lat', '$lng', '$estdeliverydays', '$AREA', '$fwddate', '$phn', '$defaultlocation', '$taxgroupid', '$defaultshipvia', '$deliverblind', '$disabletrans', '$specialinstructions', '$branchdistance', '$travelrate', '$businessunit', '$emi', '$esd', now(), '$branchstatus', '$tag', '$op_bal', '$aggrigate_ap')");
        if ($queryCustbranch === FALSE) {
            throw new Exception($conn->error);
        }
    } catch (Exception $e) {
        print_r($e);
    }
}
