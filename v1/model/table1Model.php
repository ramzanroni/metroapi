<?php
class TableException extends Exception
{
}
class Table
{
    private $id;
    private $name;
    public function construct($id, $name)
    {
        $this->setId($id);
        $this->setName($name);
    }
    public function setId($id)
    {
        $this->id = $id;
    }
    public function getId()
    {
        return $this->id;
    }
    public function setName($name)
    {
        $this->name = $name;
    }
    public function getName()
    {
        return $this->name;
    }
    public function returnTableArray()
    {
        $tbl = array();
        $tbl["id"] = 1;
        $tbl["name"] = 2;
        return $tbl;
    }
}
