<?php
class PurchordersException extends Exception
{
}
class Purchorders
{
    private $_orderno;
    private $_supplierno;
    private $_orddate;
    private $_dateprinted;
    private $_initiator;
    private $_intostocklocation;
    private $_deladd1;
    private $_deliverydate;
    private $_status;
    private $_stat_comment;
    private $_authorized_by;
    private $_authorized_date;
    private $_totalPrice;
    public function __construct($orderno, $supplierno, $orddate, $dateprinted, $initiator, $intostocklocation, $deladd1, $deliverydate, $status, $stat_comment, $authorized_by, $authorized_date, $totalPrice)
    {
        $this->setOrderno($orderno);
        $this->setSupplierno($supplierno);
        $this->setOrddate($orddate);
        $this->setDateprinted($dateprinted);
        $this->setInitiator($initiator);
        $this->setIntostocklocation($intostocklocation);
        $this->setDeladd1($deladd1);
        $this->setDeliverydate($deliverydate);
        $this->setStatus($status);
        $this->setStat_comment($stat_comment);
        $this->setAuthorized_by($authorized_by);
        $this->setAuthorized_date($authorized_date);
        $this->setTotalPrice($totalPrice);
    }
    public function setOrderno($orderno)
    {
        $this->_orderno = $orderno;
    }
    public function getOrderno()
    {
        return $this->_orderno;
    }
    public function setSupplierno($supplierno)
    {
        $this->_supplierno = $supplierno;
    }
    public function getSupplierno()
    {
        return $this->_supplierno;
    }
    public function setOrddate($orddate)
    {
        $this->_orddate = $orddate;
    }
    public function getOrddate()
    {
        return $this->_orddate;
    }
    public function setDateprinted($dateprinted)
    {
        $this->_dateprinted = $dateprinted;
    }
    public function getDateprinted()
    {
        return $this->_dateprinted;
    }
    public function setInitiator($initiator)
    {
        $this->_initiator = $initiator;
    }
    public function getInitiator()
    {
        return $this->_initiator;
    }
    public function setIntostocklocation($intostocklocation)
    {
        $this->_intostocklocation = $intostocklocation;
    }
    public function getIntostocklocation()
    {
        return $this->_intostocklocation;
    }
    public function setDeladd1($deladd1)
    {
        $this->_deladd1 = $deladd1;
    }
    public function getDeladd1()
    {
        return $this->_deladd1;
    }
    public function setDeliverydate($deliverydate)
    {
        $this->_deliverydate = $deliverydate;
    }
    public function getDeliverydate()
    {
        return $this->_deliverydate;
    }
    public function setStatus($status)
    {
        $this->_status = $status;
    }
    public function getStatus()
    {
        return $this->_status;
    }
    public function setStat_comment($stat_comment)
    {
        $this->_stat_comment = $stat_comment;
    }
    public function getStat_comment()
    {
        return $this->_stat_comment;
    }
    public function setAuthorized_by($authorized_by)
    {
        $this->_authorized_by = $authorized_by;
    }
    public function getAuthorized_by()
    {
        return $this->_authorized_by;
    }
    public function setAuthorized_date($authorized_date)
    {
        $this->_authorized_date = $authorized_date;
    }
    public function getAuthorized_date()
    {
        return $this->_authorized_date;
    }
    public function setTotalPrice($totalPrice)
    {
        $this->_totalPrice = $totalPrice;
    }
    public function getTotaPrice()
    {
        return $this->_totalPrice;
    }
    public function returnPurchordersArray()
    {
        $purchorders = array();
        $purchorders["orderno"] = $this->getOrderno();
        $purchorders["supplierno"] = $this->getSupplierno();
        $purchorders["orddate"] = $this->getOrddate();
        $purchorders["dateprinted"] = $this->getDateprinted();
        $purchorders["initiator"] = $this->getInitiator();
        $purchorders["intostocklocation"] = $this->getIntostocklocation();
        $purchorders["deladd1"] = $this->getDeladd1();
        $purchorders["deliverydate"] = $this->getDeliverydate();
        $purchorders["status"] = $this->getStatus();
        $purchorders["stat_comment"] = $this->getStat_comment();
        $purchorders["authorized_by"] = $this->getAuthorized_by();
        $purchorders["authorized_date"] = $this->getAuthorized_date();
        $purchorders["totalPrice"] = $this->getTotaPrice();
        return $purchorders;
    }
}


class PurchorderDetailsException extends Exception
{
}
class PurchorderDetails
{
    private $_podetailitem;
    private $_orderno;
    private $_itemcode;
    private $_itemdescription;
    private $_glcode;
    private $_unitprice;
    private $_quantityord;
    private $_deliverydate;
    private $_total_amount;
    public function __construct($podetailitem, $orderno, $itemcode, $itemdescription, $glcode, $unitprice, $quantityord, $deliverydate, $total_amount)
    {
        $this->setPodetailitem($podetailitem);
        $this->setOrderno($orderno);
        $this->setItemcode($itemcode);
        $this->setItemdescription($itemdescription);
        $this->setGlcode($glcode);
        $this->setUnitprice($unitprice);
        $this->setQuantityord($quantityord);
        $this->setDeliverydate($deliverydate);
        $this->setTotal_amount($total_amount);
    }
    public function setPodetailitem($podetailitem)
    {
        $this->_podetailitem = $podetailitem;
    }
    public function getPodetailitem()
    {
        return $this->_podetailitem;
    }
    public function setOrderno($orderno)
    {
        $this->_orderno = $orderno;
    }
    public function getOrderno()
    {
        return $this->_orderno;
    }
    public function setItemcode($itemcode)
    {
        $this->_itemcode = $itemcode;
    }
    public function getItemcode()
    {
        return $this->_itemcode;
    }
    public function setItemdescription($itemdescription)
    {
        $this->_itemdescription = $itemdescription;
    }
    public function getItemdescription()
    {
        return $this->_itemdescription;
    }
    public function setGlcode($glcode)
    {
        $this->_glcode = $glcode;
    }
    public function getGlcode()
    {
        return $this->_glcode;
    }
    public function setUnitprice($unitprice)
    {
        $this->_unitprice = $unitprice;
    }
    public function getUnitprice()
    {
        return $this->_unitprice;
    }
    public function setQuantityord($quantityord)
    {
        $this->_quantityord = $quantityord;
    }
    public function getQuantityord()
    {
        return $this->_quantityord;
    }
    public function setDeliverydate($deliverydate)
    {
        $this->_deliverydate = $deliverydate;
    }
    public function getDeliverydate()
    {
        return $this->_deliverydate;
    }
    public function setTotal_amount($total_amount)
    {
        $this->_total_amount = $total_amount;
    }
    public function getTotal_amount()
    {
        return $this->_total_amount;
    }
    public function returnPurchorderDetailsArray()
    {
        $purchorderdetails = array();
        $purchorderdetails["podetailitem"] = $this->getPodetailitem();
        $purchorderdetails["orderno"] = $this->getOrderno();
        $purchorderdetails["itemcode"] = $this->getItemcode();
        $purchorderdetails["itemdescription"] = $this->getItemdescription();
        $purchorderdetails["glcode"] = $this->getGlcode();
        $purchorderdetails["unitprice"] = $this->getUnitprice();
        $purchorderdetails["quantityord"] = $this->getQuantityord();
        $purchorderdetails["deliverydate"] = $this->getDeliverydate();
        $purchorderdetails["total_amount"] = $this->getTotal_amount();
        return $purchorderdetails;
    }
}
