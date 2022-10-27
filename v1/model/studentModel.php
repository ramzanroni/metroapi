<?php
class StudentException extends Exception
{
}
class Student
{
    private $_id;
    private $_name;
    private $_school;
    private $_phone;
    private $_address;
    private $_status;
    public function __construct($id, $name, $school, $phone, $address, $status)
    {
        $this->setId($id);
        $this->setName($name);
        $this->setSchool($school);
        $this->setPhone($phone);
        $this->setAddress($address);
        $this->setStatus($status);
    }
    public function setId($id)
    {
        $this->_id = $id;
    }
    public function getId()
    {
        return $this->_id;
    }
    public function setName($name)
    {
        $this->_name = $name;
    }
    public function getName()
    {
        return $this->_name;
    }
    public function setSchool($school)
    {
        $this->_school = $school;
    }
    public function getSchool()
    {
        return $this->_school;
    }
    public function setPhone($phone)
    {
        $this->_phone = $phone;
    }
    public function getPhone()
    {
        return $this->_phone;
    }
    public function setAddress($address)
    {
        $this->_address = $address;
    }
    public function getAddress()
    {
        return $this->_address;
    }
    public function setStatus($status)
    {
        $this->_status = $status;
    }
    public function getStatus()
    {
        return $this->_status;
    }
    public function returnStudentArray()
    {
        $students = array();
        $students["id"] = $this->getId();
        $students["name"] = $this->getName();
        $students["school"] = $this->getSchool();
        $students["phone"] = $this->getPhone();
        $students["address"] = $this->getAddress();
        $students["status"] = $this->getStatus();
        return $students;
    }
}
