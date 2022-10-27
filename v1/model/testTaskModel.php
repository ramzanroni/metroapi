<?php
class TaskException extends Exception
{
}
class Task
{
    private $_id;
    private $_title;
    private $_description;
    private $_deadline;
    private $_complete;
    public function __construct($id, $title, $description, $deadline, $complete)
    {
        $this->setId($id);
        $this->setTitle($title);
        $this->setDescription($description);
        $this->setDeadline($deadline);
        $this->setComplete($complete);
    }
    public function setId($id)
    {
        $this->_id = $id;
    }
    public function getId()
    {
        return $this->_id;
    }
    public function setTitle($title)
    {
        $this->_title = $title;
    }
    public function getTitle()
    {
        return $this->_title;
    }
    public function setDescription($description)
    {
        $this->_description = $description;
    }
    public function getDescription()
    {
        return $this->_description;
    }
    public function setDeadline($deadline)
    {
        $this->_deadline = $deadline;
    }
    public function getDeadline()
    {
        return $this->_deadline;
    }
    public function setComplete($complete)
    {
        $this->_complete = $complete;
    }
    public function getComplete()
    {
        return $this->_complete;
    }
    public function returnTaskArray()
    {
        $tasks = array();
        $tasks["id"] = $this->getId();
        $tasks["title"] = $this->getTitle();
        $tasks["description"] = $this->getDescription();
        $tasks["deadline"] = $this->getDeadline();
        $tasks["complete"] = $this->getComplete();
        return $tasks;
    }
}
