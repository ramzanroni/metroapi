<?php
class BasicAccountException extends Exception
{
}
class BasicAccountAdd
{
    private $accountcode;
    private $accountname;
    private $modify_code;
    public function __construct($accountcode, $accountname, $modify_code)
    {
        $this->setAccountcode($accountcode);
        $this->setAccountname($accountname);
        $this->setModify_code($modify_code);
    }

    function setAccountcode($accountcode)
    {
        $this->accountcode = $accountcode;
    }
    function getAccountcode()
    {
        return $this->accountcode;
    }
    function setAccountname($accountname)
    {
        $this->accountname = $accountname;
    }
    function getAccountname()
    {
        return $this->accountname;
    }
    function setModify_code($modify_code)
    {
        $this->modify_code = $modify_code;
    }
    function getModify_code()
    {
        return $this->modify_code;
    }
    public function returnBasicAccountArray()
    {
        $basicAccount = array();
        $basicAccount['id'] = $this->getAccountcode();
        $basicAccount['accountname'] = $this->getAccountname();
        $basicAccount['modify_code'] = $this->getModify_code();
        return $basicAccount;
    }
}
