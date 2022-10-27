<?php
class OrderException extends Exception
{
}


class CreateOrderInfo
{
    private $_id;
    private $_additionalInfo;
    private $_deliveryTime;
    private $_otherCharge;

    public function __construct($id, $additionalInfo, $deliveryTime, $otherCharge)
    {
        $this->setId($id);
        $this->setAdditionalInfo($additionalInfo);
        $this->setDeliveryTime($deliveryTime);
        $this->setOtherCharge($otherCharge);
    }

    public function getId()
    {
        return $this->_id;
    }
    public function getAdditionalInfo()
    {
        return $this->_additionalInfo;
    }
    public function getDeliveryTime()
    {
        return $this->_deliveryTime;
    }
    public function getOtherCharge()
    {
        return $this->_otherCharge;
    }


    public function setId($id)
    {
        if (!is_numeric($id) || $id === '' || $id === null) {
            throw new OrderException("ID can not be null or text data .");
        }
        $this->_id = $id;
    }

    public function setAdditionalInfo($additionalInfo)
    {
        $this->_additionalInfo = $additionalInfo;
    }
    public function setOtherCharge($otherCharge)
    {
        $this->_otherCharge = $otherCharge;
    }
    public function setDeliveryTime($deliveryTime)
    {
        if ($deliveryTime === '' || $deliveryTime === null) {
            throw new OrderException("Delivery can not be null or text data .");
        }
        $this->_deliveryTime = $deliveryTime;
    }
}
class OrderItem
{
    private $_productID;
    private $_unitPrice;
    private $_productQuantity;

    public function __construct($productId, $unitPrice, $productQuantity)
    {
        $this->setProductId($productId);
        $this->setUnitPrice($unitPrice);
        $this->setProductQuantity($productQuantity);
    }
    public function getProductId()
    {
        return $this->_productID;
    }

    public function getUnitPrice()
    {
        return $this->_unitPrice;
    }

    public function getProductQuantity()
    {
        return $this->_productQuantity;
    }


    public function setProductId($productId)
    {
        if (!is_numeric($productId) || $productId == '') {
            throw new OrderException("Product ID can not be null or String value");
        }
        $this->_productID = $productId;
    }

    public function setUnitPrice($unitPrice)
    {
        if (!is_numeric($unitPrice) || $unitPrice == '') {
            throw new OrderException("Unit Price ID can not be null or String value");
        }
        $this->_unitPrice = $unitPrice;
    }

    public function setProductQuantity($productQuantity)
    {
        if (!is_numeric($productQuantity) || $productQuantity == '') {
            throw new OrderException("Product Quantity Price ID can not be null or String value");
        }
        $this->_productQuantity = $productQuantity;
    }
}

class Orders
{
    private $orderno;
    private $name;
    private $debtorno;
    private $comments;
    private $deladd1;
    private $contactphone;
    private $freightcost;
    private $deliverydate;
    private $so_status;
    private $delivery_status;
    private $orddate;
    private $issue_date;
    private $price;
    public function __construct($orderno, $name, $debtorno, $comments, $deladd1, $contactphone, $freightcost, $deliverydate, $so_status, $delivery_status, $orddate, $issue_date, $price)
    {
        $this->setOrderno($orderno);
        $this->setName($name);
        $this->setDebtorno($debtorno);
        $this->setComments($comments);
        $this->setDeladd1($deladd1);
        $this->setContactphone($contactphone);
        $this->setFreightcost($freightcost);
        $this->setDeliverydate($deliverydate);
        $this->setSo_status($so_status);
        $this->setDelivery_status($delivery_status);
        $this->setOrddate($orddate);
        $this->setIssue_date($issue_date);
        $this->setPrice($price);
    }
    function setOrderno($orderno)
    {
        $this->orderno = $orderno;
    }
    function getOrderno()
    {
        return $this->orderno;
    }
    function setName($name)
    {
        $this->name = $name;
    }
    function getName()
    {
        return $this->name;
    }
    function setDebtorno($debtorno)
    {
        $this->debtorno = $debtorno;
    }
    function getDebtorno()
    {
        return $this->debtorno;
    }
    function setComments($comments)
    {
        $this->comments = $comments;
    }
    function getComments()
    {
        return $this->comments;
    }
    function setDeladd1($deladd1)
    {
        $this->deladd1 = $deladd1;
    }
    function getDeladd1()
    {
        return $this->deladd1;
    }
    function setContactphone($contactphone)
    {
        $this->contactphone = $contactphone;
    }
    function getContactphone()
    {
        return $this->contactphone;
    }
    function setFreightcost($freightcost)
    {
        $this->freightcost = $freightcost;
    }
    function getFreightcost()
    {
        return $this->freightcost;
    }
    function setDeliverydate($deliverydate)
    {
        $this->deliverydate = $deliverydate;
    }
    function getDeliverydate()
    {
        return $this->deliverydate;
    }
    function setSo_status($so_status)
    {
        $this->so_status = $so_status;
    }
    function getSo_status()
    {
        return $this->so_status;
    }
    function setDelivery_status($delivery_status)
    {
        $this->delivery_status = $delivery_status;
    }
    function getDelivery_status()
    {
        return $this->delivery_status;
    }
    function setOrddate($orddate)
    {
        $this->orddate = $orddate;
    }
    function getOrddate()
    {
        return $this->orddate;
    }
    function setIssue_date($issue_date)
    {
        $this->issue_date = $issue_date;
    }
    function getIssue_date()
    {
        return $this->issue_date;
    }
    function setPrice($price)
    {
        $this->price = $price;
    }
    function getPrice()
    {
        return $this->price;
    }

    public function OrderReturnArray()
    {
        $orders = array();
        $orders["orderno"] = $this->getOrderno();
        $orders["name"] = $this->getName();
        $orders["debtorno"] = $this->getDebtorno();
        $orders["comments"] = $this->getComments();
        $orders["address"] = $this->getDeladd1();
        $orders["contactphone"] = $this->getContactphone();
        $orders["freightcost"] = $this->getFreightcost();
        $orders["deliverydate"] = $this->getDeliverydate();
        $orders["so_status"] = $this->getSo_status();
        $orders["delivery_status"] = $this->getDelivery_status();
        $orders["orddate"] = $this->getOrddate();
        $orders["issue_date"] = $this->getIssue_date();
        $orders["price"] = $this->getPrice();
        return $orders;
    }
}
