<?php
class SupplierException extends Exception
{
}
class Supplier
{
    private $supplierid;
    private $code;
    private $suppname;
    private $address1;
    private $address2;
    private $address3;
    private $phn;
    private $status;
    public function __construct($supplierid, $code, $suppname, $address1, $address2, $address3, $phn, $status)
    {
        $this->setSupplierid($supplierid);
        $this->setCode($code);
        $this->setSuppname($suppname);
        $this->setAddress1($address1);
        $this->setAddress2($address2);
        $this->setAddress3($address3);
        $this->setPhn($phn);
        $this->setStatus($status);
    }
    function setSupplierid($supplierid)
    {
        $this->supplierid = $supplierid;
    }
    function getSupplierid()
    {
        return $this->supplierid;
    }
    function setCode($code)
    {
        $this->code = $code;
    }
    function getCode()
    {
        return $this->code;
    }
    function setSuppname($suppname)
    {
        if ($suppname == '' || $suppname == null) {
            throw new SupplierException("Supplier name can not be null.");
        }
        $this->suppname = $suppname;
    }
    function getSuppname()
    {
        return $this->suppname;
    }
    function setAddress1($address1)
    {
        if ($address1 == '' || $address1 == null) {
            throw new SupplierException("Supplier address can not be null.");
        }
        $this->address1 = $address1;
    }
    function getAddress1()
    {
        return $this->address1;
    }
    function setAddress2($address2)
    {
        $this->address2 = $address2;
    }
    function getAddress2()
    {
        return $this->address2;
    }
    function setAddress3($address3)
    {
        $this->address3 = $address3;
    }
    function getAddress3()
    {
        return $this->address3;
    }
    function setPhn($phn)
    {
        $this->phn = $phn;
    }
    function getPhn()
    {
        return $this->phn;
    }
    function setStatus($status)
    {
        $this->status = $status;
    }
    function getStatus()
    {
        return $this->status;
    }
    public function returnSupplierArray()
    {
        $supplier = array();
        $supplier['supplierid'] = $this->getSupplierid();
        $supplier['code'] = $this->getCode();
        $supplier['suppname'] = $this->getSuppname();
        $supplier['address1'] = $this->getAddress1();
        $supplier['address2'] = $this->getAddress2();
        $supplier['address3'] = $this->getAddress3();
        $supplier['phn'] = $this->getPhn();
        $supplier['status'] = $this->getStatus();
        return $supplier;
    }
}
